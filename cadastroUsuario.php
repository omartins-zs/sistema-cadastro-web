<?php
require_once __DIR__ . '/auth.php';
start_app_session();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>Cadastro de usuário</title>
</head>

<body>
    <main>
        <form class="login" action="cadastro.php" method="post">
            <h1>Cadastro de usuário</h1>
            <?php if (($_GET['erro'] ?? '') === 'cadastro'): ?>
                <p class="form-message" role="alert">Confira os dados. O usuário ou e-mail pode já estar cadastrado.</p>
            <?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

            <label for="txt_usuario">Usuário</label>
            <input id="txt_usuario" type="text" name="txt_usuario" maxlength="120" autocomplete="username" required>

            <label for="txt_senha">Senha (mínimo 8 caracteres)</label>
            <input id="txt_senha" type="password" name="txt_senha" minlength="8" autocomplete="new-password" required>

            <label for="txt_email">E-mail</label>
            <input id="txt_email" type="email" name="txt_email" maxlength="255" autocomplete="email" required>

            <button type="submit">Cadastrar</button>
            <p><a href="./index.php">Voltar ao login</a></p>
        </form>
    </main>
</body>

</html>
