<?php
// includes/auth.php
// Authentication helper functions used across the app.
// Uses PHP's password_hash / password_verify

// Start session only if not started elsewhere
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Hash a plain password for storage.
 * Use PASSWORD_DEFAULT (currently bcrypt/argon2 depending on PHP version).
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a plain password against a stored hash.
 */
function verifyPassword(string $password, string $hash): bool {
    if (empty($hash)) return false;
    return password_verify($password, $hash);
}

/**
 * Log the user in (store session values).
 * $userData is the DB row (associative array).
 * $role is a role constant like ROLE_ADMIN or ROLE_STUDENT.
 */
function loginUser(array $userData, string $role): void {
    // ensure session started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user_role'] = $role;
    // set username/reg_no for display
    $_SESSION['username'] = $userData['username'] ?? $userData['reg_no'] ?? '';
    $_SESSION['name'] = $userData['name'] ?? '';
    $_SESSION['login_time'] = time();

    // regenerate session ID to prevent fixation
    session_regenerate_id(true);
}

/**
 * Log the user out
 */
function logoutUser(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // clear session data
    $_SESSION = [];
    // destroy cookie + session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Return true if a user is logged in
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['user_id']);
    }
}

/**
 * Return role string or null
 */
if (!function_exists('currentUserRole')) {
    function currentUserRole(): ?string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_role'] ?? null;
    }
}

/**
 * Guard - require an admin; redirect to login if not.
 * This matches your existing middleware approach.
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== ROLE_ADMIN) {
            header('Location: ../login.php');
            exit();
        }
    }
}

/**
 * Guard - require a student
 */
if (!function_exists('requireStudent')) {
    function requireStudent(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== ROLE_STUDENT) {
            header('Location: ../login.php');
            exit();
        }
    }
}
