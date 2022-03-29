
<?php

include_once('conexao.php');

$HTML = '<table align="center" border=1>';
$HTML .= '<thead>';

$HTML .= '<tr>';

$HTML .= '<th> USUÁRIO </th>';
$HTML .= '<th> SENHA </th>';
$HTML .= '<th> E-MAIL </th>';
$HTML .= '<th> NÍVEL </th>';

$HTML .= '</tr>';

$HTML .= '</thead>';

$lista = mysqli_query($conn, "SELECT * FROM usuarios");
$total = mysqli_num_rows($lista);

while ($dados = mysqli_fetch_array($lista)) {

    $HTML .= '<tbody>';

    $HTML .= '<tr>';
    $HTML .= '<td>' . $dados['1'] . "</td>";
    $HTML .= '<td>' . $dados['2'] . "</td>";
    $HTML .= '<td>' . $dados['3'] . "</td>";
    $HTML .= '<td>' . $dados['4'] . "</td>";
    $HTML .= '</tr>';

    $HTML .= '</tbody>';
}

$HTML .= '</table>';

use Dompdf\Dompdf;

require_once 'dompdf/autoload.inc.php';
$dompdf = new DOMPDF();

$dompdf->load_html('
<h1 align="center">RELATORIO DE TESTE</h1>
' . $HTML . '
');

$dompdf->render();
$dompdf->stream(

    "Lista de Usuários !!!",
    array(
        "Attachment" => FALSE
    )

);

?>