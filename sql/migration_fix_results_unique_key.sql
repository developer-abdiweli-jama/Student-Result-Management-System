-- sql/migration_fix_results_unique_key.sql
-- Update the unique key on results table to include academic_year
-- This prevents overwriting results from different years for the same student/subject/term
SET FOREIGN_KEY_CHECKS = 0;
-- Drop the old unique key if it exists
ALTER TABLE results DROP INDEX IF EXISTS unique_student_subject_term;
-- Add the new unique key including academic_year
-- Using IGNORE is not supported in ALTER TABLE for unique keys in most MySQL versions 
-- so we assume no existing conflicting data for (student, subject, term, year)
ALTER TABLE results
ADD UNIQUE KEY unique_student_subject_term_year (student_id, subject_id, term, academic_year);
SET FOREIGN_KEY_CHECKS = 1;