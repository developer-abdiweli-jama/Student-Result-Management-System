<?php
// helpers/run_class_level_migration.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM students LIKE 'class_level'");
if ($check->num_rows == 0) {
    if ($conn->query("ALTER TABLE students ADD COLUMN class_level VARCHAR(20) DEFAULT NULL AFTER admission_year")) {
        echo "Added class_level column.\n";
        
        // Backfill
        $conn->query("UPDATE students SET class_level = 'Form 1' WHERE year_of_study = 1");
        $conn->query("UPDATE students SET class_level = 'Form 2' WHERE year_of_study = 2");
        $conn->query("UPDATE students SET class_level = 'Form 3' WHERE year_of_study = 3");
        $conn->query("UPDATE students SET class_level = 'Form 4' WHERE year_of_study >= 4");
        
        echo "Backfilled existing students to Form 1-4 based on year_of_study.\n";
        
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column class_level already exists.\n";
}

$conn->close();
?>
