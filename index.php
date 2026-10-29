<?php
require_once __DIR__ . '/auth.php';
start_app_session();
$cacheVersion = time();

$mensagemLogin = null;
$tipoMensagem = 'danger';

if (($_GET['erro'] ?? '') === 'credenciais') {
    $mensagemLogin = 'Usuário ou senha inválidos.';
    $tipoMensagem = 'danger';
} elseif (($_GET['erro'] ?? '') === 'bloqueado') {
    $segundos = max(1, (int) ($_GET['tempo'] ?? 30));
    $mensagemLogin = "Muitas tentativas incorretas. Aguarde {$segundos} segundos para tentar novamente.";
    $tipoMensagem = 'danger';
} elseif (($_GET['erro'] ?? '') === 'login') {
    $mensagemLogin = 'Faça login para acessar o sistema.';
    $tipoMensagem = 'danger';
} elseif (($_GET['cadastro'] ?? '') === 'sucesso') {
    $mensagemLogin = 'Cadastro concluído com sucesso! Digite seus dados para entrar.';
    $tipoMensagem = 'success';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="./css/app.css?v=<?= $cacheVersion ?>">
    <title>Acesso ao Sistema - Cadastro Web</title>
</head>

<body>
    <main>
        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h1>Acesso ao Sistema</h1>
                    <p>Informe suas credenciais para continuar</p>
                </div>

                <?php if ($mensagemLogin !== null): ?>
                    <div class="alert alert-<?= $tipoMensagem ?>" role="alert">
                        <i class="fa-solid <?= $tipoMensagem === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                        <span><?= escape_html($mensagemLogin) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" action="login.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                    <div class="form-group">
                        <label class="form-label" for="txt_usuario">Nome de Usuário</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon-left"></i>
                            <input type="text" id="txt_usuario" class="form-control" placeholder="Seu nome de usuário" name="txt_usuario" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="field-password">Senha de Acesso</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon-left"></i>
                            <input type="password" id="field-password" name="txt_senha" class="form-control" placeholder="Sua senha de acesso" autocomplete="current-password" required>
                            <span role="button" class="btn-toggle-password" onclick="togglePasswordVisibility('field-password', this)" title="Mostrar ou ocultar senha" aria-label="Mostrar ou ocultar senha">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-right-to-bracket"></i> Entrar
                    </button>
                </form>

                <div class="auth-footer">
                    <span>Ainda não tem uma conta? <a href="./cadastroUsuario.php">Cadastre-se <i class="fa-solid fa-arrow-right"></i></a></span>
                </div>
            </div>
        </div>
    </main>

    <script src="./js/index.js?v=<?= $cacheVersion ?>"></script>
</body>

</html>
