<?php

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Solicitação inválida.');
}

$id = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);

if (!$id || $id === (int) $_SESSION['user_id']) {
    http_response_code(400);
    exit('Não é possível excluir este usuário.');
}

$stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: listarUsuarios.php?excluido=1');
exit;
