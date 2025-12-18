<?php
require_once 'config/database.php';
$conn = getDBConnection();

// Check unique keys on results table
$res = $conn->query("SHOW INDEX FROM results WHERE Key_name = 'unique_student_subject_term'");
$columns = [];
while ($row = $res->fetch_assoc()) {
    $columns[] = $row['Column_name'];
}

echo "Unique key columns for 'unique_student_subject_term': " . implode(', ', $columns) . "\n";

if (!in_array('academic_year', $columns)) {
    echo "WARNING: academic_year is MISSING from the unique key. Results might be overwritten for different years!\n";
} else {
    echo "SUCCESS: unique key correctly includes academic_year.\n";
}

// Check for PHP lint errors in the modified file
$output = shell_exec('php -l teacher/enter_result.php');
echo "\nLint check for teacher/enter_result.php:\n$output";
?>
