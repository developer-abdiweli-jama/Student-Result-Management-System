<?php
// helpers/run_migration.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$sqlFile = __DIR__ . '/../sql/data_v2_real_subjects.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile");
}

$sql = file_get_contents($sqlFile);
$commands = array_filter(array_map('trim', explode(';', $sql)));

echo "Running migration...\n";

foreach ($commands as $query) {
    if (!empty($query)) {
        if ($conn->query($query) === TRUE) {
            // Success
        } else {
            echo "Error: " . $conn->error . "\n";
            echo "Query: " . substr($query, 0, 100) . "...\n";
            $conn->close();
            exit(1);
        }
    }
}

echo "Migration data_v2_real_subjects.sql completed successfully.\n";
$conn->close();
?>
