<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexao.php';

start_app_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou requisição inválida. Volte ao formulário e tente novamente.', 'cadastroUsuario.php');
}

$usuario = trim($_POST['txt_usuario'] ?? '');
$senha = $_POST['txt_senha'] ?? '';
$email = trim($_POST['txt_email'] ?? '');

$isAdmin = (($_SESSION['nivel'] ?? '') === 'Admin');

// Se for administrador, permite definir o perfil (Admin, Prof, Aluno). Caso contrário, perfil padrão Aluno.
if ($isAdmin && isset($_POST['nivel']) && in_array($_POST['nivel'], ['Admin', 'Prof', 'Aluno'], true)) {
    $nivel = $_POST['nivel'];
} else {
    $nivel = 'Aluno';
}

if ($usuario === '' || strlen($senha) < 8 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cadastroUsuario.php?erro=cadastro');
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

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

// Se quem realizou o cadastro foi o Administrador autenticado, redireciona para a home mantendo a sessão!
if ($isAdmin) {
    header('Location: home.php?cadastrado=1');
    exit;
}

// Se for auto-cadastro público de visitante, redireciona para a tela de login
header('Location: index.php?cadastro=sucesso');
exit;
