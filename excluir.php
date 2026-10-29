<?php

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou requisição inválida.', 'listarUsuarios.php');
}

$id = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);

if (!$id || $id === (int) $_SESSION['user_id']) {
    render_error_view(400, 'Operação Não Permitida', 'Não é possível excluir a sua própria conta ativa.', 'listarUsuarios.php');
}

$stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: listarUsuarios.php?excluido=1');
exit;
