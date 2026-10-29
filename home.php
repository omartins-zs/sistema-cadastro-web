<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';

$usuarioNome = $_SESSION['usuario'] ?? 'Usuário';
$usuarioNivel = $_SESSION['nivel'] ?? 'Aluno';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

$totalCadastros = 0;
if ($usuarioNivel === 'Admin' && isset($conn)) {
    $res = $conn->query("SELECT COUNT(*) AS total FROM usuarios");
    if ($res) {
        $row = $res->fetch_assoc();
        $totalCadastros = (int) ($row['total'] ?? 0);
    }
}

$msgSucesso = null;
if (($_GET['cadastrado'] ?? '') === '1') {
    $msgSucesso = 'Novo usuário cadastrado com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    <title>Painel Principal - <?= escape_html($usuarioNivel) ?> - Cadastro Web</title>
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
                    <?php if ($usuarioNivel === 'Admin'): ?>
                        <i class="fa-solid fa-shield-halved"></i>
                    <?php elseif ($usuarioNivel === 'Prof'): ?>
                        <i class="fa-solid fa-chalkboard-user"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-graduation-cap"></i>
                    <?php endif; ?>
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

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <?php if ($msgSucesso): ?>
            <div class="alert alert-success" role="alert" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= escape_html($msgSucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- Hero Welcome -->
        <section class="welcome-hero">
            <div class="welcome-text">
                <h1>Olá, <?= escape_html($usuarioNome) ?>! 👋</h1>
                <?php if ($usuarioNivel === 'Admin'): ?>
                    <p>Painel de Administração e Gestão de Usuários da Instituição.</p>
                <?php elseif ($usuarioNivel === 'Prof'): ?>
                    <p>Ambiente Docente - Gerenciamento de aulas, conteúdos e turmas.</p>
                <?php else: ?>
                    <p>Portal Acadêmico do Estudante - Acompanhe sua trajetória e dados cadastrais.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Stats Row com Diferenciação por Perfil -->
        <div class="stats-row">
            <?php if ($usuarioNivel === 'Admin'): ?>
                <div class="stat-box">
                    <div class="stat-icon dash-icon-blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h4><?= $totalCadastros ?></h4>
                        <span>Usuários Registrados</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Administrador</h4>
                        <span>Acesso Total / CRUD</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Ativo</h4>
                        <span>MySQL Conectado</span>
                    </div>
                </div>

            <?php elseif ($usuarioNivel === 'Prof'): ?>
                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Docente</h4>
                        <span>Perfil Professor</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-blue">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Semestre 2026.1</h4>
                        <span>Período Letivo Vigente</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Autorizado</h4>
                        <span>Lançamento de Aulas</span>
                    </div>
                </div>

            <?php else: ?>
                <div class="stat-box">
                    <div class="stat-icon dash-icon-emerald">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Estudante</h4>
                        <span>Perfil Aluno</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-blue">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Matriculado</h4>
                        <span>Situação Regular</span>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon dash-icon-purple">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Ativo</h4>
                        <span>Acesso ao Portal</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Cards Grid -->
        <div class="dash-grid">
            <?php if ($usuarioNivel === 'Admin'): ?>
                <a href="listarUsuarios.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-blue">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h3>Gerenciar Usuários</h3>
                    <p>Consulte a base completa, edite perfis de acesso e remova contas com segurança.</p>
                    <div class="dash-card-arrow">
                        <span>Acessar lista</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <a href="relatorioUsuario.php" target="_blank" class="dash-card">
                    <div class="dash-icon-box dash-icon-emerald">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <h3>Relatório em PDF</h3>
                    <p>Gere e faça download instantâneo do relatório institucional completo de usuários.</p>
                    <div class="dash-card-arrow">
                        <span>Baixar documento</span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </div>
                </a>

                <a href="cadastroUsuario.php" class="dash-card">
                    <div class="dash-icon-box dash-icon-purple">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3>Cadastrar Novo Usuário</h3>
                    <p>Adicione diretamente novos Alunos, Professores ou Administradores ao sistema.</p>
                    <div class="dash-card-arrow">
                        <span>Cadastrar agora</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

            <?php elseif ($usuarioNivel === 'Prof'): ?>
                <div class="dash-card">
                    <div class="dash-icon-box dash-icon-blue">
                        <i class="fa-solid fa-chalkboard"></i>
                    </div>
                    <h3>Diário de Classe & Notas</h3>
                    <p>Área destinada ao lançamento de notas bimestrais, frequências de alunos e planos de aula.</p>
                    <div class="dash-card-arrow">
                        <span>Módulo Docente</span>
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-icon-box dash-icon-emerald">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </div>
                    <h3>Turmas Atribuídas</h3>
                    <p>Acompanhe os estudantes matriculados nas disciplinas sob sua responsabilidade acadêmica.</p>
                    <div class="dash-card-arrow">
                        <span>Módulo Docente</span>
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-icon-box dash-icon-purple">
                        <i class="fa-solid fa-address-card"></i>
                    </div>
                    <h3>Meu Cadastro Docente</h3>
                    <p>Você está conectado como <strong><?= escape_html($usuarioNome) ?></strong> com nível de privilégio <strong>Professor</strong>.</p>
                    <div class="dash-card-arrow">
                        <span>Credenciais Válidas</span>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

            <?php else: ?>
                <div class="dash-card">
                    <div class="dash-icon-box dash-icon-emerald">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <h3>Boletim & Desempenho</h3>
                    <p>Consulte suas notas, frequência nas aulas e comunicados emitidos pelos professores.</p>
                    <div class="dash-card-arrow">
                        <span>Módulo Aluno</span>
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-icon-box dash-icon-blue">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3>Secretaria Virtual</h3>
                    <p>Solicite declarações de matrícula, atestados de frequência e histórico escolar.</p>
                    <div class="dash-card-arrow">
                        <span>Módulo Aluno</span>
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-icon-box dash-icon-purple">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                    <h3>Meus Dados</h3>
                    <p>Conectado como <strong><?= escape_html($usuarioNome) ?></strong> com perfil <strong>Aluno</strong>. Acesso regular ativo.</p>
                    <div class="dash-card-arrow">
                        <span>Conta Ativa</span>
                        <i class="fa-solid fa-shield"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
