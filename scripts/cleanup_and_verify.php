<?php
// scripts/cleanup_and_verify.php
require_once __DIR__ . '/../config/database.php';

$db = getDBConnection();

// 1. Cleanup legacy categories
$db->query("DELETE FROM students WHERE class_level IN ('Graduated', 'Pre-Grade')");
echo "Cleaned up legacy students.\n";

// 2. Verify streams
echo "\n=== STREAM CONFIGURATION PER SUBJECT ===\n";
$res = $db->query("SELECT subject_code, subject_name, class_level, stream FROM subjects WHERE class_level IN ('Form 3', 'Form 4') ORDER BY class_level, stream");
while($row = $res->fetch_assoc()) {
    echo $row['class_level'] . " | " . $row['subject_code'] . " | " . $row['stream'] . "\n";
}

// 3. Verify students with NULL stream in Form 3/4
echo "\n=== FORM 3/4 STUDENTS WITHOUT STREAMS ===\n";
$res = $db->query("SELECT reg_no, name, class_level FROM students WHERE stream IS NULL AND class_level IN ('Form 3', 'Form 4')");
while($row = $res->fetch_assoc()) {
    echo $row['reg_no'] . " | " . $row['name'] . " | " . $row['class_level'] . "\n";
}

$db->close();
?>
