<?php

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('cadastro_web_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function require_login(): void
{
    start_app_session();

    if (empty($_SESSION['user_id'])) {
        header('Location: index.php?erro=login');
        exit;
    }
}

function render_error_view(int $statusCode, string $title, string $message, string $backUrl = 'home.php'): void
{
    http_response_code($statusCode);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <link rel="stylesheet" href="./css/app.css?v=<?= time() ?>">
        <title><?= escape_html($title) ?> - Cadastro Web</title>
    </head>
    <body>
        <main>
            <div class="auth-wrapper">
                <div class="auth-card" style="text-align: center;">
                    <div class="auth-badge" style="background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.35); color: #ef4444;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?= escape_html($title) ?></h1>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;"><?= escape_html($message) ?></p>
                    <a href="<?= escape_html($backUrl) ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Retornar
                    </a>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function require_admin(): void
{
    require_login();

    if (($_SESSION['nivel'] ?? '') !== 'Admin') {
        render_error_view(403, 'Acesso Não Autorizado', 'Esta área é restrita a administradores do sistema.', 'home.php');
    }
}

function csrf_token(): string
{
    start_app_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(mixed $token): bool
{
    start_app_session();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function escape_html(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
