<?php
/**
 * =========================================================================
 * L'ÉCOLE — CALENDAR EVENT ACTIONS (v4)
 * =========================================================================
 * Handles all mutations, transactional boundaries, role-specific write
 * permissions, interval collision math, soft-lock confirmation, supervision
 * hierarchy, and security audit logging for calendar events.
 *
 * Adheres strictly to the 10-Step Discipline & School Standards:
 *   1. Input validation & sanitization
 *   2. Actor validation & fail-fast
 *   3. Scope shape validation (schoolwide, grade, class, club, sport)
 *   4. Supervision hierarchy & scope ownership check (strictly re-derived from DB)
 *   5. Interval collision detection (A_start < B_end AND A_end > B_start) with soft-lock
 *   6. Denial auditing on permission failure
 *   7. Transaction boundary ($db->beginTransaction)
 *   8. Database write & soft delete
 *   9. Immutable audit log (AuditModel::record)
 *  10. Normalized return array with HTTP status
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/AuditModel.php';
require_once __DIR__ . '/CalendarEventModel.php';

class CalendarEventActions extends Model {

    private const ALLOWED_CATEGORIES = ['Academic', 'Extracurricular', 'General', 'Other'];
    private const STAFF_ROLES = ['admin', 'management'];
    private const VALID_SCOPES = ['schoolwide', 'grade', 'class', 'club', 'sport'];

    /** Validate scope type and presence of scope ID. */
    public static function validateScopeShape(string $scopeType, $scopeId): array {
        if (!in_array($scopeType, self::VALID_SCOPES, true)) {
            return ['ok' => false, 'error' => 'Invalid event scope.'];
        }
        if ($scopeType === 'schoolwide' && !empty($scopeId)) {
            return ['ok' => false, 'error' => 'A schoolwide event cannot have a scope ID.'];
        }
        if (in_array($scopeType, ['grade', 'class', 'club', 'sport'], true) && empty($scopeId)) {
            return ['ok' => false, 'error' => "A {$scopeType} event requires a target scope ID."];
        }
        return ['ok' => true];
    }

    /** Teacher scope ownership check — re-derived from DB. */
    public static function teacherOwnsScope(int $teacherId, string $scopeType, $scopeId): bool {
        if ($scopeType === 'class') {
            return CalendarEventModel::getClassIdForTeacher($teacherId) === (int)$scopeId;
        }
        if ($scopeType === 'club') {
            return in_array((int)$scopeId, CalendarEventModel::getClubIdsForTeacher($teacherId), true);
        }
        if ($scopeType === 'sport') {
            return in_array((int)$scopeId, CalendarEventModel::getSportIdsForTeacher($teacherId), true);
        }
        return false; // Teachers never own schoolwide or grade scopes
    }

    /** Normalize incoming scope definitions from hierarchical progressive disclosure form. */
    public static function normalizeScopes(?array $rawScopes, string $fallbackType = 'schoolwide', $fallbackId = null): array {
        $normalized = [];
        if (!empty($rawScopes) && is_array($rawScopes)) {
            foreach ($rawScopes as $s) {
                if (is_string($s)) {
                    $parts = explode(':', $s, 2);
                    $type = trim($parts[0]);
                    $id = isset($parts[1]) ? trim($parts[1]) : null;
                } else {
                    $type = trim($s['scope_type'] ?? ($s['type'] ?? ''));
                    $id = isset($s['scope_id']) ? trim((string)$s['scope_id']) : (isset($s['id']) ? trim((string)$s['id']) : null);
                }
                if ($type === 'school' || $type === 'schoolwide' || empty($type)) {
                    $normalized['schoolwide:all'] = ['scope_type' => 'schoolwide', 'scope_id' => 'all'];
                } elseif (in_array($type, self::VALID_SCOPES, true) && $id !== null && $id !== '') {
                    $normalized["{$type}:{$id}"] = ['scope_type' => $type, 'scope_id' => $id];
                }
            }
        }

        if (empty($normalized)) {
            if ($fallbackType === 'school' || $fallbackType === 'schoolwide' || empty($fallbackType)) {
                $normalized['schoolwide:all'] = ['scope_type' => 'schoolwide', 'scope_id' => 'all'];
            } else {
                $normalized["{$fallbackType}:{$fallbackId}"] = ['scope_type' => $fallbackType, 'scope_id' => (string)$fallbackId];
            }
        }

        return array_values($normalized);
    }

    /**
     * Exact mathematical interval collision detection:
     * A_start < B_end AND A_end > B_start.
     * Evaluates against calendar_events as well as calendar_event_scopes.
     */
    public static function detectClashInScope(string $eventDate, string $startTime, ?string $endTime, string $scopeType, $scopeId, ?int $excludeId = null): ?array {
        $db = Database::getConnection();
        $endWindow = $endTime ?: date('H:i:s', strtotime($startTime . ' +1 hour'));
        $cleanScopeId = ($scopeId !== null && $scopeId !== '' && $scopeId !== 'all') ? (string)$scopeId : null;

        $params = [
            ':event_date' => $eventDate,
            ':end_window' => $endWindow,
            ':start_time' => $startTime
        ];

        if ($scopeType === 'schoolwide' || $cleanScopeId === null) {
            $scopeSql = "(ce.scope_type = 'schoolwide' OR EXISTS (SELECT 1 FROM calendar_event_scopes ces WHERE ces.event_id = ce.id AND ces.scope_type = 'schoolwide'))";
        } else {
            $scopeSql = "((ce.scope_type = :scope_type AND ce.scope_id = :scope_id) OR EXISTS (SELECT 1 FROM calendar_event_scopes ces WHERE ces.event_id = ce.id AND ces.scope_type = :scope_type_ces AND ces.scope_id = :scope_id_ces))";
            $params[':scope_type'] = $scopeType;
            $params[':scope_id'] = $cleanScopeId;
            $params[':scope_type_ces'] = $scopeType;
            $params[':scope_id_ces'] = $cleanScopeId;
        }

        $excludeSql = "";
        if ($excludeId !== null) {
            $excludeSql = " AND ce.id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql = "
            SELECT ce.id, ce.title, ce.start_time, ce.end_time 
            FROM calendar_events ce
            WHERE ce.event_date = :event_date
              AND ce.deleted_at IS NULL
              AND {$scopeSql}
              AND (
                  ce.start_time < :end_window
                  AND COALESCE(ce.end_time, ADDTIME(ce.start_time, '01:00:00')) > :start_time
              )
              {$excludeSql}
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function detectClash(string $eventDate, string $startTime, ?string $endTime, string $scopeType, $scopeId, ?int $excludeId = null): ?array {
        return self::detectClashInScope($eventDate, $startTime, $endTime, $scopeType, $scopeId, $excludeId);
    }

    public static function detectMultiScopeClashes(string $eventDate, string $startTime, ?string $endTime, array $targetScopes, ?int $excludeId = null): ?array {
        foreach ($targetScopes as $ts) {
            $c = self::detectClashInScope($eventDate, $startTime, $endTime, $ts['scope_type'], $ts['scope_id'], $excludeId);
            if ($c) {
                $c['conflicting_scope_type'] = $ts['scope_type'];
                $c['conflicting_scope_id'] = $ts['scope_id'];
                return $c;
            }
        }
        return null;
    }

    private static function denyAndAudit(string $action, int $actorAccountId, ?string $actorIdentifier, string $reason): array {
        AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', $action, $reason);
        return ['success' => false, 'error' => $reason, 'http_status' => 403];
    }

    private static function parseTimeRange(?string $rawTime): array {
        if (empty($rawTime)) return [null, null];
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
        $actorRole = strtolower($actorRole);
        $title      = trim($payload['title'] ?? '');
        $date       = trim($payload['date'] ?? ($payload['event_date'] ?? ''));
        $rawTime    = trim($payload['start_time'] ?? ($payload['time'] ?? '09:00'));
        [$startTime, $endTime] = self::parseTimeRange($rawTime);
        if (!$startTime) $startTime = '09:00:00';

        $category   = trim($payload['category'] ?? 'Academic');
        $details    = trim($payload['details'] ?? '');
        $scopeType  = trim($payload['scope_type'] ?? ($payload['scopeType'] ?? ''));
        $scopeId    = $payload['scope_id'] ?? ($payload['scopeId'] ?? null);
        if ($scopeId !== null) $scopeId = trim((string)$scopeId);

        if (empty($title)) return ['success' => false, 'error' => 'Event title is required.', 'http_status' => 400];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return ['success' => false, 'error' => 'A valid date is required.', 'http_status' => 400];
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) $category = 'Academic';

        // Step 3: Resolve multi-scope targets
        $targetScopes = self::normalizeScopes($payload['scopes'] ?? null, $scopeType, $scopeId);
        $primaryScopeType = $targetScopes[0]['scope_type'];
        $primaryScopeId   = ($primaryScopeType === 'schoolwide') ? null : $targetScopes[0]['scope_id'];

        // Step 4: Permission & existence check for each scope
        foreach ($targetScopes as $ts) {
            $st = $ts['scope_type'];
            $si = $ts['scope_id'];

            if (in_array($actorRole, self::STAFF_ROLES, true)) {
                if ($st === 'grade' && !self::gradeExists($si)) {
                    return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, "Grade {$si} does not exist.");
                }
                if ($st === 'class' && !self::classExists((int)$si)) {
                    return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, "Class #{$si} does not exist.");
                }
                if ($st === 'club' && !self::clubExists((int)$si)) {
                    return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, "Club #{$si} does not exist.");
                }
                if ($st === 'sport' && !self::sportExists((int)$si)) {
                    return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, "Sport #{$si} does not exist.");
                }
            } elseif ($actorRole === 'teacher') {
                $teacherId = CalendarEventModel::getTeacherId($actorAccountId);
                if (!$teacherId || in_array($st, ['schoolwide', 'grade'], true) || !self::teacherOwnsScope($teacherId, $st, $si)) {
                    return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier,
                        'Teachers can only create events for a class they are Class Teacher of or a club/sport they are Teacher-in-Charge of.');
                }
            } else {
                return self::denyAndAudit('CALENDAR_EVENT_CREATE_DENIED', $actorAccountId, $actorIdentifier, 'You are not permitted to create calendar events.');
            }
        }

        // Step 5: Collision Detection with Soft-Lock Warning
        $allowParallel = !empty($payload['allow_parallel']) || !empty($payload['allowParallel']);
        $clash = self::detectMultiScopeClashes($date, $startTime, $endTime, $targetScopes);
        if ($clash && !$allowParallel) {
            $clashTime = substr($clash['start_time'], 0, 5) . ($clash['end_time'] ? '–' . substr($clash['end_time'], 0, 5) : '');
            $scopeNote = ($clash['conflicting_scope_type'] === 'schoolwide') ? 'schoolwide' : "{$clash['conflicting_scope_type']} #{$clash['conflicting_scope_id']}";
            return [
                'success'        => false,
                'clash'          => true,
                'existing_title' => $clash['title'],
                'time'           => $clashTime,
                'error'          => "Schedule conflict on {$scopeNote}: '{$clash['title']}' is already scheduled from {$clashTime}. If this is an intentional parallel schedule, check 'Allow parallel schedule' to proceed.",
                'http_status'    => 409
            ];
        }

        // Target audience parsing
        $rawAudience = $payload['audience'] ?? null;
        $audienceList = self::resolveAudience($rawAudience, $actorRole);

        // Author attribution
        $authorName = self::resolveAuthorName($actorAccountId, $actorIdentifier, $actorRole);

        // Steps 6–9: Transaction, write, audit, commit
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("
                INSERT INTO calendar_events (scope_type, scope_id, title, details, category, audience, event_date, start_time, end_time, created_by_account_id, author_role, author_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $primaryScopeType,
                $primaryScopeId,
                $title,
                $details,
                $category,
                json_encode(array_values($audienceList)),
                $date,
                $startTime,
                $endTime,
                $actorAccountId,
                $actorRole,
                $authorName
            ]);
            $newId = (int)$db->lastInsertId();

            // Insert into calendar_event_scopes
            $scopeStmt = $db->prepare("INSERT INTO calendar_event_scopes (event_id, scope_type, scope_id) VALUES (?, ?, ?)");
            foreach ($targetScopes as $ts) {
                $scopeStmt->execute([$newId, $ts['scope_type'], $ts['scope_id']]);
            }

            $db->commit();

            $scopeLabels = array_map(fn($s) => $s['scope_type'] === 'schoolwide' ? 'schoolwide' : "{$s['scope_type']} #{$s['scope_id']}", $targetScopes);
            $scopeLabel = implode(', ', $scopeLabels);
            $parallelSuffix = $allowParallel ? " (Parallel schedule acknowledged)" : "";
            AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_CREATED',
                "Created calendar event '{$title}' ({$scopeLabel}) for {$date}. Audience: [" . implode(', ', $audienceList) . "].{$parallelSuffix}");

            if ($clash && $allowParallel) {
                $clashTime = substr($clash['start_time'], 0, 5) . ($clash['end_time'] ? '–' . substr($clash['end_time'], 0, 5) : '');
                AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_PARALLEL_OVERRIDDEN',
                    "Parallel schedule override acknowledged for '{$title}' ({$scopeLabel}) alongside existing '{$clash['title']}' ({$clashTime}).");
            }

            $timeFormatted = substr($startTime, 0, 5) . ($endTime ? '–' . substr($endTime, 0, 5) : '');

            return [
                'success' => true,
                'id'      => $newId,
                'event'   => [
                    'id'            => (string)$newId,
                    'date'          => $date,
                    'time'          => $timeFormatted,
                    'title'         => $title,
                    'details'       => $details,
                    'category'      => $category,
                    'audience'      => $audienceList,
                    'scopeType'     => $primaryScopeType,
                    'scopeId'       => $primaryScopeId,
                    'scopes'        => $targetScopes,
                    'authorRole'    => $actorRole,
                    'authorName'    => $authorName
                ]
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CalendarEventActions] createEvent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while creating the event.', 'http_status' => 500];
        }
    }

    public static function updateEvent(int $id, array $payload, int $actorAccountId, ?string $actorIdentifier, string $actorRole): array {
        if ($id <= 0) return ['success' => false, 'error' => 'Valid event ID is required.', 'http_status' => 400];
        $actorRole = strtolower($actorRole);

        $title    = trim($payload['title'] ?? '');
        $rawTime  = trim($payload['start_time'] ?? ($payload['time'] ?? ''));
        [$startTime, $endTime] = self::parseTimeRange($rawTime);
        $category = trim($payload['category'] ?? 'Academic');
        $details  = trim($payload['details'] ?? '');
        $date     = trim($payload['date'] ?? ($payload['event_date'] ?? ''));

        if (empty($title)) return ['success' => false, 'error' => 'Event title is required.', 'http_status' => 400];
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) $category = 'Academic';

        $db = Database::getConnection();
        $existing = self::fetchEventById($db, $id);
        if (!$existing) return ['success' => false, 'error' => 'Event not found or has been deleted.', 'http_status' => 404];

        // Step 4: Supervision Hierarchy Guard
        $targetAuthorRole = strtolower($existing['author_role'] ?? 'admin');
        $targetCreatorId  = (int)($existing['created_by_account_id'] ?? 0);

        if ($actorRole === 'admin') {
            // Admin master override
        } elseif ($actorRole === 'management') {
            if ($targetAuthorRole === 'admin') {
                return self::denyAndAudit('CALENDAR_EVENT_UPDATE_DENIED', $actorAccountId, $actorIdentifier,
                    'Management cannot modify events created by the Administrator.');
            }
        } elseif ($actorRole === 'teacher') {
            if ($targetAuthorRole !== 'teacher' || $targetCreatorId !== $actorAccountId) {
                return self::denyAndAudit('CALENDAR_EVENT_UPDATE_DENIED', $actorAccountId, $actorIdentifier,
                    'Teachers can only edit events created by themselves for their assigned scope.');
            }
            $teacherId = CalendarEventModel::getTeacherId($actorAccountId);
            if (!$teacherId || !self::teacherOwnsScope($teacherId, $existing['scope_type'], $existing['scope_id'])) {
                return self::denyAndAudit('CALENDAR_EVENT_UPDATE_DENIED', $actorAccountId, $actorIdentifier,
                    'You do not have permission to edit this scope.');
            }
        } else {
            return self::denyAndAudit('CALENDAR_EVENT_UPDATE_DENIED', $actorAccountId, $actorIdentifier,
                'You are not permitted to edit calendar events.');
        }

        $effectiveDate = (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : $existing['event_date'];
        $effectiveStart = $startTime ?: ($existing['start_time'] ?? '09:00:00');
        $effectiveEnd = $endTime ?: $existing['end_time'];

        // Step 5: Resolve target scopes & Collision Detection on update
        $targetScopes = !empty($payload['scopes']) 
            ? self::normalizeScopes($payload['scopes']) 
            : self::normalizeScopes(null, $existing['scope_type'], $existing['scope_id']);

        $allowParallel = !empty($payload['allow_parallel']) || !empty($payload['allowParallel']);
        $clash = self::detectMultiScopeClashes($effectiveDate, $effectiveStart, $effectiveEnd, $targetScopes, $id);
        if ($clash && !$allowParallel) {
            $clashTime = substr($clash['start_time'], 0, 5) . ($clash['end_time'] ? '–' . substr($clash['end_time'], 0, 5) : '');
            $scopeNote = ($clash['conflicting_scope_type'] === 'schoolwide') ? 'schoolwide' : "{$clash['conflicting_scope_type']} #{$clash['conflicting_scope_id']}";
            return [
                'success'        => false,
                'clash'          => true,
                'existing_title' => $clash['title'],
                'time'           => $clashTime,
                'error'          => "Schedule conflict on {$scopeNote}: '{$clash['title']}' is already scheduled from {$clashTime}. If this is an intentional parallel schedule, check 'Allow parallel schedule' to proceed.",
                'http_status'    => 409
            ];
        }

        // Scope and Audience update if passed
        $primaryScope = $targetScopes[0] ?? ['scope_type' => 'schoolwide', 'scope_id' => null];
        $scopeSql = ", scope_type = ?, scope_id = ?";
        $params = [$title, $effectiveDate, $effectiveStart, $effectiveEnd, $category, $details, $primaryScope['scope_type'], $primaryScope['scope_id']];
        $audienceSql = "";
        if (isset($payload['audience'])) {
            $cleanAud = self::resolveAudience($payload['audience'], $actorRole);
            $audienceSql = ", audience = ?";
            $params[] = json_encode(array_values($cleanAud));
        }
        $params[] = $id;

        try {
            $db->beginTransaction();
            $db->prepare("UPDATE calendar_events SET title = ?, event_date = ?, start_time = ?, end_time = ?, category = ?, details = ? {$scopeSql} {$audienceSql} WHERE id = ?")
               ->execute($params);

            if (!empty($payload['scopes'])) {
                $db->prepare("DELETE FROM calendar_event_scopes WHERE event_id = ?")->execute([$id]);
                $insScope = $db->prepare("INSERT INTO calendar_event_scopes (event_id, scope_type, scope_id) VALUES (?, ?, ?)");
                foreach ($targetScopes as $ts) {
                    $insScope->execute([$id, $ts['scope_type'], $ts['scope_id']]);
                }
            }

            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_UPDATED',
                "Updated calendar event #{$id} ('{$title}').");

            if ($clash && $allowParallel) {
                $clashTime = substr($clash['start_time'], 0, 5) . ($clash['end_time'] ? '–' . substr($clash['end_time'], 0, 5) : '');
                AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_PARALLEL_OVERRIDDEN',
                    "Parallel schedule override acknowledged for event #{$id} ('{$title}') alongside existing '{$clash['title']}' ({$clashTime}).");
            }

            $timeFormatted = $effectiveStart ? (substr($effectiveStart, 0, 5) . ($effectiveEnd ? '–' . substr($effectiveEnd, 0, 5) : '')) : '';

            return [
                'success' => true,
                'event'   => [
                    'id'       => (string)$id,
                    'title'    => $title,
                    'time'     => $timeFormatted,
                    'date'     => $effectiveDate,
                    'category' => $category,
                    'details'  => $details
                ]
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CalendarEventActions] updateEvent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while updating the event.', 'http_status' => 500];
        }
    }

    public static function deleteEvent(int $id, int $actorAccountId, ?string $actorIdentifier, string $actorRole): array {
        if ($id <= 0) return ['success' => false, 'error' => 'Valid event ID is required.', 'http_status' => 400];
        $actorRole = strtolower($actorRole);

        $db = Database::getConnection();
        $existing = self::fetchEventById($db, $id);
        if (!$existing) return ['success' => false, 'error' => 'Event not found or has already been deleted.', 'http_status' => 404];

        // Supervision Hierarchy Check
        $targetAuthorRole = strtolower($existing['author_role'] ?? 'admin');
        $targetCreatorId  = (int)($existing['created_by_account_id'] ?? 0);

        if ($actorRole === 'admin') {
            // Admin can delete any event
        } elseif ($actorRole === 'management') {
            if ($targetAuthorRole === 'admin') {
                return self::denyAndAudit('CALENDAR_EVENT_DELETE_DENIED', $actorAccountId, $actorIdentifier,
                    'Management cannot delete events created by the Administrator.');
            }
        } elseif ($actorRole === 'teacher') {
            if ($targetAuthorRole !== 'teacher' || $targetCreatorId !== $actorAccountId) {
                return self::denyAndAudit('CALENDAR_EVENT_DELETE_DENIED', $actorAccountId, $actorIdentifier,
                    'Teachers can only delete events created by themselves for their assigned scope.');
            }
            $teacherId = CalendarEventModel::getTeacherId($actorAccountId);
            if (!$teacherId || !self::teacherOwnsScope($teacherId, $existing['scope_type'], $existing['scope_id'])) {
                return self::denyAndAudit('CALENDAR_EVENT_DELETE_DENIED', $actorAccountId, $actorIdentifier,
                    'You do not have permission to delete this event.');
            }
        } else {
            return self::denyAndAudit('CALENDAR_EVENT_DELETE_DENIED', $actorAccountId, $actorIdentifier,
                'You are not permitted to delete calendar events.');
        }

        try {
            $db->beginTransaction();
            // Soft delete: 4-week retention window
            $db->prepare("UPDATE calendar_events SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'User', 'CALENDAR_EVENT_DELETED',
                "Deleted calendar event #{$id} ('{$existing['title']}'). Retained for 4 weeks.");
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CalendarEventActions] deleteEvent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while deleting the event.', 'http_status' => 500];
        }
    }

    /**
     * Purge events that have been soft-deleted for more than 4 weeks (28 days).
     * Mirrors NoticeActions::purgeExpiredNotices.
     */
    public static function purgeExpiredDeletedEvents(): int {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("DELETE FROM calendar_events WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 4 WEEK)");
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            error_log('[CalendarEventActions] purgeExpired: ' . $e->getMessage());
            return 0;
        }
    }

    // ---- Convenience wrappers for compatibility ----------------------------
    public static function create(array $actor, string $scopeType, $scopeId, string $title, ?string $details, string $category, string $eventDate, ?string $timeRange = null): array {
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

    private static function resolveAudience($raw, string $actorRole): array {
        if ($raw === null) {
            return $actorRole === 'teacher' ? ['Students', 'Parents'] : ['All'];
        }
        $list = is_array($raw) ? $raw : json_decode($raw, true);
        if (!is_array($list)) {
            $list = array_map('trim', explode(',', (string)$raw));
        }
        $clean = array_values(array_filter(array_unique($list)));
        if (empty($clean)) {
            return $actorRole === 'teacher' ? ['Students', 'Parents'] : ['All'];
        }
        if ($actorRole === 'teacher') {
            // Teachers can only address students and/or parents
            $clean = array_values(array_intersect($clean, ['Students', 'Parents']));
            if (empty($clean)) $clean = ['Students', 'Parents'];
        }
        return $clean;
    }

    private static function resolveAuthorName(int $accountId, ?string $identifier, string $role): string {
        $db = Database::getConnection();
        if ($role === 'teacher') {
            $stmt = $db->prepare("SELECT full_name FROM teachers WHERE account_id = ?");
            $stmt->execute([$accountId]);
            $name = $stmt->fetchColumn();
            if ($name) return $name;
        }
        return match ($role) {
            'management' => 'Academic Management Board',
            'teacher'    => 'Faculty Staff',
            default      => 'Admin Office'
        };
    }

    private static function fetchEventById(PDO $db, int $id): ?array {
        $stmt = $db->prepare("SELECT id, title, scope_type, scope_id, author_role, created_by_account_id, event_date, start_time, end_time FROM calendar_events WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private static function gradeExists($gradeId): bool {
        if (empty($gradeId)) return false;
        $stmt = Database::getConnection()->prepare("SELECT 1 FROM grades WHERE id = ?");
        $stmt->execute([(string)$gradeId]);
        return (bool)$stmt->fetchColumn();
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
