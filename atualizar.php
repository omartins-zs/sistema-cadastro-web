<?php

require_once('conexao.php');

$id = $_POST['codigo'];

$usuario = $_POST['txt_usuario'];
$senha = $_POST['txt_senha'];
$email = $_POST['txt_email'];
$nivel = $_POST['nivel'];

$sql_atualizar = mysqli_query($conn, "UPDATE usuarios set usuario='$usuario', senha='$senha', email='$email', nivel='$nivel' WHERE id='$id'");