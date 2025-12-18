<?php
// scripts/generate_hash.php

if ($argc < 2) {
    echo "Usage: php generate_hash.php <password>\n";
    exit(1);
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "\n";
echo "Hash:     " . $hash . "\n";
