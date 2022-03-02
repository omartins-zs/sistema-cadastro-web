<?php

include_once('conexao.php');

$usuario = $_POST['txt_usuario'];
$senha = $_POST['txt_senha'];

$sql_logar = mysqli_query($conn, " SELECT * FROM usuarios WHERE usuario='$usuario' and '$senha'");

if (mysqli_num_rows($sql_logar) != 0) {
    header('Location: home.php');
} else {

    echo "<script>
        alert('Usuario nao cadastrado no sistema');
        window.location.href='index.php';
    </script>";
}