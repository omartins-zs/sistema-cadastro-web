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
    <br>
    <div class="container">

        <h2 class="text-center">
            Usuarios cadastrados no Sistema <i class="fa fa-users"></i>
        </h2>

        <hr>
        <div class="table-responsive">
            <table class="table table-hover table-bordered border-primary">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Senha</th>
                        <th>Email</th>
                        <th>Nivel</th>
                        <th colspan="2">Açoes</th>
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
                            <a class="btn btn-danger btn-xs" href="excluir.php?codigo=<?= $dados[0] ?>"><i
                                    class="fa fa-trash"></i> Excluir</a>
                        </td>
                        <td>
                            <a class="btn btn-warning btn-xs" href="editar.php?codigo=<?= $dados[0] ?>"><i
                                    class="fa fa-user-edit"></i> Editar</a>
                        </td>
                    </tr>

                    <?php } ?>
                    <tr>
                        <td colspan="6">Total: <?= $total_registros ?> </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <hr>

        <div class="btn-page">

            <a class="btn btn-secondary" href="index.php">
                <i class="far fa-arrow-alt-circle-left"></i>
                </i> Voltar
            </a>

            <a class="btn btn-info" href="relatorioUsuario.php">
                <i class="fas fa-print"></i> Imprimir
            </a>
        </div>

    </div>

</body>

</html>