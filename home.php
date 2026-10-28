<?php
require_once __DIR__ . '/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">


    <title>Home</title>
</head>

<body>
    <center>

        <h1>Bem-vindo, <?= escape_html($_SESSION['usuario'] ?? '') ?></h1>

    </center>
    <div class="flex-box container-box">
  <div class="content-box">
  <div class="btn-list">

        <?php if (($_SESSION['nivel'] ?? '') === 'Admin'): ?>
        <a class="btn btn-info" href="listarUsuarios.php">Gerenciar usuários</a>
        <?php endif; ?>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
            <button class="btn btn-secondary" type="submit">Sair</button>
        </form>

    </div>
  </div>
</div>
  
    
    

</body>

</html>
