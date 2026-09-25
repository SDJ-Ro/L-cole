<?php
/**
 * =========================================================================
 * L'ÉCOLE — NOTICE MODEL (DATABASE-BACKED CRUD)
 * =========================================================================
 * Central backend provider for notice announcements across all 5 roles.
 * Provides full PDO MySQL CRUD with prepared statements and audit support,
 * with graceful file-storage fallback if the database server is offline.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';

class NoticeModel {

    private static ?PDO $db = null;
    private static bool $dbChecked = false;
    private static bool $useDatabase = false;
    private static string $fallbackFile = __DIR__ . '/../../storage/notices.json';

    /**
     * Initial seed announcements for first-time database migration or fallback
     */
    protected static array $defaultNotices = [
        [
            'id'             => 1,
            'title'          => 'Term 2 Examination Schedule — June 2026',
            'category'       => 'Academic',
            'audience'       => ['All'],
            'body'           => 'Term 2 examinations run from 17–26 June 2026. Students should follow their grade and class section timetable for subject sessions, rooms, and reporting times. The make-up examination session is scheduled for 26 June for approved absences.',
            'author'         => 'Academic Office',
            'date'           => '10 JUN 2026',
            'pinned'         => true,
            'attachment_url' => null,
            'created_at'     => '2026-06-10 08:30:00'
        ],
        [
            'id'             => 2,
            'title'          => 'Sports Day Rehearsal Schedule',
            'category'       => 'Extracurricular',
            'audience'       => ['Students', 'Teachers'],
            'body'           => 'Final rehearsal for the annual sports meet will take place on the main grounds this Friday at 14:00. Attendance is mandatory for all participating athletes and event coordinators.',
            'author'         => 'Student Life Office',
            'date'           => '14 JUN 2026',
            'pinned'         => false,
            'attachment_url' => null,
            'created_at'     => '2026-06-14 10:15:00'
        ],
        [
            'id'             => 3,
            'title'          => 'Library Renovation Notice',
            'category'       => 'General',
            'audience'       => ['All'],
            'body'           => 'The main library will be closed for digital catalog upgrades starting next Monday. A temporary reading room and borrowing desk has been set up in Hall B for student and faculty use.',
            'author'         => 'Admin Office',
            'date'           => '20 MAY 2026',
            'pinned'         => false,
            'attachment_url' => null,
            'created_at'     => '2026-05-20 09:00:00'
        ],
        [
            'id'             => 4,
            'title'          => 'Parent-Teacher Conference: Grade 10 & 11',
            'category'       => 'Academic',
            'audience'       => ['Parents', 'Teachers'],
            'body'           => 'The termly parent-teacher conference for Grade 10 & 11 will be held virtually this Saturday. One-on-one booking links have been dispatched to registered email addresses.',
            'author'         => 'Mrs. Perera',
            'date'           => '18 MAY 2026',
            'pinned'         => false,
            'attachment_url' => null,
            'created_at'     => '2026-05-18 14:00:00'
        ],
        [
            'id'             => 5,
            'title'          => 'Annual Staff Leadership & Curriculum Review',
            'category'       => 'Administrative',
            'audience'       => ['Teachers', 'Management'],
            'body'           => 'Departmental curriculum reviews and teaching strategy workshops will convene in the Executive Boardroom on Friday at 16:00. All faculty heads are expected to attend with term assessments.',
            'author'         => 'Dr. Vance',
            'date'           => '12 MAY 2026',
            'pinned'         => false,
            'attachment_url' => null,
            'created_at'     => '2026-05-12 16:30:00'
        ],
    ];

    /**
     * Initializes PDO connection or confirms database readiness
     */
    private static function init(): void {
        if (self::$dbChecked) {
            return;
        }
        self::$dbChecked = true;

        try {
            $pdo = Database::getConnection();
            if ($pdo instanceof PDO) {
                self::$db = $pdo;
                self::ensureTableExists();
                self::$useDatabase = true;
            }
        } catch (\Throwable $e) {
            self::$useDatabase = false;
        }

        // Initialize fallback storage if file doesn't exist
        if (!self::$useDatabase && !file_exists(self::$fallbackFile)) {
            $dir = dirname(self::$fallbackFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @file_put_contents(self::$fallbackFile, json_encode(self::$defaultNotices, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Ensures notices table exists in MySQL database
     */
    private static function ensureTableExists(): void {
        if (!self::$db) return;

        try {
            self::$db->exec("
                CREATE TABLE IF NOT EXISTS notices (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    account_id INT NULL,
                    title VARCHAR(200) NOT NULL,
                    category ENUM('Academic', 'Extracurricular', 'General', 'Administrative') NOT NULL DEFAULT 'General',
                    audience VARCHAR(150) NOT NULL DEFAULT 'All',
                    body TEXT NOT NULL,
                    author_name VARCHAR(150) NOT NULL DEFAULT 'Admin Office',
                    attachment_url VARCHAR(255) NULL,
                    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_notice_cat (category),
                    INDEX idx_notice_pin (is_pinned),
                    INDEX idx_notice_time (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed if empty
            $count = (int)self::$db->query("SELECT COUNT(*) FROM notices")->fetchColumn();
            if ($count === 0) {
                $stmt = self::$db->prepare("
                    INSERT INTO notices (id, title, category, audience, body, author_name, is_pinned, attachment_url, created_at)
                    VALUES (:id, :title, :category, :audience, :body, :author_name, :is_pinned, :attachment_url, :created_at)
                ");
                foreach (self::$defaultNotices as $n) {
                    $stmt->execute([
                        ':id'             => $n['id'],
                        ':title'          => $n['title'],
                        ':category'       => $n['category'],
                        ':audience'       => implode(',', (array)$n['audience']),
                        ':body'           => $n['body'],
                        ':author_name'    => $n['author'],
                        ':is_pinned'      => $n['pinned'] ? 1 : 0,
                        ':attachment_url' => $n['attachment_url'],
                        ':created_at'     => $n['created_at'] ?? date('Y-m-d H:i:s'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            error_log("[NoticeModel::ensureTableExists] " . $e->getMessage());
        }
    }

    /**
     * Format a raw database/fallback row into the standardized notice array
     */
    private static function formatRow(array $row): array {
        $rawAudience = $row['audience'] ?? 'All';
        $audienceArray = is_array($rawAudience) 
            ? $rawAudience 
            : array_map('trim', explode(',', (string)$rawAudience));

        if (empty($audienceArray)) {
            $audienceArray = ['All'];
        }

        $createdAt = $row['created_at'] ?? 'now';
        $formattedDate = date('d M Y', strtotime($createdAt));

        return [
            'id'             => (int)($row['id'] ?? 0),
            'title'          => (string)($row['title'] ?? ''),
            'category'       => (string)($row['category'] ?? 'General'),
            'audience'       => $audienceArray,
            'body'           => (string)($row['body'] ?? ''),
            'author'         => (string)($row['author_name'] ?? $row['author'] ?? 'Admin Office'),
            'date'           => strtoupper($formattedDate),
            'pinned'         => !empty($row['is_pinned']) || !empty($row['pinned']),
            'attachment_url' => $row['attachment_url'] ?? null,
            'created_at'     => $createdAt,
        ];
    }

    /**
     * Retrieve all notices (ordered: pinned first, then newest first)
     */
    public static function getAll(): array {
        self::init();

        if (self::$useDatabase && self::$db) {
            try {
                $stmt = self::$db->query("
                    SELECT * FROM notices 
                    ORDER BY is_pinned DESC, created_at DESC, id DESC
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return array_map([self::class, 'formatRow'], $rows);
            } catch (\Throwable $e) {
                error_log("[NoticeModel::getAll SQL Error] " . $e->getMessage());
            }
        }

        // Fallback file storage
        return self::loadFallbackNotices();
    }

    /**
     * Retrieve notices filtered by recipient role
     */
    public static function getForRole(string $role): array {
        $role = strtolower(trim($role));
        $all = self::getAll();

        if ($role === 'admin' || $role === 'management') {
            return $all;
        }

        $targetAudience = match($role) {
            'student' => 'students',
            'parent'  => 'parents',
            'teacher' => 'teachers',
            default   => 'all'
        };

        $filtered = array_filter($all, function ($n) use ($targetAudience) {
            $audiences = array_map('strtolower', (array)($n['audience'] ?? []));
            return in_array('all', $audiences, true) 
                || in_array('all users', $audiences, true) 
                || in_array($targetAudience, $audiences, true);
        });

        return array_values($filtered);
    }

    /**
     * Get single notice by ID
     */
    public static function getById(int $id): ?array {
        self::init();

        if (self::$useDatabase && self::$db) {
            try {
                $stmt = self::$db->prepare("SELECT * FROM notices WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? self::formatRow($row) : null;
            } catch (\Throwable $e) {
                error_log("[NoticeModel::getById SQL Error] " . $e->getMessage());
            }
        }

        $all = self::loadFallbackNotices();
        foreach ($all as $n) {
            if ((int)$n['id'] === $id) {
                return $n;
            }
        }
        return null;
    }

    /**
     * Create a new notice announcement
     */
    public static function create(array $data): ?array {
        self::init();

        $title          = trim($data['title'] ?? '');
        $category       = trim($data['category'] ?? 'General');
        $body           = trim($data['body'] ?? '');
        $authorName     = trim($data['author_name'] ?? $data['author'] ?? 'Admin Office');
        $accountId      = !empty($data['account_id']) ? (int)$data['account_id'] : null;
        $isPinned       = !empty($data['pinned']) || !empty($data['is_pinned']) ? 1 : 0;
        $attachmentUrl  = $data['attachment_url'] ?? null;

        $rawAudience    = $data['audience'] ?? ['All'];
        if (is_array($rawAudience)) {
            $audienceStr = implode(',', array_filter(array_map('trim', $rawAudience)));
        } else {
            $audienceStr = trim((string)$rawAudience);
        }
        if (empty($audienceStr)) {
            $audienceStr = 'All';
        }

        if (empty($title) || empty($body)) {
            return null;
        }

        if (self::$useDatabase && self::$db) {
            try {
                $stmt = self::$db->prepare("
                    INSERT INTO notices (account_id, title, category, audience, body, author_name, attachment_url, is_pinned, created_at)
                    VALUES (:account_id, :title, :category, :audience, :body, :author_name, :attachment_url, :is_pinned, NOW())
                ");
                $stmt->execute([
                    ':account_id'     => $accountId,
                    ':title'          => $title,
                    ':category'       => $category,
                    ':audience'       => $audienceStr,
                    ':body'           => $body,
                    ':author_name'    => $authorName,
                    ':attachment_url' => $attachmentUrl,
                    ':is_pinned'      => $isPinned,
                ]);

                $newId = (int)self::$db->lastInsertId();
                return self::getById($newId);
            } catch (\Throwable $e) {
                error_log("[NoticeModel::create SQL Error] " . $e->getMessage());
            }
        }

        // Fallback file storage
        $all = self::loadFallbackNotices();
        $maxId = 0;
        foreach ($all as $n) {
            if ((int)$n['id'] > $maxId) {
                $maxId = (int)$n['id'];
            }
        }
        $newId = $maxId + 1;

        $newNotice = [
            'id'             => $newId,
            'title'          => $title,
            'category'       => $category,
            'audience'       => explode(',', $audienceStr),
            'body'           => $body,
            'author'         => $authorName,
            'date'           => strtoupper(date('d M Y')),
            'pinned'         => (bool)$isPinned,
            'attachment_url' => $attachmentUrl,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        array_unshift($all, $newNotice);
        self::saveFallbackNotices($all);
        return $newNotice;
    }

    /**
     * Update an existing notice
     */
    public static function update(int $id, array $data): bool {
        self::init();

        $title         = trim($data['title'] ?? '');
        $category      = trim($data['category'] ?? 'General');
        $body          = trim($data['body'] ?? '');
        $isPinned      = !empty($data['pinned']) || !empty($data['is_pinned']) ? 1 : 0;
        $attachmentUrl = $data['attachment_url'] ?? null;

        $rawAudience   = $data['audience'] ?? ['All'];
        if (is_array($rawAudience)) {
            $audienceStr = implode(',', array_filter(array_map('trim', $rawAudience)));
        } else {
            $audienceStr = trim((string)$rawAudience);
        }
        if (empty($audienceStr)) {
            $audienceStr = 'All';
        }

        if (empty($title) || empty($body)) {
            return false;
        }

        if (self::$useDatabase && self::$db) {
            try {
                $sql = "UPDATE notices SET 
                            title = :title, 
                            category = :category, 
                            audience = :audience, 
                            body = :body, 
                            is_pinned = :is_pinned";
                $params = [
                    ':id'        => $id,
                    ':title'     => $title,
                    ':category'  => $category,
                    ':audience'  => $audienceStr,
                    ':body'      => $body,
                    ':is_pinned' => $isPinned,
                ];

                if ($attachmentUrl !== null) {
                    $sql .= ", attachment_url = :attachment_url";
                    $params[':attachment_url'] = $attachmentUrl;
                }
                $sql .= " WHERE id = :id";

                $stmt = self::$db->prepare($sql);
                return $stmt->execute($params);
            } catch (\Throwable $e) {
                error_log("[NoticeModel::update SQL Error] " . $e->getMessage());
            }
        }

        // Fallback file storage
        $all = self::loadFallbackNotices();
        $updated = false;
        foreach ($all as &$n) {
            if ((int)$n['id'] === $id) {
                $n['title']    = $title;
                $n['category'] = $category;
                $n['audience'] = explode(',', $audienceStr);
                $n['body']     = $body;
                $n['pinned']   = (bool)$isPinned;
                if ($attachmentUrl !== null) {
                    $n['attachment_url'] = $attachmentUrl;
                }
                $updated = true;
                break;
            }
        }
        if ($updated) {
            self::saveFallbackNotices($all);
        }
        return $updated;
    }

    /**
     * Delete a notice by ID
     */
    public static function delete(int $id): bool {
        self::init();

        if (self::$useDatabase && self::$db) {
            try {
                $stmt = self::$db->prepare("DELETE FROM notices WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            } catch (\Throwable $e) {
                error_log("[NoticeModel::delete SQL Error] " . $e->getMessage());
            }
        }

        // Fallback file storage
        $all = self::loadFallbackNotices();
        $filtered = array_filter($all, fn($n) => (int)$n['id'] !== $id);
        if (count($filtered) !== count($all)) {
            self::saveFallbackNotices(array_values($filtered));
            return true;
        }
        return false;
    }

    /**
     * Toggle the pinned status of a notice
     */
    public static function togglePin(int $id): bool {
        self::init();

        if (self::$useDatabase && self::$db) {
            try {
                $stmt = self::$db->prepare("UPDATE notices SET is_pinned = NOT is_pinned WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $check = self::$db->prepare("SELECT is_pinned FROM notices WHERE id = :id");
                $check->execute([':id' => $id]);
                return (bool)$check->fetchColumn();
            } catch (\Throwable $e) {
                error_log("[NoticeModel::togglePin SQL Error] " . $e->getMessage());
            }
        }

        // Fallback file storage
        $all = self::loadFallbackNotices();
        $newPinned = false;
        foreach ($all as &$n) {
            if ((int)$n['id'] === $id) {
                $n['pinned'] = !($n['pinned'] ?? false);
                $newPinned = $n['pinned'];
                break;
            }
        }
        self::saveFallbackNotices($all);
        return $newPinned;
    }

    /**
     * Categories allowed in the system
     */
    public static function getCategories(): array {
        return ['Academic', 'Extracurricular', 'General', 'Administrative'];
    }

    /**
     * Audiences allowed in the system
     */
    public static function getAudiences(): array {
        return ['All', 'Students', 'Parents', 'Teachers', 'Management'];
    }

    // -------------------------------------------------------------------------
    // Fallback file helpers
    // -------------------------------------------------------------------------
    private static function loadFallbackNotices(): array {
        if (!file_exists(self::$fallbackFile)) {
            return self::$defaultNotices;
        }
        $json = @file_get_contents(self::$fallbackFile);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return self::$defaultNotices;
        }

        usort($data, function ($a, $b) {
            $pinA = !empty($a['pinned']) || !empty($a['is_pinned']) ? 1 : 0;
            $pinB = !empty($b['pinned']) || !empty($b['is_pinned']) ? 1 : 0;
            if ($pinA !== $pinB) {
                return $pinB <=> $pinA;
            }
            return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
        });

        return array_map([self::class, 'formatRow'], $data);
    }

    private static function saveFallbackNotices(array $notices): void {
        $dir = dirname(self::$fallbackFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents(self::$fallbackFile, json_encode($notices, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
