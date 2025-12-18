<?php
// helpers/test_year_change.php
// Simulate changing academic year and see class progression

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/class_progression.php';

$conn = getDBConnection();

echo "=== Testing Academic Year Change ===\n\n";

// Get current year
$current = getSetting('current_academic_year');
echo "Current Academic Year: $current\n\n";

// Simulate changing to next year
$new_year = '2025/2026';
echo "Simulating change to: $new_year\n\n";

// Show what would happen
echo "=== Projected Class Changes ===\n\n";

$result = $conn->query("SELECT id, name, admission_year, admission_class_level, class_level FROM students WHERE admission_class_level IS NOT NULL ORDER BY 
    CASE admission_class_level
        WHEN 'Form 4' THEN 1
        WHEN 'Form 3' THEN 2
        WHEN 'Form 2' THEN 3
        WHEN 'Form 1' THEN 4
        WHEN 'Grade 8' THEN 5
        WHEN 'Grade 7' THEN 6
        WHEN 'Grade 6' THEN 7
        WHEN 'Grade 5' THEN 8
    END, name");

$by_class = [];
while ($student = $result->fetch_assoc()) {
    $new_class = calculateCurrentClassLevel(
        $student['admission_year'],
        $student['admission_class_level'],
        $new_year
    );
    
    if (!isset($by_class[$student['admission_class_level']])) {
        $by_class[$student['admission_class_level']] = [];
    }
    $by_class[$student['admission_class_level']][] = [
        'name' => $student['name'],
        'current' => $student['class_level'],
        'new' => $new_class
    ];
}

foreach ($by_class as $admission_class => $students) {
    $first = $students[0];
    echo "Students admitted as $admission_class:\n";
    echo "  Current: {$first['current']} → New: {$first['new']}\n";
    echo "  (" . count($students) . " students)\n\n";
}

echo "\n=== To Apply This Change ===\n";
echo "1. Go to Admin Settings and change Academic Year to $new_year\n";
echo "2. Run: php helpers/apply_class_progression.php\n";

$conn->close();
?>
