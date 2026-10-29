<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/documentos.php';
require_once __DIR__ . '/layout.php';

$nivel = $_SESSION['nivel'] ?? 'Aluno';
if ($nivel === 'Aluno') {
    render_error_view(403, 'Acesso Não Autorizado', 'Área restrita a professores e administradores.', 'home.php');
}

$disciplina = $_REQUEST['disciplina'] ?? DISCIPLINAS[0];
if (!in_array($disciplina, DISCIPLINAS, true)) {
    $disciplina = DISCIPLINAS[0];
}

function nota_valida($v): ?float
{
    $v = trim((string) $v);
    if ($v === '') {
        return null;
    }
    $v = str_replace(',', '.', $v);
    if (!is_numeric($v) || (float) $v < 0 || (float) $v > 10) {
        throw new InvalidArgumentException('Notas devem estar entre 0 e 10.');
    }
    return round((float) $v, 1);
}

// Salvar lançamentos (Post/Redirect/Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou expirado.', 'diarioClasse.php');
    }

    $salvos = 0;
    try {
        $stmt = $conn->prepare(
            'INSERT INTO boletim (usuario_id, disciplina, nota_b1, nota_b2, nota_b3, nota_b4, frequencia, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE nota_b1 = VALUES(nota_b1), nota_b2 = VALUES(nota_b2), nota_b3 = VALUES(nota_b3),
                                     nota_b4 = VALUES(nota_b4), frequencia = VALUES(frequencia), status = VALUES(status)'
        );

        foreach (($_POST['aluno'] ?? []) as $uid => $campos) {
            $uid = (int) $uid;
            $b = [];
            foreach (['b1', 'b2', 'b3', 'b4'] as $k) {
                $b[$k] = nota_valida($campos[$k] ?? '');
            }
            $freqRaw = trim((string) ($campos['freq'] ?? ''));
            if ($uid <= 0 || ($freqRaw === '' && !array_filter($b, fn($x) => $x !== null))) {
                continue;
            }
            if ($freqRaw !== '' && (!ctype_digit($freqRaw) || (int) $freqRaw > 100)) {
                throw new InvalidArgumentException('A frequência deve ser um número entre 0 e 100.');
            }
            $freq = $freqRaw === '' ? 100 : (int) $freqRaw;
            $n1 = $b['b1'] ?? 0.0; $n2 = $b['b2'] ?? 0.0; $n3 = $b['b3'] ?? 0.0; $n4 = $b['b4'] ?? 0.0;
            $status = calc_situacao(($n1 + $n2 + $n3 + $n4) / 4, $freq);

            $stmt->bind_param('isddddis', $uid, $disciplina, $n1, $n2, $n3, $n4, $freq, $status);
            $stmt->execute();
            $salvos++;
        }
        $stmt->close();
        header('Location: diarioClasse.php?disciplina=' . urlencode($disciplina) . '&salvo=' . $salvos);
    } catch (InvalidArgumentException $e) {
        header('Location: diarioClasse.php?disciplina=' . urlencode($disciplina) . '&erro=' . urlencode($e->getMessage()));
    }
    exit;
}

$alunos = $conn->prepare(
    'SELECT u.id, u.usuario, u.email, b.nota_b1, b.nota_b2, b.nota_b3, b.nota_b4, b.frequencia
     FROM usuarios u
     LEFT JOIN boletim b ON b.usuario_id = u.id AND b.disciplina = ?
     WHERE u.nivel = "Aluno" ORDER BY u.usuario'
);
$alunos->bind_param('s', $disciplina);
$alunos->execute();
$lista = $alunos->get_result()->fetch_all(MYSQLI_ASSOC);
$alunos->close();

// Cálculo de rendimento da disciplina
$totalAlunosDisc = count($lista);
$qtdAprovados = 0;
$qtdRecuperacao = 0;
$somaMediasDisc = 0.0;
$avaliados = 0;

foreach ($lista as $item) {
    if ($item['nota_b1'] !== null || $item['nota_b2'] !== null || $item['nota_b3'] !== null || $item['nota_b4'] !== null) {
        $mAluno = ((float)$item['nota_b1'] + (float)$item['nota_b2'] + (float)$item['nota_b3'] + (float)$item['nota_b4']) / 4;
        $fAluno = (int)($item['frequencia'] ?? 100);
        $somaMediasDisc += $mAluno;
        $avaliados++;
        if ($mAluno >= 6.0 && $fAluno >= 75) {
            $qtdAprovados++;
        } else {
            $qtdRecuperacao++;
        }
    }
}
$mediaTurmaDisc = $avaliados > 0 ? round($somaMediasDisc / $avaliados, 1) : 0.0;

$msgOk = isset($_GET['salvo']) ? ((int) $_GET['salvo'] > 0 ? ((int) $_GET['salvo'] . ' lançamento(s) salvo(s) com sucesso!') : 'Nada foi alterado: preencha ao menos uma nota ou frequência.') : null;
$msgErro = $_GET['erro'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><?= layout_head('Diário de Classe') ?></head>
<body>
    <?= layout_navbar('diarioClasse.php') ?>

    <div class="dashboard-container">
        <div class="breadcrumb-bar no-print">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Diário de Classe</span>
        </div>

        <?= layout_alert($msgOk, 'success') ?>
        <?= layout_alert($msgErro, 'danger') ?>

        <!-- Métricas da Disciplina -->
        <div class="stats-row no-print" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon dash-icon-blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <h4><?= $totalAlunosDisc ?></h4>
                    <span>Estudantes Matriculados</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-emerald">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h4><?= number_format($mediaTurmaDisc, 1, ',', '.') ?></h4>
                    <span>Média da Disciplina</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-purple">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <h4><?= $qtdAprovados ?></h4>
                    <span>Aprovados (>= 6,0)</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon dash-icon-blue" style="background:rgba(239, 68, 68, 0.15);color:#fca5a5;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="stat-info">
                    <h4><?= $qtdRecuperacao ?></h4>
                    <span>Em Recuperação / Risco</span>
                </div>
            </div>
        </div>

        <div class="table-card no-print">
            <div class="table-header-bar" style="flex-wrap:wrap;gap:1rem;">
                <div class="table-title">
                    <h2><i class="fa-solid fa-chalkboard" style="color:#38bdf8;margin-right:8px;"></i> Diário: <?= escape_html($disciplina) ?></h2>
                    <span>Lance notas dos 4 bimestres e a frequência. Média e situação são calculadas automaticamente.</span>
                </div>

                <div class="table-actions" style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    <div class="search-wrapper" style="width:240px;margin:0;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" id="filtro-diario" class="search-input" placeholder="Filtrar aluno..." onkeyup="filtrarAlunosDiario()">
                    </div>

                    <form method="get" action="diarioClasse.php" style="margin:0;">
                        <select id="disciplina" name="disciplina" class="select-inline" onchange="this.form.submit()">
                            <?php foreach (DISCIPLINAS as $d): ?>
                                <option value="<?= escape_html($d) ?>" <?= $d === $disciplina ? 'selected' : '' ?>><?= escape_html($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if (!$lista): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>Nenhum aluno cadastrado ainda. Cadastre alunos para lançar notas.</p>
                </div>
            <?php else: ?>
            <form method="post" action="diarioClasse.php" id="form-diario">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <input type="hidden" name="disciplina" value="<?= escape_html($disciplina) ?>">

                <div class="modern-table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Estudante</th>
                                <th style="text-align:center;">1º Bim</th>
                                <th style="text-align:center;">2º Bim</th>
                                <th style="text-align:center;">3º Bim</th>
                                <th style="text-align:center;">4º Bim</th>
                                <th style="text-align:center;">Freq. %</th>
                                <th style="text-align:center;">Média</th>
                                <th style="text-align:right;">Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lista as $a): $id = (int) $a['id']; ?>
                            <tr class="linha-aluno">
                                <td>
                                    <strong><?= escape_html($a['usuario']) ?></strong><br>
                                    <small style="color:var(--text-muted);"><?= escape_html($a['email']) ?></small>
                                </td>
                                <?php foreach (['b1' => 'nota_b1', 'b2' => 'nota_b2', 'b3' => 'nota_b3', 'b4' => 'nota_b4'] as $k => $col): ?>
                                    <td style="text-align:center;">
                                        <input class="nota-input" type="number" step="0.1" min="0" max="10" inputmode="decimal"
                                               name="aluno[<?= $id ?>][<?= $k ?>]" data-nota
                                               value="<?= $a[$col] !== null ? escape_html(number_format((float) $a[$col], 1, '.', '')) : '' ?>"
                                               aria-label="Nota <?= $k ?> de <?= escape_html($a['usuario']) ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td style="text-align:center;">
                                    <input class="nota-input" type="number" step="1" min="0" max="100" inputmode="numeric"
                                           name="aluno[<?= $id ?>][freq]" data-freq
                                           value="<?= $a['frequencia'] !== null ? (int) $a['frequencia'] : '' ?>"
                                           aria-label="Frequência de <?= escape_html($a['usuario']) ?>">
                                </td>
                                <td style="text-align:center;"><strong class="media-cell" style="color:#38bdf8;">–</strong></td>
                                <td style="text-align:right;"><span class="situacao-cell role-pill">–</span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-actions-row" style="justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="preencherFreq100()">
                            <i class="fa-solid fa-check-double"></i> Frequência 100% para Todos
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                            <i class="fa-solid fa-print"></i> Imprimir Diário
                        </button>
                    </div>
                    <div style="display:flex;gap:0.75rem;align-items:center;">
                        <a href="turmas.php" class="btn btn-secondary" style="flex:none;"><i class="fa-solid fa-users-rectangle"></i> Ver Turmas</a>
                        <button type="submit" class="btn btn-primary" style="flex:none;width:auto;margin-top:0;">
                            <i class="fa-solid fa-floppy-disk"></i> Salvar Lançamentos
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="./js/index.js?v=<?= time() ?>"></script>
    <script>
        // Cálculo ao vivo de média e situação enquanto o professor digita
        document.querySelectorAll('.linha-aluno').forEach(function (row) {
            function recalc() {
                var vals = Array.from(row.querySelectorAll('[data-nota]')).map(function (i) { return parseFloat(i.value) || 0; });
                var algum = Array.from(row.querySelectorAll('[data-nota]')).some(function (i) { return i.value !== ''; });
                var freqEl = row.querySelector('[data-freq]');
                var freq = freqEl.value === '' ? 100 : parseInt(freqEl.value, 10);
                var media = vals.reduce(function (a, b) { return a + b; }, 0) / 4;
                var mediaEl = row.querySelector('.media-cell');
                var sitEl = row.querySelector('.situacao-cell');
                if (!algum) { mediaEl.textContent = '–'; sitEl.textContent = '–'; sitEl.className = 'situacao-cell role-pill'; return; }
                mediaEl.textContent = media.toFixed(1).replace('.', ',');
                var txt, cls;
                if (freq < 75) { txt = 'Reprovado por falta'; cls = 'role-danger'; }
                else if (media >= 6) { txt = 'Aprovado'; cls = 'role-aluno'; }
                else { txt = 'Em recuperação'; cls = 'role-prof'; }
                sitEl.textContent = txt;
                sitEl.className = 'situacao-cell role-pill ' + cls;
            }
            row.querySelectorAll('input').forEach(function (i) { i.addEventListener('input', recalc); });
            recalc();
        });

        function preencherFreq100() {
            document.querySelectorAll('[data-freq]').forEach(function(inp) {
                if (!inp.value || inp.value === '' || inp.value === '0') {
                    inp.value = '100';
                    inp.dispatchEvent(new Event('input'));
                }
            });
        }

        function filtrarAlunosDiario() {
            var val = document.getElementById('filtro-diario').value.toLowerCase().trim();
            document.querySelectorAll('.linha-aluno').forEach(function(row) {
                var texto = row.textContent.toLowerCase();
                row.style.display = texto.includes(val) ? '' : 'none';
            });
        }
    </script>

    <!-- Versão de Impressão Oficial do Diário de Classe -->
    <div class="print-only">
        <article class="doc-paper">
            <div class="doc-header">
                <h1>Diário Oficial de Rendimento Escolar - 2026</h1>
                <span>Cadastro Web - Sistema Integrado de Gestão Acadêmica</span>
            </div>
            
            <h2 class="doc-title">Diário de Classe: <?= escape_html($disciplina) ?></h2>

            <div class="doc-info" style="grid-template-columns: 1fr 1fr; margin-bottom: 1.5rem;">
                <div><strong>Docente Responsável:</strong> <?= escape_html($_SESSION['usuario'] ?? 'Professor') ?></div>
                <div><strong>Disciplina:</strong> <?= escape_html($disciplina) ?></div>
                <div><strong>Ano Letivo:</strong> 2026</div>
                <div><strong>Data de Emissão:</strong> <?= date('d/m/Y H:i') ?></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="text-align:left;">Estudante</th>
                        <th>1º Bim</th>
                        <th>2º Bim</th>
                        <th>3º Bim</th>
                        <th>4º Bim</th>
                        <th>Freq.</th>
                        <th>Média</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lista as $item): 
                    $m = ($item['nota_b1'] !== null || $item['nota_b2'] !== null || $item['nota_b3'] !== null || $item['nota_b4'] !== null)
                        ? round(((float)$item['nota_b1'] + (float)$item['nota_b2'] + (float)$item['nota_b3'] + (float)$item['nota_b4']) / 4, 1)
                        : null;
                    $f = $item['frequencia'] !== null ? (int)$item['frequencia'] : 100;
                    $sit = $m !== null ? calc_situacao($m, $f) : '–';
                ?>
                    <tr>
                        <td style="text-align:left;"><strong><?= escape_html($item['usuario']) ?></strong></td>
                        <td><?= $item['nota_b1'] !== null ? number_format((float)$item['nota_b1'], 1, ',', '.') : '–' ?></td>
                        <td><?= $item['nota_b2'] !== null ? number_format((float)$item['nota_b2'], 1, ',', '.') : '–' ?></td>
                        <td><?= $item['nota_b3'] !== null ? number_format((float)$item['nota_b3'], 1, ',', '.') : '–' ?></td>
                        <td><?= $item['nota_b4'] !== null ? number_format((float)$item['nota_b4'], 1, ',', '.') : '–' ?></td>
                        <td><?= $f ?>%</td>
                        <td><strong><?= $m !== null ? number_format($m, 1, ',', '.') : '–' ?></strong></td>
                        <td><?= escape_html($sit) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="doc-signature">
                <div></div>
                <span>Assinatura do Docente Responsável</span>
            </div>

            <div class="doc-footer">
                <div>Autenticação: DIA-<?= date('Y') ?>-<?= strtoupper(substr(md5($disciplina . date('Ymd')), 0, 8)) ?></div>
                <div>Documento gerado automaticamente pelo Sistema Escolar</div>
            </div>
        </article>
    </div>
</body>
</html>
