
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" href="./css/style.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://kit.fontawesome.com/cba92a6c1e.js" crossorigin="anonymous" defer></script>

    <title>Acesso</title>
</head>

<body>

    <header>
        <h2>Tela de Login / Acesso</h2>
    </header>

    <main>
    <form action="login.php" method="post">
            <section class="inputs-container">
                <label for="txt_usuario">Usuario</label>
                <input type="text" placeholder="jorginho" name="txt_usuario"> <br>

                <div class="password-container">

                    <input type="password" id="field-password" name="txt_senha" class="field-password" placeholder="*******">
                    <i class="fas fa-eye" id="eye" onclick="showPassword()"></i>
                    <i class="fas fa-eye-slash" id="eye-slash" onclick="showPassword()"></i>
                </div>
            </section>

            <section class="password-infos">
                <div>
                    <input type="checkbox">
                    <span>Lembrar senha?</span>
                </div>

                <a href="#">Esqueceu sua senha?</a>
            </section>

            <button type="submit" value="Entrar" id="btn-login">Login</button>

    <section class="links-container">
                <span>Ou entre com:</span>

                <aside>
                    <a href="#"><i class="fab fa-google-plus-square google"></i></a>
                    <a href="#"><i class="fab fa-linkedin linkedin"></i></a>
                    <a href="#"><i class="fab fa-facebook-square facebook"></i></a>
                </aside>
            </section>
    <footer>
                <hr>
                <br>
                <br>
                <span>Ainda não tem uma conta? <a href="./cadastroUsuario.php">Cadastrar-se</a></span>
            </footer>
    </form>
    </main>
        <!-- Js -->
    <script src="./js/index.js"></script>
</body>

</html>
