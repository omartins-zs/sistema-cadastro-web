<?php

include_once('conexao.php');

$id = $_GET['codigo'];

$sql_eliminar = mysqli_query($conn, "DELETE FROM usuarios WHERE id='$id' ");