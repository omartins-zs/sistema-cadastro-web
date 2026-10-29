<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/documentos.php';

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$usuarioNome = $_SESSION['usuario'] ?? 'Aluno';
$usuarioNivel = $_SESSION['nivel'] ?? 'Aluno';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

// Busca notas do aluno no banco
$stmt = $conn->prepare('SELECT disciplina, nota_b1, nota_b2, nota_b3, nota_b4, frequencia, status FROM boletim WHERE usuario_id = ? ORDER BY disciplina');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

$notas = [];
$somaMedias = 0;
$somaFreq = 0;
while ($row = $result->fetch_assoc()) {
    $media = ($row['nota_b1'] + $row['nota_b2'] + $row['nota_b3'] + $row['nota_b4']) / 4;
    $row['media'] = round($media, 1);
    $notas[] = $row;
    $somaMedias += $row['media'];
    $somaFreq += (int) $row['frequencia'];
}
$stmt->close();

$totalDisciplinas = count($notas);
$mediaGeral = $totalDisciplinas > 0 ? round($somaMedias / $totalDisciplinas, 1) : 0.0;
$frequenciaGeral = $totalDisciplinas > 0 ? round($somaFreq / $totalDisciplinas) : 100;

// Dados para o documento impresso (somente o boletim)
$stmt = $conn->prepare('SELECT email FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$stmt->bind_result($usuarioEmail);
$stmt->fetch();
$stmt->close();
$boletimImpressao = render_boletim_impressao(
    ['id' => $usuarioId, 'nome' => $usuarioNome, 'email' => (string) $usuarioEmail],
    doc_notas($conn, $usuarioId)
);

// Comunicados oficiais publicados no sistema
$comunicadosQuery = $conn->query(
    'SELECT c.id, c.titulo, c.mensagem, u.usuario AS autor, u.nivel, DATE_FORMAT(c.criado_em, "%d/%m/%Y %H:%i") AS data_fmt
     FROM comunicados c
     JOIN usuarios u ON u.id = c.autor_id
     ORDER BY c.id DESC
     LIMIT 5'
);
$comunicados = $comunicadosQuery ? $comunicadosQuery->fetch_all(MYSQLI_ASSOC) : [];
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
    <title>Boletim & Desempenho - Cadastro Web</title>
</head>

<body>
    <!-- Top Navigation Bar -->
    <?= layout_navbar('boletim.php') ?>

    <div class="dashboard-container no-print">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Boletim & Desempenho Escolar</span>
        </div>

        <!-- Header -->
        <div class="table-header-bar" style="margin-bottom: 2rem;">
            <div class="table-title">
                <h2>Boletim Escolar & Rendimento Acadêmico</h2>
                <span>Ano Letivo 2026 • Aluno: <strong><?= escape_html($usuarioNome) ?></strong></span>
            </div>

            <div class="table-actions">
                <a href="home.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
                </a>
                <a href="baixar.php?boletim=1" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-file-arrow-down"></i> Baixar PDF
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" style="margin-top:0;">
                    <i class="fa-solid fa-print"></i> Imprimir Boletim
                </button>
            </div>
        </div>

        <!-- Metric Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon dash-icon-emerald">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h4><?= number_format($mediaGeral, 1, ',', '.') ?></h4>
                    <span>Média Geral do Aluno</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon dash-icon-blue">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h4><?= $frequenciaGeral ?>%</h4>
                    <span>Frequência nas Aulas</span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon dash-icon-purple">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <h4>Regular</h4>
                    <span>Situação de Aprovação</span>
                </div>
            </div>
        </div>

        <!-- Tabela de Notas -->
        <div class="table-card" style="margin-bottom: 2rem;">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2>Quadro de Notas por Disciplina</h2>
                    <span>Notas ponderadas nos 4 bimestres escolares</span>
                </div>
            </div>

            <div class="modern-table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-book"></i> Disciplina</th>
                            <th style="text-align: center;">1º Bim</th>
                            <th style="text-align: center;">2º Bim</th>
                            <th style="text-align: center;">3º Bim</th>
                            <th style="text-align: center;">4º Bim</th>
                            <th style="text-align: center;">Média Final</th>
                            <th style="text-align: center;">Frequência</th>
                            <th style="text-align: right;">Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($notas)): ?>
                        <?php foreach ($notas as $item): ?>
                            <tr>
                                <td><strong><?= escape_html($item['disciplina']) ?></strong></td>
                                <td style="text-align: center;"><?= number_format($item['nota_b1'], 1, ',', '.') ?></td>
                                <td style="text-align: center;"><?= number_format($item['nota_b2'], 1, ',', '.') ?></td>
                                <td style="text-align: center;"><?= number_format($item['nota_b3'], 1, ',', '.') ?></td>
                                <td style="text-align: center;"><?= number_format($item['nota_b4'], 1, ',', '.') ?></td>
                                <td style="text-align: center;">
                                    <strong style="color: #38bdf8;"><?= number_format($item['media'], 1, ',', '.') ?></strong>
                                </td>
                                <td style="text-align: center; color: var(--text-muted);"><?= (int) $item['frequencia'] ?>%</td>
                                <td style="text-align: right;">
                                    <span class="role-pill role-aluno">
                                        <i class="fa-solid fa-check"></i> <?= escape_html($item['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Nenhuma disciplina ou nota lançada até o momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Comunicados dos Professores -->
        <div class="table-card">
            <div class="table-title" style="margin-bottom: 1.25rem;">
                <h2><i class="fa-solid fa-bullhorn" style="color: #38bdf8; margin-right: 8px;"></i> Comunicados Pedagógicos</h2>
                <span>Mensagens e orientações emitidas pelos docentes</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php if (!empty($comunicados)): ?>
                    <?php foreach ($comunicados as $com): ?>
                        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap; gap: 0.5rem;">
                                <strong style="color: #38bdf8;">
                                    <i class="fa-solid <?= $com['nivel'] === 'Prof' ? 'fa-chalkboard-user' : 'fa-shield-halved' ?>"></i> 
                                    <?= escape_html($com['autor']) ?> (<?= escape_html($com['nivel']) ?>) - <?= escape_html($com['titulo']) ?>
                                </strong>
                                <small style="color: var(--text-muted);"><?= escape_html($com['data_fmt']) ?></small>
                            </div>
                            <p style="font-size: 0.9rem; color: #cbd5e1; margin: 0; line-height: 1.55;">
                                <?= nl2br(escape_html($com['mensagem'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0; padding: 1rem; text-align: center;">
                        Nenhum comunicado recente publicado pelos professores no momento.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Versão para impressão: apenas o boletim, sem menus ou botões -->
    <div class="print-only"><?= $boletimImpressao ?></div>
    <script src="./js/index.js?v=<?= time() ?>"></script>
</body>

</html>

