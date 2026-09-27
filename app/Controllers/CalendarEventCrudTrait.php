<?php
/**
 * =========================================================================
 * L'ÉCOLE — CALENDAR EVENT CRUD CONTROLLER TRAIT
 * =========================================================================
 * Shared across AdminController, ManagementController, TeacherController,
 * StudentController, and ParentController to provide unified Calendar APIs,
 * role-scoped event retrieval, and server-side authorization guards.
 * =========================================================================
 */

require_once __DIR__ . '/../Models/CalendarEventModel.php';
require_once __DIR__ . '/../Models/CalendarEventActions.php';

trait CalendarEventCrudTrait {

    /**
     * GET /{role}/getCalendarEvents
     * Returns calendar events strictly scoped to the authenticated role.
     */
    public function getCalendarEvents(): void {
        $actor = $this->getActorDetails();
        $actorId = (int)($actor['id'] ?? 0);

        $scopeType = $_GET['scope_type'] ?? null;
        $scopeId   = isset($_GET['scope_id']) && $_GET['scope_id'] !== '' ? (int)$_GET['scope_id'] : null;

        if ($scopeType === 'club' && $scopeId) {
            $events = CalendarEventModel::getEventsForClub($scopeId);
        } elseif ($scopeType === 'sport' && $scopeId) {
            $events = CalendarEventModel::getEventsForSport($scopeId);
        } else {
            $events = match ($actor['role']) {
                'admin', 'management' => CalendarEventModel::getAllEvents(),
                'teacher'             => CalendarEventModel::getEventsForTeacher($actorId),
                'student'             => CalendarEventModel::getEventsForStudent($actorId),
                'parent'              => CalendarEventModel::getEventsForParent($actorId),
                default               => [],
            };
        }

        $this->sendJson(['success' => true, 'events' => $events]);
    }

    /**
     * POST /{role}/addCalendarEvent
     * Admin, Management, and Teacher (for owned scope).
     */
    public function addCalendarEvent(): void {
        $payload = $this->getRequestPayload();
        $csrfToken = $payload['_csrf_token'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            $this->sendJson(['success' => false, 'error' => 'Security token invalid or expired.'], 403);
            return;
        }

        $actor = $this->getActorDetails();
        if (!in_array($actor['role'], ['admin', 'management', 'teacher'], true)) {
            $this->sendJson(['success' => false, 'error' => 'Students and parents cannot create calendar events.'], 403);
            return;
        }

        $result = CalendarEventActions::createEvent($payload, (int)$actor['id'], $actor['identifier'], $actor['role']);
        $status = $result['http_status'] ?? ($result['success'] ? 200 : 400);
        unset($result['http_status']);
        $this->sendJson($result, $status);
    }

    /**
     * POST /{role}/updateCalendarEvent
     * Admin, Management, and Teacher (for owned scope).
     */
    public function updateCalendarEvent(): void {
        $payload = $this->getRequestPayload();
        $csrfToken = $payload['_csrf_token'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            $this->sendJson(['success' => false, 'error' => 'Security token invalid or expired.'], 403);
            return;
        }

        $id = (int)($payload['id'] ?? 0);
        $actor = $this->getActorDetails();
        if (!in_array($actor['role'], ['admin', 'management', 'teacher'], true)) {
            $this->sendJson(['success' => false, 'error' => 'Students and parents cannot edit calendar events.'], 403);
            return;
        }

        $result = CalendarEventActions::updateEvent($id, $payload, (int)$actor['id'], $actor['identifier'], $actor['role']);
        $status = $result['http_status'] ?? ($result['success'] ? 200 : 400);
        unset($result['http_status']);
        $this->sendJson($result, $status);
    }

    /**
     * POST /{role}/deleteCalendarEvent
     * Admin, Management, and Teacher (for owned scope).
     */
    public function deleteCalendarEvent(): void {
        $payload = $this->getRequestPayload();
        $csrfToken = $payload['_csrf_token'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            $this->sendJson(['success' => false, 'error' => 'Security token invalid or expired.'], 403);
            return;
        }

        $id = (int)($payload['id'] ?? 0);
        $actor = $this->getActorDetails();
        if (!in_array($actor['role'], ['admin', 'management', 'teacher'], true)) {
            $this->sendJson(['success' => false, 'error' => 'Students and parents cannot delete calendar events.'], 403);
            return;
        }

        $result = CalendarEventActions::deleteEvent($id, (int)$actor['id'], $actor['identifier'], $actor['role']);
        $status = $result['http_status'] ?? ($result['success'] ? 200 : 400);
        unset($result['http_status']);
        $this->sendJson($result, $status);
    }
}
