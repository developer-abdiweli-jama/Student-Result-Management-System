<?php
// scripts/run_migrations.php
// Safe migration runner for SRMIS
// Usage: php scripts/run_migrations.php

require_once __DIR__ . '/../config/database.php';

function out($s) {
    echo $s . PHP_EOL;
}

try {
    $db = getDBConnection();

    // Ensure migrations table exists
    $create = "CREATE TABLE IF NOT EXISTS migrations (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  filename VARCHAR(255) NOT NULL UNIQUE,\n  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB";
    if (!$db->query($create)) {
        throw new Exception('Failed to ensure migrations table: ' . $db->error);
    }

    // Get already applied
    $applied = [];
    $res = $db->query('SELECT filename FROM migrations');
    if ($res) {
        while ($row = $res->fetch_assoc()) $applied[$row['filename']] = true;
        $res->close();
    }

    $dir = __DIR__ . '/../sql';
    $files = scandir($dir);
    $sqlFiles = [];
    $mysqlVersions = []; // Track which migrations have MySQL-specific versions
    
    // First pass: identify all MySQL-specific migration files
    foreach ($files as $f) {
        if (preg_match('/\.mysql\.sql$/', $f)) {
            $sqlFiles[] = $f;
            // Extract base name to skip generic version (e.g., "migration_add_settings" from "migration_add_settings.mysql.sql")
            $baseName = preg_replace('/\.mysql\.sql$/', '', $f);
            $mysqlVersions[$baseName] = true;
        }
    }
    
    // Second pass: add generic migration files only if no MySQL-specific version exists
    foreach ($files as $f) {
        // Skip if already added (mysql.sql files) or mssql variants
        if (preg_match('/\.mysql\.sql$/', $f) || preg_match('/\.mssql\.sql$/', $f)) {
            continue;
        }
        // Check for generic migration_*.sql files
        if (preg_match('/^migration_.*\.sql$/', $f)) {
            $baseName = preg_replace('/\.sql$/', '', $f);
            // Only add if no MySQL-specific version exists
            if (!isset($mysqlVersions[$baseName])) {
                $sqlFiles[] = $f;
            }
        }
    }
    sort($sqlFiles);

    if (empty($sqlFiles)) {
        out('No migration files found in sql/');
        exit(0);
    }

    foreach ($sqlFiles as $file) {
        if (isset($applied[$file])) {
            out("Skipping already applied: $file");
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        out("Applying $file...");
        $sql = file_get_contents($path);
        if ($sql === false) {
            out("Failed reading $file");
            continue;
        }

        // MySQLi single multi-query run
        if ($db->multi_query($sql)) {
            do {
                if ($res = $db->store_result()) {
                    $res->free();
                }
            } while ($db->more_results() && $db->next_result());

            // record migration
            $stmt = $db->prepare('INSERT INTO migrations (filename) VALUES (?)');
            if ($stmt) {
                $stmt->bind_param('s', $file);
                $stmt->execute();
                $stmt->close();
            } else {
                out('Warning: failed to record migration: ' . $db->error);
            }

            out("Applied $file");
        } else {
            out('Failed to apply ' . $file . ': ' . $db->error);
            // stop on failure to avoid partial state
            exit(1);
        }
    }

    $db->close();
    out('Migrations complete.');
} catch (Exception $e) {
    out('Error: ' . $e->getMessage());
    exit(1);
}
