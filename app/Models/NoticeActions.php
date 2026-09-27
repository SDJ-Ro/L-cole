<?php
/**
 * =========================================================================
 * L'ÉCOLE — NOTICE ACTIONS (MUTATIONS & BUSINESS RULES)
 * =========================================================================
 * Dedicated write engine for creating, updating, pinning, and soft-deleting
 * announcements with strict authority hierarchy and audit logging:
 *   - Admin: Full executive control over all notices.
 *   - Management: Can edit/delete/pin Management and Teacher notices.
 *                 Cannot unpin, edit, or delete Admin notices.
 *                 Cannot post announcements targeting "Management".
 *   - Pinned Cap: Enforces max 5 pinned notices.
 *   - Soft Delete: Sets `deleted_at = NOW()`.
 *
 * Adheres strictly to the 6-Step Mutation Discipline:
 *   1. Input sanitization & validation
 *   2. Business-rule verification & authority check
 *   3. Fail-fast early exit
 *   4. Transaction boundary ($db->beginTransaction)
 *   5. Integrated Audit Logging (AuditModel::record)
 *   6. Standardized JSON response
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/NoticeModel.php';
require_once __DIR__ . '/AuditModel.php';

class NoticeActions {

    const MAX_PINNED_NOTICES = 5;

    /**
     * 1. Validate notice form input.
     */
    public static function validate(array $input, string $role): array {
        $title = trim($input['title'] ?? '');
        $body  = trim($input['body'] ?? '');
        $category = trim($input['category'] ?? 'General');

        // Title validation
        if (strlen($title) < 3 || strlen($title) > 200) {
            throw new InvalidArgumentException("Notice title must be between 3 and 200 characters.");
        }

        // Body validation
        if (strlen($body) < 10 || strlen($body) > 5000) {
            throw new InvalidArgumentException("Notice body must be between 10 and 5,000 characters.");
        }

        // Category validation
        $validCategories = NoticeModel::getCategories();
        if (!in_array($category, $validCategories, true)) {
            $category = 'General';
        }

        // Audience validation
        $rawAudience = $input['audience'] ?? ['All'];
        if (!is_array($rawAudience)) {
            $rawAudience = [$rawAudience];
        }
        $validAudiences = NoticeModel::getAudiences();
        $filteredAudience = [];
        foreach ($rawAudience as $aud) {
            $audClean = trim((string)$aud);
            if (in_array($audClean, $validAudiences, true)) {
                $filteredAudience[] = $audClean;
            }
        }

        if (empty($filteredAudience)) {
            $filteredAudience = ['All'];
        }

        // Authority Rule: Management cannot target "Management"
        if ($role === 'management' && in_array('Management', $filteredAudience, true)) {
            throw new InvalidArgumentException("Management cannot post announcements to Management. Please select Teachers, Parents, or Students.");
        }

        if (in_array('All', $filteredAudience, true)) {
            $filteredAudience = ['All'];
        }

        $pinned = !empty($input['pinned']) && ($input['pinned'] === '1' || $input['pinned'] === 1 || $input['pinned'] === true) ? 1 : 0;

        $db = Database::getConnection();
        $targetClub = null;
        $targetClass = null;

        // Scope Shape & Cross-Entity Validation: ensure category and targets are mutually coherent
        if ($category === 'Extracurricular') {
            if (!empty($input['target_club_id'])) {
                $c = trim((string)$input['target_club_id']);
                if ($c !== 'All' && $c !== '') {
                    // Verify club exists in database
                    $stmtClub = $db->prepare("SELECT 1 FROM extracurricular_activities WHERE activity_id = ? LIMIT 1");
                    $stmtClub->execute([$c]);
                    if (!$stmtClub->fetchColumn()) {
                        throw new InvalidArgumentException("The selected extracurricular club does not exist.");
                    }
                    $targetClub = $c;
                }
            }
            $targetClass = null; // Clean coherence: no academic class on club notices
        } elseif ($category === 'Academic') {
            if (!empty($input['target_class_section'])) {
                $cl = trim((string)$input['target_class_section']);
                if ($cl !== 'All' && $cl !== '') {
                    // Verify section or grade exists in database
                    $stmtClass = $db->prepare("SELECT 1 FROM classes WHERE section_name = ? LIMIT 1");
                    $stmtClass->execute([$cl]);
                    if (!$stmtClass->fetchColumn()) {
                        $stmtGrade = $db->prepare("SELECT 1 FROM grades WHERE name = ? LIMIT 1");
                        $stmtGrade->execute([$cl]);
                        if (!$stmtGrade->fetchColumn()) {
                            throw new InvalidArgumentException("The selected academic grade or class section does not exist.");
                        }
                    }
                    $targetClass = $cl;
                }
            }
            $targetClub = null; // Clean coherence: no club on academic notices
        } else {
            // General & Administrative notices are strictly School-Wide
            $targetClub = null;
            $targetClass = null;
        }

        // Time Slot & Expiration Lifecycle validation
        $publishAt = !empty($input['publish_at']) ? trim((string)$input['publish_at']) : null;
        if ($publishAt) {
            $tsPub = strtotime($publishAt);
            if ($tsPub === false) {
                throw new InvalidArgumentException("Invalid publish start date format.");
            }
            $publishAt = date('Y-m-d H:i:s', $tsPub);
        } else {
            $publishAt = date('Y-m-d H:i:s');
        }

        $expiresAt = !empty($input['expires_at']) ? trim((string)$input['expires_at']) : null;
        if ($expiresAt) {
            $tsExp = strtotime($expiresAt);
            if ($tsExp === false) {
                throw new InvalidArgumentException("Invalid expiration date format.");
            }
            if ($tsExp <= strtotime($publishAt)) {
                throw new InvalidArgumentException("Notice expiration date must be later than the publish date.");
            }
            $expiresAt = date('Y-m-d H:i:s', $tsExp);
        }

        return [
            'title'                => $title,
            'body'                 => $body,
            'category'             => $category,
            'audience'             => array_values(array_unique($filteredAudience)),
            'target_club_id'       => $targetClub,
            'target_class_section' => $targetClass,
            'publish_at'           => $publishAt,
            'expires_at'           => $expiresAt,
            'pinned'               => $pinned,
        ];
    }

    /**
     * 2. Handle file attachment upload with MIME verification & segregated paths.
     */
    public static function handleUpload(?array $file, string $role): ?array {
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Max 5 MB check
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException("File attachment exceeds the maximum allowed size of 5 MB.");
        }

        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExts, true)) {
            throw new InvalidArgumentException("Invalid file type. Allowed formats: PDF, DOCX, JPG, PNG.");
        }

        // Deep binary MIME type verification via finfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = [
                'pdf'  => ['application/pdf'],
                'doc'  => ['application/msword', 'application/vnd.ms-office'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
                'jpg'  => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png'  => ['image/png', 'image/x-png'],
            ];

            $validMimeForExt = $allowedMimes[$ext] ?? [];
            if (!in_array($mime, $validMimeForExt, true)) {
                throw new InvalidArgumentException("File signature does not match its extension or is not allowed ({$mime}).");
            }
        }

        // Role-based directory: public/uploads/notices/{role}/{YYYY}/{MM}/
        $roleFolder = match(strtolower($role)) {
            'management' => 'management',
            'teacher'    => 'teacher',
            default      => 'admin'
        };

        $year = date('Y');
        $month = date('m');
        $relativeDir = "/uploads/notices/{$roleFolder}/{$year}/{$month}";
        $targetDir = dirname(__DIR__, 2) . '/public' . $relativeDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Cryptographically random, collision-proof filename
        $safeName = 'att_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $targetPath = $targetDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException("Failed to save uploaded attachment to disk.");
        }

        return [
            'name' => $origName,
            'path' => $relativeDir . '/' . $safeName
        ];
    }

    /**
     * 3. Publish a new notice.
     */
    public static function createNotice(array $input, ?array $file, int $actorId, string $actorName, string $actorRole): array {
        $validated = self::validate($input, $actorRole);
        $db = Database::getConnection();

        // Pin Cap Check
        if ($validated['pinned'] === 1) {
            $pinnedCount = NoticeModel::countPinned();
            if ($pinnedCount >= self::MAX_PINNED_NOTICES) {
                throw new InvalidArgumentException("Maximum of " . self::MAX_PINNED_NOTICES . " notices can be pinned at a time. Please unpin an older notice first.");
            }
        }

        // Handle attachment
        $attachment = self::handleUpload($file, $actorRole);

        // Run proactive maintenance / purge of notices past 4 weeks
        self::purgeExpiredNotices();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "INSERT INTO notices (
                    title, category, audience, body, author_name, author_role, author_account_id,
                    attachment_name, attachment_path, target_class_section, target_club_id,
                    publish_at, expires_at, pinned, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            $stmt->execute([
                $validated['title'],
                $validated['category'],
                json_encode($validated['audience']),
                $validated['body'],
                $actorName,
                $actorRole,
                $actorId,
                $attachment['name'] ?? null,
                $attachment['path'] ?? null,
                $validated['target_class_section'],
                $validated['target_club_id'],
                $validated['publish_at'],
                $validated['expires_at'],
                $validated['pinned']
            ]);

            $noticeId = (int)$db->lastInsertId();

            // Audit Log Event
            $auditAction = ($actorRole === 'management') ? 'EXECUTIVE_NOTICE_POSTED' : 'NOTICE_PUBLISHED';
            AuditModel::record(
                $actorId,
                $actorName,
                $auditAction,
                "Published notice #{$noticeId}: '{$validated['title']}' targeting " . implode(', ', $validated['audience']) . ($validated['pinned'] ? " (Pinned)" : "")
            );

            $db->commit();

            return [
                'success' => true,
                'message' => 'Notice published successfully.',
                'notice'  => NoticeModel::findById($noticeId)
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 4. Update an existing notice.
     */
    public static function updateNotice(int $id, array $input, ?array $file, int $actorId, string $actorRole): array {
        $db = Database::getConnection();

        $stmtFind = $db->prepare("SELECT * FROM notices WHERE id = ? AND deleted_at IS NULL");
        $stmtFind->execute([$id]);
        $existing = $stmtFind->fetch();

        if (!$existing) {
            throw new RuntimeException("Notice not found or has already been deleted.", 404);
        }

        // Authority Rule Check with Denial Auditing
        if ($existing['author_role'] === 'admin' && $actorRole !== 'admin') {
            AuditModel::record(
                $actorId,
                $existing['author_name'],
                'SECURITY_NOTICE_EDIT_DENIED',
                "Unauthorized attempt by {$actorRole} (#{$actorId}) to edit Admin notice #{$id} ('{$existing['title']}'). Action blocked."
            );
            throw new RuntimeException("Only an Administrator has permission to edit an Admin announcement.", 403);
        }

        $validated = self::validate($input, $actorRole);

        // Pin Cap Check if toggling to pinned
        if (!$existing['pinned'] && $validated['pinned'] === 1) {
            $pinnedCount = NoticeModel::countPinned();
            if ($pinnedCount >= self::MAX_PINNED_NOTICES) {
                throw new InvalidArgumentException("Maximum of " . self::MAX_PINNED_NOTICES . " notices can be pinned at a time. Please unpin an older notice first.");
            }
        }

        // Handle attachment replacement
        $attachment = self::handleUpload($file, $actorRole);
        $attName = $attachment ? $attachment['name'] : $existing['attachment_name'];
        $attPath = $attachment ? $attachment['path'] : $existing['attachment_path'];

        $db->beginTransaction();
        try {
            $stmtUpdate = $db->prepare(
                "UPDATE notices SET
                    title = ?, category = ?, audience = ?, body = ?,
                    attachment_name = ?, attachment_path = ?,
                    target_class_section = ?, target_club_id = ?,
                    publish_at = ?, expires_at = ?,
                    pinned = ?, updated_at = NOW()
                 WHERE id = ?"
            );

            $stmtUpdate->execute([
                $validated['title'],
                $validated['category'],
                json_encode($validated['audience']),
                $validated['body'],
                $attName,
                $attPath,
                $validated['target_class_section'],
                $validated['target_club_id'],
                $validated['publish_at'],
                $validated['expires_at'],
                $validated['pinned'],
                $id
            ]);

            AuditModel::record(
                $actorId,
                $existing['author_name'],
                'NOTICE_UPDATED',
                "Updated notice #{$id}: '{$validated['title']}'."
            );

            $db->commit();

            return [
                'success' => true,
                'message' => 'Notice updated successfully.',
                'notice'  => NoticeModel::findById($id)
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 5. Toggle pin status of a notice.
     */
    public static function togglePin(int $id, int $actorId, string $actorRole): array {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM notices WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $notice = $stmt->fetch();

        if (!$notice) {
            throw new RuntimeException("Notice not found or has been deleted.", 404);
        }

        // Authority Rule Check: Management cannot unpin an Admin notice (with Denial Auditing)
        if ($notice['author_role'] === 'admin' && $notice['pinned'] == 1 && $actorRole !== 'admin') {
            AuditModel::record(
                $actorId,
                $notice['author_name'],
                'SECURITY_NOTICE_UNPIN_DENIED',
                "Unauthorized attempt by {$actorRole} (#{$actorId}) to unpin Admin notice #{$id} ('{$notice['title']}'). Action blocked."
            );
            throw new RuntimeException("Only an Administrator can unpin an Admin announcement.", 403);
        }

        $newPinState = $notice['pinned'] ? 0 : 1;

        // If pinning, enforce cap
        if ($newPinState === 1) {
            $pinnedCount = NoticeModel::countPinned();
            if ($pinnedCount >= self::MAX_PINNED_NOTICES) {
                throw new InvalidArgumentException("Maximum of " . self::MAX_PINNED_NOTICES . " notices can be pinned at a time. Please unpin an older notice first.");
            }
        }

        $db->beginTransaction();
        try {
            $updateStmt = $db->prepare("UPDATE notices SET pinned = ? WHERE id = ?");
            $updateStmt->execute([$newPinState, $id]);

            $action = ($newPinState === 1) ? 'NOTICE_PINNED' : 'NOTICE_UNPINNED';
            AuditModel::record(
                $actorId,
                $notice['author_name'],
                $action,
                ($newPinState === 1 ? "Pinned" : "Unpinned") . " notice #{$id}: '{$notice['title']}'."
            );

            $db->commit();

            return [
                'success' => true,
                'pinned'  => (bool)$newPinState,
                'message' => $newPinState ? 'Notice pinned to top.' : 'Notice unpinned.'
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 6. Soft-delete a notice.
     */
    public static function deleteNotice(int $id, int $actorId, string $actorRole): array {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM notices WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $notice = $stmt->fetch();

        if (!$notice) {
            throw new RuntimeException("Notice not found or already deleted.", 404);
        }

        // Authority Rule Check: Management cannot delete an Admin notice (with Denial Auditing)
        if ($notice['author_role'] === 'admin' && $actorRole !== 'admin') {
            AuditModel::record(
                $actorId,
                $notice['author_name'],
                'SECURITY_NOTICE_DELETE_DENIED',
                "Unauthorized attempt by {$actorRole} (#{$actorId}) to delete Admin notice #{$id} ('{$notice['title']}'). Action blocked."
            );
            throw new RuntimeException("Only an Administrator has permission to delete an Admin notice.", 403);
        }

        $db->beginTransaction();
        try {
            $delStmt = $db->prepare("UPDATE notices SET deleted_at = NOW() WHERE id = ?");
            $delStmt->execute([$id]);

            AuditModel::record(
                $actorId,
                $notice['author_name'],
                'NOTICE_DELETED',
                "Deleted notice #{$id}: '{$notice['title']}'."
            );

            $db->commit();

            // Run proactive maintenance / purge of notices past 4 weeks
            self::purgeExpiredNotices();

            return [
                'success' => true,
                'message' => 'Notice deleted successfully.'
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 7. Purges notices soft-deleted > 4 weeks ago or expired > 4 weeks ago.
     * Automatically unlinks their attachments from disk.
     */
    public static function purgeExpiredNotices(): int {
        try {
            $db = Database::getConnection();
            $stmt = $db->query(
                "SELECT id, attachment_path FROM notices 
                 WHERE (deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 4 WEEK))
                    OR (expires_at IS NOT NULL AND expires_at < DATE_SUB(NOW(), INTERVAL 4 WEEK))"
            );
            $stale = $stmt->fetchAll();
            if (empty($stale)) {
                return 0;
            }

            $purgedCount = 0;
            $publicDir = dirname(__DIR__, 2) . '/public';

            foreach ($stale as $row) {
                if (!empty($row['attachment_path'])) {
                    $filePath = $publicDir . $row['attachment_path'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                $del = $db->prepare("DELETE FROM notices WHERE id = ?");
                $del->execute([$row['id']]);
                $purgedCount++;
            }

            if ($purgedCount > 0) {
                AuditModel::record(
                    1,
                    'System Janitor',
                    'NOTICES_AUTO_PURGED',
                    "Permanently purged {$purgedCount} expired/deleted notices past the 4-week retention threshold."
                );
            }

            return $purgedCount;
        } catch (\Throwable $e) {
            error_log('[NoticeActions Purge Error] ' . $e->getMessage());
            return 0;
        }
    }
}
