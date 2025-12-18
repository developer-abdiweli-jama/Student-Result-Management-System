<?php
// scripts/verify_sample_data.php
// Verify the imported sample data

require_once __DIR__ . '/../config/database.php';

$db = getDBConnection();

echo "=== STUDENTS BY CLASS LEVEL ===\n";
$res = $db->query("SELECT class_level, COUNT(*) as count FROM students GROUP BY class_level ORDER BY class_level");
while($row = $res->fetch_assoc()) {
    echo $row['class_level'] . ": " . $row['count'] . " students\n";
}

echo "\n=== SAMPLE DATA VALIDATION ===\n";

// Check Grade 6 students have Grade 5 history
echo "\n-- Grade 6 students with Grade 5 history --\n";
$res = $db->query("
    SELECT s.name, s.class_level, r.academic_year, COUNT(r.id) as results, ROUND(AVG(r.grade_point),2) as avg_gpa
    FROM students s
    JOIN results r ON s.id = r.student_id
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE s.class_level = 'Grade 6' AND sub.class_level = 'Grade 5'
    GROUP BY s.id, r.academic_year
    LIMIT 5
");
while($row = $res->fetch_assoc()) {
    echo $row['name'] . " | " . $row['class_level'] . " | " . $row['academic_year'] . " | Results: " . $row['results'] . " | GPA: " . $row['avg_gpa'] . "\n";
}

// Check Form 4 students have Form 1,2,3 history
echo "\n-- Form 4 students historical progression --\n";
$res = $db->query("
    SELECT s.name, sub.class_level as subject_class, r.academic_year, COUNT(r.id) as results, ROUND(AVG(r.grade_point),2) as avg_gpa
    FROM students s
    JOIN results r ON s.id = r.student_id
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE s.class_level = 'Form 4' AND s.reg_no LIKE 'SRM25%'
    GROUP BY s.id, sub.class_level, r.academic_year
    ORDER BY s.name, r.academic_year
    LIMIT 15
");
while($row = $res->fetch_assoc()) {
    echo $row['name'] . " | " . $row['subject_class'] . " | " . $row['academic_year'] . " | Results: " . $row['results'] . " | GPA: " . $row['avg_gpa'] . "\n";
}

// Check retained students
echo "\n=== RETAINED STUDENTS (FAILED GPA < 1.5) ===\n";
$res = $db->query("
    SELECT s.reg_no, s.name, s.class_level, r.academic_year, ROUND(AVG(r.grade_point),2) as avg_gpa
    FROM students s
    JOIN results r ON s.id = r.student_id
    WHERE s.reg_no LIKE 'SRM24%'
    GROUP BY s.id, r.academic_year
    ORDER BY s.reg_no
");
while($row = $res->fetch_assoc()) {
    $status = $row['avg_gpa'] < 1.5 ? 'FAILED' : 'PASSED';
    echo $row['reg_no'] . " | " . $row['name'] . " | " . $row['class_level'] . " | " . $row['academic_year'] . " | GPA: " . $row['avg_gpa'] . " | " . $status . "\n";
}

$db->close();
echo "\nVerification complete!\n";
