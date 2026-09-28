<?php
/**
 * =========================================================================
 * L'ÉCOLE — MAIL SERVICE & "BOOK COVER" ROUTER
 * =========================================================================
 * Displays branded @lecole.edu addresses on the UI, but routes all
 * live emails and OTP codes directly to lecoletesting@gmail.com.
 * =========================================================================
 */

class MailService {

    private static ?array $config = null;

    private static function getConfig(): array {
        if (self::$config === null) {
            $configFile = dirname(__DIR__) . '/config/mail.php';
            self::$config = file_exists($configFile) ? require $configFile : [];
        }
        return self::$config;
    }

    /**
     * Dispatch an email using the Book Cover routing architecture.
     *
     * @param string $toBookCover Target display email (e.g. alex_benjamin@lecole.edu)
     * @param string $subject     Subject line
     * @param string $bodyHtml    Main HTML content
     * @param array  $context     Optional metadata (role, name, etc.)
     * @return array [success => bool, routed_to => string, message => string]
     */
    public static function send(string $toBookCover, string $subject, string $bodyHtml, array $context = []): array {
        $config = self::getConfig();
        $baseRecipient = $config['live_testing_inbox'] ?? 'projectdrawio986@gmail.com';
        $fromEmail = $config['from_email'] ?? 'noreply@lecole.edu';
        $fromName  = $config['from_name'] ?? "L'École International School";

        $role = ucfirst($context['role'] ?? 'User');
        $roleTag = strtolower($context['role'] ?? '');
        $name = $context['name'] ?? $toBookCover;

        // Clean direct recipient for maximum inbox delivery
        $realRecipient = $baseRecipient;

        // Distinct Subject Prefix for visual clarity in Gmail inbox
        if (!empty($roleTag) && strpos($subject, '[') !== 0) {
            $subject = '[' . strtoupper($roleTag) . '] ' . $subject;
        }

        // Build branded email with clean, professional institutional layout (no debug boxes)
        $fullHtml = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #fdfaf6; margin: 0; padding: 32px 16px; color: #0f414a; }
  .mail-card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid rgba(15,65,74,0.1); overflow: hidden; box-shadow: 0 4px 20px rgba(15,65,74,0.06); }
  .mail-header { background: #0f414a; padding: 28px 36px; color: #ffffff; }
  .mail-brand { font-size: 22px; font-weight: 700; letter-spacing: -0.01em; margin: 0; color: #ffffff; }
  .mail-sub { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: #7fc7cc; margin-top: 5px; margin-bottom: 0; font-weight: 600; }
  .mail-body { padding: 32px 36px; font-size: 15px; line-height: 1.65; color: #233438; }
  .mail-footer { background: #fbf8f3; padding: 22px 36px; font-size: 11.5px; color: rgba(15,65,74,0.65); border-top: 1px solid rgba(15,65,74,0.08); text-align: center; line-height: 1.6; }
  .btn-primary { display: inline-block; background: #0f414a; color: #ffffff !important; text-decoration: none; padding: 13px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; letter-spacing: 0.02em; margin: 18px 0; text-align: center; }
  .code-box { background: #f4ece1; padding: 20px; border-radius: 12px; text-align: center; margin: 24px 0; border: 1px solid rgba(15,65,74,0.08); }
  .code-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.18em; color: rgba(15,65,74,0.7); display: block; margin-bottom: 6px; }
  .code-value { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 32px; font-weight: 800; letter-spacing: 0.25em; color: #0f414a; }
  .security-notice { background: #fff8f5; border-left: 4px solid #d85a38; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 22px 0; font-size: 13.5px; color: #5c2c20; line-height: 1.55; }
  .meta-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
  .meta-table td { padding: 6px 0; color: rgba(15,65,74,0.8); }
  .meta-table td.label { font-weight: 600; width: 130px; color: #0f414a; }
</style>
</head>
<body>
  <div class="mail-card">
    <div class="mail-header">
      <h1 class="mail-brand">L\'École</h1>
      <p class="mail-sub">Official Academic Notification</p>
    </div>
    <div class="mail-body">
      ' . $bodyHtml . '
    </div>
    <div class="mail-footer">
      &copy; ' . date('Y') . ' L\'École International School. All rights reserved.<br>
      For school office inquiries: <a href="mailto:office@lecole.edu" style="color:#0f414a;font-weight:600;text-decoration:underline;">office@lecole.edu</a> &bull; Security & IT: <a href="mailto:admin@lecole.edu" style="color:#0f414a;font-weight:600;text-decoration:underline;">admin@lecole.edu</a>
    </div>
  </div>
</body>
</html>';

        // Audit log
        self::logDispatch($toBookCover, $realRecipient, $subject, $context);

        // Attempt live SMTP delivery if Google App Password is provided
        $smtpPass = $config['smtp']['password'] ?? '';
        $smtpSent = false;
        $errorMsg = '';

        if (!empty($smtpPass)) {
            $result = self::sendViaSmtp($realRecipient, $subject, $fullHtml, $config['smtp'], $fromEmail, $fromName);
            $smtpSent = $result['success'];
            $errorMsg = $result['error'] ?? '';
        }

        return [
            'success'   => true,
            'routed_to' => $realRecipient,
            'book_cover'=> $toBookCover,
            'smtp_live' => $smtpSent,
            'error'     => $errorMsg,
            'notice'    => $smtpSent 
                ? "Live email sent to {$realRecipient}" 
                : "Routed to {$realRecipient} (Logged in storage/logs/mail.log)"
        ];
    }

    /**
     * Helper to send password reset OTP
     */
    public static function sendPasswordResetOtp(string $toBookCover, string $otp, string $role = '', string $name = ''): array {
        $subject = "L'École — Your Password Reset Code: {$otp}";
        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:18px;">Password Reset Request</h2>
          <p>Hello ' . htmlspecialchars($name ?: 'there') . ',</p>
          <p>We received a request to reset your password for your L\'École <strong>' . htmlspecialchars(ucfirst($role ?: 'portal')) . '</strong> account (' . htmlspecialchars($toBookCover) . ').</p>
          <div class="code-box">
            <span class="code-label">Your 6-Digit Verification Code</span>
            <span class="code-value">' . htmlspecialchars($otp) . '</span>
          </div>
          <p style="font-size:13px;color:rgba(15,65,74,0.7);">This code expires in <strong>10 minutes</strong>. If you did not initiate this request, you can safely ignore this message.</p>
        ';

        return self::send($toBookCover, $subject, $body, ['role' => $role, 'name' => $name, 'otp' => $otp]);
    }

    /**
     * Dispatch 6-digit Activation OTP code during account signup / password setup.
     */
    public static function sendActivationOtp(string $recipientEmail, string $otp, string $role = '', string $name = ''): array {
        $subject = "Your L'École Portal Activation Code: {$otp}";
        $roleTitle = ucfirst($role ?: 'Portal');
        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:20px;">Activate Your L\'École ' . htmlspecialchars($roleTitle) . ' Account</h2>
          <p>Hello ' . htmlspecialchars($name ?: 'there') . ',</p>
          <p>Thank you for setting up your permanent password. To verify your identity and finalize your account activation, please enter the 6-digit verification code below on the signup page:</p>
          <div class="code-box">
            <span class="code-label">Account Verification Code</span>
            <span class="code-value">' . htmlspecialchars($otp) . '</span>
          </div>
          <p style="font-size:13px;color:rgba(15,65,74,0.7);">
            This verification code is valid for <strong>15 minutes</strong>. Once verified, your account will be activated and you will be able to access your dashboard.
          </p>
        ';

        return self::send($recipientEmail, $subject, $body, ['role' => $role, 'name' => $name, 'otp' => $otp]);
    }

    /**
     * Security Lockout Alert dispatched when an account hits 5 failed attempts.
     * Contains single-use 6-digit bypass OTP and [ Unlock & Reset Password Now ] button.
     */
    public static function sendAccountLockoutAlert(
        string $toBookCover,
        string $otp,
        string $role = '',
        string $name = '',
        int $lockMinutes = 15,
        string $ipAddress = '',
        string $accountIdentifier = ''
    ): array {
        $subject = "Security Alert: Account Temporarily Locked (Unlock Code Inside)";
        $roleTitle = ucfirst($role ?: 'portal');
        $canonicalIdentifier = !empty($accountIdentifier) ? $accountIdentifier : $toBookCover;
        $unlockUrl = 'http://localhost:8040/auth/' . strtolower($role ?: 'student') . '?unlock=1&identifier=' . urlencode($canonicalIdentifier);

        $body = '
          <h2 style="margin-top:0;color:#9e2a2b;font-size:19px;">Security Alert: Temporary Account Lockout</h2>
          <p>Hello ' . htmlspecialchars($name ?: 'there') . ',</p>
          <p>Your L\'École <strong>' . htmlspecialchars($roleTitle) . '</strong> account (<code>' . htmlspecialchars($canonicalIdentifier) . '</code>) was temporarily locked after <strong>5 consecutive failed sign-in attempts</strong>.</p>
          
          <table class="meta-table">
            <tr><td class="label">Target Account:</td><td>' . htmlspecialchars($canonicalIdentifier) . ' (' . htmlspecialchars($roleTitle) . ')</td></tr>
            <tr><td class="label">Lock Duration:</td><td>' . $lockMinutes . ' Minutes</td></tr>
            <tr><td class="label">Time of Incident:</td><td>' . date('Y-m-d H:i:s T') . '</td></tr>
            ' . (!empty($ipAddress) ? '<tr><td class="label">IP Address:</td><td>' . htmlspecialchars($ipAddress) . '</td></tr>' : '') . '
          </table>

          <div style="background:#f4fbfb;border:1px solid rgba(15,65,74,0.15);border-radius:12px;padding:20px;margin:22px 0;">
            <h3 style="margin-top:0;font-size:15px;color:#0f414a;">Immediate Account Unlock</h3>
            <p style="margin:0 0 14px 0;font-size:13.5px;color:#233438;">
              If this was you, you <strong>do not have to wait ' . $lockMinutes . ' minutes</strong>. Enter the 6-digit unlock code below to immediately unlock your account and regain access:
            </p>
            <div class="code-box" style="margin:14px 0 18px 0;">
              <span class="code-label">One-Time Unlock Code</span>
              <span class="code-value">' . htmlspecialchars($otp) . '</span>
            </div>
            <div style="text-align:center;">
              <a href="' . htmlspecialchars($unlockUrl) . '" class="btn-primary" style="background:#0f414a;color:#ffffff;display:inline-block;padding:12px 26px;border-radius:8px;font-weight:700;text-decoration:none;">
                Unlock Account Now &rarr;
              </a>
            </div>
            <p style="margin:12px 0 0 0;font-size:12px;color:rgba(15,65,74,0.7);text-align:center;">
              (You can keep your current password, or set a new password during unlock if you forgot it).
            </p>
          </div>

          <div class="security-notice">
            <strong>Did not attempt to sign in?</strong><br>
            Someone may be trying to access your account. Please notify the Administrator at <a href="mailto:admin@lecole.edu" style="color:#9e2a2b;font-weight:700;text-decoration:underline;">admin@lecole.edu</a> immediately.
          </div>
        ';

        return self::send($toBookCover, $subject, $body, [
            'role' => $role,
            'name' => $name,
            'otp'  => $otp
        ]);
    }

    /**
     * Onboarding / Activation Invite dispatched to the user's private email.
     */
    public static function sendActivationInvite(
        string $toPrivateEmail,
        string $institutionalId,
        string $role = '',
        string $name = ''
    ): array {
        $subject = "Action Required: Set Up Your L'École " . ucfirst($role) . " Account";
        $roleTitle = ucfirst($role ?: 'portal');
        $activateUrl = 'http://localhost:8040/auth/' . strtolower($role ?: 'student') . 'Signup?identifier=' . urlencode($institutionalId) . '&name=' . urlencode($name);

        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:19px;">Welcome to L\'École</h2>
          <p>Hello ' . htmlspecialchars($name ?: 'there') . ',</p>
          <p>Your institutional <strong>' . htmlspecialchars($roleTitle) . '</strong> workspace has been provisioned on the L\'École School Platform.</p>
          
          <table class="meta-table">
            <tr><td class="label">Institutional ID:</td><td><strong>' . htmlspecialchars($institutionalId) . '</strong></td></tr>
            <tr><td class="label">Assigned Role:</td><td>' . htmlspecialchars($roleTitle) . '</td></tr>
            <tr><td class="label">Private Email:</td><td>' . htmlspecialchars($toPrivateEmail) . '</td></tr>
          </table>

          <p>Please complete your account activation by setting a permanent, secure password:</p>

          <div style="text-align:center;margin:24px 0;">
            <a href="' . htmlspecialchars($activateUrl) . '" class="btn-primary" style="background:#0f414a;color:#ffffff;display:inline-block;padding:13px 28px;border-radius:8px;font-weight:700;text-decoration:none;">
              Set Password & Activate Account &rarr;
            </a>
          </div>

          <p style="font-size:12.5px;color:rgba(15,65,74,0.7);line-height:1.5;">
            If you have questions regarding your enrolment or staff credentials, please contact the School Office at <a href="mailto:office@lecole.edu" style="color:#0f414a;font-weight:600;">office@lecole.edu</a>.
          </p>
        ';

        return self::send($toPrivateEmail, $subject, $body, [
            'role' => $role,
            'name' => $name
        ]);
    }

    /**
     * Teacher Onboarding: Dispatched to the teacher's personal email when registered by Admin or Management.
     * Contains official Staff Index Number, institutional email, assigned subjects, and sign-up instructions.
     */
    public static function sendTeacherOnboarding(
        string $toPersonalEmail,
        string $name,
        string $staffId,
        string $instEmail,
        string $subjects = ''
    ): array {
        $subject = "Welcome to L'École — Teacher Appointment & Portal Credentials (" . $staffId . ")";
        $signupUrl = 'http://localhost:8040/auth/teacherSignup?identifier=' . urlencode($instEmail) . '&name=' . urlencode($name);

        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:20px;">Official Faculty Appointment Confirmed</h2>
          <p>Dear ' . htmlspecialchars($name) . ',</p>
          <p>We are pleased to inform you that your teacher profile and academic information have been <strong>successfully registered in the L\'École School Database</strong>. Below are your official faculty credentials, assigned school email, and portal activation instructions.</p>

          <!-- Teacher Credentials Card -->
          <div style="background:#eef6f6;padding:20px;border-radius:12px;margin:20px 0;border-left:4px solid #207c82;">
            <h3 style="margin:0 0 12px 0;color:#0f414a;font-size:14px;text-transform:uppercase;letter-spacing:0.06em;">Official Faculty Credentials</h3>
            <table class="meta-table" style="margin:0;width:100%;">
              <tr><td class="label" style="width:160px;font-weight:600;color:#0f414a;padding:5px 0;">Teacher Full Name:</td><td style="padding:5px 0;"><strong style="font-size:15px;color:#0f414a;">' . htmlspecialchars($name) . '</strong> <span style="font-size:11px;color:#207c82;">(As registered with school)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">Staff Index Number:</td><td style="padding:5px 0;"><strong style="font-size:15px;color:#0f414a;letter-spacing:0.04em;background:#e0f0f1;padding:2px 8px;border-radius:4px;">' . htmlspecialchars($staffId) . '</strong></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">School Email:</td><td style="padding:5px 0;"><code style="font-size:14px;font-weight:700;color:#207c82;">' . htmlspecialchars($instEmail) . '</code> <span style="font-size:11px;color:rgba(15,65,74,0.65);">(Institutional Login)</span></td></tr>
              ' . (!empty($subjects) ? '<tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">Assigned Subjects:</td><td style="padding:5px 0;">' . htmlspecialchars($subjects) . '</td></tr>' : '') . '
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">Personal Email:</td><td style="padding:5px 0;">' . htmlspecialchars($toPersonalEmail) . '</td></tr>
            </table>
          </div>

          <!-- Sign-Up Instructions -->
          <div style="background:#fdfaf6;padding:18px 20px;border-radius:12px;margin:20px 0;border:1px solid rgba(15,65,74,0.12);">
            <h4 style="margin:0 0 8px 0;color:#0f414a;font-size:14px;">Next Steps — Account Activation &amp; Password Setup</h4>
            <p style="margin:0 0 10px 0;font-size:13.5px;color:#233438;line-height:1.55;">
              To activate your Teacher Workspace, please click the button below to set your permanent password.
            </p>
            <div style="background:#ffffff;border:1px solid rgba(32,124,130,0.25);border-radius:8px;padding:12px 14px;margin:12px 0;font-size:13px;line-height:1.5;color:#0f414a;">
              <strong>Important Registration Details to enter on the signup page:</strong><br>
              &bull; <strong>Full Name:</strong> ' . htmlspecialchars($name) . '<br>
              &bull; <strong>School Email:</strong> ' . htmlspecialchars($instEmail) . ' <em>(or Staff ID: ' . htmlspecialchars($staffId) . ')</em>
            </div>
            <div style="text-align:center;margin:18px 0 12px 0;">
              <a href="' . htmlspecialchars($signupUrl) . '" class="btn-primary" style="background:#0f414a;color:#ffffff;display:inline-block;padding:12px 26px;border-radius:8px;font-weight:700;text-decoration:none;">
                Set Password &amp; Activate Teacher Account &rarr;
              </a>
            </div>
            <p style="margin:8px 0 0 0;font-size:12px;color:rgba(15,65,74,0.7);text-align:center;">
              Direct Portal Sign-In: <a href="http://localhost:8040/auth/teacher" style="color:#207c82;font-weight:600;">http://localhost:8040/auth/teacher</a>
            </p>
          </div>

          <p style="font-size:12.5px;color:rgba(15,65,74,0.7);line-height:1.5;">
            If you need assistance or believe this record was created in error, please contact the School Administration at <a href="mailto:office@lecole.edu" style="color:#0f414a;font-weight:600;">office@lecole.edu</a>.
          </p>
        ';

        return self::send($toPersonalEmail, $subject, $body, [
            'role'       => 'teacher',
            'name'       => $name,
            'staff_id'   => $staffId,
            'inst_email' => $instEmail
        ]);
    }

    /**
     * Management Onboarding: Dispatched to the management member's personal email when registered by Admin.
     * Contains official Staff Index Number, institutional email, executive title, and sign-up instructions.
     */
    public static function sendManagementOnboarding(
        string $toPersonalEmail,
        string $name,
        string $staffId,
        string $instEmail,
        string $title = 'Management Member'
    ): array {
        $subject = "Welcome to L'École — Management Appointment & Portal Credentials (" . $staffId . ")";
        $signupUrl = 'http://localhost:8040/auth/managementSignup?identifier=' . urlencode($instEmail) . '&name=' . urlencode($name);

        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:20px;">Official Management Appointment Confirmed</h2>
          <p>Dear ' . htmlspecialchars($name) . ',</p>
          <p>We are pleased to inform you that your executive leadership profile and administrative credentials have been <strong>successfully registered in the L\'École School Database</strong>. Below are your official staff credentials, institutional school email, and portal activation instructions.</p>

          <!-- Management Credentials Card -->
          <div style="background:#f4ece1;padding:20px;border-radius:12px;margin:20px 0;border-left:4px solid #af5031;">
            <h3 style="margin:0 0 12px 0;color:#af5031;font-size:14px;text-transform:uppercase;letter-spacing:0.06em;">Official Leadership Credentials</h3>
            <table class="meta-table" style="margin:0;width:100%;">
              <tr><td class="label" style="width:160px;font-weight:600;color:#0f414a;padding:5px 0;">Official Full Name:</td><td style="padding:5px 0;"><strong style="font-size:15px;color:#0f414a;">' . htmlspecialchars($name) . '</strong> <span style="font-size:11px;color:#af5031;">(As registered with school)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">Staff Index Number:</td><td style="padding:5px 0;"><strong style="font-size:15px;color:#af5031;letter-spacing:0.04em;background:#f8ede7;padding:2px 8px;border-radius:4px;">' . htmlspecialchars($staffId) . '</strong></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">Executive Designation:</td><td style="padding:5px 0;"><strong>' . htmlspecialchars($title) . '</strong></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">School Email:</td><td style="padding:5px 0;"><code style="font-size:14px;font-weight:700;color:#af5031;">' . htmlspecialchars($instEmail) . '</code> <span style="font-size:11px;color:rgba(15,65,74,0.65);">(Institutional Login)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;padding:5px 0;">Personal Email:</td><td style="padding:5px 0;">' . htmlspecialchars($toPersonalEmail) . '</td></tr>
            </table>
          </div>

          <!-- Sign-Up Instructions -->
          <div style="background:#fdfaf6;padding:18px 20px;border-radius:12px;margin:20px 0;border:1px solid rgba(15,65,74,0.12);">
            <h4 style="margin:0 0 8px 0;color:#0f414a;font-size:14px;">Next Steps — Account Activation &amp; Password Setup</h4>
            <p style="margin:0 0 10px 0;font-size:13.5px;color:#233438;line-height:1.55;">
              To activate your Management Workspace, click the button below to configure your permanent password.
            </p>
            <div style="background:#ffffff;border:1px solid rgba(175,80,49,0.25);border-radius:8px;padding:12px 14px;margin:12px 0;font-size:13px;line-height:1.5;color:#0f414a;">
              <strong>Important Registration Details to enter on the signup page:</strong><br>
              &bull; <strong>Full Name:</strong> ' . htmlspecialchars($name) . '<br>
              &bull; <strong>School Email:</strong> ' . htmlspecialchars($instEmail) . ' <em>(or Staff ID: ' . htmlspecialchars($staffId) . ')</em>
            </div>
            <div style="text-align:center;margin:18px 0 12px 0;">
              <a href="' . htmlspecialchars($signupUrl) . '" class="btn-primary" style="background:#af5031;color:#ffffff;display:inline-block;padding:12px 26px;border-radius:8px;font-weight:700;text-decoration:none;">
                Set Password &amp; Activate Management Account &rarr;
              </a>
            </div>
            <p style="margin:8px 0 0 0;font-size:12px;color:rgba(15,65,74,0.7);text-align:center;">
              Direct Portal Sign-In: <a href="http://localhost:8040/auth/management" style="color:#af5031;font-weight:600;">http://localhost:8040/auth/management</a>
            </p>
          </div>

          <p style="font-size:12.5px;color:rgba(15,65,74,0.7);line-height:1.5;">
            All leadership account operations are strictly audited. For platform assistance, contact <a href="mailto:admin@lecole.edu" style="color:#0f414a;font-weight:600;">admin@lecole.edu</a>.
          </p>
        ';

        return self::send($toPersonalEmail, $subject, $body, [
            'role'       => 'management',
            'name'       => $name,
            'staff_id'   => $staffId,
            'inst_email' => $instEmail
        ]);
    }

    /**
     * Case 1: Consolidated Admission Email (New Student + New Guardian)
     * Dispatches one email to the guardian containing instructions and credentials for both parties:
     * - Parent: Username = Email, instructions to activate and set password.
     * - Student: Username = Student Registration / Index Number, grade/class placement, and portal sign-in guide.
     */
    public static function sendNewAdmissionWithGuardian(
        string $parentEmail,
        string $parentName,
        array $studentData
    ): array {
        $subject = "Welcome to L'École — Admission Confirmed & Portal Credentials (" . $studentData['index'] . ")";
        $parentSignupUrl = 'http://localhost:8040/auth/parentSignup?identifier=' . urlencode($parentEmail) . '&name=' . urlencode($parentName);
        $studentSignupUrl = 'http://localhost:8040/auth/studentSignup?identifier=' . urlencode($studentData['index']) . '&name=' . urlencode($studentData['name']);

        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:20px;">Welcome to L\'École International School</h2>
          <p>Dear ' . htmlspecialchars($parentName) . ',</p>
          <p>Congratulations! Information for your child <strong>' . htmlspecialchars($studentData['name']) . '</strong> has been <strong>successfully registered in the L\'École School Database with official Index Number ' . htmlspecialchars($studentData['index']) . '</strong>. Below are the registered details and portal activation instructions for both your parent workspace and your child\'s student workspace.</p>
          
          <!-- Parent Credentials Section -->
          <div style="background:#f4ece1;padding:18px 20px;border-radius:12px;margin:20px 0;border-left:4px solid #af5031;">
            <h3 style="margin:0 0 10px 0;color:#af5031;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;">Parent / Guardian Portal Account</h3>
            <table class="meta-table" style="margin:0;">
              <tr><td class="label" style="width:160px;font-weight:600;color:#0f414a;">Parent Full Name:</td><td><strong style="font-size:14.5px;color:#0f414a;">' . htmlspecialchars($parentName) . '</strong> <span style="font-size:11px;color:#af5031;">(As registered with school)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Parent Login Email:</td><td><code style="font-size:13.5px;font-weight:700;color:#af5031;">' . htmlspecialchars($parentEmail) . '</code></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Portal Access:</td><td><a href="http://localhost:8040/auth/parent" style="color:#0f414a;font-weight:600;">http://localhost:8040/auth/parent</a></td></tr>
            </table>
            <div style="background:#ffffff;border:1px solid rgba(175,80,49,0.25);border-radius:8px;padding:10px 14px;margin:12px 0;font-size:12.5px;line-height:1.5;color:#0f414a;">
              <strong>When activating your Parent account, enter:</strong><br>
              &bull; <strong>Full Name:</strong> ' . htmlspecialchars($parentName) . '<br>
              &bull; <strong>Email Address:</strong> ' . htmlspecialchars($parentEmail) . '
            </div>
            <div style="margin-top:12px;">
              <a href="' . htmlspecialchars($parentSignupUrl) . '" class="btn-primary" style="background:#af5031;color:#ffffff;display:inline-block;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:700;text-decoration:none;">
                Set Parent Password & Activate &rarr;
              </a>
            </div>
          </div>

          <!-- Student Credentials Section -->
          <div style="background:#e8f4f5;padding:18px 20px;border-radius:12px;margin:20px 0;border-left:4px solid #207c82;">
            <h3 style="margin:0 0 10px 0;color:#0f414a;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;">Enrolled Student Account</h3>
            <table class="meta-table" style="margin:0;">
              <tr><td class="label" style="width:160px;font-weight:600;color:#0f414a;">Student Full Name:</td><td><strong style="font-size:14.5px;color:#0f414a;">' . htmlspecialchars($studentData['name']) . '</strong> <span style="font-size:11px;color:#207c82;">(As registered with school)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Student Index / Reg No:</td><td><strong style="font-size:15px;color:#0f414a;letter-spacing:0.04em;background:#d7edef;padding:2px 8px;border-radius:4px;">' . htmlspecialchars($studentData['index']) . '</strong> <span style="font-size:11px;color:#207c82;">(Student Username)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Cohort Placement:</td><td>' . htmlspecialchars($studentData['grade']) . ' &bull; Class ' . htmlspecialchars($studentData['classSection']) . '</td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Student Portal:</td><td><a href="http://localhost:8040/auth/student" style="color:#0f414a;font-weight:600;">http://localhost:8040/auth/student</a></td></tr>
            </table>
            <div style="background:#ffffff;border:1px solid rgba(32,124,130,0.25);border-radius:8px;padding:10px 14px;margin:12px 0;font-size:12.5px;line-height:1.5;color:#0f414a;">
              <strong>When activating your child\'s Student account, enter:</strong><br>
              &bull; <strong>Full Name:</strong> ' . htmlspecialchars($studentData['name']) . '<br>
              &bull; <strong>Index Number:</strong> ' . htmlspecialchars($studentData['index']) . '
            </div>
            <div style="margin-top:12px;">
              <a href="' . htmlspecialchars($studentSignupUrl) . '" class="btn-primary" style="background:#207c82;color:#ffffff;display:inline-block;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:700;text-decoration:none;">
                Set Student Password & Activate &rarr;
              </a>
            </div>
          </div>

          <p style="font-size:13px;color:rgba(15,65,74,0.75);margin-top:20px;">
            If you need any assistance, our School Administration and Admissions Office is available at <a href="mailto:admissions@lecole.edu" style="color:#0f414a;font-weight:600;">admissions@lecole.edu</a>.
          </p>
        ';

        return self::send($parentEmail, $subject, $body, [
            'role'       => 'parent',
            'name'       => $parentName,
            'student_id' => $studentData['index']
        ]);
    }

    /**
     * Case 2: Sibling Enrolment Email (Existing Guardian)
     * Dispatched to the existing guardian informing them of the newly enrolled child.
     * Reassures the parent that their username (email) and password remain identical,
     * and provides the student's registration credentials.
     */
    public static function sendSiblingAdmissionToExistingGuardian(
        string $parentEmail,
        string $parentName,
        array $studentData
    ): array {
        $subject = "L'École — New Sibling Enrolment: " . $studentData['name'] . " (" . $studentData['index'] . ")";
        $studentSignupUrl = 'http://localhost:8040/auth/studentSignup?identifier=' . urlencode($studentData['index']) . '&name=' . urlencode($studentData['name']);

        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:20px;">New Student Enrolled — Sibling Linkage Confirmed</h2>
          <p>Dear ' . htmlspecialchars($parentName) . ',</p>
          <p>We are delighted to confirm that information for <strong>' . htmlspecialchars($studentData['name']) . '</strong> has been <strong>successfully registered in the L\'École School Database with official Index Number ' . htmlspecialchars($studentData['index']) . '</strong> and linked to your existing parent profile.</p>

          <!-- New Sibling Details Card -->
          <div style="background:#e8f4f5;padding:18px 20px;border-radius:12px;margin:20px 0;border-left:4px solid #207c82;">
            <h3 style="margin:0 0 10px 0;color:#0f414a;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;">New Student Information</h3>
            <table class="meta-table" style="margin:0;">
              <tr><td class="label" style="width:160px;font-weight:600;color:#0f414a;">Student Full Name:</td><td><strong style="font-size:14.5px;color:#0f414a;">' . htmlspecialchars($studentData['name']) . '</strong> <span style="font-size:11px;color:#207c82;">(As registered with school)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Student Index / Reg No:</td><td><strong style="font-size:15px;color:#0f414a;letter-spacing:0.04em;background:#d7edef;padding:2px 8px;border-radius:4px;">' . htmlspecialchars($studentData['index']) . '</strong> <span style="font-size:11px;color:#207c82;">(Student Username)</span></td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Class Section:</td><td>' . htmlspecialchars($studentData['grade']) . ' &bull; Class ' . htmlspecialchars($studentData['classSection']) . '</td></tr>
              <tr><td class="label" style="font-weight:600;color:#0f414a;">Student Portal:</td><td><a href="http://localhost:8040/auth/student" style="color:#0f414a;font-weight:600;">http://localhost:8040/auth/student</a></td></tr>
            </table>
            <div style="background:#ffffff;border:1px solid rgba(32,124,130,0.25);border-radius:8px;padding:10px 14px;margin:12px 0;font-size:12.5px;line-height:1.5;color:#0f414a;">
              <strong>When activating your child\'s Student account, enter:</strong><br>
              &bull; <strong>Full Name:</strong> ' . htmlspecialchars($studentData['name']) . '<br>
              &bull; <strong>Index Number:</strong> ' . htmlspecialchars($studentData['index']) . '
            </div>
            <div style="margin-top:12px;">
              <a href="' . htmlspecialchars($studentSignupUrl) . '" class="btn-primary" style="background:#207c82;color:#ffffff;display:inline-block;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:700;text-decoration:none;">
                Set Student Password & Activate &rarr;
              </a>
            </div>
          </div>

          <!-- Existing Parent Account Note -->
          <div style="background:#fdfaf6;padding:16px 20px;border-radius:12px;margin:20px 0;border:1px solid rgba(15,65,74,0.12);">
            <h4 style="margin:0 0 8px 0;color:#0f414a;font-size:14px;">Your Existing Parent Portal Account</h4>
            <p style="margin:0;font-size:13.5px;color:#233438;line-height:1.5;">
              Registered Parent Name: <strong>' . htmlspecialchars($parentName) . '</strong><br>
              Your parent username remains <strong>' . htmlspecialchars($parentEmail) . '</strong> and your existing password is unchanged. When you log into your <a href="http://localhost:8040/auth/parent" style="color:#af5031;font-weight:600;">Parent Portal</a>, your new child will automatically be visible alongside their siblings in your family dashboard!
            </p>
          </div>

          <p style="font-size:13px;color:rgba(15,65,74,0.75);">
            If you did not authorize this enrollment or have questions, please contact the School Office at <a href="mailto:office@lecole.edu" style="color:#0f414a;font-weight:600;">office@lecole.edu</a>.
          </p>
        ';

        return self::send($parentEmail, $subject, $body, [
            'role'       => 'parent',
            'name'       => $parentName,
            'student_id' => $studentData['index']
        ]);
    }

    /**
     * Helper to send account creation / activation confirmation
     */
    public static function sendWelcomeConfirmation(string $toBookCover, string $role = '', string $name = ''): array {
        $subject = "Welcome to L'École — Account Activated";
        $body = '
          <h2 style="margin-top:0;color:#0f414a;font-size:18px;">Welcome to L\'École</h2>
          <p>Hello ' . htmlspecialchars($name ?: 'there') . ',</p>
          <p>Your <strong>' . htmlspecialchars(ucfirst($role ?: 'User')) . '</strong> account (<code>' . htmlspecialchars($toBookCover) . '</code>) has been successfully activated.</p>
          <p>You can now sign in directly at <a href="http://localhost:8040/auth" style="color:#d85a38;font-weight:600;">http://localhost:8040/auth</a>.</p>
        ';

        return self::send($toBookCover, $subject, $body, ['role' => $role, 'name' => $name]);
    }

    /**
     * Record dispatch in storage/logs/mail.log
     */
    private static function logDispatch(string $toBookCover, string $realRecipient, string $subject, array $context): void {
        $config = self::getConfig();
        $logFile = $config['mail_log_file'] ?? dirname(__DIR__) . '/storage/logs/mail.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $entry = sprintf(
            "[%s] ROUTED TO: %s | BOOK COVER: %s | ROLE: %s | SUBJECT: %s | OTP: %s\n",
            date('Y-m-d H:i:s'),
            $realRecipient,
            $toBookCover,
            $context['role'] ?? 'N/A',
            $subject,
            $context['otp'] ?? 'None'
        );

        file_put_contents($logFile, $entry, FILE_APPEND);
    }

    /**
     * Native STARTTLS SMTP socket implementation for Gmail (Port 587)
     */
    private static function sendViaSmtp(string $to, string $subject, string $htmlBody, array $smtp, string $fromEmail, string $fromName): array {
        $host = $smtp['host'] ?? 'smtp.gmail.com';
        $port = (int)($smtp['port'] ?? 587);
        $user = $smtp['username'] ?? '';
        $pass = $smtp['password'] ?? '';

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
            if (!$socket) {
                return ['success' => false, 'error' => "SMTP Connection failed: $errstr ($errno)"];
            }

            $read = function () use ($socket) {
                $response = '';
                while ($line = fgets($socket, 515)) {
                    $response .= $line;
                    if (substr($line, 3, 1) === ' ') break;
                }
                return $response;
            };

            $write = function (string $cmd) use ($socket) {
                fputs($socket, $cmd . "\r\n");
            };

            $read(); // initial greeting
            $write("EHLO localhost");
            $read();

            $write("STARTTLS");
            $tlsRes = $read();
            if (substr($tlsRes, 0, 3) !== '220') {
                fclose($socket);
                return ['success' => false, 'error' => 'STARTTLS failed: ' . $tlsRes];
            }

            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write("EHLO localhost");
            $read();

            $write("AUTH LOGIN");
            $read();
            $write(base64_encode($user));
            $read();
            $write(base64_encode($pass));
            $authRes = $read();

            if (substr($authRes, 0, 3) !== '235') {
                fclose($socket);
                return ['success' => false, 'error' => 'SMTP Authentication failed. Check App Password.'];
            }

            $write("MAIL FROM: <$user>");
            $read();
            $write("RCPT TO: <$to>");
            $read();
            $write("DATA");
            $read();

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$user>\r\n";
            $headers .= "Reply-To: $fromEmail\r\n";
            $headers .= "To: <$to>\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $headers .= "Date: " . date('r') . "\r\n";

            $message = $headers . "\r\n" . $htmlBody . "\r\n.\r\n";
            $write($message);
            $read();

            $write("QUIT");
            fclose($socket);

            return ['success' => true];
        } catch (\Throwable $t) {
            return ['success' => false, 'error' => $t->getMessage()];
        }
    }
}
