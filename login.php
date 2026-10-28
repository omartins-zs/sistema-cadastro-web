<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexao.php';

start_app_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    header('Location: index.php?erro=login');
    exit;
}

$usuario = trim($_POST['txt_usuario'] ?? '');
$senha = $_POST['txt_senha'] ?? '';

if ($usuario === '' || $senha === '') {
    header('Location: index.php?erro=credenciais');
    exit;
}

$stmt = $conn->prepare('SELECT id, usuario, senha, nivel FROM usuarios WHERE usuario = ? LIMIT 1');
$stmt->bind_param('s', $usuario);
$stmt->execute();
$stmt->bind_result($id, $nome, $senhaArmazenada, $nivel);
$usuarioEncontrado = $stmt->fetch();
$stmt->close();

$senhaValida = false;
$senhaLegada = false;

if ($usuarioEncontrado) {
    $senhaValida = password_verify($senha, $senhaArmazenada);

    if (!$senhaValida && hash_equals($senhaArmazenada, $senha)) {
        // Migra senhas antigas em texto puro no primeiro login bem-sucedido.
        $senhaValida = true;
        $senhaLegada = true;
    }
}

if (!$senhaValida) {
    header('Location: index.php?erro=credenciais');
    exit;
}

if ($senhaLegada) {
    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
    $update = $conn->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
    $update->bind_param('si', $novoHash, $id);
    $update->execute();
    $update->close();
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $id;
$_SESSION['usuario'] = $nome;
$_SESSION['nivel'] = $nivel;

header('Location: home.php');
exit;
