<?php
// config/environment.php
// Environment configuration

// Define environment
define('ENVIRONMENT', 'development'); // Change to 'production' when deploying

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Set timezone
date_default_timezone_set('UTC');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable in production with HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Upload configuration
ini_set('file_uploads', 1);
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_file_uploads', 20);

// Memory and execution time
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 30);
?>