<?php
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    fwrite(STDERR, ".env file not found\n");
    exit(1);
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($k, $v) = explode('=', $line, 2);
    $v = trim($v);
    // remove surrounding quotes
    if ((substr($v,0,1) === '"' && substr($v,-1) === '"') || (substr($v,0,1) === "'" && substr($v,-1) === "'")) {
        $v = substr($v,1,-1);
    }
    $env[trim($k)] = $v;
}
$host = $env['APP_DB_HOST'] ?? $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['APP_DB_PORT'] ?? $env['DB_PORT'] ?? '3306';
$db   = $env['APP_DB_DATABASE'] ?? $env['DB_DATABASE'] ?? 'edulearn_app';
$user = $env['APP_DB_USERNAME'] ?? $env['DB_USERNAME'] ?? 'root';
$pass = $env['APP_DB_PASSWORD'] ?? $env['DB_PASSWORD'] ?? '';
try {
    $dsn = "mysql:host={$host};port={$port}";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = "CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "Database '{$db}' created or already exists.\n";
    exit(0);
} catch (PDOException $e) {
    fwrite(STDERR, "Failed to create database: " . $e->getMessage() . "\n");
    exit(2);
}
