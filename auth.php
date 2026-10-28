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

function require_admin(): void
{
    require_login();

    if (($_SESSION['nivel'] ?? '') !== 'Admin') {
        http_response_code(403);
        exit('Acesso permitido somente para administradores.');
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
