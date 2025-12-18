<?php
// helpers/run_sample_students.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Read and execute the SQL file
$sql = file_get_contents(__DIR__ . '/../sql/sample_students.sql');

if ($conn->multi_query($sql)) {
    do {
        // Flush results
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "Successfully inserted 80 sample students!\n";
    echo "- Grade 5: 10 students\n";
    echo "- Grade 6: 10 students\n";
    echo "- Grade 7: 10 students\n";
    echo "- Grade 8: 10 students\n";
    echo "- Form 1: 10 students\n";
    echo "- Form 2: 10 students\n";
    echo "- Form 3: 10 students\n";
    echo "- Form 4: 10 students\n";
} else {
    echo "Error inserting students: " . $conn->error . "\n";
}

$conn->close();
?>
