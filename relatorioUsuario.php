<?php

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

$HTML = '<table align="center" border=1>';
$HTML .= '<thead>';

$HTML .= '<tr>';

$HTML .= '<th> USUÁRIO </th>';
$HTML .= '<th> E-MAIL </th>';
$HTML .= '<th> NÍVEL </th>';

$HTML .= '</tr>';

$HTML .= '</thead>';

$lista = mysqli_query($conn, 'SELECT usuario, email, nivel FROM usuarios ORDER BY usuario');
$total = mysqli_num_rows($lista);

while ($dados = mysqli_fetch_assoc($lista)) {

    $HTML .= '<tbody>';

    $HTML .= '<tr>';
    $HTML .= '<td>' . escape_html($dados['usuario']) . '</td>';
    $HTML .= '<td>' . escape_html($dados['email']) . '</td>';
    $HTML .= '<td>' . escape_html($dados['nivel']) . '</td>';
    $HTML .= '</tr>';

    $HTML .= '</tbody>';
}

$HTML .= '</table>';

use Dompdf\Dompdf;

require_once __DIR__ . '/vendor/autoload.php';
$dompdf = new Dompdf();

$dompdf->loadHtml('
<h1 align="center">RELATORIO DE TESTE</h1>
' . $HTML . '
');

$dompdf->render();
$dompdf->stream('Lista de Usuários.pdf', ['Attachment' => false]);
