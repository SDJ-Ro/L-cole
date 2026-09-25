<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/AcademicModel.php';
require_once __DIR__ . '/../Models/ExtracurricularModel.php';
require_once __DIR__ . '/../Models/PeopleModel.php';
require_once __DIR__ . '/../Models/ComplaintModel.php';
require_once __DIR__ . '/../Models/CertificateModel.php';


class ManagementController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireRole('management');
    }

    public function index() {
        $this->dashboard();
    }

    public function extracurricular() {
        $type = $_GET['type'] ?? 'All';
        $search = $_GET['q'] ?? '';
        $clubs = ExtracurricularModel::getAll($type, $search);

        $this->view('management/extracurricular', [
            'currentRole'  => 'management',
            'currentRoute' => '/management/extracurricular',
            'clubs'        => $clubs,
            'canModerate'  => true,
            'canCreate'    => true,
            'canDelete'    => false,
            'selectedType' => $type,
            'searchQuery'  => $search,
        ]);
    }

    public function dashboard() {
        // Metric Cards Data
        $metrics = [
            [
                'color'   => 'sand',
                'icon'    => 'icon-graduationCap',
                'value'   => '2,845',
                'label'   => 'Total Students',
                'delay'   => 0,
            ],
            [
                'color'   => 'maroon',
                'icon'    => 'icon-usersRound',
                'value'   => '157',
                'label'   => 'Total Staff Members',
                'delay'   => 50,
            ],
            [
                'color'   => 'moss',
                'icon'    => 'icon-extracurricular',
                'value'   => '6',
                'label'   => 'Extracurricular Programs',
                'delay'   => 100,
            ],
            [
                'color'   => 'sunshine',
                'icon'    => 'icon-shieldCheck',
                'value'   => '12',
                'label'   => 'Pending Verifications',
                'delay'   => 150,
            ],
        ];

        // Column Chart Data (Grade 6 to 11 Enrollment)
        $chartConfig = [
            'id'         => 'j-management-enrollment-chart',
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

        // Calendar Configuration (Management: Can add and manage events)
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

        $this->view('management/dashboard', [
            'currentRole'    => 'management',
            'currentRoute'   => '/management/dashboard',
            'metrics'        => $metrics,
            'chartConfig'    => $chartConfig,
            'calendarConfig' => $calendarConfig,
            'donutSports'    => $donutSports,
            'donutClubs'     => $donutClubs,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function notice() {

        $notices    = NoticeModel::getForRole('management');
        $categories = NoticeModel::getCategories();
        $audiences  = NoticeModel::getAudiences();

        $this->view('management/notice', [
            'currentRole'  => 'management',
            'currentRoute' => '/management/notice',
            'notices'      => $notices,
            'categories'   => $categories,
            'audiences'    => $audiences,
        ]);
    }

    public function noticeBoard() {
        $this->notice();
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

        $this->view('management/academic', [
            'currentRole'      => 'management',
            'currentRoute'     => '/management/academic',
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

    public function people() {
        $grades           = PeopleModel::getGrades();
        $classContext     = PeopleModel::getClassContext();
        $classEnrollments = PeopleModel::getClassEnrollments();
        $students         = PeopleModel::getStudents();
        $teachers         = PeopleModel::getTeachers();
        $parents          = PeopleModel::getParents();

        $this->view('management/people', [
            'currentRole'      => 'management',
            'currentRoute'     => '/management/people',
            'allowedTabs'      => ['Students', 'Teachers', 'Parents'],
            'activeTab'        => 'Students',
            'grades'           => $grades,
            'classContext'     => $classContext,
            'classEnrollments' => $classEnrollments,
            'students'         => $students,
            'teachers'         => $teachers,
            'parents'          => $parents,
        ]);
    }

    public function complaints() {
        $complaints = ComplaintModel::getAll();
        $metrics = ComplaintModel::getMetrics();
        $categories = ComplaintModel::getCategories();

        $this->view('management/complaints', [
            'currentRole'  => 'management',
            'currentRoute' => '/management/complaints',
            'complaints'   => $complaints,
            'metrics'      => $metrics,
            'categories'   => $categories,
        ]);
    }

    public function characterCertificate() {
        $certificates = CertificateModel::getAll();

        $totalCount      = count($certificates);
        $pendingCount    = count(array_filter($certificates, fn($c) => ($c['status'] ?? '') === 'Pending review'));
        $issuedCount     = count(array_filter($certificates, fn($c) => ($c['status'] ?? '') === 'Issued'));
        $leavingCount    = count(array_filter($certificates, fn($c) => stripos($c['reason'] ?? '', 'leav') !== false));
        $graduatingCount = count(array_filter($certificates, fn($c) => stripos($c['reason'] ?? '', 'graduat') !== false));

        $this->view('management/character_certificate', [
            'currentRole'     => 'management',
            'currentRoute'    => '/management/character-certificate',
            'certificates'    => $certificates,
            'counts'          => [
                'all'        => $totalCount,
                'pending'    => $pendingCount,
                'issued'     => $issuedCount,
                'leaving'    => $leavingCount,
                'graduating' => $graduatingCount,
            ],
        ]);
    }

    public function profile() {
        $profileData = [
            'role'        => 'management',
            'name'        => 'Dr. Chaminda De Silva',
            'id'          => 'MGT-003',
            'status'      => 'Active',
            'avatar'      => '/assets/images/management.jpg',
            'eyebrow'     => 'School Principal & Executive Director',
            'sub'         => "Management Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'Institutional Email', 'icon' => 'icon-mail',  'type' => 'email', 'value' => 'principal@lecole.edu'],
                ['label' => 'Personal Email',      'icon' => 'icon-mail',  'type' => 'email', 'value' => 'chaminda.personal@gmail.com'],
                ['label' => 'Direct Executive Line','icon' => 'icon-phone', 'value' => '+94 11 789 0123'],
                ['label' => 'Personal Mobile',     'icon' => 'icon-phone', 'value' => '+94 77 987 6543'],
            ],
            'personal' => [
                ['label' => 'Full Name',         'icon' => 'icon-user',        'value' => 'Dr. Chaminda De Silva',                     'readonly' => false],
                ['label' => 'NIC Number',        'icon' => 'icon-lockKeyhole', 'value' => '197239201948',                             'readonly' => true],
                ['label' => 'Date of Birth',     'icon' => 'icon-calendar',    'value' => 'Sep 03, 1972',                              'readonly' => true],
                ['label' => 'Qualifications',    'icon' => 'icon-award',       'value' => 'Ph.D Education Policy, M.Ed Admin',        'readonly' => true],
                ['label' => 'Joined Date',       'icon' => 'icon-calendar',    'value' => 'Jan 01, 2015',                              'readonly' => true],
                ['label' => 'Nationality',       'icon' => 'icon-mapPin',      'value' => 'Sri Lankan',                                'readonly' => true],
                ['label' => 'Executive Office',  'icon' => 'icon-building2',   'value' => 'Main Building · Senior Executive Suite 101','readonly' => true],
            ],
            'roleSection' => [
                'title'     => 'Leadership & Institutional Governance',
                'tintClass' => 'c-profile-tinted--maroon',
                'items' => [
                    ['label' => 'Executive Job Title', 'icon' => 'icon-building2',   'value' => 'Principal & Executive Director'],
                    ['label' => 'Department',          'icon' => 'icon-usersRound',  'value' => 'Senior Institutional Administration'],
                    ['label' => 'Reports To',           'icon' => 'icon-user',        'value' => 'Board of Governors & Ministry of Education'],
                    ['label' => 'Tenure in Leadership', 'icon' => 'icon-calendar',    'value' => '11 Years at L\'École'],
                    ['label' => 'Governance Scope',    'icon' => 'icon-shieldCheck', 'value' => 'Academic Oversight, Governance, Staff & Budget Approvals'],
                    ['label' => 'Campus Station',       'icon' => 'icon-mapPin',      'value' => 'Main Building · Admissions & Executive Wing'],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Campus Workplace & Residential Details',
                    'icon'  => 'icon-building2',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Office Address',      'icon' => 'icon-building2', 'value' => 'Administrative Complex, 1st Floor, Executive Wing', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Residential Address', 'icon' => 'icon-mapPin',    'value' => '14 Palm Grove, Colombo 07',                          'readonly' => false, 'fullWidth' => true],
                    ],
                ],
                [
                    'title' => 'Emergency Contact & Executive Proxy',
                    'icon'  => 'icon-shieldCheck',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Emergency Contact Name',   'icon' => 'icon-user',            'value' => 'Dr. Emma De Silva',               'readonly' => false],
                        ['label' => 'Emergency Contact Number', 'icon' => 'icon-phone',           'value' => '+94 77 000 0091',                 'readonly' => false],
                        ['label' => 'Emergency Relationship',   'icon' => 'icon-heartHandshake',  'value' => 'Spouse / Next of Kin',            'readonly' => false],
                        ['label' => 'Executive Proxy',          'icon' => 'icon-usersRound',      'value' => 'Alex Thompson (Deputy Director)', 'readonly' => true],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Management ID',   'icon' => 'icon-lockKeyhole', 'value' => 'MGT-003',          'readonly' => true],
                ['label' => 'Portal Role',     'icon' => 'icon-building2',   'value' => 'Management Panel', 'readonly' => true],
                ['label' => 'Account Created', 'icon' => 'icon-calendar',    'value' => 'Jan 01, 2015',      'readonly' => true],
                ['label' => 'Last Login',      'icon' => 'icon-clock',       'value' => 'Today, 21:30',      'readonly' => true],
            ],
        ];

        $this->view('management/profile', [
            'currentRole'  => 'management',
            'currentRoute' => '/management/profile',
            'profileData'  => $profileData,
        ]);
    }
}

