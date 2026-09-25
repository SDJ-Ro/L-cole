<?php
/**
 * =========================================================================
 * L'ÉCOLE — PEOPLE DIRECTORY MODEL
 * =========================================================================
 * Central data provider for the Users / People Directory across all portals.
 * Ported from Admin/people/data.js. Replace with PDO/SQL queries later.
 * =========================================================================
 */

require_once __DIR__ . '/AcademicModel.php';

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
        return AcademicModel::getClassEnrollments();
    }

    public static function getStudents(): array {
        return [
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
    }

    public static function getTeachers(): array {
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
        return [
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
    }

    public static function getManagement(): array {
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
