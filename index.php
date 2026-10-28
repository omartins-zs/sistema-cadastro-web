<?php
require_once __DIR__ . '/auth.php';
start_app_session();

$mensagemLogin = null;
if (($_GET['erro'] ?? '') === 'credenciais') {
    $mensagemLogin = 'Usuário ou senha inválidos.';
} elseif (($_GET['erro'] ?? '') === 'login') {
    $mensagemLogin = 'Faça login para continuar.';
} elseif (($_GET['cadastro'] ?? '') === 'sucesso') {
    $mensagemLogin = 'Cadastro concluído. Entre com seu usuário e senha.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" href="./css/style.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://kit.fontawesome.com/cba92a6c1e.js" crossorigin="anonymous" defer></script>

    <title>Acesso</title>
</head>

<body>

    <header>
        <h2>Tela de Login / Acesso</h2>
    </header>

    <main>
        <form action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
            <?php if ($mensagemLogin !== null): ?>
                <p class="form-message" role="status"><?= escape_html($mensagemLogin) ?></p>
            <?php endif; ?>
            <section class="inputs-container">
                <label for="txt_usuario">Usuário</label>
                <input type="text" id="txt_usuario" placeholder="Seu usuário" name="txt_usuario" autocomplete="username" required> <br>

                <div class="password-container">

                    <input type="password" id="field-password" name="txt_senha" class="field-password" placeholder="Sua senha" autocomplete="current-password" required>
                    <i class="fas fa-eye" id="eye" onclick="showPassword()"></i>
                    <i class="fas fa-eye-slash" id="eye-slash" onclick="showPassword()"></i>
                </div>
            </section>

            <button type="submit" value="Entrar">Login</button>
            <footer>
                <hr>
                <span>Ainda não tem uma conta? <a href="./cadastroUsuario.php">Cadastrar-se</a></span>
            </footer>
    </form>
    </main>
        <!-- Js -->
    <script src="./js/index.js"></script>
</body>

</html>
