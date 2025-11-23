<?php
/**
 * Migration: Update schedule column to support datetime
 * 
 * Changes schedule column from DATE to DATETIME to support appointment times
 * 
 * Run this script once: php scheme/database/migrate_schedule_to_datetime.php
 */

define('PREVENT_DIRECT_ACCESS', true);

// Load database config
$dbConfigFile = __DIR__ . '/../../app/config/database.php';
if (!file_exists($dbConfigFile)) {
    echo "❌ Database config not found: $dbConfigFile\n";
    exit(1);
}

require $dbConfigFile;

if (!isset($database) || !isset($database['main'])) {
    echo "❌ Invalid database configuration\n";
    exit(1);
}

$cfg = $database['main'];

$host = $cfg['hostname'] ?? '127.0.0.1';
$port = $cfg['port'] ?? '3306';
$db   = $cfg['database'] ?? '';
$user = $cfg['username'] ?? '';
$pass = $cfg['password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host:$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to database: $db\n";
    
    // Check if schedule column exists and is DATE type
    $check = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                          WHERE TABLE_NAME='patients' AND COLUMN_NAME='schedule' 
                          AND TABLE_SCHEMA='$db'");
    $result = $check->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        echo "❌ Schedule column not found\n";
        exit(1);
    }
    
    echo "✓ Current column type: " . $result['COLUMN_TYPE'] . "\n";
    
    // If already DATETIME, skip
    if (strpos(strtoupper($result['COLUMN_TYPE']), 'DATETIME') !== false) {
        echo "✓ Schedule column is already DATETIME type\n";
        echo "✓ Migration not needed\n";
        exit(0);
    }
    
    // Perform migration
    echo "\n⏳ Migrating schedule column from DATE to DATETIME...\n";
    
    $pdo->exec("ALTER TABLE patients MODIFY COLUMN schedule DATETIME DEFAULT NULL");
    
    echo "✓ Migration completed successfully!\n";
    echo "✓ Schedule column now supports date and time (YYYY-MM-DD HH:MM:SS)\n";
    
    // Verify
    $verify = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                           WHERE TABLE_NAME='patients' AND COLUMN_NAME='schedule' 
                           AND TABLE_SCHEMA='$db'");
    $result = $verify->fetch(PDO::FETCH_ASSOC);
    echo "✓ Verified: " . $result['COLUMN_TYPE'] . "\n";
    
    echo "\n✅ All done! Time-based appointments are now ready to use.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
