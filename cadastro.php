<?php

require_once('conexao.php');

$usuario = $_POST['txt_usuario'];
$senha = $_POST['txt_senha'];
$email = $_POST['txt_email'];
$nivel = $_POST['nivel'];

$sql_cadastro = mysqli_query($conn, "INSERT INTO usuarios ( usuario, senha, email, nivel) VALUES ( '$usuario', '$senha', '$email', '$nivel')");

if ($sql_cadastro == true) {
    echo "<script>
        alert('Usuario cadastrado com Sucesso!!');
        window.location.href='index.php';
    </script>";
} else {
    echo "<script>
        alert('Falha no cadastro de usuario!');
        window.location.href='cadastroUsuario.php';
    </script>";
}