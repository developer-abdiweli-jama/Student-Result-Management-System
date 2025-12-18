-- sql/migration_add_academic_year.sql
-- Add academic_year column to results table if it doesn't exist
-- Direct ALTER TABLE approach (safe for fresh databases)
-- If column already exists, this will fail gracefully and the migration runner will skip it next time
-- Add column to results table
ALTER TABLE results
ADD COLUMN academic_year VARCHAR(20) NOT NULL DEFAULT '2024/2025'
AFTER term;
-- Add index for better performance
ALTER TABLE results
ADD INDEX idx_academic_year (academic_year);
-- Insert current_academic_year into settings if it doesn't exist
INSERT IGNORE INTO settings (`key`, `value`)
VALUES ('current_academic_year', '2024/2025');