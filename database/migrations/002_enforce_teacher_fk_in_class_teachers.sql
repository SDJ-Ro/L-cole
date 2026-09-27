-- =========================================================================
-- MIGRATION 002: ENFORCE TEACHER FOREIGN KEYS IN CLASS & SUBJECT ASSIGNMENTS
-- =========================================================================
-- Replaces plain text teacher_name columns in class_teachers and
-- class_subject_teachers with strict relational foreign keys referencing
-- teachers(id) ON DELETE CASCADE, matching the model used in club_teachers
-- and sport_teachers.
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Backfill teacher_id by matching existing teacher_name text against teachers.full_name
SET @has_ct_name := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'class_teachers' AND column_name = 'teacher_name');
SET @sql_ct_backfill := IF(@has_ct_name > 0, 
    'UPDATE class_teachers ct JOIN teachers t ON (t.full_name = ct.teacher_name OR TRIM(REPLACE(REPLACE(REPLACE(ct.teacher_name, "Mr. ", ""), "Mrs. ", ""), "Ms. ", "")) = TRIM(REPLACE(REPLACE(REPLACE(t.full_name, "Mr. ", ""), "Mrs. ", ""), "Ms. ", ""))) SET ct.teacher_id = t.id WHERE ct.teacher_id IS NULL',
    'SELECT 1'
);
PREPARE stmt_ct FROM @sql_ct_backfill;
EXECUTE stmt_ct;
DEALLOCATE PREPARE stmt_ct;

SET @has_cst_name := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'class_subject_teachers' AND column_name = 'teacher_name');
SET @sql_cst_backfill := IF(@has_cst_name > 0, 
    'UPDATE class_subject_teachers cst JOIN teachers t ON (t.full_name = cst.teacher_name OR TRIM(REPLACE(REPLACE(REPLACE(cst.teacher_name, "Mr. ", ""), "Mrs. ", ""), "Ms. ", "")) = TRIM(REPLACE(REPLACE(REPLACE(t.full_name, "Mr. ", ""), "Mrs. ", ""), "Ms. ", ""))) SET cst.teacher_id = t.id WHERE cst.teacher_id IS NULL',
    'SELECT 1'
);
PREPARE stmt_cst FROM @sql_cst_backfill;
EXECUTE stmt_cst;
DEALLOCATE PREPARE stmt_cst;

-- Clean up any unmapped rows that cannot satisfy the foreign key constraint
DELETE FROM class_teachers WHERE teacher_id IS NULL OR teacher_id NOT IN (SELECT id FROM teachers);
DELETE FROM class_subject_teachers WHERE teacher_id IS NULL OR teacher_id NOT IN (SELECT id FROM teachers);

-- 2. Modify class_teachers table: NOT NULL, FK constraint, and UNIQUE exclusivity constraint
ALTER TABLE class_teachers MODIFY COLUMN teacher_id INT NOT NULL;

-- Ensure foreign key fk_class_teacher_teacher exists
SET @has_fk_ct := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'class_teachers' AND CONSTRAINT_NAME = 'fk_class_teacher_teacher');
SET @sql_add_fk_ct := IF(@has_fk_ct = 0, 'ALTER TABLE class_teachers ADD CONSTRAINT fk_class_teacher_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE', 'SELECT 1');
PREPARE stmt_add_fk_ct FROM @sql_add_fk_ct;
EXECUTE stmt_add_fk_ct;
DEALLOCATE PREPARE stmt_add_fk_ct;

-- Ensure teacher exclusivity constraint (1 teacher can only be class teacher of 1 class)
SET @has_uq_ct := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'class_teachers' AND index_name = 'uq_ct_teacher');
SET @sql_uq_ct := IF(@has_uq_ct = 0, 'ALTER TABLE class_teachers ADD CONSTRAINT uq_ct_teacher UNIQUE (teacher_id)', 'SELECT 1');
PREPARE stmt_uq_ct FROM @sql_uq_ct;
EXECUTE stmt_uq_ct;
DEALLOCATE PREPARE stmt_uq_ct;

-- Drop redundant teacher_name column if it still exists
SET @sql_drop_ct_name := IF(@has_ct_name > 0, 'ALTER TABLE class_teachers DROP COLUMN teacher_name', 'SELECT 1');
PREPARE stmt_drop_ct FROM @sql_drop_ct_name;
EXECUTE stmt_drop_ct;
DEALLOCATE PREPARE stmt_drop_ct;

-- 3. Modify class_subject_teachers table: NOT NULL, FK constraint
ALTER TABLE class_subject_teachers MODIFY COLUMN teacher_id INT NOT NULL;

-- Ensure foreign key fk_cst_teacher exists
SET @has_fk_cst := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'class_subject_teachers' AND CONSTRAINT_NAME = 'fk_cst_teacher');
SET @sql_add_fk_cst := IF(@has_fk_cst = 0, 'ALTER TABLE class_subject_teachers ADD CONSTRAINT fk_cst_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE', 'SELECT 1');
PREPARE stmt_add_fk_cst FROM @sql_add_fk_cst;
EXECUTE stmt_add_fk_cst;
DEALLOCATE PREPARE stmt_add_fk_cst;

-- Drop redundant teacher_name column if it still exists
SET @sql_drop_cst_name := IF(@has_cst_name > 0, 'ALTER TABLE class_subject_teachers DROP COLUMN teacher_name', 'SELECT 1');
PREPARE stmt_drop_cst FROM @sql_drop_cst_name;
EXECUTE stmt_drop_cst;
DEALLOCATE PREPARE stmt_drop_cst;

SET FOREIGN_KEY_CHECKS = 1;
