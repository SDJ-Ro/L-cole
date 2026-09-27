-- =========================================================================
-- L'ÉCOLE — CALENDAR MODULE SCHEMA (v3 — scope_type/scope_id design)
-- Run AFTER schema.sql and academic_schema.sql.
-- =========================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- 1. CALENDAR EVENTS
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS calendar_events;
CREATE TABLE calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,

    scope_type ENUM('schoolwide', 'grade', 'class', 'club', 'sport') NOT NULL,
    scope_id VARCHAR(50) NULL,

    title VARCHAR(150) NOT NULL,
    details VARCHAR(500) NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Academic',
    audience JSON NOT NULL DEFAULT ('["All"]'),

    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,

    created_by_account_id INT NOT NULL,
    author_role VARCHAR(50) NOT NULL DEFAULT 'admin',
    author_name VARCHAR(150) NULL,

    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_calendar_scope (scope_type, scope_id),
    INDEX idx_calendar_scope_composite (scope_type, scope_id, event_date),
    INDEX idx_calendar_event_date (event_date),
    INDEX idx_calendar_creator (created_by_account_id),
    INDEX idx_calendar_deleted (deleted_at),

    CONSTRAINT fk_calendar_creator FOREIGN KEY (created_by_account_id)
        REFERENCES user_accounts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 1B. CALENDAR EVENT SCOPES (Multi-Scope Mapping)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS calendar_event_scopes;
CREATE TABLE calendar_event_scopes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    scope_type ENUM('schoolwide', 'grade', 'class', 'club', 'sport') NOT NULL,
    scope_id VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_scope_lookup (scope_type, scope_id),
    INDEX idx_event_lookup (event_id),
    CONSTRAINT fk_ces_event FOREIGN KEY (event_id)
        REFERENCES calendar_events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 2. CLUBS — academic/hobby clubs. TIC only (a real teacher).
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS clubs;
CREATE TABLE clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    category VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS club_teachers;
CREATE TABLE club_teachers (
    club_id INT PRIMARY KEY,
    teacher_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_club_teacher_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    CONSTRAINT fk_club_teacher_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS club_tic_history;
CREATE TABLE club_tic_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    teacher_id INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    ended_at TIMESTAMP NULL,
    CONSTRAINT fk_ctich_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    CONSTRAINT fk_ctich_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    CONSTRAINT fk_ctich_actor FOREIGN KEY (assigned_by) REFERENCES user_accounts(id) ON DELETE SET NULL,
    INDEX idx_ctich_club (club_id, ended_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS club_members;
CREATE TABLE club_members (
    club_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('active', 'pending', 'inactive') NOT NULL DEFAULT 'active',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (club_id, student_id),
    CONSTRAINT fk_club_member_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    CONSTRAINT fk_club_member_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 2b. SPORTS — separate table, per the real UI: a sport has a Teacher-in-
--     Charge (a real teacher) PLUS a Coach/Instructor.
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS sports;
CREATE TABLE sports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    category VARCHAR(100) NULL,
    coach_name VARCHAR(150) NULL,
    coach_title VARCHAR(150) NULL,
    coach_email VARCHAR(150) NULL,
    coach_phone VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS sport_teachers;
CREATE TABLE sport_teachers (
    sport_id INT PRIMARY KEY,
    teacher_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sport_teacher_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    CONSTRAINT fk_sport_teacher_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS sport_tic_history;
CREATE TABLE sport_tic_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sport_id INT NOT NULL,
    teacher_id INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    ended_at TIMESTAMP NULL,
    CONSTRAINT fk_stich_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    CONSTRAINT fk_stich_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    CONSTRAINT fk_stich_actor FOREIGN KEY (assigned_by) REFERENCES user_accounts(id) ON DELETE SET NULL,
    INDEX idx_stich_sport (sport_id, ended_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS sport_members;
CREATE TABLE sport_members (
    sport_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('active', 'pending', 'inactive') NOT NULL DEFAULT 'active',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (sport_id, student_id),
    CONSTRAINT fk_sport_member_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    CONSTRAINT fk_sport_member_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 6. TEACHER-ROLE VIEW — derived only, never stored, always live.
-- -------------------------------------------------------------------------
CREATE OR REPLACE VIEW teacher_roles AS
SELECT
    t.id AS teacher_id,
    t.full_name,
    CASE
        WHEN ct.teacher_id IS NOT NULL AND (cl.club_count > 0 OR sp.sport_count > 0) THEN 'Class Teacher & TIC'
        WHEN ct.teacher_id IS NOT NULL THEN 'Class Teacher'
        WHEN cl.club_count > 0 OR sp.sport_count > 0 THEN 'Teacher-in-Charge'
        ELSE 'Subject Teacher'
    END AS role_label,
    ct.class_id,
    COALESCE(cl.club_count, 0) AS club_count,
    COALESCE(sp.sport_count, 0) AS sport_count
FROM teachers t
LEFT JOIN class_teachers ct ON ct.teacher_id = t.id
LEFT JOIN (
    SELECT teacher_id, COUNT(*) AS club_count FROM club_teachers GROUP BY teacher_id
) cl ON cl.teacher_id = t.id
LEFT JOIN (
    SELECT teacher_id, COUNT(*) AS sport_count FROM sport_teachers GROUP BY teacher_id
) sp ON sp.teacher_id = t.id;

-- -------------------------------------------------------------------------
-- 7. HARD CONSTRAINT — one Class Teacher assignment per teacher
-- -------------------------------------------------------------------------
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = 'l_ecole' AND table_name = 'class_teachers' AND index_name = 'uq_ct_teacher');
SET @sql := IF(@exist = 0, 'ALTER TABLE class_teachers ADD CONSTRAINT uq_ct_teacher UNIQUE (teacher_id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -------------------------------------------------------------------------
-- 8. BACKFILLS (if legacy teacher_name column exists)
-- -------------------------------------------------------------------------
SET @has_ct_tn := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = 'l_ecole' AND table_name = 'class_teachers' AND column_name = 'teacher_name');
SET @sql_ct_bf := IF(@has_ct_tn > 0, 'UPDATE class_teachers ct JOIN teachers t ON t.full_name = ct.teacher_name SET ct.teacher_id = t.id WHERE ct.teacher_id IS NULL', 'SELECT 1');
PREPARE stmt_bf1 FROM @sql_ct_bf; EXECUTE stmt_bf1; DEALLOCATE PREPARE stmt_bf1;

SET @has_cst_tn := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = 'l_ecole' AND table_name = 'class_subject_teachers' AND column_name = 'teacher_name');
SET @sql_cst_bf := IF(@has_cst_tn > 0, 'UPDATE class_subject_teachers cst JOIN teachers t ON t.full_name = cst.teacher_name SET cst.teacher_id = t.id WHERE cst.teacher_id IS NULL', 'SELECT 1');
PREPARE stmt_bf2 FROM @sql_cst_bf; EXECUTE stmt_bf2; DEALLOCATE PREPARE stmt_bf2;

-- -------------------------------------------------------------------------
-- 9. SEED CLUBS & SPORTS
-- -------------------------------------------------------------------------
INSERT INTO clubs (id, name, category) VALUES
(2, 'Model United Nations & Debating', 'Debating & Public Speaking'),
(5, 'Drama & Theatrical Society', 'Arts & Culture'),
(6, 'Robotics & AI Society', 'Technology')
ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category);

INSERT INTO sports (id, name, category, coach_name, coach_title, coach_email, coach_phone) VALUES
(1, 'Varsity Football Club', 'Athletics', 'Coach Dinesh', 'UEFA B Licensed', 'dinesh@lecole.edu', '+94 77 123 4567'),
(3, 'Senior Cricket Club', 'Athletics', 'Coach Roshan', 'Level 3 Certified', 'roshan@lecole.edu', '+94 77 234 5678'),
(4, 'Aquatic Club & Swimming', 'Aquatics', 'Coach Nishantha', 'FINA Certified', 'nishantha@lecole.edu', '+94 77 345 6789')
ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category);

-- Assign Alex Benjamin (teachers.id = 1) as TIC of Football (sport 1), Cricket (sport 3), and MUN (club 2)
INSERT INTO sport_teachers (sport_id, teacher_id) VALUES
(1, 1),
(3, 1)
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

INSERT INTO club_teachers (club_id, teacher_id) VALUES
(2, 1)
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Assign Class Teacher for Alex Benjamin: Class 1 (6-A)
INSERT INTO class_teachers (class_id, teacher_id) VALUES
(1, 1)
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Seed memberships for student 1 (Nethmi Perera)
INSERT INTO sport_members (sport_id, student_id, status) VALUES
(1, 1, 'active'),
(4, 1, 'active')
ON DUPLICATE KEY UPDATE status = VALUES(status);

INSERT INTO club_members (club_id, student_id, status) VALUES
(2, 1, 'active')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- -------------------------------------------------------------------------
-- 10. SEED CALENDAR EVENTS (v3 specification)
-- -------------------------------------------------------------------------
INSERT INTO calendar_events (id, scope_type, scope_id, title, details, category, audience, event_date, start_time, end_time, created_by_account_id, author_role, author_name) VALUES
(1, 'schoolwide', NULL, 'Mathematics examination', 'Grades 6–8 · Respective classrooms', 'Academic', '["All"]', '2026-06-17', '08:30:00', '10:30:00', 1, 'admin', 'Admin Office'),
(2, 'schoolwide', NULL, 'Science examination', 'Grades 6–11 · Respective classrooms', 'Academic', '["All"]', '2026-06-19', '08:30:00', '10:30:00', 1, 'admin', 'Admin Office'),
(3, 'schoolwide', NULL, 'History examination', 'Main hall and classrooms', 'Academic', '["All"]', '2026-06-20', '09:00:00', '11:00:00', 1, 'admin', 'Admin Office'),
(4, 'schoolwide', NULL, 'Inter-School Championship', 'Official divisional match', 'Extracurricular', '["All"]', '2026-06-24', '14:00:00', '18:00:00', 1, 'admin', 'Admin Office'),
(5, 'schoolwide', NULL, 'Make-up examination session', 'Hall 3 · Registered students only', 'Academic', '["All"]', '2026-06-26', '08:30:00', '10:30:00', 1, 'admin', 'Admin Office'),
(6, 'schoolwide', NULL, 'Senior stream papers', 'Grades 12–13 · Senior examination hall', 'Academic', '["All"]', '2026-06-27', '08:30:00', '11:30:00', 1, 'admin', 'Admin Office'),
(7, 'class', 1, 'Term 2 examinations begin', 'Class 6-A Morning Session', 'Academic', '["Students", "Parents"]', '2026-06-17', '08:30:00', '10:30:00', 2, 'teacher', 'Mr. Alex Benjamin'),
(8, 'sport', 1, 'Regular Team Training', 'Weekly training on main pitch', 'Extracurricular', '["Students", "Parents"]', '2026-06-17', '15:30:00', '17:30:00', 2, 'teacher', 'Mr. Alex Benjamin'),
(9, 'sport', 3, 'Weekly Team Training', 'Main cricket nets · Full whites', 'Extracurricular', '["Students", "Parents"]', '2026-06-17', '15:30:00', '17:30:00', 2, 'teacher', 'Mr. Alex Benjamin'),
(10, 'club', 2, 'Debate Preparation Session', 'Library Conference Room', 'Extracurricular', '["Students", "Parents"]', '2026-06-18', '14:00:00', '16:00:00', 2, 'teacher', 'Mr. Alex Benjamin'),
(11, 'grade', 'g6', 'Grade 6 Colombo Museum Field Trip', 'Educational excursion for all Grade 6 classes', 'Extracurricular', '["Students", "Parents", "Teachers"]', '2026-06-22', '08:30:00', '14:30:00', 1, 'admin', 'Admin Office'),
(12, 'schoolwide', NULL, 'Term 2 Staff Briefing', 'All academic and coaching staff · Main Staffroom', 'Academic', '["Teachers", "Management"]', '2026-06-16', '14:00:00', '15:30:00', 1, 'admin', 'Admin Office')
ON DUPLICATE KEY UPDATE 
    scope_type = VALUES(scope_type),
    scope_id = VALUES(scope_id),
    title = VALUES(title),
    details = VALUES(details),
    category = VALUES(category),
    audience = VALUES(audience),
    event_date = VALUES(event_date),
    start_time = VALUES(start_time),
    end_time = VALUES(end_time),
    author_role = VALUES(author_role),
    author_name = VALUES(author_name);

SET FOREIGN_KEY_CHECKS = 1;
