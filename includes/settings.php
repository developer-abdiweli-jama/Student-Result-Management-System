<?php
// includes/settings.php
// Simple settings loader that fetches key/value pairs from settings table
// Usage: require_once __DIR__ . '/settings.php'; then getSetting('site_name')

require_once __DIR__ . '/../config/database.php';

function getDBSettings() {
    static $settings = null;
    if ($settings !== null) return $settings;

    $settings = [];
    try {
        $db = getDBConnection();
        $stmt = $db->prepare('SELECT `key`, `value` FROM settings');
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $settings[$row['key']] = $row['value'];
            }
            $stmt->close();
        }
        $db->close();
    } catch (Exception $e) {
        // ignore and return empty settings
    }
    return $settings;
}

function getSetting($key, $default = null) {
    $settings = getDBSettings();
    if (isset($settings[$key])) return $settings[$key];
    return $default;
}

function setSetting($key, $value) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?');
        if ($stmt) {
            $stmt->bind_param('sss', $key, $value, $value);
            $stmt->execute();
            $stmt->close();
        }
        $db->close();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
