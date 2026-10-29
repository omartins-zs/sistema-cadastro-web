<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';

$usuarioNome = $_SESSION['usuario'] ?? 'Professor';
$usuarioNivel = $_SESSION['nivel'] ?? 'Prof';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

// Alunos não podem acessar o diário de classe docente
if ($usuarioNivel === 'Aluno') {
    render_error_view(403, 'Acesso Não Autorizado', 'Área restrita a professores e administradores.', 'home.php');
}

// Busca alunos com notas para o professor visualizar
$alunosRes = $conn->query("
    SELECT u.id, u.usuario, u.email, b.disciplina, b.nota_b1, b.nota_b2, b.nota_b3, b.nota_b4, b.frequencia, b.status 
    FROM usuarios u
    JOIN boletim b ON b.usuario_id = u.id
    WHERE b.disciplina = 'Matemática'
    ORDER BY u.usuario
");
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
    <title>Diário de Classe & Notas - Cadastro Web</title>
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
                    <i class="fa-solid fa-chalkboard-user"></i>
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
            <span>Diário de Classe & Lançamento de Notas</span>
        </div>

        <div class="table-card">
            <div class="table-header-bar">
                <div class="table-title">
                    <h2><i class="fa-solid fa-chalkboard" style="color: #38bdf8; margin-right: 8px;"></i> Diário de Classe: Matemática - Turma 3º Ano A</h2>
                    <span>Docente responsável: <strong><?= escape_html($usuarioNome) ?></strong> • Ano Letivo 2026</span>
                </div>

                <div class="table-actions">
                    <a href="home.php" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="alert('Notas consolidadas com sucesso!')" style="margin-top:0;">
                        <i class="fa-solid fa-floppy-disk"></i> Salvar Lançamentos
                    </button>
                </div>
            </div>

            <div class="modern-table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-user-graduate"></i> Estudante</th>
                            <th><i class="fa-solid fa-envelope"></i> E-mail</th>
                            <th style="text-align: center;">1º Bim</th>
                            <th style="text-align: center;">2º Bim</th>
                            <th style="text-align: center;">3º Bim</th>
                            <th style="text-align: center;">4º Bim</th>
                            <th style="text-align: center;">Média</th>
                            <th style="text-align: right;">Frequência</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($alunosRes && $alunosRes->num_rows > 0): ?>
                        <?php while ($aluno = $alunosRes->fetch_assoc()): ?>
                            <?php $media = round(($aluno['nota_b1'] + $aluno['nota_b2'] + $aluno['nota_b3'] + $aluno['nota_b4']) / 4, 1); ?>
                            <tr>
                                <td><strong><?= escape_html($aluno['usuario']) ?></strong></td>
                                <td style="color: var(--text-muted);"><?= escape_html($aluno['email']) ?></td>
                                <td style="text-align: center;"><?= number_format($aluno['nota_b1'], 1, ',', '.') ?></td>
                                <td style="text-align: center;"><?= number_format($aluno['nota_b2'], 1, ',', '.') ?></td>
                                <td style="text-align: center;"><?= number_format($aluno['nota_b3'], 1, ',', '.') ?></td>
                                <td style="text-align: center;"><?= number_format($aluno['nota_b4'], 1, ',', '.') ?></td>
                                <td style="text-align: center;">
                                    <strong style="color: #38bdf8;"><?= number_format($media, 1, ',', '.') ?></strong>
                                </td>
                                <td style="text-align: right;">
                                    <span class="role-pill role-aluno">
                                        <?= (int) $aluno['frequencia'] ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                Nenhum estudante matriculado nesta turma até o momento.
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
