<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';

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
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <strong style="color: #38bdf8;"><i class="fa-solid fa-chalkboard-user"></i> Prof. Carlos (Matemática)</strong>
                        <small style="color: var(--text-muted);">Ontem às 14:30</small>
                    </div>
                    <p style="font-size: 0.9rem; color: #cbd5e1; margin: 0;">
                        Parabéns pelo ótimo desempenho nas avaliações do 4º bimestre! O trabalho final com resolução de problemas já está disponível para consulta na sala de aula.
                    </p>
                </div>

                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <strong style="color: #a855f7;"><i class="fa-solid fa-school"></i> Coordenação Pedagógica</strong>
                        <small style="color: var(--text-muted);">28/10/2026</small>
                    </div>
                    <p style="font-size: 0.9rem; color: #cbd5e1; margin: 0;">
                        Lembramos que o período de renovação de matrículas para o próximo semestre letivo estará aberto a partir da próxima semana através da Secretaria Virtual.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
