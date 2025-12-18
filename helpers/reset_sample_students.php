<?php
// helpers/reset_sample_students.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

echo "Step 1: Removing existing sample students (SRM25001-SRM25080)...\n";

// Delete only the sample students we're about to insert
for ($i = 1; $i <= 80; $i++) {
    $reg_no = 'SRM25' . str_pad($i, 3, '0', STR_PAD_LEFT);
    $conn->query("DELETE FROM students WHERE reg_no = '$reg_no'");
}

echo "Cleared sample students.\n\n";

echo "Step 2: Inserting all 80 students...\n";

// Read and execute the SQL file line by line to avoid multi_query issues
$sql = file_get_contents(__DIR__ . '/../sql/sample_students.sql');

// Remove comments and split by semicolon
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && 
               !str_starts_with($stmt, '--') && 
               strlen($stmt) > 10;
    }
);

$inserted = 0;
$errors = 0;

foreach ($statements as $stmt) {
    if (empty(trim($stmt))) continue;
    
    try {
        if ($conn->query($stmt)) {
            // Count how many rows inserted
            $affected = $conn->affected_rows;
            if ($affected > 0) {
                $inserted += $affected;
            }
        } else {
            $errors++;
            echo "Error: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        $errors++;
        echo "Exception: " . $e->getMessage() . "\n";
    }
}

echo "\nInserted $inserted students successfully.\n";
if ($errors > 0) echo "Errors: $errors\n";

// Verify
echo "\nStep 3: Verification...\n";
$result = $conn->query("SELECT class_level, COUNT(*) as count FROM students GROUP BY class_level ORDER BY class_level");

echo str_repeat("=", 40) . "\n";
while ($row = $result->fetch_assoc()) {
    echo $row['class_level'] . ": " . $row['count'] . " students\n";
}
echo str_repeat("=", 40) . "\n";

$conn->close();
?>
