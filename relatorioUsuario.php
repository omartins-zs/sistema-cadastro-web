
<?php
// Conectando com o banco
require_once('conexao.php');

require 'vendor/autoload.php';
require_once 'dompdf/autoload.inc.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
// Gerando os codigos HTML
$dompdf->loadHtml('
<h1> Relatorio de teste </h1>
' . $HTML . '
');
$html = '<table border=1>';

$html .= '<thead>';
$html .= '<tr>';

$html .= '<th> Usuario </th>';
$html .= '<th> Email </th>';
$html .= '<th> Nivel </th>';

$html .= '</tr>';
$html .= '<thead>';

$lista = mysqli_query($conn, "SELECT * FROM usuarios");
$total = mysqli_num_rows($lista);

while ($dados = mysqli_fetch_array($lista)) {

    $html .= '<tbody>';
    $html .= '<tr>';
    $html .= '<td>' . $dados['1'] . "</td>";
    $html .= '<td>' . $dados['3'] . "</td>";
    $html .= '<td>' . $dados['4'] . "</td>";
    $html .= '</tr>';
    $html .= '</tbody>';
};

$html .= '</table>';



// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("relatorio-cadastro.pdf", array("Attachment" => false));


$dompdf->loadHtml($HTML);

