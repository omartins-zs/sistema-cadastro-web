<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
start_app_session();
$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin = (($_SESSION['nivel'] ?? '') === 'Admin');
$usuarioNome = $_SESSION['usuario'] ?? 'Admin';
$usuarioNivel = $_SESSION['nivel'] ?? 'Admin';
$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
$cacheVersion = time();
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
    <title><?= $isAdmin ? 'Novo Usuário' : 'Cadastro de Usuário' ?> - Cadastro Web</title>
</head>

<body>
    <?php if ($isAdmin): ?>
        <!-- Top Navigation Bar para Administrador -->
        <?= layout_navbar('listarUsuarios.php') ?>

        <div class="edit-page-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb-bar">
                <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <a href="listarUsuarios.php">Gerenciar Usuários</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <span>Novo Usuário</span>
            </div>

            <div class="edit-card">
                <div class="auth-header">
                    <div class="auth-badge">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h1>Cadastrar Novo Usuário</h1>
                    <p>Adicione uma nova conta definindo usuário, senha e nível de privilégio</p>
                </div>

                <?php if (($_GET['erro'] ?? '') === 'cadastro'): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Confira os dados. O usuário ou e-mail pode já estar cadastrado ou a senha possui menos de 8 caracteres.</span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" action="cadastro.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                    <div class="form-group">
                        <label class="form-label" for="txt_usuario">Nome de Usuário</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon-left"></i>
                            <input id="txt_usuario" class="form-control" type="text" name="txt_usuario" maxlength="120" placeholder="Ex: professor_marcos" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="txt_email">Endereço de E-mail</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input id="txt_email" class="form-control" type="email" name="txt_email" maxlength="255" placeholder="email@exemplo.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="txt_senha">Senha Inicial</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon-left"></i>
                            <input id="txt_senha" class="form-control" type="password" name="txt_senha" minlength="8" placeholder="Digite no mínimo 8 caracteres" autocomplete="new-password" required>
                            <span role="button" class="btn-toggle-password" onclick="togglePasswordVisibility('txt_senha', this)" title="Mostrar ou ocultar senha" aria-label="Mostrar ou ocultar senha">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        <span class="field-hint">
                            <i class="fa-solid fa-circle-info"></i> Mínimo de 8 caracteres
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nivel">Perfil de Acesso</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-id-badge input-icon-left"></i>
                            <select id="nivel" name="nivel" class="form-control" required>
                                <option value="Aluno" selected>Aluno (Estudante)</option>
                                <option value="Prof">Prof (Professor)</option>
                                <option value="Admin">Admin (Administrador)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions-row">
                        <a href="home.php" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Voltar ao Início
                        </a>
                        <button type="submit" class="btn btn-primary" style="margin-top: 0;">
                            <i class="fa-solid fa-user-plus"></i> Salvar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Tela de Auto-Cadastro Público (Visitante Não Autenticado) -->
        <main>
            <div class="auth-wrapper">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="auth-badge">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <h1>Cadastro de Usuário</h1>
                        <p>Preencha os campos abaixo para criar sua conta</p>
                    </div>

                    <?php if (($_GET['erro'] ?? '') === 'cadastro'): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Confira os dados. O usuário ou e-mail pode já estar cadastrado ou a senha possui menos de 8 caracteres.</span>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" action="cadastro.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                        <div class="form-group">
                            <label class="form-label" for="txt_usuario">Nome de Usuário</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-user input-icon-left"></i>
                                <input id="txt_usuario" class="form-control" type="text" name="txt_usuario" maxlength="120" placeholder="Ex: gabriel_silva" autocomplete="username" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="txt_email">Endereço de E-mail</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-envelope input-icon-left"></i>
                                <input id="txt_email" class="form-control" type="email" name="txt_email" maxlength="255" placeholder="seuemail@exemplo.com" autocomplete="email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="txt_senha">Senha de Acesso</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-lock input-icon-left"></i>
                                <input id="txt_senha" class="form-control" type="password" name="txt_senha" minlength="8" placeholder="Digite uma senha segura" autocomplete="new-password" required>
                                <span role="button" class="btn-toggle-password" onclick="togglePasswordVisibility('txt_senha', this)" title="Mostrar ou ocultar senha" aria-label="Mostrar ou ocultar senha">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </div>
                            <span class="field-hint">
                                <i class="fa-solid fa-circle-info"></i> Mínimo de 8 caracteres
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-user-check"></i> Finalizar Cadastro
                        </button>
                    </form>

                    <div class="auth-footer">
                        <?php if ($isLoggedIn): ?>
                            <span><a href="home.php"><i class="fa-solid fa-house"></i> Voltar ao painel principal</a></span>
                        <?php else: ?>
                            <span>Já tem uma conta? <a href="./index.php"><i class="fa-solid fa-arrow-left"></i> Voltar ao login</a></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    <?php endif; ?>

    <script src="./js/index.js?v=<?= $cacheVersion ?>"></script>
</body>

</html>
