<?php
// index.php (Enhanced)
require_once 'config/environment.php';
require_once 'config/security.php';

// Set security headers
setSecurityHeaders();

// Check if system is installed
if (!file_exists('config/database.php')) {
    header('Location: setup/install.php');
    exit;
}

// Redirect to login
header('Location: login.php');
exit;
?>