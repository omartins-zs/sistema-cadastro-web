<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$usuarioNome = $_SESSION['usuario'] ?? 'Aluno';
$usuarioNivel = $_SESSION['nivel'] ?? 'Aluno';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

$documentoEmitido = null;
$msgSucesso = null;

// Processa emissão de novo documento via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou expirado.', 'secretaria.php');
    }

    $tipoDoc = trim($_POST['tipo_documento'] ?? '');
    $docsValidos = [
        'Declaração de Matrícula Regular',
        'Atestado de Frequência Escolar',
        'Histórico Escolar Parcial',
        'Declaração de Quitação e Nada Consta'
    ];

    if (in_array($tipoDoc, $docsValidos, true)) {
        $protocolo = 'SEC-' . date('Y') . '-' . str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $status = 'Emitido (Disponível)';

        $stmt = $conn->prepare('INSERT INTO solicitacoes_secretaria (usuario_id, tipo_documento, protocolo, status, data_solicitacao) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('isss', $usuarioId, $tipoDoc, $protocolo, $status);
        $stmt->execute();
        $stmt->close();

        $documentoEmitido = [
            'tipo' => $tipoDoc,
            'protocolo' => $protocolo,
            'aluno' => $usuarioNome,
            'data' => date('d/m/Y H:i'),
            'codigo_autenticidade' => strtoupper(substr(hash('sha256', $protocolo . $usuarioNome . time()), 0, 16))
        ];
        $msgSucesso = 'Documento emitido com sucesso e autenticado digitalmente!';
    }
}

// Busca histórico de solicitações do aluno
$stmt = $conn->prepare('SELECT id, tipo_documento, protocolo, status, DATE_FORMAT(data_solicitacao, "%d/%m/%Y %H:%i") as data_fmt FROM solicitacoes_secretaria WHERE usuario_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$solicitacoes = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="./css/app.css?v=<?= $cacheVersion ?>">
    <title>Secretaria Virtual - Cadastro Web</title>
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="app-navbar">
        <a href="home.php" class="nav-brand">
            <div class="nav-brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <span>Cadastro Web</span>
        </a>

        <div class="nav-user-area">
            <div class="user-badge-pill">
                <span class="user-avatar-sm"><?= escape_html($primeiraLetra) ?></span>
                <strong><?= escape_html($usuarioNome) ?></strong>
                <span class="role-pill role-<?= strtolower($usuarioNivel) ?>">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <?= escape_html($usuarioNivel) ?>
                </span>
            </div>

            <form action="logout.php" method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <button type="submit" class="btn btn-secondary btn-sm" title="Encerrar sessão">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Secretaria Virtual</span>
        </div>

        <?php if ($msgSucesso): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= escape_html($msgSucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- Bloco de Documento Recém-Emitido (Certificado Digital) -->
        <?php if ($documentoEmitido): ?>
            <div class="table-card" style="margin-bottom: 2rem; border-color: rgba(56, 189, 248, 0.4); background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <span class="role-pill role-aluno" style="margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-certificate"></i> Documento Oficial Autenticado
                        </span>
                        <h2 style="font-size: 1.4rem; color: #fff; margin-top: 0.25rem;"><?= escape_html($documentoEmitido['tipo']) ?></h2>
                        <span style="color: var(--text-muted); font-size: 0.88rem;">Protocolo: <strong><?= escape_html($documentoEmitido['protocolo']) ?></strong></span>
                    </div>

                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" style="margin-top:0;">
                        <i class="fa-solid fa-print"></i> Imprimir / Salvar PDF
                    </button>
                </div>

                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 1.75rem; color: #e2e8f0; line-height: 1.6; font-size: 0.95rem;">
                    <div style="text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 1rem; margin-bottom: 1.25rem;">
                        <strong style="font-size: 1.15rem; color: #fff; text-transform: uppercase;">Instituição de Ensino Cadastro Web</strong><br>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Secretaria Geral de Registros Acadêmicos</span>
                    </div>

                    <p style="margin-bottom: 1rem;">
                        Certificamos para os devidos fins que o(a) estudante <strong><?= escape_html($documentoEmitido['aluno']) ?></strong> encontra-se regularmente matriculado(a) e com situação acadêmica ativa no período letivo de 2026.
                    </p>
                    <p style="margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.88rem;">
                        Este documento foi emitido eletronicamente pela Secretaria Virtual e possui validade jurídica respaldada por chave de autenticação digital.
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; border-top: 1px dashed rgba(255, 255, 255, 0.1); padding-top: 1rem; font-size: 0.82rem; color: var(--text-muted);">
                        <span>Data de Emissão: <strong><?= escape_html($documentoEmitido['data']) ?></strong></span>
                        <span>Chave Digital: <strong style="color: #38bdf8;"><?= escape_html($documentoEmitido['codigo_autenticidade']) ?></strong></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulário de Nova Solicitação -->
        <div class="table-card" style="margin-bottom: 2rem;">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2><i class="fa-solid fa-file-circle-plus" style="color: #38bdf8; margin-right: 8px;"></i> Solicitar Novo Documento</h2>
                    <span>Selecione a declaração necessária para geração instantânea</span>
                </div>
            </div>

            <form action="secretaria.php" method="post" class="auth-form" style="max-width: 600px;">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                <div class="form-group">
                    <label class="form-label" for="tipo_documento">Tipo de Documento Desejado</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-file-contract input-icon-left"></i>
                        <select id="tipo_documento" name="tipo_documento" class="form-control" required>
                            <option value="Declaração de Matrícula Regular">Declaração de Matrícula Regular</option>
                            <option value="Atestado de Frequência Escolar">Atestado de Frequência Escolar</option>
                            <option value="Histórico Escolar Parcial">Histórico Escolar Parcial</option>
                            <option value="Declaração de Quitação e Nada Consta">Declaração de Quitação e Nada Consta</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="max-width: 260px; margin-top: 0.5rem;">
                    <i class="fa-solid fa-bolt"></i> Emitir Documento Agora
                </button>
            </form>
        </div>

        <!-- Histórico de Documentos -->
        <div class="table-card">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2>Histórico de Documentos e Protocolos</h2>
                    <span>Consulte os requerimentos já emitidos para o seu usuário</span>
                </div>
            </div>

            <div class="modern-table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-hashtag"></i> Protocolo</th>
                            <th><i class="fa-solid fa-file-lines"></i> Documento</th>
                            <th><i class="fa-solid fa-calendar-day"></i> Data de Emissão</th>
                            <th style="text-align: right;"><i class="fa-solid fa-circle-check"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($solicitacoes && $solicitacoes->num_rows > 0): ?>
                        <?php while ($row = $solicitacoes->fetch_assoc()): ?>
                            <tr>
                                <td><strong style="color: #38bdf8;"><?= escape_html($row['protocolo']) ?></strong></td>
                                <td><?= escape_html($row['tipo_documento']) ?></td>
                                <td style="color: var(--text-muted);"><?= escape_html($row['data_fmt']) ?></td>
                                <td style="text-align: right;">
                                    <span class="role-pill role-aluno">
                                        <i class="fa-solid fa-check"></i> <?= escape_html($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Nenhuma solicitação realizada até o momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
