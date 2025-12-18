<?php
// helpers/check_students.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$result = $conn->query("SELECT class_level, COUNT(*) as count FROM students GROUP BY class_level ORDER BY class_level");

echo "Current Students in Database:\n";
echo str_repeat("=", 40) . "\n";

$total = 0;
while ($row = $result->fetch_assoc()) {
    echo $row['class_level'] . ": " . $row['count'] . " students\n";
    $total += $row['count'];
}

echo str_repeat("=", 40) . "\n";
echo "TOTAL: $total students\n";

$conn->close();
?>
