<?php
// scripts/migrate.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$sqlFile = __DIR__ . '/../sql/migration_v2_highschool.sql';

if (!file_exists($sqlFile)) {
    die("Migration file not found at: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Split SQL into individual queries
$queries = explode(';', $sql);

echo "<h1>Running Migration...</h1>";

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        echo "<p>Executing: " . htmlspecialchars(substr($query, 0, 100)) . "...</p>";
        try {
            if ($conn->query($query)) {
                echo "<p style='color: green;'>Success</p>";
            } else {
                // Ignore "Duplicate column name" error (1060) or "Can't drop" (1091) if re-running
                if ($conn->errno == 1060 || $conn->errno == 1091) {
                     echo "<p style='color: orange;'>Skipped (Already exists/done): " . $conn->error . "</p>";
                } else {
                     echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
                }
            }
        } catch (Exception $e) {
             if ($conn->errno == 1060 || $conn->errno == 1091) {
                 echo "<p style='color: orange;'>Skipped: " . $e->getMessage() . "</p>";
             } else {
                 echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
             }
        }
    }
}

echo "<h2>Migration Completed.</h2>";
echo "<a href='../admin/dashboard.php'>Go to Dashboard</a>";
