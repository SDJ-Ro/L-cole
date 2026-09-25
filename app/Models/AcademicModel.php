<?php
/**
 * =========================================================================
 * L'ÉCOLE — ACADEMIC MODEL
 * =========================================================================
 * Seed and business data for Academic Overview (Admin & Management).
 * =========================================================================
 */

class AcademicModel {

    public static function getGrades(): array {
        return [
            [
                'id'            => 'g6',
                'name'          => 'Grade 6',
                'classes'       => ['6-A', '6-B'],
                'subjectScores' => [
                    'Mathematics'     => [76, 82],
                    'English'         => [80, 78],
                    'Science'         => [75, 80],
                    'History'         => [72, 76],
                    'Sinhala / Tamil' => [79, 77],
                    'ICT'             => [84, 80],
                ]
            ],
            [
                'id'            => 'g7',
                'name'          => 'Grade 7',
                'classes'       => ['7-A', '7-B'],
                'subjectScores' => [
                    'Mathematics'     => [77, 80],
                    'English'         => [79, 82],
                    'Science'         => [76, 79],
                    'History'         => [73, 76],
                    'Sinhala / Tamil' => [78, 80],
                    'ICT'             => [82, 84],
                ]
            ],
            [
                'id'            => 'g8',
                'name'          => 'Grade 8',
                'classes'       => ['8-A', '8-B'],
                'subjectScores' => [
                    'Mathematics'     => [79, 76],
                    'English'         => [82, 79],
                    'Science'         => [77, 75],
                    'History'         => [74, 72],
                    'Sinhala / Tamil' => [81, 78],
                    'ICT'             => [85, 82],
                ]
            ],
            [
                'id'            => 'g9',
                'name'          => 'Grade 9',
                'classes'       => ['9-A', '9-B'],
                'subjectScores' => [
                    'Mathematics'     => [74, 78],
                    'English'         => [80, 82],
                    'Science'         => [75, 78],
                    'History'         => [71, 74],
                    'Sinhala / Tamil' => [77, 79],
                    'ICT'             => [81, 84],
                ]
            ],
            [
                'id'            => 'g10',
                'name'          => 'Grade 10',
                'classes'       => ['10-A', '10-B'],
                'subjectScores' => [
                    'Mathematics'     => [76, 79],
                    'English'         => [81, 83],
                    'Science'         => [77, 80],
                    'History'         => [73, 75],
                    'Sinhala / Tamil' => [78, 81],
                    'ICT'             => [84, 86],
                ]
            ],
            [
                'id'            => 'g11',
                'name'          => 'Grade 11',
                'classes'       => ['11-A', '11-B'],
                'subjectScores' => [
                    'Mathematics'     => [78, 75],
                    'English'         => [82, 80],
                    'Science'         => [79, 76],
                    'History'         => [75, 72],
                    'Sinhala / Tamil' => [80, 78],
                    'ICT'             => [86, 83],
                ]
            ]
        ];
    }

    public static function getSubjects(): array {
        return ['Mathematics', 'English', 'Science', 'History', 'Sinhala / Tamil', 'ICT'];
    }

    public static function getTerms(): array {
        return ['Term 1', 'Term 2', 'Term 3'];
    }

    public static function getExamEvents(): array {
        return [
            ['id' => 'exam-17', 'date' => '2026-06-17', 'time' => '08:30–10:30', 'title' => 'Mathematics examination',     'details' => 'Grades 6–8 · Respective classrooms',  'category' => 'Academic'],
            ['id' => 'exam-18', 'date' => '2026-06-18', 'time' => '08:30–10:30', 'title' => 'English examination',         'details' => 'Grades 6–8 · Respective classrooms',  'category' => 'Academic'],
            ['id' => 'exam-19', 'date' => '2026-06-19', 'time' => '08:30–10:30', 'title' => 'Science examination',         'details' => 'Grades 6–11 · Respective classrooms', 'category' => 'Academic'],
            ['id' => 'exam-20', 'date' => '2026-06-20', 'time' => '08:30–10:00', 'title' => 'History examination',         'details' => 'Grades 6–11 · Respective classrooms', 'category' => 'Academic'],
            ['id' => 'exam-23', 'date' => '2026-06-23', 'time' => '08:30–10:30', 'title' => 'Sinhala / Tamil examination', 'details' => 'Grades 6–11 · Respective classrooms', 'category' => 'Academic'],
            ['id' => 'exam-24', 'date' => '2026-06-24', 'time' => '08:30–11:00', 'title' => 'ICT practical assessment',    'details' => 'Grades 9–11 · Computer laboratories',  'category' => 'Academic'],
            ['id' => 'exam-26', 'date' => '2026-06-26', 'time' => '08:30–10:30', 'title' => 'Make-up examination session', 'details' => 'Grades 6–11 · Library seminar room',   'category' => 'Academic'],
        ];
    }

    public static function getInitialClassPerformance(string $gradeId = 'g6', string $subject = 'Mathematics', string $term = 'Term 1'): array {
        $grades = self::getGrades();
        $selectedGrade = null;
        foreach ($grades as $g) {
            if ($g['id'] === $gradeId || $g['name'] === $gradeId) {
                $selectedGrade = $g;
                break;
            }
        }
        if (!$selectedGrade) {
            $selectedGrade = $grades[0];
        }

        $scores = $selectedGrade['subjectScores'][$subject] ?? [];
        $termOffset = ($term === 'Term 2') ? 3 : (($term === 'Term 3') ? 6 : 0);
        $classAvg = !empty($scores) ? (int)round(array_sum($scores) / count($scores)) : 0;

        $result = [];
        foreach ($selectedGrade['classes'] as $index => $section) {
            $avg = isset($scores[$index]) ? $scores[$index] : $classAvg;
            $avg = min(100, $avg + $termOffset);
            $high = min(100, $avg + 13);
            $result[] = [
                'label'   => $section,
                'average' => $avg,
                'highest' => $high,
            ];
        }
        return $result;
    }

    public static function getCurriculumGroups(): array {
        return [
            [
                'range'       => 'Years 6–9',
                'description' => 'Lower secondary core foundation curriculum.',
                'subjects'    => ['English', 'Mathematics', 'Science', 'Humanities', 'Sinhala / Tamil', 'ICT'],
            ],
            [
                'range'       => 'Years 10–11',
                'description' => 'Senior secondary exam preparation and specialization.',
                'subjects'    => ['English Language', 'Mathematics', 'Science', 'History', 'Business Studies', 'ICT'],
            ],
        ];
    }

    public static function getStaffAssignments(): array {
        return [
            ['id' => 'priya-de-silva',   'name' => 'Priya De Silva',   'subject' => 'Visual Arts',                'subjectClasses' => [],                     'extracurriculars' => []],
            ['id' => 'anura-wijesinghe', 'name' => 'Anura Wijesinghe', 'subject' => 'Geography',                  'subjectClasses' => ['8-A', '9-B'],         'extracurriculars' => []],
            ['id' => 'sofia-fernando',   'name' => 'Sofia Fernando',   'subject' => 'Subject allocation pending', 'subjectClasses' => [],                     'extracurriculars' => ['Eco Club']],
            ['id' => 'james-wilson',     'name' => 'James Wilson',     'subject' => 'Science',                    'subjectClasses' => ['6-A', '7-B', '8-C'], 'extracurriculars' => ['Science Society']],
            ['id' => 'sarah-peiris',     'name' => 'Sarah Peiris',     'subject' => 'English',                    'subjectClasses' => ['6-B', '7-A'],         'extracurriculars' => ['Debate Society']],
            ['id' => 'rohan-dias',       'name' => 'Rohan Dias',       'subject' => 'Mathematics',                'subjectClasses' => ['9-A', '10-B', '11-C'],'extracurriculars' => ['Chess Club']],
            ['id' => 'shanthi-silva',    'name' => 'Shanthi Silva',    'subject' => 'Computer Science',           'subjectClasses' => [],                     'extracurriculars' => ['Robotics & AI Lab']],
            ['id' => 'madhavi-fernando', 'name' => 'Madhavi Fernando', 'subject' => 'English Literature',         'subjectClasses' => ['7-C', '11-A'],        'extracurriculars' => ['L’École Philharmonic']],
        ];
    }

    public static function getClassTeachers(): array {
        return [
            '6-A' => 'James Wilson', '6-B' => 'Sarah Peiris', '6-C' => 'Nethmi Perera', '6-D' => 'Amara Silva',
            '7-A' => 'Kavindi Jayasinghe', '7-B' => 'Rohan Dias', '7-C' => 'Madhavi Fernando',
            '8-A' => 'Ishara Perera', '8-B' => 'David Peris', '8-C' => 'Nimali Wijesekara', '8-D' => 'Samira Cooray',
            '9-A' => 'Ruwan Silva', '9-B' => 'Nadeesha Pinto', '9-C' => 'Tharindu Jayasuriya',
            '10-A' => 'Chandani Fernando', '10-B' => 'Mihiran De Silva', '10-C' => 'Sashika Ramanayake', '10-D' => 'Rukshan Abeysinghe',
            '11-A' => 'Anjali Perera', '11-B' => 'Pradeep Ratnayake', '11-C' => 'Harsha Wickramasinghe'
        ];
    }

    public static function getClassEnrollments(): array {
        return [
            '6-A' => 30, '6-B' => 29, '6-C' => 31, '6-D' => 30,
            '7-A' => 44, '7-B' => 43, '7-C' => 43,
            '8-A' => 35, '8-B' => 34, '8-C' => 36, '8-D' => 35,
            '9-A' => 50, '9-B' => 49, '9-C' => 51,
            '10-A' => 40, '10-B' => 40, '10-C' => 40, '10-D' => 40,
            '11-A' => 52, '11-B' => 51, '11-C' => 52
        ];
    }
}
