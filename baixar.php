<?php
/**
 * Download em PDF dos documentos do próprio usuário autenticado.
 *   baixar.php?doc=ID     -> documento emitido pela Secretaria Virtual
 *   baixar.php?boletim=1  -> boletim escolar
 */
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/documentos.php';

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$usuarioNome = $_SESSION['usuario'] ?? 'Aluno';

$stmt = $conn->prepare('SELECT email FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$stmt->bind_result($usuarioEmail);
$stmt->fetch();
$stmt->close();

$aluno = ['id' => $usuarioId, 'nome' => $usuarioNome, 'email' => (string) $usuarioEmail];

function slug_arquivo(string $texto): string
{
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
    $texto = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $texto));
    return trim($texto, '-') ?: 'documento';
}

if (isset($_GET['boletim'])) {
    $html = render_boletim_impressao($aluno, doc_notas($conn, $usuarioId));
    doc_enviar_pdf($html, 'Boletim Escolar', 'boletim-escolar-2026.pdf');
}

$docId = filter_input(INPUT_GET, 'doc', FILTER_VALIDATE_INT);
if ($docId) {
    $stmt = $conn->prepare('SELECT tipo_documento, protocolo, DATE_FORMAT(data_solicitacao, "%d/%m/%Y %H:%i") FROM solicitacoes_secretaria WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $docId, $usuarioId);
    $stmt->execute();
    $stmt->bind_result($tipo, $protocolo, $data);
    if ($stmt->fetch()) {
        $stmt->close();
        $html = render_documento($tipo, $aluno, doc_notas($conn, $usuarioId), $protocolo, $data);
        doc_enviar_pdf($html, $tipo, slug_arquivo($tipo) . '-' . strtolower($protocolo) . '.pdf');
    }
    $stmt->close();
}

render_error_view(404, 'Documento não encontrado', 'O documento solicitado não existe ou não pertence ao seu usuário.', 'secretaria.php');
