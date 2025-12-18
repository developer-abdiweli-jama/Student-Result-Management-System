<?php
// scripts/reset_admin_password.php
// Usage (CLI): php scripts/reset_admin_password.php <username> <new_password>
// This script updates the admin password using the project's DB config and password hashing.

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

if ($argc < 3) {
    echo "Usage: php scripts/reset_admin_password.php <username> <new_password>\n";
    exit(1);
}

$username = $argv[1];
$newPassword = $argv[2];

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$conn = getDBConnection();
$hash = hashPassword($newPassword);

$stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
if (!$stmt) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error . "\n";
    exit(1);
}
$stmt->bind_param('ss', $hash, $username);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "\n";
    exit(1);
}

$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected > 0) {
    echo "Password for '$username' updated successfully.\n";
    exit(0);
} else {
    echo "No rows updated. Check that user '$username' exists.\n";
    exit(2);
}
