<?php
if (!function_exists('e')) {
    /**
     * Global escaping helper for HTML views to prevent XSS.
     * Replaces verbose htmlspecialchars($value, ENT_QUOTES, 'UTF-8').
     */
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

class Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkSessionTimeout();
    }
    
    // This function fetches your UI files from the Views folder
    public function view($viewName, $data = []) {
        // Automatically inject CSRF token into all views
        if (!isset($data['csrf_token'])) {
            $data['csrf_token'] = $this->getCsrfToken();
        }

        // This takes backend data and turns it into simple variables for your HTML
        extract($data);
        
        // Check if your UI file actually exists
        if (file_exists('../app/Views/' . $viewName . '.php')) {
            require_once '../app/Views/' . $viewName . '.php';
        } else {
            echo "Error: The View file " . $viewName . ".php does not exist.";
        }
    }

    public function isAuthenticated(): bool {
        return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    public function getUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public function getProfile(): ?array {
        return $_SESSION['profile'] ?? null;
    }

    public function setFlash(string $key, string $message): void {
        $_SESSION['flash'][$key] = $message;
    }

    public function getFlash(string $key): ?string {
        if (!empty($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }

    public function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Generate or fetch the current session's CSRF token.
     */
    public function getCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Output a hidden HTML input field containing the CSRF token.
     */
    public function csrfField(): string {
        return '<input type="hidden" name="_csrf_token" value="' . e($this->getCsrfToken()) . '">';
    }

    /**
     * Validate an incoming CSRF token against the session token.
     */
    public function validateCsrf(?string $token = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $expected = $_SESSION['_csrf_token'] ?? '';
        if (empty($expected)) {
            return false;
        }

        // 1. Explicitly passed parameter
        if (!empty($token) && hash_equals($expected, (string)$token)) {
            return true;
        }

        // 2. HTTP header X-CSRF-Token or X-XSRF-Token
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? null;
        if (!empty($headerToken) && hash_equals($expected, (string)$headerToken)) {
            return true;
        }

        // 3. POST body parameter
        if (!empty($_POST['_csrf_token']) && hash_equals($expected, (string)$_POST['_csrf_token'])) {
            return true;
        }

        return false;
    }

    /**
     * Idle session inactivity guard.
     * Enforces 15-minute timeout for Admin and 30-minute timeout for all other roles.
     */
    protected function checkSessionTimeout(): void {
        if (!$this->isAuthenticated()) {
            return;
        }

        $user = $this->getUser();
        $userRole = strtolower($user['role'] ?? '');
        
        // Admin: 15 minutes (900s), others: 30 minutes (1800s)
        $timeoutSeconds = ($userRole === 'admin') ? 900 : 1800;

        $lastActivity = $_SESSION['last_activity'] ?? null;
        $now = time();

        if ($lastActivity !== null && ($now - $lastActivity) > $timeoutSeconds) {
            // Log expiration in Audit Trail
            require_once __DIR__ . '/../app/Models/AuditModel.php';
            AuditModel::record(
                $user['id'] ?? null,
                $user['identifier'] ?? $user['email'] ?? null,
                'SESSION_EXPIRED',
                "Idle inactivity timeout reached ({$timeoutSeconds}s) for role: {$userRole}"
            );

            // Destroy stale session
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

            // Re-initialize clean session for the redirection flash message
            session_start();

            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => false,
                    'error'    => 'Your session has expired due to inactivity. Please sign in again.',
                    'redirect' => '/auth?role=' . urlencode($userRole)
                ]);
                exit;
            }

            $this->setFlash('notice', 'Your session has expired due to inactivity. Please sign in again.');
            $this->redirect('/auth?role=' . urlencode($userRole));
        }

        // Active request: bump last_activity
        $_SESSION['last_activity'] = $now;
    }

    /**
     * Enforce role-based access control.
     * @param string|array $allowedRoles
     */
    public function requireRole($allowedRoles): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkSessionTimeout();

        $allowed = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if (!$this->isAuthenticated()) {
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error'   => 'Session expired or not signed in. Please sign in.',
                    'redirect'=> '/auth'
                ]);
                exit;
            }
            $targetRole = count($allowed) === 1 ? reset($allowed) : '';
            $this->setFlash('notice', 'Please sign in to access this portal.');
            $this->redirect('/auth' . ($targetRole ? '?role=' . urlencode($targetRole) : ''));
        }

        $user = $this->getUser();
        $userRole = strtolower($user['role'] ?? '');

        // Check if user has permission
        if (!in_array($userRole, array_map('strtolower', $allowed), true)) {
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error'   => 'Access denied. You do not have permission to view this portal.',
                    'redirect'=> '/' . $userRole
                ]);
                exit;
            }
            // User is signed in under a different role:
            // Safely bounce them back to their legitimate dashboard
            $this->setFlash('notice', 'Access restricted. You have been returned to your ' . ucfirst($userRole) . ' dashboard.');
            $this->redirect('/' . $userRole);
        }
    }

    public function requireAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkSessionTimeout();

        if (!$this->isAuthenticated()) {
            $this->setFlash('notice', 'Please sign in to continue.');
            $this->redirect('/auth');
        }
    }
}