-- sql/migration_add_class_level.sql
-- Add class_level column to students table and backfill data
-- Add the column
ALTER TABLE students
ADD COLUMN class_level VARCHAR(20) DEFAULT NULL
AFTER admission_year;
-- Backfill: Map numeric year_of_study to Form 1-4
UPDATE students
SET class_level = 'Form 1'
WHERE year_of_study = 1;
UPDATE students
SET class_level = 'Form 2'
WHERE year_of_study = 2;
UPDATE students
SET class_level = 'Form 3'
WHERE year_of_study = 3;
UPDATE students
SET class_level = 'Form 4'
WHERE year_of_study = 4;
UPDATE students
SET class_level = 'Form 4'
WHERE year_of_study = 5;