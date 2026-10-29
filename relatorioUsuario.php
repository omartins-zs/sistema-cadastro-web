<?php

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/vendor/autoload.php';

$lista = mysqli_query($conn, 'SELECT usuario, email, nivel FROM usuarios ORDER BY usuario');
$total = $lista ? mysqli_num_rows($lista) : 0;
$dataHora = date('d/m/Y H:i');

$tableRows = '';
while ($dados = mysqli_fetch_assoc($lista)) {
    $tableRows .= '<tr>';
    $tableRows .= '<td>' . escape_html($dados['usuario']) . '</td>';
    $tableRows .= '<td>' . escape_html($dados['email']) . '</td>';
    $tableRows .= '<td>' . escape_html($dados['nivel']) . '</td>';
    $tableRows .= '</tr>';
}

$html = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Usuários</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #334155;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0f172a;
            font-size: 20px;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #64748b;
            font-size: 11px;
            margin: 0;
        }
        .meta-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
            color: #475569;
        }
        .meta-left {
            display: table-cell;
            text-align: left;
        }
        .meta-right {
            display: table-cell;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 10px;
            font-size: 11px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema de Cadastro Web</h1>
        <p>Relatório Analítico de Usuários Cadastrados</p>
    </div>

    <div class="meta-info">
        <div class="meta-left">
            <strong>Emissão:</strong> ' . $dataHora . '
        </div>
        <div class="meta-right">
            <strong>Total de Registros:</strong> ' . $total . '
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35%;">Usuário</th>
                <th style="width: 45%;">E-mail</th>
                <th style="width: 20%;">Perfil</th>
            </tr>
        </thead>
        <tbody>
            ' . $tableRows . '
        </tbody>
    </table>

    <div class="footer">
        Documento gerado automaticamente pelo Sistema de Cadastro Web.
    </div>
</body>
</html>
';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('Lista de Usuários.pdf', ['Attachment' => false]);
