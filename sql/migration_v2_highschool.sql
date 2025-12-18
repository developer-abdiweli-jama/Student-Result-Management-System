-- Migration to v2 (High School / Mid Level Support)
-- 1. Subjects: Add class_level if not exists
-- 2. Teacher Assignments: Add status if not exists
-- Note: semester column removal skipped as it may not exist
SET FOREIGN_KEY_CHECKS = 0;
-- Add class_level to subjects if it doesn't exist
-- Using ALTER IGNORE to gracefully handle if column exists
SET @colexists = (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'subjects'
            AND column_name = 'class_level'
    );
SET @query = IF(
        @colexists = 0,
        "ALTER TABLE subjects ADD COLUMN class_level VARCHAR(20) NOT NULL DEFAULT 'Form 1' COMMENT 'e.g., Form 1, Grade 9'",
        'SELECT 1'
    );
PREPARE stmt
FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
-- Add status to teacher_assignments if it doesn't exist
SET @colexists2 = (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'teacher_assignments'
            AND column_name = 'status'
    );
SET @query2 = IF(
        @colexists2 = 0,
        "ALTER TABLE teacher_assignments ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved'",
        'SELECT 1'
    );
PREPARE stmt2
FROM @query2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
SET FOREIGN_KEY_CHECKS = 1;