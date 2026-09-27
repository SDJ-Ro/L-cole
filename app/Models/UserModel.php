<?php
/**
 * =========================================================================
 * L'ÉCOLE — USER MODEL (READ-ONLY QUERIES & VALIDATION)
 * =========================================================================
 * Dedicated read model providing account lookup, normalization,
 * role profile resolution, locked accounts listing, and pure validation
 * logic without database mutations.
 *
 * All mutation and authentication methods reside in UserActions.php.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';

class UserModel extends Model {

    /**
     * Normalizes a student index into standard canonical format: STU-YYYY-XXXX.
     */
    public static function normalizeStudentIndex(string $identifier): string {
        $norm = preg_replace('/[^a-zA-Z0-9]/', '', $identifier);
        if (preg_match('/^stu(\d{4})(\d{4})$/i', $norm, $m)) {
            return 'STU-' . $m[1] . '-' . $m[2];
        } elseif (preg_match('/^(\d{4})(\d{4})$/', $norm, $m)) {
            return 'STU-' . $m[1] . '-' . $m[2];
        }
        return $identifier;
    }

    /**
     * Unified, canonical account finder:
     * Resolves an account by:
     * 1. Direct case-insensitive identifier (email or student index)
     * 2. Normalized student index format (e.g. stu20260001, 2026/0001 -> STU-2026-0001)
     * 3. Linked student record in the students table by index_no
     * 4. Teachers personal/institutional email or staff_id
     * 5. Management personal/institutional email or staff_id
     * 6. Parents personal email or parent_id
     */
    public static function findAccountByIdentifier(string $identifier): ?array {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $db = Database::getConnection();

        // 1. Direct case-insensitive match on user_accounts.identifier
        $stmt = $db->prepare("
            SELECT * FROM user_accounts 
            WHERE LOWER(identifier) = LOWER(:id) 
            LIMIT 1
        ");
        $stmt->execute([':id' => $identifier]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($account) {
            return $account;
        }

        // 2. Normalized student index match
        $norm = self::normalizeStudentIndex($identifier);
        if ($norm !== $identifier) {
            $stmt = $db->prepare("
                SELECT * FROM user_accounts 
                WHERE LOWER(identifier) = LOWER(:id) 
                LIMIT 1
            ");
            $stmt->execute([':id' => $norm]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($account) {
                return $account;
            }
        }

        // 3. Match against students table by index_no
        $stmt = $db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN students s ON s.account_id = ua.id
            WHERE LOWER(s.index_no) = LOWER(:id1) OR LOWER(s.index_no) = LOWER(:id2)
            LIMIT 1
        ");
        $stmt->execute([':id1' => $identifier, ':id2' => $norm]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($account) {
            return $account;
        }

        // 4. Match against teachers table by personal_email, institutional_email, or staff_id
        $stmt = $db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN teachers t ON t.account_id = ua.id
            WHERE LOWER(t.personal_email) = LOWER(?) 
               OR LOWER(t.institutional_email) = LOWER(?)
               OR LOWER(t.staff_id) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($account) {
            return $account;
        }

        // 5. Match against management_profiles by personal_email, institutional_email, or staff_id
        $stmt = $db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN management_profiles m ON m.account_id = ua.id
            WHERE LOWER(m.personal_email) = LOWER(?) 
               OR LOWER(m.institutional_email) = LOWER(?)
               OR LOWER(m.staff_id) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($account) {
            return $account;
        }

        // 6. Match against parents table by personal_email or parent_id
        $stmt = $db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN parents p ON p.account_id = ua.id
            WHERE LOWER(p.personal_email) = LOWER(?) 
               OR LOWER(p.parent_id) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($account) {
            return $account;
        }

        return null;
    }

    /**
     * Checks if entered full name matches the pre-provisioned database profile.
     */
    public static function verifyFullNameMatches(string $enteredName, array $profile): array {
        $entered = trim($enteredName);
        if (empty($entered)) {
            return ['matches' => false, 'error' => 'Please enter your full name as registered with the school.'];
        }

        $dbFullName = trim($profile['full_name'] ?? '');
        $dbFirst    = trim($profile['first_name'] ?? '');
        $dbLast     = trim($profile['last_name'] ?? '');

        // If no name on record (unlikely), allow it through
        if (empty($dbFullName) && empty($dbFirst) && empty($dbLast)) {
            return ['matches' => true];
        }

        // String normalizer: lowercase, strip special characters, collapse whitespaces
        $normalize = function(string $s): string {
            $s = mb_strtolower($s, 'UTF-8');
            $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
            return trim(preg_replace('/\s+/', ' ', $s));
        };

        $normEntered = $normalize($entered);
        $normDbFull  = $normalize($dbFullName);
        $normDbFirst = $normalize($dbFirst);
        $normDbLast  = $normalize($dbLast);

        // 1. Exact match after normalization
        if ($normEntered === $normDbFull) {
            return ['matches' => true];
        }

        // 2. Tokenized word comparison
        $enteredTokens = array_values(array_filter(explode(' ', $normEntered)));
        $dbTokens      = array_values(array_filter(explode(' ', $normDbFull)));
        if (!empty($normDbFirst) && !in_array($normDbFirst, $dbTokens, true)) $dbTokens[] = $normDbFirst;
        if (!empty($normDbLast)  && !in_array($normDbLast,  $dbTokens, true)) $dbTokens[] = $normDbLast;

        // Check if both first and last name match or exist in entered name
        $hasLast = !empty($normDbLast) && (
            in_array($normDbLast, $enteredTokens, true) || 
            str_contains($normEntered, $normDbLast)
        );
        $hasFirst = !empty($normDbFirst) && (
            in_array($normDbFirst, $enteredTokens, true) || 
            str_contains($normEntered, $normDbFirst)
        );
        $firstInitial = !empty($normDbFirst) ? mb_substr($normDbFirst, 0, 1) : '';
        $hasFirstInitial = $firstInitial && in_array($firstInitial, $enteredTokens, true);

        if ($hasLast && ($hasFirst || $hasFirstInitial)) {
            return ['matches' => true];
        }

        // 3. Significant token overlap (at least 2 tokens match or >= 50% match)
        $matchingTokens = array_intersect($enteredTokens, $dbTokens);
        if (count($matchingTokens) >= 2 || (count($dbTokens) > 0 && (count($matchingTokens) / count($dbTokens)) >= 0.5)) {
            return ['matches' => true];
        }

        // 4. Similarity ratio
        similar_text($normEntered, $normDbFull, $percent);
        if ($percent >= 70) {
            return ['matches' => true];
        }

        return [
            'matches' => false,
            'error'   => 'The entered full name does not match our school admission records for this account. Please enter the full name as registered with the school.'
        ];
    }

    /**
     * Helper to mask email address for privacy (e.g. j***@domain.com).
     */
    public static function maskEmail(string $email): string {
        $email = trim($email);
        if (!str_contains($email, '@')) return $email;
        [$user, $domain] = explode('@', $email, 2);
        $len = strlen($user);
        if ($len <= 2) {
            $maskedUser = $user[0] . '*';
        } else {
            $maskedUser = substr($user, 0, 2) . str_repeat('*', max(2, $len - 3)) . substr($user, -1);
        }
        return $maskedUser . '@' . $domain;
    }

    /**
     * Shared password validation policy:
     * Minimum 8 characters, must contain at least one letter, one number, and one symbol.
     */
    public static function validatePasswordPolicy(string $password): array {
        if (strlen($password) < 8) {
            return [
                'valid'   => false,
                'message' => 'Password must be at least 8 characters in length.'
            ];
        }
        if (!preg_match('/[a-zA-Z]/', $password)) {
            return [
                'valid'   => false,
                'message' => 'Password must contain at least one letter.'
            ];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return [
                'valid'   => false,
                'message' => 'Password must contain at least one number.'
            ];
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return [
                'valid'   => false,
                'message' => 'Password must contain at least one special symbol.'
            ];
        }
        return ['valid' => true, 'message' => 'Password meets security requirements.'];
    }

    /**
     * Fetch role-specific profile details.
     */
    public static function getProfileByAccountId(int $accountId, string $role): array {
        $tableMap = [
            'student'    => 'students',
            'teacher'    => 'teachers',
            'parent'     => 'parents',
            'management' => 'management_profiles',
            'admin'      => 'admin_profiles'
        ];

        $table = $tableMap[$role] ?? null;
        if (!$table) {
            return [];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE account_id = :id LIMIT 1");
        $stmt->execute([':id' => $accountId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /**
     * Find notification/reset email for an account.
     * (Students dispatch to parent's email; teachers and management prefer personal email).
     */
    public static function getNotificationEmailForAccount(array $account): ?string {
        $role = $account['role'] ?? '';
        $accountId = (int)($account['id'] ?? 0);
        $db = Database::getConnection();

        if ($role === 'student') {
            // Find linked parent's personal email through pivot table
            $stmt = $db->prepare("
                SELECT p.personal_email 
                FROM student_parents sp 
                JOIN students s ON sp.student_id = s.id 
                JOIN parents p ON sp.parent_id = p.id 
                WHERE s.account_id = :account_id 
                LIMIT 1
            ");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
        } elseif ($role === 'teacher') {
            $stmt = $db->prepare("SELECT personal_email, institutional_email FROM teachers WHERE account_id = :account_id LIMIT 1");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
            if (!empty($res['institutional_email'])) {
                return $res['institutional_email'];
            }
        } elseif ($role === 'management') {
            $stmt = $db->prepare("SELECT personal_email, institutional_email FROM management_profiles WHERE account_id = :account_id LIMIT 1");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
            if (!empty($res['institutional_email'])) {
                return $res['institutional_email'];
            }
        } elseif ($role === 'parent') {
            $stmt = $db->prepare("SELECT personal_email FROM parents WHERE account_id = :account_id LIMIT 1");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
        }

        // Fallback to identifier
        return $account['identifier'] ?? null;
    }

    /**
     * Break-Glass Admin CLI: List all currently locked accounts.
     */
    public static function listLockedAccounts(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT id, identifier, role, activation_status, failed_login_count, lock_expires_at, locked_at, last_login_at 
            FROM user_accounts 
            WHERE locked_at IS NOT NULL 
               OR (lock_expires_at IS NOT NULL AND lock_expires_at > NOW())
            ORDER BY id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
