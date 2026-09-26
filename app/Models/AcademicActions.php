<?php
/**
 * =========================================================================
 * L'ÉCOLE — ACADEMIC ACTIONS (MUTATIONS & BUSINESS RULES)
 * =========================================================================
 * Dedicated write model handling all state mutations, transactional integrity,
 * business-rule enforcement, boundary checks, cascades, and audit logs for:
 *   - Grades (add, delete, cascade unassign)
 *   - Classes (add, edit, rename cascade, delete)
 *   - Class Teachers (1:1 exclusivity, reassignment auto-clear)
 *   - Subject Teachers (workload cap, upsert/clear)
 *   - Curriculum Stages (add, range-relink, orphan protection, cascade cleanup, delete guard)
 *
 * All methods adhere to the 6-Step Discipline:
 *   1. Input validation & sanitization
 *   2. Business-rule & constraint verification
 *   3. Fail-fast error exit (zero DB impact)
 *   4. Transaction boundary ($db->beginTransaction)
 *   5. Integrated Audit Logging (AuditModel::record)
 *   6. Return standardized JSON response array
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/AuditModel.php';

class AcademicActions extends Model {

    // =========================================================================
    // 1. GRADE MUTATIONS
    // =========================================================================

    /**
     * Add a new grade.
     * Enforces strict "Grade N" naming, checks uniqueness, auto-links curriculum stage,
     * and generates the initial section (e.g. "12-A").
     */
    public static function addGrade(string $name, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $name = trim($name);

        // 1. Validation
        if (!preg_match('/^Grade\s+(\d+)$/i', $name, $matches)) {
            return ['success' => false, 'error' => 'Grade name must follow the format "Grade N" (e.g. "Grade 12").'];
        }

        $gradeNum      = (int)$matches[1];
        $gradeId       = 'g' . $gradeNum;
        $canonicalName = 'Grade ' . $gradeNum;

        $db = Database::getConnection();

        // 2. Business rule: Grade uniqueness
        $stmtCheck = $db->prepare("SELECT id FROM grades WHERE id = ? OR name = ?");
        $stmtCheck->execute([$gradeId, $canonicalName]);
        if ($stmtCheck->fetch()) {
            return ['success' => false, 'error' => "{$canonicalName} already exists in the academic structure."];
        }

        // Automatic curriculum linking by grade number
        $curriculumGroupId = self::findCurriculumGroupIdForGradeNumber($gradeNum);
        if (!$curriculumGroupId) {
            return [
                'success' => false,
                'error'   => "No curriculum stage covers Grade {$gradeNum}. Please create a curriculum stage covering this year before adding the grade."
            ];
        }

        // 4. Transaction
        $db->beginTransaction();
        try {
            $stmtInsert = $db->prepare("INSERT INTO grades (id, name, group_id, sort_order) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$gradeId, $canonicalName, $curriculumGroupId, $gradeNum]);

            // Automatically create initial default section (e.g. 12-A)
            $initialSection = "{$gradeNum}-A";
            $stmtClass = $db->prepare("INSERT INTO classes (grade_id, section_name, student_count) VALUES (?, ?, 30)");
            $stmtClass->execute([$gradeId, $initialSection]);

            // Retrieve curriculum subjects for the linked group
            $stmtCurSubj = $db->prepare("SELECT subject_name FROM curriculum_group_subjects WHERE group_id = ? ORDER BY sort_order ASC, id ASC");
            $stmtCurSubj->execute([$curriculumGroupId]);
            $curriculumSubjects = $stmtCurSubj->fetchAll(PDO::FETCH_COLUMN) ?: [];

            $db->commit();

            // 5. Audit Log
            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'GRADE_CREATED',
                "Created {$canonicalName} (ID: {$gradeId}) linked to curriculum group #{$curriculumGroupId} with initial section {$initialSection}"
            );

            // 6. Return Success
            return [
                'success' => true,
                'grade'   => [
                    'id'                  => $gradeId,
                    'name'                => $canonicalName,
                    'classes'             => [$initialSection],
                    'subjectScores'       => self::getDefaultSubjectScores(),
                    'curriculum_subjects' => $curriculumSubjects
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] addGrade: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create grade: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a grade and cascade unassignment.
     * All classes under this grade are removed, and students in these classes become unassigned.
     */
    public static function deleteGrade(string $gradeId, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $db = Database::getConnection();

        $stmtCheck = $db->prepare("SELECT name FROM grades WHERE id = ?");
        $stmtCheck->execute([$gradeId]);
        $gradeName = $stmtCheck->fetchColumn();

        if (!$gradeName) {
            return ['success' => false, 'error' => "Grade '{$gradeId}' not found."];
        }

        $db->beginTransaction();
        try {
            // Find all classes under this grade
            $stmtClasses = $db->prepare("SELECT id, section_name FROM classes WHERE grade_id = ?");
            $stmtClasses->execute([$gradeId]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

            $sectionNames = array_column($classes, 'section_name');

            // 1. Unassign all students belonging to this grade or these classes
            if (!empty($sectionNames)) {
                $inSections = implode(',', array_fill(0, count($sectionNames), '?'));
                $stmtUnassignStudents = $db->prepare("
                    UPDATE students 
                    SET grade = NULL, class_section = NULL 
                    WHERE grade = ? OR class_section IN ({$inSections})
                ");
                $params = array_merge([$gradeName], $sectionNames);
                $stmtUnassignStudents->execute($params);
            } else {
                $stmtUnassignStudents = $db->prepare("UPDATE students SET grade = NULL, class_section = NULL WHERE grade = ?");
                $stmtUnassignStudents->execute([$gradeName]);
            }

            // 2. Explicitly remove teacher assignments and delete classes
            $classIds = array_column($classes, 'id');
            if (!empty($classIds)) {
                $inIds = implode(',', array_fill(0, count($classIds), '?'));
                $db->prepare("DELETE FROM class_teachers WHERE class_id IN ({$inIds})")->execute($classIds);
                $db->prepare("DELETE FROM class_subject_teachers WHERE class_id IN ({$inIds})")->execute($classIds);
            }
            $stmtDelClasses = $db->prepare("DELETE FROM classes WHERE grade_id = ?");
            $stmtDelClasses->execute([$gradeId]);

            // 3. Delete the grade itself
            $stmtDelGrade = $db->prepare("DELETE FROM grades WHERE id = ?");
            $stmtDelGrade->execute([$gradeId]);

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'GRADE_DELETED',
                "Deleted {$gradeName} ({$gradeId}) and cascade-unassigned " . count($sectionNames) . " classes"
            );

            return [
                'success'        => true,
                'gradeId'        => $gradeId,
                'removedClasses' => $sectionNames
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] deleteGrade: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete grade: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 2. CLASS MUTATIONS
    // =========================================================================

    /**
     * Add a class section under a grade.
     * Enforces naming format, 1:1 teacher exclusivity, and capacity bounds.
     */
    public static function addClass(string $gradeId, string $sectionName, int $studentCount, ?string $teacherName = null, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $sectionName  = trim($sectionName);
        $studentCount = max(0, $studentCount);
        $teacherName  = trim((string)$teacherName);
        if ($teacherName === 'Assignment pending') $teacherName = '';

        if (!preg_match('/^\d+-[A-Za-z0-9]+$/', $sectionName)) {
            return ['success' => false, 'error' => 'Class section must follow format like "6-A" or "10-C".'];
        }

        $db = Database::getConnection();

        // Check grade exists
        $stmtGrade = $db->prepare("SELECT name FROM grades WHERE id = ?");
        $stmtGrade->execute([$gradeId]);
        if (!$stmtGrade->fetchColumn()) {
            return ['success' => false, 'error' => "Grade '{$gradeId}' does not exist."];
        }

        // Check section uniqueness within this grade
        $stmtCheck = $db->prepare("SELECT id FROM classes WHERE grade_id = ? AND section_name = ?");
        $stmtCheck->execute([$gradeId, $sectionName]);
        if ($stmtCheck->fetchColumn()) {
            return ['success' => false, 'error' => "Class '{$sectionName}' already exists in this grade."];
        }


        $db->beginTransaction();
        try {
            $stmtClass = $db->prepare("INSERT INTO classes (grade_id, section_name, student_count) VALUES (?, ?, ?)");
            $stmtClass->execute([$gradeId, $sectionName, $studentCount]);
            $classId = (int)$db->lastInsertId();

            $reassignedFrom = null;
            if (!empty($teacherName)) {
                // Strict exclusivity rule: do NOT steal/reassign automatically. Reject if already assigned.
                $stmtPrev = $db->prepare("
                    SELECT c.section_name, c.id 
                    FROM class_teachers ct 
                    JOIN classes c ON ct.class_id = c.id 
                    WHERE ct.teacher_name = ?
                ");
                $stmtPrev->execute([$teacherName]);
                $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                if ($prev) {
                    $db->rollBack();
                    return [
                        'success' => false,
                        'error'   => "Teacher '{$teacherName}' is already the class teacher of Class {$prev['section_name']}. Please unassign them from Class {$prev['section_name']} first."
                    ];
                }

                $stmtAssign = $db->prepare("INSERT INTO class_teachers (class_id, teacher_name) VALUES (?, ?)");
                $stmtAssign->execute([$classId, $teacherName]);
            }

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CLASS_CREATED',
                "Added class {$sectionName} to {$gradeId} (students: {$studentCount}, teacher: " . ($teacherName ?: 'pending') . ($reassignedFrom ? " reassigned from {$reassignedFrom}" : "") . ")"
            );

            return [
                'success' => true,
                'class'   => [
                    'class_id'        => $classId,
                    'grade_id'        => $gradeId,
                    'section_name'    => $sectionName,
                    'student_count'   => $studentCount,
                    'class_teacher'   => $teacherName ?: 'Assignment pending',
                    'reassigned_from' => $reassignedFrom
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] addClass: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to add class: ' . $e->getMessage()];
        }
    }

    /**
     * Edit a class section (rename, student count, class teacher).
     * Cascades renaming across student records and enforces 1:1 teacher exclusivity.
     */
    public static function editClass(string $gradeId, string $oldSectionName, string $newSectionName, int $studentCount, ?string $teacherName = null, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $oldSectionName = trim($oldSectionName);
        $newSectionName = trim($newSectionName);
        $studentCount   = max(0, $studentCount);
        $teacherName    = trim((string)$teacherName);
        if ($teacherName === 'Assignment pending') $teacherName = '';

        if (!preg_match('/^\d+-[A-Za-z0-9]+$/', $newSectionName)) {
            return ['success' => false, 'error' => 'Class section must follow format like "6-A" or "10-C".'];
        }

        $db = Database::getConnection();

        $stmtClass = $db->prepare("SELECT id FROM classes WHERE section_name = ?");
        $stmtClass->execute([$oldSectionName]);
        $classId = $stmtClass->fetchColumn();

        if (!$classId) {
            return ['success' => false, 'error' => "Class '{$oldSectionName}' not found."];
        }

        // Check collision if renaming within this grade
        if (strcasecmp($oldSectionName, $newSectionName) !== 0) {
            $stmtCollision = $db->prepare("SELECT id FROM classes WHERE grade_id = ? AND section_name = ? AND id != ?");
            $stmtCollision->execute([$gradeId, $newSectionName, $classId]);
            if ($stmtCollision->fetchColumn()) {
                return ['success' => false, 'error' => "Class '{$newSectionName}' already exists in this grade."];
            }
        }


        $db->beginTransaction();
        try {
            // Update class
            $stmtUpdate = $db->prepare("UPDATE classes SET section_name = ?, student_count = ? WHERE id = ?");
            $stmtUpdate->execute([$newSectionName, $studentCount, $classId]);

            // Cascade rename on student records
            if (strcasecmp($oldSectionName, $newSectionName) !== 0) {
                $stmtUpdateStudents = $db->prepare("UPDATE students SET class_section = ? WHERE class_section = ?");
                $stmtUpdateStudents->execute([$newSectionName, $oldSectionName]);
            }

            // Manage class teacher
            $reassignedFrom = null;
            if (empty($teacherName)) {
                $stmtClearCt = $db->prepare("DELETE FROM class_teachers WHERE class_id = ?");
                $stmtClearCt->execute([$classId]);
            } else {
                // Strict exclusivity: reject if already assigned to a different class
                $stmtPrev = $db->prepare("
                    SELECT c.section_name, c.id 
                    FROM class_teachers ct 
                    JOIN classes c ON ct.class_id = c.id 
                    WHERE ct.teacher_name = ? AND c.id != ?
                ");
                $stmtPrev->execute([$teacherName, $classId]);
                $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                if ($prev) {
                    $db->rollBack();
                    return [
                        'success' => false,
                        'error'   => "Teacher '{$teacherName}' is already the class teacher of Class {$prev['section_name']}. Please unassign them from Class {$prev['section_name']} first."
                    ];
                }

                $stmtUpsertCt = $db->prepare("
                    INSERT INTO class_teachers (class_id, teacher_name) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE teacher_name = VALUES(teacher_name)
                ");
                $stmtUpsertCt->execute([$classId, $teacherName]);
            }

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CLASS_UPDATED',
                "Updated class {$oldSectionName} -> {$newSectionName} (students: {$studentCount}, teacher: " . ($teacherName ?: 'pending') . ")"
            );

            return [
                'success' => true,
                'class'   => [
                    'class_id'        => $classId,
                    'old_section'     => $oldSectionName,
                    'section_name'    => $newSectionName,
                    'student_count'   => $studentCount,
                    'class_teacher'   => $teacherName ?: 'Assignment pending',
                    'reassigned_from' => $reassignedFrom
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] editClass: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update class: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a single class section.
     * Enrolled students have their class_section set to NULL (unassigned).
     */
    public static function deleteClass(string $sectionName, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $sectionName = trim($sectionName);
        $db = Database::getConnection();

        $stmtClass = $db->prepare("SELECT id, grade_id FROM classes WHERE section_name = ?");
        $stmtClass->execute([$sectionName]);
        $row = $stmtClass->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['success' => false, 'error' => "Class '{$sectionName}' not found."];
        }

        $classId = (int)$row['id'];
        $gradeId = $row['grade_id'];

        $db->beginTransaction();
        try {
            // Set student class_section to NULL for all students in this class
            $stmtUnassign = $db->prepare("UPDATE students SET class_section = NULL WHERE class_section = ?");
            $stmtUnassign->execute([$sectionName]);

            // Explicitly delete teacher assignments before deleting class row
            $stmtCt = $db->prepare("DELETE FROM class_teachers WHERE class_id = ?");
            $stmtCt->execute([$classId]);
            $stmtCst = $db->prepare("DELETE FROM class_subject_teachers WHERE class_id = ?");
            $stmtCst->execute([$classId]);

            $stmtDelete = $db->prepare("DELETE FROM classes WHERE id = ?");
            $stmtDelete->execute([$classId]);

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CLASS_DELETED',
                "Deleted class {$sectionName} from {$gradeId}; students unassigned"
            );

            return [
                'success'      => true,
                'section_name' => $sectionName,
                'grade_id'     => $gradeId
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] deleteClass: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete class: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 3. TEACHER ASSIGNMENT MUTATIONS
    // =========================================================================

    /**
     * Assign or clear a class teacher.
     * Enforces the 1:1 exclusivity rule.
     */
    public static function assignClassTeacher(string $sectionName, ?string $teacherName, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $sectionName = trim($sectionName);
        $teacherName = trim((string)$teacherName);
        if ($teacherName === 'Assignment pending') $teacherName = '';

        $db = Database::getConnection();

        $stmtClass = $db->prepare("SELECT id FROM classes WHERE section_name = ?");
        $stmtClass->execute([$sectionName]);
        $classId = $stmtClass->fetchColumn();

        if (!$classId) {
            return ['success' => false, 'error' => "Class '{$sectionName}' not found."];
        }

        $db->beginTransaction();
        try {
            $reassignedFrom = null;

            if (empty($teacherName)) {
                $stmtClear = $db->prepare("DELETE FROM class_teachers WHERE class_id = ?");
                $stmtClear->execute([$classId]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CLASS_TEACHER_CLEARED',
                    "Class teacher cleared for {$sectionName}"
                );
            } else {
                // Strict exclusivity: reject if already assigned to another class
                $stmtPrev = $db->prepare("
                    SELECT c.section_name, c.id 
                    FROM class_teachers ct 
                    JOIN classes c ON ct.class_id = c.id 
                    WHERE ct.teacher_name = ? AND c.id != ?
                ");
                $stmtPrev->execute([$teacherName, $classId]);
                $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                if ($prev) {
                    $db->rollBack();
                    return [
                        'success' => false,
                        'error'   => "Teacher '{$teacherName}' is already the class teacher of Class {$prev['section_name']}. Please unassign them from Class {$prev['section_name']} first."
                    ];
                }

                $stmtUpsert = $db->prepare("
                    INSERT INTO class_teachers (class_id, teacher_name) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE teacher_name = VALUES(teacher_name)
                ");
                $stmtUpsert->execute([$classId, $teacherName]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CLASS_TEACHER_ASSIGNED',
                    "Assigned {$teacherName} as class teacher for {$sectionName}"
                );
            }

            $db->commit();

            return [
                'success'         => true,
                'section_name'    => $sectionName,
                'class_teacher'   => $teacherName ?: 'Assignment pending',
                'reassigned_from' => $reassignedFrom
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] assignClassTeacher: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to assign class teacher: ' . $e->getMessage()];
        }
    }

    /**
     * Assign or clear a subject teacher for a class.
     * Enforces the 5-subject teacher workload cap.
     */
    public static function assignSubjectTeacher(string $sectionName, string $subjectName, ?string $teacherName, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $sectionName = trim($sectionName);
        $subjectName = trim($subjectName);
        $teacherName = trim((string)$teacherName);
        if ($teacherName === 'Assignment pending') $teacherName = '';

        $db = Database::getConnection();

        $stmtClass = $db->prepare("SELECT id FROM classes WHERE section_name = ?");
        $stmtClass->execute([$sectionName]);
        $classId = $stmtClass->fetchColumn();

        if (!$classId) {
            return ['success' => false, 'error' => "Class '{$sectionName}' not found."];
        }

        // Workload Cap Guard: Max 5 subjects across the school
        if (!empty($teacherName)) {
            $stmtCount = $db->prepare("
                SELECT COUNT(DISTINCT subject_name) 
                FROM class_subject_teachers 
                WHERE teacher_name = ? AND NOT (class_id = ? AND subject_name = ?)
            ");
            $stmtCount->execute([$teacherName, $classId, $subjectName]);
            $currentDistinctSubjects = (int)$stmtCount->fetchColumn();

            if ($currentDistinctSubjects >= 5) {
                return [
                    'success' => false,
                    'error'   => "Teacher '{$teacherName}' has already reached the maximum workload limit of 5 distinct subjects."
                ];
            }
        }

        $db->beginTransaction();
        try {
            if (empty($teacherName)) {
                $stmtDelete = $db->prepare("DELETE FROM class_subject_teachers WHERE class_id = ? AND subject_name = ?");
                $stmtDelete->execute([$classId, $subjectName]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'SUBJECT_TEACHER_CLEARED',
                    "Cleared subject teacher for {$subjectName} in {$sectionName}"
                );
            } else {
                $teacherIdLookup = $db->prepare("SELECT id FROM teachers WHERE full_name = ? LIMIT 1");
                $teacherIdLookup->execute([$teacherName]);
                $resolvedTeacherId = $teacherIdLookup->fetchColumn() ?: null;

                $stmtUpsert = $db->prepare("
                    INSERT INTO class_subject_teachers (class_id, subject_name, teacher_name, teacher_id) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE teacher_name = VALUES(teacher_name), teacher_id = VALUES(teacher_id)
                ");
                $stmtUpsert->execute([$classId, $subjectName, $teacherName, $resolvedTeacherId]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'SUBJECT_TEACHER_ASSIGNED',
                    "Assigned {$teacherName} to teach {$subjectName} in {$sectionName}"
                );
            }

            $db->commit();

            return [
                'success'      => true,
                'section_name' => $sectionName,
                'subject_name' => $subjectName,
                'teacher_name' => $teacherName ?: 'Assignment pending'
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] assignSubjectTeacher: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to assign subject teacher: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 4. CURRICULUM GROUP MUTATIONS
    // =========================================================================

    /**
     * Add a curriculum group with subjects and auto-link matching grades.
     */
    public static function addCurriculumGroup(string $rangeLabel, ?string $description, array $subjects, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $rangeLabel  = trim($rangeLabel);
        $description = trim((string)$description);
        $subjects    = array_values(array_filter(array_map('trim', $subjects)));

        if (empty($rangeLabel)) {
            return ['success' => false, 'error' => 'Curriculum stage range is required (e.g. "Years 12–13").'];
        }

        $db = Database::getConnection();

        $stmtCheck = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ?");
        $stmtCheck->execute([$rangeLabel]);
        if ($stmtCheck->fetchColumn()) {
            return ['success' => false, 'error' => "Curriculum stage '{$rangeLabel}' already exists."];
        }

        $db->beginTransaction();
        try {
            $stmtGroup = $db->prepare("INSERT INTO curriculum_groups (range_label, description) VALUES (?, ?)");
            $stmtGroup->execute([$rangeLabel, $description]);
            $groupId = (int)$db->lastInsertId();

            if (!empty($subjects)) {
                $stmtSubj = $db->prepare("INSERT INTO curriculum_group_subjects (group_id, subject_name, sort_order) VALUES (?, ?, ?)");
                foreach ($subjects as $i => $subj) {
                    $stmtSubj->execute([$groupId, $subj, $i]);
                }
            }

            // Auto-link any existing grades whose number matches this new stage
            $rangeBounds = self::parseRangeBounds($rangeLabel);
            if ($rangeBounds) {
                $stmtGrades = $db->query("SELECT id, sort_order FROM grades");
                $allGrades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

                $stmtUpdateGrade = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                foreach ($allGrades as $gr) {
                    $order = (int)$gr['sort_order'];
                    if ($order >= $rangeBounds['min'] && $order <= $rangeBounds['max']) {
                        $stmtUpdateGrade->execute([$groupId, $gr['id']]);
                    }
                }
            }

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CURRICULUM_GROUP_CREATED',
                "Created curriculum stage '{$rangeLabel}' with " . count($subjects) . " subjects"
            );

            return [
                'success' => true,
                'group'   => [
                    'id'          => $groupId,
                    'range'       => $rangeLabel,
                    'description' => $description,
                    'subjects'    => $subjects
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] addCurriculumGroup: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create curriculum stage: ' . $e->getMessage()];
        }
    }

    /**
     * Edit a curriculum group.
     * Enforces orphan protection on range changes, and cascade-clears removed subjects.
     */
    public static function editCurriculumGroup(string $rangeLabel, ?string $newRangeLabel, ?string $description, array $subjects, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $rangeLabel    = trim($rangeLabel);
        $newRangeLabel = !empty($newRangeLabel) ? trim($newRangeLabel) : $rangeLabel;
        $description   = trim((string)$description);
        $subjects      = array_values(array_filter(array_map('trim', $subjects)));

        $db = Database::getConnection();

        $stmtGroup = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ?");
        $stmtGroup->execute([$rangeLabel]);
        $groupId = $stmtGroup->fetchColumn();

        if (!$groupId) {
            return ['success' => false, 'error' => "Curriculum stage '{$rangeLabel}' not found."];
        }

        $db->beginTransaction();
        try {
            // Check collision if range label changed
            if (strcasecmp($rangeLabel, $newRangeLabel) !== 0) {
                $stmtCheck = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ? AND id != ?");
                $stmtCheck->execute([$newRangeLabel, $groupId]);
                if ($stmtCheck->fetchColumn()) {
                    $db->rollBack();
                    return ['success' => false, 'error' => "Curriculum stage '{$newRangeLabel}' already exists."];
                }
            }

            // Grade re-linking and orphan protection when range bounds change (e.g. Years 6–9 -> Years 6–8)
            if (strcasecmp($rangeLabel, $newRangeLabel) !== 0) {
                $newBounds = self::parseRangeBounds($newRangeLabel);
                if ($newBounds) {
                    $stmtLinkedGrades = $db->prepare("SELECT id, name, sort_order FROM grades WHERE group_id = ?");
                    $stmtLinkedGrades->execute([$groupId]);
                    $linkedGrades = $stmtLinkedGrades->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($linkedGrades as $lGrade) {
                        $gNum = (int)$lGrade['sort_order'];
                        if ($gNum < $newBounds['min'] || $gNum > $newBounds['max']) {
                            // Grade falls outside the new range. Look for another stage that covers it.
                            $stmtOtherGroups = $db->prepare("SELECT id, range_label FROM curriculum_groups WHERE id != ?");
                            $stmtOtherGroups->execute([$groupId]);
                            $otherGroups = $stmtOtherGroups->fetchAll(PDO::FETCH_ASSOC);

                            $newGroupIdForGrade = null;
                            foreach ($otherGroups as $og) {
                                $ogBounds = self::parseRangeBounds($og['range_label']);
                                if ($ogBounds && $gNum >= $ogBounds['min'] && $gNum <= $ogBounds['max']) {
                                    $newGroupIdForGrade = (int)$og['id'];
                                    break;
                                }
                            }

                            if ($newGroupIdForGrade) {
                                $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                                $stmtRelink->execute([$newGroupIdForGrade, $lGrade['id']]);
                            } else {
                                $db->rollBack();
                                return [
                                    'success' => false,
                                    'error'   => "Cannot change range to '{$newRangeLabel}' because {$lGrade['name']} would be left without a curriculum stage. Every grade must have a curriculum stage. Please create or update another stage (e.g. expand 'Years 10–11' to 'Years 9–11') to cover {$lGrade['name']} first."
                                ];
                            }
                        }
                    }

                    // Check if any other grades now fall inside this updated range:
                    $stmtAllOtherGrades = $db->prepare("SELECT id, sort_order FROM grades WHERE group_id != ? OR group_id IS NULL");
                    $stmtAllOtherGrades->execute([$groupId]);
                    while ($otherGrade = $stmtAllOtherGrades->fetch(PDO::FETCH_ASSOC)) {
                        $otherNum = (int)$otherGrade['sort_order'];
                        if ($otherNum >= $newBounds['min'] && $otherNum <= $newBounds['max']) {
                            $stmtRelinkToThis = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                            $stmtRelinkToThis->execute([$groupId, $otherGrade['id']]);
                        }
                    }
                }
            }

            // Update group header
            $stmtUpdate = $db->prepare("UPDATE curriculum_groups SET range_label = ?, description = ? WHERE id = ?");
            $stmtUpdate->execute([$newRangeLabel, $description, $groupId]);

            // Read existing subjects to determine removals
            $stmtOldSubjs = $db->prepare("SELECT subject_name FROM curriculum_group_subjects WHERE group_id = ?");
            $stmtOldSubjs->execute([$groupId]);
            $oldSubjects = $stmtOldSubjs->fetchAll(PDO::FETCH_COLUMN);

            $removedSubjects = array_diff($oldSubjects, $subjects);

            // Auto-clear subject assignments for removed subjects
            if (!empty($removedSubjects)) {
                $stmtClasses = $db->prepare("
                    SELECT c.id 
                    FROM classes c 
                    JOIN grades g ON c.grade_id = g.id 
                    WHERE g.group_id = ?
                ");
                $stmtClasses->execute([$groupId]);
                $classIds = $stmtClasses->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($classIds)) {
                    $inClasses = implode(',', array_fill(0, count($classIds), '?'));
                    $stmtClearSubjectTeacher = $db->prepare("
                        DELETE FROM class_subject_teachers 
                        WHERE class_id IN ({$inClasses}) AND subject_name = ?
                    ");
                    foreach ($removedSubjects as $remSubj) {
                        $params = array_merge($classIds, [$remSubj]);
                        $stmtClearSubjectTeacher->execute($params);
                    }
                }
            }

            // Replace curriculum subjects
            $stmtDelSubjs = $db->prepare("DELETE FROM curriculum_group_subjects WHERE group_id = ?");
            $stmtDelSubjs->execute([$groupId]);

            if (!empty($subjects)) {
                $stmtAddSubj = $db->prepare("INSERT INTO curriculum_group_subjects (group_id, subject_name, sort_order) VALUES (?, ?, ?)");
                foreach ($subjects as $i => $subj) {
                    $stmtAddSubj->execute([$groupId, $subj, $i]);
                }
            }

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CURRICULUM_GROUP_UPDATED',
                "Updated curriculum stage '{$newRangeLabel}'; " . count($subjects) . " subjects (" . count($removedSubjects) . " removed & cascade-cleared)"
            );

            return [
                'success' => true,
                'group'   => [
                    'id'          => $groupId,
                    'range'       => $newRangeLabel,
                    'description' => $description,
                    'subjects'    => $subjects
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] editCurriculumGroup: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update curriculum stage: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a curriculum group.
     * Hard-blocked if any grades depend on it.
     */
    public static function deleteCurriculumGroup(string $rangeLabel, ?int $actorId = null, ?string $actorIdentifier = null): array {
        $rangeLabel = trim($rangeLabel);
        $db = Database::getConnection();

        $stmtGroup = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ?");
        $stmtGroup->execute([$rangeLabel]);
        $groupId = $stmtGroup->fetchColumn();

        if (!$groupId) {
            return ['success' => false, 'error' => "Curriculum stage '{$rangeLabel}' not found."];
        }

        // Guard: Hard block if grades depend on it
        $stmtGrades = $db->prepare("SELECT name FROM grades WHERE group_id = ?");
        $stmtGrades->execute([$groupId]);
        $dependentGrades = $stmtGrades->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($dependentGrades)) {
            $names = implode(', ', $dependentGrades);
            return [
                'success' => false,
                'error'   => "Cannot delete curriculum stage '{$rangeLabel}'. It is currently required by: {$names}. Remove or reassign those grades first."
            ];
        }

        $db->beginTransaction();
        try {
            $stmtDelete = $db->prepare("DELETE FROM curriculum_groups WHERE id = ?");
            $stmtDelete->execute([$groupId]);

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CURRICULUM_GROUP_DELETED',
                "Deleted curriculum stage '{$rangeLabel}'"
            );

            return [
                'success' => true,
                'range'   => $rangeLabel
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] deleteCurriculumGroup: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete curriculum stage: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 5. INTERNAL REUSABLE HELPERS
    // =========================================================================

    public static function findCurriculumGroupIdForGradeNumber(int $gradeNum): ?int {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT id, range_label FROM curriculum_groups");
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($groups as $g) {
                $bounds = self::parseRangeBounds($g['range_label']);
                if ($bounds && $gradeNum >= $bounds['min'] && $gradeNum <= $bounds['max']) {
                    return (int)$g['id'];
                }
            }
        } catch (\Throwable $e) {
            error_log("[AcademicActions Error] findCurriculumGroupIdForGradeNumber: " . $e->getMessage());
        }
        return null;
    }

    public static function parseRangeBounds(string $rangeLabel): ?array {
        if (preg_match_all('/\d+/', $rangeLabel, $matches)) {
            $nums = $matches[0];
            if (count($nums) >= 2) {
                return ['min' => (int)$nums[0], 'max' => (int)$nums[1]];
            } elseif (count($nums) === 1) {
                return ['min' => (int)$nums[0], 'max' => (int)$nums[0]];
            }
        }
        return null;
    }

    private static function getDefaultSubjectScores(): array {
        return [
            'Mathematics'     => [76, 82],
            'English'         => [80, 78],
            'Science'         => [75, 80],
            'History'         => [72, 76],
            'Sinhala / Tamil' => [79, 77],
            'ICT'             => [84, 80],
        ];
    }

    public static function assignClubTic(int $clubId, string $teacherName, int $actorAccountId, ?string $actorIdentifier): array {
        $db = Database::getConnection();
        $teacherLookup = $db->prepare("SELECT id FROM teachers WHERE full_name = ? LIMIT 1");
        $teacherLookup->execute([$teacherName]);
        $newTeacherId = $teacherLookup->fetchColumn();
        if (!$newTeacherId) return ['success' => false, 'error' => 'No matching teacher found.'];

        try {
            $db->beginTransaction();
            $db->prepare("UPDATE club_tic_history SET ended_at = NOW() WHERE club_id = ? AND ended_at IS NULL")->execute([$clubId]);
            $db->prepare("INSERT INTO club_teachers (club_id, teacher_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)")
               ->execute([$clubId, $newTeacherId]);
            $db->prepare("INSERT INTO club_tic_history (club_id, teacher_id, assigned_by) VALUES (?, ?, ?)")->execute([$clubId, $newTeacherId, $actorAccountId]);
            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Admin', 'TIC_ASSIGNED', "Assigned {$teacherName} as TIC of club #{$clubId}.");
            return ['success' => true, 'message' => 'Teacher-in-Charge assigned successfully.'];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[AcademicActions] assignClubTic: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while assigning TIC.'];
        }
    }

    public static function assignSportTic(int $sportId, string $teacherName, int $actorAccountId, ?string $actorIdentifier): array {
        $db = Database::getConnection();
        $teacherLookup = $db->prepare("SELECT id FROM teachers WHERE full_name = ? LIMIT 1");
        $teacherLookup->execute([$teacherName]);
        $newTeacherId = $teacherLookup->fetchColumn();
        if (!$newTeacherId) return ['success' => false, 'error' => 'No matching teacher found.'];

        try {
            $db->beginTransaction();
            $db->prepare("UPDATE sport_tic_history SET ended_at = NOW() WHERE sport_id = ? AND ended_at IS NULL")->execute([$sportId]);
            $db->prepare("INSERT INTO sport_teachers (sport_id, teacher_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)")
               ->execute([$sportId, $newTeacherId]);
            $db->prepare("INSERT INTO sport_tic_history (sport_id, teacher_id, assigned_by) VALUES (?, ?, ?)")->execute([$sportId, $newTeacherId, $actorAccountId]);
            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Admin', 'TIC_ASSIGNED', "Assigned {$teacherName} as TIC of sport #{$sportId}.");
            return ['success' => true, 'message' => 'Teacher-in-Charge assigned successfully.'];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[AcademicActions] assignSportTic: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while assigning TIC.'];
        }
    }
}
