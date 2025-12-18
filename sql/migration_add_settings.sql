-- migration_add_settings.sql
-- Creates a settings table for key/value pairs

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'settings')
BEGIN
    CREATE TABLE settings (
        [key] VARCHAR(100) PRIMARY KEY,
        [value] NVARCHAR(MAX) NULL
    );
END

-- Insert defaults
MERGE INTO settings AS target
USING (SELECT 'site_name' AS [key], 'Student Result Management System' AS [value]) AS source
ON (target.[key] = source.[key])
WHEN MATCHED THEN
    UPDATE SET [value] = source.[value]
WHEN NOT MATCHED THEN
    INSERT ([key], [value]) VALUES (source.[key], source.[value]);
