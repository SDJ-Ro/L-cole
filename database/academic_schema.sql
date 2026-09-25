-- =========================================================================
-- L'ÉCOLE — ACADEMIC MODULE SCHEMA & SEEDS
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. CURRICULUM GROUPS
CREATE TABLE IF NOT EXISTS curriculum_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    range_label VARCHAR(50) NOT NULL UNIQUE,       -- e.g. 'Years 6–9', 'Years 10–11'
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CURRICULUM GROUP SUBJECTS
CREATE TABLE IF NOT EXISTS curriculum_group_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cgs_group FOREIGN KEY (group_id) 
        REFERENCES curriculum_groups(id) ON DELETE CASCADE,
    UNIQUE KEY uq_group_subject (group_id, subject_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. GRADES
CREATE TABLE IF NOT EXISTS grades (
    id VARCHAR(20) PRIMARY KEY,                    -- e.g. 'g6', 'g7', 'g10'
    name VARCHAR(50) NOT NULL UNIQUE,              -- e.g. 'Grade 6'
    group_id INT NULL,                             -- Foreign key to curriculum group
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_grades_group FOREIGN KEY (group_id) 
        REFERENCES curriculum_groups(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CLASSES
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id VARCHAR(20) NOT NULL,
    section_name VARCHAR(20) NOT NULL,             -- e.g. '6-A', '6-B', 'Lily'
    student_count INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_classes_grade FOREIGN KEY (grade_id) 
        REFERENCES grades(id) ON DELETE CASCADE,
    UNIQUE KEY uq_grade_section (grade_id, section_name),
    INDEX idx_classes_grade (grade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 5. CLASS TEACHERS (1 teacher to 1 class exclusively)
CREATE TABLE IF NOT EXISTS class_teachers (
    class_id INT PRIMARY KEY,
    teacher_name VARCHAR(150) NOT NULL,
    teacher_id INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ct_class FOREIGN KEY (class_id) 
        REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. CLASS SUBJECT TEACHERS
CREATE TABLE IF NOT EXISTS class_subject_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    teacher_name VARCHAR(150) NOT NULL,
    teacher_id INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cst_class FOREIGN KEY (class_id) 
        REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY uq_class_subject (class_id, subject_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Ensure students table columns grade and class_section allow NULL
ALTER TABLE students MODIFY COLUMN grade VARCHAR(20) NULL;
ALTER TABLE students MODIFY COLUMN class_section VARCHAR(20) NULL;

SET FOREIGN_KEY_CHECKS = 1;
