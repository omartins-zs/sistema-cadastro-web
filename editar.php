<?php

require_once('conexao.php');

$id = $_GET['codigo'];

$sql_consulta = mysqli_query($conn, " SELECT * FROM usuarios WHERE id='$id'");

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

            Usuário: <br>
            <input type="text" name="txt_usuario" value="<?php $dados[1] ?>"> <br>
            Senha: <br>
            <input type="password" name="txt_senha" id="" value="<?php $dados[2] ?>"> <br>
            <br>
            Email: <br>
            <input type="email" name="txt_email" id="" value="<?php $dados[3] ?>"> <br>
            <br>
            Nivel: <br>
            <select name="nivel" id="">
                <option value='<?php $dados[4] ?>'><?php $dados[4] ?></option>
                <option value="Prof">Professor</option>
                <option value="Aluno">Aluno</option>
            </select> <br>
            <br>


            <input type="submit" value="Atualizar"> <br>
            <br>
            <a href="./index.php">Logar</a>
        </form>
    </center>

</body>

</html>