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
        if (preg_match('/^\d+$/', $name)) {
            $name = 'Grade ' . $name;
        }

        // 1. Validation
        if (!preg_match('/^Grade\s+(\d+)$/i', $name, $matches)) {
            return ['success' => false, 'error' => 'Grade name must follow the format "Grade N" or a number (e.g. "Grade 12" or "12").'];
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

        // Automatic curriculum linking by grade number (optional: null if no curriculum stage covers it yet)
        $curriculumGroupId = self::findCurriculumGroupIdForGradeNumber($gradeNum);

        // 4. Transaction
        $db->beginTransaction();
        try {
            $stmtInsert = $db->prepare("INSERT INTO grades (id, name, group_id, sort_order) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$gradeId, $canonicalName, $curriculumGroupId, $gradeNum]);

            // Automatically create initial default section (e.g. 12-A)
            $initialSection = "{$gradeNum}-A";
            $stmtClass = $db->prepare("INSERT INTO classes (grade_id, section_name, student_count) VALUES (?, ?, 30)");
            $stmtClass->execute([$gradeId, $initialSection]);

            // Retrieve curriculum subjects for the linked group (if linked)
            $curriculumSubjects = [];
            if ($curriculumGroupId) {
                $stmtCurSubj = $db->prepare("SELECT subject_name FROM curriculum_group_subjects WHERE group_id = ? ORDER BY sort_order ASC, id ASC");
                $stmtCurSubj->execute([$curriculumGroupId]);
                $curriculumSubjects = $stmtCurSubj->fetchAll(PDO::FETCH_COLUMN) ?: [];
            }

            $db->commit();

            // 5. Audit Log
            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'GRADE_CREATED',
                "Created {$canonicalName} (ID: {$gradeId})" . ($curriculumGroupId ? " linked to curriculum group #{$curriculumGroupId}" : " without curriculum stage") . " with initial section {$initialSection}"
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
            $classIds = array_column($classes, 'id');
            if (!empty($classIds)) {
                $inClassIds = implode(',', array_fill(0, count($classIds), '?'));
                $stmtUnassignStudents = $db->prepare("
                    UPDATE students 
                    SET grade = NULL, class_section = NULL, class_id = NULL 
                    WHERE class_id IN ({$inClassIds}) OR grade = ?
                ");
                $params = array_merge($classIds, [$gradeName]);
                $stmtUnassignStudents->execute($params);
            } else {
                $stmtUnassignStudents = $db->prepare("UPDATE students SET grade = NULL, class_section = NULL, class_id = NULL WHERE grade = ?");
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
        $gradeName = $stmtGrade->fetchColumn();
        if (!$gradeName) {
            return ['success' => false, 'error' => "Grade '{$gradeId}' does not exist."];
        }

        // Check section uniqueness within this grade (case-insensitive)
        $stmtCheck = $db->prepare("SELECT id FROM classes WHERE grade_id = ? AND LOWER(TRIM(section_name)) = LOWER(TRIM(?))");
        $stmtCheck->execute([$gradeId, $sectionName]);
        if ($stmtCheck->fetchColumn()) {
            return ['success' => false, 'error' => "Class '{$sectionName}' already exists in {$gradeName}. You cannot have duplicate class sections in the same grade."];
        }


        $db->beginTransaction();
        try {
            $stmtClass = $db->prepare("INSERT INTO classes (grade_id, section_name, student_count) VALUES (?, ?, ?)");
            $stmtClass->execute([$gradeId, $sectionName, $studentCount]);
            $classId = (int)$db->lastInsertId();

            $reassignedFrom = null;
            $teacherFullName = null;
            if (!empty($teacherName)) {
                $teacher = self::resolveTeacher($teacherName, $db);
                if ($teacher === false) {
                    $db->rollBack();
                    return ['success' => false, 'error' => "Teacher '{$teacherName}' does not exist in faculty records."];
                }

                if ($teacher !== null) {
                    $teacherId = (int)$teacher['id'];
                    $teacherFullName = $teacher['full_name'];

                    // Strict exclusivity rule: do NOT steal/reassign automatically. Reject if already assigned (Denial Auditing)
                    $stmtPrev = $db->prepare("
                        SELECT c.section_name, c.id 
                        FROM class_teachers ct 
                        JOIN classes c ON ct.class_id = c.id 
                        WHERE ct.teacher_id = ?
                    ");
                    $stmtPrev->execute([$teacherId]);
                    $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                    if ($prev) {
                        $db->rollBack();
                        AuditModel::record(
                            $actorId,
                            $actorIdentifier,
                            'SECURITY_CLASS_TEACHER_EXCLUSIVITY_BLOCKED',
                            "Attempted assignment of {$teacherFullName} to Class {$sectionName} blocked: already assigned to Class {$prev['section_name']}."
                        );
                        return [
                            'success' => false,
                            'error'   => "Teacher '{$teacherFullName}' is already the class teacher of Class {$prev['section_name']}. Please unassign them from Class {$prev['section_name']} first."
                        ];
                    }

                    $stmtAssign = $db->prepare("INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?)");
                    $stmtAssign->execute([$classId, $teacherId]);
                }
            }

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CLASS_CREATED',
                "Added class {$sectionName} to {$gradeId} (students: {$studentCount}, teacher: " . ($teacherFullName ?: 'pending') . ($reassignedFrom ? " reassigned from {$reassignedFrom}" : "") . ")"
            );

            return [
                'success' => true,
                'class'   => [
                    'class_id'        => $classId,
                    'grade_id'        => $gradeId,
                    'section_name'    => $sectionName,
                    'student_count'   => $studentCount,
                    'class_teacher'   => $teacherFullName ?: 'Assignment pending',
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
            $stmtCollision = $db->prepare("SELECT id FROM classes WHERE grade_id = ? AND LOWER(TRIM(section_name)) = LOWER(TRIM(?)) AND id != ?");
            $stmtCollision->execute([$gradeId, $newSectionName, $classId]);
            if ($stmtCollision->fetchColumn()) {
                return ['success' => false, 'error' => "Class '{$newSectionName}' already exists in this grade. You cannot have duplicate class sections in the same grade."];
            }
        }


        $db->beginTransaction();
        try {
            // Update class
            $stmtUpdate = $db->prepare("UPDATE classes SET section_name = ?, student_count = ? WHERE id = ?");
            $stmtUpdate->execute([$newSectionName, $studentCount, $classId]);

            // Sync display column on student records linked via foreign key (students follow via class_id automatically)
            if (strcasecmp($oldSectionName, $newSectionName) !== 0) {
                $stmtUpdateStudents = $db->prepare("UPDATE students SET class_section = ? WHERE class_id = ?");
                $stmtUpdateStudents->execute([$newSectionName, $classId]);
            }

            // Manage class teacher
            $reassignedFrom = null;
            $teacherFullName = null;

            $teacher = self::resolveTeacher($teacherName, $db);
            if ($teacher === false) {
                $db->rollBack();
                return ['success' => false, 'error' => "Teacher '{$teacherName}' does not exist in faculty records."];
            }

            if ($teacher === null) {
                $stmtClearCt = $db->prepare("DELETE FROM class_teachers WHERE class_id = ?");
                $stmtClearCt->execute([$classId]);
            } else {
                $teacherId = (int)$teacher['id'];
                $teacherFullName = $teacher['full_name'];

                // Strict exclusivity: reject if already assigned to a different class (Denial Auditing)
                $stmtPrev = $db->prepare("
                    SELECT c.section_name, c.id 
                    FROM class_teachers ct 
                    JOIN classes c ON ct.class_id = c.id 
                    WHERE ct.teacher_id = ? AND c.id != ?
                ");
                $stmtPrev->execute([$teacherId, $classId]);
                $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                if ($prev) {
                    $db->rollBack();
                    AuditModel::record(
                        $actorId,
                        $actorIdentifier,
                        'SECURITY_CLASS_TEACHER_EXCLUSIVITY_BLOCKED',
                        "Attempted reassignment of {$teacherFullName} to Class {$newSectionName} blocked: already assigned to Class {$prev['section_name']}."
                    );
                    return [
                        'success' => false,
                        'error'   => "Teacher '{$teacherFullName}' is already the class teacher of Class {$prev['section_name']}. Please unassign them from Class {$prev['section_name']} first."
                    ];
                }

                $stmtUpsertCt = $db->prepare("
                    INSERT INTO class_teachers (class_id, teacher_id) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)
                ");
                $stmtUpsertCt->execute([$classId, $teacherId]);
            }

            $db->commit();

            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CLASS_UPDATED',
                "Updated class {$oldSectionName} -> {$newSectionName} (students: {$studentCount}, teacher: " . ($teacherFullName ?: 'pending') . ")"
            );

            return [
                'success' => true,
                'class'   => [
                    'class_id'        => $classId,
                    'old_section'     => $oldSectionName,
                    'section_name'    => $newSectionName,
                    'student_count'   => $studentCount,
                    'class_teacher'   => $teacherFullName ?: 'Assignment pending',
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
            // Set student class_section to NULL for all students in this class (foreign key ON DELETE SET NULL also sets class_id to NULL)
            $stmtUnassign = $db->prepare("UPDATE students SET class_section = NULL, class_id = NULL WHERE class_id = ? OR class_section = ?");
            $stmtUnassign->execute([$classId, $sectionName]);

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
            $teacherFullName = null;

            $teacher = self::resolveTeacher($teacherName, $db);
            if ($teacher === false) {
                $db->rollBack();
                return ['success' => false, 'error' => "Teacher '{$teacherName}' does not exist in faculty records."];
            }

            if ($teacher === null) {
                $stmtClear = $db->prepare("DELETE FROM class_teachers WHERE class_id = ?");
                $stmtClear->execute([$classId]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CLASS_TEACHER_CLEARED',
                    "Class teacher cleared for {$sectionName}"
                );
            } else {
                $teacherId = (int)$teacher['id'];
                $teacherFullName = $teacher['full_name'];

                // Strict exclusivity: reject if already assigned to another class (Denial Auditing)
                $stmtPrev = $db->prepare("
                    SELECT c.section_name, c.id 
                    FROM class_teachers ct 
                    JOIN classes c ON ct.class_id = c.id 
                    WHERE ct.teacher_id = ? AND c.id != ?
                ");
                $stmtPrev->execute([$teacherId, $classId]);
                $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
                if ($prev) {
                    $db->rollBack();
                    AuditModel::record(
                        $actorId,
                        $actorIdentifier,
                        'SECURITY_CLASS_TEACHER_EXCLUSIVITY_BLOCKED',
                        "Attempted reassignment of {$teacherFullName} to Class {$sectionName} blocked: already assigned to Class {$prev['section_name']}."
                    );
                    return [
                        'success' => false,
                        'error'   => "Teacher '{$teacherFullName}' is already the class teacher of Class {$prev['section_name']}. Please unassign them from Class {$prev['section_name']} first."
                    ];
                }

                $stmtUpsert = $db->prepare("
                    INSERT INTO class_teachers (class_id, teacher_id) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)
                ");
                $stmtUpsert->execute([$classId, $teacherId]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CLASS_TEACHER_ASSIGNED',
                    "Assigned {$teacherFullName} as class teacher for {$sectionName}"
                );
            }

            $db->commit();

            return [
                'success'         => true,
                'section_name'    => $sectionName,
                'class_teacher'   => $teacherFullName ?: 'Assignment pending',
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

        $teacher = self::resolveTeacher($teacherName, $db);
        if ($teacher === false) {
            return ['success' => false, 'error' => "Teacher '{$teacherName}' does not exist in faculty records."];
        }

        $teacherFullName = null;
        $teacherId = null;

        // Workload Cap Guard & Scope Shape: Max 5 subjects across the school
        if ($teacher !== null) {
            $teacherId = (int)$teacher['id'];
            $teacherFullName = $teacher['full_name'];

            $stmtCount = $db->prepare("
                SELECT COUNT(DISTINCT subject_name) 
                FROM class_subject_teachers 
                WHERE teacher_id = ? AND NOT (class_id = ? AND subject_name = ?)
            ");
            $stmtCount->execute([$teacherId, $classId, $subjectName]);
            $currentDistinctSubjects = (int)$stmtCount->fetchColumn();

            if ($currentDistinctSubjects >= 5) {
                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'SECURITY_WORKLOAD_LIMIT_BLOCKED',
                    "Assignment of {$teacherFullName} to teach {$subjectName} in {$sectionName} blocked: teacher reached max workload cap of 5 subjects."
                );
                return [
                    'success' => false,
                    'error'   => "Teacher '{$teacherFullName}' has already reached the maximum workload limit of 5 distinct subjects."
                ];
            }
        }

        $db->beginTransaction();
        try {
            if ($teacher === null) {
                $stmtDelete = $db->prepare("DELETE FROM class_subject_teachers WHERE class_id = ? AND subject_name = ?");
                $stmtDelete->execute([$classId, $subjectName]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'SUBJECT_TEACHER_CLEARED',
                    "Cleared subject teacher for {$subjectName} in {$sectionName}"
                );
            } else {
                $stmtUpsert = $db->prepare("
                    INSERT INTO class_subject_teachers (class_id, subject_name, teacher_id) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)
                ");
                $stmtUpsert->execute([$classId, $subjectName, $teacherId]);

                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'SUBJECT_TEACHER_ASSIGNED',
                    "Assigned {$teacherFullName} to teach {$subjectName} in {$sectionName}"
                );
            }

            $db->commit();

            return [
                'success'      => true,
                'section_name' => $sectionName,
                'subject_name' => $subjectName,
                'teacher_name' => $teacherFullName ?: 'Assignment pending'
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

        if (empty($subjects)) {
            return ['success' => false, 'error' => 'A curriculum stage must have at least one subject. Please add at least one subject before saving.'];
        }

        $db = Database::getConnection();

        $rangeBounds = self::parseRangeBounds($rangeLabel);
        if ($rangeBounds) {
            $stmtCheckGrades = $db->prepare("SELECT COUNT(*) FROM grades WHERE sort_order >= ? AND sort_order <= ?");
            $stmtCheckGrades->execute([$rangeBounds['min'], $rangeBounds['max']]);
            $matchingGradeCount = (int)$stmtCheckGrades->fetchColumn();
            if ($matchingGradeCount === 0) {
                $stageHint = ($rangeBounds['min'] === $rangeBounds['max']) 
                    ? "Grade {$rangeBounds['min']}" 
                    : "Grade {$rangeBounds['min']} or Grade {$rangeBounds['max']}";
                return [
                    'success' => false,
                    'error'   => "Cannot add curriculum stage '{$rangeLabel}': No existing grades found in this range. In L'École, at least one grade (e.g. {$stageHint}) must exist before creating its curriculum stage. Please add the grade first."
                ];
            }
        }

        $stmtCheck = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ?");
        $stmtCheck->execute([$rangeLabel]);
        if ($stmtCheck->fetchColumn()) {
            $suffixIndex = 2;
            $candidate = "{$rangeLabel} #{$suffixIndex}";
            while (true) {
                $stmtCheck->execute([$candidate]);
                if (!$stmtCheck->fetchColumn()) {
                    $rangeLabel = $candidate;
                    break;
                }
                $suffixIndex++;
                $candidate = "{$rangeLabel} #{$suffixIndex}";
            }
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

        if (empty($subjects)) {
            return ['success' => false, 'error' => 'A curriculum stage must have at least one subject. Please add at least one subject before saving.'];
        }

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

            // Check that new range has at least one existing grade
            if (strcasecmp($rangeLabel, $newRangeLabel) !== 0) {
                $newBounds = self::parseRangeBounds($newRangeLabel);
                if ($newBounds) {
                    $stmtCheckGrades = $db->prepare("SELECT COUNT(*) FROM grades WHERE sort_order >= ? AND sort_order <= ?");
                    $stmtCheckGrades->execute([$newBounds['min'], $newBounds['max']]);
                    if ((int)$stmtCheckGrades->fetchColumn() === 0) {
                        $db->rollBack();
                        $stageHint = ($newBounds['min'] === $newBounds['max']) 
                            ? "Grade {$newBounds['min']}" 
                            : "Grade {$newBounds['min']} or Grade {$newBounds['max']}";
                        return [
                            'success' => false,
                            'error'   => "Cannot change range to '{$newRangeLabel}': No existing grades found in this range. In L'École, at least one grade (e.g. {$stageHint}) must exist before creating its curriculum stage. Please add the grade first."
                        ];
                    }
                }
            }

            // Grade re-linking when range bounds change (e.g. Years 6–9 -> Years 6–8)
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

                            // Relink to matching group or set to NULL (standalone)
                            $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                            $stmtRelink->execute([$newGroupIdForGrade, $lGrade['id']]);
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
     * Compute standard range label (e.g. 6 to 9 -> 'Years 6–9', 8 to 8 -> 'Year 8')
     */
    public static function computeRangeLabel(int $startYear, int $endYear): string {
        $min = min($startYear, $endYear);
        $max = max($startYear, $endYear);
        if ($min === $max) {
            return "Year {$min}";
        }
        return "Years {$min}–{$max}";
    }

    /**
     * Delete a curriculum group.
     * Blocked if any active grades depend solely on it.
     * Allowed if all active grades are covered by another curriculum group (or no grades exist).
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

        $db->beginTransaction();
        try {
            // Find all grades currently linked or matching this stage's range
            $rangeBounds = self::parseRangeBounds($rangeLabel);
            $boundCondition = "";
            $params = [$groupId];
            if ($rangeBounds) {
                $boundCondition = " OR (sort_order >= ? AND sort_order <= ?)";
                $params[] = $rangeBounds['min'];
                $params[] = $rangeBounds['max'];
            }
            $stmtGrades = $db->prepare("SELECT id, name, sort_order, group_id FROM grades WHERE group_id = ? {$boundCondition}");
            $stmtGrades->execute($params);
            $existingGrades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

            // Check if any other curriculum group covers these grades
            $stmtOtherGroups = $db->prepare("SELECT id, range_label FROM curriculum_groups WHERE id != ?");
            $stmtOtherGroups->execute([$groupId]);
            $otherGroups = $stmtOtherGroups->fetchAll(PDO::FETCH_ASSOC);

            $otherBounds = [];
            foreach ($otherGroups as $og) {
                $ob = self::parseRangeBounds($og['range_label']);
                if ($ob) {
                    $otherBounds[] = ['min' => $ob['min'], 'max' => $ob['max'], 'id' => (int)$og['id'], 'range_label' => $og['range_label']];
                }
            }

            $orphanedGradeNames = [];
            $relinkMap = []; // grade_id => new_group_id

            foreach ($existingGrades as $gradeRow) {
                $gSort = (int)($gradeRow['sort_order'] ?? 0);
                $coveredBy = null;
                foreach ($otherBounds as $ob) {
                    if ($gSort >= $ob['min'] && $gSort <= $ob['max']) {
                        $coveredBy = $ob['id'];
                        break;
                    }
                }
                if ($coveredBy === null) {
                    $orphanedGradeNames[] = $gradeRow['name'];
                } else {
                    $relinkMap[$gradeRow['id']] = $coveredBy;
                }
            }

            if (!empty($orphanedGradeNames)) {
                $db->rollBack();
                $names = implode(', ', array_unique($orphanedGradeNames));
                return [
                    'success' => false,
                    'error'   => "Cannot delete curriculum stage '{$rangeLabel}'. Active grades ({$names}) depend solely on it. Every active grade must belong to an academic curriculum stage."
                ];
            }

            // Safe to delete! Re-link any grades pointing to this group to their alternative group
            foreach ($relinkMap as $gradeId => $newGroupId) {
                $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ? AND group_id = ?");
                $stmtRelink->execute([$newGroupId, $gradeId, $groupId]);
            }

            // Clean up curriculum subjects first (foreign key cascade safety)
            $stmtDelSubjs = $db->prepare("DELETE FROM curriculum_group_subjects WHERE group_id = ?");
            $stmtDelSubjs->execute([$groupId]);

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

    /**
     * Remove / Carve out a specific grade from a curriculum group.
     * Implements Way A (Range Splitting / Carving).
     *
     * - If single-grade stage: Deletes the stage (if covered elsewhere).
     * - If start boundary (grade == min): Shrinks stage to (min+1)–max.
     * - If end boundary (grade == max): Shrinks stage to min–(max-1).
     * - If middle grade (min < grade < max): Splits into Stage 1 (min–[grade-1]) and Stage 2 ([grade+1]–max).
     */
    public static function removeGradeFromCurriculumGroup(
        string $rangeLabel, 
        int $gradeNumber, 
        ?int $actorId = null, 
        ?string $actorIdentifier = null
    ): array {
        $rangeLabel = trim($rangeLabel);
        if ($gradeNumber <= 0) {
            return ['success' => false, 'error' => 'Invalid grade number specified.'];
        }

        $db = Database::getConnection();

        $stmtGroup = $db->prepare("SELECT id, range_label, description FROM curriculum_groups WHERE range_label = ?");
        $stmtGroup->execute([$rangeLabel]);
        $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            return ['success' => false, 'error' => "Curriculum stage '{$rangeLabel}' not found."];
        }
        $groupId = (int)$group['id'];
        $description = $group['description'] ?? '';

        $bounds = self::parseRangeBounds($rangeLabel);
        if (!$bounds || $gradeNumber < $bounds['min'] || $gradeNumber > $bounds['max']) {
            return ['success' => false, 'error' => "Grade {$gradeNumber} is not part of curriculum stage '{$rangeLabel}'."];
        }

        $min = $bounds['min'];
        $max = $bounds['max'];

        // Check if grade is active in school
        $stmtGrade = $db->prepare("SELECT id, name, sort_order, group_id FROM grades WHERE sort_order = ? LIMIT 1");
        $stmtGrade->execute([$gradeNumber]);
        $gradeRow = $stmtGrade->fetch(PDO::FETCH_ASSOC);

        // Find alternative curriculum group covering this grade
        $stmtOther = $db->prepare("SELECT id, range_label FROM curriculum_groups WHERE id != ?");
        $stmtOther->execute([$groupId]);
        $otherGroups = $stmtOther->fetchAll(PDO::FETCH_ASSOC);

        $altGroupId = null;
        $altGroupLabel = null;
        foreach ($otherGroups as $og) {
            $ob = self::parseRangeBounds($og['range_label']);
            if ($ob && $gradeNumber >= $ob['min'] && $gradeNumber <= $ob['max']) {
                $altGroupId = (int)$og['id'];
                $altGroupLabel = $og['range_label'];
                break;
            }
        }

        // If grade is active in school and has NO other curriculum, removal is prohibited!
        if ($gradeRow && $altGroupId === null) {
            return [
                'success' => false,
                'error'   => "Cannot remove Grade {$gradeNumber} from '{$rangeLabel}': It has no other curriculum stage assigned. Every active grade must belong to an academic curriculum stage."
            ];
        }

        // Fetch subjects of this group to copy if splitting
        $stmtSubjs = $db->prepare("SELECT subject_name FROM curriculum_group_subjects WHERE group_id = ? ORDER BY sort_order ASC");
        $stmtSubjs->execute([$groupId]);
        $subjects = $stmtSubjs->fetchAll(PDO::FETCH_COLUMN);

        $db->beginTransaction();
        try {
            // Case 1: Single-grade stage (min == max) -> delete the stage
            if ($min === $max) {
                if ($gradeRow && $altGroupId) {
                    $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                    $stmtRelink->execute([$altGroupId, $gradeRow['id']]);
                }

                $stmtDelSubjs = $db->prepare("DELETE FROM curriculum_group_subjects WHERE group_id = ?");
                $stmtDelSubjs->execute([$groupId]);

                $stmtDelGroup = $db->prepare("DELETE FROM curriculum_groups WHERE id = ?");
                $stmtDelGroup->execute([$groupId]);

                $db->commit();
                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CURRICULUM_GRADE_REMOVED',
                    "Removed Grade {$gradeNumber} from '{$rangeLabel}' (Stage deleted as it was single-grade)"
                );

                return [
                    'success'       => true,
                    'action'        => 'deleted',
                    'originalRange' => $rangeLabel,
                    'removedGrade'  => $gradeNumber,
                    'stages'        => []
                ];
            }

            // Case 2: Start boundary ($gradeNumber === $min) -> Shrink to (min+1) to max
            if ($gradeNumber === $min) {
                $newRangeLabel = self::computeRangeLabel($min + 1, $max);

                $stmtCol = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ? AND id != ?");
                $stmtCol->execute([$newRangeLabel, $groupId]);
                if ($stmtCol->fetchColumn()) {
                    $db->rollBack();
                    return ['success' => false, 'error' => "Cannot shrink to '{$newRangeLabel}': A curriculum stage with this range already exists."];
                }

                $stmtUpd = $db->prepare("UPDATE curriculum_groups SET range_label = ? WHERE id = ?");
                $stmtUpd->execute([$newRangeLabel, $groupId]);

                if ($gradeRow && $altGroupId) {
                    $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                    $stmtRelink->execute([$altGroupId, $gradeRow['id']]);
                }

                $db->commit();
                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CURRICULUM_GRADE_REMOVED',
                    "Removed Grade {$gradeNumber} from '{$rangeLabel}', updated to '{$newRangeLabel}'"
                );

                return [
                    'success'       => true,
                    'action'        => 'shrink',
                    'originalRange' => $rangeLabel,
                    'removedGrade'  => $gradeNumber,
                    'stages'        => [
                        [
                            'range'       => $newRangeLabel,
                            'description' => $description,
                            'subjects'    => $subjects
                        ]
                    ]
                ];
            }

            // Case 3: End boundary ($gradeNumber === $max) -> Shrink to min to (max-1)
            if ($gradeNumber === $max) {
                $newRangeLabel = self::computeRangeLabel($min, $max - 1);

                $stmtCol = $db->prepare("SELECT id FROM curriculum_groups WHERE range_label = ? AND id != ?");
                $stmtCol->execute([$newRangeLabel, $groupId]);
                if ($stmtCol->fetchColumn()) {
                    $db->rollBack();
                    return ['success' => false, 'error' => "Cannot shrink to '{$newRangeLabel}': A curriculum stage with this range already exists."];
                }

                $stmtUpd = $db->prepare("UPDATE curriculum_groups SET range_label = ? WHERE id = ?");
                $stmtUpd->execute([$newRangeLabel, $groupId]);

                if ($gradeRow && $altGroupId) {
                    $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                    $stmtRelink->execute([$altGroupId, $gradeRow['id']]);
                }

                $db->commit();
                AuditModel::record(
                    $actorId,
                    $actorIdentifier,
                    'CURRICULUM_GRADE_REMOVED',
                    "Removed Grade {$gradeNumber} from '{$rangeLabel}', updated to '{$newRangeLabel}'"
                );

                return [
                    'success'       => true,
                    'action'        => 'shrink',
                    'originalRange' => $rangeLabel,
                    'removedGrade'  => $gradeNumber,
                    'stages'        => [
                        [
                            'range'       => $newRangeLabel,
                            'description' => $description,
                            'subjects'    => $subjects
                        ]
                    ]
                ];
            }

            // Case 4: Middle grade ($min < $gradeNumber < $max) -> SPLIT (Way A)
            $stage1Label = self::computeRangeLabel($min, $gradeNumber - 1);
            $stage2Label = self::computeRangeLabel($gradeNumber + 1, $max);

            $stmtCol = $db->prepare("SELECT id, range_label FROM curriculum_groups WHERE range_label IN (?, ?) AND id != ?");
            $stmtCol->execute([$stage1Label, $stage2Label, $groupId]);
            $colRows = $stmtCol->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($colRows)) {
                $db->rollBack();
                $colNames = implode(', ', array_column($colRows, 'range_label'));
                return ['success' => false, 'error' => "Cannot split stage: Stage(s) '{$colNames}' already exist."];
            }

            // 1. Update current group to Stage 1
            $stmtUpd = $db->prepare("UPDATE curriculum_groups SET range_label = ? WHERE id = ?");
            $stmtUpd->execute([$stage1Label, $groupId]);

            // 2. Create Stage 2 group
            $stmtInsert = $db->prepare("INSERT INTO curriculum_groups (range_label, description) VALUES (?, ?)");
            $stmtInsert->execute([$stage2Label, $description]);
            $stage2Id = (int)$db->lastInsertId();

            // 3. Copy subjects to Stage 2
            if (!empty($subjects)) {
                $stmtAddSubj = $db->prepare("INSERT INTO curriculum_group_subjects (group_id, subject_name, sort_order) VALUES (?, ?, ?)");
                foreach ($subjects as $i => $subj) {
                    $stmtAddSubj->execute([$stage2Id, $subj, $i]);
                }
            }

            // 4. Re-link Stage 2 grades (sort_order >= gradeNumber + 1 AND sort_order <= max) to Stage 2 ID
            $stmtRelinkStage2 = $db->prepare("
                UPDATE grades 
                SET group_id = ? 
                WHERE group_id = ? AND sort_order >= ? AND sort_order <= ?
            ");
            $stmtRelinkStage2->execute([$stage2Id, $groupId, $gradeNumber + 1, $max]);

            // 5. Re-link removed grade to alternative group
            if ($gradeRow && $altGroupId) {
                $stmtRelink = $db->prepare("UPDATE grades SET group_id = ? WHERE id = ?");
                $stmtRelink->execute([$altGroupId, $gradeRow['id']]);
            }

            $db->commit();
            AuditModel::record(
                $actorId,
                $actorIdentifier,
                'CURRICULUM_GRADE_REMOVED',
                "Removed Grade {$gradeNumber} from '{$rangeLabel}', split stage into '{$stage1Label}' and '{$stage2Label}'"
            );

            return [
                'success'       => true,
                'action'        => 'split',
                'originalRange' => $rangeLabel,
                'removedGrade'  => $gradeNumber,
                'stages'        => [
                    [
                        'range'       => $stage1Label,
                        'description' => $description,
                        'subjects'    => $subjects
                    ],
                    [
                        'range'       => $stage2Label,
                        'description' => $description,
                        'subjects'    => $subjects
                    ]
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[AcademicActions Error] removeGradeFromCurriculumGroup: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to remove grade from curriculum stage: ' . $e->getMessage()];
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

    /**
     * Resolves a teacher identifier (id or full_name) to a valid teacher record [id, full_name].
     * Returns null if empty/pending.
     * Returns ['id' => int, 'full_name' => string] if found.
     * Returns false if non-empty but not found in faculty records.
     */
    private static function resolveTeacher($teacherIdentifier, PDO $db) {
        if ($teacherIdentifier === null) {
            return null;
        }
        $teacherIdentifier = trim((string)$teacherIdentifier);
        if ($teacherIdentifier === '' || $teacherIdentifier === 'Assignment pending') {
            return null;
        }

        // 1. If numeric ID
        if (ctype_digit($teacherIdentifier)) {
            $stmt = $db->prepare("SELECT id, full_name FROM teachers WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$teacherIdentifier]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return ['id' => (int)$row['id'], 'full_name' => $row['full_name']];
            }
        }

        // 2. Exact full_name match
        $stmt = $db->prepare("SELECT id, full_name FROM teachers WHERE full_name = ? LIMIT 1");
        $stmt->execute([$teacherIdentifier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return ['id' => (int)$row['id'], 'full_name' => $row['full_name']];
        }

        // 3. Prefix-stripped match for Mr. / Mrs. / Ms.
        $cleanInput = trim(str_replace(['Mr. ', 'Mrs. ', 'Ms. '], '', $teacherIdentifier));
        $stmt = $db->prepare("
            SELECT id, full_name FROM teachers 
            WHERE TRIM(REPLACE(REPLACE(REPLACE(full_name, 'Mr. ', ''), 'Mrs. ', ''), 'Ms. ', '')) = ? 
            LIMIT 1
        ");
        $stmt->execute([$cleanInput]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return ['id' => (int)$row['id'], 'full_name' => $row['full_name']];
        }

        return false;
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

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Admin', 'CLUB_TIC_ASSIGNED', "Assigned {$teacherName} as TIC of club #{$clubId}.");
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

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Admin', 'SPORT_TIC_ASSIGNED', "Assigned {$teacherName} as TIC of sport #{$sportId}.");
            return ['success' => true, 'message' => 'Teacher-in-Charge assigned successfully.'];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[AcademicActions] assignSportTic: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while assigning TIC.'];
        }
    }
}

