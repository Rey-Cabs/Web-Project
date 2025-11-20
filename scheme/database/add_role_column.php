<?php
/**
 * Small CLI helper to add `role` column to `users` table if it doesn't exist.
 * Run from project root:
 * php "scheme/database/add_role_column.php"
 */

define('PREVENT_DIRECT_ACCESS', true);

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

    // Check if `users` table exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = 'users'");
    $stmt->execute([$db]);
    $exists = (int) $stmt->fetchColumn();
    if (!$exists) {
        echo "No `users` table found in database '$db'. Cannot add role column.\n";
        exit(1);
    }

    // Check if column exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = 'users' AND column_name = 'role'");
    $stmt->execute([$db]);
    $colExists = (int) $stmt->fetchColumn();

    if ($colExists) {
        echo "Column `role` already exists on `users` table.\n";
        exit(0);
    }

    // Add the column with default 'user'
    $sql = "ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user'";
    $pdo->exec($sql);
    echo "Added `role` column to `users` table (default 'user').\n";
    exit(0);
} catch (PDOException $e) {
    echo "Failed to add role column: " . $e->getMessage() . "\n";
    exit(1);
}
