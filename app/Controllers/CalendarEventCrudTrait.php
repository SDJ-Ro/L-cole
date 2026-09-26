<?php
/**
 * =========================================================================
 * L'ÉCOLE — CALENDAR EVENT CRUD CONTROLLER TRAIT (v3)
 * =========================================================================
 * Shared between TeacherController, AdminController, and ManagementController
 * conforming to MVC 6 Calendar CRUD Final Specification (v3).
 * =========================================================================
 */

require_once __DIR__ . '/../Models/CalendarEventModel.php';
require_once __DIR__ . '/../Models/CalendarEventActions.php';

trait CalendarEventCrudTrait {

    protected function getRequestPayload(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) return $decoded;
        }
        return $_POST ?? [];
    }

    protected function sendJson(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function getActorDetails(): array {
        $user = $this->getUser();
        return [
            'id'         => $user['id'] ?? null,
            'identifier' => $user['identifier'] ?? ($user['email'] ?? 'User'),
            'role'       => strtolower($user['role'] ?? 'teacher')
        ];
    }

    /** GET — used for the initial page's calendarConfig AND calendar.js's refetch-after-write. */
    public function getCalendarEvents(): void {
        $actor = $this->getActorDetails();
        $events = match ($actor['role']) {
            'admin', 'management' => CalendarEventModel::getAllEvents(),
            'teacher'             => CalendarEventModel::getEventsForTeacher((int)$actor['id']),
            default               => [],
        };
        $this->sendJson(['success' => true, 'events' => $events]);
    }

    public function addCalendarEvent(): void {
        $payload = $this->getRequestPayload();
        $actor = $this->getActorDetails();
        $result = CalendarEventActions::createEvent($payload, (int)$actor['id'], $actor['identifier'], $actor['role']);
        $status = $result['http_status'] ?? ($result['success'] ? 200 : 400);
        unset($result['http_status']);
        $this->sendJson($result, $status);
    }

    public function updateCalendarEvent(): void {
        $payload = $this->getRequestPayload();
        $id = (int)($payload['id'] ?? 0);
        $actor = $this->getActorDetails();
        $result = CalendarEventActions::updateEvent($id, $payload, (int)$actor['id'], $actor['identifier'], $actor['role']);
        $status = $result['http_status'] ?? ($result['success'] ? 200 : 400);
        unset($result['http_status']);
        $this->sendJson($result, $status);
    }

    public function deleteCalendarEvent(): void {
        $payload = $this->getRequestPayload();
        $id = (int)($payload['id'] ?? 0);
        $actor = $this->getActorDetails();
        $result = CalendarEventActions::deleteEvent($id, (int)$actor['id'], $actor['identifier'], $actor['role']);
        $status = $result['http_status'] ?? ($result['success'] ? 200 : 400);
        unset($result['http_status']);
        $this->sendJson($result, $status);
    }
}
