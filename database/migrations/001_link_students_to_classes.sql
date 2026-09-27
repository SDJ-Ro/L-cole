-- =========================================================================
-- MIGRATION 001: LINK STUDENTS TO CLASSES VIA FOREIGN KEY
-- =========================================================================
-- Replaces string-comparison matching with strict relational foreign key
-- between students and classes. Prevents orphaned student records on class
-- section renames and ensures data integrity.
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Add nullable class_id column to students table
ALTER TABLE students 
ADD COLUMN IF NOT EXISTS class_id INT NULL AFTER birth_certificate_number;

-- 2. Backfill class_id for all existing students via grade and section matching
UPDATE students s
JOIN grades g ON (g.name = s.grade OR g.id = s.grade)
JOIN classes c ON c.grade_id = g.id AND c.section_name = s.class_section
SET s.class_id = c.id;

-- 3. Add foreign key constraint and index
ALTER TABLE students 
DROP FOREIGN KEY IF EXISTS fk_students_class;

ALTER TABLE students 
ADD CONSTRAINT fk_students_class 
FOREIGN KEY (class_id) REFERENCES classes(id) 
ON DELETE SET NULL;

-- 4. Add index for join optimization
ALTER TABLE students 
ADD INDEX IF NOT EXISTS idx_students_class_id (class_id);

SET FOREIGN_KEY_CHECKS = 1;
