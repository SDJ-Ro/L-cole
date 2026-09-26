<?php
/**
 * =========================================================================
 * L'ÉCOLE — CALENDAR EVENT MODEL (v3)
 * =========================================================================
 * Read-only model adhering to MVC 6 Calendar CRUD Final Specification (v3).
 *
 * Implements strict role-scoped visibility:
 *   - Admin / Management: School-wide bypass (all events across all scopes)
 *   - Teacher: Schoolwide + owned class + subject-taught classes (read-only) + owned clubs/sports
 *   - Student: Schoolwide + enrolled class + active club/sport memberships
 *   - Parent: Schoolwide + children's classes + children's active club/sport memberships
 *   - Club/Sport Page: Scoped to specific club or sport
 *
 * Note: Queries avoid `DISTINCT` to guarantee 100% compatibility with
 * MySQL ONLY_FULL_GROUP_BY and prevent SQL 3065 errors.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';

class CalendarEventModel extends Model {

    private const SELECT_COLS = "
        id,
        DATE_FORMAT(event_date, '%Y-%m-%d') AS `date`,
        CASE WHEN end_time IS NOT NULL
             THEN CONCAT(DATE_FORMAT(start_time, '%H:%i'), '–', DATE_FORMAT(end_time, '%H:%i'))
             ELSE DATE_FORMAT(start_time, '%H:%i')
        END AS `time`,
        title, details, category, scope_type AS scopeType, scope_id AS scopeId
    ";

    // ---- identity resolution -------------------------------------------------

    public static function getTeacherId(int $accountId): ?int {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM teachers WHERE account_id = ?");
        $stmt->execute([$accountId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public static function getStudentId(int $accountId): ?int {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM students WHERE account_id = ?");
        $stmt->execute([$accountId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public static function getParentId(int $accountId): ?int {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM parents WHERE account_id = ?");
        $stmt->execute([$accountId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    // Aliases for compatibility
    public static function getTeacherIdForAccount(int $accountId): int {
        return self::getTeacherId($accountId) ?? 0;
    }
    public static function getStudentIdForAccount(int $accountId): int {
        return self::getStudentId($accountId) ?? 0;
    }
    public static function getParentIdForAccount(int $accountId): int {
        return self::getParentId($accountId) ?? 0;
    }

    // ---- scope lookups (used by both reads here and writes in Actions) ------

    /** class_teachers — capped at one class by the uq_ct_teacher constraint. */
    public static function getClassIdForTeacher(int $teacherId): ?int {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT class_id FROM class_teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    /** club_teachers — a teacher may be TIC of more than one club. */
    public static function getClubIdsForTeacher(int $teacherId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT club_id FROM club_teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** sport_teachers — same shape as clubs, separate table per the real UI's Club vs Sport split. */
    public static function getSportIdsForTeacher(int $teacherId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT sport_id FROM sport_teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** class_subject_teachers — READ-ONLY scope, never a write permission. */
    public static function getSubjectClassIdsForTeacher(int $teacherId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT class_id FROM class_subject_teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        return array_values(array_unique(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    public static function getClassIdForStudent(int $studentId): ?int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.id FROM classes c
            JOIN students s ON s.class_section = c.section_name
            WHERE s.id = ? LIMIT 1
        ");
        $stmt->execute([$studentId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public static function getClubIdsForStudent(int $studentId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT club_id FROM club_members WHERE student_id = ? AND status = 'active'");
        $stmt->execute([$studentId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function getSportIdsForStudent(int $studentId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT sport_id FROM sport_members WHERE student_id = ? AND status = 'active'");
        $stmt->execute([$studentId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function getStudentIdsForParent(int $parentId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT student_id FROM student_parents WHERE parent_id = ?");
        $stmt->execute([$parentId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // ---- role-scoped reads ----------------------------------------------------

    /** Admin & Management: explicit, intentional bypass — no scope restriction. */
    public static function getAllEvents(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT " . self::SELECT_COLS . " FROM calendar_events ORDER BY event_date ASC, start_time ASC");
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Teacher: schoolwide + owned class(es) + subject-taught class(es) READ ONLY + owned club(s)/sport(s). */
    public static function getEventsForTeacher(int $accountIdOrTeacherId): array {
        $teacherId = self::getTeacherId($accountIdOrTeacherId);
        if (!$teacherId) {
            // Check if passed directly as teacherId
            $teacherId = $accountIdOrTeacherId;
        }

        $classIds = array_unique(array_filter([
            self::getClassIdForTeacher($teacherId),
            ...self::getSubjectClassIdsForTeacher($teacherId),
        ]));
        $clubIds = self::getClubIdsForTeacher($teacherId);
        $sportIds = self::getSportIdsForTeacher($teacherId);

        return self::queryByScopes($classIds, $clubIds, $sportIds);
    }

    public static function getEventsForStudent(int $studentId): array {
        $classId = self::getClassIdForStudent($studentId);
        $clubIds = self::getClubIdsForStudent($studentId);
        $sportIds = self::getSportIdsForStudent($studentId);
        return self::queryByScopes($classId ? [$classId] : [], $clubIds, $sportIds);
    }

    public static function getEventsForParent(int $parentId): array {
        $studentIds = self::getStudentIdsForParent($parentId);
        $classIds = [];
        $clubIds = [];
        $sportIds = [];
        foreach ($studentIds as $sid) {
            $c = self::getClassIdForStudent($sid);
            if ($c) $classIds[] = $c;
            $clubIds = array_merge($clubIds, self::getClubIdsForStudent($sid));
            $sportIds = array_merge($sportIds, self::getSportIdsForStudent($sid));
        }
        return self::queryByScopes(array_unique($classIds), array_unique($clubIds), array_unique($sportIds));
    }

    /** The per-club calendar on every extracurricular detail page, club variant. */
    public static function getEventsForClub(int $clubId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT " . self::SELECT_COLS . " FROM calendar_events WHERE (scope_type = 'schoolwide' OR (scope_type = 'club' AND scope_id = ?)) ORDER BY event_date ASC, start_time ASC");
        $stmt->execute([$clubId]);
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Same, sport variant — separate method because it's a separate table/scope_type, not a style choice. */
    public static function getEventsForSport(int $sportId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT " . self::SELECT_COLS . " FROM calendar_events WHERE (scope_type = 'schoolwide' OR (scope_type = 'sport' AND scope_id = ?)) ORDER BY event_date ASC, start_time ASC");
        $stmt->execute([$sportId]);
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ---- format aliases for seamless view interoperability ------------------
    public static function getAllEventsFormatted(): array {
        return self::getAllEvents();
    }
    public static function getEventsForTeacherFormatted(int $id): array {
        return self::getEventsForTeacher($id);
    }
    public static function getEventsForStudentFormatted(int $id): array {
        return self::getEventsForStudent($id);
    }
    public static function getEventsForParentFormatted(int $id): array {
        return self::getEventsForParent($id);
    }
    public static function getEventsForClubFormatted(int $id): array {
        return self::getEventsForClub($id);
    }
    public static function getEventsForSportFormatted(int $id): array {
        return self::getEventsForSport($id);
    }

    // ---- internals --------------------------------------------------------

    private static function schoolwideOnly(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT " . self::SELECT_COLS . " FROM calendar_events WHERE scope_type = 'schoolwide' ORDER BY event_date ASC, start_time ASC");
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private static function queryByScopes(array $classIds, array $clubIds, array $sportIds = []): array {
        $db = Database::getConnection();
        $conditions = ["scope_type = 'schoolwide'"];
        $params = [];

        if ($classIds) {
            $conditions[] = "(scope_type = 'class' AND scope_id IN (" . implode(',', array_fill(0, count($classIds), '?')) . "))";
            $params = array_merge($params, array_values($classIds));
        }
        if ($clubIds) {
            $conditions[] = "(scope_type = 'club' AND scope_id IN (" . implode(',', array_fill(0, count($clubIds), '?')) . "))";
            $params = array_merge($params, array_values($clubIds));
        }
        if ($sportIds) {
            $conditions[] = "(scope_type = 'sport' AND scope_id IN (" . implode(',', array_fill(0, count($sportIds), '?')) . "))";
            $params = array_merge($params, array_values($sportIds));
        }

        // No DISTINCT anywhere — every ID array above is already deduped with array_unique()
        // before it gets here, and one event's scope_type can only ever match one OR branch,
        // so duplicate rows are structurally impossible. This prevents MySQL error 3065.
        $stmt = $db->prepare("SELECT " . self::SELECT_COLS . " FROM calendar_events
                               WHERE " . implode(' OR ', $conditions) . "
                               ORDER BY event_date ASC, start_time ASC");
        $stmt->execute($params);
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function castIds(array $events): array {
        return array_map(function ($ev) {
            $ev['id'] = (string)$ev['id'];
            return $ev;
        }, $events);
    }

    public static function getScopeOptionsForStaff(): array {
        $db = Database::getConnection();
        $options = [
            ['type' => 'schoolwide', 'id' => null, 'label' => 'Schoolwide (All Users)']
        ];

        try {
            $classes = $db->query("SELECT id, section_name FROM classes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($classes as $c) {
                $options[] = [
                    'type'  => 'class',
                    'id'    => (int)$c['id'],
                    'label' => 'Class: ' . $c['section_name'],
                ];
            }

            $clubs = $db->query("SELECT id, name FROM clubs ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($clubs as $cl) {
                $options[] = [
                    'type'  => 'club',
                    'id'    => (int)$cl['id'],
                    'label' => 'Club: ' . $cl['name'],
                ];
            }

            $sports = $db->query("SELECT id, name FROM sports ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($sports as $sp) {
                $options[] = [
                    'type'  => 'sport',
                    'id'    => (int)$sp['id'],
                    'label' => 'Sport: ' . $sp['name'],
                ];
            }
        } catch (\Throwable $e) {
            error_log('[CalendarEventModel] getScopeOptionsForStaff: ' . $e->getMessage());
        }

        return $options;
    }

    public static function getScopeOptionsForTeacher(int $teacherId): array {
        $db = Database::getConnection();
        $options = [];

        try {
            $classId = self::getClassIdForTeacher($teacherId);
            if ($classId) {
                $stmt = $db->prepare("SELECT section_name FROM classes WHERE id = ?");
                $stmt->execute([$classId]);
                $sec = $stmt->fetchColumn() ?: "Class #{$classId}";
                $options[] = [
                    'type'  => 'class',
                    'id'    => $classId,
                    'label' => 'My Class (' . $sec . ')',
                ];
            }

            $clubIds = self::getClubIdsForTeacher($teacherId);
            if (!empty($clubIds)) {
                $inClause = implode(',', array_fill(0, count($clubIds), '?'));
                $stmt = $db->prepare("SELECT id, name FROM clubs WHERE id IN ($inClause) ORDER BY name ASC");
                $stmt->execute(array_values($clubIds));
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $cl) {
                    $options[] = [
                        'type'  => 'club',
                        'id'    => (int)$cl['id'],
                        'label' => 'Club: ' . $cl['name'],
                    ];
                }
            }

            $sportIds = self::getSportIdsForTeacher($teacherId);
            if (!empty($sportIds)) {
                $inClause = implode(',', array_fill(0, count($sportIds), '?'));
                $stmt = $db->prepare("SELECT id, name FROM sports WHERE id IN ($inClause) ORDER BY name ASC");
                $stmt->execute(array_values($sportIds));
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $sp) {
                    $options[] = [
                        'type'  => 'sport',
                        'id'    => (int)$sp['id'],
                        'label' => 'Sport: ' . $sp['name'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log('[CalendarEventModel] getScopeOptionsForTeacher: ' . $e->getMessage());
        }

        return $options;
    }
}
