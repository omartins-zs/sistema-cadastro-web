<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/conexao.php';

$usuarios = $conn->query('SELECT id, usuario, email, nivel FROM usuarios ORDER BY usuario');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>Usuários</title>
</head>

<body>
    <main>
        <div class="container py-4">
            <h1 class="text-center">Usuários cadastrados</h1>
            <div class="table-responsive">
                <table class="table table-hover table-bordered bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                        <tr>
                            <td><?= escape_html($usuario['usuario']) ?></td>
                            <td><?= escape_html($usuario['email']) ?></td>
                            <td><?= escape_html($usuario['nivel']) ?></td>
                            <td class="d-flex gap-2">
                                <a class="btn btn-warning btn-sm" href="editar.php?codigo=<?= (int) $usuario['id'] ?>">Editar</a>
                                <form action="excluir.php" method="post" onsubmit="return confirm('Excluir este usuário?')">
                                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                                    <input type="hidden" name="codigo" value="<?= (int) $usuario['id'] ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <p><?= $usuarios->num_rows ?> usuário(s)</p>
            <a class="btn btn-secondary" href="home.php">Voltar</a>
            <a class="btn btn-info" href="relatorioUsuario.php">Baixar relatório PDF</a>
        </div>
    </main>
</body>

</html>
