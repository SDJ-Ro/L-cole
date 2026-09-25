<?php
class AuthController extends Controller {

    // Access selector page: http://localhost:8040/auth
    public function index() {
        $this->view('sign-in-up_page/access');
    }

    // Role specific sign-in views using master sign-in-template
    public function student() {
        $this->view('sign-in-up_page/sign-in-template', [
            'role'             => 'student',
            'title'            => "Student sign in — L'École",
            'eyebrow'          => 'Your learning journey',
            'desc'             => 'Pick up your academic day, activities, teams, and next opportunities.',
            'headline'         => 'Follow academics, discover clubs, and build the skills that shape your next chapter.',
            'image'            => '/assets/images/students.jpg',
            'imageAlt'         => 'Student boy and girl in school uniform',
            'badgeIcon'        => 'icon-graduationCap',
            'inputLabel'       => 'Student email or index number',
            'inputPlaceholder' => 'name@lecole.edu',
            'signupUrl'        => '/auth/studentSignup',
            'signupLabel'      => 'Need access? Sign up',
            'alternateText'    => "New to L'École?",
            'alternateAction'  => 'Create your student account'
        ]);
    }

    public function teacher() {
        $this->view('sign-in-up_page/sign-in-template', [
            'role'             => 'teacher',
            'title'            => "Teacher sign in — L'École",
            'eyebrow'          => 'Guide the journey',
            'desc'             => 'Move between your classes, activities, and student progress with clarity.',
            'headline'         => 'Bring learning, clubs, teams, and student development into one thoughtful view.',
            'image'            => '/assets/images/teacher.jpg',
            'imageAlt'         => 'Teacher mentoring students on campus',
            'badgeIcon'        => 'icon-bookOpen',
            'inputLabel'       => 'School email',
            'inputPlaceholder' => 'name@lecole.edu',
            'signupUrl'        => '/auth/teacherSignup',
            'signupLabel'      => 'Need access? Sign up',
            'alternateText'    => "New to L'École?",
            'alternateAction'  => 'Create your teacher account'
        ]);
    }

    public function parent() {
        $this->view('sign-in-up_page/sign-in-template', [
            'role'             => 'parent',
            'title'            => "Parent sign in — L'École",
            'eyebrow'          => 'Part of the team',
            'desc'             => "Stay connected to your child's academics, activities, moments, and progress.",
            'headline'         => 'See academic milestones and the activities helping your child grow in confidence.',
            'image'            => '/assets/images/parents.jpg',
            'imageAlt'         => 'Father and mother holding a baby',
            'badgeIcon'        => 'icon-heartHandshake',
            'inputLabel'       => 'Email address',
            'inputPlaceholder' => 'parent@email.com',
            'signupUrl'        => '/auth/parentSignup',
            'signupLabel'      => 'Need access? Sign up',
            'alternateText'    => "New to L'École?",
            'alternateAction'  => 'Create your parent account'
        ]);
    }

    public function management() {
        $this->view('sign-in-up_page/sign-in-template', [
            'role'             => 'management',
            'title'            => "Management sign in — L'École",
            'eyebrow'          => 'Restricted access',
            'desc'             => 'Approvals, enrolment, staffing, and school-wide performance in one leadership workspace.',
            'headline'         => 'Lead the school with a clear view of every decision.',
            'image'            => '/assets/images/management.jpg',
            'imageAlt'         => 'School leadership team talking in a bright campus corridor',
            'badgeIcon'        => 'icon-building2',
            'inputLabel'       => 'Institutional email',
            'inputPlaceholder' => 'name@staff.lecole.edu',
            'noticeText'       => "You're entering the management panel. Accounts are issued by the school office.",
            'noticeIcon'       => 'icon-lockKeyhole',
            'noticeBgStyle'    => 'background:var(--maroon);color:var(--alabaster);',
            'isAudited'        => true,
            'footnote'         => 'Need access or lost your credentials? Contact <strong>office@lecole.edu</strong>'
        ]);
    }

    public function admin() {
        $this->view('sign-in-up_page/sign-in-template', [
            'role'             => 'admin',
            'title'            => "Admin sign in — L'École",
            'eyebrow'          => 'Restricted access',
            'desc'             => "Manage accounts, permissions, records, and the full audit trail of the L'École platform.",
            'headline'         => 'The system behind every record, role, and safeguard.',
            'image'            => '/assets/images/schoolyard.jpg',
            'imageAlt'         => "L'École school building and front lawn",
            'badgeIcon'        => 'icon-shield',
            'inputLabel'       => 'Administrator email',
            'inputPlaceholder' => 'admin@lecole.edu',
            'noticeText'       => "You're entering the system admin panel. Every action here is logged.",
            'noticeIcon'       => 'icon-lockKeyhole',
            'noticeBgStyle'    => 'background:var(--midnight);color:#fff;',
            'isAudited'        => true,
            'footnote'         => 'Need access or lost your credentials? Contact <strong>admin@lecole.edu</strong>'
        ]);
    }

    // Role specific sign-up views using master sign-up-template
    public function studentSignup() {
        $this->view('sign-in-up_page/sign-up-template', [
            'role'             => 'student',
            'title'            => "Start your student access — L'École",
            'eyebrow'          => 'Your learning journey',
            'desc'             => 'Request access to your academics, activities, and school community.',
            'headline'         => 'Follow academics, discover clubs, and build the skills that shape your next chapter.',
            'image'            => '/assets/images/students.jpg',
            'imageAlt'         => 'Student boy and girl in school uniform',
            'badgeIcon'        => 'icon-graduationCap',
            'inputLabel'       => 'Student email or index number',
            'inputPlaceholder' => 'name@lecole.edu',
            'signinUrl'        => '/auth/student'
        ]);
    }

    public function teacherSignup() {
        $this->view('sign-in-up_page/sign-up-template', [
            'role'             => 'teacher',
            'title'            => "Set up teacher access — L'École",
            'eyebrow'          => 'Guide the journey',
            'desc'             => 'Request your school access to teaching, activities, and community updates.',
            'headline'         => 'Bring learning, clubs, teams, and student development into one thoughtful view.',
            'image'            => '/assets/images/teacher.jpg',
            'imageAlt'         => 'Teacher mentoring students on campus',
            'badgeIcon'        => 'icon-bookOpen',
            'inputLabel'       => 'School email',
            'inputPlaceholder' => 'name@lecole.edu',
            'signinUrl'        => '/auth/teacher'
        ]);
    }

    public function parentSignup() {
        $this->view('sign-in-up_page/sign-up-template', [
            'role'             => 'parent',
            'title'            => "Set up parent access — L'École",
            'eyebrow'          => 'Part of the team',
            'desc'             => "Request a secure view of your child's school life and opportunities.",
            'headline'         => 'See academic milestones and the activities helping your child grow in confidence.',
            'image'            => '/assets/images/parents.jpg',
            'imageAlt'         => 'Father and mother holding a baby',
            'badgeIcon'        => 'icon-heartHandshake',
            'inputLabel'       => 'Email address',
            'inputPlaceholder' => 'parent@email.com',
            'signinUrl'        => '/auth/parent'
        ]);
    }

    /**
     * Process Sign-In submission via POST (AJAX JSON or Form POST)
     */
    public function handleSignin() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        // Support both application/json payload and form urlencoded
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $data = !empty($json) ? $json : $_POST;

        // Verify CSRF token
        $csrfToken = $data['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'Security token invalid or expired. Please refresh the page and try again.',
                'code'    => 'CSRF_INVALID'
            ]);
            return;
        }

        $identifier = trim($data['identifier'] ?? $data['email'] ?? '');
        $password   = (string)($data['password'] ?? '');
        $role       = strtolower(trim($data['role'] ?? ''));

        if (empty($identifier) || empty($password) || empty($role)) {
            echo json_encode(['success' => false, 'error' => 'Please provide all required fields.']);
            return;
        }

        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();
        $result = $userModel->authenticate($identifier, $password, $role);

        if (!$result['success']) {
            // Check if account is PENDING activation
            if (!empty($result['needs_activation'])) {
                $redirectUrl = '/auth/' . $role . 'Signup?identifier=' . urlencode($result['identifier'] ?? $identifier);
                echo json_encode([
                    'success'          => false,
                    'needs_activation' => true,
                    'redirect'         => $redirectUrl,
                    'error'            => $result['error']
                ]);
                return;
            }

            echo json_encode([
                'success' => false,
                'error'   => $result['error'],
                'code'    => $result['code'] ?? 'AUTH_FAILED'
            ]);
            return;
        }

        // Authentication Success: Defend against session fixation
        session_regenerate_id(true);

        $_SESSION['user']          = $result['account'];
        $_SESSION['profile']       = $result['profile'];
        $_SESSION['last_activity'] = time();

        // Redirect map by authenticated user's actual role
        $actualRole = $result['account']['role'] ?? $role;
        $redirectUrl = '/' . $actualRole;
        if ($actualRole === 'management') {
            $redirectUrl = '/management';
        }

        echo json_encode([
            'success'               => true,
            'redirect'              => $redirectUrl,
            'first_login_required'  => (bool)$result['first_login_required']
        ]);
    }

    /**
     * Process Sign-Up / Activation submission via POST
     */
    public function handleSignup() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $data = !empty($json) ? $json : $_POST;

        // Verify CSRF token
        $csrfToken = $data['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'Security token invalid or expired. Please refresh the page and try again.',
                'code'    => 'CSRF_INVALID'
            ]);
            return;
        }

        $fullName   = trim($data['fullName'] ?? $data['name'] ?? '');
        $identifier = trim($data['identifier'] ?? $data['email'] ?? '');
        $password   = (string)($data['password'] ?? '');
        $role       = strtolower(trim($data['role'] ?? ''));

        if (empty($fullName) || empty($identifier) || empty($password) || empty($role)) {
            echo json_encode(['success' => false, 'error' => 'Please fill in all fields (name, email, and password).']);
            return;
        }

        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();
        $res = $userModel->registerOrActivateAccount($fullName, $identifier, $password, $role);

        if (!$res['success']) {
            echo json_encode(['success' => false, 'error' => $res['error']]);
            return;
        }

        echo json_encode([
            'success'  => true,
            'message'  => $res['message'],
            'redirect' => '/auth/' . $role
        ]);
    }

    /**
     * Send password reset OTP code
     */
    public function handleForgotSend() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $data = !empty($json) ? $json : $_POST;

        // Verify CSRF token
        $csrfToken = $data['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'Security token invalid or expired. Please refresh the page and try again.',
                'code'    => 'CSRF_INVALID'
            ]);
            return;
        }

        $identifier = trim($data['email'] ?? $data['identifier'] ?? '');
        if (empty($identifier)) {
            echo json_encode(['success' => false, 'error' => 'Please enter your account email.']);
            return;
        }

        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();
        $res = $userModel->requestPasswordReset($identifier);

        if (!$res['success']) {
            echo json_encode([
                'success' => false,
                'error'   => $res['error'] ?? 'Unable to process reset request.'
            ]);
            return;
        }

        $rawEmail = $res['recipient_email'] ?? $identifier;
        $maskedRecipient = $rawEmail;
        if (str_contains($rawEmail, '@')) {
            $parts = explode('@', $rawEmail, 2);
            $userPart = $parts[0];
            $domainPart = $parts[1];
            $maskedUser = (strlen($userPart) <= 2) 
                ? $userPart[0] . '*' 
                : substr($userPart, 0, 2) . str_repeat('*', max(2, strlen($userPart) - 3)) . substr($userPart, -1);
            $maskedRecipient = $maskedUser . '@' . $domainPart;
        }

        echo json_encode([
            'success'   => true,
            'message'   => 'A 6-digit verification code has been dispatched to your email.',
            'recipient' => $maskedRecipient
        ]);
    }

    /**
     * Verify OTP and reset password
     */
    public function handleForgotReset() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $data = !empty($json) ? $json : $_POST;

        // Verify CSRF token
        $csrfToken = $data['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'Security token invalid or expired. Please refresh the page and try again.',
                'code'    => 'CSRF_INVALID'
            ]);
            return;
        }

        $identifier  = trim($data['email'] ?? $data['identifier'] ?? '');
        $code        = trim($data['code'] ?? '');
        $password    = trim((string)($data['password'] ?? $data['newPassword'] ?? ''));
        $isReset     = !empty($data['is_reset']);

        if (empty($identifier) || empty($code)) {
            echo json_encode(['success' => false, 'error' => 'Please provide both your account identifier and 6-digit code.']);
            return;
        }

        if (empty($password)) {
            echo json_encode([
                'success' => false,
                'error'   => $isReset ? 'Please enter your new password.' : 'Please enter your password to sign in.'
            ]);
            return;
        }

        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();

        $res = $userModel->authenticateOrResetWithOtp($identifier, $code, $password, $isReset);

        if (!$res['success']) {
            echo json_encode($res);
            return;
        }

        // Authentication Success: Establish session and redirect to dashboard
        session_regenerate_id(true);
        $_SESSION['user']          = $res['account'];
        $_SESSION['profile']       = $res['profile'];
        $_SESSION['last_activity'] = time();

        $actualRole = strtolower($res['account']['role'] ?? 'teacher');
        $redirectUrl = '/' . $actualRole;
        if ($actualRole === 'management') {
            $redirectUrl = '/management';
        }

        echo json_encode([
            'success'  => true,
            'redirect' => $redirectUrl,
            'message'  => $res['message'] ?? 'Signed in successfully! Redirecting to your dashboard…'
        ]);
    }

    /**
     * Change password for student on first login
     */
    public function handleForcePassword() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        if (empty($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $data = !empty($json) ? $json : $_POST;

        // Verify CSRF token
        $csrfToken = $data['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'Security token invalid or expired. Please refresh the page and try again.',
                'code'    => 'CSRF_INVALID'
            ]);
            return;
        }

        $newPassword = (string)($data['newPassword'] ?? '');

        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();
        $res = $userModel->forcePasswordChange((int)$_SESSION['user']['id'], $newPassword);

        if ($res['success']) {
            $_SESSION['user']['first_login_required'] = false;
        }

        echo json_encode($res);
    }

    /**
     * Secure session logout
     */
    public function logout() {
        if ($this->isAuthenticated()) {
            require_once __DIR__ . '/../Models/AuditModel.php';
            $user = $this->getUser();
            AuditModel::record(
                (int)($user['id'] ?? 0),
                $user['identifier'] ?? $user['email'] ?? null,
                'LOGOUT',
                'User signed out of portal session.'
            );
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header('Location: /auth');
        exit;
    }
}

