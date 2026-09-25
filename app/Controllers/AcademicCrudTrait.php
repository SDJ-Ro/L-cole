<?php
/**
 * =========================================================================
 * L'ÉCOLE — ACADEMIC CRUD CONTROLLER TRAIT
 * =========================================================================
 * Shared between AdminController and ManagementController to provide
 * identical academic operations, access auditing, and JSON API endpoints.
 * =========================================================================
 */

require_once __DIR__ . '/../Models/AcademicModel.php';
require_once __DIR__ . '/../Models/AcademicActions.php';
require_once __DIR__ . '/../Models/AuditModel.php';

trait AcademicCrudTrait {

    /**
     * Parse incoming JSON request or POST parameters.
     */
    protected function getRequestPayload(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST ?? [];
    }

    /**
     * Send standard JSON response and terminate.
     */
    protected function sendJson(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Helper to get current authenticated user actor info.
     */
    protected function getActorDetails(): array {
        $user = $this->getUser();
        return [
            'id'         => $user['id'] ?? null,
            'identifier' => $user['identifier'] ?? ($user['email'] ?? null)
        ];
    }

    /**
     * GET /admin/getAcademicData or /management/getAcademicData (Pure Reads)
     */
    public function getAcademicData(): void {
        $this->sendJson([
            'success'          => true,
            'grades'           => AcademicModel::getGrades(),
            'subjects'         => AcademicModel::getSubjects(),
            'curriculumGroups' => AcademicModel::getCurriculumGroups(),
            'classTeachers'    => AcademicModel::getClassTeachers(),
            'classEnrollments' => AcademicModel::getClassEnrollments(),
            'subjectTeachers'  => AcademicModel::getSubjectTeachers(),
            'staffAssignments' => AcademicModel::getStaffAssignments(),
        ]);
    }

    /**
     * POST /admin/addGrade or /management/addGrade (Mutation via AcademicActions)
     */
    public function addGrade(): void {
        $data  = $this->getRequestPayload();
        $name  = $data['name'] ?? '';
        $actor = $this->getActorDetails();

        $result = AcademicActions::addGrade($name, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/deleteGrade or /management/deleteGrade (Mutation via AcademicActions)
     */
    public function deleteGrade(): void {
        $data    = $this->getRequestPayload();
        $gradeId = $data['grade_id'] ?? ($data['id'] ?? '');
        $actor   = $this->getActorDetails();

        $result = AcademicActions::deleteGrade($gradeId, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/addClass or /management/addClass (Mutation via AcademicActions)
     */
    public function addClass(): void {
        $data         = $this->getRequestPayload();
        $gradeId      = $data['grade_id'] ?? '';
        $sectionName  = $data['section_name'] ?? '';
        $studentCount = isset($data['student_count']) ? (int)$data['student_count'] : 30;
        $teacherName  = $data['teacher_name'] ?? null;
        $actor        = $this->getActorDetails();

        $result = AcademicActions::addClass($gradeId, $sectionName, $studentCount, $teacherName, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/editClass or /management/editClass (Mutation via AcademicActions)
     */
    public function editClass(): void {
        $data           = $this->getRequestPayload();
        $gradeId        = $data['grade_id'] ?? '';
        $oldSectionName = $data['old_section_name'] ?? ($data['className'] ?? '');
        $newSectionName = $data['new_section_name'] ?? ($data['section_name'] ?? $oldSectionName);
        $studentCount   = isset($data['student_count']) ? (int)$data['student_count'] : 30;
        $teacherName    = $data['teacher_name'] ?? null;
        $actor          = $this->getActorDetails();

        $result = AcademicActions::editClass($gradeId, $oldSectionName, $newSectionName, $studentCount, $teacherName, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/deleteClass or /management/deleteClass (Mutation via AcademicActions)
     */
    public function deleteClass(): void {
        $data        = $this->getRequestPayload();
        $sectionName = $data['section_name'] ?? ($data['className'] ?? '');
        $actor       = $this->getActorDetails();

        $result = AcademicActions::deleteClass($sectionName, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/assignClassTeacher or /management/assignClassTeacher (Mutation via AcademicActions)
     */
    public function assignClassTeacher(): void {
        $data        = $this->getRequestPayload();
        $sectionName = $data['section_name'] ?? ($data['className'] ?? '');
        $teacherName = $data['teacher_name'] ?? null;
        $actor       = $this->getActorDetails();

        $result = AcademicActions::assignClassTeacher($sectionName, $teacherName, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/assignSubjectTeacher or /management/assignSubjectTeacher (Mutation via AcademicActions)
     */
    public function assignSubjectTeacher(): void {
        $data        = $this->getRequestPayload();
        $sectionName = $data['section_name'] ?? ($data['className'] ?? '');
        $subjectName = $data['subject_name'] ?? ($data['subject'] ?? '');
        $teacherName = $data['teacher_name'] ?? null;
        $actor       = $this->getActorDetails();

        $result = AcademicActions::assignSubjectTeacher($sectionName, $subjectName, $teacherName, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/addCurriculumGroup or /management/addCurriculumGroup (Mutation via AcademicActions)
     */
    public function addCurriculumGroup(): void {
        $data        = $this->getRequestPayload();
        $rangeLabel  = $data['range_label'] ?? ($data['range'] ?? '');
        $description = $data['description'] ?? '';
        $subjects    = $data['subjects'] ?? [];
        $actor       = $this->getActorDetails();

        $result = AcademicActions::addCurriculumGroup($rangeLabel, $description, $subjects, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/editCurriculumGroup or /management/editCurriculumGroup (Mutation via AcademicActions)
     */
    public function editCurriculumGroup(): void {
        $data          = $this->getRequestPayload();
        $rangeLabel    = $data['range_label'] ?? ($data['range'] ?? '');
        $newRangeLabel = $data['new_range_label'] ?? $rangeLabel;
        $description   = $data['description'] ?? null;
        $subjects      = $data['subjects'] ?? [];
        $actor         = $this->getActorDetails();

        $result = AcademicActions::editCurriculumGroup($rangeLabel, $newRangeLabel, $description, $subjects, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/deleteCurriculumGroup or /management/deleteCurriculumGroup (Mutation via AcademicActions)
     */
    public function deleteCurriculumGroup(): void {
        $data       = $this->getRequestPayload();
        $rangeLabel = $data['range_label'] ?? ($data['range'] ?? '');
        $actor      = $this->getActorDetails();

        $result = AcademicActions::deleteCurriculumGroup($rangeLabel, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }
}

