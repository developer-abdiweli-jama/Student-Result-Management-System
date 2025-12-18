<?php
// includes/delete_logo.php
// Remove current site logo (admin only)

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings.php';

if (!isLoggedIn() || currentUserRole() !== ROLE_ADMIN) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$logo = getSetting('site_logo', null);
$destDir = __DIR__ . '/../assets/uploads/logos/';
if ($logo) {
    if (file_exists($destDir . $logo)) {
        @unlink($destDir . $logo);
    }
    setSetting('site_logo', null);
    $_SESSION['flash_success'] = 'Site logo removed.';
} else {
    $_SESSION['flash_error'] = 'No site logo to remove.';
}

header('Location: ../admin/settings.php');
exit;
