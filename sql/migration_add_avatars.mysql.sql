-- migration_add_avatars.mysql.sql
-- MySQL: Adds avatar columns to admins and students

ALTER TABLE admins ADD avatar VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE students ADD avatar VARCHAR(255) NULL DEFAULT NULL;
