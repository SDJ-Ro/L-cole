<?php
/**
 * =========================================================================
 * L'ÉCOLE — PEOPLE DIRECTORY MODEL
 * =========================================================================
 * Central data provider for the Users / People Directory across all portals.
 * Ported from Admin/people/data.js. Replace with PDO/SQL queries later.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/AcademicModel.php';
require_once __DIR__ . '/ParentModel.php';
require_once __DIR__ . '/StudentModel.php';
require_once __DIR__ . '/SqlJsMapper.php';

class PeopleModel {

    public static function getGrades(): array {
        return AcademicModel::getGrades();
    }

    public static function getClassContext(): array {
        $teachers = AcademicModel::getClassTeachers();
        $context = [];
        foreach ($teachers as $class => $teacher) {
            $context[$class] = ['classTeacher' => $teacher];
        }
        return $context;
    }

    public static function getClassEnrollments(): array {
        $academic = AcademicModel::getClassEnrollments();
        return $academic;
    }

    public static function getStudents(): array {
        $dbStudents = [];
        try {
            $dbStudents = StudentModel::getStudents();
        } catch (\Throwable $e) {
            error_log("Failed to load students from DB: " . $e->getMessage());
        }

        $mockStudents = [
            [
                'firstName'   => 'Nethmi',
                'lastName'    => 'Perera',
                'name'        => 'Nethmi Perera',
                'initials'    => 'NP',
                'index'       => 'S2021-091',
                'email'       => 'n.perera@lecole.com',
                'phone'       => '+94 77 345 6678',
                'grade'       => 'Grade 6',
                'gradeId'     => 'g6',
                'className'   => '6-A',
                'activities'  => ['Debating', 'Choir'],
                'parentName'  => 'Mrs. Manori Perera',
                'status'      => 'Active',
                'avatar'      => 'bg-sand text-midnight',
            ],
            [
                'firstName'   => 'Kavindu',
                'lastName'    => 'Senaratne',
                'name'        => 'Kavindu Senaratne',
                'initials'    => 'KS',
                'index'       => 'S2021-094',
                'email'       => 'k.senaratne@lecole.com',
                'phone'       => '+94 77 444 1234',
                'grade'       => 'Grade 6',
                'gradeId'     => 'g6',
                'className'   => '6-A',
                'activities'  => ['Swimming', 'Science'],
                'parentName'  => 'Mr. P. Senaratne',
                'status'      => 'Active',
                'avatar'      => 'bg-skyblue text-midnight',
            ],
            [
                'firstName'   => 'Dilshan',
                'lastName'    => 'Mendis',
                'name'        => 'Dilshan Mendis',
                'initials'    => 'DM',
                'index'       => 'S2021-098',
                'email'       => 'd.mendis@lecole.com',
                'phone'       => '+94 71 888 2345',
                'grade'       => 'Grade 6',
                'gradeId'     => 'g6',
                'className'   => '6-A',
                'activities'  => ['Football'],
                'parentName'  => 'Mr. S. Mendis',
                'status'      => 'Active',
                'avatar'      => 'bg-sand text-midnight',
            ],
            [
                'firstName'   => 'Ananya',
                'lastName'    => 'Jayasuriya',
                'name'        => 'Ananya Jayasuriya',
                'initials'    => 'AJ',
                'index'       => 'S2021-102',
                'email'       => 'a.jayasuriya@lecole.com',
                'phone'       => '+94 70 555 9988',
                'grade'       => 'Grade 6',
                'gradeId'     => 'g6',
                'className'   => '6-A',
                'activities'  => ['Debating'],
                'parentName'  => 'Mrs. N. Jayasuriya',
                'status'      => 'Active',
                'avatar'      => 'bg-sand text-midnight',
            ],
            [
                'firstName'   => 'Maya',
                'lastName'    => 'Kapoor',
                'name'        => 'Maya Kapoor',
                'initials'    => 'MK',
                'index'       => 'S2022-092',
                'email'       => 'm.kapoor@lecole.com',
                'phone'       => '+94 71 554 0921',
                'grade'       => 'Grade 7',
                'gradeId'     => 'g7',
                'className'   => '7-B',
                'activities'  => ['Robotics'],
                'parentName'  => 'Mr. Rajesh Kapoor',
                'status'      => 'Active',
                'avatar'      => 'bg-skyblue text-midnight',
            ],
            [
                'firstName'   => 'Amara',
                'lastName'    => 'Silva',
                'name'        => 'Amara Silva',
                'initials'    => 'AS',
                'index'       => 'S2023-044',
                'email'       => 'a.silva@lecole.com',
                'phone'       => '+94 70 456 7891',
                'grade'       => 'Grade 8',
                'gradeId'     => 'g8',
                'className'   => '8-C',
                'activities'  => ['Science'],
                'parentName'  => 'Mrs. K. Silva',
                'status'      => 'Active',
                'avatar'      => 'bg-moss text-white',
            ],
            [
                'firstName'   => 'Arjun',
                'lastName'    => 'Kapoor',
                'name'        => 'Arjun Kapoor',
                'initials'    => 'AK',
                'index'       => 'S2024-019',
                'email'       => 'a.kapoor@lecole.com',
                'phone'       => '+94 71 345 6789',
                'grade'       => 'Grade 9',
                'gradeId'     => 'g9',
                'className'   => '9-A',
                'activities'  => ['Football', 'Robotics'],
                'parentName'  => 'Mr. Rajesh Kapoor',
                'status'      => 'Active',
                'avatar'      => 'bg-terracotta text-white',
            ],
        ];

        if (!empty($dbStudents)) {
            $dbIndices = array_column($dbStudents, 'index');
            $filteredMock = array_filter($mockStudents, fn($s) => !in_array($s['index'] ?? '', $dbIndices, true));
            return array_merge($dbStudents, array_values($filteredMock));
        }

        return $mockStudents;
    }

    public static function getTeachers(): array {
        $dbTeachers = [];
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT t.*, u.activation_status, u.role as user_role,
                       ct.class_id as homeroom_class_id,
                       c.section_name as homeroom_class_name
                FROM teachers t
                JOIN user_accounts u ON u.id = t.account_id
                LEFT JOIN class_teachers ct ON ct.teacher_id = t.id
                LEFT JOIN classes c ON c.id = ct.class_id
                ORDER BY t.id ASC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $teacherIds = array_column($rows, 'id');
                $inIds = implode(',', array_fill(0, count($teacherIds), '?'));

                $qualMap = [];
                $qStmt = $db->prepare("SELECT teacher_id, degree_title, institution, year_obtained FROM teacher_qualifications WHERE teacher_id IN ({$inIds})");
                $qStmt->execute($teacherIds);
                while ($qr = $qStmt->fetch(PDO::FETCH_ASSOC)) {
                    $qualMap[$qr['teacher_id']][] = [
                        'title'       => $qr['degree_title'],
                        'institution' => $qr['institution'],
                        'year'        => $qr['year_obtained']
                    ];
                }

                $clubMap = [];
                $clStmt = $db->prepare("SELECT ct.teacher_id, c.name FROM club_teachers ct JOIN clubs c ON c.id = ct.club_id WHERE ct.teacher_id IN ({$inIds})");
                $clStmt->execute($teacherIds);
                while ($clr = $clStmt->fetch(PDO::FETCH_ASSOC)) {
                    $clubMap[$clr['teacher_id']][] = $clr['name'];
                }

                $sportMap = [];
                $spStmt = $db->prepare("SELECT st.teacher_id, s.name FROM sport_teachers st JOIN sports s ON s.id = st.sport_id WHERE st.teacher_id IN ({$inIds})");
                $spStmt->execute($teacherIds);
                while ($spr = $spStmt->fetch(PDO::FETCH_ASSOC)) {
                    $sportMap[$spr['teacher_id']][] = $spr['name'];
                }

                foreach ($rows as $row) {
                    $tid = (int)$row['id'];
                    $dbTeachers[] = SqlJsMapper::teacherToJs(
                        $row,
                        $qualMap[$tid] ?? [],
                        $row['homeroom_class_name'] ?? null,
                        $clubMap[$tid] ?? [],
                        $sportMap[$tid] ?? []
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log("Failed to load teachers from DB: " . $e->getMessage());
        }

        if (!empty($dbTeachers)) {
            return $dbTeachers;
        }

        return [
            [
                'firstName'        => 'James',
                'lastName'         => 'Wilson',
                'name'             => 'James Wilson',
                'initials'         => 'JW',
                'id'               => 'T-004',
                'subject'          => 'Science',
                'classes'          => ['6-A', '7-B', '8-C'],
                'role'             => 'Class Teacher',
                'classTeacherOf'   => '6-A',
                'tic'              => 'Science Society',
                'email'            => 'j.wilson@lecole.com',
                'phone'            => '+94 77 123 4567',
                'status'           => 'Active',
                'tone'             => 'bg-midnight text-white',
            ],
            [
                'firstName'        => 'Rohan',
                'lastName'         => 'Dias',
                'name'             => 'Rohan Dias',
                'initials'         => 'RD',
                'id'               => 'T-021',
                'subject'          => 'Mathematics',
                'classes'          => ['9-A', '10-B', '11-C'],
                'role'             => 'Class Teacher',
                'classTeacherOf'   => '9-A',
                'tic'              => 'Chess Club',
                'email'            => 'r.dias@lecole.com',
                'phone'            => '+94 71 987 6543',
                'status'           => 'Deactivated',
                'tone'             => 'bg-sunshine text-white',
            ],
            [
                'firstName'        => 'Sarah',
                'lastName'         => 'Peiris',
                'name'             => 'Sarah Peiris',
                'initials'         => 'SP',
                'id'               => 'T-056',
                'subject'          => 'English',
                'classes'          => ['6-B', '7-A'],
                'role'             => 'Class Teacher',
                'classTeacherOf'   => '6-B',
                'tic'              => 'Debating Society',
                'email'            => 's.peiris@lecole.com',
                'phone'            => '+94 70 456 7890',
                'status'           => 'Active',
                'tone'             => 'bg-terracotta text-white',
            ],
        ];
    }

    public static function getParents(): array {
        $dbParents = [];
        try {
            $dbParents = ParentModel::getAll();
        } catch (\Throwable $e) {
            error_log("Failed to load parents from DB: " . $e->getMessage());
        }

        $mockParents = [
            [
                'firstName' => 'Suresh',
                'lastName'  => 'Perera',
                'name'      => 'Suresh Perera',
                'initials'  => 'SP',
                'id'        => 'P-045',
                'children'  => ['Nethmi Perera — 6-A'],
                'relation'  => 'Father',
                'email'     => 's.perera@gmail.com',
                'phone'     => '+94 77 234 5678',
                'status'    => 'Active',
                'tone'      => 'bg-deepsea text-white',
            ],
            [
                'firstName' => 'Lakshmi',
                'lastName'  => 'Kapoor',
                'name'      => 'Lakshmi Kapoor',
                'initials'  => 'LK',
                'id'        => 'P-112',
                'children'  => ['Maya Kapoor — 7-B', 'Arjun Kapoor — 9-A'],
                'relation'  => 'Mother',
                'email'     => 'l.kapoor@gmail.com',
                'phone'     => '+94 71 345 6789',
                'status'    => 'Active',
                'tone'      => 'bg-deepsea text-white',
            ],
            [
                'firstName' => 'Ranil',
                'lastName'  => 'Silva',
                'name'      => 'Ranil Silva',
                'initials'  => 'RS',
                'id'        => 'P-078',
                'children'  => ['Amara Silva — 8-C'],
                'relation'  => 'Guardian',
                'email'     => 'r.silva@gmail.com',
                'phone'     => '+94 70 456 7891',
                'status'    => 'Deactivated',
                'tone'      => 'bg-maroon text-white',
            ],
        ];

        if (!empty($dbParents)) {
            $dbIds = array_column($dbParents, 'id');
            $filteredMock = array_filter($mockParents, fn($p) => !in_array($p['id'] ?? '', $dbIds, true));
            return array_merge($dbParents, array_values($filteredMock));
        }

        return $mockParents;
    }

    public static function getManagement(): array {
        $dbMgmt = [];
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT m.*, u.activation_status, u.role as user_role
                FROM management_profiles m
                JOIN user_accounts u ON u.id = m.account_id
                ORDER BY m.id ASC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $dbMgmt[] = SqlJsMapper::managementToJs($row);
            }
        } catch (\Throwable $e) {
            error_log("Failed to load management from DB: " . $e->getMessage());
        }

        if (!empty($dbMgmt)) {
            return $dbMgmt;
        }

        return [
            [
                'firstName'      => 'Alex',
                'lastName'       => 'Thompson',
                'name'           => 'Alex Thompson',
                'initials'       => 'AT',
                'id'             => 'M-001',
                'jobTitle'       => 'Enrollment Manager',
                'email'          => 'alex.thompson@lecole.com',
                'phone'          => '+94 77 000 0001',
                'status'         => 'Active',
                'tone'           => 'bg-deepsea text-white',
                'officeLocation' => 'Main Building · Admissions Desk',
            ],
            [
                'firstName'      => 'Maria',
                'lastName'       => 'Rodrigo',
                'name'           => 'Maria Rodrigo',
                'initials'       => 'MR',
                'id'             => 'M-002',
                'jobTitle'       => 'Operations Manager',
                'email'          => 'maria.rodrigo@lecole.com',
                'phone'          => '+94 77 000 0002',
                'status'         => 'Active',
                'tone'           => 'bg-maroon text-white',
                'officeLocation' => 'Main Building · Service Desk',
            ],
            [
                'firstName'      => 'David',
                'lastName'       => 'Kumar',
                'name'           => 'David Kumar',
                'initials'       => 'DK',
                'id'             => 'M-005',
                'jobTitle'       => 'Character Certificate Manager',
                'email'          => 'david.kumar@lecole.com',
                'phone'          => '+94 77 000 0005',
                'status'         => 'Active',
                'tone'           => 'bg-moss text-white',
                'officeLocation' => 'Main Building · Records Desk',
            ],
        ];
    }

    public static function getTabThemes(): array {
        return [
            'Students' => [
                'accentClass' => 'c-tone-sky',
                'tint'        => 'rgba(127,199,204,0.15)',
                'headerTint'  => 'rgba(127,199,204,0.2)',
                'rowHover'    => 'c-row-hover-sky',
                'tone'        => 'sky',
            ],
            'Teachers' => [
                'accentClass' => 'c-tone-sunshine',
                'tint'        => 'rgba(234,137,19,0.15)',
                'headerTint'  => 'rgba(234,137,19,0.2)',
                'rowHover'    => 'c-row-hover-sunshine',
                'tone'        => 'sunshine',
            ],
            'Parents' => [
                'accentClass' => 'c-tone-terracotta',
                'tint'        => 'rgba(175,80,49,0.1)',
                'headerTint'  => 'rgba(175,80,49,0.15)',
                'rowHover'    => 'c-row-hover-terracotta',
                'tone'        => 'terracotta',
            ],
            'Management Panel' => [
                'accentClass' => 'c-tone-maroon',
                'tint'        => 'rgba(127,3,3,0.1)',
                'headerTint'  => 'rgba(127,3,3,0.1)',
                'rowHover'    => 'c-row-hover-maroon',
                'tone'        => 'maroon',
            ],
        ];
    }
}
