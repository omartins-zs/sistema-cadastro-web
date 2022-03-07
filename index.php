<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./css/style.css">

    <title>Acesso</title>
</head>

<body>

    <h1>TECNOLOGIA DA INFORMAÇÃO</h1>
    <hr>
    <h2>Tela de Login / Acesso</h2>


    <div class="login">
        <form action="login.php" method="post">
            <h3>Faça seu login</h3>

            Usuário: <br>
            <input type="text" name="txt_usuario"> <br>
            Senha: <br>
            <input type="password" name="txt_senha" id=""> <br>
            <br>
            <input type="submit" value="Entrar"> <br>
            <br>
            <a href="./cadastroUsuario.php">Cadastrar-se</a>
        </form>
    </div>
</body>

</html>