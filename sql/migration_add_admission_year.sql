-- sql/migration_add_admission_year.sql
-- Add admission_year column to students table
-- Direct ALTER TABLE approach (safe for fresh databases)
ALTER TABLE students
ADD COLUMN admission_year VARCHAR(20) NOT NULL DEFAULT '2024/2025'
AFTER year_of_study;