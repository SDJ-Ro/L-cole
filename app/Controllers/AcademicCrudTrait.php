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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $name  = $data['name'] ?? '';
        $actor = $this->getActorDetails();

        $result = AcademicActions::addGrade($name, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/deleteGrade or /management/deleteGrade (Mutation via AcademicActions)
     */
    public function deleteGrade(): void {
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $gradeId = $data['grade_id'] ?? ($data['id'] ?? '');
        $actor   = $this->getActorDetails();

        $result = AcademicActions::deleteGrade($gradeId, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/addClass or /management/addClass (Mutation via AcademicActions)
     */
    public function addClass(): void {
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $sectionName = $data['section_name'] ?? ($data['className'] ?? '');
        $actor       = $this->getActorDetails();

        $result = AcademicActions::deleteClass($sectionName, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/assignClassTeacher or /management/assignClassTeacher (Mutation via AcademicActions)
     */
    public function assignClassTeacher(): void {
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

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
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $rangeLabel = $data['range_label'] ?? ($data['range'] ?? '');
        $actor      = $this->getActorDetails();

        $result = AcademicActions::deleteCurriculumGroup($rangeLabel, $actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * Shared mock dashboard data (calendar events, donut analytics, upcoming events)
     * utilized across admin and management dashboard views.
     */
    protected function getSharedDashboardData(): array {
        return [
            'calendarConfig' => [
                'canAddEvent'  => true,
                'scopeOptions' => class_exists('CalendarEventModel') ? CalendarEventModel::getScopeOptionsForStaff() : [],
                'initialDate'  => date('Y-m-d'),
                'viewDate'     => date('Y-m-01'),
                'events'       => class_exists('CalendarEventModel') ? CalendarEventModel::getAllEvents() : [],
            ],
            'donutSports' => [
                'id'          => 'j-donut-sports',
                'title'       => 'Sports Participation',
                'totalLabel'  => 'Students in all sports',
                'centerLabel' => 'Total',
                'total'       => 438,
                'slices'      => [
                    ['name' => 'Football', 'value' => 150, 'color' => BRAND_SKYBLUE, 'd' => 'M 162.68 131.17 A 70 70 0 0 0 100.00 30.00 L 100.00 50.00 A 50 50 0 0 1 144.77 122.26 Z'],
                    ['name' => 'Cricket', 'value' => 122, 'color' => BRAND_MIDNIGHT, 'd' => 'M 58.72 156.53 A 70 70 0 0 0 159.72 136.51 L 142.66 126.08 A 50 50 0 0 1 70.51 140.38 Z'],
                    ['name' => 'Swimming', 'value' => 90, 'color' => BRAND_SUNSHINE, 'd' => 'M 34.65 74.91 A 70 70 0 0 0 53.95 152.72 L 67.10 137.65 A 50 50 0 0 1 53.32 82.08 Z'],
                    ['name' => 'Athletics', 'value' => 76, 'color' => BRAND_TERRACOTTA, 'd' => 'M 93.90 30.27 A 70 70 0 0 0 37.09 69.31 L 55.06 78.08 A 50 50 0 0 1 95.64 50.19 Z'],
                ],
            ],
            'donutClubs' => [
                'id'          => 'j-donut-clubs',
                'title'       => 'Clubs & Societies',
                'totalLabel'  => 'Club and society members',
                'centerLabel' => 'Total',
                'total'       => 314,
                'slices'      => [
                    ['name' => 'Science Society', 'value' => 120, 'color' => BRAND_LIGHTBLUE, 'd' => 'M 153.67 144.94 A 70 70 0 0 0 100.00 30.00 L 100.00 50.00 A 50 50 0 0 1 138.34 132.10 Z'],
                    ['name' => 'Debate', 'value' => 80, 'color' => BRAND_TERRACOTTA, 'd' => 'M 53.56 152.38 A 70 70 0 0 0 149.55 149.44 L 135.39 135.32 A 50 50 0 0 1 66.83 137.41 Z'],
                    ['name' => 'Music', 'value' => 60, 'color' => BRAND_MAROON, 'd' => 'M 34.88 74.31 A 70 70 0 0 0 49.17 148.13 L 63.69 134.38 A 50 50 0 0 1 53.49 81.65 Z'],
                    ['name' => 'Robotics', 'value' => 54, 'color' => BRAND_MOSS, 'd' => 'M 93.90 30.27 A 70 70 0 0 0 37.37 68.73 L 55.26 77.67 A 50 50 0 0 1 95.64 50.19 Z'],
                ],
            ],
            'upcomingEvents' => [
                ['day' => '27', 'month' => 'SEP', 'name' => 'Term Assessment Review & Practical Exams', 'tag' => 'Academic', 'tagColor' => 'sand'],
                ['day' => '28', 'month' => 'SEP', 'name' => 'All-Island School Athletics Meet', 'tag' => 'Sports', 'tagColor' => 'sky'],
                ['day' => '29', 'month' => 'SEP', 'name' => 'Science Society Annual Exhibition', 'tag' => 'Academic', 'tagColor' => 'terracotta'],
            ],
        ];
    }

    /**
     * POST /admin/assignClubTic or /management/assignClubTic
     */
    public function assignClubTic(): void {
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $clubId      = (int)($data['club_id'] ?? 0);
        $teacherName = trim($data['teacher_name'] ?? '');
        $actor       = $this->getActorDetails();

        $result = AcademicActions::assignClubTic($clubId, $teacherName, (int)$actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /admin/assignSportTic or /management/assignSportTic
     */
    public function assignSportTic(): void {
        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $sportId     = (int)($data['sport_id'] ?? 0);
        $teacherName = trim($data['teacher_name'] ?? '');
        $actor       = $this->getActorDetails();

        $result = AcademicActions::assignSportTic($sportId, $teacherName, (int)$actor['id'], $actor['identifier']);
        $this->sendJson($result, $result['success'] ? 200 : 400);
    }
}


