<?php
// scripts/mark_migration_applied.php
// Usage: php scripts/mark_migration_applied.php migration_filename.sql

require_once __DIR__ . '/../config/database.php';

if ($argc < 2) {
    echo "Usage: php mark_migration_applied.php migration_filename.sql" . PHP_EOL;
    exit(1);
}

$file = $argv[1];

try {
    $db = getDBConnection();
    $create = "CREATE TABLE IF NOT EXISTS migrations (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  filename VARCHAR(255) NOT NULL UNIQUE,\n  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB";
    if (!$db->query($create)) {
        throw new Exception('Failed to ensure migrations table: ' . $db->error);
    }

    $stmt = $db->prepare('INSERT IGNORE INTO migrations (filename) VALUES (?)');
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }
    $stmt->bind_param('s', $file);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Marked $file as applied." . PHP_EOL;
    } else {
        echo "$file was already recorded or failed to insert." . PHP_EOL;
    }
    $stmt->close();
    $db->close();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
