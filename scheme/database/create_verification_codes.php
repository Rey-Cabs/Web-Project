<?php
/**
 * CLI helper to create verification_codes table.
 * Run from project root:
 * php "scheme/database/create_verification_codes.php"
 */

define('PREVENT_DIRECT_ACCESS', true);

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

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = 'verification_codes'");
    $stmt->execute([$db]);
    $exists = (int) $stmt->fetchColumn();
    if ($exists) {
        echo "Table verification_codes already exists.\n";
        exit(0);
    }

    $sql = "CREATE TABLE `verification_codes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL,
        `user_id` INT NULL,
        `code` VARCHAR(10) NOT NULL,
        `purpose` VARCHAR(50) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (`email`),
        INDEX (`purpose`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "Created verification_codes table.\n";
    exit(0);
} catch (PDOException $e) {
    echo "Failed to create table: " . $e->getMessage() . "\n";
    exit(1);
}
