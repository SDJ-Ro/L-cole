<?php
/**
 * =========================================================================
 * L'ÉCOLE — HYBRID PROFILE MODEL
 * =========================================================================
 * Generates unified, role-specific profile view data structures.
 * 
 * HYBRID STRATEGY:
 *   1. Live Identity Priority: Real database records (Name, ID, Email,
 *      Phone, NIC, DOB, Class, Guardian, Status) take precedence.
 *   2. Graceful Showcase Fallbacks: Modules still in development (prefect
 *      status, house allocations, attendance, term awards) use rich
 *      showcase data so cards remain complete and visually stunning.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';

class ProfileModel {

    public static function getProfileData(string $role, ?array $user = null, ?array $profile = null): array {
        $role = strtolower($role);

        // Refresh profile directly from database if not explicitly provided
        if (empty($profile) && !empty($user['id'])) {
            $profile = self::fetchFreshProfile((int)$user['id'], $role) ?: [];
        }

        return match ($role) {
            'student'    => self::buildStudentProfile($user, $profile),
            'teacher'    => self::buildTeacherProfile($user, $profile),
            'parent'     => self::buildParentProfile($user, $profile),
            'management' => self::buildManagementProfile($user, $profile),
            'admin'      => self::buildAdminProfile($user, $profile),
            default      => self::buildAdminProfile($user, $profile)
        };
    }

    private static function fetchFreshProfile(int $accountId, string $role): ?array {
        try {
            $db = Database::getConnection();
            if ($role === 'student') {
                $stmt = $db->prepare("
                    SELECT s.*, 
                           COALESCE(c.section_name, s.class_section) AS class_section, 
                           COALESCE(g.name, s.grade) AS grade
                    FROM students s
                    LEFT JOIN classes c ON c.id = s.class_id
                    LEFT JOIN grades g ON g.id = c.grade_id
                    WHERE s.account_id = ? LIMIT 1
                ");
                $stmt->execute([$accountId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            $tableMap = [
                'teacher'    => 'teachers',
                'parent'     => 'parents',
                'management' => 'management_profiles',
                'admin'      => 'admin_profiles',
            ];
            $table = $tableMap[$role] ?? null;
            if (!$table) return null;

            $stmt = $db->prepare("SELECT * FROM {$table} WHERE account_id = ? LIMIT 1");
            $stmt->execute([$accountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function buildStudentProfile(?array $user, ?array $p): array {
        $name = !empty($p['full_name']) ? $p['full_name'] : 'Jason Mendis';
        $index = !empty($p['index_no']) ? $p['index_no'] : 'STU-001';
        $email = !empty($user['identifier']) ? $user['identifier'] : ($p['personal_email'] ?? 'jason.mendis@student.lecole.edu');
        $phone = !empty($p['mobile_phone']) ? $p['mobile_phone'] : '+94 77 234 5678';
        $grade = !empty($p['grade']) ? $p['grade'] : 'Grade 10';
        $sec   = !empty($p['class_section']) ? $p['class_section'] : 'A';
        $dob   = !empty($p['date_of_birth']) ? date('M d, Y', strtotime($p['date_of_birth'])) : 'Aug 15, 2008';

        return [
            'role'         => 'student',
            'name'         => $name,
            'id'           => $index,
            'status'       => ucfirst(strtolower($p['account_status'] ?? ($p['activation_status'] ?? 'Active'))),
            'avatar'       => '/assets/images/students.jpg',
            'eyebrow'      => "Student · {$grade}-{$sec}",
            'sub'          => "Student Portal · L'École School Management",
            'editable'     => true,
            'showPassword' => false,
            'contact' => [
                ['label' => 'Student Email',       'icon' => 'icon-mail',  'type' => 'email', 'value' => $email, 'readonly' => false],
                ['label' => 'Parent Contact',      'icon' => 'icon-phone', 'type' => 'phone', 'value' => $phone . ' (Mother)', 'readonly' => true],
                ['label' => 'Emergency Line',      'icon' => 'icon-phone', 'type' => 'phone', 'value' => '+94 11 456 7890', 'readonly' => true],
            ],
            'personal' => [
                ['label' => 'Full Name',           'icon' => 'icon-user',      'value' => $name,                                          'readonly' => false],
                ['label' => 'Date of Birth',       'icon' => 'icon-calendar',  'type' => 'date', 'value' => $dob,                         'readonly' => false],
                ['label' => 'Gender',              'icon' => 'icon-user',      'type' => 'select', 'options' => ['Female', 'Male', 'Prefer not to say'], 'value' => $p['gender'] ?? 'Male', 'readonly' => false],
                ['label' => 'Birth Certificate No','icon' => 'icon-award',     'value' => $p['birth_certificate_number'] ?? '2008/COL/00142', 'readonly' => false],
                ['label' => 'Blood Group',         'icon' => 'icon-heart',     'type' => 'select', 'options' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Not provided'], 'value' => $p['blood_group'] ?? 'O+', 'readonly' => false],
                ['label' => 'Nationality',         'icon' => 'icon-mapPin',    'value' => $p['nationality'] ?? 'Sri Lankan',              'readonly' => false],
                ['label' => 'Religion',            'icon' => 'icon-shield',    'type' => 'select', 'options' => ['Buddhism', 'Christianity', 'Catholic', 'Hinduism', 'Islam', 'Other', 'Prefer not to say'], 'value' => $p['religion'] ?? 'Buddhism', 'readonly' => false],
                ['label' => 'Previous School',     'icon' => 'icon-bookOpen',  'value' => $p['previous_school'] ?? 'Royal College Primary','readonly' => false],
            ],
            'roleSection' => [
                'title'     => 'Academic & Extracurricular Information',
                'tintClass' => 'c-profile-tinted--sky',
                'items' => [
                    ['label' => 'Current Class',    'icon' => 'icon-graduationCap',  'value' => "{$grade}-{$sec}", 'readonly' => true],
                    ['label' => 'Class Teacher',    'icon' => 'icon-user',           'value' => $p['class_teacher'] ?? 'Mr. H. Rajapaksha', 'readonly' => true],
                    ['label' => 'House',            'icon' => 'icon-shield',        'value' => 'Emerald House', 'readonly' => true],
                    ['label' => 'Primary Sport',    'icon' => 'icon-extracurricular','value' => 'Badminton (Junior Captain)', 'readonly' => true],
                    ['label' => 'Clubs & Societies','icon' => 'icon-usersRound',    'value' => 'Debating Society, ICT Club', 'readonly' => true],
                    ['label' => 'Prefect Role',     'icon' => 'icon-shieldCheck',    'value' => 'Junior Prefect (2025/2026)', 'readonly' => true],
                    ['label' => 'Admission Date',   'icon' => 'icon-calendar',      'type' => 'date', 'value' => !empty($p['admission_date']) ? date('M d, Y', strtotime($p['admission_date'])) : 'Jan 05, 2019', 'readonly' => false],
                    ['label' => 'Attendance Rate',  'icon' => 'icon-clock',          'value' => '96.4% this term', 'readonly' => true],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Residential & Regional Details',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Residential Address', 'icon' => 'icon-mapPin',   'value' => $p['home_address'] ?? '45 Galle Road, Wellawatte, Colombo 06', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Educational Zone',    'icon' => 'icon-building2','type' => 'select', 'options' => ['Colombo Zone 1', 'Colombo Zone 2', 'Colombo Zone 3', 'Sri Jayawardenepura', 'Piliyandala', 'Homagama'], 'value' => $p['educational_zone'] ?? 'Colombo Zone 3', 'readonly' => false],
                        ['label' => 'District',            'icon' => 'icon-mapPin',   'type' => 'select', 'options' => ['Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Galle', 'Matara', 'Kurunegala'], 'value' => $p['district'] ?? 'Colombo', 'readonly' => false],
                        ['label' => 'Province',            'icon' => 'icon-mapPin',   'type' => 'select', 'options' => ['Western', 'Central', 'Southern', 'North Western', 'Sabaragamuwa', 'Eastern', 'Northern', 'Uva', 'North Central'], 'value' => $p['province'] ?? 'Western', 'readonly' => false],
                    ],
                ],
                [
                    'title' => 'Guardian & Medical Notes',
                    'icon'  => 'icon-heartHandshake',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Primary Guardian',      'icon' => 'icon-heartHandshake', 'value' => $p['parent_name'] ?? 'Samantha Perera (Mother)', 'readonly' => true],
                        ['label' => 'Guardian Phone',        'icon' => 'icon-phone',          'type' => 'phone', 'value' => $phone,                         'readonly' => true],
                        ['label' => 'Guardian Email',        'icon' => 'icon-mail',           'type' => 'email', 'value' => $p['parent_email'] ?? 'samantha.p@email.com', 'readonly' => true],
                        ['label' => 'Medical Notes / Health','icon' => 'icon-heart',         'value' => $p['medical_notes'] ?? 'None recorded · Medically cleared for all competitive athletics.', 'readonly' => false, 'fullWidth' => true],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Student ID',       'icon' => 'icon-lockKeyhole', 'value' => $index,           'readonly' => true],
                ['label' => 'Portal Role',      'icon' => 'icon-graduationCap','value' => 'Student',       'readonly' => true],
                ['label' => 'Academic Year',    'icon' => 'icon-calendar',    'value' => '2025 / 2026',    'readonly' => true],
                ['label' => 'Last Portal Login','icon' => 'icon-clock',       'value' => 'Today, ' . date('H:i'), 'readonly' => true],
            ],
        ];
    }

    private static function buildTeacherProfile(?array $user, ?array $p): array {
        $name = !empty($p['full_name']) ? $p['full_name'] : 'James Wilson';
        $id   = !empty($p['staff_id']) ? $p['staff_id'] : 'TEA-2026-0004';
        $email = !empty($user['identifier']) ? $user['identifier'] : ($p['institutional_email'] ?? 'j.wilson@lecole.edu');
        $phone = !empty($p['mobile_phone']) ? $p['mobile_phone'] : ($p['phone'] ?? '+94 77 123 4567');
        $subject = !empty($p['primary_subject']) ? $p['primary_subject'] : 'Science';
        $joinDate = !empty($p['join_date']) ? date('M d, Y', strtotime($p['join_date'])) : 'Sep 01, 2018';

        return [
            'role'        => 'teacher',
            'name'        => $name,
            'id'          => $id,
            'status'      => ucfirst(strtolower($p['account_status'] ?? ($p['activation_status'] ?? 'Active'))),
            'avatar'      => '/assets/images/teacher.jpg',
            'eyebrow'     => "Senior Faculty · {$subject} Department",
            'sub'         => "Teacher Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'School Email',       'icon' => 'icon-mail',  'type' => 'email', 'value' => $email, 'readonly' => false],
                ['label' => 'Mobile Phone',       'icon' => 'icon-phone', 'type' => 'phone', 'value' => $phone, 'readonly' => false],
                ['label' => 'Department Extension','icon' => 'icon-phone', 'value' => '+94 11 234 5678 ext. 204', 'readonly' => true],
            ],
            'personal' => [
                ['label' => 'Full Name',          'icon' => 'icon-user',        'value' => $name,                                          'readonly' => false],
                ['label' => 'Date of Birth',      'icon' => 'icon-calendar',    'type' => 'date', 'value' => !empty($p['date_of_birth']) ? date('M d, Y', strtotime($p['date_of_birth'])) : 'Mar 12, 1982', 'readonly' => false],
                ['label' => 'NIC / Passport',     'icon' => 'icon-lockKeyhole', 'value' => $p['nic'] ?? '198207103421',                   'readonly' => true],
                ['label' => 'Gender',             'icon' => 'icon-user',        'type' => 'select', 'options' => ['Female', 'Male', 'Prefer not to say'], 'value' => $p['gender'] ?? 'Male', 'readonly' => false],
                ['label' => 'Joined Date',        'icon' => 'icon-calendar',    'type' => 'date', 'value' => $joinDate,                   'readonly' => false],
                ['label' => 'Qualifications',     'icon' => 'icon-award',       'value' => $p['qualifications'] ?? 'B.Sc. Education (Hons), Dip. Secondary Teaching', 'readonly' => false],
            ],
            'roleSection' => [
                'title'     => 'Teaching Assignments & School Roles',
                'tintClass' => 'c-profile-tinted--sunshine',
                'items' => [
                    ['label' => 'Primary Subject',     'icon' => 'icon-bookOpen',       'type' => 'select', 'options' => ['Mathematics', 'Science', 'English', 'History', 'ICT', 'Arts', 'Commerce', 'Health & Physical Education'], 'value' => $subject, 'readonly' => false],
                    ['label' => 'Homeroom Class',      'icon' => 'icon-graduationCap',  'value' => $p['homeroom_class'] ?? 'Class 6-A (Class Teacher)', 'readonly' => true],
                    ['label' => 'Classes Taught',      'icon' => 'icon-usersRound',     'value' => '6-A, 7-B, 8-C (24 Periods/Week)', 'readonly' => true],
                    ['label' => 'Teacher-in-Charge',   'icon' => 'icon-extracurricular','value' => 'Science Society, Nature Club', 'readonly' => true],
                    ['label' => 'Staff Room Desk',     'icon' => 'icon-building2',      'value' => 'Senior Wing · Floor 2 · Desk 14', 'readonly' => true],
                    ['label' => 'Employment Status',   'icon' => 'icon-shieldCheck',    'value' => 'Permanent Full-Time Faculty', 'readonly' => true],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Residential & Emergency Contact',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Home Address',       'icon' => 'icon-mapPin',   'value' => $p['address'] ?? '12 Park Road, Colombo 05', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Emergency Contact',  'icon' => 'icon-phone',    'value' => $p['emergency_name'] ?? 'Mrs. Eleanor Wilson (Spouse)', 'readonly' => false],
                        ['label' => 'Emergency Phone',    'icon' => 'icon-phone',    'type' => 'phone', 'value' => $p['emergency_phone'] ?? '+94 77 987 6543', 'readonly' => false],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Staff ID',         'icon' => 'icon-lockKeyhole', 'value' => $id,            'readonly' => true],
                ['label' => 'Portal Access',    'icon' => 'icon-shieldCheck', 'value' => 'Teacher Portal','readonly' => true],
                ['label' => 'Account Created',  'icon' => 'icon-calendar',    'value' => $joinDate,       'readonly' => true],
                ['label' => 'Last Login',       'icon' => 'icon-clock',       'value' => 'Today, ' . date('H:i'), 'readonly' => true],
            ],
        ];
    }

    private static function buildParentProfile(?array $user, ?array $p): array {
        $name = !empty($p['full_name']) ? $p['full_name'] : 'Samantha Perera';
        $id   = !empty($p['parent_id']) ? $p['parent_id'] : 'PAR-2026-0001';
        $email = !empty($user['identifier']) ? $user['identifier'] : ($p['personal_email'] ?? 'samantha.p@email.com');
        $phone = !empty($p['mobile_phone']) ? $p['mobile_phone'] : '+94 77 234 5678';
        $relation = !empty($p['relationship']) ? $p['relationship'] : 'Mother';
        $dob = !empty($p['date_of_birth']) ? date('M d, Y', strtotime($p['date_of_birth'])) : 'Apr 12, 1978';

        return [
            'role'        => 'parent',
            'name'        => $name,
            'id'          => $id,
            'status'      => ucfirst(strtolower($p['account_status'] ?? ($p['activation_status'] ?? 'Active'))),
            'avatar'      => '/assets/images/parents.jpg',
            'eyebrow'     => "Parent / Guardian · {$relation}",
            'sub'         => "Parent Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'Personal Email',     'icon' => 'icon-mail',  'type' => 'email', 'value' => $email, 'readonly' => false],
                ['label' => 'Primary Mobile',     'icon' => 'icon-phone', 'type' => 'phone', 'value' => $phone, 'readonly' => false],
                ['label' => 'Alternative Phone',  'icon' => 'icon-phone', 'type' => 'phone', 'value' => '+94 11 456 7890 (Home)', 'readonly' => false],
            ],
            'personal' => [
                ['label' => 'Full Name',          'icon' => 'icon-user',        'value' => $name,                                          'readonly' => false],
                ['label' => 'Gender',             'icon' => 'icon-user',        'type' => 'select', 'options' => ['Female', 'Male', 'Prefer not to say'], 'value' => $p['gender'] ?? 'Male', 'readonly' => false],
                ['label' => 'Relationship',       'icon' => 'icon-heart',       'type' => 'select', 'options' => ['Father', 'Mother', 'Guardian', 'Other'], 'value' => $relation, 'readonly' => false],
                ['label' => 'Date of Birth',      'icon' => 'icon-calendar',    'type' => 'date', 'value' => $dob,                         'readonly' => false],
                ['label' => 'NIC Number',         'icon' => 'icon-lockKeyhole', 'value' => $p['nic'] ?? '198075301245',                   'readonly' => true],
                ['label' => 'Occupation',         'icon' => 'icon-briefcase',   'value' => $p['occupation'] ?? 'Architect',                'readonly' => false],
                ['label' => 'Employer / Business','icon' => 'icon-building2',   'value' => $p['employer'] ?? 'Studio Design Consortium',                     'readonly' => false],
            ],
            'roleSection' => [
                'title'     => 'Linked Children & School Engagement',
                'tintClass' => 'c-profile-tinted--terracotta',
                'items' => [
                    ['label' => 'Linked Children',    'icon' => 'icon-graduationCap',  'value' => 'Jason Mendis (Grade 10-A)', 'readonly' => true],
                    ['label' => 'PTA Member',         'icon' => 'icon-usersRound',     'value' => 'Active Member · Executive Committee 2025/26', 'readonly' => true],
                    ['label' => 'Communications',     'icon' => 'icon-mail',           'value' => 'SMS & Email Alerts Enabled', 'readonly' => true],
                    ['label' => 'Emergency Pick-Up',  'icon' => 'icon-shieldCheck',    'value' => 'Authorized Primary Collector', 'readonly' => true],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Residential & Family Information',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Home Address',       'icon' => 'icon-mapPin', 'value' => $p['home_address'] ?? '45 Galle Road, Wellawatte, Colombo 06', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Emergency Contact',  'icon' => 'icon-phone',  'value' => $p['emergency_name'] ?? 'Nimal Perera (Brother)',                         'readonly' => false],
                        ['label' => 'Emergency Phone',    'icon' => 'icon-phone',  'type' => 'phone', 'value' => '+94 71 888 9900',                                'readonly' => false],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Parent ID',        'icon' => 'icon-lockKeyhole', 'value' => $id,            'readonly' => true],
                ['label' => 'Portal Access',    'icon' => 'icon-shieldCheck', 'value' => 'Parent Portal', 'readonly' => true],
                ['label' => 'Registration Date','icon' => 'icon-calendar',    'value' => 'Jan 05, 2019',  'readonly' => true],
                ['label' => 'Last Login',       'icon' => 'icon-clock',       'value' => 'Today, ' . date('H:i'), 'readonly' => true],
            ],
        ];
    }

    private static function buildManagementProfile(?array $user, ?array $p): array {
        $name = !empty($p['full_name']) ? $p['full_name'] : 'Sarah Vance';
        $id   = !empty($p['staff_id']) ? $p['staff_id'] : 'MAN-2026-0001';
        $email = !empty($user['identifier']) ? $user['identifier'] : ($p['institutional_email'] ?? 'sarah_vance@lecole.edu');
        $phone = !empty($p['contact_number']) ? $p['contact_number'] : ($p['phone'] ?? '+94 77 000 0001');
        $title = !empty($p['title']) ? $p['title'] : 'Senior Executive Management';
        $office = !empty($p['office_location']) ? $p['office_location'] : 'Main Admin Building · Suite 102';
        $dob = !empty($p['date_of_birth']) ? date('M d, Y', strtotime($p['date_of_birth'])) : 'May 20, 1975';
        $joinDate = !empty($p['join_date']) ? date('M d, Y', strtotime($p['join_date'])) : 'Jan 10, 2020';

        return [
            'role'        => 'management',
            'name'        => $name,
            'id'          => $id,
            'status'      => ucfirst(strtolower($p['account_status'] ?? ($p['activation_status'] ?? 'Active'))),
            'avatar'      => '/assets/images/management.jpg',
            'eyebrow'     => $title,
            'sub'         => "Management Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'Institutional Email','icon' => 'icon-mail',  'type' => 'email', 'value' => $email, 'readonly' => false],
                ['label' => 'Direct Mobile',      'icon' => 'icon-phone', 'type' => 'phone', 'value' => $phone, 'readonly' => false],
                ['label' => 'Office Line',        'icon' => 'icon-phone', 'value' => '+94 11 555 1200', 'readonly' => true],
            ],
            'personal' => [
                ['label' => 'Full Name',          'icon' => 'icon-user',        'value' => $name,                                          'readonly' => false],
                ['label' => 'Designation',        'icon' => 'icon-briefcase',   'value' => $title,                                         'readonly' => false],
                ['label' => 'Gender',             'icon' => 'icon-user',        'type' => 'select', 'options' => ['Female', 'Male', 'Prefer not to say'], 'value' => $p['gender'] ?? 'Female', 'readonly' => false],
                ['label' => 'NIC Number',         'icon' => 'icon-lockKeyhole', 'value' => $p['nic'] ?? '198421004561',                   'readonly' => true],
                ['label' => 'Date of Birth',      'icon' => 'icon-calendar',    'type' => 'date', 'value' => $dob,                         'readonly' => false],
                ['label' => 'Join Date',          'icon' => 'icon-calendar',    'type' => 'date', 'value' => $joinDate,                    'readonly' => false],
                ['label' => 'Office Location',    'icon' => 'icon-building2',   'value' => $office,                                        'readonly' => false],
            ],
            'roleSection' => [
                'title'     => 'Executive Portfolios & Operational Oversight',
                'tintClass' => 'c-profile-tinted--maroon',
                'items' => [
                    ['label' => 'Assigned Department', 'icon' => 'icon-building2',   'value' => 'Academic Operations & Governance', 'readonly' => true],
                    ['label' => 'Approval Authority',  'icon' => 'icon-shieldCheck', 'value' => 'Financial Budgets, Leave Sanction, Curriculum Approvals', 'readonly' => true],
                    ['label' => 'Committee Seat',      'icon' => 'icon-usersRound',   'value' => 'Board of Governors · Secretary', 'readonly' => true],
                    ['label' => 'Audit Level',         'icon' => 'icon-lockKeyhole', 'value' => 'Level 3 Executive Audit Access', 'readonly' => true],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Emergency Contacts & Address',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Home Address',       'icon' => 'icon-mapPin', 'value' => $p['address'] ?? '77 Flower Road, Colombo 07', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Emergency Contact',  'icon' => 'icon-phone',  'value' => $p['emergency_name'] ?? 'Dr. R. Vance (Spouse)', 'readonly' => false],
                        ['label' => 'Emergency Phone',    'icon' => 'icon-phone',  'type' => 'phone', 'value' => $p['emergency_phone'] ?? '+94 77 111 2233',      'readonly' => false],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Staff ID',         'icon' => 'icon-lockKeyhole', 'value' => $id,                 'readonly' => true],
                ['label' => 'Portal Access',    'icon' => 'icon-shieldCheck', 'value' => 'Management Portal', 'readonly' => true],
                ['label' => 'Account Created',  'icon' => 'icon-calendar',    'value' => 'Jan 10, 2020',      'readonly' => true],
                ['label' => 'Last Login',       'icon' => 'icon-clock',       'value' => 'Today, ' . date('H:i'), 'readonly' => true],
            ],
        ];
    }

    private static function buildAdminProfile(?array $user, ?array $p): array {
        $name = !empty($p['full_name']) ? $p['full_name'] : 'Alex Mendis';
        $id   = !empty($p['admin_id']) ? $p['admin_id'] : 'ADM-001';
        $email = !empty($user['identifier']) ? $user['identifier'] : ($p['institutional_email'] ?? 'alex.m@lecole.edu');
        $phone = !empty($p['mobile_phone']) ? $p['mobile_phone'] : '+94 77 123 4567';
        $dob = !empty($p['date_of_birth']) ? date('M d, Y', strtotime($p['date_of_birth'])) : 'Jun 14, 1985';
        $joinDate = !empty($p['join_date']) ? date('M d, Y', strtotime($p['join_date'])) : 'Jan 01, 2021';

        return [
            'role'        => 'admin',
            'name'        => $name,
            'id'          => $id,
            'status'      => 'Active',
            'avatar'      => '/assets/images/admin.jpg',
            'eyebrow'     => 'System Administrator & IT Director',
            'sub'         => "Admin Portal · L'École School Management",
            'editable'    => true,
            'showPassword'=> true,
            'contact' => [
                ['label' => 'Administrator Email', 'icon' => 'icon-mail',  'type' => 'email', 'value' => $email, 'readonly' => false],
                ['label' => 'Primary Mobile',       'icon' => 'icon-phone', 'type' => 'phone', 'value' => $phone, 'readonly' => false],
                ['label' => 'Central IT Office',   'icon' => 'icon-phone', 'value' => '+94 11 234 5678', 'readonly' => true],
                ['label' => 'Emergency NOC Line',  'icon' => 'icon-phone', 'value' => '+94 11 999 8877', 'readonly' => true],
            ],
            'personal' => [
                ['label' => 'Full Name',            'icon' => 'icon-user',        'value' => $name,                                         'readonly' => false],
                ['label' => 'Gender',               'icon' => 'icon-user',        'type' => 'select', 'options' => ['Female', 'Male', 'Prefer not to say'], 'value' => $p['gender'] ?? 'Male', 'readonly' => false],
                ['label' => 'NIC Number',           'icon' => 'icon-lockKeyhole', 'value' => $p['nic'] ?? '198516503921',                   'readonly' => true],
                ['label' => 'Date of Birth',        'icon' => 'icon-calendar',    'type' => 'date', 'value' => $dob,                        'readonly' => false],
                ['label' => 'Nationality',          'icon' => 'icon-mapPin',      'value' => 'Sri Lankan',                                 'readonly' => false],
                ['label' => 'Joined Date',          'icon' => 'icon-calendar',    'type' => 'date', 'value' => $joinDate,                   'readonly' => false],
                ['label' => 'Qualifications',       'icon' => 'icon-award',       'value' => 'B.Sc in Computer Science, CISA Certified',    'readonly' => false],
                ['label' => 'Central Station',      'icon' => 'icon-building2',   'value' => 'Central Server Facility · Server Room A',    'readonly' => false],
            ],
            'roleSection' => [
                'title'     => 'Administrative Access & System Operations',
                'tintClass' => 'c-profile-tinted--midnight',
                'items' => [
                    ['label' => 'Access Level',         'icon' => 'icon-shieldCheck',  'value' => 'Full Superadmin Privileges · Tier 4 Root', 'readonly' => true],
                    ['label' => 'Department',           'icon' => 'icon-building2',    'value' => 'Central IT & Platform Operations', 'readonly' => true],
                    ['label' => 'Infrastructure Node',  'icon' => 'icon-building2',    'value' => 'Colombo Regional Cloud Node · AWS ap-southeast-1', 'readonly' => true],
                    ['label' => 'Security Audit Log',   'icon' => 'icon-lockKeyhole',  'value' => 'Level 4 Immutable Audit Log Active', 'readonly' => true],
                    ['label' => 'Reports To',           'icon' => 'icon-user',         'value' => 'Principal & Board of Trustees', 'readonly' => true],
                    ['label' => 'Session Security',     'icon' => 'icon-shieldCheck',  'value' => 'Mandatory 2FA · IP Whitelist Enforced', 'readonly' => true],
                ],
            ],
            'extraSections' => [
                [
                    'title' => 'Residential & Emergency Contact',
                    'icon'  => 'icon-mapPin',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Home Address',       'icon' => 'icon-mapPin', 'value' => $p['address'] ?? '42 Alfred House Gardens, Colombo 03', 'readonly' => false, 'fullWidth' => true],
                        ['label' => 'Emergency Contact',  'icon' => 'icon-phone',  'value' => $p['emergency_name'] ?? 'P. Mendis (Spouse)',            'readonly' => false],
                        ['label' => 'Emergency Phone',    'icon' => 'icon-phone',  'type' => 'phone', 'value' => $p['emergency_phone'] ?? '+94 77 999 1122', 'readonly' => false],
                    ],
                ],
                [
                    'title' => 'System Scope & Platform Governance',
                    'icon'  => 'icon-shieldCheck',
                    'cols'  => 'c-cols-3',
                    'items' => [
                        ['label' => 'Modules Managed',       'icon' => 'icon-building2',   'value' => 'User Directory, RBAC Roles, Cloud Infrastructure, Security Audits', 'readonly' => true, 'fullWidth' => true],
                        ['label' => 'Compliance Standard',   'icon' => 'icon-shieldCheck', 'value' => 'FERPA & GDPR Educational Data Compliance Verified',            'readonly' => true],
                        ['label' => 'Maintenance Window',   'icon' => 'icon-clock',       'value' => 'Weekly Scheduled Window: Sundays 02:00 – 04:00 UTC',           'readonly' => true],
                        ['label' => 'Disaster Recovery RPO', 'icon' => 'icon-lockKeyhole', 'value' => 'RPO: 15 mins · RTO: 1 hour (Certified Q1 2026)',              'readonly' => true],
                    ],
                ],
            ],
            'account' => [
                ['label' => 'Administrator ID', 'icon' => 'icon-lockKeyhole', 'value' => $id,                   'readonly' => true],
                ['label' => 'Portal Role',      'icon' => 'icon-shieldCheck', 'value' => 'System Administrator', 'readonly' => true],
                ['label' => 'Account Created',  'icon' => 'icon-calendar',    'value' => 'Jan 01, 2021',         'readonly' => true],
                ['label' => 'Last Login',       'icon' => 'icon-clock',       'value' => 'Today, ' . date('H:i'),'readonly' => true],
            ],
        ];
    }
}
