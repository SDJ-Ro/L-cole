<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/AuditModel.php';

class NoticeController extends Controller {

    public function __construct() {
        parent::__construct();
        // Require authentication for managing or viewing notices
        $this->requireAuth();
    }

    /**
     * Read / List all notices for the current user's role
     * GET /notice or GET /notice/index
     */
    public function index() {
        $user = $this->getUser();
        $role = strtolower($user['role'] ?? 'student');

        $notices    = NoticeModel::getForRole($role);
        $categories = NoticeModel::getCategories();
        $audiences  = NoticeModel::getAudiences();

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'notices' => $notices]);
            return;
        }

        // Render view corresponding to user role
        $viewPath = match($role) {
            'admin'      => 'admin/notice',
            'management' => 'management/notice',
            'teacher'    => 'teacher/notice',
            'parent'     => 'parent/notice',
            'student'    => 'student/notice',
            default      => 'student/notice'
        };

        $this->view($viewPath, [
            'currentRole'  => $role,
            'currentRoute' => '/' . $role . '/notice',
            'notices'      => $notices,
            'categories'   => $categories,
            'audiences'    => $audiences,
        ]);
    }

    /**
     * Alias for index / read
     */
    public function read() {
        $this->index();
    }

    /**
     * Read single notice by ID
     * GET /notice/get-notice?id=1 or GET /notice/get?id=1
     */
    public function getNotice() {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid notice ID.']);
            return;
        }

        $notice = NoticeModel::getById($id);
        if ($notice) {
            echo json_encode(['success' => true, 'notice' => $notice]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Notice not found.']);
        }
    }

    public function get() {
        $this->getNotice();
    }

    /**
     * Save a notice (Create new or Update existing)
     * POST /notice/save-notice or POST /notice/save or POST /notice/create
     */
    public function saveNotice() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        // Validate CSRF token
        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token. Please refresh.']);
            return;
        }

        $user = $this->getUser();
        $userRole = strtolower($user['role'] ?? '');

        // Only Admin, Management, and Teacher roles can create/update notices
        if (!in_array($userRole, ['admin', 'management', 'teacher'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied. You do not have permission to publish notices.']);
            return;
        }

        $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $pinned   = !empty($_POST['pinned']) ? 1 : 0;
        
        $audience = $_POST['audience'] ?? ['All'];
        if (is_string($audience)) {
            $audience = array_filter(array_map('trim', explode(',', $audience)));
        }

        if (empty($title)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Notice title is required.']);
            return;
        }

        if (empty($body)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Notice body content is required.']);
            return;
        }

        // Handle optional file attachment
        $attachmentUrl = null;
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Invalid attachment file type. Allowed: PDF, DOCX, JPG, PNG.']);
                return;
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Attachment exceeds 5MB size limit.']);
                return;
            }

            $uploadDir = __DIR__ . '/../../public/assets/uploads/notices/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $safeFileName = 'notice_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destination = $uploadDir . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachmentUrl = '/assets/uploads/notices/' . $safeFileName;
            }
        }

        $authorName = 'Admin Office';
        if (!empty($user['name'])) {
            $authorName = $user['name'];
        } elseif ($userRole === 'teacher') {
            $authorName = 'Faculty Office';
        } elseif ($userRole === 'management') {
            $authorName = 'Management Office';
        }

        $data = [
            'account_id'     => $user['id'] ?? null,
            'title'          => $title,
            'category'       => $category,
            'audience'       => $audience,
            'body'           => $body,
            'author_name'    => $authorName,
            'pinned'         => $pinned,
            'attachment_url' => $attachmentUrl,
        ];

        if ($id && $id > 0) {
            // UPDATE
            $ok = NoticeModel::update($id, $data);
            if ($ok) {
                $updatedNotice = NoticeModel::getById($id);
                AuditModel::record(
                    $user['id'] ?? null,
                    $user['identifier'] ?? $userRole,
                    'NOTICE_UPDATED',
                    "Updated notice #{$id}: {$title}"
                );
                echo json_encode([
                    'success' => true,
                    'action'  => 'update',
                    'notice'  => $updatedNotice,
                    'message' => 'Notice updated successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update notice in database.']);
            }
        } else {
            // CREATE
            $createdNotice = NoticeModel::create($data);
            if ($createdNotice) {
                AuditModel::record(
                    $user['id'] ?? null,
                    $user['identifier'] ?? $userRole,
                    'NOTICE_CREATED',
                    "Published notice #{$createdNotice['id']}: {$title}"
                );
                echo json_encode([
                    'success' => true,
                    'action'  => 'create',
                    'notice'  => $createdNotice,
                    'message' => 'Notice published successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to publish notice.']);
            }
        }
    }

    public function create() {
        $this->saveNotice();
    }

    public function save() {
        $this->saveNotice();
    }

    public function update() {
        $this->saveNotice();
    }

    /**
     * Delete notice
     * POST /notice/delete-notice or POST /notice/delete
     */
    public function deleteNotice() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
            return;
        }

        $user = $this->getUser();
        $userRole = strtolower($user['role'] ?? '');

        if (!in_array($userRole, ['admin', 'management'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied. You do not have permission to delete notices.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid notice ID.']);
            return;
        }

        $notice = NoticeModel::getById($id);
        $ok = NoticeModel::delete($id);

        if ($ok) {
            AuditModel::record(
                $user['id'] ?? null,
                $user['identifier'] ?? $userRole,
                'NOTICE_DELETED',
                "Deleted notice #{$id}: " . ($notice['title'] ?? 'Unknown')
            );
            echo json_encode(['success' => true, 'message' => 'Notice deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete notice.']);
        }
    }

    public function delete() {
        $this->deleteNotice();
    }

    /**
     * Toggle pinned status of notice
     * POST /notice/toggle-pin-notice or POST /notice/toggle-pin
     */
    public function togglePinNotice() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
            return;
        }

        $user = $this->getUser();
        $userRole = strtolower($user['role'] ?? '');

        if (!in_array($userRole, ['admin', 'management'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied. You do not have permission to pin notices.']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid notice ID.']);
            return;
        }

        $newPinned = NoticeModel::togglePin($id);
        AuditModel::record(
            $user['id'] ?? null,
            $user['identifier'] ?? $userRole,
            'NOTICE_PIN_TOGGLED',
            "Toggled pin on notice #{$id} to " . ($newPinned ? 'pinned' : 'unpinned')
        );

        echo json_encode(['success' => true, 'pinned' => $newPinned]);
    }

    public function togglePin() {
        $this->togglePinNotice();
    }
}
