<?php
// helpers/run_academic_year_migration.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();
$sqlFile = __DIR__ . '/../sql/migration_add_academic_year.sql';

if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    
    // Split by delimiter logic is tricky in PHP, but for this specific file:
    // We can execute command by command, but DELIMITER syntax is client-side.
    // Simpler approach for this specific migration: 
    // Just run the ALTER directly via PHP logic if column check fails, effectively replicating the SQL logic but in PHP.
    
    // 1. Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM results LIKE 'academic_year'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE results ADD COLUMN academic_year VARCHAR(20) NOT NULL DEFAULT '2024/2025' AFTER term")) {
            echo "Added academic_year column.\n";
            $conn->query("ALTER TABLE results ADD INDEX idx_academic_year (academic_year)");
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } else {
        echo "Column academic_year already exists.\n";
    }

    // 2. Insert setting
    $checkSetting = $conn->query("SELECT * FROM settings WHERE `key` = 'current_academic_year'");
    if ($checkSetting->num_rows == 0) {
        if ($conn->query("INSERT INTO settings (`key`, `value`) VALUES ('current_academic_year', '2024/2025')")) {
            echo "Added current_academic_year setting.\n";
        } else {
            echo "Error adding setting: " . $conn->error . "\n";
        }
    } else {
        echo "Setting current_academic_year already exists.\n";
    }

} else {
    echo "Migration file not found (though logic handled in PHP).\n";
}
$conn->close();
?>
