<?php
// includes/upload_avatar.php
// Handles avatar uploads for logged-in users (admin and student)
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

if (!isLoggedIn()) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// default target is the logged in user
$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? '';

// Admins may specify a target user to update: target_role=student|admin and target_id
if ($role === ROLE_ADMIN && isset($_POST['target_role']) && isset($_POST['target_id'])) {
    $target_role = $_POST['target_role'];
    $target_id = (int)$_POST['target_id'];
    if (in_array($target_role, ['student', 'admin'])) {
        $role = $target_role === 'admin' ? ROLE_ADMIN : ROLE_STUDENT;
        $user_id = $target_id;
    }
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo 'No file uploaded or upload error';
    exit;
}

$allowedTypes = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
if (!isset($allowedTypes[$mime])) {
    http_response_code(400);
    echo 'Invalid file type';
    exit;
}

$ext = $allowedTypes[$mime];
// Validation: max size and dimensions
$maxBytes = 1 * 1024 * 1024; // 1 MB for avatars
if ($_FILES['avatar']['size'] > $maxBytes) {
    http_response_code(400);
    echo 'Avatar must be 1MB or smaller';
    exit;
}

$uploadsDir = __DIR__ . '/../assets/uploads/avatars/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Save original
$timestamp = time();
$origFilename = ($role ?: 'user') . '_' . $user_id . '_orig_' . $timestamp . $ext;
$origPath = $uploadsDir . $origFilename;
if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $origPath)) {
    http_response_code(500);
    echo 'Failed to move uploaded file';
    exit;
}

// Ensure dimensions are reasonable and create square thumbnail (256x256)
$size = @getimagesize($origPath);
if ($size === false) {
    @unlink($origPath);
    http_response_code(400);
    echo 'Invalid image';
    exit;
}
list($width, $height) = $size;
$maxDim = 2000;
if ($width > $maxDim || $height > $maxDim) {
    @unlink($origPath);
    http_response_code(400);
    echo 'Image dimensions are too large';
    exit;
}

$thumbSize = 256;
$thumbFilename = ($role ?: 'user') . '_' . $user_id . '_thumb_' . $timestamp . $ext;
$thumbPath = $uploadsDir . $thumbFilename;

// Check if GD is available
if (extension_loaded('gd')) {
    // Create image resource
    switch ($mime) {
        case 'image/jpeg':
            $src = @imagecreatefromjpeg($origPath);
            break;
        case 'image/png':
            $src = @imagecreatefrompng($origPath);
            break;
        case 'image/gif':
            $src = @imagecreatefromgif($origPath);
            break;
        default:
            $src = false;
    }

    if ($src !== false) {
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        // crop to center square
        $min = min($srcW, $srcH);
        $cropX = (int)(($srcW - $min) / 2);
        $cropY = (int)(($srcH - $min) / 2);
        $dst = imagecreatetruecolor($thumbSize, $thumbSize);
        // Preserve transparency for PNG and GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $thumbSize, $thumbSize, $transparent);
        }
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $thumbSize, $thumbSize, $min, $min);
        // Save thumbnail
        if ($mime === 'image/jpeg') {
            imagejpeg($dst, $thumbPath, 90);
        } elseif ($mime === 'image/png') {
            imagepng($dst, $thumbPath);
        } else {
            imagegif($dst, $thumbPath);
        }
        imagedestroy($dst);
        imagedestroy($src);
    } else {
        // failed to create resource; cleanup and use original as thumbnail
        copy($origPath, $thumbPath);
    }
} else {
    // GD not installed, just use original
    copy($origPath, $thumbPath);
}

// Update DB: save thumbnail filename and delete previous files
$avatarToSave = $thumbFilename;
$conn = getDBConnection();
// get old avatar (if any)
if ($role === ROLE_ADMIN) {
    $sel = $conn->prepare('SELECT avatar FROM admins WHERE id = ?');
    $sel->bind_param('i', $user_id);
} else {
    $sel = $conn->prepare('SELECT avatar FROM students WHERE id = ?');
    $sel->bind_param('i', $user_id);
}
$oldAvatar = null;
if ($sel) {
    $sel->execute();
    $res = $sel->get_result();
    $row = $res->fetch_assoc();
    $oldAvatar = $row['avatar'] ?? null;
    $sel->close();
}

// delete old files (thumb and corresponding orig)
$uploadsDir = __DIR__ . '/../assets/uploads/avatars/';
if ($oldAvatar) {
    if (file_exists($uploadsDir . $oldAvatar)) @unlink($uploadsDir . $oldAvatar);
    $oldOrig = str_replace('_thumb_', '_orig_', $oldAvatar);
    if (file_exists($uploadsDir . $oldOrig)) @unlink($uploadsDir . $oldOrig);
}

if ($role === ROLE_ADMIN) {
    $stmt = $conn->prepare('UPDATE admins SET avatar = ? WHERE id = ?');
    $stmt->bind_param('si', $avatarToSave, $user_id);
} else {
    $stmt = $conn->prepare('UPDATE students SET avatar = ? WHERE id = ?');
    $stmt->bind_param('si', $avatarToSave, $user_id);
}
$stmt->execute();
$stmt->close();
$conn->close();

// Redirect back
$referer = $_SERVER['HTTP_REFERER'] ?? '/';
header('Location: ' . $referer);
exit;
