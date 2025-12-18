-- sql/migration_add_streams.sql
-- Adds stream support for high school students (Form 3 & 4)
SET FOREIGN_KEY_CHECKS = 0;
-- 1. Add stream column to students table
ALTER TABLE students
ADD COLUMN stream ENUM('General', 'Science', 'Arts') DEFAULT NULL
AFTER class_level;
-- 2. Add stream column to subjects table to categorize them
-- We'll use this to filter which subjects are available for which stream.
-- 'Core' means available for all streams in that class level.
ALTER TABLE subjects
ADD COLUMN stream VARCHAR(50) DEFAULT 'Core'
AFTER class_level;
SET FOREIGN_KEY_CHECKS = 1;