<?php

class ComplaintModel {

    /**
     * Complete seed dataset of complaints & inquiries
     */
    private static array $complaints = [
        [
            'id'            => 1,
            'ref_no'        => 'INQ-2024-1042',
            'parent_id'     => 1,
            'parent_name'   => 'Mr. & Mrs. Perera',
            'parent_email'  => 'perera.family@example.com',
            'parent_phone'  => '+94 77 123 4567',
            'student_name'  => 'Nethmi Perera',
            'student_grade' => 'Grade 10-A',
            'title'         => 'Clarification on Chemistry Term Exam Syllabus',
            'category'      => 'Academic',
            'message'       => 'We noticed that the upcoming Chemistry term exam syllabus includes organic reactions which were not fully covered during regular classes prior to the revision week. Could the subject teacher please clarify the specific chapters tested?',
            'status'        => 'In Progress',
            'priority'      => 'High',
            'created_at'    => '2024-10-18',
            'resolved_at'   => null,
            'responses'     => [
                [
                    'author_name' => 'Academic Affairs',
                    'author_role' => 'Management',
                    'date'        => '2024-10-19',
                    'message'     => 'We have forwarded your note to the Head of Science Department. Mrs. Fernando will be sharing an updated revision outline during tomorrow morning class.'
                ]
            ]
        ],
        [
            'id'            => 2,
            'ref_no'        => 'INQ-2024-1035',
            'parent_id'     => 1,
            'parent_name'   => 'Mr. & Mrs. Perera',
            'parent_email'  => 'perera.family@example.com',
            'parent_phone'  => '+94 77 123 4567',
            'student_name'  => 'Nethmi Perera',
            'student_grade' => 'Grade 10-A',
            'title'         => 'School Bus Route #4 Morning Arrival Timings',
            'category'      => 'Transport',
            'message'       => 'Bus #4 has been arriving 20 minutes past the scheduled pickup window at Rajagiriya junction on rain days. This resulted in students almost missing the morning assembly twice last week.',
            'status'        => 'Resolved',
            'priority'      => 'Medium',
            'created_at'    => '2024-10-10',
            'resolved_at'   => '2024-10-12',
            'responses'     => [
                [
                    'author_name' => 'Transport Office',
                    'author_role' => 'Management',
                    'date'        => '2024-10-12',
                    'message'     => 'The driver route schedule has been adjusted with an earlier 6:40 AM departure from the depot on bad weather days. Timings have normalized and traffic monitoring is active.'
                ]
            ]
        ],
        [
            'id'            => 3,
            'ref_no'        => 'INQ-2024-1029',
            'parent_id'     => 2,
            'parent_name'   => 'Dr. Samantha Silva',
            'parent_email'  => 'samantha.silva@example.com',
            'parent_phone'  => '+94 71 889 2234',
            'student_name'  => 'Kavindu Silva',
            'student_grade' => 'Grade 11-B',
            'title'         => 'Physics Lab Equipment Calibration Request',
            'category'      => 'Facilities',
            'message'       => 'During the recent practical preparation session, several digital multimeters and optical benches in Physics Lab 2 gave erratic measurements according to the students.',
            'status'        => 'In Progress',
            'priority'      => 'High',
            'created_at'    => '2024-10-15',
            'resolved_at'   => null,
            'responses'     => []
        ],
        [
            'id'            => 4,
            'ref_no'        => 'INQ-2024-1018',
            'parent_id'     => 3,
            'parent_name'   => 'Mrs. Nilmini De Silva',
            'parent_email'  => 'nilmini.ds@example.com',
            'parent_phone'  => '+94 70 345 8899',
            'student_name'  => 'Dineth De Silva',
            'student_grade' => 'Grade 8-C',
            'title'         => 'Canteen Healthy Menu Options Query',
            'category'      => 'Facilities',
            'message'       => 'Can we introduce more fresh fruit and unsweetened dairy options in the junior canteen for students with lactose sensitivity and diabetic family histories?',
            'status'        => 'Resolved',
            'priority'      => 'Normal',
            'created_at'    => '2024-09-28',
            'resolved_at'   => '2024-10-02',
            'responses'     => [
                [
                    'author_name' => 'Student Welfare Board',
                    'author_role' => 'Management',
                    'date'        => '2024-10-02',
                    'message'     => 'Thank you for the constructive suggestion. The catering contractor has introduced fresh seasonal cut fruit cups and low-sugar yogurt cups starting this week.'
                ]
            ]
        ],
        [
            'id'            => 5,
            'ref_no'        => 'INQ-2024-1002',
            'parent_id'     => 1,
            'parent_name'   => 'Mr. & Mrs. Perera',
            'parent_email'  => 'perera.family@example.com',
            'parent_phone'  => '+94 77 123 4567',
            'student_name'  => 'Nethmi Perera',
            'student_grade' => 'Grade 10-A',
            'title'         => 'Extracurricular Club Clashing Times (Debating & Swimming)',
            'category'      => 'Extracurricular',
            'message'       => 'Senior Debating society practice on Thursdays at 3:30 PM overlaps directly with Swim squad dry-land training. Is it possible for debating team A to practice on Tuesday afternoons instead?',
            'status'        => 'Resolved',
            'priority'      => 'Normal',
            'created_at'    => '2024-09-15',
            'resolved_at'   => '2024-09-18',
            'responses'     => [
                [
                    'author_name' => 'Activities Coordinator',
                    'author_role' => 'Management',
                    'date'        => '2024-09-18',
                    'message'     => 'The Debating society Master-in-Charge has rescheduled the senior division practice slot to Tuesday 3:30 PM to avoid clash with aquatic disciplines.'
                ]
            ]
        ],
        [
            'id'            => 6,
            'ref_no'        => 'INQ-2024-0994',
            'parent_id'     => 4,
            'parent_name'   => 'Mr. Farhan Rasheed',
            'parent_email'  => 'farhan.r@example.com',
            'parent_phone'  => '+94 76 512 3456',
            'student_name'  => 'Tariq Rasheed',
            'student_grade' => 'Grade 9-B',
            'title'         => 'Duplicate Fee Receipt Notification for Term 3',
            'category'      => 'Administration',
            'message'       => 'We received two differing invoice reference emails for the third term facility maintenance fee. Please verify that our online bank transfer was credited only once.',
            'status'        => 'Resolved',
            'priority'      => 'High',
            'created_at'    => '2024-09-10',
            'resolved_at'   => '2024-09-11',
            'responses'     => [
                [
                    'author_name' => 'Bursar & Accounts Office',
                    'author_role' => 'Management',
                    'date'        => '2024-09-11',
                    'message'     => 'Payment verified. Single credit confirmed against receipt #REC-88421. The duplicate reminder was generated due to an automated batch retry error and has been cleared.'
                ]
            ]
        ]
    ];

    /**
     * Get all complaints (for Management)
     */
    public static function getAll(): array {
        return self::$complaints;
    }

    /**
     * Get complaints for a specific parent (by parent ID or parent name)
     */
    public static function getByParent(int|string $parentIdentifier): array {
        return array_values(array_filter(self::$complaints, function ($c) use ($parentIdentifier) {
            if (is_numeric($parentIdentifier)) {
                return (int)$c['parent_id'] === (int)$parentIdentifier;
            }
            return stripos($c['parent_name'], (string)$parentIdentifier) !== false;
        }));
    }

    /**
     * Get list of children associated with the parent
     */
    public static function getParentChildren(int $parentId = 1): array {
        return [
            ['id' => 1, 'name' => 'Nethmi Perera', 'grade' => 'Grade 10-A'],
        ];
    }

    /**
     * Get list of available categories
     */
    public static function getCategories(): array {
        return [
            'All',
            'Academic',
            'Facilities',
            'Transport',
            'Administration',
            'Extracurricular'
        ];
    }

    /**
     * Get summary metrics for Management dashboard/header
     */
    public static function getMetrics(?array $complaints = null): array {
        if ($complaints === null) {
            $complaints = self::$complaints;
        }
        $total = count($complaints);
        $inProgress = 0;
        $resolved = 0;

        foreach ($complaints as $c) {
            if ($c['status'] === 'In Progress') {
                $inProgress++;
            } elseif ($c['status'] === 'Resolved') {
                $resolved++;
            }
        }

        return [
            'total'       => $total,
            'in_progress' => $inProgress,
            'resolved'    => $resolved,
        ];
    }
}
