-- sql/data_v2_real_subjects.sql
-- Clear existing subjects
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE subjects;
SET FOREIGN_KEY_CHECKS = 1;
-- Form 1 Subjects
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH-F1', 'Mathematics', 4, 'Form 1'),
    ('ENG-F1', 'English Language', 3, 'Form 1'),
    ('SOM-F1', 'Somali Language', 3, 'Form 1'),
    ('ARA-F1', 'Arabic Language', 3, 'Form 1'),
    ('ISL-F1', 'Islamic Studies', 2, 'Form 1'),
    ('BIO-F1', 'Biology', 3, 'Form 1'),
    ('CHEM-F1', 'Chemistry', 3, 'Form 1'),
    ('PHY-F1', 'Physics', 3, 'Form 1'),
    ('GEO-F1', 'Geography', 2, 'Form 1'),
    ('HIST-F1', 'History', 2, 'Form 1');
-- Form 2 Subjects
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH-F2', 'Mathematics', 4, 'Form 2'),
    ('ENG-F2', 'English Language', 3, 'Form 2'),
    ('SOM-F2', 'Somali Language', 3, 'Form 2'),
    ('ARA-F2', 'Arabic Language', 3, 'Form 2'),
    ('ISL-F2', 'Islamic Studies', 2, 'Form 2'),
    ('BIO-F2', 'Biology', 3, 'Form 2'),
    ('CHEM-F2', 'Chemistry', 3, 'Form 2'),
    ('PHY-F2', 'Physics', 3, 'Form 2'),
    ('GEO-F2', 'Geography', 2, 'Form 2'),
    ('HIST-F2', 'History', 2, 'Form 2');
-- Form 3 Subjects
INSERT INTO subjects (
        subject_code,
        subject_name,
        credits,
        class_level,
        stream
    )
VALUES ('MATH-F3', 'Mathematics', 4, 'Form 3', 'Core'),
    (
        'ENG-F3',
        'English Language',
        3,
        'Form 3',
        'Core'
    ),
    ('SOM-F3', 'Somali Language', 3, 'Form 3', 'Core'),
    ('ARA-F3', 'Arabic Language', 3, 'Form 3', 'Core'),
    ('ISL-F3', 'Islamic Studies', 2, 'Form 3', 'Core'),
    (
        'BIO-F3',
        'Biology',
        3,
        'Form 3',
        'Science,General'
    ),
    (
        'CHEM-F3',
        'Chemistry',
        3,
        'Form 3',
        'Science,General'
    ),
    (
        'PHY-F3',
        'Physics',
        3,
        'Form 3',
        'Science,General'
    ),
    (
        'GEO-F3',
        'Geography',
        2,
        'Form 3',
        'Arts,General'
    ),
    (
        'HIST-F3',
        'History',
        2,
        'Form 3',
        'Arts,General'
    ),
    (
        'ICT-F3',
        'ICT (Computer Studies)',
        2,
        'Form 3',
        'Core'
    ),
    (
        'HBUS-F3',
        'Business Study',
        2,
        'Form 3',
        'Arts,General'
    ),
    ('AGRI-F3', 'Agriculture', 2, 'Form 3', 'Science'),
    (
        'GOV-F3',
        'Government and Politics',
        2,
        'Form 3',
        'Arts'
    );
-- Form 4 Subjects
INSERT INTO subjects (
        subject_code,
        subject_name,
        credits,
        class_level,
        stream
    )
VALUES ('MATH-F4', 'Mathematics', 4, 'Form 4', 'Core'),
    (
        'ENG-F4',
        'English Language',
        3,
        'Form 4',
        'Core'
    ),
    ('SOM-F4', 'Somali Language', 3, 'Form 4', 'Core'),
    ('ARA-F4', 'Arabic Language', 3, 'Form 4', 'Core'),
    ('ISL-F4', 'Islamic Studies', 2, 'Form 4', 'Core'),
    (
        'BIO-F4',
        'Biology',
        3,
        'Form 4',
        'Science,General'
    ),
    (
        'CHEM-F4',
        'Chemistry',
        3,
        'Form 4',
        'Science,General'
    ),
    (
        'PHY-F4',
        'Physics',
        3,
        'Form 4',
        'Science,General'
    ),
    (
        'GEO-F4',
        'Geography',
        2,
        'Form 4',
        'Arts,General'
    ),
    (
        'HIST-F4',
        'History',
        2,
        'Form 4',
        'Arts,General'
    ),
    (
        'ICT-F4',
        'ICT (Computer Studies)',
        2,
        'Form 4',
        'Core'
    ),
    (
        'HBUS-F4',
        'Business Study',
        2,
        'Form 4',
        'Arts,General'
    ),
    ('AGRI-F4', 'Agriculture', 2, 'Form 4', 'Science'),
    (
        'GOV-F4',
        'Government and Politics',
        2,
        'Form 4',
        'Arts'
    );
-- Grade 5 Subjects
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH-G5', 'Mathematics', 5, 'Grade 5'),
    ('ENG-G5', 'English Language', 4, 'Grade 5'),
    ('SOM-G5', 'Somali Language', 4, 'Grade 5'),
    ('ARA-G5', 'Arabic Language', 3, 'Grade 5'),
    ('ISL-G5', 'Islamic Studies', 2, 'Grade 5'),
    ('SCI-G5', 'Science', 3, 'Grade 5'),
    ('SOC-G5', 'Social Studies', 3, 'Grade 5');
-- Grade 6 Subjects
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH-G6', 'Mathematics', 5, 'Grade 6'),
    ('ENG-G6', 'English Language', 4, 'Grade 6'),
    ('SOM-G6', 'Somali Language', 4, 'Grade 6'),
    ('ARA-G6', 'Arabic Language', 3, 'Grade 6'),
    ('ISL-G6', 'Islamic Studies', 2, 'Grade 6'),
    ('SCI-G6', 'Science', 3, 'Grade 6'),
    ('SOC-G6', 'Social Studies', 3, 'Grade 6');
-- Grade 7 Subjects
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH-G7', 'Mathematics', 5, 'Grade 7'),
    ('ENG-G7', 'English Language', 4, 'Grade 7'),
    ('SOM-G7', 'Somali Language', 4, 'Grade 7'),
    ('ARA-G7', 'Arabic Language', 3, 'Grade 7'),
    ('ISL-G7', 'Islamic Studies', 2, 'Grade 7'),
    ('SCI-G7', 'Science', 3, 'Grade 7'),
    ('SOC-G7', 'Social Studies', 3, 'Grade 7'),
    ('ICT-G7', 'ICT (Computer Studies)', 2, 'Grade 7');
-- Grade 8 Subjects
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH-G8', 'Mathematics', 5, 'Grade 8'),
    ('ENG-G8', 'English Language', 4, 'Grade 8'),
    ('SOM-G8', 'Somali Language', 4, 'Grade 8'),
    ('ARA-G8', 'Arabic Language', 3, 'Grade 8'),
    ('ISL-G8', 'Islamic Studies', 2, 'Grade 8'),
    ('SCI-G8', 'Science', 3, 'Grade 8'),
    ('SOC-G8', 'Social Studies', 3, 'Grade 8'),
    ('ICT-G8', 'ICT (Computer Studies)', 2, 'Grade 8');