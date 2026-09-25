<?php
/**
 * =========================================================================
 * L'ÉCOLE — USER MODEL (AUTHENTICATION & ACCOUNT LIFECYCLE)
 * =========================================================================
 * Central business logic for account authentication, progressive lockout,
 * first-login password enforcement, account activation, and OTP lifecycle.
 * Inherits the shared PDO connection and transaction utilities from Model.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/MailService.php';
require_once __DIR__ . '/AuditModel.php';

class UserModel extends Model {

    /**
     * Soft lockout duration in minutes after 5 consecutive failures.
     */
    private const LOCKOUT_MINUTES = 15;

    /**
     * Consecutive failed attempts threshold before soft lockout.
     */
    private const MAX_FAILED_ATTEMPTS = 5;

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
     */
    public function findAccountByIdentifier(string $identifier): ?array {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        // 1. Direct case-insensitive match on user_accounts.identifier
        $stmt = $this->db->prepare("
            SELECT * FROM user_accounts 
            WHERE LOWER(identifier) = LOWER(:id) 
            LIMIT 1
        ");
        $stmt->execute([':id' => $identifier]);
        $account = $stmt->fetch();
        if ($account) {
            return $account;
        }

        // 2. Normalized student index match
        $norm = self::normalizeStudentIndex($identifier);
        if ($norm !== $identifier) {
            $stmt = $this->db->prepare("
                SELECT * FROM user_accounts 
                WHERE LOWER(identifier) = LOWER(:id) 
                LIMIT 1
            ");
            $stmt->execute([':id' => $norm]);
            $account = $stmt->fetch();
            if ($account) {
                return $account;
            }
        }

        // 3. Match against students table by index_no
        $stmt = $this->db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN students s ON s.account_id = ua.id
            WHERE LOWER(s.index_no) = LOWER(:id1) OR LOWER(s.index_no) = LOWER(:id2)
            LIMIT 1
        ");
        $stmt->execute([':id1' => $identifier, ':id2' => $norm]);
        $account = $stmt->fetch();
        if ($account) {
            return $account;
        }

        // 4. Match against teachers table by personal_email, institutional_email, or staff_id
        $stmt = $this->db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN teachers t ON t.account_id = ua.id
            WHERE LOWER(t.personal_email) = LOWER(?) 
               OR LOWER(t.institutional_email) = LOWER(?)
               OR LOWER(t.staff_id) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $account = $stmt->fetch();
        if ($account) {
            return $account;
        }

        // 5. Match against management_profiles by personal_email, institutional_email, or staff_id
        $stmt = $this->db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN management_profiles m ON m.account_id = ua.id
            WHERE LOWER(m.personal_email) = LOWER(?) 
               OR LOWER(m.institutional_email) = LOWER(?)
               OR LOWER(m.staff_id) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $account = $stmt->fetch();
        if ($account) {
            return $account;
        }

        // 6. Match against parents table by personal_email or parent_id
        $stmt = $this->db->prepare("
            SELECT ua.* FROM user_accounts ua
            JOIN parents p ON p.account_id = ua.id
            WHERE LOWER(p.personal_email) = LOWER(?) 
               OR LOWER(p.parent_id) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $account = $stmt->fetch();
        if ($account) {
            return $account;
        }

        return null;
    }

    /**
     * Authenticate an account by identifier (email or index number), password, and role.
     *
     * @param string $identifier Email address or Student Index Number
     * @param string $password   Plain-text password to verify
     * @param string $role       Expected role ('admin', 'management', 'teacher', 'parent', 'student')
     * @return array Result array with status, user data, or specific redirect flag
     */
    public function authenticate(string $identifier, string $password, string $role = ''): array {
        $identifier = trim($identifier);
        $expectedRole = strtolower(trim($role));

        // 1. Fetch account row using canonical finder (handles email, index, normalization, and student links)
        $account = $this->findAccountByIdentifier($identifier);

        // 2. Anti-enumeration: Account does not exist
        if (!$account) {
            AuditModel::record(null, $identifier, 'LOGIN_FAILED', "Identifier not found or role mismatch (tried role: {$expectedRole})");
            return [
                'success' => false,
                'error'   => 'Invalid credentials.',
                'code'    => 'INVALID_CREDENTIALS'
            ];
        }

        // 3. Check if account is still PENDING (Needs initial activation / signup)
        if ($account['activation_status'] === 'PENDING') {
            AuditModel::record((int)$account['id'], $account['identifier'], 'LOGIN_BLOCKED_PENDING', 'Attempted sign-in on unactivated account.');
            return [
                'success'           => false,
                'error'             => 'This account is pending activation. Please set your password to activate.',
                'code'              => 'PENDING_ACTIVATION',
                'needs_activation'  => true,
                'identifier'        => $account['identifier'],
                'role'              => $account['role']
            ];
        }

        // 4. Check if account is INACTIVE / Deactivated
        if ($account['activation_status'] === 'INACTIVE') {
            AuditModel::record((int)$account['id'], $account['identifier'], 'LOGIN_BLOCKED_INACTIVE', 'Attempted sign-in on deactivated account.');
            return [
                'success' => false,
                'error'   => 'This account has been deactivated. Please contact the School Office (office@lecole.edu).',
                'code'    => 'ACCOUNT_INACTIVE'
            ];
        }

        $now = new DateTime();

        // 5. Check for HARD lockout (requires Admin manual unlock)
        if (!empty($account['locked_at'])) {
            AuditModel::record((int)$account['id'], $account['identifier'], 'LOGIN_BLOCKED_HARD_LOCK', 'Attempted sign-in on hard-locked account.');
            return [
                'success' => false,
                'error'   => 'This account has been locked for security. Please notify the Administrator (admin@lecole.edu) immediately.',
                'code'    => 'ACCOUNT_LOCKED_HARD'
            ];
        }

        // 6. Check for SOFT lockout (progressive 15-minute timer)
        if (!empty($account['lock_expires_at'])) {
            $lockExpiry = new DateTime($account['lock_expires_at']);
            if ($now < $lockExpiry) {
                $remainingMinutes = ceil(($lockExpiry->getTimestamp() - $now->getTimestamp()) / 60);
                AuditModel::record((int)$account['id'], $account['identifier'], 'LOGIN_BLOCKED_SOFT_LOCK', "Attempted sign-in during 15m lockout ({$remainingMinutes}m remaining).");
                return [
                    'success' => false,
                    'error'   => "Account temporarily locked due to multiple failed attempts. Please check your email for the unlock code to regain access immediately, or try again in {$remainingMinutes} minute(s).",
                    'code'    => 'ACCOUNT_LOCKED_SOFT',
                    'identifier' => $account['identifier']
                ];
            } else {
                // Lock expired: reset lock timer and failed count
                $this->clearSoftLock($account['id']);
                $account['failed_login_count'] = 0;
            }
        }

        // 7. Verify password hash using native PHP bcrypt
        if (empty($account['password_hash']) || !password_verify($password, $account['password_hash'])) {
            // Failed attempt: increment failed count and check lockout threshold
            $this->handleFailedLogin($account);
            $newCount = (int)$account['failed_login_count'] + 1;
            if ($newCount >= self::MAX_FAILED_ATTEMPTS) {
                return [
                    'success' => false,
                    'error'   => "Account locked after " . self::MAX_FAILED_ATTEMPTS . " failed attempts. A security alert with an unlock code has been sent to your email.",
                    'code'    => 'ACCOUNT_LOCKED_SOFT',
                    'identifier' => $account['identifier']
                ];
            }
            $remaining = self::MAX_FAILED_ATTEMPTS - $newCount;
            return [
                'success' => false,
                'error'   => "Invalid credentials. {$remaining} attempt(s) remaining before temporary lockout.",
                'code'    => 'INVALID_CREDENTIALS'
            ];
        }

        // 8. Authentication SUCCESS!
        // Reset failed attempts, clear locks, and record login timestamp
        $this->handleSuccessfulLogin($account['id'], $account['identifier'], $account['role']);

        // 9. Fetch profile data for this role
        $profile = $this->getProfileByAccountId($account['id'], $account['role']);

        return [
            'success'               => true,
            'account'               => [
                'id'                  => (int)$account['id'],
                'identifier'          => $account['identifier'],
                'role'                => $account['role'],
                'first_login_required'=> (bool)$account['first_login_required']
            ],
            'profile'               => $profile,
            'first_login_required'  => (bool)$account['first_login_required']
        ];
    }

    /**
     * Activates a pending account by setting its permanent password.
     *
     * @param string $identifier Email address or student index
     * @param string $password   New password chosen by user
     * @param string $role       Expected role
     * @return array Result array
     */
    public function activateAccount(string $identifier, string $password, string $role): array {
        return $this->registerOrActivateAccount('', $identifier, $password, $role);
    }

    /**
     * Registers a new account or activates an existing pending account.
     *
     * @param string $fullName   Full display name
     * @param string $identifier Email or student index number
     * @param string $password   Password chosen by user
     * @param string $role       Role: 'student', 'parent', 'teacher'
     * @return array Result array with status and message
     */
    public function registerOrActivateAccount(string $fullName, string $identifier, string $password, string $role): array {
        $fullName = trim($fullName);
        $identifier = trim($identifier);
        $role = strtolower(trim($role));

        // Leadership security guard: Admin & Management can never be self-created online
        if (in_array($role, ['admin', 'management'], true)) {
            return [
                'success' => false,
                'error'   => 'Management and Administrator accounts cannot be self-created online. Leadership workspaces are provisioned by the Board of Directors.'
            ];
        }

        // 1. Password policy validation
        $policyCheck = self::validatePasswordPolicy($password);
        if (!$policyCheck['valid']) {
            return [
                'success' => false,
                'error'   => $policyCheck['message']
            ];
        }

        // 2. Lookup pre-provisioned account using canonical unified finder (handles normalization and student registry)
        $existing = $this->findAccountByIdentifier($identifier);

        // Provisioned-first rule: If no account was pre-provisioned by the school, reject immediately!
        if (!$existing) {
            if ($role === 'student') {
                return [
                    'success' => false,
                    'error'   => 'Student index not found in the school registry. Please visit the Admissions Office or contact office@lecole.edu.'
                ];
            }
            return [
                'success' => false,
                'error'   => 'No pending account found for this email address. Accounts must be provisioned by the School Office. Please contact office@lecole.edu.'
            ];
        }

        // If existing account was found but has a different role, prevent cross-role hijacking
        if (strtolower($existing['role']) !== $role) {
            return [
                'success' => false,
                'error'   => 'An account with this identifier already exists under a different portal role.'
            ];
        }

        // Case A: Account already exists and is ACTIVE
        if ($existing['activation_status'] === 'ACTIVE') {
            return [
                'success' => false,
                'error'   => 'An account with this email/index number already exists and is active. Please sign in directly.'
            ];
        }

        // Case B: Account exists and is INACTIVE
        if ($existing['activation_status'] === 'INACTIVE') {
            return [
                'success' => false,
                'error'   => 'This account has been deactivated. Please contact the School Office (office@lecole.edu).'
            ];
        }

        // Case C: Valid activation — Account exists and is strictly PENDING
        if ($existing['activation_status'] === 'PENDING') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $this->beginTransaction();
            try {
                $updateStmt = $this->db->prepare("
                    UPDATE user_accounts 
                    SET password_hash = :hash,
                        activation_status = 'ACTIVE',
                        activated_at = NOW(),
                        first_login_required = 0,
                        failed_login_count = 0,
                        lock_expires_at = NULL,
                        locked_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':hash' => $hash,
                    ':id'   => $existing['id']
                ]);

                // Update full name in corresponding pre-provisioned profile if provided
                if (!empty($fullName)) {
                    $this->syncProfileName((int)$existing['id'], $existing['role'], $fullName);
                }

                $this->commit();

                AuditModel::record((int)$existing['id'], $existing['identifier'], 'ACCOUNT_ACTIVATED', "Pre-provisioned {$role} account activated.");

                // Dispatch notification to real inbox
                MailService::sendWelcomeConfirmation($existing['identifier'], $existing['role'], $fullName ?: $existing['identifier']);

                return [
                    'success' => true,
                    'message' => 'Account activated successfully! You may now sign in.'
                ];
            } catch (\Exception $e) {
                $this->rollBack();
                return [
                    'success' => false,
                    'error'   => 'Failed to activate account. Please try again.'
                ];
            }
        }

        return [
            'success' => false,
            'error'   => 'Unable to activate this account. Please contact the School Office.'
        ];
    }

    private function syncProfileName(int $accountId, string $role, string $fullName): void {
        $parts = explode(' ', trim($fullName), 2);
        $firstName = $parts[0] ?? $fullName;
        $lastName = $parts[1] ?? '';

        if ($role === 'student') {
            $stmt = $this->db->prepare("UPDATE students SET full_name = :fn, first_name = :first, last_name = :last WHERE account_id = :id AND (full_name IS NULL OR full_name = '')");
            $stmt->execute([':fn' => $fullName, ':first' => $firstName, ':last' => $lastName, ':id' => $accountId]);
        } elseif ($role === 'parent') {
            $stmt = $this->db->prepare("UPDATE parents SET full_name = :fn, first_name = :first, last_name = :last WHERE account_id = :id AND (full_name IS NULL OR full_name = '')");
            $stmt->execute([':fn' => $fullName, ':first' => $firstName, ':last' => $lastName, ':id' => $accountId]);
        } elseif ($role === 'teacher') {
            $stmt = $this->db->prepare("UPDATE teachers SET full_name = :fn, first_name = :first, last_name = :last WHERE account_id = :id AND (full_name IS NULL OR full_name = '')");
            $stmt->execute([':fn' => $fullName, ':first' => $firstName, ':last' => $lastName, ':id' => $accountId]);
        }
    }

    /**
     * Forces a password update (e.g. Student first login change).
     */
    public function forcePasswordChange(int $accountId, string $newPassword): array {
        $policyCheck = self::validatePasswordPolicy($newPassword);
        if (!$policyCheck['valid']) {
            return [
                'success' => false,
                'error'   => $policyCheck['message']
            ];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            UPDATE user_accounts 
            SET password_hash = :hash,
                first_login_required = 0,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':hash' => $hash,
            ':id'   => $accountId
        ]);

        AuditModel::record((int)$accountId, null, 'PASSWORD_FIRST_LOGIN_CHANGED', 'First-login temporary password changed.');

        return [
            'success' => true,
            'message' => 'Password changed successfully.'
        ];
    }

    /**
     * Request a 6-digit password reset OTP.
     *
     * @param string $identifier Email or student index number
     * @return array Contains success flag and generated OTP for delivery
     */
    public function requestPasswordReset(string $identifier): array {
        $identifier = trim($identifier);

        // 1. Find account using canonical unified finder
        $account = $this->findAccountByIdentifier($identifier);

        // 2. Anti-enumeration: Return success-like response regardless if account doesn't exist
        if (!$account) {
            return [
                'success'    => true,
                'account_id' => null,
                'recipient'  => null,
                'otp'        => null
            ];
        }

        // 3. Deactivated account check
        if ($account['activation_status'] === 'INACTIVE') {
            return [
                'success' => false,
                'error'   => 'This account has been deactivated. Please contact the School Office (office@lecole.edu).'
            ];
        }

        // 4. Hard lockout check (requires manual Administrator intervention)
        if (!empty($account['locked_at'])) {
            return [
                'success' => false,
                'error'   => 'This account has been locked for security. Please notify the Administrator (admin@lecole.edu) directly.'
            ];
        }

        // Rate Limiting: Cap OTP requests to maximum 3 requests per 10 minutes per account
        $rateStmt = $this->db->prepare("
            SELECT COUNT(*) AS req_count 
            FROM activity_logs 
            WHERE account_id = :account_id 
              AND action = 'PASSWORD_RESET_REQUESTED' 
              AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $rateStmt->execute([':account_id' => $account['id']]);
        $rateRow = $rateStmt->fetch();
        if ($rateRow && (int)$rateRow['req_count'] >= 3) {
            AuditModel::record(
                (int)$account['id'],
                $account['identifier'],
                'PASSWORD_RESET_RATE_LIMITED',
                'Blocked: Exceeded maximum 3 password reset requests within 10 minutes.'
            );
            return [
                'success'      => false,
                'error'        => 'Too many password reset requests. Please check your inbox for the recent code or wait 10 minutes before requesting again.',
                'rate_limited' => true
            ];
        }

        // Generate cryptographically secure 6-digit numeric code
        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);

        // Invalidate any previous unused OTPs for this account
        $invalidateStmt = $this->db->prepare("
            DELETE FROM password_reset_otps WHERE account_id = :account_id
        ");
        $invalidateStmt->execute([':account_id' => $account['id']]);

        // Insert new OTP with 10-minute expiry window
        $insertOtp = $this->db->prepare("
            INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
            VALUES (:account_id, :otp_hash, 5, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ");
        $insertOtp->execute([
            ':account_id' => $account['id'],
            ':otp_hash'   => $otpHash
        ]);

        // Determine destination email (for students, dispatch goes to guardian/parent)
        $recipientEmail = $this->getNotificationEmailForAccount($account) ?: $account['identifier'];

        // Dispatch via Book Cover Mail Service (delivers to lecoletesting@gmail.com)
        $dispatchRes = MailService::sendPasswordResetOtp($recipientEmail, $otp, $account['role'], $account['identifier']);

        AuditModel::record((int)$account['id'], $account['identifier'], 'PASSWORD_RESET_REQUESTED', "OTP dispatched to {$recipientEmail} (routed to lecoletesting@gmail.com)");

        return [
            'success'         => true,
            'account_id'      => (int)$account['id'],
            'recipient_email' => $recipientEmail,
            'routed_to'       => $dispatchRes['routed_to'] ?? ''
        ];
    }

    /**
     * Unlock an account and optionally set a new password using a verified 6-digit OTP code.
     *
     * @param string $identifier   Account email or student index
     * @param string $otp          6-digit numeric OTP code
     * @param string|null $newPassword Optional new password. If omitted, only unlocks without changing password.
     * @return array Result array with status and user feedback message
     */
    public function unlockWithOtp(string $identifier, string $otp, ?string $newPassword = null): array {
        $identifier = trim($identifier);

        // 1. Password policy check (only if new password was provided)
        $hasNewPassword = !empty($newPassword);
        if ($hasNewPassword) {
            $policyCheck = self::validatePasswordPolicy($newPassword);
            if (!$policyCheck['valid']) {
                return [
                    'success' => false,
                    'error'   => $policyCheck['message']
                ];
            }
        }

        // 2. Find account using canonical unified finder
        $account = $this->findAccountByIdentifier($identifier);

        if (!$account) {
            return [
                'success' => false,
                'error'   => 'Invalid or expired verification code.'
            ];
        }

        if (!empty($account['locked_at'])) {
            return [
                'success' => false,
                'error'   => 'This account has been administratively locked for security. Please contact admin@lecole.edu directly.'
            ];
        }

        // 3. Find active unexpired OTP
        $otpStmt = $this->db->prepare("
            SELECT * FROM password_reset_otps 
            WHERE account_id = :account_id 
              AND used_at IS NULL 
              AND expires_at > NOW() 
            ORDER BY id DESC LIMIT 1
        ");
        $otpStmt->execute([':account_id' => $account['id']]);
        $activeOtp = $otpStmt->fetch();

        if (!$activeOtp || $activeOtp['attempts_left'] <= 0) {
            return [
                'success' => false,
                'error'   => 'Verification code has expired or maximum attempts exceeded. Please request a new one.'
            ];
        }

        // 4. Verify code
        if (!password_verify($otp, $activeOtp['otp_hash'])) {
            // Decrement remaining attempts
            $decrement = $this->db->prepare("
                UPDATE password_reset_otps 
                SET attempts_left = attempts_left - 1 
                WHERE id = :id
            ");
            $decrement->execute([':id' => $activeOtp['id']]);

            return [
                'success' => false,
                'error'   => 'Invalid verification code.'
            ];
        }

        // 5. Code matched! Mark OTP as used and unlock
        $this->beginTransaction();
        try {
            $markUsed = $this->db->prepare("
                UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id
            ");
            $markUsed->execute([':id' => $activeOtp['id']]);

            if ($hasNewPassword) {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $updatePass = $this->db->prepare("
                    UPDATE user_accounts 
                    SET password_hash = :hash,
                        activation_status = IF(activation_status = 'PENDING', 'ACTIVE', activation_status),
                        first_login_required = 0,
                        failed_login_count = 0,
                        lock_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id AND locked_at IS NULL
                ");
                $updatePass->execute([
                    ':hash' => $newHash,
                    ':id'   => $account['id']
                ]);

                $this->commit();

                AuditModel::record((int)$account['id'], $account['identifier'], 'PASSWORD_RESET_COMPLETED', 'Password reset & unlocked successfully with verified 6-digit OTP.');

                return [
                    'success'       => true,
                    'unlocked_only' => false,
                    'message'       => 'Password reset and account unlocked! Please return to the sign-in page and enter your new password.',
                    'role'          => $account['role'],
                    'identifier'    => $account['identifier'],
                    'signin_url'    => '/auth/' . strtolower($account['role'] ?: 'student')
                ];
            } else {
                // Just unlock! Keep current password intact
                $updatePass = $this->db->prepare("
                    UPDATE user_accounts 
                    SET failed_login_count = 0,
                        lock_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id AND locked_at IS NULL
                ");
                $updatePass->execute([':id' => $account['id']]);

                $this->commit();

                AuditModel::record((int)$account['id'], $account['identifier'], 'ACCOUNT_UNLOCKED_OTP', 'Account unlocked with verified 6-digit OTP (password preserved).');

                return [
                    'success'       => true,
                    'unlocked_only' => true,
                    'message'       => 'Account unlocked successfully! Please return to the sign-in page and enter your password.',
                    'role'          => $account['role'],
                    'identifier'    => $account['identifier'],
                    'signin_url'    => '/auth/' . strtolower($account['role'] ?: 'student')
                ];
            }
        } catch (\Throwable $e) {
            $this->rollBack();
            return [
                'success' => false,
                'error'   => 'An unexpected error occurred while unlocking your account.'
            ];
        }
    }

    /**
     * Single-Step Authenticated Verification:
     * Validates 6-digit OTP and verifies password (or sets new password),
     * clears lockout, logs user in, and returns full account/profile data.
     *
     * @param string $identifier Account email or student index
     * @param string $otp        6-digit numeric OTP code
     * @param string $password   Existing password (if keeping) or new password (if resetting)
     * @param bool   $isReset    True if user chose to set a new password
     * @return array Result array with status, user data, or error message
     */
    public function authenticateOrResetWithOtp(string $identifier, string $otp, string $password, bool $isReset = false): array {
        $identifier = trim($identifier);
        $otp        = trim($otp);
        $password   = trim($password);

        if (empty($identifier) || empty($otp) || empty($password)) {
            return [
                'success' => false,
                'error'   => 'Please provide both your verification code and password.'
            ];
        }

        // 1. Find account using canonical unified finder
        $account = $this->findAccountByIdentifier($identifier);

        if (!$account) {
            return [
                'success' => false,
                'error'   => 'Invalid or expired verification code.'
            ];
        }

        if (!empty($account['locked_at'])) {
            return [
                'success' => false,
                'error'   => 'This account has been administratively locked for security. Please contact admin@lecole.edu directly.'
            ];
        }

        // 2. Find active unexpired OTP
        $otpStmt = $this->db->prepare("
            SELECT * FROM password_reset_otps 
            WHERE account_id = :account_id 
              AND used_at IS NULL 
              AND expires_at > NOW() 
            ORDER BY id DESC LIMIT 1
        ");
        $otpStmt->execute([':account_id' => $account['id']]);
        $activeOtp = $otpStmt->fetch();

        if (!$activeOtp || $activeOtp['attempts_left'] <= 0) {
            return [
                'success' => false,
                'error'   => 'Verification code has expired or maximum attempts exceeded. Please request a new one.'
            ];
        }

        // 3. Verify OTP code
        if (!password_verify($otp, $activeOtp['otp_hash'])) {
            $decrement = $this->db->prepare("
                UPDATE password_reset_otps 
                SET attempts_left = attempts_left - 1 
                WHERE id = :id
            ");
            $decrement->execute([':id' => $activeOtp['id']]);

            return [
                'success' => false,
                'error'   => 'Invalid verification code.'
            ];
        }

        // 4. OTP is verified! Handle Reset vs Existing Password
        if ($isReset) {
            $policyCheck = self::validatePasswordPolicy($password);
            if (!$policyCheck['valid']) {
                return [
                    'success' => false,
                    'error'   => $policyCheck['message']
                ];
            }

            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $this->beginTransaction();
            try {
                $markUsed = $this->db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id");
                $markUsed->execute([':id' => $activeOtp['id']]);

                $updatePass = $this->db->prepare("
                    UPDATE user_accounts 
                    SET password_hash = :hash,
                        activation_status = IF(activation_status = 'PENDING', 'ACTIVE', activation_status),
                        first_login_required = 0,
                        failed_login_count = 0,
                        lock_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id AND locked_at IS NULL
                ");
                $updatePass->execute([
                    ':hash' => $newHash,
                    ':id'   => $account['id']
                ]);

                $this->commit();

                AuditModel::record((int)$account['id'], $account['identifier'], 'PASSWORD_RESET_COMPLETED', 'Password reset & account unlocked with verified 6-digit OTP.');
                $this->handleSuccessfulLogin((int)$account['id'], $account['identifier'], $account['role']);
                $profile = $this->getProfileByAccountId((int)$account['id'], $account['role']);

                return [
                    'success' => true,
                    'account' => [
                        'id'                   => (int)$account['id'],
                        'identifier'           => $account['identifier'],
                        'role'                 => $account['role'],
                        'first_login_required' => false
                    ],
                    'profile' => $profile,
                    'message' => 'Password reset and signed in successfully! Redirecting to your dashboard…'
                ];
            } catch (\Throwable $e) {
                $this->rollBack();
                return ['success' => false, 'error' => 'An unexpected error occurred while resetting your password.'];
            }
        } else {
            // Verify existing password
            if (empty($account['password_hash']) || !password_verify($password, $account['password_hash'])) {
                return [
                    'success' => false,
                    'error'   => 'Verification code accepted, but password was incorrect. If you forgot your password, please check "I forgot my password — set a new one".'
                ];
            }

            // Existing password matches! Mark OTP as used and clear lock
            $this->beginTransaction();
            try {
                $markUsed = $this->db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id");
                $markUsed->execute([':id' => $activeOtp['id']]);

                $clearLock = $this->db->prepare("
                    UPDATE user_accounts 
                    SET failed_login_count = 0,
                        lock_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id AND locked_at IS NULL
                ");
                $clearLock->execute([':id' => $account['id']]);

                $this->commit();

                AuditModel::record((int)$account['id'], $account['identifier'], 'ACCOUNT_UNLOCKED_LOGIN', 'Account unlocked and signed in with verified 6-digit OTP & password.');
                $this->handleSuccessfulLogin((int)$account['id'], $account['identifier'], $account['role']);
                $profile = $this->getProfileByAccountId((int)$account['id'], $account['role']);

                return [
                    'success' => true,
                    'account' => [
                        'id'                   => (int)$account['id'],
                        'identifier'           => $account['identifier'],
                        'role'                 => $account['role'],
                        'first_login_required' => (bool)$account['first_login_required']
                    ],
                    'profile' => $profile,
                    'message' => 'Account unlocked and signed in successfully! Redirecting to your dashboard…'
                ];
            } catch (\Throwable $e) {
                $this->rollBack();
                return ['success' => false, 'error' => 'An unexpected error occurred while unlocking your account.'];
            }
        }
    }

    /**
     * Verify a 6-digit OTP code and set the new password.
     */
    public function verifyAndResetPassword(string $identifier, string $otp, string $newPassword): array {
        return $this->unlockWithOtp($identifier, $otp, $newPassword);
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
     * Internal helper: Handles failed login attempt and progressive lockout escalation.
     */
    private function handleFailedLogin(array $account): void {
        $newCount = (int)$account['failed_login_count'] + 1;
        $lockExpiresAt = null;

        if ($newCount >= self::MAX_FAILED_ATTEMPTS) {
            $expiry = new DateTime();
            $expiry->modify('+' . self::LOCKOUT_MINUTES . ' minutes');
            $lockExpiresAt = $expiry->format('Y-m-d H:i:s');

            // Generate single-use 6-digit lockout bypass OTP
            $otp = (string)random_int(100000, 999999);
            $otpHash = password_hash($otp, PASSWORD_BCRYPT);

            // Invalidate any previous unused OTPs for this account
            $invalidateStmt = $this->db->prepare("DELETE FROM password_reset_otps WHERE account_id = :account_id");
            $invalidateStmt->execute([':account_id' => $account['id']]);

            // Insert new bypass OTP with 15-minute expiry window
            $insertOtp = $this->db->prepare("
                INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
                VALUES (:account_id, :otp_hash, 5, DATE_ADD(NOW(), INTERVAL " . self::LOCKOUT_MINUTES . " MINUTE))
            ");
            $insertOtp->execute([
                ':account_id' => $account['id'],
                ':otp_hash'   => $otpHash
            ]);

            // Determine recipient email (for students, dispatch goes to guardian/parent)
            $recipientEmail = $this->getNotificationEmailForAccount($account) ?: $account['identifier'];
            $profile = $this->getProfileByAccountId((int)$account['id'], $account['role']);
            $fullName = $profile['full_name'] ?? $account['identifier'];
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            // Dispatch Security Alert Email with unlock CTA and OTP
            MailService::sendAccountLockoutAlert(
                $recipientEmail,
                $otp,
                $account['role'],
                $fullName,
                self::LOCKOUT_MINUTES,
                $ipAddress,
                $account['identifier']
            );

            AuditModel::record(
                (int)$account['id'],
                $account['identifier'],
                'ACCOUNT_LOCKED',
                "Account locked for " . self::LOCKOUT_MINUTES . " minutes after {$newCount} consecutive failed attempts. Security alert & bypass OTP dispatched to {$recipientEmail}."
            );
        } else {
            AuditModel::record(
                (int)$account['id'],
                $account['identifier'],
                'LOGIN_FAILED',
                "Invalid password. Failed attempt {$newCount} of " . self::MAX_FAILED_ATTEMPTS . "."
            );
        }

        $stmt = $this->db->prepare("
            UPDATE user_accounts 
            SET failed_login_count = :count,
                lock_expires_at = :lock_expires
            WHERE id = :id
        ");
        $stmt->execute([
            ':count'        => $newCount,
            ':lock_expires' => $lockExpiresAt,
            ':id'           => $account['id']
        ]);
    }

    /**
     * Internal helper: Clears failed attempts and lockouts upon successful login.
     */
    private function handleSuccessfulLogin(int $accountId, ?string $identifier = null, ?string $role = null): void {
        AuditModel::record(
            $accountId,
            $identifier,
            'LOGIN_SUCCESS',
            $role ? "Signed in to " . ucfirst($role) . " portal." : "Signed in successfully."
        );
        $stmt = $this->db->prepare("
            UPDATE user_accounts 
            SET failed_login_count = 0,
                lock_expires_at = NULL,
                last_login_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $accountId]);

        // Increment platform daily activity counters
        try {
            $this->db->exec("
                INSERT INTO daily_stats (stat_date, landing_views, portal_logins) 
                VALUES (CURDATE(), 0, 1) 
                ON DUPLICATE KEY UPDATE portal_logins = portal_logins + 1
            ");
        } catch (\Throwable $e) {
            // Non-critical telemetry logging error should never interrupt authentication
        }
    }

    /**
     * Internal helper: Resets soft lock after timer expiry.
     */
    private function clearSoftLock(int $accountId): void {
        $stmt = $this->db->prepare("
            UPDATE user_accounts 
            SET lock_expires_at = NULL,
                failed_login_count = 0
            WHERE id = :id
        ");
        $stmt->execute([':id' => $accountId]);
    }

    /**
     * Fetch role-specific profile details.
     */
    private function getProfileByAccountId(int $accountId, string $role): array {
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

        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE account_id = :id LIMIT 1");
        $stmt->execute([':id' => $accountId]);
        $row = $stmt->fetch();
        return $row ?: [];
    }

    /**
     * Find notification/reset email for an account.
     * (Students dispatch to parent's email; teachers and management prefer personal email).
     */
    public function getNotificationEmailForAccount(array $account): ?string {
        $role = $account['role'] ?? '';
        $accountId = (int)($account['id'] ?? 0);

        if ($role === 'student') {
            // Find linked parent's personal email through pivot table
            $stmt = $this->db->prepare("
                SELECT p.personal_email 
                FROM student_parents sp 
                JOIN students s ON sp.student_id = s.id 
                JOIN parents p ON sp.parent_id = p.id 
                WHERE s.account_id = :account_id 
                LIMIT 1
            ");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch();
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
        } elseif ($role === 'teacher') {
            $stmt = $this->db->prepare("SELECT personal_email, institutional_email FROM teachers WHERE account_id = :account_id LIMIT 1");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch();
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
            if (!empty($res['institutional_email'])) {
                return $res['institutional_email'];
            }
        } elseif ($role === 'management') {
            $stmt = $this->db->prepare("SELECT personal_email, institutional_email FROM management_profiles WHERE account_id = :account_id LIMIT 1");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch();
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
            if (!empty($res['institutional_email'])) {
                return $res['institutional_email'];
            }
        } elseif ($role === 'parent') {
            $stmt = $this->db->prepare("SELECT personal_email FROM parents WHERE account_id = :account_id LIMIT 1");
            $stmt->execute([':account_id' => $accountId]);
            $res = $stmt->fetch();
            if (!empty($res['personal_email'])) {
                return $res['personal_email'];
            }
        }

        // Fallback to identifier
        return $account['identifier'] ?? null;
    }

    /**
     * Dispatch an activation invite to the user's private email.
     */
    public function sendActivationInviteForAccount(string $identifier): array {
        $identifier = trim($identifier);
        $account = $this->findAccountByIdentifier($identifier);

        if (!$account) {
            return ['success' => false, 'error' => "Account '{$identifier}' not found."];
        }

        if ($account['activation_status'] === 'ACTIVE') {
            return ['success' => false, 'error' => "Account '{$identifier}' is already ACTIVE."];
        }

        $recipientEmail = $this->getNotificationEmailForAccount($account) ?: $account['identifier'];
        $profile = $this->getProfileByAccountId((int)$account['id'], $account['role']);
        $fullName = $profile['full_name'] ?? $account['identifier'];

        $res = MailService::sendActivationInvite($recipientEmail, $account['identifier'], $account['role'], $fullName);

        AuditModel::record((int)$account['id'], $account['identifier'], 'ACTIVATION_INVITE_SENT', "Activation invite sent to {$recipientEmail}.");

        return [
            'success'   => true,
            'message'   => "Activation invite dispatched to {$recipientEmail}.",
            'account'   => $account,
            'recipient' => $recipientEmail,
            'routed_to' => $res['routed_to'] ?? ''
        ];
    }

    /**
     * Break-Glass Admin CLI: Unlocks a locked or suspended user account.
     */
    public function unlockAccount(string $identifier): array {
        $identifier = trim($identifier);
        $account = $this->findAccountByIdentifier($identifier);

        if (!$account) {
            return ['success' => false, 'error' => "Account '{$identifier}' not found."];
        }

        $update = $this->db->prepare("
            UPDATE user_accounts 
            SET failed_login_count = 0,
                lock_expires_at = NULL,
                locked_at = NULL,
                activation_status = IF(activation_status = 'INACTIVE', 'ACTIVE', activation_status)
            WHERE id = :id
        ");
        $update->execute([':id' => $account['id']]);

        AuditModel::record((int)$account['id'], $account['identifier'], 'ADMIN_CLI_UNLOCK', "Account manually unlocked via Break-Glass CLI.");

        return [
            'success'    => true,
            'message'    => "Account {$account['identifier']} (Role: {$account['role']}) has been successfully unlocked!",
            'account'    => $account
        ];
    }

    /**
     * Break-Glass Admin CLI: List all currently locked accounts.
     */
    public function listLockedAccounts(): array {
        $stmt = $this->db->query("
            SELECT id, identifier, role, activation_status, failed_login_count, lock_expires_at, locked_at, last_login_at 
            FROM user_accounts 
            WHERE locked_at IS NOT NULL 
               OR (lock_expires_at IS NOT NULL AND lock_expires_at > NOW())
            ORDER BY id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Break-Glass Admin CLI: Reset account password directly from CLI.
     */
    public function resetPasswordDirect(string $identifier, string $newPassword): array {
        $identifier = trim($identifier);

        $policyCheck = self::validatePasswordPolicy($newPassword);
        if (!$policyCheck['valid']) {
            return ['success' => false, 'error' => $policyCheck['message']];
        }

        $account = $this->findAccountByIdentifier($identifier);

        if (!$account) {
            return ['success' => false, 'error' => "Account '{$identifier}' not found."];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $this->db->prepare("
            UPDATE user_accounts 
            SET password_hash = :hash,
                failed_login_count = 0,
                lock_expires_at = NULL,
                locked_at = NULL,
                first_login_required = 0,
                activation_status = IF(activation_status = 'PENDING', 'ACTIVE', activation_status)
            WHERE id = :id
        ");
        $update->execute([':hash' => $hash, ':id' => $account['id']]);

        AuditModel::record((int)$account['id'], $account['identifier'], 'ADMIN_CLI_PW_RESET', "Password reset directly via Break-Glass CLI.");

        return [
            'success' => true,
            'message' => "Password for {$account['identifier']} ({$account['role']}) updated successfully!"
        ];
    }
}
