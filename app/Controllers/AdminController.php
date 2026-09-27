<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/VerifyModel.php';
require_once __DIR__ . '/../Models/AuditModel.php';
require_once __DIR__ . '/../Models/AcademicModel.php';
require_once __DIR__ . '/../Models/ExtracurricularModel.php';
require_once __DIR__ . '/../Models/PeopleModel.php';
require_once __DIR__ . '/AcademicCrudTrait.php';
require_once __DIR__ . '/PeopleCrudTrait.php';
require_once __DIR__ . '/NoticeCrudTrait.php';
require_once __DIR__ . '/CalendarEventCrudTrait.php';
require_once __DIR__ . '/../Models/CalendarEventModel.php';

class AdminController extends Controller {
    use AcademicCrudTrait, PeopleCrudTrait, NoticeCrudTrait, CalendarEventCrudTrait;

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

        $this->view('admin/dashboard', array_merge([
            'currentRole'    => 'admin',
            'currentRoute'   => '/admin/dashboard',
            'metrics'        => $metrics,
            'chartConfig'    => $chartConfig,
        ], $this->getSharedDashboardData()));
    }

    public function notice() {
        $notices    = NoticeModel::getForRole('admin');
        $categories = NoticeModel::getCategories();
        $audiences  = NoticeModel::getAudiences();
        $clubs      = NoticeModel::getExtracurricularActivities();
        $classes    = NoticeModel::getAcademicClasses();

        $this->view('admin/notice', [
            'currentRole'  => 'admin',
            'currentRoute' => '/admin/notice',
            'notices'      => $notices,
            'categories'   => $categories,
            'audiences'    => $audiences,
            'clubs'        => $clubs,
            'classes'      => $classes,
        ]);
    }

    public function noticeBoard() {
        $this->notice();
    }

    public function notices() {
        $this->notice();
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
            'canAddEvent'  => true,
            'scopeOptions' => CalendarEventModel::getScopeOptionsForStaff(),
            'initialDate'  => date('Y-m-d'),
            'viewDate'     => date('Y-m-01'),
            'events'       => CalendarEventModel::getAllEvents(),
        ];

        $curriculumGroups = AcademicModel::getCurriculumGroups();
        $classTeachers    = AcademicModel::getClassTeachers();
        $classEnrollments = AcademicModel::getClassEnrollments();
        $staffAssignments = AcademicModel::getStaffAssignments();
        $subjectTeachers  = AcademicModel::getSubjectTeachers();

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
            'subjectTeachers'  => $subjectTeachers,
        ]);
    }

    public function extracurricular() {
        $type = $_GET['type'] ?? 'All';
        $search = $_GET['q'] ?? '';
        $clubs = ExtracurricularModel::getAll($type, $search);
        $staffAssignments = AcademicModel::getStaffAssignments();

        $scopeType = ($_GET['type'] ?? '') === 'sport' ? 'sport' : 'club';
        $itemId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $selectedClub = $itemId ? (ExtracurricularModel::getById($itemId) ?? ($clubs[0] ?? [])) : ($clubs[0] ?? []);

        $calendarConfig = [
            'canAddEvent'  => true,
            'scopeOptions' => CalendarEventModel::getScopeOptionsForStaff(),
            'initialDate'  => date('Y-m-d'),
            'viewDate'     => date('Y-m-01'),
            'events'       => $itemId ? ($scopeType === 'sport' ? CalendarEventModel::getEventsForSport($itemId) : CalendarEventModel::getEventsForClub($itemId)) : CalendarEventModel::getAllEvents(),
            'fixedScope'   => $itemId ? ['type' => $scopeType, 'id' => $itemId] : null,
        ];

        $this->view('admin/extracurricular', [
            'currentRole'      => 'admin',
            'currentRoute'     => '/admin/extracurricular',
            'clubs'            => $clubs,
            'club'             => $selectedClub,
            'staffAssignments' => $staffAssignments,
            'canModerate'      => true,
            'canCreate'        => true,
            'selectedType'     => $type,
            'searchQuery'      => $search,
            'scopeType'        => $scopeType,
            'calendarConfig'   => $calendarConfig,
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
        require_once __DIR__ . '/../Models/ProfileModel.php';
        $profileData = ProfileModel::getProfileData('admin', $this->getUser(), $this->getProfile());

        $this->view('admin/profile', [
            'currentRole'  => 'admin',
            'currentRoute' => '/admin/profile',
            'profileData'  => $profileData,
        ]);
    }
}
