<?php
// helpers/class_progression.php
// Functions to calculate current class level based on academic year progression

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/settings.php';

/**
 * Get the class level order (for progression calculation)
 * Grade 5 = 1, Grade 6 = 2, ..., Grade 8 = 4, Form 1 = 5, ..., Form 4 = 8
 */
function getClassOrder($class_level) {
    $order = [
        'Grade 5' => 1,
        'Grade 6' => 2,
        'Grade 7' => 3,
        'Grade 8' => 4,
        'Form 1' => 5,
        'Form 2' => 6,
        'Form 3' => 7,
        'Form 4' => 8,
    ];
    return $order[$class_level] ?? 0;
}

/**
 * Get class level from order number
 * Returns "Graduated" for students beyond Form 4
 * Returns "Pre-Grade" for students before their admission (negative progression)
 */
function getClassFromOrder($order) {
    $classes = [
        1 => 'Grade 5',
        2 => 'Grade 6',
        3 => 'Grade 7',
        4 => 'Grade 8',
        5 => 'Form 1',
        6 => 'Form 2',
        7 => 'Form 3',
        8 => 'Form 4',
    ];
    
    // Students beyond Form 4 are Graduated
    if ($order > 8) return 'Graduated';
    
    // Students before Grade 5 (going back before admission) are Pre-Grade
    if ($order < 1) return 'Pre-Grade';
    
    return $classes[$order] ?? 'Graduated';
}

/**
 * Extract start year from academic year string (e.g., "2024/2025" -> 2024)
 */
function getStartYear($academic_year) {
    if (empty($academic_year)) return date('Y');
    $parts = explode('/', $academic_year);
    return intval($parts[0]);
}

/**
 * Calculate current class level based on admission year, admission class, and current academic year
 * 
 * @param string $admission_year e.g., "2022/2023"
 * @param string $admission_class_level e.g., "Form 1" (class at time of admission)
 * @param string $current_academic_year e.g., "2024/2025" (from settings)
 * @return string Current class level (e.g., "Form 3")
 */
function calculateCurrentClassLevel($admission_year, $admission_class_level, $current_academic_year = null) {
    if (empty($current_academic_year)) {
        $current_academic_year = getSetting('current_academic_year', date('Y') . '/' . (date('Y') + 1));
    }
    
    $admission_start = getStartYear($admission_year);
    $current_start = getStartYear($current_academic_year);
    
    // How many years have passed since admission?
    $years_passed = $current_start - $admission_start;
    
    // Get base class order
    $base_order = getClassOrder($admission_class_level);
    
    // Calculate new class order (progress by years passed)
    $new_order = $base_order + $years_passed;
    
    return getClassFromOrder($new_order);
}

/**
 * Update all students' class_level based on current academic year
 * This should be called when academic year changes
 */
function updateAllStudentClassLevels() {
    $conn = getDBConnection();
    $current_year = getSetting('current_academic_year');
    
    // Get all students with their admission info
    $result = $conn->query("SELECT id, admission_year, class_level FROM students");
    
    $updated = 0;
    while ($student = $result->fetch_assoc()) {
        // Skip if no admission data
        if (empty($student['admission_year'])) continue;
        
        // For now, use class_level as the admission class (we'll add admission_class_level later)
        $new_class = calculateCurrentClassLevel(
            $student['admission_year'], 
            $student['class_level'], // This should be admission_class_level once we add it
            $current_year
        );
        
        // Only update if different (to avoid unnecessary updates)
        // For now, skip this since we need the admission_class_level column first
    }
    
    $conn->close();
    return $updated;
}

// Test function
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    echo "=== Class Progression Test ===\n\n";
    
    $test_cases = [
        // Normal progression
        ['admission' => '2022/2023', 'class' => 'Form 1', 'current' => '2024/2025', 'desc' => 'Normal: Form 1 + 2 years'],
        ['admission' => '2023/2024', 'class' => 'Grade 5', 'current' => '2024/2025', 'desc' => 'Normal: Grade 5 + 1 year'],
        
        // Graduated scenarios
        ['admission' => '2020/2021', 'class' => 'Form 4', 'current' => '2024/2025', 'desc' => 'Graduated: Form 4 + 4 years'],
        ['admission' => '2021/2022', 'class' => 'Form 3', 'current' => '2024/2025', 'desc' => 'Graduated: Form 3 + 3 years'],
        ['admission' => '2020/2021', 'class' => 'Form 2', 'current' => '2024/2025', 'desc' => 'Graduated: Form 2 + 4 years'],
        
        // Pre-Grade scenarios (going back before admission)
        ['admission' => '2024/2025', 'class' => 'Grade 5', 'current' => '2022/2023', 'desc' => 'Pre-Grade: Grade 5 - 2 years'],
        ['admission' => '2024/2025', 'class' => 'Form 1', 'current' => '2020/2021', 'desc' => 'Pre-Grade: Form 1 - 4 years'],
    ];
    
    foreach ($test_cases as $test) {
        $result = calculateCurrentClassLevel($test['admission'], $test['class'], $test['current']);
        $years = getStartYear($test['current']) - getStartYear($test['admission']);
        echo "{$test['desc']}\n";
        echo "  Admitted: {$test['admission']} as {$test['class']}\n";
        echo "  Current Year: {$test['current']} (Years: $years)\n";
        echo "  → Result: $result\n\n";
    }
}
?>
