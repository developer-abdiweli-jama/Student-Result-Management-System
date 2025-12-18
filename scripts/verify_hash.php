<?php
// scripts/verify_hash.php

if ($argc < 3) {
    echo "Usage: php verify_hash.php <password> <hash>\n";
    exit(1);
}

$password = $argv[1];
$hash = $argv[2];

if (password_verify($password, $hash)) {
    echo "✅ MATCH! The password is correct.\n";
    exit(0);
} else {
    echo "❌ MISMATCH! The password is incorrect.\n";
    exit(1);
}
