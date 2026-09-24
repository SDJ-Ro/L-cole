<?php
/**
 * =========================================================================
 * L'ÉCOLE — AUDIT MODEL
 * =========================================================================
 * Records immutable security audit logs for compliance, account tracking,
 * and security diagnostics in the `activity_logs` table.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';

class AuditModel extends Model {

    /**
     * Resolve the client IP address considering proxy headers safely.
     */
    public static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return trim(explode(',', $_SERVER['HTTP_CF_CONNECTING_IP'])[0]);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Resolve client user agent.
     */
    public static function getUserAgent(): string {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? (php_sapi_name() === 'cli' ? 'CLI/Terminal' : 'Unknown'), 0, 500);
    }

    /**
     * Static convenience method to quickly write an audit entry anywhere.
     */
    public static function record(?int $accountId, ?string $identifier, string $action, ?string $details = null): bool {
        try {
            $instance = new self();
            return $instance->logAuthEvent($accountId, $identifier, $action, $details);
        } catch (\Throwable $e) {
            error_log("[AuditModel Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record an event into the immutable activity_logs table.
     */
    public function logAuthEvent(?int $accountId, ?string $identifier, string $action, ?string $details = null, ?string $ip = null, ?string $userAgent = null): bool {
        try {
            $ipAddress = $ip ?: self::getClientIp();
            $ua = $userAgent ?: self::getUserAgent();

            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (account_id, identifier, action, ip_address, user_agent, details, created_at)
                VALUES (:account_id, :identifier, :action, :ip_address, :user_agent, :details, NOW())
            ");

            return $stmt->execute([
                ':account_id' => $accountId,
                ':identifier' => $identifier ? substr($identifier, 0, 191) : null,
                ':action'     => substr(strtoupper($action), 0, 100),
                ':ip_address' => substr($ipAddress, 0, 45),
                ':user_agent' => $ua,
                ':details'    => $details
            ]);
        } catch (\Throwable $e) {
            error_log("[AuditModel Error] Could not write activity log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve the most recent audit records.
     */
    public function getRecentLogs(int $limit = 50, ?string $action = null): array {
        try {
            $limit = max(1, min(500, $limit));
            if ($action) {
                $stmt = $this->db->prepare("
                    SELECT a.*, u.role, u.activation_status 
                    FROM activity_logs a
                    LEFT JOIN user_accounts u ON a.account_id = u.id
                    WHERE a.action = :action
                    ORDER BY a.created_at DESC, a.id DESC
                    LIMIT " . (int)$limit
                );
                $stmt->execute([':action' => strtoupper($action)]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT a.*, u.role, u.activation_status 
                    FROM activity_logs a
                    LEFT JOIN user_accounts u ON a.account_id = u.id
                    ORDER BY a.created_at DESC, a.id DESC
                    LIMIT " . (int)$limit
                );
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("[AuditModel Error] Failed reading activity logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve audit history for a specific account ID.
     */
    public function getLogsByAccount(int $accountId, int $limit = 25): array {
        try {
            $limit = max(1, min(100, $limit));
            $stmt = $this->db->prepare("
                SELECT * FROM activity_logs 
                WHERE account_id = :aid
                ORDER BY created_at DESC, id DESC
                LIMIT " . (int)$limit
            );
            $stmt->execute([':aid' => $accountId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("[AuditModel Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve formatted logs for the Admin Audit UI view.
     */
    public static function getAll(int $limit = 100): array {
        try {
            $instance = new self();
            $raw = $instance->getRecentLogs($limit);
            $formatted = [];
            foreach ($raw as $r) {
                $role = !empty($r['role']) ? ucfirst($r['role']) : 'System';
                $actor = $r['identifier'] ?: 'System / Automated';
                $cleanActor = preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $actor)[0]);
                $initials = strtoupper(substr($cleanActor ?: 'SY', 0, 2));

                $createdAt = strtotime($r['created_at']);
                $formatted[] = [
                    'id'            => $r['id'],
                    'role'          => $role,
                    'action'        => $r['action'],
                    'actor'         => $actor,
                    'avatar'        => $initials,
                    'actorRole'     => $role . ' account',
                    'details'       => $r['details'] ?? '',
                    'date'          => date('M d, Y', $createdAt),
                    'time'          => date('H:i:s', $createdAt),
                    'ip'            => $r['ip_address'] ?? '127.0.0.1',
                    'linkedStudent' => null
                ];
            }
            return $formatted;
        } catch (\Throwable $e) {
            error_log("[AuditModel Error] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Filter options for activity dropdown in Admin Audit view.
     */
    public static function getActivityOptions(): array {
        return [
            'All activities',
            'LOGIN_SUCCESS',
            'LOGIN_FAILED',
            'ACCOUNT_LOCKED',
            'ACCOUNT_UNLOCKED_OTP',
            'PASSWORD_RESET_REQUESTED',
            'PASSWORD_RESET_COMPLETED',
            'ACCOUNT_ACTIVATED',
            'ACCOUNT_REGISTERED',
            'SESSION_EXPIRED',
            'LOGOUT'
        ];
    }

    /**
     * Filter options for actor role dropdown in Admin Audit view.
     */
    public static function getActorOptions(): array {
        return [
            'All actors',
            'Admin',
            'Management',
            'Teacher',
            'Parent',
            'Student',
            'System'
        ];
    }
}

