<?php
// router.php - used with PHP built-in server to serve static files directly and route other requests to index.php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$requested = __DIR__ . $uri;

// If the request is for an existing file (asset like css/js/img or a php file that should run), let the server handle it
if ($uri !== '/' && file_exists($requested) && is_file($requested)) {
    return false;
}

// Otherwise fallback to index.php (you can change this to another front controller if needed)
require_once __DIR__ . '/index.php';
