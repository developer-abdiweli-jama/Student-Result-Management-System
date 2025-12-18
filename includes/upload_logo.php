<?php
// includes/upload_logo.php
// Handle admin uploads for site logo. Expects an authenticated admin.

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings.php';

// Only admin can update site logo
if (!isLoggedIn() || currentUserRole() !== ROLE_ADMIN) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['site_logo']) || $_FILES['site_logo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_error'] = 'Please select a logo to upload.';
        header('Location: ../admin/settings.php');
        exit;
    }

    $file = $_FILES['site_logo'];
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/svg+xml' => 'svg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        $_SESSION['flash_error'] = 'Only PNG, JPG, or SVG logos are allowed.';
        header('Location: ../admin/settings.php');
        exit;
    }

    $ext = $allowed[$mime];

    // Validation: max size and dimensions
    $maxBytes = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $maxBytes) {
        $_SESSION['flash_error'] = 'Logo must be 2MB or smaller.';
        header('Location: ../admin/settings.php');
        exit;
    }

    $destDir = __DIR__ . '/../assets/uploads/logos/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $isSvg = ($mime === 'image/svg+xml');
    if (!$isSvg) {
        $size = @getimagesize($file['tmp_name']);
        if ($size === false) {
            $_SESSION['flash_error'] = 'Unable to read image dimensions.';
            header('Location: ../admin/settings.php');
            exit;
        }
        list($width, $height) = $size;
        $maxWidth = 4000; $maxHeight = 4000;
        if ($width > $maxWidth || $height > $maxHeight) {
            $_SESSION['flash_error'] = 'Image dimensions are too large. Maximum is ' . $maxWidth . 'x' . $maxHeight . '.';
            header('Location: ../admin/settings.php');
            exit;
        }
    }

    // Save original first
    $timestamp = time();
    $origFilename = 'logo_orig_' . $timestamp . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $destDir . $origFilename)) {
        $_SESSION['flash_error'] = 'Failed to move uploaded file.';
        header('Location: ../admin/settings.php');
        exit;
    }

    $finalFilename = $origFilename;
    // If raster, generate a resized version (keep aspect ratio) and use that as the displayed logo
    if (!$isSvg) {
        if (extension_loaded('gd')) {
            $resizeMaxW = 600; // max width for resized logo
            $resizeMaxH = 200;
            if ($ext === 'jpg') {
                $src = @imagecreatefromjpeg($destDir . $origFilename);
            } else { // png
                $src = @imagecreatefrompng($destDir . $origFilename);
            }
            if ($src !== false) {
                $srcW = imagesx($src);
                $srcH = imagesy($src);
                $scale = min(1, $resizeMaxW / $srcW, $resizeMaxH / $srcH);
                $newW = (int)($srcW * $scale);
                $newH = (int)($srcH * $scale);
                if ($newW <= 0) $newW = 1; if ($newH <= 0) $newH = 1;
                $dst = imagecreatetruecolor($newW, $newH);
                if ($ext === 'png') {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
                $resizedFilename = 'logo_' . $timestamp . '.' . $ext;
                $resizedPath = $destDir . $resizedFilename;
                if ($ext === 'jpg') {
                    imagejpeg($dst, $resizedPath, 85);
                } else {
                    imagepng($dst, $resizedPath);
                }
                imagedestroy($dst);
                imagedestroy($src);
                $finalFilename = $resizedFilename;
            } else {
                $finalFilename = $origFilename;
            }
        } else {
            // GD not loaded - just use original
            $finalFilename = $origFilename;
        }
    }

    // Delete old logo files if present (both display and original)
    $oldDisplay = getSetting('site_logo', null);
    $oldOrig = getSetting('site_logo_orig', null);
    if ($oldDisplay && file_exists($destDir . $oldDisplay)) {
        @unlink($destDir . $oldDisplay);
    }
    if ($oldOrig && file_exists($destDir . $oldOrig)) {
        @unlink($destDir . $oldOrig);
    }

    // Save to settings: final display filename and original filename
    if (!setSetting('site_logo', $finalFilename) || !setSetting('site_logo_orig', $origFilename)) {
        $_SESSION['flash_error'] = 'Failed to save site logo setting.';
        header('Location: ../admin/settings.php');
        exit;
    }

    $_SESSION['flash_success'] = 'Site logo updated.';
    header('Location: ../admin/settings.php');
    exit;
}

http_response_code(405);
echo 'Method not allowed';
