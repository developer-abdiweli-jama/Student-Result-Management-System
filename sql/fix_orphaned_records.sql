-- sql/fix_orphaned_records.sql
-- Deletes results and assignments that reference non-existent subjects
DELETE r
FROM results r
    LEFT JOIN subjects s ON r.subject_id = s.id
WHERE s.id IS NULL;
DELETE ta
FROM teacher_assignments ta
    LEFT JOIN subjects s ON ta.subject_id = s.id
WHERE s.id IS NULL;