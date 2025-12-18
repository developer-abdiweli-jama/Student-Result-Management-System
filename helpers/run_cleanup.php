<?php
// helpers/run_cleanup.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();
$sqlFile = __DIR__ . '/../sql/fix_orphaned_records.sql';

if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($commands as $cmd) {
        if (!empty($cmd)) {
            $conn->query($cmd);
        }
    }
    echo "Cleanup completed.\n";
} else {
    echo "SQL file not found.\n";
}
$conn->close();
?>
