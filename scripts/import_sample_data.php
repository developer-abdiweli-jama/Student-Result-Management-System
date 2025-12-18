<?php
// scripts/import_sample_data.php
// Import subjects, students, and results

require_once __DIR__ . '/../config/database.php';

function out($s) {
    echo $s . PHP_EOL;
}

function runSqlFile($db, $filepath, $name) {
    out("Importing $name...");
    $sql = file_get_contents($filepath);
    if ($sql === false) {
        out("Failed to read $filepath");
        return false;
    }
    
    if ($db->multi_query($sql)) {
        do {
            if ($res = $db->store_result()) {
                // Check if it's a verification query with results
                if ($res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        out("  " . implode(" | ", $row));
                    }
                }
                $res->free();
            }
        } while ($db->more_results() && $db->next_result());
        out("$name imported successfully!");
        return true;
    } else {
        out("Error importing $name: " . $db->error);
        return false;
    }
}

try {
    $db = getDBConnection();
    out("Connected to database.\n");
    
    // Step 1: Import subjects
    $subjectsFile = __DIR__ . '/../sql/data_v2_real_subjects.sql';
    if (file_exists($subjectsFile)) {
        runSqlFile($db, $subjectsFile, "Subjects");
        // Need to close and reopen connection after multi_query
        $db->close();
        $db = getDBConnection();
    }
    
    // Step 2: Import sample students
    $studentsFile = __DIR__ . '/../sql/sample_students.sql';
    if (file_exists($studentsFile)) {
        runSqlFile($db, $studentsFile, "Sample Students");
        $db->close();
        $db = getDBConnection();
    }
    
    // Step 3: Import sample results
    $resultsFile = __DIR__ . '/../sql/sample_results.sql';
    if (file_exists($resultsFile)) {
        runSqlFile($db, $resultsFile, "Sample Results");
    }
    
    // Summary
    out("\n=== IMPORT SUMMARY ===");
    
    $res = $db->query("SELECT COUNT(*) as cnt FROM subjects");
    $row = $res->fetch_assoc();
    out("Total Subjects: " . $row['cnt']);
    
    $res = $db->query("SELECT COUNT(*) as cnt FROM students");
    $row = $res->fetch_assoc();
    out("Total Students: " . $row['cnt']);
    
    $res = $db->query("SELECT COUNT(*) as cnt FROM results");
    $row = $res->fetch_assoc();
    out("Total Results: " . $row['cnt']);
    
    out("\n=== STUDENTS BY CLASS ===");
    $res = $db->query("SELECT class_level, COUNT(*) as cnt FROM students GROUP BY class_level ORDER BY class_level");
    while ($row = $res->fetch_assoc()) {
        out($row['class_level'] . ": " . $row['cnt'] . " students");
    }
    
    out("\n=== RETAINED STUDENTS (GPA < 1.5) ===");
    $res = $db->query("
        SELECT s.reg_no, s.name, s.class_level, r.academic_year, 
               ROUND(AVG(r.grade_point), 2) as avg_gpa
        FROM students s
        JOIN results r ON s.id = r.student_id
        WHERE s.reg_no LIKE 'SRM24%'
        GROUP BY s.id, r.academic_year
        ORDER BY s.reg_no
    ");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            out($row['reg_no'] . " | " . $row['name'] . " | " . $row['class_level'] . " | " . $row['academic_year'] . " | GPA: " . $row['avg_gpa']);
        }
    } else {
        out("No retained students found yet.");
    }
    
    $db->close();
    out("\nImport completed!");
    
} catch (Exception $e) {
    out("Error: " . $e->getMessage());
    exit(1);
}
