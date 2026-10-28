<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

$id = filter_input(INPUT_GET, 'codigo', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Identificador de usuário inválido.');
}

$stmt = $conn->prepare('SELECT id, usuario, email, nivel FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($usuarioId, $usuario, $email, $nivel);
$encontrado = $stmt->fetch();
$stmt->close();

if (!$encontrado) {
    http_response_code(404);
    exit('Usuário não encontrado.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>Editar usuário</title>
</head>

<body>
    <main>
        <form class="login" action="atualizar.php" method="post">
            <h1>Editar usuário</h1>
            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
            <input type="hidden" name="codigo" value="<?= (int) $usuarioId ?>">

            <label for="txt_usuario">Usuário</label>
            <input id="txt_usuario" type="text" name="txt_usuario" maxlength="120" value="<?= escape_html($usuario) ?>" required>

            <label for="txt_senha">Nova senha (opcional)</label>
            <input id="txt_senha" type="password" name="txt_senha" minlength="8" autocomplete="new-password">
            <small>Deixe em branco para manter a senha atual.</small>

            <label for="txt_email">E-mail</label>
            <input id="txt_email" type="email" name="txt_email" maxlength="255" value="<?= escape_html($email) ?>" required>

            <label for="nivel">Perfil</label>
            <select id="nivel" name="nivel" required>
                <?php foreach (['Admin', 'Prof', 'Aluno'] as $perfil): ?>
                    <option value="<?= escape_html($perfil) ?>" <?= $nivel === $perfil ? 'selected' : '' ?>><?= escape_html($perfil) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Salvar alterações</button>
            <p><a href="listarUsuarios.php">Voltar à lista</a></p>
        </form>
    </main>
</body>

</html>
