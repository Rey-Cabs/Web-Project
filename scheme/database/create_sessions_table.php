<?php
/**
 * Small CLI helper to create the `sessions` table using your app's DB config.
 * Run from project root:
 * php "scheme/database/create_sessions_table.php"
 */

define('PREVENT_DIRECT_ACCESS', true);

$schemaFile = __DIR__ . '/schema_sessions.sql';
if (!file_exists($schemaFile)) {
    echo "schema file not found: $schemaFile\n";
    exit(1);
}

// load database config
$dbConfigFile = __DIR__ . '/../../app/config/database.php';
if (!file_exists($dbConfigFile)) {
    echo "database config not found: $dbConfigFile\n";
    exit(1);
}

require $dbConfigFile; // expects $database['main']

if (!isset($database) || !isset($database['main'])) {
    echo "Invalid database configuration in $dbConfigFile\n";
    exit(1);
}

$cfg = $database['main'];

$host = $cfg['hostname'] ?? '127.0.0.1';
$port = $cfg['port'] ?? '3306';
$db   = $cfg['database'] ?? '';
$user = $cfg['username'] ?? '';
$pass = $cfg['password'] ?? '';
$charset = $cfg['charset'] ?? 'utf8mb4';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = file_get_contents($schemaFile);
    $pdo->exec($sql);
    echo "sessions table created or already exists in database '$db'.\n";
} catch (PDOException $e) {
    echo "Failed to create sessions table: " . $e->getMessage() . "\n";
    exit(1);
}
