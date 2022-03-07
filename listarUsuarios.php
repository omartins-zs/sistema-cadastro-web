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

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Fontawesome 5 -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css"
        integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

    <title>Lista de usuarios</title>
</head>

<body>
    <center>

        <h1>Usuarios cadastrados no Sistema</h1>

        <hr>
        <table rules="all">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Senha</th>
                    <th>Email</th>
                    <th>Nivel</th>
                </tr>
            </thead>

            <tbody>
                <?php

                $sql_consulta = mysqli_query($conn, " SELECT * FROM usuarios ");

                $total_registros = mysqli_num_rows($sql_consulta);

                while ($dados = mysqli_fetch_array($sql_consulta)) {
                ?>

                <tr>
                    <td> <?= $dados[1] ?> </td>
                    <td> <?= $dados[2] ?> </td>
                    <td> <?= $dados[3] ?> </td>
                    <td> <?= $dados[4] ?> </td>
                    <td>
                        <a href="excluir.php?codigo=<?= $dados[0] ?>">Excluir</a>
                    </td>
                    <td>
                        <a href="editar.php?codigo=<?= $dados[0] ?>">Editar</a>
                    </td>
                </tr>

                <?php } ?>
                <tr>
                    <td colspan="6">Total: <?= $total_registros ?> </td>
                </tr>
            </tbody>
        </table>

        <hr>
        <a href="index.php">Voltar</a><br>
        <a href="relatorioUsuario.php">Imprimir</a>


    </center>


</body>

</html>