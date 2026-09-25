<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/ExtracurricularModel.php';
require_once __DIR__ . '/../Models/PeopleModel.php';
require_once __DIR__ . '/../Models/AchievementModel.php';
require_once __DIR__ . '/../Models/AcademicModel.php';

class TeacherController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireRole('teacher');
    }

    public function index() {
        $this->dashboard();
    }

    public function students() {
        $grades        = PeopleModel::getGrades();
        $classContext  = PeopleModel::getClassContext();
        $enrollments   = PeopleModel::getClassEnrollments();
        $students      = PeopleModel::getStudents();

        $activeGradeId = 'g6';
        $activeClass   = '6-A';

        $this->view('teacher/students', [
            'currentRole'   => 'teacher',
            'currentRoute'  => '/teacher/students',
            'grades'        => $grades,
            'classContext'  => $classContext,
            'enrollments'   => $enrollments,
            'students'      => $students,
            'activeGradeId' => $activeGradeId,
            'activeClass'   => $activeClass,
        ]);
    }

    public function studentDetails() {
        $this->students();
    }

    public function marks() {
        $this->termMarks();
    }

    public function termMarks() {
        $students = [
            [
                'name'       => 'Nethmi Perera',
                'index'      => '2021/0456',
                'class'      => 'Class 6-A',
                'avatar'     => null,
                'initials'   => 'NP',
                'colorClass' => 'c-bg-card-slate',
                'marks'      => [
                    'English'         => 76,
                    'Mathematics'     => 84,
                    'Science'         => 63,
                    'Humanities'      => 72,
                    'Sinhala / Tamil' => 68,
                    'ICT'             => 89,
                ],
                'feedback'   => 'Strong overall performance. Needs a bit more focus on Science practicals.',
            ],
            [
                'name'       => 'Maya Kapoor',
                'index'      => '2022/0118',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'MK',
                'colorClass' => 'c-bg-card-taupe',
                'marks'      => [
                    'English'         => 88,
                    'Mathematics'     => 79,
                    'Science'         => 82,
                    'Humanities'      => 85,
                    'Sinhala / Tamil' => 74,
                    'ICT'             => 91,
                ],
                'feedback'   => 'Excellent work in Humanities and ICT. Consistent effort shown throughout term.',
            ],
            [
                'name'       => 'Amara Silva',
                'index'      => '2021/0203',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'AS',
                'colorClass' => 'c-bg-card-steel',
                'marks'      => [
                    'English'         => 92,
                    'Mathematics'     => 68,
                    'Science'         => 74,
                    'Humanities'      => 81,
                    'Sinhala / Tamil' => 79,
                    'ICT'             => 85,
                ],
                'feedback'   => 'High aptitude in languages. Additional practice recommended for Mathematics.',
            ],
            [
                'name'       => 'Dilan Jayasuriya',
                'index'      => '2021/0501',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'DJ',
                'colorClass' => 'c-bg-card-sand',
                'marks'      => [
                    'English'         => 65,
                    'Mathematics'     => 72,
                    'Science'         => 70,
                    'Humanities'      => 68,
                    'Sinhala / Tamil' => 64,
                    'ICT'             => 78,
                ],
                'feedback'   => 'Steady progress across all subjects. Active participant in classroom discussions.',
            ],
            [
                'name'       => 'Ishara Wickramasinghe',
                'index'      => '2021/0620',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'IW',
                'colorClass' => 'c-bg-card-seafoam',
                'marks'      => [
                    'English'         => 80,
                    'Mathematics'     => 85,
                    'Science'         => 88,
                    'Humanities'      => 76,
                    'Sinhala / Tamil' => 70,
                    'ICT'             => 94,
                ],
                'feedback'   => 'Outstanding performance in Science and ICT. Keep up the high standard.',
            ],
            [
                'name'       => 'John Doe',
                'index'      => '2021/0892',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'JD',
                'colorClass' => 'c-bg-card-sage',
                'marks'      => [
                    'English'         => 70,
                    'Mathematics'     => 62,
                    'Science'         => 66,
                    'Humanities'      => 69,
                    'Sinhala / Tamil' => 65,
                    'ICT'             => 72,
                ],
                'feedback'   => 'Satisfactory term marks. Regular revision will help improve core subjects.',
            ],
            [
                'name'       => 'Kavindu Perera',
                'index'      => '2021/0733',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'KP',
                'colorClass' => 'c-bg-card-slate',
                'marks'      => [
                    'English'         => 78,
                    'Mathematics'     => 81,
                    'Science'         => 80,
                    'Humanities'      => 75,
                    'Sinhala / Tamil' => 72,
                    'ICT'             => 86,
                ],
                'feedback'   => 'Shows great enthusiasm and analytical thinking in classroom activities.',
            ],
            [
                'name'       => 'Sahan Peiris',
                'index'      => '2021/0914',
                'class'      => 'Class 6-A',
                'avatar'     => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&auto=format&fit=crop&q=80',
                'initials'   => 'SP',
                'colorClass' => 'c-bg-card-steel',
                'marks'      => [
                    'English'         => 84,
                    'Mathematics'     => 77,
                    'Science'         => 75,
                    'Humanities'      => 82,
                    'Sinhala / Tamil' => 78,
                    'ICT'             => 88,
                ],
                'feedback'   => 'Consistently high engagement and well-written essays in Humanities.',
            ],
        ];

        $subjects = [
            'English',
            'Mathematics',
            'Science',
            'Humanities',
            'Sinhala / Tamil',
            'ICT'
        ];

        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $selectedTerm = 'Term 1';

        $this->view('teacher/term_marks', [
            'currentRole'  => 'teacher',
            'currentRoute' => '/teacher/marks',
            'students'     => $students,
            'subjects'     => $subjects,
            'terms'        => $terms,
            'selectedTerm' => $selectedTerm,
        ]);
    }

    public function achievements() {
        $students = [
            [
                'name'       => 'Amara Silva',
                'index'      => '2021/0203',
                'avatar'     => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
                'hasIssue'   => true,
                'itemType'   => 'Class',
                'colorClass' => 'c-bg-card-slate',
            ],
            [
                'name'       => 'Dilan Jayasuriya',
                'index'      => '2021/0501',
                'avatar'     => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                'hasIssue'   => false,
                'itemType'   => 'Class',
                'colorClass' => 'c-bg-card-steel',
            ],
            [
                'name'       => 'Ishara Wickramasinghe',
                'index'      => '2021/0620',
                'avatar'     => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
                'hasIssue'   => false,
                'itemType'   => 'Extracurricular',
                'colorClass' => 'c-bg-card-taupe',
            ],
            [
                'name'       => 'John Doe',
                'index'      => '2021/0892',
                'avatar'     => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
                'hasIssue'   => false,
                'itemType'   => 'Class',
                'colorClass' => 'c-bg-card-sand',
            ],
            [
                'name'       => 'Maya Kapoor',
                'index'      => '2022/0118',
                'avatar'     => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
                'hasIssue'   => false,
                'itemType'   => 'Extracurricular',
                'colorClass' => 'c-bg-card-seafoam',
            ],
            [
                'name'       => 'Nethmi Perera',
                'index'      => '2021/0456',
                'avatar'     => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&auto=format&fit=crop&q=80',
                'hasIssue'   => false,
                'itemType'   => 'Class',
                'colorClass' => 'c-bg-card-sage',
            ],
        ];

        // Highlighted profiles with pending issues automatically sort to the top
        usort($students, fn($a, $b) => (!empty($b['hasIssue']) <=> !empty($a['hasIssue'])));

        $this->view('teacher/achievements', [
            'currentRole'  => 'teacher',
            'currentRoute' => '/teacher/achievements',
            'students'     => $students,
            'achievements' => \App\Models\AchievementModel::getAll(),
        ]);
    }

    public function extracurricular() {
        // Assigned club managed by this faculty member (e.g. Cricket Club, ID: 3)
        $club  = ExtracurricularModel::getById(3) ?? (ExtracurricularModel::getAll()[0] ?? []);
        $clubs = [$club];

        $joinRequests = $club['joinRequests'] ?? [];
        $pendingCount = count($joinRequests);
        $totalMembers = $club['stats']['members'] ?? 45;

        $enrollmentMetrics = [
            [
                'color'   => 'sand',
                'icon'    => 'icon-users',
                'value'   => (string)$pendingCount,
                'label'   => 'Pending Join Requests',
                'valueId' => 'j-metric-pending-requests',
                'delay'   => 0,
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-check',
                'value'   => '12',
                'label'   => 'Enrolled This Term',
                'valueId' => 'j-metric-approved-requests',
                'delay'   => 50,
            ],
            [
                'color'   => 'maroon',
                'icon'    => 'icon-trophy',
                'value'   => '300',
                'label'   => 'Capacity Per Age Group',
                'valueId' => 'j-metric-capacity',
                'delay'   => 100,
            ],
        ];

        $this->view('teacher/extracurricular', [
            'currentRole'       => 'teacher',
            'currentRoute'      => '/teacher/extracurricular',
            'club'              => $club,
            'clubs'             => $clubs,
            'joinRequests'      => $joinRequests,
            'enrollmentMetrics' => $enrollmentMetrics,
            'staffAssignments'  => AcademicModel::getStaffAssignments(),
            'canModerate'       => false,
            'canCreate'         => true,
            'canEdit'           => true,
        ]);
    }

    public function dashboard() {
        // Metric Cards Data
        $metrics = [
            [
                'color'   => 'maroon',
                'icon'    => 'icon-book-open',
                'value'   => '4',
                'label'   => 'Active Subject Classes',
                'delay'   => 0,
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-users',
                'value'   => '168',
                'label'   => 'Total Assigned Students',
                'delay'   => 50,
            ],
            [
                'color'   => 'sand',
                'icon'    => 'icon-trendingUp',
                'value'   => '82%',
                'label'   => 'Average Term Mark',
                'delay'   => 100,
            ],
            [
                'color'   => 'sunshine',
                'icon'    => 'icon-usersRound',
                'value'   => '94',
                'label'   => 'Total Enrolled Players',
                'delay'   => 150,
            ],
        ];

        // Column Chart Data — Full 12 Academic Subjects Across All 3 Terms
        $termsData = [
            'Term 1' => [
                ['label' => 'English',        'avg' => 78, 'high' => 88],
                ['label' => 'Math',           'avg' => 82, 'high' => 91],
                ['label' => 'Science',        'avg' => 74, 'high' => 89],
                ['label' => 'Religion',       'avg' => 69, 'high' => 80],
                ['label' => 'History',        'avg' => 67, 'high' => 79],
                ['label' => 'Sinhala',        'avg' => 75, 'high' => 88],
                ['label' => 'Geography',      'avg' => 73, 'high' => 87],
                ['label' => 'Civic',          'avg' => 74, 'high' => 88],
                ['label' => 'Aesthetics',     'avg' => 76, 'high' => 89],
                ['label' => 'ICT',            'avg' => 82, 'high' => 90],
                ['label' => 'Health Science', 'avg' => 75, 'high' => 88],
                ['label' => 'Tamil',          'avg' => 74, 'high' => 87],
            ],
            'Term 2' => [
                ['label' => 'English',        'avg' => 82, 'high' => 90],
                ['label' => 'Math',           'avg' => 86, 'high' => 94],
                ['label' => 'Science',        'avg' => 76, 'high' => 91],
                ['label' => 'Religion',       'avg' => 71, 'high' => 82],
                ['label' => 'History',        'avg' => 71, 'high' => 82],
                ['label' => 'Sinhala',        'avg' => 78, 'high' => 90],
                ['label' => 'Geography',      'avg' => 78, 'high' => 90],
                ['label' => 'Civic',          'avg' => 78, 'high' => 90],
                ['label' => 'Aesthetics',     'avg' => 78, 'high' => 90],
                ['label' => 'ICT',            'avg' => 85, 'high' => 93],
                ['label' => 'Health Science', 'avg' => 78, 'high' => 90],
                ['label' => 'Tamil',          'avg' => 78, 'high' => 90],
            ],
            'Term 3' => [
                ['label' => 'English',        'avg' => 85, 'high' => 93],
                ['label' => 'Math',           'avg' => 88, 'high' => 96],
                ['label' => 'Science',        'avg' => 80, 'high' => 94],
                ['label' => 'Religion',       'avg' => 75, 'high' => 86],
                ['label' => 'History',        'avg' => 76, 'high' => 87],
                ['label' => 'Sinhala',        'avg' => 82, 'high' => 93],
                ['label' => 'Geography',      'avg' => 81, 'high' => 92],
                ['label' => 'Civic',          'avg' => 83, 'high' => 94],
                ['label' => 'Aesthetics',     'avg' => 84, 'high' => 95],
                ['label' => 'ICT',            'avg' => 89, 'high' => 96],
                ['label' => 'Health Science', 'avg' => 82, 'high' => 93],
                ['label' => 'Tamil',          'avg' => 81, 'high' => 92],
            ],
        ];

        // Column Chart Configuration
        $chartConfig = [
            'id'         => 'j-teacher-perf-chart',
            'title'      => 'Academic Performance (Class 6-A)',
            'xAxisTitle' => 'SUBJECTS',
            'yAxisTitle' => 'SCORE (%)',
            'maxVal'     => 100,
            'ticks'      => [0, 25, 50, 75, 100],
            'series'     => [
                ['key' => 'avg',  'label' => 'Class Average', 'color' => BRAND_MAROON],
                ['key' => 'high', 'label' => 'Highest Score', 'color' => BRAND_SKYBLUE],
            ],
            'data'       => $termsData['Term 2'],
            'termsData'  => $termsData,
            
            // Role-Specific Feature: Term Selector Dropdown in Header Slot
            'headerExtra' => '
                <div class="c-select c-select--cream term-dropdown j-term-dropdown">
                  <button type="button" class="c-select__trigger j-term-dropdown-btn" aria-haspopup="listbox" aria-expanded="false">
                    <span class="j-select-value">Term 2</span>
                    <svg class="c-icon c-select__chevron" width="14" height="14"><use href="#icon-chevronDown"/></svg>
                  </button>
                  <div class="c-select__menu j-term-dropdown-menu" role="listbox" hidden>
                    <button type="button" class="c-select__option j-term-option" data-value="Term 1" role="option"><span>Term 1</span></button>
                    <button type="button" class="c-select__option j-term-option c-is-selected" data-value="Term 2" role="option"><span>Term 2</span></button>
                    <button type="button" class="c-select__option j-term-option" data-value="Term 3" role="option"><span>Term 3</span></button>
                  </div>
                </div>',
        ];

        // Calendar Configuration (Teacher: Can add and manage events)
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

        // Donut / Pie Charts Data (Teacher Dashboard)
        $donutEngagement = [
            'id'          => 'j-donut-sports',
            'title'       => 'Extra-curricular Activity Engagement',
            'totalLabel'  => 'Students engaged in activities',
            'centerLabel' => 'TOTAL',
            'total'       => 30,
            'slices'      => [
                [
                    'name'  => 'Cricket',
                    'value' => 12,
                    'color' => BRAND_LIGHTBLUE,
                    'd'     => 'M 153.67 144.94 A 70 70 0 0 0 100.00 30.00 L 100.00 50.00 A 50 50 0 0 1 138.34 132.10 Z',
                ],
                [
                    'name'  => 'Athletics',
                    'value' => 8,
                    'color' => BRAND_TERRACOTTA,
                    'd'     => 'M 53.56 152.38 A 70 70 0 0 0 149.55 149.44 L 135.39 135.32 A 50 50 0 0 1 66.83 137.41 Z',
                ],
                [
                    'name'  => 'Debate',
                    'value' => 5,
                    'color' => BRAND_MAROON,
                    'd'     => 'M 34.88 74.31 A 70 70 0 0 0 49.17 148.13 L 63.69 134.38 A 50 50 0 0 1 53.49 81.65 Z',
                ],
                [
                    'name'  => 'Arts & Drama',
                    'value' => 5,
                    'color' => BRAND_MOSS,
                    'd'     => 'M 93.90 30.27 A 70 70 0 0 0 37.37 68.73 L 55.26 77.67 A 50 50 0 0 1 95.64 50.19 Z',
                ],
            ],
        ];

        $donutCricketGrade = [
            'id'          => 'j-donut-clubs',
            'title'       => 'Grade wise engagement of cricket',
            'totalLabel'  => 'Players in cricket',
            'centerLabel' => 'TOTAL',
            'total'       => 94,
            'slices'      => [
                [
                    'name'  => 'Grade 6',
                    'value' => 17,
                    'color' => BRAND_TERRACOTTA,
                    'd'     => 'M 100.00 30.00 A 70 70 0 0 1 160.62 65.00 L 143.30 75.00 A 50 50 0 0 0 100.00 50.00 Z',
                ],
                [
                    'name'  => 'Grade 7',
                    'value' => 18,
                    'color' => BRAND_SKYBLUE,
                    'd'     => 'M 160.62 65.00 A 70 70 0 0 1 160.62 135.00 L 143.30 125.00 A 50 50 0 0 0 143.30 75.00 Z',
                ],
                [
                    'name'  => 'Grade 8',
                    'value' => 19,
                    'color' => BRAND_SUNSHINE,
                    'd'     => 'M 160.62 135.00 A 70 70 0 0 1 100.00 170.00 L 100.00 150.00 A 50 50 0 0 0 143.30 125.00 Z',
                ],
                [
                    'name'  => 'Grade 9',
                    'value' => 17,
                    'color' => BRAND_MIDNIGHT,
                    'd'     => 'M 100.00 170.00 A 70 70 0 0 1 39.38 135.00 L 56.70 125.00 A 50 50 0 0 0 100.00 150.00 Z',
                ],
                [
                    'name'  => 'Grade 10',
                    'value' => 13,
                    'color' => BRAND_MOSS,
                    'd'     => 'M 39.38 135.00 A 70 70 0 0 1 39.38 65.00 L 56.70 75.00 A 50 50 0 0 0 56.70 125.00 Z',
                ],
                [
                    'name'  => 'Grade 11',
                    'value' => 10,
                    'color' => BRAND_LIGHTBLUE,
                    'd'     => 'M 39.38 65.00 A 70 70 0 0 1 100.00 30.00 L 100.00 50.00 A 50 50 0 0 0 56.70 75.00 Z',
                ],
            ],
        ];

        // Upcoming Events Data (Teacher)
        $upcomingEvents = [
            ['day' => '17', 'month' => 'JUN', 'name' => 'Mathematics Examination', 'tag' => 'ACADEMIC', 'tagColor' => 'sand'],
            ['day' => '20', 'month' => 'JUN', 'name' => 'Term 1 Exams Begin', 'tag' => 'ACADEMIC', 'tagColor' => 'sky'],
            ['day' => '28', 'month' => 'JUN', 'name' => 'Science Lab Practical', 'tag' => 'ACADEMIC', 'tagColor' => 'terracotta'],
        ];

        $this->view('teacher/dashboard', [
            'currentRole'       => 'teacher',
            'currentRoute'      => '/teacher/dashboard',
            'metrics'           => $metrics,
            'chartConfig'       => $chartConfig,
            'calendarConfig'    => $calendarConfig,
            'donutEngagement'   => $donutEngagement,
            'donutCricketGrade' => $donutCricketGrade,
            'upcomingEvents'    => $upcomingEvents,
        ]);
    }

    public function notice() {

        $notices    = NoticeModel::getForRole('teacher');
        $categories = NoticeModel::getCategories();
        $audiences  = ['All', 'Students', 'Parents'];

        $this->view('teacher/notice', [
            'currentRole'  => 'teacher',
            'currentRoute' => '/teacher/notice',
            'notices'      => $notices,
            'categories'   => $categories,
            'audiences'    => $audiences,
        ]);
    }

    public function noticeBoard() {
        $this->notice();
    }

    public function feedback() {
        require_once __DIR__ . '/../Models/FeedbackModel.php';
        $feedbacks = \App\Models\FeedbackModel::getForTeacher(1);
        $filterTypes = \App\Models\FeedbackModel::getTypes();

        $this->view('teacher/feedback', [
            'currentRole'  => 'teacher',
            'currentRoute' => '/teacher/feedback',
            'feedbacks'    => $feedbacks,
            'filterTypes'  => $filterTypes,
        ]);
    }

    public function profile() {
        $profileData = [
            'role'        => 'teacher',
            'name'        => 'Havindu Rajapaksha',
            'id'          => 'TCH-047',
            'status'      => 'Active',
            'avatar'      => '/assets/images/teacher.jpg',
            'eyebrow'     => 'Senior Teacher',
            'sub'         => "Teacher Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'Institutional Email', 'icon' => 'icon-mail',  'type' => 'email', 'value' => 'h.rajapaksha@lecole.edu'],
                ['label' => 'Personal Email',      'icon' => 'icon-mail',  'type' => 'email', 'value' => 'h.rajapaksha.personal@gmail.com'],
                ['label' => 'Mobile Number',       'icon' => 'icon-phone', 'value' => '+94 71 345 6789'],
            ],
            'personal' => [
                ['label' => 'Full Name',            'icon' => 'icon-user',        'value' => 'Havindu Rajapaksha',                        'readonly' => false],
                ['label' => 'NIC Number',           'icon' => 'icon-lockKeyhole', 'value' => '199083245621',                              'readonly' => true],
                ['label' => 'Date of Birth',        'icon' => 'icon-calendar',    'value' => 'Mar 22, 1990',                              'readonly' => true],
                ['label' => 'Nationality',          'icon' => 'icon-mapPin',      'value' => 'Sri Lankan',                                'readonly' => true],
                ['label' => 'Experience (Years)',   'icon' => 'icon-award',       'value' => '8 Years',                                   'readonly' => true],
                ['label' => 'Joined Date',          'icon' => 'icon-calendar',    'value' => 'Feb 14, 2018',                              'readonly' => true],
                ['label' => 'Qualifications',       'icon' => 'icon-award',       'value' => 'B.Ed (Mathematics), Dip. in Ed. Leadership','readonly' => true],
                ['label' => 'Office Address',       'icon' => 'icon-building2',   'value' => 'Main Building · Staff Room 204',            'readonly' => true],
            ],
            'roleSection' => [
                'title'     => 'Teaching Assignments & Responsibilities',
                'tintClass' => 'c-profile-tinted--sunshine',
                'items' => [
                    ['label' => 'Primary Subject',     'icon' => 'icon-bookOpen',      'value' => 'Mathematics'],
                    ['label' => 'Secondary Subject',   'icon' => 'icon-bookOpen',      'value' => 'ICT'],
                    ['label' => 'Class Teacher Role',  'icon' => 'icon-usersRound',    'value' => 'Class Teacher — In charge of Grade 9-A'],
                    ['label' => 'Teaches Grades',      'icon' => 'icon-graduationCap', 'value' => 'Grades 7 – 11'],
                    ['label' => 'Weekly Workload',     'icon' => 'icon-calendar',      'value' => '24 instructional periods'],
                    ['label' => 'TIC Responsibility',  'icon' => 'icon-extracurricular','value' => 'Senior Debating Society TIC'],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Residential & Emergency Contact Details',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Residential Address',      'icon' => 'icon-mapPin',          'value' => '12 Lake Drive, Colombo 05', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Emergency Contact Name',   'icon' => 'icon-user',            'value' => 'Nimal Rajapaksha',          'readonly' => false],
                        ['label' => 'Emergency Contact Number', 'icon' => 'icon-phone',           'value' => '+94 77 456 7890',          'readonly' => false],
                        ['label' => 'Emergency Relationship',   'icon' => 'icon-heartHandshake',  'value' => 'Spouse',                    'readonly' => false],
                    ],
                ],
                [
                    'title' => 'Academic Background & Professional Certifications',
                    'icon'  => 'icon-award',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Degree / Title',        'icon' => 'icon-award',     'value' => 'B.Sc in Mathematics & Statistics',         'readonly' => true],
                        ['label' => 'Awarding Institution',  'icon' => 'icon-building2', 'value' => 'University of Colombo',                   'readonly' => true],
                        ['label' => 'Graduation Year',       'icon' => 'icon-calendar',  'value' => '2015 · First Class Honours',              'readonly' => true],
                        ['label' => 'Professional Training', 'icon' => 'icon-award',     'value' => 'National Diploma in Teaching · NIE Maharagama (2017)', 'readonly' => true, 'fullWidth' => true],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Staff ID',        'icon' => 'icon-lockKeyhole',   'value' => 'TCH-047',        'readonly' => true],
                ['label' => 'Portal Role',     'icon' => 'icon-graduationCap', 'value' => 'Teacher',        'readonly' => true],
                ['label' => 'Account Created', 'icon' => 'icon-calendar',      'value' => 'Feb 14, 2018',   'readonly' => true],
                ['label' => 'Last Login',      'icon' => 'icon-clock',         'value' => 'Today, 22:10',   'readonly' => true],
            ],
        ];

        $this->view('teacher/profile', [
            'currentRole'  => 'teacher',
            'currentRoute' => '/teacher/profile',
            'profileData'  => $profileData,
        ]);
    }
}
