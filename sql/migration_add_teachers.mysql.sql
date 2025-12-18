-- Migration: add teachers and teacher_assignments (MySQL)
-- Run this file against the srmis database

SET FOREIGN_KEY_CHECKS = 0;

-- Create teachers table if not exists
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reg_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teacher_reg_no (reg_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create teacher_assignments table if not exists
CREATE TABLE IF NOT EXISTS teacher_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    year_of_study INT NULL,
    semester INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_teacher_subject_year (teacher_id, subject_id, year_of_study, semester),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_subject_id (subject_id),
    CONSTRAINT fk_ta_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Make the following operations idempotent for environments where the column or constraint
-- may already exist. Use information_schema and prepared statements to only run
-- the ALTER statements when necessary. Some MySQL versions don't support ADD COLUMN IF NOT EXISTS.
-- First, add column if missing using a conditional prepared statement.
SET @col_check_stmt = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE results ADD COLUMN entered_by_teacher_id INT NULL',
        'SELECT 1')
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'results' AND COLUMN_NAME = 'entered_by_teacher_id'
);
PREPARE col_stmt FROM @col_check_stmt;
EXECUTE col_stmt;
DEALLOCATE PREPARE col_stmt;

-- Then add the foreign key constraint if missing.
SET @fk_check_stmt = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE results ADD CONSTRAINT fk_results_teacher FOREIGN KEY (entered_by_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL',
        'SELECT 1')
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'results' AND CONSTRAINT_NAME = 'fk_results_teacher'
);
PREPARE fk_stmt FROM @fk_check_stmt;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

SET FOREIGN_KEY_CHECKS = 1;
