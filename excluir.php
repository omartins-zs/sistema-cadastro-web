<?php

include_once('conexao.php');

$id = $_GET['codigo'];

$sql_eliminar = mysqli_query($conn, "DELETE FROM usuarios WHERE id='$id' ");

if ($sql_eliminar == true) {
    echo "<script>
        alert('Usuario foi excluido com Sucesso!!');
        window.location.href='listarUsuarios.php';
    </script>";
} else {
    echo "<script>
        alert('Falha ao excluir usuario!');
        window.location.href='listarUsuarios.php';
    </script>";
}