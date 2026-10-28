<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Solicitação inválida. Volte ao formulário e tente novamente.');
}

$usuario = trim($_POST['txt_usuario'] ?? '');
$senha = $_POST['txt_senha'] ?? '';
$email = trim($_POST['txt_email'] ?? '');

if ($usuario === '' || strlen($senha) < 8 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cadastroUsuario.php?erro=cadastro');
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$nivel = 'Aluno';

try {
    $stmt = $conn->prepare('INSERT INTO usuarios (usuario, senha, email, nivel) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $usuario, $senhaHash, $email, $nivel);
    $stmt->execute();
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log('User registration failed: ' . $exception->getMessage());
    header('Location: cadastroUsuario.php?erro=cadastro');
    exit;
}

header('Location: index.php?cadastro=sucesso');
exit;
