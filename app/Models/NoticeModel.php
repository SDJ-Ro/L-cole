<?php
/**
 * =========================================================================
 * L'ÉCOLE — NOTICE READ MODEL
 * =========================================================================
 * Pure read engine querying the MySQL `notices` table for active announcements.
 * Handles role-based audience filtering, date formatting, and search filters.
 * Zero state mutations or writes.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';

class NoticeModel {

    /**
     * Predefined category list.
     */
    public static function getCategories(): array {
        return ['Academic', 'Extracurricular', 'General', 'Administrative'];
    }

    /**
     * Predefined audience list.
     */
    public static function getAudiences(): array {
        return ['All', 'Students', 'Parents', 'Teachers', 'Management'];
    }

    /**
     * Count currently pinned notices.
     */
    public static function countPinned(): int {
        try {
            $db = Database::getConnection();
            $stmt = $db->query(
                "SELECT COUNT(*) FROM notices 
                 WHERE pinned = 1 
                   AND deleted_at IS NULL 
                   AND (publish_at IS NULL OR publish_at <= NOW()) 
                   AND (expires_at IS NULL OR expires_at > NOW())"
            );
            return (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log("[NoticeModel Error] Failed counting pinned notices: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lookup a single notice by ID.
     */
    public static function findById(int $id): ?array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM notices WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? self::formatRow($row) : null;
        } catch (\Throwable $e) {
            error_log("[NoticeModel Error] Failed finding notice #{$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve all active notices (Admin & Management overview).
     */
    public static function getAll(?string $category = null, ?string $audience = null, ?string $search = null): array {
        try {
            $db = Database::getConnection();
            $sql = "SELECT * FROM notices 
                    WHERE deleted_at IS NULL 
                      AND (publish_at IS NULL OR publish_at <= NOW()) 
                      AND (expires_at IS NULL OR expires_at > NOW())";
            $params = [];

            if ($category && $category !== 'All') {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            if ($audience && $audience !== 'All') {
                $sql .= " AND (JSON_CONTAINS(audience, '\"All\"') OR JSON_CONTAINS(audience, ?))";
                $params[] = json_encode($audience);
            }

            if ($search && trim($search) !== '') {
                $sql .= " AND (title LIKE ? OR body LIKE ? OR author_name LIKE ?)";
                $q = '%' . trim($search) . '%';
                $params[] = $q;
                $params[] = $q;
                $params[] = $q;
            }

            $sql .= " ORDER BY pinned DESC, created_at DESC, id DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            return array_map([self::class, 'formatRow'], $rows);
        } catch (\Throwable $e) {
            error_log("[NoticeModel Error] Failed reading notices: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve notices visible to a specific authenticated role.
     */
    public static function getForRole(string $role, ?string $category = null, ?string $search = null): array {
        $role = strtolower(trim($role));

        // Admin and Management see all active announcements
        if ($role === 'admin' || $role === 'management') {
            return self::getAll($category, null, $search);
        }

        $targetAudience = match($role) {
            'student' => 'Students',
            'parent'  => 'Parents',
            'teacher' => 'Teachers',
            default   => 'All'
        };

        return self::getAll($category, $targetAudience, $search);
    }

    /**
     * Format raw MySQL notice row for frontend templates.
     */
    public static function formatRow(array $r): array {
        $rawAudience = json_decode($r['audience'] ?? '[]', true);
        $audienceList = is_array($rawAudience) ? $rawAudience : ['All'];

        // Human-friendly date formatting
        $createdTime = !empty($r['created_at']) ? strtotime($r['created_at']) : time();
        $todayStart = strtotime('today midnight');
        $yesterdayStart = strtotime('yesterday midnight');

        if ($createdTime >= $todayStart) {
            $formattedDate = 'TODAY';
        } elseif ($createdTime >= $yesterdayStart) {
            $formattedDate = 'YESTERDAY';
        } else {
            $formattedDate = strtoupper(date('d M Y', $createdTime));
        }

        return [
            'id'                => (int)$r['id'],
            'title'             => htmlspecialchars($r['title'] ?? ''),
            'category'          => htmlspecialchars($r['category'] ?? 'General'),
            'audience'          => $audienceList,
            'body'              => htmlspecialchars($r['body'] ?? ''),
            'author'            => htmlspecialchars($r['author_name'] ?? 'Admin Office'),
            'author_name'       => htmlspecialchars($r['author_name'] ?? 'Admin Office'),
            'author_role'       => strtolower($r['author_role'] ?? 'admin'),
            'author_account_id' => !empty($r['author_account_id']) ? (int)$r['author_account_id'] : null,
            'attachment_name'   => !empty($r['attachment_name']) ? htmlspecialchars($r['attachment_name']) : null,
            'attachment_path'   => !empty($r['attachment_path']) ? htmlspecialchars($r['attachment_path']) : null,
            'target_class'      => $r['target_class_section'] ?? null,
            'target_club'       => $r['target_club_id'] ?? null,
            'publish_at'        => $r['publish_at'] ?? null,
            'expires_at'        => $r['expires_at'] ?? null,
            'pinned'            => (bool)$r['pinned'],
            'created_at'        => $r['created_at'],
            'date'              => $formattedDate,
        ];
    }

    /**
     * Retrieve all active extracurricular activities for dropdown selection.
     */
    public static function getExtracurricularActivities(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT activity_id, activity_name, category FROM extracurricular_activities ORDER BY activity_name ASC");
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log("[NoticeModel Error] Failed fetching activities: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve all classes and grades for academic targeting.
     */
    public static function getAcademicClasses(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT c.section_name FROM classes c JOIN grades g ON g.id = c.grade_id ORDER BY g.sort_order ASC, LENGTH(c.section_name) ASC, c.section_name ASC");
            $rows = $stmt->fetchAll();
            $sections = array_column($rows, 'section_name');

            $stmtGrades = $db->query("SELECT name FROM grades ORDER BY sort_order ASC");
            $grades = array_column($stmtGrades->fetchAll(), 'name');

            return [
                'grades'   => $grades,
                'sections' => $sections
            ];
        } catch (\Throwable $e) {
            error_log("[NoticeModel Error] Failed fetching classes: " . $e->getMessage());
            return ['grades' => [], 'sections' => []];
        }
    }

    /**
     * Retrieve notices specifically linked to an extracurricular club.
     */
    public static function getClubNotices(string $clubId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM notices 
                 WHERE (target_club_id = ? OR target_club_id = ?) 
                   AND deleted_at IS NULL 
                   AND (publish_at IS NULL OR publish_at <= NOW()) 
                   AND (expires_at IS NULL OR expires_at > NOW())
                 ORDER BY pinned DESC, created_at DESC"
            );
            $stmt->execute([$clubId, strtolower($clubId)]);
            $rows = $stmt->fetchAll();
            return array_map([self::class, 'formatRow'], $rows);
        } catch (\Throwable $e) {
            error_log("[NoticeModel Error] Failed fetching club notices: " . $e->getMessage());
            return [];
        }
    }
}
