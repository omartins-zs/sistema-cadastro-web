<?php

use Dompdf\Dompdf;
// Criando um 
$dompdf = new Dompdf();
// Conectando com o banco
require_once('conexao.php');

$HTML = '<table border=1>';

$HTML .= '<thead>';
$HTML .= '<tr>';

$HTML .= '<th> Usuario </th>';
$HTML .= '<th> Email </th>';
$HTML .= '<th> Nivel </th>';

$HTML .= '</tr>';
$HTML .= '<thead>';

$lista = mysqli_query($conn, "SELECT * FROM usuarios");
$total = mysqli_num_rows($lista);

while ($dados = mysqli_fetch_array($lista)) {

    $HTML .= '<tbody>';
    $HTML .= '<tr>';
    $HTML .= '<td>' . $dados['1'] . "</td>";
    $HTML .= '<td>' . $dados['3'] . "</td>";
    $HTML .= '<td>' . $dados['4'] . "</td>";
    $HTML .= '</tr>';
    $HTML .= '</tbody>';
};

$HTML .= '</table>';

// Incluindo o documento dompdf
// include autoloader
require_once 'dompdf/autoload.inc.php';


// Gerando os codigos HTML
$dompdf->loadHtml('

<h1> Relatorio de teste </h1>
' . $HTML . '

');

// escolhendo a configuração do papel a ser gerado o pdf
$dompdf->setPaper("A4", "portrait");

// Renderizando HTML
$dompdf->render();

// Exibir os dados html ja renderizados
// informando como será a saída do arquivo pdf
$dompdf->stream("relatorio.pdf", array("Attachment" => false));


$dompdf->loadHtml($html);