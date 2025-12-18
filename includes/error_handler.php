<?php
// includes/error_handler.php
// Custom error handling

function logError($message, $context = []) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? json_encode($context) : '';
    
    $log_message = "[$timestamp] $message $context_str" . PHP_EOL;
    
    // Ensure logs directory exists
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    error_log($log_message, 3, $log_file);
}

function handleException($exception) {
    logError('Uncaught Exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    // Don't show detailed errors in production
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<div class="alert alert-error">';
        echo '<strong>Exception:</strong> ' . $exception->getMessage() . '<br>';
        echo '<strong>File:</strong> ' . $exception->getFile() . '<br>';
        echo '<strong>Line:</strong> ' . $exception->getLine() . '<br>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-error">';
        echo 'An unexpected error occurred. Please try again later.';
        echo '</div>';
    }
}

function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_types = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice'
    ];
    
    $error_type = $error_types[$errno] ?? 'Unknown Error';
    
    logError("$error_type: $errstr", [
        'file' => $errfile,
        'line' => $errline
    ]);
    
    // Don't show notices and warnings in production
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<div class="alert alert-warning">';
        echo "<strong>$error_type:</strong> $errstr in $errfile on line $errline";
        echo '</div>';
    }
    
    return true;
}

// Set error handlers
set_error_handler('handleError');
set_exception_handler('handleException');
?>