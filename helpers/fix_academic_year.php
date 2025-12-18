<?php
// helpers/fix_academic_year.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/settings.php';

$conn = getDBConnection();

echo "=== Academic Year Configuration Fix ===\n\n";

// 1. Check current academic year
$current_year = getSetting('current_academic_year');
echo "Current Academic Year Setting: " . ($current_year ?: 'NOT SET') . "\n";

// 2. Set to 2024/2025
$new_year = '2024/2025';
setSetting('current_academic_year', $new_year);
echo "Updated Academic Year to: $new_year\n";

// 3. Update ALL sample student admission years to 2024/2025
echo "\n=== Updating Sample Student Admission Years ===\n";
$conn->query("UPDATE students SET admission_year = '2024/2025' WHERE reg_no LIKE 'SRM25%'");
echo "Updated all sample students to admission year: 2024/2025\n";

// 4. Verify
echo "\n=== Verification ===\n";
// Need new connection to get fresh data
$conn2 = getDBConnection();
$stmt = $conn2->prepare("SELECT `value` FROM settings WHERE `key` = 'current_academic_year'");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo "New Academic Year: " . ($row['value'] ?? 'NOT SET') . "\n\n";
$stmt->close();

$result = $conn->query("SELECT class_level, COUNT(*) as count, admission_year FROM students WHERE reg_no LIKE 'SRM25%' GROUP BY class_level, admission_year ORDER BY class_level");
echo "Students by Class:\n";
while ($row = $result->fetch_assoc()) {
    echo "  " . $row['class_level'] . ": " . $row['count'] . " students (Admission: " . $row['admission_year'] . ")\n";
}

$conn->close();
$conn2->close();
echo "\n✓ Done! You can now add results for academic year 2024/2025.\n";
?>
