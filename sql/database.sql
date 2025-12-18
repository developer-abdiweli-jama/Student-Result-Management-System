-- Reset existing tables
SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW IF EXISTS student_result_details;
DROP TABLE IF EXISTS settings,
teacher_assignments,
results,
teachers,
students,
subjects,
admins;
SET FOREIGN_KEY_CHECKS = 1;
-- Create database
CREATE DATABASE IF NOT EXISTS srmis;
USE srmis;
-- Admins table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reg_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    year_of_study INT NOT NULL CHECK (
        year_of_study BETWEEN 1 AND 5
    ),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reg_no (reg_no),
    INDEX idx_year (year_of_study)
);
-- Subjects table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    credits DECIMAL(3, 2) DEFAULT 1.0,
    -- Kept for 'weight' if needed, or can be ignored
    class_level VARCHAR(20) NOT NULL COMMENT 'e.g. Grade 9, Grade 10, Form 1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subject_code (subject_code)
);
-- Teachers table (new user category)
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reg_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teacher_reg_no (reg_no)
);
-- Map teachers to subjects and (optionally) class/year/semester they teach
CREATE TABLE teacher_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_level VARCHAR(20) NULL,
    -- Specific class level this teacher is assigned to for this subject
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    -- For approval workflow
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_teacher_assignment (teacher_id, subject_id, class_level),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_subject_id (subject_id)
);
-- Results table
CREATE TABLE results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    -- who entered the result (nullable if entered by admin or migrated)
    entered_by_teacher_id INT NULL,
    marks_obtained DECIMAL(5, 2) NOT NULL CHECK (
        marks_obtained BETWEEN 0 AND 100
    ),
    grade CHAR(2),
    grade_point DECIMAL(3, 2),
    exam_date DATE,
    term INT NOT NULL CHECK (
        term BETWEEN 1 AND 3
    ),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (entered_by_teacher_id) REFERENCES teachers(id) ON DELETE
    SET NULL,
        UNIQUE KEY unique_student_subject_term (student_id, subject_id, term),
        INDEX idx_student_id (student_id),
        INDEX idx_subject_id (subject_id),
        INDEX idx_term (term),
        INDEX idx_grade (grade)
);
-- Insert default admin (password = admin123)
INSERT INTO admins (username, password_hash)
VALUES (
        'admin',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86'
    );
-- Insert sample subjects (restored full list)
INSERT INTO subjects (subject_code, subject_name, credits, class_level)
VALUES ('MATH101', 'Mathematics', 4.0, 'Form 1'),
    ('PHY101', 'Physics', 4.0, 'Form 1'),
    ('CHEM101', 'Chemistry', 3.0, 'Form 1'),
    ('ENG101', 'English', 3.0, 'Form 1'),
    ('BIO101', 'Biology', 3.0, 'Form 1'),
    ('MATH102', 'Mathematics', 4.0, 'Form 2'),
    ('PHY102', 'Physics', 4.0, 'Form 2'),
    ('ENG102', 'English', 3.0, 'Form 2'),
    ('HIST101', 'History', 3.0, 'Form 1'),
    ('GEO101', 'Geography', 3.0, 'Form 1');
-- Insert sample students (all password = admin123, hashed)
INSERT INTO students (reg_no, name, password_hash, year_of_study)
VALUES (
        'SRM23101',
        'John Smith',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        2
    ),
    (
        'SRM23102',
        'Emma Johnson',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        2
    ),
    (
        'SRM23103',
        'Michael Brown',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        1
    ),
    (
        'SRM23104',
        'Sarah Davis',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        3
    ),
    (
        'SRM23105',
        'David Wilson',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        2
    ),
    (
        'SRM23106',
        'Lisa Anderson',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        1
    ),
    (
        'SRM23107',
        'Robert Garcia',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        3
    ),
    (
        'SRM23108',
        'Jennifer Martinez',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86',
        2
    );
-- Insert sample teachers (password = admin123 hashed)
INSERT INTO teachers (reg_no, name, password_hash)
VALUES (
        'TCH1001',
        'Alice Thompson',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86'
    ),
    (
        'TCH1002',
        'Benjamin Carter',
        '$2y$10$sAnWdEoi.BK.dgEQ7v8ENOfU6mtkgiDJ6XbW21XXSp0sMc/0v/B86'
    );
-- Insert sample results
INSERT INTO results (
        student_id,
        subject_id,
        marks_obtained,
        grade,
        grade_point,
        exam_date,
        term
    )
VALUES (1, 1, 85.5, 'A-', 3.7, '2024-04-15', 1),
    (1, 2, 78.0, 'B', 3.0, '2024-04-16', 1),
    (1, 3, 92.0, 'A', 4.0, '2024-04-17', 1),
    (2, 1, 88.0, 'A-', 3.7, '2024-04-15', 1),
    (2, 2, 82.5, 'B+', 3.3, '2024-04-16', 1),
    (2, 3, 76.0, 'B', 3.0, '2024-04-17', 1),
    (3, 1, 65.0, 'C+', 2.3, '2024-04-15', 1),
    (3, 2, 58.0, 'D', 1.0, '2024-04-16', 1),
    (3, 3, 72.0, 'B-', 2.7, '2024-04-17', 1);
-- Example teacher assignments: assign teachers to subjects and optionally year/semester
INSERT INTO teacher_assignments (teacher_id, subject_id, class_level, status)
VALUES (1, 1, 'Form 1', 'approved'),
    (1, 6, 'Form 2', 'approved'),
    (2, 4, 'Form 1', 'approved'),
    (2, 8, 'Form 2', 'pending');
-- Create view for student results
CREATE VIEW student_result_details AS
SELECT r.id,
    s.reg_no,
    s.name AS student_name,
    s.year_of_study,
    sub.subject_code,
    sub.subject_name,
    sub.credits,
    r.marks_obtained,
    r.grade,
    r.grade_point,
    r.term,
    r.exam_date
FROM results r
    JOIN students s ON r.student_id = s.id
    JOIN subjects sub ON r.subject_id = sub.id;
-- ==========================================
-- ADDED MIGRATIONS (Avatars & Settings)
-- ==========================================
-- 1. Add Avatar columns
-- Check if columns exist before adding equivalent logic (simple ALTERs for now, easy to run on empty DB)
ALTER TABLE admins
ADD COLUMN avatar VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE students
ADD COLUMN avatar VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE teachers
ADD COLUMN avatar VARCHAR(255) NULL DEFAULT NULL;
-- 2. Add Settings table
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Insert default site_name
INSERT INTO settings (`key`, `value`)
SELECT 'site_name',
    'Student Result Management System'
FROM DUAL
WHERE NOT EXISTS (
        SELECT 1
        FROM settings
        WHERE `key` = 'site_name'
    );