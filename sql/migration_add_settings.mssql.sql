-- migration_add_settings.mssql.sql
-- MSSQL-friendly migration: create settings table if not exists
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='settings' AND xtype='U')
BEGIN
    CREATE TABLE settings (
        [key] NVARCHAR(100) PRIMARY KEY,
        [value] NVARCHAR(MAX) NULL
    );
END

-- Insert default
IF NOT EXISTS (SELECT 1 FROM settings WHERE [key] = 'site_name')
BEGIN
    INSERT INTO settings ([key], [value]) VALUES ('site_name', 'Student Result Management System');
END
