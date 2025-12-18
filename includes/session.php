<?php
// includes/session.php
// Ensure config constants (like SESSION_TIMEOUT) are available. Use require_once so it's safe if already included.
if (!defined('SITE_NAME')) {
    // constants.php defines SITE_NAME and PASSWORD_MIN_LENGTH
    @require_once __DIR__ . '/../config/constants.php';
}
// load security defaults (SESSION_TIMEOUT, etc.) if present
@require_once __DIR__ . '/../config/security.php';

// Provide a safe default for SESSION_TIMEOUT if it's still not defined
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 1800); // 30 minutes fallback
}

session_start();

// Session security
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: ../login.php?error=timeout');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

function isStudent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_STUDENT;
}

function isTeacher() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_TEACHER;
}

function requireTeacher() {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: ../login.php?error=unauthorized');
        exit();
    }
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ../login.php?error=unauthorized');
        exit();
    }
}

function requireStudent() {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: ../login.php?error=unauthorized');
        exit();
    }
}
?>