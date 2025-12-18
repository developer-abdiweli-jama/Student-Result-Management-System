-- migration_add_settings.mysql.sql
-- MySQL: Creates a settings table for key/value pairs

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default site_name if missing
INSERT INTO settings (`key`, `value`)
SELECT 'site_name', 'Student Result Management System'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM settings WHERE `key` = 'site_name');
