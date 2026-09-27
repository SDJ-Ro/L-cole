<?php
/**
 * =========================================================================
 * L'ÉCOLE — USER ACTIONS (MUTATIONS & AUTHENTICATION LIFECYCLE)
 * =========================================================================
 * Dedicated write model handling all state mutations, authentication,
 * progressive lockout, account activation, OTP verification, password changes,
 * and security audit logs.
 *
 * Adheres to the split architectural pattern ({Feature}Model for queries,
 * {Feature}Actions for mutations).
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/MailService.php';
require_once __DIR__ . '/UserModel.php';
require_once __DIR__ . '/AuditModel.php';

class UserActions extends Model {

    /**
     * Soft lockout duration in minutes after 5 consecutive failures.
     */
    private const LOCKOUT_MINUTES = 15;

    /**
     * Consecutive failed attempts threshold before soft lockout.
     */
    private const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Authenticate an account by identifier (email or index number), password, and role.
     *
     * @param string $identifier Email address or Student Index Number
     * @param string $password   Plain-text password to verify
     * @param string $role       Expected role ('admin', 'management', 'teacher', 'parent', 'student')
     * @return array Result array with status, user data, or specific redirect flag
     */
    public static function authenticate(string $identifier, string $password, string $role = ''): array {
        $identifier = trim($identifier);
        $expectedRole = strtolower(trim($role));

        // 1. Fetch account row using canonical finder (handles email, index, normalization, and student links)
        $account = UserModel::findAccountByIdentifier($identifier);

        // 2. Account does not exist
        if (!$account) {
            AuditModel::record(null, $identifier, 'LOGIN_FAILED', "Identifier not found or role mismatch (tried role: {$expectedRole})");
            return [
                'success' => false,
                'error'   => 'You have not registered with us',
                'code'    => 'NOT_REGISTERED'
            ];
        }

        // Strict Role Confinement: Each role must sign in strictly through its own portal
        if ($expectedRole !== '' && strtolower($account['role']) !== $expectedRole) {
            $actualName = ucfirst($account['role']);
            $expectedName = ucfirst($expectedRole);
            AuditModel::record(
                (int)$account['id'],
                $account['identifier'],
                'SECURITY_CROSS_ROLE_LOGIN_BLOCKED',
                "Cross-role sign-in attempt blocked: Account role ({$account['role']}) does not match {$expectedName} portal."
            );
            return [
                'success'     => false,
                'error'       => "Access restricted: This account is registered as {$actualName}. Please sign in via the {$actualName} portal.",
                'code'        => 'ROLE_MISMATCH',
                'actual_role' => $account['role'],
                'correct_url' => '/auth/' . $account['role']
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
                self::clearSoftLock((int)$account['id']);
                $account['failed_login_count'] = 0;
            }
        }

        // 7. Verify password hash using native PHP bcrypt
        if (empty($account['password_hash']) || !password_verify($password, $account['password_hash'])) {
            // Failed attempt: increment failed count and check lockout threshold
            self::handleFailedLogin($account);
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
        self::handleSuccessfulLogin((int)$account['id'], $account['identifier'], $account['role']);

        // 9. Fetch profile data for this role
        $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);

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
    public static function activateAccount(string $identifier, string $password, string $role): array {
        return self::registerOrActivateAccount('', $identifier, $password, $role);
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
    public static function registerOrActivateAccount(string $fullName, string $identifier, string $password, string $role): array {
        $fullName   = trim($fullName);
        $identifier = trim($identifier);
        $role       = strtolower(trim($role));

        // Leadership security guard: Admin & Management can never be self-created online
        if (in_array($role, ['admin', 'management'], true)) {
            return [
                'success' => false,
                'error'   => 'Management and Administrator accounts cannot be self-created online. Leadership workspaces are provisioned by the Board of Directors.'
            ];
        }

        // 1. Password policy validation
        $policyCheck = UserModel::validatePasswordPolicy($password);
        if (!$policyCheck['valid']) {
            return [
                'success' => false,
                'error'   => $policyCheck['message']
            ];
        }

        // 2. Lookup pre-provisioned account using canonical unified finder
        $existing = UserModel::findAccountByIdentifier($identifier);

        // Provisioned-first rule: If no account was pre-provisioned by the school, reject immediately!
        if (!$existing) {
            AuditModel::record(null, $identifier, 'SIGNUP_REJECTED', "Self-signup attempted with unprovisioned identifier: {$identifier} (role: {$role})");
            return [
                'success' => false,
                'error'   => 'You have not registered with us',
                'code'    => 'NOT_REGISTERED'
            ];
        }

        // Strict Role Confinement: If existing account was found but has a different role, prevent cross-role hijacking
        if (strtolower($existing['role']) !== $role) {
            $actualName = ucfirst($existing['role']);
            $expectedName = ucfirst($role);
            AuditModel::record(
                (int)$existing['id'],
                $existing['identifier'],
                'SECURITY_CROSS_ROLE_SIGNUP_BLOCKED',
                "Cross-role sign-up attempt blocked: Account role ({$existing['role']}) does not match {$expectedName} sign-up portal."
            );
            return [
                'success'     => false,
                'error'       => "Access restricted: This account is registered as {$actualName}. Please access via the {$actualName} portal.",
                'code'        => 'ROLE_MISMATCH',
                'actual_role' => $existing['role'],
                'correct_url' => '/auth/' . $existing['role']
            ];
        }

        // Case A: Account already exists and is ACTIVE
        if ($existing['activation_status'] === 'ACTIVE') {
            return [
                'success'        => false,
                'already_active' => true,
                'error'          => 'An account with this email/index number already exists and is active. Please sign in directly.'
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
            // 3. Verify Full Name matches the official database record on file
            $profile = UserModel::getProfileByAccountId((int)$existing['id'], $existing['role']);
            $nameCheck = UserModel::verifyFullNameMatches($fullName, $profile);
            if (!$nameCheck['matches']) {
                return [
                    'success' => false,
                    'error'   => $nameCheck['error']
                ];
            }

            $db = Database::getConnection();

            // 4. Save pending password hash (account stays PENDING until OTP verification)
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $updateStmt = $db->prepare("
                UPDATE user_accounts 
                SET password_hash = :hash,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':hash' => $hash,
                ':id'   => $existing['id']
            ]);

            // Sync name if empty in database profile
            if (!empty($fullName)) {
                self::syncProfileName((int)$existing['id'], $existing['role'], $fullName);
            }

            // 5. Generate secure 6-digit numeric OTP code
            $otp = (string)random_int(100000, 999999);
            $otpHash = password_hash($otp, PASSWORD_BCRYPT);

            // Invalidate any previous unused OTPs for this account
            $invalidateStmt = $db->prepare("DELETE FROM password_reset_otps WHERE account_id = :account_id");
            $invalidateStmt->execute([':account_id' => $existing['id']]);

            // Insert new OTP with 15-minute validity window
            $insertOtp = $db->prepare("
                INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
                VALUES (:account_id, :otp_hash, 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
            ");
            $insertOtp->execute([
                ':account_id' => $existing['id'],
                ':otp_hash'   => $otpHash
            ]);

            // Determine recipient email (for students, code goes to linked parent email)
            $recipientEmail = UserModel::getNotificationEmailForAccount($existing) ?: $existing['identifier'];
            $maskedEmail = UserModel::maskEmail($recipientEmail);

            // 6. Dispatch activation email with OTP
            MailService::sendActivationOtp($recipientEmail, $otp, $role, $fullName ?: ($profile['full_name'] ?? ''));

            AuditModel::record(
                (int)$existing['id'],
                $existing['identifier'],
                'ACTIVATION_OTP_DISPATCHED',
                "Activation code dispatched to {$recipientEmail}"
            );

            return [
                'success'      => true,
                'step'         => 'verify_otp',
                'identifier'   => $existing['identifier'],
                'role'         => $role,
                'masked_email' => $maskedEmail,
                'message'      => "A verification code has been sent to your email ({$maskedEmail}). Please enter the code below to complete account activation."
            ];
        }

        return [
            'success' => false,
            'error'   => 'Unable to activate this account. Please contact the School Office.'
        ];
    }

    /**
     * Step 2 of Sign-Up: Verify 6-digit OTP code, activate account to ACTIVE,
     * record audit trail, dispatch welcome email, and authenticate user session.
     *
     * @param string $identifier Account email or student index
     * @param string $otp        6-digit numeric code entered by user
     * @param string $role       Role: 'student', 'parent', 'teacher'
     * @return array Result array with status, redirect, or error
     */
    public static function verifyActivationOtp(string $identifier, string $otp, string $role): array {
        $identifier = trim($identifier);
        $otp        = trim($otp);
        $role       = strtolower(trim($role));

        if (empty($identifier) || empty($otp)) {
            return ['success' => false, 'error' => 'Please enter the 6-digit verification code.'];
        }

        $account = UserModel::findAccountByIdentifier($identifier);
        if (!$account || strtolower($account['role']) !== $role) {
            return ['success' => false, 'error' => 'Account not found or role mismatch.'];
        }

        if ($account['activation_status'] === 'ACTIVE') {
            return [
                'success'  => true,
                'redirect' => '/' . $role,
                'message'  => 'Your account is already activated. Redirecting to sign in…'
            ];
        }

        if ($account['activation_status'] !== 'PENDING') {
            return ['success' => false, 'error' => 'This account is not eligible for online activation.'];
        }

        $db = Database::getConnection();

        // Fetch latest active OTP for this account
        $stmt = $db->prepare("
            SELECT id, otp_hash, attempts_left, expires_at 
            FROM password_reset_otps 
            WHERE account_id = :account_id 
              AND used_at IS NULL 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([':account_id' => $account['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'success' => false,
                'error'   => 'No active verification code found. Please click "Resend code" to receive a new one.'
            ];
        }

        $now = new DateTime();
        $expires = new DateTime($row['expires_at']);
        if ($now > $expires) {
            return [
                'success' => false,
                'error'   => 'The verification code has expired (15-minute limit). Please click "Resend code" to request a new code.'
            ];
        }

        if ((int)$row['attempts_left'] <= 0) {
            return [
                'success' => false,
                'error'   => 'Too many incorrect attempts. Please request a new verification code.'
            ];
        }

        // Verify OTP code hash
        if (!password_verify($otp, $row['otp_hash'])) {
            $remaining = max(0, (int)$row['attempts_left'] - 1);
            $upd = $db->prepare("UPDATE password_reset_otps SET attempts_left = :att WHERE id = :id");
            $upd->execute([':att' => $remaining, ':id' => $row['id']]);

            AuditModel::record(
                (int)$account['id'],
                $account['identifier'],
                'ACTIVATION_OTP_FAILED',
                "Invalid activation code entered ({$remaining} attempts remaining)"
            );

            if ($remaining <= 0) {
                return [
                    'success' => false,
                    'error'   => 'Too many incorrect code attempts. Please click "Resend code" to receive a new code.'
                ];
            }

            return [
                'success' => false,
                'error'   => "Incorrect verification code. {$remaining} attempt(s) remaining."
            ];
        }

        // OTP Valid! Mark OTP used and activate account atomically
        $db->beginTransaction();
        try {
            // Mark OTP used
            $markStmt = $db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id");
            $markStmt->execute([':id' => $row['id']]);

            // Set account to ACTIVE
            $actStmt = $db->prepare("
                UPDATE user_accounts 
                SET activation_status = 'ACTIVE',
                    activated_at = NOW(),
                    first_login_required = 0,
                    failed_login_count = 0,
                    lock_expires_at = NULL,
                    locked_at = NULL,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $actStmt->execute([':id' => $account['id']]);

            $db->commit();
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'error' => 'Database error while activating account. Please try again.'];
        }

        // Fetch refreshed profile data
        $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);
        $displayName = $profile['full_name'] ?? $account['identifier'];

        AuditModel::record(
            (int)$account['id'],
            $account['identifier'],
            'ACCOUNT_ACTIVATED',
            "Pre-provisioned {$role} account verified and activated via OTP."
        );

        // Send welcome email confirmation
        MailService::sendWelcomeConfirmation($account['identifier'], $account['role'], $displayName);

        // Authenticate into session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'                => (int)$account['id'],
            'identifier'        => $account['identifier'],
            'role'              => $account['role'],
            'activation_status' => 'ACTIVE'
        ];
        $_SESSION['profile']       = $profile;
        $_SESSION['last_activity'] = time();

        $redirectUrl = '/' . $role;
        return [
            'success'  => true,
            'redirect' => $redirectUrl,
            'message'  => "Account successfully activated! Welcome to L'École."
        ];
    }

    /**
     * Resend a fresh 6-digit activation code.
     */
    public static function resendActivationOtp(string $identifier, string $role): array {
        $identifier = trim($identifier);
        $role       = strtolower(trim($role));

        $account = UserModel::findAccountByIdentifier($identifier);
        if (!$account || strtolower($account['role']) !== $role) {
            return ['success' => false, 'error' => 'Account not found.'];
        }

        if ($account['activation_status'] === 'ACTIVE') {
            return ['success' => false, 'error' => 'Account is already active. Please sign in directly.'];
        }

        if ($account['activation_status'] !== 'PENDING') {
            return ['success' => false, 'error' => 'Account is not eligible for activation.'];
        }

        $db = Database::getConnection();

        // Rate limiting: maximum 4 activation codes per 10 minutes
        $rateStmt = $db->prepare("
            SELECT COUNT(*) AS cnt 
            FROM activity_logs 
            WHERE account_id = :id 
              AND action = 'ACTIVATION_OTP_DISPATCHED' 
              AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $rateStmt->execute([':id' => $account['id']]);
        $row = $rateStmt->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['cnt'] >= 4) {
            return [
                'success' => false,
                'error'   => 'Too many code requests. Please check your inbox or wait a few minutes before trying again.'
            ];
        }

        // Generate new code
        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);

        $db->prepare("DELETE FROM password_reset_otps WHERE account_id = :id")->execute([':id' => $account['id']]);

        $insertOtp = $db->prepare("
            INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
            VALUES (:account_id, :otp_hash, 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
        ");
        $insertOtp->execute([
            ':account_id' => $account['id'],
            ':otp_hash'   => $otpHash
        ]);

        $recipientEmail = UserModel::getNotificationEmailForAccount($account) ?: $account['identifier'];
        $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);
        $displayName = $profile['full_name'] ?? $account['identifier'];

        MailService::sendActivationOtp($recipientEmail, $otp, $role, $displayName);

        AuditModel::record(
            (int)$account['id'],
            $account['identifier'],
            'ACTIVATION_OTP_DISPATCHED',
            "Resent activation OTP code to {$recipientEmail}"
        );

        $maskedEmail = UserModel::maskEmail($recipientEmail);
        return [
            'success'      => true,
            'masked_email' => $maskedEmail,
            'message'      => "A fresh verification code has been dispatched to {$maskedEmail}."
        ];
    }

    /**
     * Synchronize profile full_name, first_name, and last_name if empty.
     */
    private static function syncProfileName(int $accountId, string $role, string $fullName): void {
        $db = Database::getConnection();
        $parts = explode(' ', trim($fullName), 2);
        $firstName = $parts[0] ?? $fullName;
        $lastName = $parts[1] ?? '';

        if ($role === 'student') {
            $stmt = $db->prepare("UPDATE students SET full_name = :fn, first_name = :first, last_name = :last WHERE account_id = :id AND (full_name IS NULL OR full_name = '')");
            $stmt->execute([':fn' => $fullName, ':first' => $firstName, ':last' => $lastName, ':id' => $accountId]);
        } elseif ($role === 'parent') {
            $stmt = $db->prepare("UPDATE parents SET full_name = :fn, first_name = :first, last_name = :last WHERE account_id = :id AND (full_name IS NULL OR full_name = '')");
            $stmt->execute([':fn' => $fullName, ':first' => $firstName, ':last' => $lastName, ':id' => $accountId]);
        } elseif ($role === 'teacher') {
            $stmt = $db->prepare("UPDATE teachers SET full_name = :fn, first_name = :first, last_name = :last WHERE account_id = :id AND (full_name IS NULL OR full_name = '')");
            $stmt->execute([':fn' => $fullName, ':first' => $firstName, ':last' => $lastName, ':id' => $accountId]);
        }
    }

    /**
     * Forces a password update (e.g. Student first login change).
     */
    public static function forcePasswordChange(int $accountId, string $newPassword): array {
        $policyCheck = UserModel::validatePasswordPolicy($newPassword);
        if (!$policyCheck['valid']) {
            return [
                'success' => false,
                'error'   => $policyCheck['message']
            ];
        }

        $db = Database::getConnection();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("
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
    public static function requestPasswordReset(string $identifier, string $expectedRole = ''): array {
        $identifier = trim($identifier);
        $expectedRole = strtolower(trim($expectedRole));

        // 1. Find account using canonical unified finder
        $account = UserModel::findAccountByIdentifier($identifier);

        // 2. Account check: Return error banner if not registered
        if (!$account) {
            AuditModel::record(null, $identifier, 'PASSWORD_RESET_FAILED', "Identifier not found in registry (tried role: {$expectedRole})");
            return [
                'success' => false,
                'error'   => 'You have not registered with us',
                'code'    => 'NOT_REGISTERED'
            ];
        }

        // Strict Role Confinement: Prevent cross-role password reset requests
        if ($expectedRole !== '' && strtolower($account['role']) !== $expectedRole) {
            $actualName = ucfirst($account['role']);
            return [
                'success'     => false,
                'error'       => "Access restricted: This account is registered under the {$actualName} portal. Please reset your password from the {$actualName} sign-in page.",
                'code'        => 'ROLE_MISMATCH',
                'actual_role' => $account['role'],
                'correct_url' => '/auth/' . $account['role']
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

        $db = Database::getConnection();

        // Rate Limiting: Cap OTP requests to maximum 3 requests per 10 minutes per account
        $rateStmt = $db->prepare("
            SELECT COUNT(*) AS req_count 
            FROM activity_logs 
            WHERE account_id = :account_id 
              AND action = 'PASSWORD_RESET_REQUESTED' 
              AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $rateStmt->execute([':account_id' => $account['id']]);
        $rateRow = $rateStmt->fetch(PDO::FETCH_ASSOC);
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
        $invalidateStmt = $db->prepare("
            DELETE FROM password_reset_otps WHERE account_id = :account_id
        ");
        $invalidateStmt->execute([':account_id' => $account['id']]);

        // Insert new OTP with 10-minute expiry window
        $insertOtp = $db->prepare("
            INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
            VALUES (:account_id, :otp_hash, 5, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ");
        $insertOtp->execute([
            ':account_id' => $account['id'],
            ':otp_hash'   => $otpHash
        ]);

        // Determine destination email (for students, dispatch goes to guardian/parent)
        $recipientEmail = UserModel::getNotificationEmailForAccount($account) ?: $account['identifier'];

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
    public static function authenticateOrResetWithOtp(string $identifier, string $otp, string $password, bool $isReset = false, string $expectedRole = ''): array {
        $identifier = trim($identifier);
        $otp        = trim($otp);
        $password   = trim($password);
        $expectedRole = strtolower(trim($expectedRole));

        if (empty($identifier) || empty($otp) || empty($password)) {
            return [
                'success' => false,
                'error'   => 'Please provide both your verification code and password.'
            ];
        }

        // 1. Find account using canonical unified finder
        $account = UserModel::findAccountByIdentifier($identifier);

        if (!$account) {
            return [
                'success' => false,
                'error'   => 'Invalid or expired verification code.'
            ];
        }

        // Strict Role Confinement: Prevent cross-role OTP signin / reset
        if ($expectedRole !== '' && strtolower($account['role']) !== $expectedRole) {
            $actualName = ucfirst($account['role']);
            return [
                'success'     => false,
                'error'       => "Access restricted: This account is registered under the {$actualName} portal. Please sign in via the {$actualName} portal.",
                'code'        => 'ROLE_MISMATCH',
                'actual_role' => $account['role'],
                'correct_url' => '/auth/' . $account['role']
            ];
        }

        if (!empty($account['locked_at'])) {
            return [
                'success' => false,
                'error'   => 'This account has been administratively locked for security. Please contact admin@lecole.edu directly.'
            ];
        }

        $db = Database::getConnection();

        // 2. Find active unexpired OTP
        $otpStmt = $db->prepare("
            SELECT * FROM password_reset_otps 
            WHERE account_id = :account_id 
              AND used_at IS NULL 
              AND expires_at > NOW() 
            ORDER BY id DESC LIMIT 1
        ");
        $otpStmt->execute([':account_id' => $account['id']]);
        $activeOtp = $otpStmt->fetch(PDO::FETCH_ASSOC);

        if (!$activeOtp || $activeOtp['attempts_left'] <= 0) {
            return [
                'success' => false,
                'error'   => 'Verification code has expired or maximum attempts exceeded. Please request a new one.'
            ];
        }

        // 3. Verify OTP code
        if (!password_verify($otp, $activeOtp['otp_hash'])) {
            $decrement = $db->prepare("
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
            $policyCheck = UserModel::validatePasswordPolicy($password);
            if (!$policyCheck['valid']) {
                return [
                    'success' => false,
                    'error'   => $policyCheck['message']
                ];
            }

            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $db->beginTransaction();
            try {
                $markUsed = $db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id");
                $markUsed->execute([':id' => $activeOtp['id']]);

                $updatePass = $db->prepare("
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

                $db->commit();

                AuditModel::record((int)$account['id'], $account['identifier'], 'PASSWORD_RESET_COMPLETED', 'Password reset & account unlocked with verified 6-digit OTP.');
                self::handleSuccessfulLogin((int)$account['id'], $account['identifier'], $account['role']);
                $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);

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
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
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
            $db->beginTransaction();
            try {
                $markUsed = $db->prepare("UPDATE password_reset_otps SET used_at = NOW() WHERE id = :id");
                $markUsed->execute([':id' => $activeOtp['id']]);

                $clearLock = $db->prepare("
                    UPDATE user_accounts 
                    SET failed_login_count = 0,
                        lock_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id AND locked_at IS NULL
                ");
                $clearLock->execute([':id' => $account['id']]);

                $db->commit();

                AuditModel::record((int)$account['id'], $account['identifier'], 'ACCOUNT_UNLOCKED_LOGIN', 'Account unlocked and signed in with verified 6-digit OTP & password.');
                self::handleSuccessfulLogin((int)$account['id'], $account['identifier'], $account['role']);
                $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);

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
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                return ['success' => false, 'error' => 'An unexpected error occurred while unlocking your account.'];
            }
        }
    }

    /**
     * Internal helper: Handles failed login attempt and progressive lockout escalation.
     */
    private static function handleFailedLogin(array $account): void {
        $db = Database::getConnection();
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
            $invalidateStmt = $db->prepare("DELETE FROM password_reset_otps WHERE account_id = :account_id");
            $invalidateStmt->execute([':account_id' => $account['id']]);

            // Insert new bypass OTP with 15-minute expiry window
            $insertOtp = $db->prepare("
                INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
                VALUES (:account_id, :otp_hash, 5, DATE_ADD(NOW(), INTERVAL " . self::LOCKOUT_MINUTES . " MINUTE))
            ");
            $insertOtp->execute([
                ':account_id' => $account['id'],
                ':otp_hash'   => $otpHash
            ]);

            // Determine recipient email (for students, dispatch goes to guardian/parent)
            $recipientEmail = UserModel::getNotificationEmailForAccount($account) ?: $account['identifier'];
            $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);
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

        $stmt = $db->prepare("
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
    private static function handleSuccessfulLogin(int $accountId, ?string $identifier = null, ?string $role = null): void {
        $db = Database::getConnection();
        AuditModel::record(
            $accountId,
            $identifier,
            'LOGIN_SUCCESS',
            $role ? "Signed in to " . ucfirst($role) . " portal." : "Signed in successfully."
        );
        $stmt = $db->prepare("
            UPDATE user_accounts 
            SET failed_login_count = 0,
                lock_expires_at = NULL,
                last_login_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $accountId]);

        // Increment platform daily activity counters
        try {
            $db->exec("
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
    private static function clearSoftLock(int $accountId): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE user_accounts 
            SET lock_expires_at = NULL,
                failed_login_count = 0
            WHERE id = :id
        ");
        $stmt->execute([':id' => $accountId]);
    }

    /**
     * Dispatch an activation invite to the user's private email.
     */
    public static function sendActivationInviteForAccount(string $identifier): array {
        $identifier = trim($identifier);
        $account = UserModel::findAccountByIdentifier($identifier);

        if (!$account) {
            return ['success' => false, 'error' => "Account '{$identifier}' not found."];
        }

        if ($account['activation_status'] === 'ACTIVE') {
            return ['success' => false, 'error' => "Account '{$identifier}' is already ACTIVE."];
        }

        $recipientEmail = UserModel::getNotificationEmailForAccount($account) ?: $account['identifier'];
        $profile = UserModel::getProfileByAccountId((int)$account['id'], $account['role']);
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
    public static function unlockAccount(string $identifier): array {
        $identifier = trim($identifier);
        $account = UserModel::findAccountByIdentifier($identifier);

        if (!$account) {
            return ['success' => false, 'error' => "Account '{$identifier}' not found."];
        }

        $db = Database::getConnection();
        $update = $db->prepare("
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
     * Break-Glass Admin CLI: Reset account password directly from CLI.
     */
    public static function resetPasswordDirect(string $identifier, string $newPassword): array {
        $identifier = trim($identifier);

        $policyCheck = UserModel::validatePasswordPolicy($newPassword);
        if (!$policyCheck['valid']) {
            return ['success' => false, 'error' => $policyCheck['message']];
        }

        $account = UserModel::findAccountByIdentifier($identifier);

        if (!$account) {
            return ['success' => false, 'error' => "Account '{$identifier}' not found."];
        }

        $db = Database::getConnection();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $db->prepare("
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
