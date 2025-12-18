-- migration_add_avatars.mssql.sql
-- MSSQL-friendly migration: add columns if they do not exist
IF COL_LENGTH('dbo.admins', 'avatar') IS NULL
    ALTER TABLE dbo.admins ADD avatar NVARCHAR(255) NULL;
IF COL_LENGTH('dbo.students', 'avatar') IS NULL
    ALTER TABLE dbo.students ADD avatar NVARCHAR(255) NULL;
