<?php
/**
 * =========================================================================
 * L'ÉCOLE — CALENDAR EVENT ACTIONS (v3)
 * =========================================================================
 * Handles all mutations, transactional boundaries, role-specific write
 * permissions, scope validation, and security audit logging for calendar events.
 *
 * Adheres strictly to the 10-Step Discipline from MVC6 v3:
 *   1. Input validation & sanitization
 *   2. Actor validation & fail-fast
 *   3. Scope shape validation & internal consistency
 *   4. Business permission check (strictly re-derived from DB)
 *   5. Denial auditing on permission failure
 *   6. Transaction boundary ($db->beginTransaction)
 *   7. Database write
 *   8. Immutable audit log (AuditModel::record)
 *   9. Commit transaction
 *  10. Normalized return array with HTTP status
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/AuditModel.php';
require_once __DIR__ . '/CalendarEventModel.php';

class CalendarEventActions extends Model {

    private const ALLOWED_CATEGORIES = ['Academic', 'Extracurricular', 'Other'];
    private const STAFF_ROLES = ['admin', 'management'];

    /** Steps 1+3 combined: validate the incoming scope_type/scope_id pair is internally consistent. */
    public static function validateScopeShape(string $scopeType, ?int $scopeId): array {
        if (!in_array($scopeType, ['schoolwide', 'class', 'club', 'sport'], true)) {
            return ['ok' => false, 'error' => 'Invalid event scope.'];
        }
        if ($scopeType === 'schoolwide' && $scopeId !== null && $scopeId > 0) {
            return ['ok' => false, 'error' => 'A schoolwide event cannot have a scope ID.'];
        }
        if (in_array($scopeType, ['class', 'club', 'sport'], true) && !$scopeId) {
            return ['ok' => false, 'error' => 'A class, club, or sport event requires a scope ID.'];
        }
        return ['ok' => true];
    }

    /** Step 4: Teacher permission check — always re-derived from the DB, never trusts the browser. */
    public static function teacherOwnsScope(int $teacherId, string $scopeType, int $scopeId): bool {
        if ($scopeType === 'class') {
            return CalendarEventModel::getClassIdForTeacher($teacherId) === $scopeId;
        }
        if ($scopeType === 'club') {
            return in_array($scopeId, CalendarEventModel::getClubIdsForTeacher($teacherId), true);
        }
        if ($scopeType === 'sport') {
            return in_array($scopeId, CalendarEventModel::getSportIdsForTeacher($teacherId), true);
        }
        return false; // Teachers never own 'schoolwide'.
    }

    private static function denyAndAudit(string $action, int $actorAccountId, ?string $actorIdentifier, string $reason): array {
        AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', $action, $reason);
        return ['success' => false, 'error' => $reason, 'http_status' => 403];
    }

    private static function parseTimeRange(?string $rawTime): array {
        if (empty($rawTime)) {
            return [null, null];
        }
        $parts = preg_split('/\s*[-–—]\s*/u', trim($rawTime));
        $start = self::normalizeTime($parts[0] ?? null);
        $end = isset($parts[1]) ? self::normalizeTime($parts[1]) : null;
        return [$start, $end];
    }

    private static function normalizeTime(?string $t): ?string {
        if (!$t) return null;
        $t = trim($t);
        $timestamp = strtotime($t);
        if ($timestamp !== false) {
            return date('H:i:s', $timestamp);
        }
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $t, $m)) {
            return sprintf('%02d:%02d:%02d', (int)$m[1], (int)$m[2], isset($m[3]) ? (int)$m[3] : 0);
        }
        return null;
    }

    public static function createEvent(array $payload, int $actorAccountId, ?string $actorIdentifier, string $actorRole): array {
        // Step 1: validate input
        $title      = trim($payload['title'] ?? '');
        $date       = trim($payload['date'] ?? ($payload['event_date'] ?? ''));
        $rawTime    = trim($payload['start_time'] ?? ($payload['time'] ?? '09:00'));
        [$startTime, $endTime] = self::parseTimeRange($rawTime);
        if (!$startTime) $startTime = '09:00:00';

        $category   = trim($payload['category'] ?? 'Academic');
        $details    = trim($payload['details'] ?? '');
        $scopeType  = trim($payload['scope_type'] ?? ($payload['scopeType'] ?? ''));
        $scopeId    = isset($payload['scope_id']) && $payload['scope_id'] !== '' ? (int)$payload['scope_id'] : (isset($payload['scopeId']) ? (int)$payload['scopeId'] : null);

        if (empty($title)) return ['success' => false, 'error' => 'Event title is required.', 'http_status' => 400];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return ['success' => false, 'error' => 'A valid date is required.', 'http_status' => 400];
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) $category = 'Academic';

        // Normalize schoolwide
        if ($scopeType === 'school' || empty($scopeType)) {
            $scopeType = 'schoolwide';
            $scopeId = null;
        }

        // Step 3: resolve/validate scope shape
        $shapeCheck = self::validateScopeShape($scopeType, $scopeId);
        if (!$shapeCheck['ok']) return ['success' => false, 'error' => $shapeCheck['error'], 'http_status' => 400];

        // Step 4: business permission check
        if (in_array($actorRole, self::STAFF_ROLES, true)) {
            // Staff: any scope, but scope_id must exist
            if ($scopeType === 'class' && !self::classExists($scopeId)) {
                return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, 'That class does not exist.');
            }
            if ($scopeType === 'club' && !self::clubExists($scopeId)) {
                return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, 'That club does not exist.');
            }
            if ($scopeType === 'sport' && !self::sportExists($scopeId)) {
                return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, 'That sport does not exist.');
            }
        } elseif ($actorRole === 'teacher') {
            $teacherId = CalendarEventModel::getTeacherId($actorAccountId);
            if (!$teacherId || $scopeType === 'schoolwide' || !self::teacherOwnsScope($teacherId, $scopeType, (int)$scopeId)) {
                return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier,
                    'You can only create events for a class you are Class Teacher of or a club/sport you are Teacher-in-Charge of.');
            }
        } else {
            return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, 'You are not permitted to create calendar events.');
        }

        // Steps 5–9: transaction, write, audit, commit
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("
                INSERT INTO calendar_events (scope_type, scope_id, title, details, category, event_date, start_time, end_time, created_by_account_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$scopeType, $scopeId, $title, $details, $category, $date, $startTime, $endTime, $actorAccountId]);
            $newId = (int)$db->lastInsertId();
            $db->commit();

            $scopeLabel = $scopeType === 'schoolwide' ? 'schoolwide' : "{$scopeType} #{$scopeId}";
            AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_CREATED',
                "Created calendar event '{$title}' ({$scopeLabel}) for {$date}.");

            $timeFormatted = substr($startTime, 0, 5) . ($endTime ? '–' . substr($endTime, 0, 5) : '');

            // Step 10: normalized return
            return ['success' => true, 'id' => $newId, 'event' => [
                'id' => (string)$newId, 'date' => $date, 'time' => $timeFormatted, 'title' => $title,
                'details' => $details, 'category' => $category, 'scopeType' => $scopeType, 'scopeId' => $scopeId,
            ]];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CalendarEventActions] createEvent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while creating the event.', 'http_status' => 500];
        }
    }

    public static function updateEvent(int $id, array $payload, int $actorAccountId, ?string $actorIdentifier, string $actorRole): array {
        if ($id <= 0) return ['success' => false, 'error' => 'Valid event ID is required.', 'http_status' => 400];

        $title    = trim($payload['title'] ?? '');
        $rawTime  = trim($payload['start_time'] ?? ($payload['time'] ?? ''));
        [$startTime, $endTime] = self::parseTimeRange($rawTime);
        $category = trim($payload['category'] ?? 'Academic');
        $details  = trim($payload['details'] ?? '');
        $date     = trim($payload['date'] ?? ($payload['event_date'] ?? ''));

        if (empty($title)) return ['success' => false, 'error' => 'Event title is required.', 'http_status' => 400];
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) $category = 'Academic';

        $db = Database::getConnection();

        // ALWAYS re-derive scope from the DB — never trust scope_type/scope_id from the request body.
        $existing = self::fetchEventById($db, $id);
        if (!$existing) return ['success' => false, 'error' => 'Event not found.', 'http_status' => 404];

        if (in_array($actorRole, self::STAFF_ROLES, true)) {
            // authorized — no further check
        } elseif ($actorRole === 'teacher') {
            $teacherId = CalendarEventModel::getTeacherId($actorAccountId);
            if (!$teacherId || !self::teacherOwnsScope($teacherId, $existing['scope_type'], (int)$existing['scope_id'])) {
                return self::denyAndAudit('CALENDAR_EVENT_UPDATE_DENIED', $actorAccountId, $actorIdentifier, 'You do not have permission to edit this event.');
            }
        } else {
            return self::denyAndAudit('CALENDAR_EVENT_UPDATE_DENIED', $actorAccountId, $actorIdentifier, 'You are not permitted to edit calendar events.');
        }

        try {
            $db->beginTransaction();
            if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $db->prepare("UPDATE calendar_events SET title = ?, event_date = ?, start_time = ?, end_time = ?, category = ?, details = ? WHERE id = ?")
                   ->execute([$title, $date, $startTime, $endTime, $category, $details, $id]);
            } else {
                $db->prepare("UPDATE calendar_events SET title = ?, start_time = ?, end_time = ?, category = ?, details = ? WHERE id = ?")
                   ->execute([$title, $startTime, $endTime, $category, $details, $id]);
            }
            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_UPDATED', "Updated calendar event #{$id} ('{$title}').");
            $timeFormatted = $startTime ? (substr($startTime, 0, 5) . ($endTime ? '–' . substr($endTime, 0, 5) : '')) : '';

            return ['success' => true, 'event' => [
                'id' => (string)$id,
                'title' => $title,
                'time' => $timeFormatted,
                'date' => !empty($date) ? $date : $existing['event_date'],
                'category' => $category,
                'details' => $details
            ]];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CalendarEventActions] updateEvent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while updating the event.', 'http_status' => 500];
        }
    }

    public static function deleteEvent(int $id, int $actorAccountId, ?string $actorIdentifier, string $actorRole): array {
        if ($id <= 0) return ['success' => false, 'error' => 'Valid event ID is required.', 'http_status' => 400];

        $db = Database::getConnection();
        $existing = self::fetchEventById($db, $id);
        if (!$existing) return ['success' => false, 'error' => 'Event not found.', 'http_status' => 404];

        if (in_array($actorRole, self::STAFF_ROLES, true)) {
            // authorized
        } elseif ($actorRole === 'teacher') {
            $teacherId = CalendarEventModel::getTeacherId($actorAccountId);
            if (!$teacherId || !self::teacherOwnsScope($teacherId, $existing['scope_type'], (int)$existing['scope_id'])) {
                return self::denyAndAudit('CALENDAR_EVENT_DELETE_DENIED', $actorAccountId, $actorIdentifier, 'You do not have permission to delete this event.');
            }
        } else {
            return self::denyAndAudit('CALENDAR_EVENT_DELETE_DENIED', $actorAccountId, $actorIdentifier, 'You are not permitted to delete calendar events.');
        }

        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM calendar_events WHERE id = ?")->execute([$id]);
            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_DELETED', "Deleted calendar event #{$id} ('{$existing['title']}').");
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CalendarEventActions] deleteEvent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while deleting the event.', 'http_status' => 500];
        }
    }

    // ---- Convenience wrappers for compatibility ----------------------------
    public static function create(array $actor, string $scopeType, int $scopeId, string $title, ?string $details, string $category, string $eventDate, ?string $timeRange = null): array {
        $payload = [
            'scope_type' => $scopeType,
            'scope_id'   => $scopeId,
            'title'      => $title,
            'details'    => $details,
            'category'   => $category,
            'event_date' => $eventDate,
            'time'       => $timeRange,
        ];
        return self::createEvent($payload, (int)($actor['id'] ?? 0), $actor['identifier'] ?? null, $actor['role'] ?? 'teacher');
    }

    public static function update(array $actor, int $eventId, array $fields): array {
        return self::updateEvent($eventId, $fields, (int)($actor['id'] ?? 0), $actor['identifier'] ?? null, $actor['role'] ?? 'teacher');
    }

    public static function delete(array $actor, int $eventId): array {
        return self::deleteEvent($eventId, (int)($actor['id'] ?? 0), $actor['identifier'] ?? null, $actor['role'] ?? 'teacher');
    }

    // ---- Internals ---------------------------------------------------------
    private static function fetchEventById(PDO $db, int $id): ?array {
        $stmt = $db->prepare("SELECT id, title, scope_type, scope_id FROM calendar_events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private static function classExists(?int $id): bool {
        if (!$id) return false;
        $stmt = Database::getConnection()->prepare("SELECT 1 FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    private static function clubExists(?int $id): bool {
        if (!$id) return false;
        $stmt = Database::getConnection()->prepare("SELECT 1 FROM clubs WHERE id = ?");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    private static function sportExists(?int $id): bool {
        if (!$id) return false;
        $stmt = Database::getConnection()->prepare("SELECT 1 FROM sports WHERE id = ?");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }
}
