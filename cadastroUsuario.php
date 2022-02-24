<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./css/style.css">

    <title>Cadastro</title>
</head>

<body>
    <center>


        <h1>TECNOLOGIA DA INFORMAÇÃO</h1>
        <hr>
        <h2>Tela de Cadastro de Usuarios / Acesso</h2>

        <hr>
        <form class="login" action="cadastro.php" method="post">

            Usuário: <br>
            <input type="text" name="txt_usuario"> <br>
            Senha: <br>
            <input type="password" name="txt_senha" id=""> <br>
            <br>
            Email: <br>
            <input type="email" name="txt_email" id=""> <br>
            <br>
            Nivel: <br>
            <select name="nivel" id="">
                <option value="Admin">Administrador</option>
                <option value="Prof">Professor</option>
                <option value="Aluno">Aluno</option>
            </select> <br>
            <br>


            <input type="submit" value="Cadastrar"> <br>
            <br>
            <a href="./index.php">Logar</a>
        </form>
    </center>

</body>

</html>