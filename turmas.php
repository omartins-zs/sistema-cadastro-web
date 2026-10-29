<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';

$usuarioNome = $_SESSION['usuario'] ?? 'Professor';
$usuarioNivel = $_SESSION['nivel'] ?? 'Prof';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

if ($usuarioNivel === 'Aluno') {
    render_error_view(403, 'Acesso Não Autorizado', 'Área restrita a professores e administradores.', 'home.php');
}

$totalAlunos = 0;
$countRes = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel = 'Aluno'");
if ($countRes) {
    $totalAlunos = (int) ($countRes->fetch_assoc()['total'] ?? 0);
}
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
    <title>Turmas Atribuídas - Cadastro Web</title>
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
            <span>Turmas & Carga Horária Atribuída</span>
        </div>

        <div class="table-header-bar" style="margin-bottom: 2rem;">
            <div class="table-title">
                <h2>Turmas Atribuídas e Horários</h2>
                <span>Acompanhamento do corpo discente por turma e disciplina</span>
            </div>

            <div class="table-actions">
                <a href="home.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
                </a>
                <a href="diarioClasse.php" class="btn btn-primary btn-sm" style="margin-top:0;">
                    <i class="fa-solid fa-chalkboard"></i> Abrir Diário de Classe
                </a>
            </div>
        </div>

        <div class="dash-grid">
            <div class="dash-card">
                <div class="dash-icon-box dash-icon-blue">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>
                <h3>Turma 3º Ano A - Ensino Médio</h3>
                <p><strong>Disciplina:</strong> Matemática Aplicada<br>
                   <strong>Carga Horária:</strong> 80 horas/aula<br>
                   <strong>Alunos Matriculados:</strong> <?= $totalAlunos ?> alunos
                </p>
                <div class="dash-card-arrow">
                    <span>Horário: Segundas e Quartas (08:00 - 10:00)</span>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-icon-box dash-icon-emerald">
                    <i class="fa-solid fa-users-rectangle"></i>
                </div>
                <h3>Turma 2º Ano B - Ensino Fundamental</h3>
                <p><strong>Disciplina:</strong> Raciocínio Lógico & Geometria<br>
                   <strong>Carga Horária:</strong> 60 horas/aula<br>
                   <strong>Alunos Matriculados:</strong> <?= $totalAlunos ?> alunos
                </p>
                <div class="dash-card-arrow">
                    <span>Horário: Terças e Quintas (10:15 - 12:00)</span>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-icon-box dash-icon-purple">
                    <i class="fa-solid fa-flask"></i>
                </div>
                <h3>Laboratório de Práticas & Monitoria</h3>
                <p><strong>Atividade:</strong> Plantão de Dúvidas e Reforço Escolar<br>
                   <strong>Carga Horária:</strong> 20 horas/aula<br>
                   <strong>Atendimento:</strong> Aberto a todos os alunos
                </p>
                <div class="dash-card-arrow">
                    <span>Horário: Sextas-feiras (14:00 - 16:30)</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

