-- migration_v3_dynamic_home.sql
-- Adds settings for home page dynamic content
INSERT INTO settings (`key`, `value`)
VALUES (
        'hero_title',
        'Revolutionizing Student Success One Grade at a Time.'
    ),
    (
        'hero_subtitle',
        'Empower your institution with a high-performance management system for tracking results, managing students, and delivering academic excellence.'
    ),
    ('feature_1_title', 'Dynamic Reporting'),
    (
        'feature_1_desc',
        'Generate instantly readable result slips and progress reports for every student.'
    ),
    ('feature_2_title', 'Bulk Data Entry'),
    (
        'feature_2_desc',
        'Save hours with our optimized bulk resulting tools for teachers and staff.'
    ),
    ('feature_3_title', 'Secure by Design'),
    (
        'feature_3_desc',
        'Role-based access control and modern security practices protect your data.'
    ) ON DUPLICATE KEY
UPDATE `value` =
VALUES(`value`);