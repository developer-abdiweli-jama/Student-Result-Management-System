<?php
// config/security.php (Enhanced)
// Security configurations
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
// PASSWORD_MIN_LENGTH should be defined in config/constants.php to keep a single source of truth.
// If you need to override it for a specific deployment, update config/constants.php.
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// CSRF protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check token lifetime
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function deepSanitize($data) {
    if (is_array($data)) {
        return array_map('deepSanitize', $data);
    }
    
    // Remove null bytes
    $data = str_replace(chr(0), '', $data);
    
    // Convert to UTF-8
    if (!mb_detect_encoding($data, 'UTF-8', true)) {
        $data = mb_convert_encoding($data, 'UTF-8', 'auto');
    }
    
    // Standard sanitization
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return $data;
}

// SQL injection prevention helper
function escapeSQL($conn, $data) {
    if (is_array($data)) {
        return array_map(function($item) use ($conn) {
            return $conn->real_escape_string($item);
        }, $data);
    }
    return $conn->real_escape_string($data);
}

// Rate limiting
function checkRateLimit($key, $limit = 10, $timeframe = 60) {
    $current_time = time();
    $window_start = $current_time - $timeframe;
    
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    // Clean old entries
    $_SESSION['rate_limits'][$key] = array_filter(
        $_SESSION['rate_limits'][$key], 
        function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        }
    );
    
    // Check if limit exceeded
    if (count($_SESSION['rate_limits'][$key]) >= $limit) {
        return false;
    }
    
    // Add current request
    $_SESSION['rate_limits'][$key][] = $current_time;
    return true;
}

// Security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data:",
        "connect-src 'self'"
    ];
    
    header("Content-Security-Policy: " . implode('; ', $csp));
}
?>