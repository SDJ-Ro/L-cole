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

-- 7. EXTRACURRICULAR ACTIVITIES
INSERT INTO extracurricular_activities (activity_id, activity_name, category, description, venue, meeting_schedule)
VALUES
('football', 'Varsity Football Club', 'SPORTS', 'Competitive league training and internal tournaments for senior grades.', 'Main Football Pitch', 'Mondays & Wednesdays, 3:30 - 5:30 PM'),
('basketball', 'Senior Basketball Team', 'SPORTS', 'Inter-school championship training and tactical drills.', 'Indoor Gymnasium', 'Tuesdays & Thursdays, 3:30 - 5:00 PM'),
('cricket', 'Cricket Academy', 'SPORTS', 'Net sessions, fielding practice, and weekend friendly fixtures.', 'School Cricket Grounds', 'Mondays, Wednesdays & Fridays, 3:00 - 5:30 PM'),
('swimming', 'Aquatics & Swimming Team', 'SPORTS', 'Stroke mechanics, endurance conditioning, and relay time-trials.', 'School Swimming Pool', 'Tuesdays & Fridays, 6:00 - 7:30 AM'),
('badminton', 'Badminton Club', 'SPORTS', 'Singles and doubles sparring and tournament preparation.', 'Sports Hall Courts', 'Wednesdays & Saturdays, 3:30 - 5:00 PM'),
('robotics', 'Junior Robotics & STEM League', 'CLUBS', 'Hands-on Arduino programming, robot building, and Olympiad prep.', 'Innovation & Robotics Lab', 'Wednesdays, 3:30 - 5:00 PM'),
('debate', 'Model UN & Debate Society', 'CLUBS', 'Parliamentary debate, speechcraft, and inter-school MUN conferences.', 'Audio-Visual Auditorium', 'Thursdays, 3:30 - 5:00 PM'),
('drama', 'Drama & Theatrical Society', 'AESTHETIC', 'Stage acting, scriptwriting, voice modulation, and annual school play.', 'Main Auditorium Stage', 'Tuesdays, 3:30 - 5:30 PM'),
('chess', 'Chess Club', 'CLUBS', 'Strategic masterclasses, rapid-fire chess puzzles, and tournaments.', 'Library Annex Room 2', 'Fridays, 3:30 - 4:45 PM')
ON DUPLICATE KEY UPDATE activity_name = VALUES(activity_name);

-- 8. STARTER NOTICES
INSERT INTO notices (id, title, category, audience, body, author_name, author_role, author_account_id, pinned, target_class_section, target_club_id, created_at)
VALUES
(1, 'Term 2 Examination Schedule - June 2026', 'Academic', '["All"]', 'The official examination timetable for Grades 6 through 12 has been finalized. Morning sessions begin at 8:30 AM.', 'Academic Office', 'admin', 1, 1, NULL, NULL, NOW()),
(2, 'Sports Day Rehearsal Schedule', 'Extracurricular', '["Students", "Teachers"]', 'Inter-house march past and track rehearsal times are posted. House captains must ensure full attendance.', 'Student Life Office', 'admin', 1, 0, NULL, NULL, NOW()),
(3, 'Library Renovation Notice', 'General', '["All"]', 'The senior library annex will be closed for electrical upgrades this weekend. Digital resources remain accessible.', 'Admin Office', 'admin', 1, 0, NULL, NULL, NOW()),
(4, 'Parent-Teacher Conference: Grade 10 & 11', 'Academic', '["Parents", "Teachers"]', 'Individual consultation slots are available for booking via the parent portal. Progress reports will be distributed.', 'Mrs. Perera', 'management', 2, 0, 'Grade 10', NULL, NOW()),
(5, 'Annual Staff Leadership & Curriculum Review', 'Administrative', '["Teachers", "Management"]', 'Senior management and department heads will meet in the Boardroom for the annual academic audit.', 'Dr. Vance', 'management', 2, 0, NULL, NULL, NOW())
ON DUPLICATE KEY UPDATE title = VALUES(title);

SET FOREIGN_KEY_CHECKS = 1;

