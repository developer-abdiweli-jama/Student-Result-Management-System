<?php
// includes/middleware/teacher_auth.php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../validation.php';
require_once __DIR__ . '/../auth.php';

if (!isLoggedIn() || !isTeacher()) {
    header('Location: ../../login.php?error=unauthorized');
    exit();
}
?>
