<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_admin();
require_once __DIR__ . '/conexao.php';

$adminNome = $_SESSION['usuario'] ?? 'Admin';
$adminNivel = $_SESSION['nivel'] ?? 'Admin';
$primeiraLetra = strtoupper(mb_substr($adminNome, 0, 1, 'UTF-8'));
$cacheVersion = time();

$id = filter_input(INPUT_GET, 'codigo', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Identificador de usuário inválido.');
}

$stmt = $conn->prepare('SELECT id, usuario, email, nivel FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($usuarioId, $usuario, $email, $nivel);
$encontrado = $stmt->fetch();
$stmt->close();

if (!$encontrado) {
    http_response_code(404);
    exit('Usuário não encontrado.');
}

$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="./css/app.css?v=<?= $cacheVersion ?>">
    <title>Editar Usuário - Cadastro Web</title>
</head>

<body>
    <!-- Top Navigation Bar -->
    <?= layout_navbar('listarUsuarios.php') ?>

    <div class="edit-page-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <a href="listarUsuarios.php">Gerenciar Usuários</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Editar Usuário #<?= (int) $usuarioId ?></span>
        </div>

        <div class="edit-card">
            <div class="auth-header" style="margin-bottom: 2rem;">
                <div class="auth-badge">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <h1>Editar Usuário</h1>
                <p>Atualize as informações cadastrais e o nível de acesso</p>
            </div>

            <?php if ($erro === 'validacao'): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Dados inválidos. Verifique o formato do e-mail, perfil ou se a nova senha possui pelo menos 8 caracteres.</span>
                </div>
            <?php elseif ($erro === 'atualizacao'): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Falha ao atualizar o usuário. O e-mail informado já pode estar em uso por outra conta.</span>
                </div>
            <?php endif; ?>

            <form class="auth-form" action="atualizar.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                <input type="hidden" name="codigo" value="<?= (int) $usuarioId ?>">

                <div class="form-group">
                    <label class="form-label" for="txt_usuario">Nome de Usuário</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user input-icon-left"></i>
                        <input id="txt_usuario" class="form-control" type="text" name="txt_usuario" maxlength="120" value="<?= escape_html($usuario) ?>" autocomplete="username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="txt_email">Endereço de E-mail</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon-left"></i>
                        <input id="txt_email" class="form-control" type="email" name="txt_email" maxlength="255" value="<?= escape_html($email) ?>" autocomplete="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="txt_senha">Nova Senha (opcional)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon-left"></i>
                        <input id="txt_senha" class="form-control" type="password" name="txt_senha" minlength="8" placeholder="Digite apenas para alterar a senha atual" autocomplete="new-password">
                        <span role="button" class="btn-toggle-password" onclick="togglePasswordVisibility('txt_senha', this)" title="Mostrar ou ocultar senha" aria-label="Mostrar ou ocultar senha">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                    <span class="field-hint">
                        <i class="fa-solid fa-circle-info"></i> Deixe o campo em branco para manter a senha atual.
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nivel">Perfil de Acesso</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-id-badge input-icon-left"></i>
                        <select id="nivel" name="nivel" class="form-control" required>
                            <?php foreach (['Admin', 'Prof', 'Aluno'] as $perfil): ?>
                                <option value="<?= escape_html($perfil) ?>" <?= $nivel === $perfil ? 'selected' : '' ?>><?= escape_html($perfil) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions-row">
                    <a href="listarUsuarios.php" class="btn btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0;">
                        <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/index.js?v=<?= $cacheVersion ?>"></script>
</body>

</html>
