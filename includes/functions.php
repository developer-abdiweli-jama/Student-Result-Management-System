<?php
// includes/functions.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
// General helper functions used across the app
function calculateGrade($marks) {
    foreach (GRADE_SCALE as $grade => $range) {
        if ($marks >= $range['min'] && $marks <= $range['max']) {
            return [
                'grade' => $grade,
                'point' => $range['point']
            ];
        }
    }
    return ['grade' => 'F', 'point' => 0.0];
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function formatGPA($gpa) {
    return number_format($gpa, 2);
}

/**
 * Generate a registration number.
 * Pattern: SRM + two-digit yearSuffix + 3-digit random
 * If you want guaranteed uniqueness, we could check DB and loop until unique.
 */
function generateRegNo($year) {
    $prefix = 'SRM';
    // If $year is numeric year_of_study (1..5) use current year suffix
    if (is_numeric($year) && (int)$year <= 5) {
        $yearSuffix = date('y'); // e.g. 25
    } else {
        // fallback to last two digits of current year
        $yearSuffix = date('y');
    }
    $random = mt_rand(100, 999);
    return strtoupper($prefix . $yearSuffix . $random);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * JSON response helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
