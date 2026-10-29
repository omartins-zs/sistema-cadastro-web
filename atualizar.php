<?php

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou requisição inválida.', 'listarUsuarios.php');
}

$id = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
$usuario = trim($_POST['txt_usuario'] ?? '');
$senha = $_POST['txt_senha'] ?? '';
$email = trim($_POST['txt_email'] ?? '');
$nivel = $_POST['nivel'] ?? '';

if (!$id || $usuario === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !in_array($nivel, ['Admin', 'Prof', 'Aluno'], true)
    || ($senha !== '' && strlen($senha) < 8)) {
    header('Location: editar.php?codigo=' . (int) $id . '&erro=validacao');
    exit;
}

try {
    if ($senha !== '') {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE usuarios SET usuario = ?, senha = ?, email = ?, nivel = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $usuario, $senhaHash, $email, $nivel, $id);
    } else {
        $stmt = $conn->prepare('UPDATE usuarios SET usuario = ?, email = ?, nivel = ? WHERE id = ?');
        $stmt->bind_param('sssi', $usuario, $email, $nivel, $id);
    }

    $stmt->execute();
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log('User update failed: ' . $exception->getMessage());
    header('Location: editar.php?codigo=' . $id . '&erro=atualizacao');
    exit;
}

header('Location: listarUsuarios.php?atualizado=1');
exit;
