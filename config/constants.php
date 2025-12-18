<?php
// config/constants.php
define('SITE_NAME', 'Student Result Management System');
define('SITE_URL', 'http://localhost/srmis');

// Grade calculation constants
define('GRADE_SCALE', [
    'A' => ['min' => 90, 'max' => 100, 'point' => 4.0],
    'A-' => ['min' => 85, 'max' => 89, 'point' => 3.7],
    'B+' => ['min' => 80, 'max' => 84, 'point' => 3.3],
    'B' => ['min' => 75, 'max' => 79, 'point' => 3.0],
    'B-' => ['min' => 70, 'max' => 74, 'point' => 2.7],
    'C+' => ['min' => 65, 'max' => 69, 'point' => 2.3],
    'C' => ['min' => 60, 'max' => 64, 'point' => 2.0],
    'D' => ['min' => 50, 'max' => 59, 'point' => 1.0],
    'F' => ['min' => 0, 'max' => 49, 'point' => 0.0]
]);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_STUDENT', 'student');
define('ROLE_TEACHER', 'teacher');
// Minimum password length used by the app (can be overridden elsewhere)
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8);
}
?>