<?php
/**
 * =========================================================================
 * L'ÉCOLE — PEOPLE DIRECTORY CRUD CONTROLLER TRAIT
 * =========================================================================
 * Shared between AdminController and ManagementController to provide
 * identical People Directory API endpoints across all school roles:
 *   - SECTION 1: STUDENT ACTIONS (Admission, Profile Update, Status Toggle)
 *   - SECTION 2: PARENT ACTIONS  (Search, Profile Update, Deactivation)
 *   - SECTION 3: TEACHER ACTIONS (Profile Update, Status Toggle, Handover)
 *   - SECTION 4: MANAGEMENT ACTIONS (Profile Update, Status Toggle)
 * =========================================================================
 */

require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/StudentActions.php';
require_once __DIR__ . '/../Models/ParentModel.php';
require_once __DIR__ . '/../Models/ParentActions.php';
require_once __DIR__ . '/../Models/TeacherActions.php';
require_once __DIR__ . '/../Models/ManagementActions.php';
require_once __DIR__ . '/../Models/AuditModel.php';
require_once __DIR__ . '/../Models/SqlJsMapper.php';

trait PeopleCrudTrait {

    // =========================================================================
    // SECTION 1: STUDENT ENDPOINTS (ADMISSION, PROFILE UPDATE, STATUS TOGGLE)
    // =========================================================================

    /**
     * POST /admin/admitStudent or /management/admitStudent
     * Atomically registers a student and links or co-creates their legal guardian.
     */
    public function admitStudent(): void {
        $this->requireAuth();
        $this->validateCsrf();

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = StudentActions::register($_POST, $actorId);
            $this->sendJson($result, 201);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 409 ? 409 : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (Throwable $e) {
            error_log('[Student Admission Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'An unexpected server error occurred during admission.'], 500);
        }
    }

    /**
     * POST /admin/changeStudentGuardian or /management/changeStudentGuardian
     * Reassigns a student's legal guardian to a different registered parent.
     */
    public function changeStudentGuardian(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $studentIndex = trim($_POST['studentIndex'] ?? '');
        $parentCode   = trim($_POST['parentCode'] ?? '');

        if ($studentIndex === '' || $parentCode === '') {
            $this->sendJson(['error' => 'Student index and parent code are required.'], 422);
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = StudentActions::changeGuardian($studentIndex, $parentCode, $actorId);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            error_log('[Change Guardian Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to reassign guardian.'], 500);
        }
    }

    /**
     * POST /admin/updateStudentProfile or /management/updateStudentProfile
     * Updates an enrolled student's personal, academic, and medical details.
     */
    public function updateStudentProfile(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();

        // CSRF verification
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? '');
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $studentIndex = trim($data['studentIndex'] ?? ($data['index_no'] ?? ''));
        if (empty($studentIndex)) {
            $this->sendJson(['error' => 'Student index number is required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = StudentActions::updateProfile($studentIndex, $data, $actorId);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            error_log('[Student Profile Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update student profile.'], 500);
        }
    }

    /**
     * POST /admin/updateStudentStatus or /management/updateStudentStatus
     * Toggles a student's activation_status between ACTIVE and INACTIVE.
     * Cascades parent to INACTIVE if the last active child leaves.
     */
    public function updateStudentStatus(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();

        // CSRF verification
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? '');
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $studentIndex = trim($data['studentIndex'] ?? ($data['index_no'] ?? ''));
        $status       = trim($data['status'] ?? '');

        if (empty($studentIndex) || empty($status)) {
            $this->sendJson(['error' => 'Student index and target status are required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = StudentActions::updateStatus($studentIndex, $status, $actorId);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            error_log('[Student Status Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update student status.'], 500);
        }
    }

    // =========================================================================
    // SECTION 2: PARENT ENDPOINTS (SEARCH, PROFILE UPDATE, DEACTIVATION)
    // =========================================================================

    /**
     * GET /admin/searchParents or /management/searchParents
     * Live search for active parents to link siblings.
     */
    public function searchParents(): void {
        $this->requireAuth();

        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            $this->sendJson(['parents' => []]);
        }

        try {
            $parents = ParentModel::searchForSiblingLink($query);
            $this->sendJson(['parents' => $parents]);
        } catch (Throwable $e) {
            error_log('[Parent Search Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Could not complete parent search.'], 500);
        }
    }

    /**
     * POST /admin/updateParentProfile or /management/updateParentProfile
     * Updates parent workplace, contact, and address details.
     */
    public function updateParentProfile(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();

        // Concurrency token / CSRF verification
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? '');
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $code = trim($data['parentId'] ?? ($data['code'] ?? ''));
        if (empty($code)) {
            $this->sendJson(['error' => 'Parent code is required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);

            ParentActions::updateProfile($code, $data, $actorId);
            $updatedParentRow = ParentModel::findByCode($code);
            $freshVersion = $updatedParentRow ? ParentModel::version($updatedParentRow) : '';

            $this->sendJson([
                'success' => true,
                'message' => 'Parent profile updated successfully.',
                'version' => $freshVersion,
                'parent'  => $updatedParentRow ? SqlJsMapper::parentToJs($updatedParentRow) : null
            ], 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $statusCode = $e->getCode() === 409 ? 409 : 500;
            $this->sendJson(['error' => $e->getMessage()], $statusCode);
        } catch (Throwable $e) {
            error_log('[Parent Profile Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update parent profile.'], 500);
        }
    }

    /**
     * POST /admin/updateParentStatus or /management/updateParentStatus
     * Toggles parent status with Sibling-Guard protection.
     */
    public function updateParentStatus(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();
        $code = trim($data['parentId'] ?? ($data['code'] ?? ''));
        $status = trim($data['status'] ?? '');

        if (empty($code) || empty($status)) {
            $this->sendJson(['error' => 'Parent code and status are required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);

            if (strcasecmp($status, 'Deactivated') === 0 || strcasecmp($status, 'Inactive') === 0) {
                ParentActions::deactivate($code, $actorId);
            } else {
                $db = Database::getConnection();
                $stmt = $db->prepare(
                    "UPDATE user_accounts u
                     JOIN parents p ON p.account_id = u.id
                     SET u.activation_status = 'ACTIVE'
                     WHERE p.parent_id = ?"
                );
                $stmt->execute([$code]);
                AuditModel::record($actorId, $code, 'USER_ACTIVATED', "Parent {$code} reactivated.");
            }

            $this->sendJson([
                'success' => true,
                'status'  => $status,
                'message' => "Parent account {$code} status updated to {$status}."
            ], 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $statusCode = $e->getCode() === 409 ? 409 : 500;
            $this->sendJson(['error' => $e->getMessage()], $statusCode);
        } catch (Throwable $e) {
            error_log('[Parent Status Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update parent status.'], 500);
        }
    }

    // =========================================================================
    // SECTION 3: TEACHER ENDPOINTS
    // =========================================================================

    /**
     * POST /admin/registerTeacher or /management/registerTeacher
     * Registers a new teacher account.
     */
    public function registerTeacher(): void {
        $this->requireAuth();
        $this->validateCsrf();

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = TeacherActions::register($_POST, $actorId, $actor['identifier'] ?? null, $actor['role'] ?? 'admin');
            $this->sendJson($result, 201);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 409 ? 409 : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            error_log('[Teacher Registration Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'An unexpected server error occurred during teacher registration.'], 500);
        }
    }

    /**
     * POST /admin/updateTeacherProfile or /management/updateTeacherProfile
     */
    public function updateTeacherProfile(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? '');
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $staffId = trim($data['staffId'] ?? ($data['id'] ?? ''));
        if (empty($staffId)) {
            $this->sendJson(['error' => 'Teacher staff ID is required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = TeacherActions::updateProfile($staffId, $data, $actorId, $actor['identifier'] ?? null);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 409 ? 409 : ($e->getCode() === 404 ? 404 : 500);
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            error_log('[Teacher Profile Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update teacher profile.'], 500);
        }
    }

    /**
     * POST /admin/updateTeacherStatus or /management/updateTeacherStatus
     */
    public function updateTeacherStatus(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();
        $staffId = trim($data['staffId'] ?? ($data['id'] ?? ''));
        $status = trim($data['status'] ?? '');

        if (empty($staffId) || empty($status)) {
            $this->sendJson(['error' => 'Teacher staff ID and status are required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = TeacherActions::updateStatus($staffId, $status, $actorId, $actor['identifier'] ?? null);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 404 ? 404 : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            error_log('[Teacher Status Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update teacher status.'], 500);
        }
    }

    // =========================================================================
    // SECTION 4: MANAGEMENT ENDPOINTS
    // =========================================================================

    /**
     * POST /admin/registerManagement
     * Registers a new management panel member (Admin only).
     */
    public function registerManagement(): void {
        $this->requireAuth();
        $this->validateCsrf();

        try {
            $actor = $this->getActorDetails();
            if ($actor['role'] !== 'admin') {
                $this->sendJson(['error' => 'Only the Administrator can register management accounts.'], 403);
                return;
            }
            $actorId = (int)($actor['id'] ?? 1);
            $result = ManagementActions::register($_POST, $actorId, $actor['identifier'] ?? null, 'admin');
            $this->sendJson($result, 201);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 409 ? 409 : ($e->getCode() === 403 ? 403 : 500);
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            error_log('[Management Registration Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'An unexpected server error occurred during management registration.'], 500);
        }
    }

    /**
     * POST /admin/updateManagementProfile or /management/updateManagementProfile
     */
    public function updateManagementProfile(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();
        $token = $data['_csrf_token'] ?? ($_POST['_csrf_token'] ?? '');
        if (!$this->validateCsrfToken($token)) {
            $this->sendJson(['error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $staffId = trim($data['staffId'] ?? ($data['id'] ?? ''));
        if (empty($staffId)) {
            $this->sendJson(['error' => 'Management staff ID is required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = ManagementActions::updateProfile($staffId, $data, $actorId, $actor['identifier'] ?? null);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 409 ? 409 : ($e->getCode() === 404 ? 404 : 500);
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            error_log('[Management Profile Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update management profile.'], 500);
        }
    }

    /**
     * POST /admin/updateManagementStatus or /management/updateManagementStatus
     */
    public function updateManagementStatus(): void {
        $this->requireAuth();

        $data = $this->getRequestPayload();
        $staffId = trim($data['staffId'] ?? ($data['id'] ?? ''));
        $status = trim($data['status'] ?? '');

        if (empty($staffId) || empty($status)) {
            $this->sendJson(['error' => 'Management staff ID and status are required.'], 422);
            return;
        }

        try {
            $actor = $this->getActorDetails();
            $actorId = (int)($actor['id'] ?? 1);
            $result = ManagementActions::updateStatus($staffId, $status, $actorId, $actor['identifier'] ?? null);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 404 ? 404 : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            error_log('[Management Status Update Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update management status.'], 500);
        }
    }

    // =========================================================================
    // ROUTE ALIASES  (JS-facing names → canonical handler methods)
    // =========================================================================

    /**
     * Alias: POST /admin/registerStudent  →  admitStudent()
     * The people-directory.js form submits to `registerStudent`.
     */
    public function registerStudent(): void {
        $this->admitStudent();
    }

    /**
     * Alias: POST /admin/updateParent  →  updateParentProfile()
     * The profile-modal.js submits parent edits to `updateParent`.
     */
    public function updateParent(): void {
        $this->updateParentProfile();
    }

    /**
     * Alias: POST /admin/deactivateParent  →  updateParentStatus()
     * The parent-deactivation.js submits to `deactivateParent`.
     */
    public function deactivateParent(): void {
        $this->updateParentStatus();
    }
}
