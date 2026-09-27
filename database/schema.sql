-- =========================================================================
-- L'ÉCOLE — RELATIONAL DATABASE SCHEMA (UI-MATCHED)
-- =========================================================================
-- Target DBMS: MySQL 8.0+ / MariaDB 10.5+
-- Storage Engine: InnoDB
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- 1. CENTRAL AUTHENTICATION SPINE
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS user_accounts;
CREATE TABLE user_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(191) NOT NULL UNIQUE,          -- Official email for staff/parents, Index No for students
    role ENUM('admin', 'management', 'teacher', 'parent', 'student') NOT NULL,
    password_hash VARCHAR(255) NULL,                  -- NULL while PENDING activation
    activation_status ENUM('PENDING', 'ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'PENDING',
    activated_at DATETIME NULL,
    first_login_required TINYINT(1) NOT NULL DEFAULT 0, -- 1 for students who must change temporary password
    failed_login_count INT NOT NULL DEFAULT 0,
    lock_expires_at DATETIME NULL,                    -- Soft lockout timestamp (15-30 min window)
    locked_at DATETIME NULL,                          -- Hard lockout timestamp (cleared by Admin)
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role),
    INDEX idx_user_status (activation_status),
    INDEX idx_user_lock (lock_expires_at, locked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 2. STUDENT PROFILES (1:1 with Enrollment Form)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL UNIQUE,
    index_no VARCHAR(50) NOT NULL UNIQUE,             -- e.g. STU-2026-0001
    full_name VARCHAR(150) NOT NULL,
    first_name VARCHAR(75) NOT NULL,
    last_name VARCHAR(75) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Female', 'Male', 'Prefer not to say') NOT NULL DEFAULT 'Female',
    national_id VARCHAR(30) NULL,                     -- Optional NIC if issued
    birth_certificate_number VARCHAR(50) NOT NULL,
    grade VARCHAR(20) NOT NULL,                       -- e.g. 'Grade 6'
    class_section VARCHAR(10) NOT NULL,               -- e.g. '6-A'
    religion VARCHAR(50) NULL DEFAULT 'Buddhism',
    home_address TEXT NOT NULL,
    admission_date DATE NOT NULL,
    nationality VARCHAR(50) NOT NULL DEFAULT 'Sri Lankan',
    educational_zone VARCHAR(50) NOT NULL DEFAULT 'Colombo',
    district VARCHAR(50) NOT NULL DEFAULT 'Colombo',
    province VARCHAR(50) NOT NULL DEFAULT 'Western',
    previous_school VARCHAR(150) NULL,
    blood_group VARCHAR(15) NOT NULL DEFAULT 'Not provided',
    photo_url VARCHAR(255) NULL,                      -- Uploaded profile picture path
    medical_notes TEXT NULL,                          -- Asthma, allergies, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE CASCADE,
    INDEX idx_student_grade_class (grade, class_section)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 3. TEACHER PROFILES (1:1 with Teacher Form)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS teachers;
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL UNIQUE,
    staff_id VARCHAR(50) NOT NULL UNIQUE,             -- e.g. TEA-2026-0001
    full_name VARCHAR(150) NOT NULL,
    first_name VARCHAR(75) NOT NULL,
    last_name VARCHAR(75) NOT NULL,
    nic VARCHAR(30) NOT NULL,
    date_of_birth DATE NOT NULL,
    office_address VARCHAR(255) NULL,
    phone VARCHAR(30) NOT NULL,
    personal_email VARCHAR(191) NOT NULL,             -- Personal inbox where activation invite is sent
    institutional_email VARCHAR(191) NOT NULL,        -- e.g. alex_benjamin@lecole.edu
    subjects VARCHAR(255) NOT NULL,                   -- e.g. 'Mathematics, Physics'
    experience_years INT NOT NULL DEFAULT 0,
    join_date DATE NOT NULL,
    emergency_name VARCHAR(150) NOT NULL,
    emergency_phone VARCHAR(30) NOT NULL,
    photo_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_teachers_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 3B. TEACHER QUALIFICATIONS (Dynamic Degrees from UI)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS teacher_qualifications;
CREATE TABLE teacher_qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    degree_title VARCHAR(150) NOT NULL,               -- e.g. 'BSc in Mathematics'
    institution VARCHAR(150) NOT NULL,                -- e.g. 'University of Colombo'
    year_obtained VARCHAR(10) NOT NULL,               -- e.g. '2018'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tq_teacher FOREIGN KEY (teacher_id) 
        REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 4. PARENT PROFILES (1:1 with Parent Form)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS parents;
CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL UNIQUE,
    parent_id VARCHAR(50) NOT NULL UNIQUE,            -- e.g. PAR-2026-0001
    relationship VARCHAR(50) NOT NULL DEFAULT 'Father',-- 'Father', 'Mother', 'Guardian'
    full_name VARCHAR(150) NOT NULL,
    first_name VARCHAR(75) NOT NULL,
    last_name VARCHAR(75) NOT NULL,
    nic VARCHAR(30) NULL,                             -- Sri Lankan NIC (or NULL if foreign passport)
    date_of_birth DATE NOT NULL,
    passport VARCHAR(50) NULL,                        -- If NIC unavailable
    occupation VARCHAR(100) NOT NULL,
    employer VARCHAR(150) NULL,                       -- Place of work
    mobile_phone VARCHAR(30) NOT NULL,
    home_phone VARCHAR(30) NULL,
    office_phone VARCHAR(30) NULL,
    office_address VARCHAR(255) NULL,
    home_address TEXT NULL,
    personal_email VARCHAR(191) NOT NULL,
    emergency_name VARCHAR(150) NULL,
    emergency_contact VARCHAR(30) NULL,
    photo_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_parents_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 5. MANAGEMENT PROFILES (1:1 with Management Form)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS management_profiles;
CREATE TABLE management_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL UNIQUE,
    staff_id VARCHAR(50) NOT NULL UNIQUE,             -- e.g. MAN-2026-0001
    full_name VARCHAR(150) NOT NULL,
    first_name VARCHAR(75) NOT NULL,
    last_name VARCHAR(75) NOT NULL,
    nic VARCHAR(30) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    personal_email VARCHAR(191) NOT NULL,
    institutional_email VARCHAR(191) NOT NULL,        -- e.g. sarah_vance@lecole.edu
    title VARCHAR(100) NOT NULL DEFAULT 'Executive Staff',
    join_date DATE NOT NULL,
    office_location VARCHAR(150) NULL,
    emergency_name VARCHAR(150) NOT NULL,
    emergency_phone VARCHAR(30) NOT NULL,
    photo_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_management_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 6. SYSTEM ADMINISTRATOR PROFILES
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS admin_profiles;
CREATE TABLE admin_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL UNIQUE,
    staff_id VARCHAR(50) NOT NULL UNIQUE,             -- e.g. ADM-2026-0001
    full_name VARCHAR(150) NOT NULL,
    first_name VARCHAR(75) NOT NULL,
    last_name VARCHAR(75) NOT NULL,
    phone VARCHAR(30) NULL,
    photo_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 7. STUDENT-PARENT RELATIONSHIP (PIVOT TABLE)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS student_parents;
CREATE TABLE student_parents (
    student_id INT NOT NULL,
    parent_id INT NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, parent_id),
    CONSTRAINT fk_sp_student FOREIGN KEY (student_id) 
        REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_sp_parent FOREIGN KEY (parent_id) 
        REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 7b. STUDENT ADMISSIONS TRANSACTION LOG (IDEMPOTENCY & DEDUPLICATION)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS student_admissions;
CREATE TABLE student_admissions (
    request_key CHAR(32) PRIMARY KEY,
    created_by INT NOT NULL,
    student_id INT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admission_creator FOREIGN KEY (created_by) 
        REFERENCES user_accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_admission_student FOREIGN KEY (student_id) 
        REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 8. CRYPTOGRAPHIC PASSWORD RESET OTPS
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS password_reset_otps;
CREATE TABLE password_reset_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,                   -- Hashed 6-digit verification code
    attempts_left INT NOT NULL DEFAULT 5,             -- Brute-force guessing cap
    expires_at DATETIME NOT NULL,                     -- 10-minute expiry window
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_otp_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE CASCADE,
    INDEX idx_otp_lookup (account_id, expires_at, used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 9. IMMUTABLE ACTIVITY AUDIT TRAIL
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NULL,
    identifier VARCHAR(191) NULL,                     -- Target email/index attempted
    action VARCHAR(100) NOT NULL,                     -- e.g. 'LOGIN_SUCCESS', 'ACCOUNT_LOCKED_SOFT'
    ip_address VARCHAR(45) NOT NULL,                  -- IPv4 or IPv6
    user_agent TEXT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_account FOREIGN KEY (account_id) 
        REFERENCES user_accounts(id) ON DELETE SET NULL,
    INDEX idx_log_action (action),
    INDEX idx_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 10. DAILY VISITOR & LOGIN AGGREGATES
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS daily_stats;
CREATE TABLE daily_stats (
    stat_date DATE PRIMARY KEY,
    landing_views INT UNSIGNED NOT NULL DEFAULT 0,
    portal_logins INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 11. EXTRACURRICULAR ACTIVITIES
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS extracurricular_activities;
CREATE TABLE extracurricular_activities (
    activity_id VARCHAR(50) PRIMARY KEY,              -- e.g. 'CHESS', 'ROBOTICS', 'CHOIR', 'DEBATING'
    activity_name VARCHAR(100) NOT NULL,
    category ENUM('SPORTS', 'CLUBS', 'ACADEMIC', 'AESTHETIC', 'LEADERSHIP') NOT NULL DEFAULT 'CLUBS',
    teacher_id INT NULL,                              -- TIC (Teacher in Charge)
    description TEXT NULL,
    venue VARCHAR(100) NULL,
    meeting_schedule VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_extracurricular_teacher FOREIGN KEY (teacher_id)
        REFERENCES teachers(id) ON DELETE SET NULL,
    INDEX idx_extracurricular_category (category)
-- -------------------------------------------------------------------------
-- 12. NOTICES & ANNOUNCEMENTS
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS notices;
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'General',
    audience JSON NOT NULL,                           -- e.g. ["All"] or ["Students", "Parents"]
    body TEXT NOT NULL,
    author_name VARCHAR(150) NOT NULL,
    author_role VARCHAR(50) NOT NULL,                 -- 'admin', 'management', 'teacher'
    author_account_id INT NULL,
    attachment_name VARCHAR(255) NULL,
    attachment_path VARCHAR(255) NULL,
    target_class_section VARCHAR(20) NULL,           -- e.g. '6-A' (for Homeroom notices)
    target_club_id VARCHAR(50) NULL,                  -- e.g. 'CHESS' (for Club TIC notices)
    publish_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP, -- Scheduled publish start time
    expires_at DATETIME NULL DEFAULT NULL,             -- Notice expiration / unpublish time
    pinned TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at TIMESTAMP NULL,                        -- Soft-delete timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_pinned (pinned),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_class_section (target_class_section),
    INDEX idx_club_id (target_club_id),
    INDEX idx_created_at (created_at),
    INDEX idx_notices_active_feed (deleted_at, pinned, publish_at, expires_at, created_at),
    CONSTRAINT fk_notice_author FOREIGN KEY (author_account_id)
        REFERENCES user_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 11. ADMISSION IDEMPOTENCY KEYS
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admission_idempotency_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idempotency_key VARCHAR(191) NOT NULL UNIQUE,
    student_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
