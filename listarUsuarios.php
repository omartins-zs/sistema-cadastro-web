<?php

include_once('conexao.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./css/style.css">

    <title>Lista de usuarios</title>
</head>

<body>
    <center>

        <h1>Usuarios cadastrados no Sistema</h1>

        <hr>
        <table rules="all">
            <thead>
                <tr>
                    <th>Usuarios</th>
                    <th>Senha</th>
                    <th>Email</th>
                    <th>Nivel</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $sql_consulta = mysqli_query($conn, "SELECT * FROM usuarios");



</body>

</html>