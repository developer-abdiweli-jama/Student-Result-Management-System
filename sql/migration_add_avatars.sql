-- migration_add_avatars.sql
-- Adds avatar columns to admins and students

ALTER TABLE admins ADD avatar NVARCHAR(255) NULL;
ALTER TABLE students ADD avatar NVARCHAR(255) NULL;
