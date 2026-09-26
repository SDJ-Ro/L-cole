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

    scope_type ENUM('schoolwide', 'class', 'club', 'sport') NOT NULL,
    scope_id INT NULL,

    title VARCHAR(150) NOT NULL,
    details VARCHAR(500) NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Academic',

    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,

    created_by_account_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_calendar_scope (scope_type, scope_id),
    INDEX idx_calendar_event_date (event_date),
    INDEX idx_calendar_creator (created_by_account_id),

    CONSTRAINT fk_calendar_creator FOREIGN KEY (created_by_account_id)
        REFERENCES user_accounts(id) ON DELETE RESTRICT
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
-- 8. BACKFILLS
-- -------------------------------------------------------------------------
UPDATE class_teachers ct
JOIN teachers t ON t.full_name = ct.teacher_name
SET ct.teacher_id = t.id
WHERE ct.teacher_id IS NULL;

UPDATE class_subject_teachers cst
JOIN teachers t ON t.full_name = cst.teacher_name
SET cst.teacher_id = t.id
WHERE cst.teacher_id IS NULL;

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
INSERT INTO class_teachers (class_id, teacher_id, teacher_name) VALUES
(1, 1, 'Mr. Alex Benjamin')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id), teacher_name = VALUES(teacher_name);

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
INSERT INTO calendar_events (id, scope_type, scope_id, title, details, category, event_date, start_time, end_time, created_by_account_id) VALUES
(1, 'schoolwide', NULL, 'Mathematics examination', 'Grades 6–8 · Respective classrooms', 'Academic', '2026-06-17', '08:30:00', '10:30:00', 1),
(2, 'schoolwide', NULL, 'Science examination', 'Grades 6–11 · Respective classrooms', 'Academic', '2026-06-19', '08:30:00', '10:30:00', 1),
(3, 'schoolwide', NULL, 'History examination', 'Main hall and classrooms', 'Academic', '2026-06-20', '09:00:00', '11:00:00', 1),
(4, 'schoolwide', NULL, 'Inter-School Championship', 'Official divisional match', 'Extracurricular', '2026-06-24', '14:00:00', '18:00:00', 1),
(5, 'schoolwide', NULL, 'Make-up examination session', 'Hall 3 · Registered students only', 'Academic', '2026-06-26', '08:30:00', '10:30:00', 1),
(6, 'schoolwide', NULL, 'Senior stream papers', 'Grades 12–13 · Senior examination hall', 'Academic', '2026-06-27', '08:30:00', '11:30:00', 1),
(7, 'class', 1, 'Term 2 examinations begin', 'Class 6-A Morning Session', 'Academic', '2026-06-17', '08:30:00', '10:30:00', 1),
(8, 'sport', 1, 'Regular Team Training', 'Weekly training on main pitch', 'Extracurricular', '2026-06-17', '15:30:00', '17:30:00', 1),
(9, 'sport', 3, 'Weekly Team Training', 'Main cricket nets · Full whites', 'Extracurricular', '2026-06-17', '15:30:00', '17:30:00', 1),
(10, 'club', 2, 'Debate Preparation Session', 'Library Conference Room', 'Extracurricular', '2026-06-18', '14:00:00', '16:00:00', 1)
ON DUPLICATE KEY UPDATE 
    scope_type = VALUES(scope_type),
    scope_id = VALUES(scope_id),
    title = VALUES(title),
    details = VALUES(details),
    category = VALUES(category),
    event_date = VALUES(event_date),
    start_time = VALUES(start_time),
    end_time = VALUES(end_time);

SET FOREIGN_KEY_CHECKS = 1;
