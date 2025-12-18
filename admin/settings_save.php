<?php
// admin/settings_save.php
require_once __DIR__ . '/../includes/middleware/admin_auth.php';
require_once __DIR__ . '/../includes/settings.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name'] ?? '');
    $academicYear = trim($_POST['current_academic_year'] ?? '');
    
    if ($siteName) {
        if (setSetting('site_name', $siteName) && setSetting('current_academic_year', $academicYear)) {
            $_SESSION['flash_success'] = 'Settings updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update settings.';
        }
    } else {
        $_SESSION['flash_error'] = 'Site Name cannot be empty.';
    }
}

header('Location: settings.php');
exit;
