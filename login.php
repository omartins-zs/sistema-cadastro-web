<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexao.php';

start_app_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    header('Location: index.php?erro=login');
    exit;
}

// Verificação de taxa de tentativas (Rate Limiting)
$agora = time();
$tempoBloqueio = (int) ($_SESSION['login_lockout_until'] ?? 0);
if ($tempoBloqueio > $agora) {
    $segundosRestantes = $tempoBloqueio - $agora;
    header('Location: index.php?erro=bloqueado&tempo=' . $segundosRestantes);
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
    // Incrementa contagem de falhas
    $tentativas = ((int) ($_SESSION['login_tentativas'] ?? 0)) + 1;
    $_SESSION['login_tentativas'] = $tentativas;

    if ($tentativas >= 5) {
        $_SESSION['login_lockout_until'] = time() + 30; // Bloqueio temporário de 30s
        $_SESSION['login_tentativas'] = 0;
        header('Location: index.php?erro=bloqueado&tempo=30');
        exit;
    }

    header('Location: index.php?erro=credenciais');
    exit;
}

// Login efetuado: zera contadores de bloqueio
unset($_SESSION['login_tentativas'], $_SESSION['login_lockout_until']);

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
