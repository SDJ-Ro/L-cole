<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/ExtracurricularModel.php';
require_once __DIR__ . '/../Models/AchievementModel.php';
require_once __DIR__ . '/../Models/CertificateModel.php';

class StudentController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireRole('student');
    }

    public function index() {
        $this->dashboard();
    }

    public function extracurricular() {
        $clubs = ExtracurricularModel::getForStudent();

        $this->view('student/extracurricular', [
            'currentRole'  => 'student',
            'currentRoute' => '/student/extracurricular',
            'clubs'        => $clubs,
            'canModerate'  => false,
            'canCreate'    => false,
        ]);
    }

    public function sports() {
        $this->extracurricular();
    }

    public function clubs() {
        $this->extracurricular();
    }

    public function sportsAndClubs() {
        $this->extracurricular();
    }

    public function academic() {
        $grades = [6, 7, 8, 9, 10, 11];
        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $subjects = ['English Language', 'Mathematics', 'Science', 'History', 'Geography', 'ICT'];
        $teachers = ['Mrs. Ishara Gunasekara', 'Mr. K. Perera', 'Mrs. D. Jayawardena', 'Mr. R. Silva', 'Mrs. M. Fernando'];

        $academicData = [];
        foreach ($grades as $gIdx => $grade) {
            $academicData[$grade] = ['terms' => [], 'trend' => [], 'positions' => [], 'feedback' => []];

            foreach ($terms as $tIdx => $term) {
                $rows = [];
                foreach ($subjects as $sIdx => $subject) {
                    $seed = ($grade * 131 + $tIdx * 37 + $sIdx * 17) % 100;
                    $base = 72 + ($gIdx * 2) + ($tIdx * 1.5);
                    $marks = min(98, (int)round($base + ($seed / 100) * 18));
                    $highest = min(100, $marks + 3 + ($seed % 6));
                    $rows[] = [
                        'subject'     => $subject,
                        'marks'       => $marks,
                        'highest'     => $highest,
                        'mark'        => $marks,
                        'highestMark' => $highest
                    ];
                }
                $academicData[$grade]['terms'][$term] = $rows;

                $scored = array_sum(array_column($rows, 'marks'));
                $myAvg = count($rows) > 0 ? (int)round($scored / count($rows)) : 80;
                $gradeAvg = max(50, $myAvg - 6 - (($grade * 3 + $tIdx) % 5));
                $academicData[$grade]['trend'][] = [
                    'term'  => $term,
                    'mine'  => $myAvg,
                    'grade' => $gradeAvg
                ];

                $pos = max(1, min(42, 22 - (int)round(($myAvg - 70) * 0.8) + (($grade + $tIdx) % 3)));
                $academicData[$grade]['positions'][$term] = $pos;

                $teacher = $teachers[($grade + $tIdx) % count($teachers)];
                $feedbackTexts = [
                    'Term 1' => "A solid performance overall with consistent dedication across key subject areas. Demonstrates strong analytical aptitude and regular engagement in class discussions.",
                    'Term 2' => "Commendable progress demonstrated throughout the term. Shows disciplined study habits and has made steady gains in technical and scientific competencies.",
                    'Term 3' => "Exemplary performance during the concluding term. Conscientious, diligent, and continues to set a strong benchmark for peers."
                ];

                $academicData[$grade]['feedback'][$term] = [
                    'name' => $teacher,
                    'date' => ($term === 'Term 1' ? 'Apr 17, 2024' : ($term === 'Term 2' ? 'Aug 14, 2024' : 'Dec 08, 2024')),
                    'text' => $feedbackTexts[$term]
                ];
            }
        }

        $selectedGrade = 6;
        $selectedTerm = 'Term 1';
        $initialRows = $academicData[$selectedGrade]['terms'][$selectedTerm];
        $initialScored = array_sum(array_column($initialRows, 'marks'));
        $initialOutOf = count($initialRows) * 100;
        $initialAverage = number_format(($initialScored / $initialOutOf) * 100, 1) . '%';
        $rawPos = $academicData[$selectedGrade]['positions'][$selectedTerm];
        $rem100 = $rawPos % 100;
        $suffix = 'th';
        if ($rem100 < 11 || $rem100 > 13) {
            if ($rawPos % 10 === 1) $suffix = 'st';
            elseif ($rawPos % 10 === 2) $suffix = 'nd';
            elseif ($rawPos % 10 === 3) $suffix = 'rd';
        }
        $initialPosition = $rawPos . $suffix;

        $recordData = [
            'selectedGrade'   => $selectedGrade,
            'selectedTerm'    => $selectedTerm,
            'marks'           => $initialRows,
            'feedback'        => $academicData[$selectedGrade]['feedback'][$selectedTerm]['text'],
            'feedbackDate'    => $academicData[$selectedGrade]['feedback'][$selectedTerm]['date'],
            'feedbackTeacher' => $academicData[$selectedGrade]['feedback'][$selectedTerm]['name']
        ];

        $this->view('student/academic', [
            'currentRole'     => 'student',
            'currentRoute'    => '/student/academic',
            'academicData'    => $academicData,
            'selectedGrade'   => $selectedGrade,
            'selectedTerm'    => $selectedTerm,
            'recordData'      => $recordData,
            'initialScored'   => $initialScored,
            'initialOutOf'    => $initialOutOf,
            'initialAverage'  => $initialAverage,
            'initialPosition' => $initialPosition,
        ]);
    }

    public function achievements() {
        $achievements = \App\Models\AchievementModel::getAll();
        $metrics = \App\Models\AchievementModel::getMetrics($achievements);

        $this->view('student/achievements', [
            'currentRole'  => 'student',
            'currentRoute' => '/student/achievements',
            'achievements' => $achievements,
            'metrics'      => $metrics,
        ]);
    }

    public function certificates() {
        $studentId   = '2021/0456';
        $certificate = CertificateModel::getById($studentId) ?? (CertificateModel::getAll()[0] ?? []);

        $this->view('student/certificates', [
            'currentRole'  => 'student',
            'currentRoute' => '/student/certificates',
            'certificate'  => $certificate,
        ]);
    }

    public function profile() {
        $profileData = [
            'role'         => 'student',
            'name'         => 'Jason Mendis',
            'id'           => 'STU-001',
            'status'       => 'Active',
            'avatar'       => '/assets/images/students.jpg',
            'eyebrow'      => 'Student · Grade 10-A',
            'sub'          => "Student Portal · L'École School Management",
            'editable'     => false,
            'showPassword' => false,
            'contact' => [
                ['label' => 'Student Email',       'icon' => 'icon-mail',  'type' => 'email', 'value' => 'jason.mendis@student.lecole.edu'],
                ['label' => 'Parent Contact',      'icon' => 'icon-phone', 'value' => '+94 77 234 5678 (Mother)'],
                ['label' => 'Emergency Line',      'icon' => 'icon-phone', 'value' => '+94 11 456 7890'],
            ],
            'personal' => [
                ['label' => 'Full Name',           'icon' => 'icon-user',      'value' => 'Jason Ravindu Mendis',    'readonly' => true],
                ['label' => 'Date of Birth',       'icon' => 'icon-calendar',  'value' => 'Aug 15, 2008',             'readonly' => true],
                ['label' => 'Gender',              'icon' => 'icon-user',      'value' => 'Male',                     'readonly' => true],
                ['label' => 'Birth Certificate No','icon' => 'icon-award',     'value' => '2008/COL/00142',           'readonly' => true],
                ['label' => 'Blood Group',         'icon' => 'icon-heart',     'value' => 'O+',                       'readonly' => true],
                ['label' => 'Nationality',         'icon' => 'icon-mapPin',    'value' => 'Sri Lankan',               'readonly' => true],
                ['label' => 'Religion',            'icon' => 'icon-shield',    'value' => 'Buddhism',                 'readonly' => true],
                ['label' => 'Previous School',     'icon' => 'icon-bookOpen',  'value' => 'Royal College Primary',    'readonly' => true],
            ],
            'roleSection' => [
                'title'     => 'Academic & Extracurricular Information',
                'tintClass' => 'c-profile-tinted--sky',
                'items' => [
                    ['label' => 'Current Class',    'icon' => 'icon-graduationCap',  'value' => 'Grade 10-A'],
                    ['label' => 'Class Teacher',    'icon' => 'icon-user',           'value' => 'Mr. H. Rajapaksha'],
                    ['label' => 'House',            'icon' => 'icon-shield',        'value' => 'Emerald House'],
                    ['label' => 'Primary Sport',    'icon' => 'icon-extracurricular','value' => 'Badminton (Junior Captain)'],
                    ['label' => 'Clubs & Societies','icon' => 'icon-usersRound',    'value' => 'Debating Society, ICT Club'],
                    ['label' => 'Prefect Role',     'icon' => 'icon-shieldCheck',    'value' => 'Junior Prefect (2025/2026)'],
                    ['label' => 'Admission Date',   'icon' => 'icon-calendar',      'value' => 'Jan 05, 2019'],
                    ['label' => 'Attendance Rate',  'icon' => 'icon-clock',          'value' => '96.4% this term'],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Residential & Regional Details',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Residential Address', 'icon' => 'icon-mapPin',   'value' => '45 Galle Road, Wellawatte, Colombo 06', 'readonly' => true, 'fullWidth' => true],
                        ['label' => 'Educational Zone',    'icon' => 'icon-building2','value' => 'Colombo Zone 3',                        'readonly' => true],
                        ['label' => 'District',            'icon' => 'icon-mapPin',   'value' => 'Colombo',                               'readonly' => true],
                        ['label' => 'Province',            'icon' => 'icon-mapPin',   'value' => 'Western',                               'readonly' => true],
                    ],
                ],
                [
                    'title' => 'Guardian & Medical Notes',
                    'icon'  => 'icon-heartHandshake',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Primary Guardian',     'icon' => 'icon-heartHandshake', 'value' => 'Samantha Perera (Mother)', 'readonly' => true],
                        ['label' => 'Guardian Phone',       'icon' => 'icon-phone',          'value' => '+94 77 234 5678',          'readonly' => true],
                        ['label' => 'Guardian Email',       'icon' => 'icon-mail',           'value' => 'samantha.p@email.com',     'readonly' => true],
                        ['label' => 'Medical Notes / Health','icon' => 'icon-heart',         'value' => 'None recorded · Medically cleared for all competitive athletics and excursions.', 'readonly' => true, 'fullWidth' => true],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Student ID',       'icon' => 'icon-lockKeyhole', 'value' => 'STU-001',        'readonly' => true],
                ['label' => 'Portal Role',      'icon' => 'icon-graduationCap','value' => 'Student',       'readonly' => true],
                ['label' => 'Academic Year',    'icon' => 'icon-calendar',    'value' => '2025 / 2026',    'readonly' => true],
                ['label' => 'Last Portal Login','icon' => 'icon-clock',       'value' => 'Today, 18:20',   'readonly' => true],
            ],
        ];

        $this->view('student/profile', [
            'currentRole'  => 'student',
            'currentRoute' => '/student/profile',
            'profileData'  => $profileData,
        ]);
    }

    public function dashboard() {
        // Metric Cards Data
        $metrics = [
            [
                'color'   => 'maroon',
                'icon'    => 'icon-extracurricular',
                'value'   => '2',
                'label'   => 'Sports / Clubs Enrolled',
                'delay'   => 0,
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-achievements',
                'value'   => '5',
                'label'   => 'Total Achievements',
                'delay'   => 50,
            ],
            [
                'color'   => 'sand',
                'icon'    => 'icon-podium',
                'value'   => '17th',
                'label'   => 'Class Position (Term 2)',
                'delay'   => 100,
            ],
            [
                'color'   => 'moss',
                'icon'    => 'icon-mail',
                'value'   => '2',
                'label'   => 'Upcoming Notices / Exams',
                'delay'   => 150,
            ],
        ];

        // Column Chart Data — Full 12 Academic Subjects Across All 3 Terms
        $termsData = [
            'Term 1' => [
                ['label' => 'English',        'mine' => 78, 'best' => 88],
                ['label' => 'Math',           'mine' => 82, 'best' => 91],
                ['label' => 'Science',        'mine' => 74, 'best' => 89],
                ['label' => 'Religion',       'mine' => 74, 'best' => 89],
                ['label' => 'History',        'mine' => 67, 'best' => 79],
                ['label' => 'Sinhala',        'mine' => 74, 'best' => 89],
                ['label' => 'Geography',      'mine' => 74, 'best' => 89],
                ['label' => 'Civic',          'mine' => 74, 'best' => 89],
                ['label' => 'Aesthetics',     'mine' => 74, 'best' => 89],
                ['label' => 'ICT',            'mine' => 84, 'best' => 92],
                ['label' => 'Health Science', 'mine' => 74, 'best' => 89],
                ['label' => 'Tamil',          'mine' => 74, 'best' => 89],
            ],
            'Term 2' => [
                ['label' => 'English',        'mine' => 81, 'best' => 90],
                ['label' => 'Math',           'mine' => 86, 'best' => 94],
                ['label' => 'Science',        'mine' => 76, 'best' => 91],
                ['label' => 'Religion',       'mine' => 71, 'best' => 82],
                ['label' => 'History',        'mine' => 71, 'best' => 82],
                ['label' => 'Sinhala',        'mine' => 78, 'best' => 90],
                ['label' => 'Geography',      'mine' => 78, 'best' => 90],
                ['label' => 'Civic',          'mine' => 78, 'best' => 90],
                ['label' => 'Aesthetics',     'mine' => 78, 'best' => 90],
                ['label' => 'ICT',            'mine' => 87, 'best' => 93],
                ['label' => 'Health Science', 'mine' => 78, 'best' => 90],
                ['label' => 'Tamil',          'mine' => 78, 'best' => 90],
            ],
            'Term 3' => [
                ['label' => 'English',        'mine' => 85, 'best' => 93],
                ['label' => 'Math',           'mine' => 89, 'best' => 96],
                ['label' => 'Science',        'mine' => 80, 'best' => 93],
                ['label' => 'Religion',       'mine' => 75, 'best' => 85],
                ['label' => 'History',        'mine' => 76, 'best' => 86],
                ['label' => 'Sinhala',        'mine' => 82, 'best' => 92],
                ['label' => 'Geography',      'mine' => 82, 'best' => 92],
                ['label' => 'Civic',          'mine' => 82, 'best' => 92],
                ['label' => 'Aesthetics',     'mine' => 82, 'best' => 92],
                ['label' => 'ICT',            'mine' => 90, 'best' => 95],
                ['label' => 'Health Science', 'mine' => 82, 'best' => 92],
                ['label' => 'Tamil',          'mine' => 82, 'best' => 92],
            ],
        ];

        // Column Chart Configuration
        $chartConfig = [
            'id'         => 'j-student-perf-chart',
            'title'      => 'Academic Performance',
            'xAxisTitle' => 'SUBJECTS',
            'yAxisTitle' => 'SCORE (%)',
            'maxVal'     => 100,
            'ticks'      => [0, 25, 50, 75, 100],
            'series'     => [
                ['key' => 'mine', 'label' => 'My Score',      'color' => BRAND_MAROON],
                ['key' => 'best', 'label' => 'Highest Score', 'color' => BRAND_SKYBLUE],
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

        // Calendar Configuration (Student: View-only)
        $calendarConfig = [
            'canAddEvent' => false,
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

        // Upcoming Events (Student: Activities & Events)
        $upcomingEvents = [
            [
                'day'      => '02',
                'month'    => 'AUG',
                'name'     => 'U17 Cricket Tournament',
                'tag'      => 'Sports',
                'tagColor' => 'sky',
            ],
            [
                'day'      => '07',
                'month'    => 'AUG',
                'name'     => 'Debating Society Meet',
                'tag'      => 'Club',
                'tagColor' => 'sand',
            ],
        ];

        // Unread Notices
        $notices = [
            [
                'title' => 'Final Exam Schedule',
                'desc'  => 'The schedule for the final term examination is now available on the portal.',
            ],
            [
                'title' => 'Library Books Return',
                'desc'  => 'All issued library books must be returned to the main library by Friday, June 26.',
            ],
            [
                'title' => 'Inter-House Sports Meet',
                'desc'  => 'Rehearsals commence Monday at 3:30 PM on the college main ground.',
            ],
        ];

        $this->view('student/dashboard', [
            'currentRole'    => 'student',
            'currentRoute'   => '/student/dashboard',
            'metrics'        => $metrics,
            'chartConfig'    => $chartConfig,
            'calendarConfig' => $calendarConfig,
            'upcomingEvents' => $upcomingEvents,
            'notices'        => $notices,
        ]);
    }

    public function notice() {

        $notices    = NoticeModel::getForRole('student');
        $categories = NoticeModel::getCategories();

        $this->view('student/notice', [
            'currentRole'  => 'student',
            'currentRoute' => '/student/notice',
            'notices'      => $notices,
            'categories'   => $categories,
        ]);
    }

    public function noticeBoard() {
        $this->notice();
    }
}
