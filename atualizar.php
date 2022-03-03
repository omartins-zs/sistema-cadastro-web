<?php

require_once('conexao.php');

$id = $_POST['codigo'];

$usuario = $_POST['txt_usuario'];
$senha = $_POST['txt_senha'];
$email = $_POST['txt_email'];
$nivel = $_POST['nivel'];

$sql_atualizar = mysqli_query($conn, " UPDATE usuarios SET usuario='$usuario', senha='$senha', email='$email', nivel='$nivel' WHERE id='$id'");

if ($sql_atualizar == true) {
    echo "<script>
        alert('Dados do usuario atualizados com Sucesso!!');
        window.location.href='listarUsuarios.php';
    </script>";
} else {
    echo "<script>
        alert('Falha na edição do usuario');
        window.location.href='editar.php';
    </script>";
}