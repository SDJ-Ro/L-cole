<?php
require_once __DIR__ . '/../../config/brand.php';
require_once __DIR__ . '/../Models/NoticeModel.php';
require_once __DIR__ . '/../Models/ExtracurricularModel.php';
require_once __DIR__ . '/../Models/AchievementModel.php';
require_once __DIR__ . '/../Models/ComplaintModel.php';
require_once __DIR__ . '/../Models/ParentApprovalModel.php';
require_once __DIR__ . '/CalendarEventCrudTrait.php';
require_once __DIR__ . '/../Models/CalendarEventModel.php';

class ParentController extends Controller {
    use CalendarEventCrudTrait;

    public function __construct() {
        parent::__construct();
        $this->requireRole('parent');
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        // Metric Cards Data
        $metrics = [
            [
                'color'   => 'maroon',
                'icon'    => 'icon-trendingUp',
                'value'   => '84.6%',
                'label'   => 'Average Term Mark (Term 2)',
                'delay'   => 0,
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-usersRound',
                'value'   => '3',
                'label'   => 'Sports & Clubs Active',
                'delay'   => 50,
            ],
            [
                'color'   => 'sand',
                'icon'    => 'icon-podium',
                'value'   => '17th',
                'label'   => 'Class Position',
                'delay'   => 100,
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-achievements',
                'value'   => '5',
                'label'   => 'Student Achievements',
                'delay'   => 150,
            ],
        ];

        // Column Chart Data — Full 12 Academic Subjects Across All 3 Terms
        $termsData = [
            'Term 1' => [
                ['label' => 'English',        'student' => 78, 'average' => 88],
                ['label' => 'Math',           'student' => 82, 'average' => 91],
                ['label' => 'Science',        'student' => 74, 'average' => 89],
                ['label' => 'Religion',       'student' => 74, 'average' => 89],
                ['label' => 'History',        'student' => 67, 'average' => 79],
                ['label' => 'Sinhala',        'student' => 74, 'average' => 89],
                ['label' => 'Geography',      'student' => 74, 'average' => 89],
                ['label' => 'Civic',          'student' => 74, 'average' => 89],
                ['label' => 'Aesthetics',     'student' => 74, 'average' => 89],
                ['label' => 'ICT',            'student' => 84, 'average' => 92],
                ['label' => 'Health Science', 'student' => 74, 'average' => 89],
                ['label' => 'Tamil',          'student' => 74, 'average' => 89],
            ],
            'Term 2' => [
                ['label' => 'English',        'student' => 81, 'average' => 90],
                ['label' => 'Math',           'student' => 86, 'average' => 94],
                ['label' => 'Science',        'student' => 76, 'average' => 91],
                ['label' => 'Religion',       'student' => 71, 'average' => 82],
                ['label' => 'History',        'student' => 71, 'average' => 82],
                ['label' => 'Sinhala',        'student' => 78, 'average' => 90],
                ['label' => 'Geography',      'student' => 78, 'average' => 90],
                ['label' => 'Civic',          'student' => 78, 'average' => 90],
                ['label' => 'Aesthetics',     'student' => 78, 'average' => 90],
                ['label' => 'ICT',            'student' => 87, 'average' => 93],
                ['label' => 'Health Science', 'student' => 78, 'average' => 90],
                ['label' => 'Tamil',          'student' => 78, 'average' => 90],
            ],
            'Term 3' => [
                ['label' => 'English',        'student' => 85, 'average' => 93],
                ['label' => 'Math',           'student' => 89, 'average' => 96],
                ['label' => 'Science',        'student' => 80, 'average' => 93],
                ['label' => 'Religion',       'student' => 75, 'average' => 85],
                ['label' => 'History',        'student' => 76, 'average' => 86],
                ['label' => 'Sinhala',        'student' => 82, 'average' => 92],
                ['label' => 'Geography',      'student' => 82, 'average' => 92],
                ['label' => 'Civic',          'student' => 82, 'average' => 92],
                ['label' => 'Aesthetics',     'student' => 82, 'average' => 92],
                ['label' => 'ICT',            'student' => 90, 'average' => 95],
                ['label' => 'Health Science', 'student' => 82, 'average' => 92],
                ['label' => 'Tamil',          'student' => 82, 'average' => 92],
            ],
        ];

        // Column Chart Configuration
        $chartConfig = [
            'id'         => 'j-parent-perf-chart',
            'title'      => "Child's Academic Performance",
            'xAxisTitle' => 'SUBJECTS',
            'yAxisTitle' => 'SCORE (%)',
            'maxVal'     => 100,
            'ticks'      => [0, 25, 50, 75, 100],
            'series'     => [
                ['key' => 'student', 'label' => 'Student Score', 'color' => BRAND_MAROON],
                ['key' => 'average', 'label' => 'Class Average', 'color' => BRAND_SKYBLUE],
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

        // Calendar Configuration (Parent: View-only)
        $actor = $this->getUser();
        $parentId = CalendarEventModel::getParentId((int)($actor['id'] ?? 0));

        $calendarConfig = [
            'canAddEvent' => false,
            'initialDate' => date('Y-m-d'),
            'viewDate'    => date('Y-m-01'),
            'events'      => $parentId ? CalendarEventModel::getEventsForParent($parentId) : [],
        ];

        // Upcoming Events (Parent: Academic & Examinations)
        $upcomingEvents = [
            [
                'day'      => '27',
                'month'    => 'SEP',
                'name'     => 'Term Assessment Review & Practical Exams',
                'tag'      => 'Academic',
                'tagColor' => 'sand',
            ],
            [
                'day'      => '28',
                'month'    => 'SEP',
                'name'     => 'All-Island School Athletics Meet',
                'tag'      => 'Sports',
                'tagColor' => 'sky',
            ],
            [
                'day'      => '29',
                'month'    => 'SEP',
                'name'     => 'Science Society Annual Exhibition',
                'tag'      => 'Academic',
                'tagColor' => 'terracotta',
            ],
        ];

        // Unread Notices
        $notices = [
            [
                'title' => 'Final Exam Schedule',
                'desc'  => 'The schedule for the final term examination is now available on the portal.',
            ],
            [
                'title' => 'Parent-Teacher Conference',
                'desc'  => 'Individual progress consultation slots with subject masters are now open for reservation.',
            ],
            [
                'title' => 'Term 3 Tuition Due Date',
                'desc'  => 'School fees payment deadline for the upcoming third term is Friday, July 10.',
            ],
        ];

        $this->view('parent/dashboard', [
            'currentRole'    => 'parent',
            'currentRoute'   => '/parent/dashboard',
            'metrics'        => $metrics,
            'chartConfig'    => $chartConfig,
            'calendarConfig' => $calendarConfig,
            'upcomingEvents' => $upcomingEvents,
            'notices'        => $notices,
        ]);
    }

    public function notice() {

        $notices    = NoticeModel::getForRole('parent');
        $categories = NoticeModel::getCategories();

        $this->view('parent/notice', [
            'currentRole'  => 'parent',
            'currentRoute' => '/parent/notice-board',
            'notices'      => $notices,
            'categories'   => $categories,
        ]);
    }

    public function noticeBoard() {
        $this->notice();
    }

    public function notices() {
        $this->notice();
    }

    public function child() {
        $this->childProfile();
    }

    public function childProfile() {
        // 1. Child Identity & Information (Nethmi Perera, Grade 6-A)
        $student = [
            'name'           => 'Nethmi Perera',
            'initials'       => 'NP',
            'index'          => 'S2021-091',
            'grade'          => 'Grade 6',
            'class'          => '6-A',
            'status'         => 'Active',
            'email'          => 'n.perera@lecole.com',
            'phone'          => '+94 77 345 6678',
            'bc'             => '2014/COL/00123',
            'dob'            => '18 Mar 2014',
            'gender'         => 'Female',
            'nationality'    => 'Sri Lankan',
            'religion'       => 'Buddhism',
            'ethnicity'      => 'Sinhalese',
            'firstLanguage'  => 'Sinhala',
            'address'        => '45 Galle Road, Wellawatte, Colombo 06',
            'zone'           => 'Colombo Zone 3',
            'district'       => 'Colombo',
            'province'       => 'Western',
            'admission'      => '08 Jan 2021',
            'blood'          => 'O+',
            'prevSchool'     => 'Colombo Primary Academy',
            'medical'        => 'None reported. Mild pollen allergies during spring season.',
            'fatherName'     => 'Mr. Sunil Perera',
            'fatherPhone'    => '+94 71 234 5678',
            'fatherOcc'      => 'Civil Engineer',
            'motherName'     => 'Mrs. Manori Perera',
            'motherPhone'    => '+94 77 345 6678',
            'motherEmail'    => 'manori.p@gmail.com',
            'motherOcc'      => 'Senior Accountant',
        ];

        // 2. Multi-Grade Academic Dataset (Grades 6–11)
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

        // 3. Child's Extracurricular Activities
        $allClubs = ExtracurricularModel::getForStudent();
        // Specifically mark activities: Football & Swimming as Enrolled, and 2 activities as Pending (Approve/Reject cards)
        $childClubs = array_map(function($c) {
            if (in_array($c['id'], [1, 4])) {
                $c['status'] = 'Enrolled';
                $c['enrolled'] = true;
            } elseif (in_array($c['id'], [2, 6])) {
                $c['status'] = 'Pending';
                $c['enrolled'] = false;
            }
            return $c;
        }, $allClubs);

        $achievements = \App\Models\AchievementModel::getAll();
        $metrics = \App\Models\AchievementModel::getMetrics($achievements);

        $actor = $this->getUser();
        $parentId = CalendarEventModel::getParentId((int)($actor['id'] ?? 0));
        $calendarConfig = [
            'canAddEvent' => false,
            'initialDate' => date('Y-m-d'),
            'viewDate'    => date('Y-m-01'),
            'events'      => $parentId ? CalendarEventModel::getEventsForParent($parentId) : [],
        ];

        $this->view('parent/child-profile', [
            'currentRole'     => 'parent',
            'currentRoute'    => '/parent/child-profile',
            'student'         => $student,
            'academicData'    => $academicData,
            'selectedGrade'   => $selectedGrade,
            'selectedTerm'    => $selectedTerm,
            'recordData'      => $recordData,
            'initialScored'   => $initialScored,
            'initialOutOf'    => $initialOutOf,
            'initialAverage'  => $initialAverage,
            'initialPosition' => $initialPosition,
            'childClubs'      => $childClubs,
            'achievements'    => $achievements,
            'metrics'         => $metrics,
            'canModerate'     => false,
            'calendarConfig'  => $calendarConfig,
        ]);
    }

    public function complaints() {
        $complaints = ComplaintModel::getByParent(1);
        $categories = ComplaintModel::getCategories();
        $children = [
            ['name' => 'Nethmi Perera', 'grade' => 'Grade 10-A']
        ];

        $this->view('parent/complaints', [
            'currentRole'  => 'parent',
            'currentRoute' => '/parent/complaints',
            'complaints'   => $complaints,
            'categories'   => $categories,
            'children'     => $children,
        ]);
    }

    public function feedback() {
        require_once __DIR__ . '/../Models/FeedbackModel.php';
        $feedbacks = \App\Models\FeedbackModel::getForParent(1);
        $filterTypes = \App\Models\FeedbackModel::getTypes();

        $this->view('parent/feedback', [
            'currentRole'  => 'parent',
            'currentRoute' => '/parent/feedback',
            'feedbacks'    => $feedbacks,
            'filterTypes'  => $filterTypes,
        ]);
    }

    public function approvals() {
        $items        = \App\Models\ParentApprovalModel::getAll();
        $statusCounts = \App\Models\ParentApprovalModel::getStatusCounts();

        $this->view('parent/approvals', [
            'currentRole'  => 'parent',
            'currentRoute' => '/parent/approvals',
            'items'        => $items,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function profile() {
        require_once __DIR__ . '/../Models/ProfileModel.php';
        $profileData = ProfileModel::getProfileData('parent', $this->getUser(), $this->getProfile());

        $this->view('parent/profile', [
            'currentRole'  => 'parent',
            'currentRoute' => '/parent/profile',
            'profileData'  => $profileData,
        ]);
    }
}


