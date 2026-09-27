<?php
/**
 * =========================================================================
 * L'ÉCOLE — NOTICE CRUD CONTROLLER TRAIT
 * =========================================================================
 * Shared between AdminController and ManagementController to provide
 * identical Notice Board operations, authority enforcement, and JSON APIs.
 * =========================================================================
 */

require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/NoticeActions.php';
require_once __DIR__ . '/../Models/AuditModel.php';

trait NoticeCrudTrait {

    /**
     * POST /admin/createNotice or /management/createNotice
     * Publishes a new announcement.
     */
    public function createNotice(): void {
        $this->requireAuth();
        $token = $_POST['_csrf_token'] ?? ($this->getRequestPayload()['_csrf_token'] ?? null);
        if (!$this->validateCsrf($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $user = $this->getUser();
        $actorId = (int)($user['id'] ?? 1);
        $actorRole = strtolower($user['role'] ?? 'admin');
        $defaultName = match($actorRole) {
            'management' => 'School Leadership',
            'teacher'    => 'Faculty Member',
            default      => 'Admin Office'
        };
        $actorName = $user['name'] ?? $defaultName;

        try {
            $file = !empty($_FILES['attachment']) ? $_FILES['attachment'] : null;
            $result = NoticeActions::createNotice($_POST, $file, $actorId, $actorName, $actorRole);
            $this->sendJson($result, 201);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = in_array($e->getCode(), [403, 404, 409], true) ? $e->getCode() : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (Throwable $e) {
            error_log('[Create Notice Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'An unexpected server error occurred while publishing notice.'], 500);
        }
    }

    /**
     * POST /admin/updateNotice or /management/updateNotice
     * Updates an existing announcement.
     */
    public function updateNotice(): void {
        $this->requireAuth();
        $token = $_POST['_csrf_token'] ?? ($this->getRequestPayload()['_csrf_token'] ?? null);
        if (!$this->validateCsrf($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $user = $this->getUser();
        $actorId = (int)($user['id'] ?? 1);
        $actorRole = strtolower($user['role'] ?? 'admin');

        $noticeId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($noticeId <= 0) {
            $this->sendJson(['error' => 'Valid notice ID is required.'], 422);
            return;
        }

        try {
            $file = !empty($_FILES['attachment']) ? $_FILES['attachment'] : null;
            $result = NoticeActions::updateNotice($noticeId, $_POST, $file, $actorId, $actorRole);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = in_array($e->getCode(), [403, 404, 409], true) ? $e->getCode() : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (Throwable $e) {
            error_log('[Update Notice Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to update notice.'], 500);
        }
    }

    /**
     * POST /admin/togglePinNotice or /management/togglePinNotice
     * Toggles a notice pin state.
     */
    public function togglePinNotice(): void {
        $this->requireAuth();
        $payload = $this->getRequestPayload();
        $token = $payload['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrf($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $user = $this->getUser();
        $actorId = (int)($user['id'] ?? 1);
        $actorRole = strtolower($user['role'] ?? 'admin');

        $noticeId = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($noticeId <= 0) {
            $this->sendJson(['error' => 'Valid notice ID is required.'], 422);
            return;
        }

        try {
            $result = NoticeActions::togglePin($noticeId, $actorId, $actorRole);
            $this->sendJson($result, 200);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = in_array($e->getCode(), [403, 404], true) ? $e->getCode() : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (Throwable $e) {
            error_log('[Toggle Pin Notice Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to toggle pin state.'], 500);
        }
    }

    /**
     * POST /admin/deleteNotice or /management/deleteNotice
     * Soft-deletes an announcement.
     */
    public function deleteNotice(): void {
        $this->requireAuth();
        $payload = $this->getRequestPayload();
        $token = $payload['_csrf_token'] ?? ($_POST['_csrf_token'] ?? null);
        if (!$this->validateCsrf($token)) {
            $this->sendJson(['success' => false, 'error' => 'Invalid or expired security token.'], 403);
            return;
        }

        $user = $this->getUser();
        $actorId = (int)($user['id'] ?? 1);
        $actorRole = strtolower($user['role'] ?? 'admin');

        $noticeId = isset($payload['id']) ? (int)$payload['id'] : 0;
        if ($noticeId <= 0) {
            $this->sendJson(['error' => 'Valid notice ID is required.'], 422);
            return;
        }

        try {
            $result = NoticeActions::deleteNotice($noticeId, $actorId, $actorRole);
            $this->sendJson($result, 200);
        } catch (RuntimeException $e) {
            $code = in_array($e->getCode(), [403, 404], true) ? $e->getCode() : 500;
            $this->sendJson(['error' => $e->getMessage()], $code);
        } catch (Throwable $e) {
            error_log('[Delete Notice Error] ' . $e->getMessage());
            $this->sendJson(['error' => 'Failed to delete notice.'], 500);
        }
    }
}
