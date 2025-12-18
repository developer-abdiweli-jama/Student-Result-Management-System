<?php
// helpers/apply_class_progression.php
// Apply class progression to all students based on current academic year

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/class_progression.php';

$conn = getDBConnection();

echo "=== Applying Class Progression ===\n\n";

// 1. Check if admission_class_level column exists, if not create it
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'admission_class_level'");
if ($result->num_rows === 0) {
    echo "Adding admission_class_level column...\n";
    $conn->query("ALTER TABLE students ADD COLUMN admission_class_level VARCHAR(20) DEFAULT NULL AFTER class_level");
    
    // Copy current class_level to admission_class_level for existing students
    $conn->query("UPDATE students SET admission_class_level = class_level WHERE admission_class_level IS NULL");
    echo "Copied current class levels to admission_class_level.\n\n";
} else {
    echo "admission_class_level column already exists.\n\n";
}

// 2. Get current academic year
$current_year = getSetting('current_academic_year');
echo "Current Academic Year: $current_year\n\n";

// 3. Update all students' class_level based on progression
echo "Updating student class levels...\n\n";

$result = $conn->query("SELECT id, name, admission_year, admission_class_level, class_level FROM students WHERE admission_class_level IS NOT NULL");

$updated = 0;
$changes = [];

while ($student = $result->fetch_assoc()) {
    // Calculate new class level
    $new_class = calculateCurrentClassLevel(
        $student['admission_year'],
        $student['admission_class_level'],
        $current_year
    );
    
    // Only update if different
    if ($new_class !== $student['class_level']) {
        $stmt = $conn->prepare("UPDATE students SET class_level = ? WHERE id = ?");
        $stmt->bind_param("si", $new_class, $student['id']);
        $stmt->execute();
        $stmt->close();
        
        $changes[] = [
            'name' => $student['name'],
            'from' => $student['class_level'],
            'to' => $new_class
        ];
        $updated++;
    }
}

echo "Updated $updated students.\n\n";

if (count($changes) > 0) {
    echo "Changes made:\n";
    foreach ($changes as $change) {
        echo "  - {$change['name']}: {$change['from']} → {$change['to']}\n";
    }
    echo "\n";
}

// 4. Show current distribution
echo "=== Current Class Distribution ===\n";
$result = $conn->query("SELECT class_level, COUNT(*) as count FROM students GROUP BY class_level ORDER BY 
    CASE class_level
        WHEN 'Form 4' THEN 1
        WHEN 'Form 3' THEN 2
        WHEN 'Form 2' THEN 3
        WHEN 'Form 1' THEN 4
        WHEN 'Grade 8' THEN 5
        WHEN 'Grade 7' THEN 6
        WHEN 'Grade 6' THEN 7
        WHEN 'Grade 5' THEN 8
    END");

while ($row = $result->fetch_assoc()) {
    echo "  " . $row['class_level'] . ": " . $row['count'] . " students\n";
}

$conn->close();
echo "\n✓ Class progression applied for academic year $current_year\n";
?>
