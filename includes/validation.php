<?php
// includes/validation.php
// Improved domain-aware validation utilities for the SRMIS application.

// Ensure PASSWORD_MIN_LENGTH exists (will use config/constants.php if set)
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8);
}

// If the project already defines sanitizeInput (in includes/functions.php), use it.
// Otherwise provide a safe fallback.
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($value) {
        if (is_array($value)) {
            return array_map('sanitizeInput', $value);
        }
        $value = trim((string)$value);
        $value = stripslashes($value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function validateEmail($email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password): bool {
    if (!is_string($password) || strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    // Optional: enforce complexity (uncomment if desired)
    // return preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[\W]/', $password);
    return true;
}

function validateMarks($marks): bool {
    return is_numeric($marks) && $marks >= 0 && $marks <= 100;
}

function validateYear($year): bool {
    $y = (int)$year;
    return ($y >= 1 && $y <= 5);
}

function validateTerm($term): bool {
    $t = (int)$term;
    return ($t >= 1 && $t <= 2);
}

function validateName($name): bool {
    return (bool)preg_match('/^[a-zA-Z\s\.\-]{2,100}$/', (string)$name);
}

function validateRegNo($reg_no): bool {
    // Normalize to uppercase for validation; store normalization elsewhere if needed
    return (bool)preg_match('/^[A-Z0-9]{3,20}$/', strtoupper((string)$reg_no));
}

function validateSubjectCode($code): bool {
    return (bool)preg_match('/^[A-Z0-9]{2,10}$/', strtoupper((string)$code));
}

function validateRequired($value): bool {
    if (is_array($value)) {
        return !empty($value);
    }
    return trim((string)$value) !== '';
}

function validateInput($data, $type): bool {
    $data = sanitizeInput($data);

    switch ($type) {
        case 'name':
            return validateName($data);
        case 'email':
            return validateEmail($data);
        case 'password':
            return validatePassword($data);
        case 'marks':
            return validateMarks($data);
        case 'year':
            return validateYear($data);
        case 'term':
            return validateTerm($data);
        case 'reg_no':
            return validateRegNo($data);
        case 'subject_code':
            return validateSubjectCode($data);
        case 'required':
            return validateRequired($data);
        default:
            return validateRequired($data);
    }
}

function getValidationError($field, $type): string {
    $errors = [
        'name' => 'Name must be 2-100 characters containing only letters, spaces, dots, and hyphens',
        'email' => 'Please enter a valid email address',
        'password' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long',
        'marks' => 'Marks must be a number between 0 and 100',
        'year' => 'Year must be between 1 and 5',
        'term' => 'Term must be between 1 and 3',
        'reg_no' => 'Registration number must be 3-20 alphanumeric characters',
        'subject_code' => 'Subject code must be 2-10 alphanumeric characters',
        'required' => $field . ' is required'
    ];

    return $errors[$type] ?? 'Invalid input';
}

/**
 * Validate that the result academic year is not before the student's admission year.
 * @param int $student_id
 * @param string $academic_year
 * @param mysqli $conn
 * @return bool|string True if valid, or error message string if invalid.
 */
function validateStudentResultYear($student_id, $academic_year, $conn) {
    if (empty($academic_year)) return true;
    
    $stmt = $conn->prepare("SELECT admission_year FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if (!$student || empty($student['admission_year'])) {
        return true;
    }
    
    if (strcmp($academic_year, $student['admission_year']) < 0) {
        return "Cannot add result for academic year $academic_year. Student was admitted in " . $student['admission_year'] . ".";
    }
    
    return true;
}
?>