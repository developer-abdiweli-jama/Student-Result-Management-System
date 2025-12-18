<?php
// includes/middleware/admin_auth.php
// Use __DIR__ to require files relative to this middleware file, which avoids include path issues
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../validation.php';
require_once __DIR__ . '/../auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php?error=unauthorized');
    exit();
}
?>