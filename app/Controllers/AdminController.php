<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/VerifyModel.php';
require_once __DIR__ . '/../Models/AuditModel.php';
require_once __DIR__ . '/../Models/AcademicModel.php';
require_once __DIR__ . '/../Models/ExtracurricularModel.php';
require_once __DIR__ . '/../Models/PeopleModel.php';

class AdminController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireRole('admin');
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        // Metric Cards Data (5 Cards)
        $metrics = [
            [
                'color'   => 'sand',
                'icon'    => 'icon-graduationCap',
                'value'   => '2,845',
                'label'   => 'Total Students',
                'valueId' => 'j-metric-students',
                'delay'   => 0,
            ],
            [
                'color'   => 'maroon',
                'icon'    => 'icon-presentation',
                'value'   => '145',
                'label'   => 'Total Teachers',
                'delay'   => 50,
            ],
            [
                'color'   => 'sunshine',
                'icon'    => 'icon-usersRound',
                'value'   => '2,102',
                'label'   => 'Total Parents',
                'delay'   => 100,
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-briefcase',
                'value'   => '12',
                'label'   => 'Management Panel',
                'delay'   => 150,
            ],
            [
                'color'   => 'moss',
                'icon'    => 'icon-extracurricular',
                'value'   => '6',
                'label'   => 'Extracurriculars',
                'delay'   => 200,
            ],
        ];

        // Column Chart Data (Grade 6 to 11 Enrollment & Sports)
        $chartConfig = [
            'id'         => 'j-admin-enrollment-chart',
            'title'      => 'Total Students vs Sports Participation by Class',
            'yAxisTitle' => 'STUDENTS',
            'xAxisTitle' => 'GRADE LEVEL',
            'maxVal'     => 160,
            'ticks'      => [0, 40, 80, 120, 160],
            'series'     => [
                ['key' => 'total',  'label' => 'Total Students',       'color' => BRAND_SKYBLUE],
                ['key' => 'sports', 'label' => 'Sports Participation', 'color' => BRAND_MAROON],
            ],
            'data'       => [
                ['label' => 'Grade 6',  'total' => 120, 'sports' => 35],
                ['label' => 'Grade 7',  'total' => 130, 'sports' => 42],
                ['label' => 'Grade 8',  'total' => 140, 'sports' => 48],
                ['label' => 'Grade 9',  'total' => 150, 'sports' => 51],
                ['label' => 'Grade 10', 'total' => 160, 'sports' => 57],
                ['label' => 'Grade 11', 'total' => 155, 'sports' => 61],
            ],
            'headerExtra' => '',
        ];

        // Calendar Configuration (Admin: Can add and manage events)
        $calendarConfig = [
            'canAddEvent' => true,
            'initialDate' => '2026-06-17',
            'viewDate'    => '2026-06-01',
            'events'      => [
                ['id' => 'exam-17', 'date' => '2026-06-17', 'time' => '08:30–10:30', 'title' => 'Mathematics examination', 'details' => 'Grades 6–8 · Respective classrooms', 'category' => 'Academic'],
                ['id' => 'exam-18', 'date' => '2026-06-18', 'time' => '08:30–10:30', 'title' => 'English examination', 'details' => 'Grades 6–8 · Respective classrooms', 'category' => 'Academic'],
                ['id' => 'exam-19', 'date' => '2026-06-19', 'time' => '08:30–10:30', 'title' => 'Science examination', 'details' => 'Grades 6–11 · Respective classrooms', 'category' => 'Academic'],
                ['id' => 'exam-20', 'date' => '2026-06-20', 'time' => '08:30–10:00', 'title' => 'History examination', 'details' => 'Grades 6–11 · Respective classrooms', 'category' => 'Academic'],
                ['id' => 'exam-23', 'date' => '2026-06-23', 'time' => '08:30–10:30', 'title' => 'Sinhala / Tamil examination', 'details' => 'Grades 6–11 · Respective classrooms', 'category' => 'Academic'],
                ['id' => 'exam-24', 'date' => '2026-06-24', 'time' => '08:30–11:00', 'title' => 'ICT practical assessment', 'details' => 'Grades 9–13 · Computer laboratories', 'category' => 'Academic'],
                ['id' => 'exam-25', 'date' => '2026-06-25', 'time' => '08:30–11:30', 'title' => 'Senior stream papers', 'details' => 'Grades 12–13 · Senior examination hall', 'category' => 'Academic'],
                ['id' => 'exam-26', 'date' => '2026-06-26', 'time' => '08:30–10:30', 'title' => 'Make-up examination session', 'details' => 'Grades 6–13 · Library seminar room', 'category' => 'Academic'],
            ],
        ];

        // Donut / Pie Charts Data
        $donutSports = [
            'id'          => 'j-donut-sports',
            'title'       => 'Sports Participation',
            'totalLabel'  => 'Students in all sports',
            'centerLabel' => 'Total',
            'total'       => 438,
            'slices'      => [
                [
                    'name'  => 'Football',
                    'value' => 150,
                    'color' => BRAND_SKYBLUE,
                    'd'     => 'M 162.68 131.17 A 70 70 0 0 0 100.00 30.00 L 100.00 50.00 A 50 50 0 0 1 144.77 122.26 Z',
                ],
                [
                    'name'  => 'Cricket',
                    'value' => 122,
                    'color' => BRAND_MIDNIGHT,
                    'd'     => 'M 58.72 156.53 A 70 70 0 0 0 159.72 136.51 L 142.66 126.08 A 50 50 0 0 1 70.51 140.38 Z',
                ],
                [
                    'name'  => 'Swimming',
                    'value' => 90,
                    'color' => BRAND_SUNSHINE,
                    'd'     => 'M 34.65 74.91 A 70 70 0 0 0 53.95 152.72 L 67.10 137.65 A 50 50 0 0 1 53.32 82.08 Z',
                ],
                [
                    'name'  => 'Athletics',
                    'value' => 76,
                    'color' => BRAND_TERRACOTTA,
                    'd'     => 'M 93.90 30.27 A 70 70 0 0 0 37.09 69.31 L 55.06 78.08 A 50 50 0 0 1 95.64 50.19 Z',
                ],
            ],
        ];

        $donutClubs = [
            'id'          => 'j-donut-clubs',
            'title'       => 'Clubs & Societies',
            'totalLabel'  => 'Club and society members',
            'centerLabel' => 'Total',
            'total'       => 314,
            'slices'      => [
                [
                    'name'  => 'Science Society',
                    'value' => 120,
                    'color' => BRAND_LIGHTBLUE,
                    'd'     => 'M 153.67 144.94 A 70 70 0 0 0 100.00 30.00 L 100.00 50.00 A 50 50 0 0 1 138.34 132.10 Z',
                ],
                [
                    'name'  => 'Debate',
                    'value' => 80,
                    'color' => BRAND_TERRACOTTA,
                    'd'     => 'M 53.56 152.38 A 70 70 0 0 0 149.55 149.44 L 135.39 135.32 A 50 50 0 0 1 66.83 137.41 Z',
                ],
                [
                    'name'  => 'Music',
                    'value' => 60,
                    'color' => BRAND_MAROON,
                    'd'     => 'M 34.88 74.31 A 70 70 0 0 0 49.17 148.13 L 63.69 134.38 A 50 50 0 0 1 53.49 81.65 Z',
                ],
                [
                    'name'  => 'Robotics',
                    'value' => 54,
                    'color' => BRAND_MOSS,
                    'd'     => 'M 93.90 30.27 A 70 70 0 0 0 37.37 68.73 L 55.26 77.67 A 50 50 0 0 1 95.64 50.19 Z',
                ],
            ],
        ];

        // Upcoming Events Data
        $upcomingEvents = [
            ['day' => '17', 'month' => 'JUN', 'name' => 'Term 2 examinations begin', 'tag' => 'Academic', 'tagColor' => 'sand'],
            ['day' => '20', 'month' => 'JUN', 'name' => 'History examination', 'tag' => 'Academic', 'tagColor' => 'sky'],
            ['day' => '26', 'month' => 'JUN', 'name' => 'Make-up examination session', 'tag' => 'Academic', 'tagColor' => 'terracotta'],
        ];

        $this->view('admin/dashboard', [
            'currentRole'    => 'admin',
            'currentRoute'   => '/admin/dashboard',
            'metrics'        => $metrics,
            'chartConfig'    => $chartConfig,
            'calendarConfig' => $calendarConfig,
            'donutSports'    => $donutSports,
            'donutClubs'     => $donutClubs,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function notice() {

        $notices    = NoticeModel::getForRole('admin');
        $categories = NoticeModel::getCategories();
        $audiences  = NoticeModel::getAudiences();

        $this->view('admin/notice', [
            'currentRole'  => 'admin',
            'currentRoute' => '/admin/notice',
            'notices'      => $notices,
            'categories'   => $categories,
            'audiences'    => $audiences,
        ]);
    }

    public function noticeBoard() {
        $this->notice();
    }

    /**
     * Save a notice (Create new or Update existing)
     * Supports multipart/form-data with optional file attachment
     */
    public function saveNotice() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
            return;
        }

        // Validate CSRF
        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token. Please refresh.']);
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

        $currentUser = $this->getUser();
        $authorName = 'Admin Office';
        if (!empty($currentUser['name'])) {
            $authorName = $currentUser['name'];
        }

        $data = [
            'account_id'     => $currentUser['id'] ?? null,
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
                    $currentUser['id'] ?? null,
                    $currentUser['identifier'] ?? 'admin',
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
                    $currentUser['id'] ?? null,
                    $currentUser['identifier'] ?? 'admin',
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

    /**
     * Delete notice
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

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid notice ID.']);
            return;
        }

        $notice = NoticeModel::getById($id);
        $ok = NoticeModel::delete($id);

        if ($ok) {
            $currentUser = $this->getUser();
            AuditModel::record(
                $currentUser['id'] ?? null,
                $currentUser['identifier'] ?? 'admin',
                'NOTICE_DELETED',
                "Deleted notice #{$id}: " . ($notice['title'] ?? 'Unknown')
            );
            echo json_encode(['success' => true, 'message' => 'Notice deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete notice.']);
        }
    }

    /**
     * Toggle pinned status of notice
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

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid notice ID.']);
            return;
        }

        $newPinned = NoticeModel::togglePin($id);
        $currentUser = $this->getUser();
        AuditModel::record(
            $currentUser['id'] ?? null,
            $currentUser['identifier'] ?? 'admin',
            'NOTICE_PIN_TOGGLED',
            "Toggled pin on notice #{$id} to " . ($newPinned ? 'pinned' : 'unpinned')
        );

        echo json_encode(['success' => true, 'pinned' => $newPinned]);
    }

    /**
     * Get single notice by ID (for edit view)
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

    public function verify() {

        $items         = VerifyModel::getAll();
        $pendingCounts = VerifyModel::getPendingCounts();
        $statusOptions = VerifyModel::getStatusOptions();

        $this->view('admin/verify', [
            'currentRole'   => 'admin',
            'currentRoute'  => '/admin/verify',
            'items'         => $items,
            'pendingCounts' => $pendingCounts,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function audit() {

        $logs            = AuditModel::getAll();
        $activityOptions = AuditModel::getActivityOptions();
        $actorOptions    = AuditModel::getActorOptions();

        $this->view('admin/audit', [
            'currentRole'     => 'admin',
            'currentRoute'    => '/admin/audit',
            'logs'            => $logs,
            'activityOptions' => $activityOptions,
            'actorOptions'    => $actorOptions,
        ]);
    }

    public function academic() {


        $grades   = AcademicModel::getGrades();
        $subjects = AcademicModel::getSubjects();
        $terms    = AcademicModel::getTerms();
        $events   = AcademicModel::getExamEvents();

        // 1. Class Section Performance Chart Config
        $chartConfig = [
            'id'          => 'j-academic-perf-chart',
            'title'       => 'Class section performance',
            'yAxisTitle'  => 'SCORE',
            'xAxisTitle'  => 'CLASS SECTION',
            'maxVal'      => 100,
            'ticks'       => [0, 20, 40, 60, 80, 100],
            'series'      => [
                ['key' => 'average', 'label' => 'Class average',   'color' => BRAND_SKYBLUE],
                ['key' => 'highest', 'label' => 'Highest average', 'color' => BRAND_MAROON],
            ],
            'data'        => AcademicModel::getInitialClassPerformance('g6', 'Mathematics', 'Term 1'),
        ];

        // 2. Calendar Config
        $calendarConfig = [
            'canAddEvent' => true,
            'initialDate' => '2026-06-17',
            'viewDate'    => '2026-06-01',
            'events'      => $events,
        ];

        $curriculumGroups = AcademicModel::getCurriculumGroups();
        $classTeachers    = AcademicModel::getClassTeachers();
        $classEnrollments = AcademicModel::getClassEnrollments();
        $staffAssignments = AcademicModel::getStaffAssignments();

        $this->view('admin/academic', [
            'currentRole'      => 'admin',
            'currentRoute'     => '/admin/academic',
            'chartConfig'      => $chartConfig,
            'calendarConfig'   => $calendarConfig,
            'academicDataset'  => $grades,
            'grades'           => $grades,
            'subjects'         => $subjects,
            'terms'            => $terms,
            'curriculumGroups' => $curriculumGroups,
            'classTeachers'    => $classTeachers,
            'classEnrollments' => $classEnrollments,
            'staffAssignments' => $staffAssignments,
        ]);
    }

    public function extracurricular() {
        $type = $_GET['type'] ?? 'All';
        $search = $_GET['q'] ?? '';
        $clubs = ExtracurricularModel::getAll($type, $search);

        $this->view('admin/extracurricular', [
            'currentRole'  => 'admin',
            'currentRoute' => '/admin/extracurricular',
            'clubs'        => $clubs,
            'canModerate'  => true,
            'canCreate'    => true,
            'selectedType' => $type,
            'searchQuery'  => $search,
        ]);
    }

    public function people() {
        $grades           = PeopleModel::getGrades();
        $classContext     = PeopleModel::getClassContext();
        $classEnrollments = PeopleModel::getClassEnrollments();
        $students         = PeopleModel::getStudents();
        $teachers         = PeopleModel::getTeachers();
        $parents          = PeopleModel::getParents();
        $management       = PeopleModel::getManagement();

        $this->view('admin/people', [
            'currentRole'      => 'admin',
            'currentRoute'     => '/admin/people',
            'allowedTabs'      => ['Students', 'Teachers', 'Parents', 'Management Panel'],
            'activeTab'        => 'Students',
            'grades'           => $grades,
            'classContext'     => $classContext,
            'classEnrollments' => $classEnrollments,
            'students'         => $students,
            'teachers'         => $teachers,
            'parents'          => $parents,
            'management'       => $management,
        ]);
    }

    public function profile() {
        $profileData = [
            'role'        => 'admin',
            'name'        => 'Alex Mendis',
            'id'          => 'ADM-001',
            'status'      => 'Active',
            'avatar'      => '/assets/images/admin.jpg',
            'eyebrow'     => 'System Administrator & IT Director',
            'sub'         => "Admin Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'Administrator Email', 'icon' => 'icon-mail',  'type' => 'email', 'value' => 'alex.m@lecole.edu'],
                ['label' => 'Primary Mobile',       'icon' => 'icon-phone', 'value' => '+94 77 123 4567'],
                ['label' => 'Central IT Office',   'icon' => 'icon-phone', 'value' => '+94 11 234 5678'],
                ['label' => 'Emergency NOC Line',  'icon' => 'icon-phone', 'value' => '+94 11 999 8877'],
            ],
            'personal' => [
                ['label' => 'Full Name',            'icon' => 'icon-user',        'value' => 'Alexander Ravindu Mendis',                   'readonly' => false],
                ['label' => 'NIC Number',           'icon' => 'icon-lockKeyhole', 'value' => '198516503921',                              'readonly' => true],
                ['label' => 'Date of Birth',        'icon' => 'icon-calendar',    'value' => 'Jun 14, 1985',                              'readonly' => true],
                ['label' => 'Nationality',          'icon' => 'icon-mapPin',      'value' => 'Sri Lankan',                                'readonly' => true],
                ['label' => 'Joined Date',          'icon' => 'icon-calendar',    'value' => 'Jan 01, 2021',                              'readonly' => true],
                ['label' => 'Qualifications',       'icon' => 'icon-award',       'value' => 'B.Sc in Computer Science, CISA Certified',   'readonly' => true],
                ['label' => 'Central Station',      'icon' => 'icon-building2',   'value' => 'Central Server Facility · Server Room A',   'readonly' => true],
            ],
            'roleSection' => [
                'title'     => 'Administrative Access & System Operations',
                'tintClass' => 'c-profile-tinted--midnight',
                'items' => [
                    ['label' => 'Access Level',         'icon' => 'icon-shieldCheck',  'value' => 'Full Superadmin Privileges · Tier 4 Root'],
                    ['label' => 'Department',           'icon' => 'icon-building2',    'value' => 'Central IT & Platform Operations'],
                    ['label' => 'Infrastructure Node',  'icon' => 'icon-building2',    'value' => 'Colombo Regional Cloud Node · AWS ap-southeast-1'],
                    ['label' => 'Security Audit Log',   'icon' => 'icon-lockKeyhole',  'value' => 'Level 4 Immutable Audit Log Active'],
                    ['label' => 'Reports To',           'icon' => 'icon-user',         'value' => 'Principal & Board of Trustees'],
                    ['label' => 'Session Security',     'icon' => 'icon-shieldCheck',  'value' => 'Mandatory 2FA · IP Whitelist Enforced'],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'System Scope & Platform Governance',
                    'icon'  => 'icon-shieldCheck',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Modules Managed',       'icon' => 'icon-building2',   'value' => 'User Directory, RBAC Roles, Cloud Infrastructure, Database Backups, Security Audits', 'readonly' => true, 'fullWidth' => true],
                        ['label' => 'Compliance Standard',   'icon' => 'icon-shieldCheck', 'value' => 'FERPA & GDPR Educational Data Compliance Verified',                                    'readonly' => true],
                        ['label' => 'Maintenance Window',   'icon' => 'icon-clock',       'value' => 'Weekly Scheduled Window: Sundays 02:00 – 04:00 UTC',                                   'readonly' => true],
                        ['label' => 'Disaster Recovery RPO', 'icon' => 'icon-lockKeyhole', 'value' => 'RPO: 15 mins · RTO: 1 hour (Certified Q1 2026)',                                      'readonly' => true],
                    ],
                ],
                [
                    'title' => 'Residential & Emergency Support Contacts',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Residential Address',   'icon' => 'icon-mapPin',          'value' => '88 Havelock Road, Colombo 05',        'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Backup Lead Admin',     'icon' => 'icon-user',            'value' => 'Sarah Perera (Lead DevOps Engineer)', 'readonly' => true],
                        ['label' => 'Backup Contact Number', 'icon' => 'icon-phone',           'value' => '+94 77 444 5566',                     'readonly' => true],
                        ['label' => 'NOC Escalation',        'icon' => 'icon-shieldCheck',     'value' => 'Level 1 Critical Response Unit',      'readonly' => true],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Administrator ID', 'icon' => 'icon-lockKeyhole', 'value' => 'ADM-001',              'readonly' => true],
                ['label' => 'Portal Role',      'icon' => 'icon-shieldCheck', 'value' => 'System Administrator', 'readonly' => true],
                ['label' => 'Account Created',  'icon' => 'icon-calendar',    'value' => 'Jan 01, 2021',         'readonly' => true],
                ['label' => 'Last Login',       'icon' => 'icon-clock',       'value' => 'Today, 23:45',         'readonly' => true],
            ],
        ];

        $this->view('admin/profile', [
            'currentRole'  => 'admin',
            'currentRoute' => '/admin/profile',
            'profileData'  => $profileData,
        ]);
    }
}
