<?php
// scripts/reseed_data.php
require_once __DIR__ . '/../config/database.php';

function runSqlFile($db, $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        return;
    }
    echo "Running $file...\n";
    $sql = file_get_contents($file);
    if ($db->multi_query($sql)) {
        do {
            if ($res = $db->store_result()) {
                $res->free();
            }
        } while ($db->more_results() && $db->next_result());
        echo "Finished $file\n";
    } else {
        echo "Error running $file: " . $db->error . "\n";
    }
}

try {
    $db = getDBConnection();
    
    // 1. Load students
    runSqlFile($db, __DIR__ . '/../sql/sample_students.sql');
    
    // 2. Generate results (and update streams)
    echo "Generating sample results...\n";
    require_once __DIR__ . '/generate_sample_results.php';
    
    // 3. Load generated results
    runSqlFile($db, __DIR__ . '/../sql/sample_results.sql');
    
    $db->close();
    echo "Data reseeded successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
