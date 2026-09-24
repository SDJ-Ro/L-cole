-- =========================================================================
-- L'ÉCOLE — STARTER TEST ACCOUNTS (SEEDS)
-- =========================================================================
-- Password for all seed accounts is: Password@123
-- Bcrypt Hash: $2y$10$Q7y0vG8eZ9s2cW4aK6mNpO9rS5tU1vW3xY5zA7bC9dE1fG3hI5jK2
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ADMIN ACCOUNT
INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, activated_at)
VALUES (1, 'david_silva_admin@lecole.edu', 'admin', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW())
ON DUPLICATE KEY UPDATE identifier = VALUES(identifier);

INSERT INTO admin_profiles (account_id, staff_id, full_name, first_name, last_name, phone)
VALUES (1, 'ADM-2026-0001', 'David Silva', 'David', 'Silva', '+94 77 100 0001')
ON DUPLICATE KEY UPDATE staff_id = VALUES(staff_id);

-- 2. MANAGEMENT ACCOUNT
INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, activated_at)
VALUES (2, 'sarah_vance@lecole.edu', 'management', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW())
ON DUPLICATE KEY UPDATE identifier = VALUES(identifier);

INSERT INTO management_profiles (account_id, staff_id, full_name, first_name, last_name, nic, phone, personal_email, institutional_email, title, join_date, emergency_name, emergency_phone)
VALUES (2, 'MAN-2026-0001', 'Sarah Vance', 'Sarah', 'Vance', '198212345678V', '+94 77 000 0001', 'sarah.vance.test@gmail.com', 'sarah_vance@lecole.edu', 'Academic Principal', '2020-01-15', 'Mark Vance', '+94 77 000 0009')
ON DUPLICATE KEY UPDATE staff_id = VALUES(staff_id);

-- 3. TEACHER ACCOUNT
INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, activated_at)
VALUES (3, 'alex_benjamin@lecole.edu', 'teacher', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW())
ON DUPLICATE KEY UPDATE identifier = VALUES(identifier);

INSERT INTO teachers (account_id, staff_id, full_name, first_name, last_name, nic, date_of_birth, phone, personal_email, institutional_email, subjects, experience_years, join_date, emergency_name, emergency_phone)
VALUES (3, 'TEA-2026-0001', 'Alex Benjamin', 'Alex', 'Benjamin', '198812345678V', '1988-06-20', '+94 70 456 7890', 'alex.benjamin.test@gmail.com', 'alex_benjamin@lecole.edu', 'Science, Physics', 6, '2021-03-01', 'Helen Benjamin', '+94 70 456 7899')
ON DUPLICATE KEY UPDATE staff_id = VALUES(staff_id);

-- 4. PARENT ACCOUNT
INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, activated_at)
VALUES (4, 'suresh_perera@gmail.com', 'parent', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW())
ON DUPLICATE KEY UPDATE identifier = VALUES(identifier);

INSERT INTO parents (account_id, parent_id, relationship, full_name, first_name, last_name, nic, date_of_birth, occupation, mobile_phone, personal_email)
VALUES (4, 'PAR-2026-0001', 'Father', 'Suresh Perera', 'Suresh', 'Perera', '198012345678V', '1980-04-12', 'Civil Engineer', '+94 77 234 5678', 'suresh_perera@gmail.com')
ON DUPLICATE KEY UPDATE parent_id = VALUES(parent_id);

-- 5. STUDENT ACCOUNT
INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, activated_at, first_login_required)
VALUES (5, 'STU-2026-0001', 'student', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW(), 0)
ON DUPLICATE KEY UPDATE identifier = VALUES(identifier);

INSERT INTO students (account_id, index_no, full_name, first_name, last_name, date_of_birth, gender, birth_certificate_number, grade, class_section, admission_date, home_address)
VALUES (5, 'STU-2026-0001', 'Nethmi Perera', 'Nethmi', 'Perera', '2014-05-12', 'Female', 'BC-2014-8849', 'Grade 6', '6-A', '2026-01-10', 'No. 42, Flower Road, Colombo 07')
ON DUPLICATE KEY UPDATE index_no = VALUES(index_no);

-- 6. LINK STUDENT TO PARENT (SIBLING / GUARDIAN LINK)
INSERT INTO student_parents (student_id, parent_id, is_primary)
VALUES (1, 1, 1)
ON DUPLICATE KEY UPDATE is_primary = 1;

SET FOREIGN_KEY_CHECKS = 1;
