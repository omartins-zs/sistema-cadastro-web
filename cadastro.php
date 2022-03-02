<?php

require_once('conexao.php');

$usuario = $_POST['txt_usuario'];
$senha = $_POST['txt_senha'];
$email = $_POST['txt_email'];
$nivel = $_POST['txt_nivel'];

$sql_cadastro = mysqli_query($conn, "INSERT INTO usuarios ( usuario, senha, email, nivel) VALUES ( '$usuario', '$senha', '$email', '$nivel')");