<?php
// includes/middleware/student_auth.php
// Use __DIR__ so the required files are resolved relative to this file
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../functions.php';

if (!isLoggedIn() || !isStudent()) {
    header('Location: ../../login.php?error=unauthorized');
    exit();
}
?>