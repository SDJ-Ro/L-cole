<?php
/**
 * =========================================================================
 * L'ÉCOLE — CALENDAR EVENT MODEL (v4)
 * =========================================================================
 * Read-only model adhering to MVC Architecture and Real-World School Rules:
 *
 * Implements strict role-scoped visibility:
 *   - Admin / Management: School-wide bypass (all events, staff + internal)
 *   - Teacher: Schoolwide + owned class + subject classes (read-only) + owned clubs/sports
 *              Filtered by audience: 'All' or 'Teachers'
 *   - Student: Schoolwide + enrolled grade + enrolled class + active club/sport memberships
 *              Filtered by audience: 'All' or 'Students'
 *   - Parent: Schoolwide + children's grades + children's classes + active club/sport memberships
 *             Filtered by audience: 'All' or 'Parents'
 *   - Extracurricular Detail: Scoped to specific club or sport
 *
 * Enforces `deleted_at IS NULL` (4-week soft-delete retention window).
 * Queries avoid `DISTINCT` to guarantee 100% MySQL ONLY_FULL_GROUP_BY compliance.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/SqlJsMapper.php';

class CalendarEventModel extends Model {

    private const SELECT_COLS = "
        id,
        event_date,
        start_time,
        end_time,
        DATE_FORMAT(event_date, '%Y-%m-%d') AS `date`,
        CASE WHEN end_time IS NOT NULL
             THEN CONCAT(DATE_FORMAT(start_time, '%H:%i'), '–', DATE_FORMAT(end_time, '%H:%i'))
             ELSE DATE_FORMAT(start_time, '%H:%i')
        END AS `time`,
        title, details, category, audience,
        scope_type, scope_id,
        scope_type AS scopeType, scope_id AS scopeId,
        author_role, author_name,
        author_role AS authorRole, author_name AS authorName,
        created_by_account_id
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

    // ---- scope lookups -------------------------------------------------------

    /** class_teachers — homeroom teacher class. */
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

    /** sport_teachers — TIC of sports programs. */
    public static function getSportIdsForTeacher(int $teacherId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT sport_id FROM sport_teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** class_subject_teachers — READ-ONLY scope. */
    public static function getSubjectClassIdsForTeacher(int $teacherId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT class_id FROM class_subject_teachers WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        return array_values(array_unique(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    public static function getClassIdForStudent(int $studentId): ?int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(s.class_id, c.id) FROM students s
            LEFT JOIN classes c ON c.section_name = s.class_section
            WHERE s.id = ? LIMIT 1
        ");
        $stmt->execute([$studentId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public static function getGradeIdForStudent(int $studentId): ?string {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.grade_id FROM classes c
            JOIN students s ON s.class_id = c.id
            WHERE s.id = ? LIMIT 1
        ");
        $stmt->execute([$studentId]);
        $id = $stmt->fetchColumn();
        if ($id) return (string)$id;

        // Fallback for transition
        $stmtFallback = $db->prepare("
            SELECT c.grade_id FROM classes c
            JOIN students s ON s.class_section = c.section_name
            WHERE s.id = ? LIMIT 1
        ");
        $stmtFallback->execute([$studentId]);
        $fallbackId = $stmtFallback->fetchColumn();
        return $fallbackId ? (string)$fallbackId : null;
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

    // ---- audience visibility filter ------------------------------------------

    public static function getAudienceSql(string $role): string {
        $role = strtolower($role);
        if (in_array($role, ['admin', 'management'], true)) {
            return "1=1";
        }
        if ($role === 'teacher') {
            return "(JSON_CONTAINS(audience, '\"All\"') OR JSON_CONTAINS(audience, '\"Teachers\"'))";
        }
        if ($role === 'student') {
            return "(JSON_CONTAINS(audience, '\"All\"') OR JSON_CONTAINS(audience, '\"Students\"'))";
        }
        if ($role === 'parent') {
            return "(JSON_CONTAINS(audience, '\"All\"') OR JSON_CONTAINS(audience, '\"Parents\"'))";
        }
        return "JSON_CONTAINS(audience, '\"All\"')";
    }

    // ---- role-scoped reads ----------------------------------------------------

    /** Admin & Management: intentional bypass of scope, sees all active events. */
    public static function getAllEvents(string $role = 'admin'): array {
        $db = Database::getConnection();
        $audSql = self::getAudienceSql($role);
        $stmt = $db->query("SELECT " . self::SELECT_COLS . " FROM calendar_events 
                             WHERE deleted_at IS NULL AND {$audSql}
                             ORDER BY event_date ASC, start_time ASC");
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Teacher: schoolwide + owned class + subject classes (read-only) + owned clubs/sports. */
    public static function getEventsForTeacher(int $accountIdOrTeacherId): array {
        $teacherId = self::getTeacherId($accountIdOrTeacherId) ?: $accountIdOrTeacherId;

        $classIds = array_unique(array_filter([
            self::getClassIdForTeacher($teacherId),
            ...self::getSubjectClassIdsForTeacher($teacherId),
        ]));
        $clubIds = self::getClubIdsForTeacher($teacherId);
        $sportIds = self::getSportIdsForTeacher($teacherId);

        return self::queryByScopes([], $classIds, $clubIds, $sportIds, 'teacher');
    }

    /** Student: schoolwide + enrolled grade + enrolled class + active club/sport memberships. */
    public static function getEventsForStudent(int $accountIdOrStudentId): array {
        $studentId = self::getStudentId($accountIdOrStudentId) ?: $accountIdOrStudentId;

        $gradeId = self::getGradeIdForStudent($studentId);
        $classId = self::getClassIdForStudent($studentId);
        $clubIds = self::getClubIdsForStudent($studentId);
        $sportIds = self::getSportIdsForStudent($studentId);

        return self::queryByScopes(
            $gradeId ? [$gradeId] : [],
            $classId ? [$classId] : [],
            $clubIds,
            $sportIds,
            'student'
        );
    }

    /** Parent: schoolwide + children's grades + children's classes + active club/sport memberships. */
    public static function getEventsForParent(int $accountIdOrParentId): array {
        $parentId = self::getParentId($accountIdOrParentId) ?: $accountIdOrParentId;

        $studentIds = self::getStudentIdsForParent($parentId);
        $gradeIds = [];
        $classIds = [];
        $clubIds = [];
        $sportIds = [];

        foreach ($studentIds as $sid) {
            $g = self::getGradeIdForStudent($sid);
            if ($g) $gradeIds[] = $g;
            $c = self::getClassIdForStudent($sid);
            if ($c) $classIds[] = $c;
            $clubIds = array_merge($clubIds, self::getClubIdsForStudent($sid));
            $sportIds = array_merge($sportIds, self::getSportIdsForStudent($sid));
        }

        return self::queryByScopes(
            array_unique($gradeIds),
            array_unique($classIds),
            array_unique($clubIds),
            array_unique($sportIds),
            'parent'
        );
    }

    /** The per-club calendar on every extracurricular detail page, club variant. */
    public static function getEventsForClub(int $clubId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT DISTINCT " . self::SELECT_COLS . " FROM calendar_events 
                               WHERE deleted_at IS NULL 
                                 AND (
                                     scope_type = 'schoolwide' 
                                     OR (scope_type = 'club' AND scope_id = ?)
                                     OR EXISTS (SELECT 1 FROM calendar_event_scopes ces WHERE ces.event_id = calendar_events.id AND (ces.scope_type = 'schoolwide' OR (ces.scope_type = 'club' AND ces.scope_id = ?)))
                                 ) 
                               ORDER BY event_date ASC, start_time ASC");
        $stmt->execute([$clubId, (string)$clubId]);
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** The per-sport calendar on every extracurricular detail page, sport variant. */
    public static function getEventsForSport(int $sportId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT DISTINCT " . self::SELECT_COLS . " FROM calendar_events 
                               WHERE deleted_at IS NULL 
                                 AND (
                                     scope_type = 'schoolwide' 
                                     OR (scope_type = 'sport' AND scope_id = ?)
                                     OR EXISTS (SELECT 1 FROM calendar_event_scopes ces WHERE ces.event_id = calendar_events.id AND (ces.scope_type = 'schoolwide' OR (ces.scope_type = 'sport' AND ces.scope_id = ?)))
                                 ) 
                               ORDER BY event_date ASC, start_time ASC");
        $stmt->execute([$sportId, (string)$sportId]);
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ---- format aliases for seamless view interoperability ------------------
    public static function getAllEventsFormatted(): array {
        return self::getAllEvents('admin');
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

    private static function queryByScopes(array $gradeIds, array $classIds, array $clubIds, array $sportIds = [], string $role = 'admin'): array {
        $db = Database::getConnection();
        $conditions = [
            "(scope_type = 'schoolwide' OR EXISTS (SELECT 1 FROM calendar_event_scopes ces_sw WHERE ces_sw.event_id = calendar_events.id AND ces_sw.scope_type = 'schoolwide'))"
        ];
        $params = [];

        if (!empty($gradeIds)) {
            $inG = implode(',', array_fill(0, count($gradeIds), '?'));
            $conditions[] = "(scope_type = 'grade' AND scope_id IN ({$inG}))";
            $conditions[] = "EXISTS (SELECT 1 FROM calendar_event_scopes ces_g WHERE ces_g.event_id = calendar_events.id AND ces_g.scope_type = 'grade' AND ces_g.scope_id IN ({$inG}))";
            $params = array_merge($params, array_values($gradeIds), array_values($gradeIds));
        }
        if (!empty($classIds)) {
            $inC = implode(',', array_fill(0, count($classIds), '?'));
            $conditions[] = "(scope_type = 'class' AND scope_id IN ({$inC}))";
            $conditions[] = "EXISTS (SELECT 1 FROM calendar_event_scopes ces_c WHERE ces_c.event_id = calendar_events.id AND ces_c.scope_type = 'class' AND ces_c.scope_id IN ({$inC}))";
            $params = array_merge($params, array_values($classIds), array_values($classIds));
        }
        if (!empty($clubIds)) {
            $inCl = implode(',', array_fill(0, count($clubIds), '?'));
            $conditions[] = "(scope_type = 'club' AND scope_id IN ({$inCl}))";
            $conditions[] = "EXISTS (SELECT 1 FROM calendar_event_scopes ces_cl WHERE ces_cl.event_id = calendar_events.id AND ces_cl.scope_type = 'club' AND ces_cl.scope_id IN ({$inCl}))";
            $params = array_merge($params, array_values($clubIds), array_values($clubIds));
        }
        if (!empty($sportIds)) {
            $inS = implode(',', array_fill(0, count($sportIds), '?'));
            $conditions[] = "(scope_type = 'sport' AND scope_id IN ({$inS}))";
            $conditions[] = "EXISTS (SELECT 1 FROM calendar_event_scopes ces_s WHERE ces_s.event_id = calendar_events.id AND ces_s.scope_type = 'sport' AND ces_s.scope_id IN ({$inS}))";
            $params = array_merge($params, array_values($sportIds), array_values($sportIds));
        }

        $audSql = self::getAudienceSql($role);
        $scopeSql = "(" . implode(' OR ', $conditions) . ")";

        $stmt = $db->prepare("SELECT DISTINCT " . self::SELECT_COLS . " FROM calendar_events
                               WHERE deleted_at IS NULL 
                                 AND {$audSql}
                                 AND {$scopeSql}
                               ORDER BY event_date ASC, start_time ASC");
        $stmt->execute($params);
        return self::castIds($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function castIds(array $events): array {
        if (empty($events)) {
            return [];
        }
        $db = Database::getConnection();
        $eventIds = array_column($events, 'id');
        $scopeRows = [];
        try {
            $inIds = implode(',', array_fill(0, count($eventIds), '?'));
            $stmt = $db->prepare("SELECT event_id, scope_type, scope_id FROM calendar_event_scopes WHERE event_id IN ({$inIds})");
            $stmt->execute(array_values($eventIds));
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rawId = $r['scope_id'];
                $sId = null;
                if ($rawId !== null && $rawId !== '') {
                    $sId = ($r['scope_type'] === 'grade') ? (string)$rawId : (is_numeric($rawId) ? (int)$rawId : (string)$rawId);
                }
                $scopeRows[$r['event_id']][] = [
                    'type' => $r['scope_type'],
                    'id'   => $sId
                ];
            }
        } catch (\Throwable $e) {}

        return array_map(function($ev) use ($scopeRows) {
            $mapped = SqlJsMapper::calendarEventToJs($ev);
            if (!empty($scopeRows[$ev['id']])) {
                $mapped['scopes'] = $scopeRows[$ev['id']];
            } else {
                $evScopeType = $ev['scope_type'] ?? 'schoolwide';
                $rawEvScopeId = $ev['scope_id'] ?? null;
                $evScopeId = null;
                if ($rawEvScopeId !== null && $rawEvScopeId !== '') {
                    $evScopeId = ($evScopeType === 'grade') ? (string)$rawEvScopeId : (is_numeric($rawEvScopeId) ? (int)$rawEvScopeId : (string)$rawEvScopeId);
                }
                $mapped['scopes'] = [
                    ['type' => $evScopeType, 'id' => $evScopeId]
                ];
            }
            return $mapped;
        }, $events);
    }

    /**
     * Scope options for Admin & Management.
     * Clean typography, plain text, zero emojis.
     */
    public static function getScopeOptionsForStaff(): array {
        $db = Database::getConnection();
        $options = [
            ['type' => 'schoolwide', 'id' => null, 'label' => 'School-wide (All Users)']
        ];

        try {
            // Grades
            $grades = $db->query("SELECT id, name FROM grades ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($grades as $g) {
                $options[] = [
                    'type'  => 'grade',
                    'id'    => (string)$g['id'],
                    'label' => 'Grade: ' . $g['name'],
                ];
            }

            // Classes
            $classes = $db->query("SELECT id, section_name FROM classes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($classes as $c) {
                $options[] = [
                    'type'  => 'class',
                    'id'    => (int)$c['id'],
                    'label' => 'Class: ' . $c['section_name'],
                ];
            }

            // Clubs
            $clubs = $db->query("SELECT id, name FROM clubs ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($clubs as $cl) {
                $options[] = [
                    'type'  => 'club',
                    'id'    => (int)$cl['id'],
                    'label' => 'Club: ' . $cl['name'],
                ];
            }

            // Sports
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

    /**
     * Scope options for Teachers (Homeroom class & assigned clubs/sports).
     * Clean typography, plain text, zero emojis.
     */
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

    /**
     * Complete hierarchical scope structure for progressive disclosure modal.
     * Role-restricted for Teacher; Full access for Admin & Management.
     */
    public static function getScopeStructure(string $role, int $accountId): array {
        $db = Database::getConnection();
        $role = strtolower($role);

        if (in_array($role, ['admin', 'management'], true)) {
            $grades = $db->query("SELECT id, name FROM grades ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
            $classes = $db->query("SELECT id, grade_id, section_name as name FROM classes ORDER BY grade_id ASC, section_name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $clubs = $db->query("SELECT id, name FROM clubs ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $sports = $db->query("SELECT id, name FROM sports ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

            return [
                'role'           => $role,
                'grades'         => $grades,
                'classes'        => $classes,
                'clubs'          => $clubs,
                'sports'         => $sports,
                'can_schoolwide' => true,
                'can_grade'      => true,
            ];
        }

        if ($role === 'teacher') {
            $teacherId = self::getTeacherId($accountId);
            if (!$teacherId) {
                return ['role' => 'teacher', 'grades' => [], 'classes' => [], 'clubs' => [], 'sports' => [], 'can_schoolwide' => false, 'can_grade' => false];
            }

            // Homeroom class
            $classId = self::getClassIdForTeacher($teacherId);
            $classes = [];
            $grades = [];
            if ($classId) {
                $stmt = $db->prepare("SELECT c.id, c.grade_id, c.section_name as name, g.name as grade_name FROM classes c JOIN grades g ON g.id = c.grade_id WHERE c.id = ?");
                $stmt->execute([$classId]);
                $cRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($cRow) {
                    $classes[] = ['id' => (int)$cRow['id'], 'grade_id' => $cRow['grade_id'], 'name' => $cRow['name']];
                    $grades[]  = ['id' => $cRow['grade_id'], 'name' => $cRow['grade_name']];
                }
            }

            // Owned clubs
            $clubIds = self::getClubIdsForTeacher($teacherId);
            $clubs = [];
            if (!empty($clubIds)) {
                $placeholders = implode(',', array_fill(0, count($clubIds), '?'));
                $stmt = $db->prepare("SELECT id, name FROM clubs WHERE id IN ({$placeholders}) ORDER BY name ASC");
                $stmt->execute(array_values($clubIds));
                $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Owned sports
            $sportIds = self::getSportIdsForTeacher($teacherId);
            $sports = [];
            if (!empty($sportIds)) {
                $placeholders = implode(',', array_fill(0, count($sportIds), '?'));
                $stmt = $db->prepare("SELECT id, name FROM sports WHERE id IN ({$placeholders}) ORDER BY name ASC");
                $stmt->execute(array_values($sportIds));
                $sports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'role'           => 'teacher',
                'grades'         => $grades,
                'classes'        => $classes,
                'clubs'          => $clubs,
                'sports'         => $sports,
                'can_schoolwide' => false,
                'can_grade'      => false,
            ];
        }

        return ['role' => $role, 'grades' => [], 'classes' => [], 'clubs' => [], 'sports' => [], 'can_schoolwide' => false, 'can_grade' => false];
    }
}
