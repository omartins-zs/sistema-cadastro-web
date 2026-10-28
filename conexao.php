<?php

require_once __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

$environmentValue = static function (string $key, string $default): string {
    $processValue = getenv($key);
    $value = $processValue !== false ? $processValue : ($_ENV[$key] ?? $_SERVER[$key] ?? null);
    return $value === false || $value === null || $value === '' ? $default : (string) $value;
};

$dbHost = $environmentValue('DB_HOST', '127.0.0.1');
$dbPort = (int) $environmentValue('DB_PORT', '3307');
$dbUser = $environmentValue('DB_USER', 'root');
$dbPassword = $environmentValue('DB_PASSWORD', '');
$dbName = $environmentValue('DB_NAME', 'escola');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Verifique o arquivo .env e confirme que o MySQL está ativo.');
}
