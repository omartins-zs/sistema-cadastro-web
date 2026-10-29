<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_login();
require_once __DIR__ . '/conexao.php';

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$cacheVersion = time();

$msgSucesso = null;
$msgErro = null;

// Processa atualização dos próprios dados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        render_error_view(400, 'Solicitação Inválida', 'Token CSRF ausente ou expirado.', 'meusDados.php');
    }

    $novoEmail = trim($_POST['txt_email'] ?? '');
    $novaSenha = $_POST['txt_senha'] ?? '';

    if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
        $msgErro = 'Informe um endereço de e-mail válido.';
    } elseif ($novaSenha !== '' && strlen($novaSenha) < 8) {
        $msgErro = 'A nova senha deve possuir pelo menos 8 caracteres.';
    } else {
        try {
            if ($novaSenha !== '') {
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE usuarios SET email = ?, senha = ? WHERE id = ?');
                $stmt->bind_param('ssi', $novoEmail, $senhaHash, $usuarioId);
            } else {
                $stmt = $conn->prepare('UPDATE usuarios SET email = ? WHERE id = ?');
                $stmt->bind_param('si', $novoEmail, $usuarioId);
            }
            $stmt->execute();
            $stmt->close();
            $msgSucesso = 'Seus dados cadastrais foram atualizados com sucesso!';
        } catch (mysqli_sql_exception $e) {
            $msgErro = 'O e-mail informado já está em uso por outra conta no sistema.';
        }
    }
}

// Busca dados atuais do usuário
$stmt = $conn->prepare('SELECT usuario, email, nivel FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$stmt->bind_result($usuarioNome, $usuarioEmail, $usuarioNivel);
$stmt->fetch();
$stmt->close();

$primeiraLetra = strtoupper(mb_substr($usuarioNome, 0, 1, 'UTF-8'));
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
    <title>Meus Dados - Cadastro Web</title>
</head>

<body>
    <!-- Top Navigation Bar -->
    <?= layout_navbar('meusDados.php') ?>

    <div class="dashboard-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="home.php"><i class="fa-solid fa-house"></i> Início</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Meus Dados Cadastrais</span>
        </div>

        <?php if ($msgSucesso): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= escape_html($msgSucesso) ?></span>
            </div>
        <?php elseif ($msgErro): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= escape_html($msgErro) ?></span>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; align-items: start;">
            <!-- Card de Visão Geral do Perfil -->
            <div class="table-card">
                <div style="text-align: center; margin-bottom: 1.75rem;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #2563eb); display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 700; color: #fff; margin-bottom: 1rem; box-shadow: 0 0 25px rgba(56, 189, 248, 0.35);">
                        <?= escape_html($primeiraLetra) ?>
                    </div>
                    <h2 style="font-size: 1.45rem; color: #fff; margin-bottom: 0.25rem;"><?= escape_html($usuarioNome) ?></h2>
                    <span class="role-pill role-<?= strtolower($usuarioNivel) ?>">
                        Perfil <?= escape_html($usuarioNivel) ?>
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.92rem; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-id-card"></i> Identificador</span>
                        <strong style="color: #38bdf8;">#<?= (int) $usuarioId ?></strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-envelope"></i> E-mail Cadastrado</span>
                        <strong><?= escape_html($usuarioEmail) ?></strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-circle-check"></i> Status da Conta</span>
                        <span style="color: #34d399; font-weight: 600;"><i class="fa-solid fa-circle" style="font-size: 0.55rem; vertical-align: middle; margin-right: 4px;"></i> Conta Ativa</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-shield"></i> Autenticação</span>
                        <span style="color: var(--text-muted);">Protegido por Criptografia</span>
                    </div>
                </div>
            </div>

            <!-- Card de Formulário de Edição -->
            <div class="table-card">
                <div class="table-title" style="margin-bottom: 1.5rem;">
                    <h2><i class="fa-solid fa-user-pen" style="color: #38bdf8; margin-right: 8px;"></i> Atualizar Informações</h2>
                    <span>Edite seu e-mail de contato ou altere sua senha de acesso</span>
                </div>

                <form action="meusDados.php" method="post" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">

                    <div class="form-group">
                        <label class="form-label" for="txt_usuario">Nome de Usuário</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon-left"></i>
                            <input id="txt_usuario" class="form-control" type="text" value="<?= escape_html($usuarioNome) ?>" disabled style="opacity: 0.65; cursor: not-allowed;">
                        </div>
                        <span class="field-hint">O nome de usuário não pode ser alterado por este formulário.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="txt_email">Endereço de E-mail</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input id="txt_email" class="form-control" type="email" name="txt_email" value="<?= escape_html($usuarioEmail) ?>" maxlength="255" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="txt_senha">Nova Senha (opcional)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon-left"></i>
                            <input id="txt_senha" class="form-control" type="password" name="txt_senha" minlength="8" placeholder="Preencha apenas para alterar a senha" autocomplete="new-password">
                            <span role="button" class="btn-toggle-password" onclick="togglePasswordVisibility('txt_senha', this)" title="Mostrar ou ocultar senha" aria-label="Mostrar ou ocultar senha">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        <span class="field-hint">
                            <i class="fa-solid fa-circle-info"></i> Deixe o campo em branco para manter a senha atual.
                        </span>
                    </div>

                    <div class="form-actions-row">
                        <a href="home.php" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
                        </a>
                        <button type="submit" class="btn btn-primary" style="margin-top: 0;">
                            <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="./js/index.js?v=<?= $cacheVersion ?>"></script>
</body>

</html>

