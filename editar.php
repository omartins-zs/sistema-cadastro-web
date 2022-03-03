<?php

require_once('conexao.php');

$id = $_GET['codigo'];

$sql_consulta = mysqli_query($conn, "SELECT *FROM usuarios where id='$id' ");

$dados = mysqli_fetch_array($sql_consulta);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./css/style.css">

    <title>Edição</title>
</head>

<body>
    <center>


        <h1>TECNOLOGIA DA INFORMAÇÃO</h1>
        <hr>
        <h2>Edição dos dados</h2>

        <hr>
        <form class="login" action="atualizar.php" method="post">

            <input type="hidden" name="codigo" value="<?= $dados[0] ?>">

            <label for="nome">Nome:</label>
            <br>
            <input type="text" name="txt_usuario" value="<?= $dados[1] ?>">
            <br>
            <label for="senha">Senha:</label>
            <br>
            <input type="password" name="txt_senha" value="<?= $dados[2] ?>">
            <br>
            <label for="mail">E-mail:</label>
            <br>
            <input type="email" name="txt_email" value="<?= $dados[3] ?>">
            <br>
            <br>
            <label for="text">Nivel:</label>
            <select name="nivel" id="">
                <option <?= $dados[4] ?>><?= $dados[4] ?></option>
                <option value="Prof">Professor</option>
                <option value="Aluno">Aluno</option>
            </select>
            <br> <br>

            <input type="submit" value="Atualizar">
            <br>

        </form>
    </center>

</body>

</html>