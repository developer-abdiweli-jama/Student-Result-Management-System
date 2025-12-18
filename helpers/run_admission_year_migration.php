<?php
// helpers/run_admission_year_migration.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM students LIKE 'admission_year'");
if ($check->num_rows == 0) {
    // Add column
    // We default to 2023/2024 for existing students to be safe and allow some backward compatibility, 
    // or we can strictly use the current year. 
    // Let's use 2020/2021 for EXISTING students to avoid blocking their past results, 
    // but the column definition itself will default to '2024/2025' for NEW inserts if not specified.
    
    // Actually, SQL DEFAULT applies to existing rows when adding a NOT NULL column in MySQL.
    
    if ($conn->query("ALTER TABLE students ADD COLUMN admission_year VARCHAR(20) NOT NULL DEFAULT '2024/2025' AFTER year_of_study")) {
        echo "Added admission_year column.\n";
        
        // Optional: Backfill easier constraint for existing students
        $conn->query("UPDATE students SET admission_year = '2020/2021'");
        echo "Backfilled existing students to 2020/2021 for compatibility.\n";
        
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column admission_year already exists.\n";
}

$conn->close();
?>
