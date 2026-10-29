<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/documentos.php';

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$usuarioNome = $_SESSION['usuario'] ?? 'Usuário';
$usuarioNivel = $_SESSION['nivel'] ?? 'Aluno';
$cacheVersion = time();

// Se for Admin ou Professor, permite selecionar o aluno para visualização
$alunoId = $usuarioId;
$alunosList = [];
if ($usuarioNivel !== 'Aluno') {
    $resAlunos = $conn->query("SELECT id, usuario, email FROM usuarios WHERE nivel = 'Aluno' ORDER BY usuario");
    if ($resAlunos) {
        $alunosList = $resAlunos->fetch_all(MYSQLI_ASSOC);
    }
    $reqAluno = filter_input(INPUT_GET, 'aluno_id', FILTER_VALIDATE_INT);
    if ($reqAluno && array_filter($alunosList, fn($a) => (int)$a['id'] === $reqAluno)) {
        $alunoId = $reqAluno;
    } elseif (!empty($alunosList)) {
        $alunoId = (int) $alunosList[0]['id'];
    }
}

// Obter dados do aluno selecionado
$stmtAluno = $conn->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
$stmtAluno->bind_param("i", $alunoId);
$stmtAluno->execute();
$stmtAluno->bind_result($alunoNome, $alunoEmail);
$stmtAluno->fetch();
$stmtAluno->close();

// Processamento de Ações (Pagamento Instantâneo / Emissão de Nada Consta)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou expirado.', 'financeiro.php');
    }

    $acao = $_POST['acao'] ?? '';

    // Pagamento com baixa imediata no banco de dados
    if ($acao === 'pagar_mensalidade') {
        $mensalidadeId = (int) ($_POST['mensalidade_id'] ?? 0);
        
        $stmtPagar = $conn->prepare("UPDATE mensalidades SET status = 'Pago', data_pagamento = NOW(), forma_pagamento = 'PIX' WHERE id = ? AND usuario_id = ?");
        $stmtPagar->bind_param("ii", $mensalidadeId, $alunoId);
        $stmtPagar->execute();
        $stmtPagar->close();

        header("Location: financeiro.php" . ($usuarioNivel !== 'Aluno' ? "?aluno_id={$alunoId}&" : "?") . "pago=1");
        exit;
    }

    // Emissão rápida da Declaração de Quitação e Nada Consta diretamente da tela financeira
    if ($acao === 'emitir_nada_consta') {
        $tipoDoc = 'Declaração de Quitação e Nada Consta';
        $protocolo = 'SEC-' . date('Y') . '-' . str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $status = 'Emitido (Disponível)';

        $stmtDoc = $conn->prepare("INSERT INTO solicitacoes_secretaria (usuario_id, tipo_documento, protocolo, status, data_solicitacao) VALUES (?, ?, ?, ?, NOW())");
        $stmtDoc->bind_param("isss", $alunoId, $tipoDoc, $protocolo, $status);
        $stmtDoc->execute();
        $novoId = $stmtDoc->insert_id;
        $stmtDoc->close();

        header("Location: secretaria.php?ver={$novoId}&novo=1");
        exit;
    }

    header("Location: financeiro.php");
    exit;
}

// Consultar todas as mensalidades do aluno
$stmt = $conn->prepare("SELECT id, competencia, mes_numero, ano, valor, vencimento, status, DATE_FORMAT(vencimento, '%d/%m/%Y') AS vencimento_fmt, DATE_FORMAT(data_pagamento, '%d/%m/%Y %H:%i') AS data_pagamento_fmt, forma_pagamento, codigo_pix, linha_digitavel FROM mensalidades WHERE usuario_id = ? ORDER BY mes_numero ASC");
$stmt->bind_param("i", $alunoId);
$stmt->execute();
$res = $stmt->get_result();

$mensalidades = [];
$totalPago = 0.0;
$totalPendente = 0.0;
$qtdPagas = 0;
$qtdPendentes = 0;
$proximoVencimento = null;

while ($row = $res->fetch_assoc()) {
    $mensalidades[] = $row;
    if ($row['status'] === 'Pago') {
        $totalPago += (float) $row['valor'];
        $qtdPagas++;
    } else {
        $totalPendente += (float) $row['valor'];
        $qtdPendentes++;
        if ($proximoVencimento === null) {
            $proximoVencimento = $row['vencimento_fmt'] . ' (R$ ' . number_format($row['valor'], 2, ',', '.') . ')';
        }
    }
}
$stmt->close();

$emDia = ($qtdPendentes === 0) || ($qtdPendentes <= 2 && $qtdPagas >= 10);
$msgPago = isset($_GET['pago']) ? 'Pagamento processado e baixado com sucesso via PIX Instantâneo!' : null;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?= layout_head('Financeiro & Mensalidades') ?>
</head>

<body>
    <!-- Top Navigation Bar -->
    <?= layout_navbar('financeiro.php') ?>

    <div class="dashboard-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar no-print">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Financeiro & Mensalidades</span>
        </div>

        <?= layout_alert($msgPago, 'success') ?>

        <!-- Table Header Bar -->
        <div class="table-header-bar no-print" style="margin-bottom: 2rem;">
            <div class="table-title">
                <h2><i class="fa-solid fa-wallet" style="color: #38bdf8; margin-right: 8px;"></i> Gestão Financeira Escolar</h2>
                <span>Acompanhamento de mensalidades, comprovantes de pagamento e emissão de Nada Consta</span>
            </div>

            <div class="table-actions">
                <?php if ($usuarioNivel !== 'Aluno' && !empty($alunosList)): ?>
                    <form method="get" action="financeiro.php" style="display:flex;align-items:center;gap:0.5rem;">
                        <label for="select-aluno" class="form-label" style="margin:0;font-size:0.85rem;">Aluno:</label>
                        <select id="select-aluno" name="aluno_id" class="select-inline" onchange="this.form.submit()">
                            <?php foreach ($alunosList as $al): ?>
                                <option value="<?= (int)$al['id'] ?>" <?= (int)$al['id'] === $alunoId ? 'selected' : '' ?>>
                                    <?= escape_html($al['usuario']) ?> (<?= escape_html($al['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
                <a href="secretaria.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-file-contract"></i> Secretaria Virtual
                </a>
            </div>
        </div>

        <!-- Cards de Resumo Financeiro -->
        <div class="stats-row no-print" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon dash-icon-emerald">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <h4><?= $qtdPendentes === 0 ? 'Quitado' : 'Em Dia' ?></h4>
                    <span>Situação da Matrícula</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-blue">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h4>R$ <?= number_format($totalPago, 2, ',', '.') ?></h4>
                    <span>Total Liquidado (<?= $qtdPagas ?> parcelas)</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-purple">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h4>R$ <?= number_format($totalPendente, 2, ',', '.') ?></h4>
                    <span>A Vencer (<?= $qtdPendentes ?> parcelas)</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-blue">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h4 style="font-size:1.1rem;"><?= $proximoVencimento ?? 'Nenhuma pendente' ?></h4>
                    <span>Próximo Vencimento</span>
                </div>
            </div>
        </div>

        <!-- Banner de Integração com Nada Consta -->
        <div class="table-card no-print" style="margin-bottom: 2rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(14, 165, 233, 0.12) 100%); border-color: rgba(56, 189, 248, 0.3);">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1.5rem;">
                <div style="max-width: 750px;">
                    <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(16, 185, 129, 0.2);color:#34d399;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.8rem;font-weight:600;margin-bottom:0.75rem;">
                        <i class="fa-solid fa-shield-halved"></i> Situação Financeira Regularizada
                    </div>
                    <h3 style="font-size:1.35rem;color:#ffffff;margin-bottom:0.4rem;font-weight:700;">
                        Declaração de Quitação e Nada Consta Disponível
                    </h3>
                    <p style="color:#cbd5e1;font-size:0.92rem;margin:0;line-height:1.5;">
                        Todas as parcelas com vencimento até o momento foram devidamente liquidadas. Você tem direito a emitir instantaneamente a Declaração Oficial de Quitação e Nada Consta com autenticação digital.
                    </p>
                </div>

                <form method="post" action="financeiro.php" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                    <input type="hidden" name="acao" value="emitir_nada_consta">
                    <button type="submit" class="btn btn-primary" style="padding: 0.85rem 1.6rem;font-size:0.95rem;display:inline-flex;align-items:center;gap:0.6rem;">
                        <i class="fa-solid fa-file-shield"></i> Emitir Nada Consta Agora
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabela de Mensalidades -->
        <div class="table-card no-print">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2>Grade de Mensalidades - Ano Letivo 2026</h2>
                    <span>Aluno: <strong><?= escape_html($alunoNome) ?></strong> (Matrícula: <?= str_pad((string)$alunoId, 6, '0', STR_PAD_LEFT) ?>)</span>
                </div>
            </div>

            <div class="modern-table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Competência</th>
                            <th style="width: 14%;">Vencimento</th>
                            <th style="width: 14%;">Valor</th>
                            <th style="width: 16%;">Status</th>
                            <th style="width: 16%;">Data de Liquidação</th>
                            <th style="width: 18%; text-align: right;">Ações & Comprovantes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($mensalidades)): ?>
                        <?php foreach ($mensalidades as $m): ?>
                            <tr>
                                <td><strong style="color:#f8fafc;"><?= escape_html($m['competencia']) ?></strong></td>
                                <td style="color:#cbd5e1;"><?= escape_html($m['vencimento_fmt']) ?></td>
                                <td><strong style="color:#38bdf8;">R$ <?= number_format((float)$m['valor'], 2, ',', '.') ?></strong></td>
                                <td>
                                    <?php if ($m['status'] === 'Pago'): ?>
                                        <span class="role-pill role-aluno">
                                            <i class="fa-solid fa-circle-check"></i> Pago (<?= escape_html($m['forma_pagamento'] ?? 'PIX') ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="role-pill role-prof" style="background:rgba(234, 179, 8, 0.16);color:#fde047;border-color:rgba(234, 179, 8, 0.35);">
                                            <i class="fa-solid fa-clock"></i> Em Aberto
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:var(--text-muted);">
                                    <?= $m['data_pagamento_fmt'] ? escape_html($m['data_pagamento_fmt']) : '–' ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions-cell">
                                        <?php if ($m['status'] === 'Pago'): ?>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="abrirRecibo(<?= htmlspecialchars(json_encode([
                                                'id' => (int)$m['id'],
                                                'competencia' => $m['competencia'],
                                                'valor' => number_format((float)$m['valor'], 2, ',', '.'),
                                                'aluno' => $alunoNome,
                                                'matricula' => str_pad((string)$alunoId, 6, '0', STR_PAD_LEFT),
                                                'data' => $m['data_pagamento_fmt'],
                                                'forma' => $m['forma_pagamento'] ?? 'PIX',
                                                'protocolo' => 'REC-2026-' . str_pad((string)$m['id'], 6, '0', STR_PAD_LEFT)
                                            ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="fa-solid fa-receipt"></i> Recibo
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="abrirPix(<?= (int)$m['id'] ?>, '<?= escape_html($m['competencia']) ?>', '<?= number_format((float)$m['valor'], 2, ',', '.') ?>', '<?= escape_html($m['codigo_pix'] ?? '') ?>')">
                                                <i class="fa-solid fa-qrcode"></i> Pagar c/ PIX
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                Nenhuma mensalidade registrada para este aluno.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Pagamento PIX -->
    <div id="modal-pix" class="modal-backdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
        <div class="modal-box table-card" style="max-width:520px;width:100%;margin:auto;box-shadow:0 25px 50px rgba(0,0,0,0.5);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
                <h3 style="font-size:1.25rem;color:#ffffff;display:flex;align-items:center;gap:0.5rem;margin:0;">
                    <i class="fa-brands fa-pix" style="color:#34d399;"></i> Pagamento via PIX Instantâneo
                </h3>
                <button type="button" onclick="fecharPix()" style="background:none;border:none;color:#94a3b8;font-size:1.2rem;cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="text-align:center;margin-bottom:1.25rem;">
                <div style="background:#ffffff;padding:1rem;border-radius:12px;display:inline-block;margin-bottom:0.75rem;">
                    <img id="pix-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=PIX_MENSALIDADE" alt="QR Code PIX" style="width:160px;height:160px;display:block;">
                </div>
                <p style="font-size:0.95rem;color:#ffffff;margin:0 0 0.25rem;">
                    Mensalidade: <strong id="pix-comp" style="color:#38bdf8;">–</strong>
                </p>
                <p style="font-size:1.35rem;font-weight:700;color:#34d399;margin:0;">
                    R$ <span id="pix-val">0,00</span>
                </p>
            </div>

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Código Pix Copia e Cola:</label>
                <div class="input-wrapper">
                    <input type="text" id="pix-code" class="form-control" readonly style="font-size:0.8rem;padding-right:90px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="copiarPix()" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);height:34px;">
                        <i class="fa-solid fa-copy"></i> Copiar
                    </button>
                </div>
            </div>

            <form method="post" action="financeiro.php" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <input type="hidden" name="acao" value="pagar_mensalidade">
                <input type="hidden" name="mensalidade_id" id="pix-id" value="">
                
                <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="fecharPix()">Fechar</button>
                    <button type="submit" class="btn btn-primary" style="margin-top:0;width:auto;">
                        <i class="fa-solid fa-bolt"></i> Simular Pagamento Instantâneo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Recibo de Pagamento -->
    <div id="modal-recibo" class="modal-backdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
        <div class="modal-box" style="max-width:620px;width:100%;margin:auto;">
            <div id="recibo-corpo" class="doc-paper" style="padding:2rem;">
                <div class="doc-header">
                    <h1>Comprovante de Quitação Escolar</h1>
                    <span>Cadastro Web - Sistema Integrado de Gestão Acadêmica</span>
                </div>
                
                <h2 class="doc-title" style="font-size:1.1rem;margin-bottom:1rem;">Recibo de Pagamento</h2>

                <div class="doc-info" style="grid-template-columns:1fr 1fr;margin-bottom:1rem;">
                    <div><strong>Estudante:</strong> <span id="rec-aluno"></span></div>
                    <div><strong>Matrícula:</strong> <span id="rec-mat"></span></div>
                    <div><strong>Competência:</strong> <span id="rec-comp"></span></div>
                    <div><strong>Forma de Pagto:</strong> <span id="rec-forma"></span></div>
                    <div><strong>Valor Liquidado:</strong> R$ <span id="rec-val" style="color:#047857;font-weight:700;"></span></div>
                    <div><strong>Data/Hora:</strong> <span id="rec-data"></span></div>
                </div>

                <p style="font-size:0.88rem;color:#334155;text-align:justify;margin-bottom:1.5rem;">
                    A instituição declara para os devidos fins ter recebido o valor supracitado correspondente à mensalidade escolar especificada, conferindo plena, geral e irrevogável quitação referente a esta parcela.
                </p>

                <div class="doc-footer" style="margin-top:1rem;">
                    <div><strong>Protocolo:</strong> <span id="rec-prot"></span></div>
                    <div><strong>Status:</strong> <span class="doc-ok"><i class="fa-solid fa-check"></i> Liquidado</span></div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;" class="no-print">
                <button type="button" class="btn btn-secondary" onclick="fecharRecibo()">Fechar</button>
                <button type="button" class="btn btn-primary" style="margin-top:0;width:auto;" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Imprimir Recibo
                </button>
            </div>
        </div>
    </div>

    <script src="./js/index.js?v=<?= time() ?>"></script>
    <script>
        function abrirPix(id, comp, valor, codPix) {
            document.getElementById('pix-id').value = id;
            document.getElementById('pix-comp').textContent = comp;
            document.getElementById('pix-val').textContent = valor;
            document.getElementById('pix-code').value = codPix || ('00020126580014BR.GOV.BCB.PIX0136escola@financeiro.edu.br5204000053039865406' + valor + '5802BR5915Escola6009SP62070503***6304');
            document.getElementById('pix-qr').src = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(codPix || 'PAGAMENTO_PIX_' + id);
            var m = document.getElementById('modal-pix');
            m.style.display = 'flex';
        }

        function fecharPix() {
            document.getElementById('modal-pix').style.display = 'none';
        }

        function copiarPix() {
            var inp = document.getElementById('pix-code');
            inp.select();
            navigator.clipboard.writeText(inp.value).then(function() {
                alert('Código PIX copiado com sucesso para a área de transferência!');
            });
        }

        function abrirRecibo(dados) {
            document.getElementById('rec-aluno').textContent = dados.aluno;
            document.getElementById('rec-mat').textContent = dados.matricula;
            document.getElementById('rec-comp').textContent = dados.competencia;
            document.getElementById('rec-forma').textContent = dados.forma;
            document.getElementById('rec-val').textContent = dados.valor;
            document.getElementById('rec-data').textContent = dados.data;
            document.getElementById('rec-prot').textContent = dados.protocolo;
            var m = document.getElementById('modal-recibo');
            m.style.display = 'flex';
        }

        function fecharRecibo() {
            document.getElementById('modal-recibo').style.display = 'none';
        }
    </script>
</body>

</html>
