<?php

class CertificateModel {

    /**
     * Complete seed dataset of character certificates
     */
    private static array $certificates = [
        [
            'id'            => '2021/0456',
            'name'          => 'Nethmi Perera',
            'cohort'        => 'Grade 13-A · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Pending review',
            'requestedOn'   => '21 Jul 2026',
            'academic'      => 'Nethmi has consistently demonstrated strong academic commitment, contributing thoughtfully in class and maintaining a high standard across her senior subjects.',
            'conduct'       => 'She is courteous, dependable, and respectful toward peers and staff. Nethmi has carried her responsibilities with maturity throughout her time at L’École.',
            'activities'    => 'Senior Prefect · School Choir · Inter-school Public Speaking finalist',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [
                [
                    'id' => 'REQ-2026-001',
                    'submittedAt' => '22 Jul 2026, 09:30 AM',
                    'status' => 'Pending',
                    'category' => 'Academic',
                    'title' => 'National Science Olympiad',
                    'description' => 'I was also the runner-up in the National Science Olympiad in 2025, which is not mentioned in my academic section.',
                    'evidenceFiles' => [
                        ['name' => 'Olympiad_Certificate.pdf', 'url' => '#', 'type' => 'pdf', 'size' => '1.2 MB']
                    ],
                    'assignedTeacher' => 'Mr. Silva (Science)',
                    'teacherStatus' => 'Approved',
                    'managementStatus' => 'Pending',
                    'activityLog' => [
                        ['id' => 'ACT-REQ-1', 'timestamp' => '22 Jul 2026, 09:30 AM', 'action' => 'Student submitted request', 'user' => 'Nethmi Perera'],
                        ['id' => 'ACT-REQ-2', 'timestamp' => '22 Jul 2026, 09:35 AM', 'action' => 'Automatically routed to Subject Teacher', 'user' => 'System'],
                        ['id' => 'ACT-REQ-3', 'timestamp' => '23 Jul 2026, 10:15 AM', 'action' => 'Teacher reviewed and approved', 'user' => 'Mr. Silva']
                    ]
                ],
                [
                    'id' => 'REQ-2026-005',
                    'submittedAt' => '23 Jul 2026, 10:00 AM',
                    'status' => 'Pending',
                    'category' => 'Academic',
                    'title' => 'Math Competition Winner',
                    'description' => 'I won first place in the regional math competition.',
                    'evidenceFiles' => [],
                    'assignedTeacher' => 'Mr. Silva (Science)',
                    'teacherStatus' => 'Approved',
                    'managementStatus' => 'Pending',
                    'activityLog' => [
                        ['id' => 'ACT-REQ-9', 'timestamp' => '23 Jul 2026, 10:00 AM', 'action' => 'Student submitted request', 'user' => 'Nethmi Perera']
                    ]
                ]
            ],
            'certificateRequests' => [
                [
                    'id' => 'CERT-REQ-001',
                    'submittedAt' => '24 Jul 2026, 08:00 AM',
                    'status' => 'Pending',
                    'purpose' => 'University Application',
                    'copies' => 2,
                    'deliveryMethod' => 'Pickup from Office',
                    'activityLog' => [
                        ['id' => 'ACT-CREQ-1', 'timestamp' => '24 Jul 2026, 08:00 AM', 'action' => 'Student requested official certificate', 'user' => 'Nethmi Perera']
                    ]
                ]
            ],
            'activityLog' => [
                ['id' => 'ACT-1', 'timestamp' => '21 Jul 2026, 10:00 AM', 'action' => 'Certificate generated', 'user' => 'System'],
                ['id' => 'ACT-2', 'timestamp' => '22 Jul 2026, 09:30 AM', 'action' => 'Student submitted correction request REQ-2026-001', 'user' => 'Nethmi Perera']
            ]
        ],
        [
            'id'            => '2022/0112',
            'name'          => 'James Smith',
            'cohort'        => 'Grade 11-B · Transfer request',
            'reason'        => 'Leaving student',
            'status'        => 'Pending review',
            'requestedOn'   => '19 Jul 2026',
            'academic'      => 'James has made steady progress in his academic work and approaches feedback with a positive, practical attitude.',
            'conduct'       => 'He is a cooperative member of the school community who is known for his calm manner and respectful conduct.',
            'activities'    => 'Robotics & AI Lab · House athletics',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [
                [
                    'id' => 'REQ-2026-002',
                    'submittedAt' => '23 Jul 2026, 11:00 AM',
                    'status' => 'Pending',
                    'category' => 'Sports',
                    'title' => 'Inter-house Athletics Bronze',
                    'description' => 'I won bronze in the 100m sprint.',
                    'evidenceFiles' => [],
                    'assignedTeacher' => 'Mr. Perera (PE)',
                    'teacherStatus' => 'Pending',
                    'managementStatus' => 'Pending',
                    'activityLog' => [
                        ['id' => 'ACT-REQ-4', 'timestamp' => '23 Jul 2026, 11:00 AM', 'action' => 'Student submitted request', 'user' => 'James Smith']
                    ]
                ]
            ],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/0879',
            'name'          => 'Ayesha Fernando',
            'cohort'        => 'Grade 13-C · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Pending review',
            'requestedOn'   => '18 Jul 2026',
            'academic'      => 'Ayesha is a focused learner whose work reflects curiosity, discipline, and an ability to communicate ideas clearly.',
            'conduct'       => 'She has demonstrated excellent judgement, consideration for others, and a consistent willingness to contribute beyond the classroom.',
            'activities'    => 'Debate Society Head · Environmental Council',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [
                [
                    'id' => 'REQ-2026-003',
                    'submittedAt' => '24 Jul 2026, 02:15 PM',
                    'status' => 'Pending',
                    'category' => 'Club',
                    'title' => 'Debate Society Best Speaker',
                    'description' => 'Awarded best speaker at the regional debate tournament.',
                    'evidenceFiles' => [],
                    'assignedTeacher' => 'Ms. Silva (English)',
                    'teacherStatus' => 'Approved',
                    'managementStatus' => 'Pending',
                    'activityLog' => [
                        ['id' => 'ACT-REQ-5', 'timestamp' => '24 Jul 2026, 02:15 PM', 'action' => 'Student submitted request', 'user' => 'Ayesha Fernando'],
                        ['id' => 'ACT-REQ-6', 'timestamp' => '25 Jul 2026, 09:00 AM', 'action' => 'Teacher reviewed and approved', 'user' => 'Ms. Silva']
                    ]
                ]
            ],
            'certificateRequests' => []
        ],
        [
            'id'            => '2020/1102',
            'name'          => 'Rahul Wijesinghe',
            'cohort'        => 'Grade 13-B · Class of 2025',
            'reason'        => 'Graduating student',
            'status'        => 'Issued',
            'requestedOn'   => '12 Jul 2026',
            'academic'      => 'Rahul completed his senior studies with a consistent commitment to learning and a strong collaborative approach.',
            'conduct'       => 'He is an engaged, disciplined student who has represented the school positively in every setting.',
            'activities'    => 'Varsity Football Club · House Captain',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [
                [
                    'id' => 'REQ-2026-004',
                    'submittedAt' => '10 Jul 2026, 10:00 AM',
                    'status' => 'Resolved',
                    'category' => 'Sports',
                    'title' => 'Varsity Football Captain',
                    'description' => 'I was the captain of the team this year.',
                    'evidenceFiles' => [],
                    'assignedTeacher' => 'Mr. Perera (PE)',
                    'teacherStatus' => 'Approved',
                    'managementStatus' => 'Approved',
                    'activityLog' => [
                        ['id' => 'ACT-REQ-7', 'timestamp' => '10 Jul 2026, 10:00 AM', 'action' => 'Student submitted request', 'user' => 'Rahul Wijesinghe'],
                        ['id' => 'ACT-REQ-8', 'timestamp' => '11 Jul 2026, 11:00 AM', 'action' => 'Management approved request', 'user' => 'Management Panel']
                    ]
                ]
            ],
            'certificateRequests' => []
        ],
        [
            'id'            => '2022/0334',
            'name'          => 'Michael Chen',
            'cohort'        => 'Grade 10-A · Relocation',
            'reason'        => 'Leaving student',
            'status'        => 'Issued',
            'requestedOn'   => '09 Jul 2026',
            'academic'      => 'Michael applied himself well to his studies and showed particular enthusiasm for creative and technical subjects.',
            'conduct'       => 'He was a thoughtful and friendly presence within his class, maintaining positive relationships with students and staff.',
            'activities'    => 'Digital Arts Collective',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => [
                [
                    'id' => 'CERT-REQ-002',
                    'submittedAt' => '08 Jul 2026, 09:00 AM',
                    'status' => 'Rejected',
                    'purpose' => 'Personal Copy',
                    'copies' => 5,
                    'deliveryMethod' => 'Mail',
                    'managementNotes' => 'Please request a maximum of 2 copies for personal use.',
                    'activityLog' => [
                        ['id' => 'ACT-CREQ-2', 'timestamp' => '08 Jul 2026, 09:00 AM', 'action' => 'Student requested official certificate', 'user' => 'Michael Chen'],
                        ['id' => 'ACT-CREQ-3', 'timestamp' => '09 Jul 2026, 10:00 AM', 'action' => 'Management rejected request', 'user' => 'Management Panel']
                    ]
                ]
            ]
        ],
        [
            'id'            => '2021/0101',
            'name'          => 'Sarah Jones',
            'cohort'        => 'Grade 13-A · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Pending review',
            'requestedOn'   => '22 Jul 2026',
            'academic'      => 'Sarah has shown excellent dedication to her studies.',
            'conduct'       => 'She is a model student with impeccable behavior.',
            'activities'    => 'Drama Club · Debate Team',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => [
                ['id' => 'CERT-REQ-003', 'submittedAt' => '25 Jul 2026, 08:30 AM', 'status' => 'Pending', 'purpose' => 'Scholarship Application', 'copies' => 1]
            ]
        ],
        [
            'id'            => '2022/0202',
            'name'          => 'David Lee',
            'cohort'        => 'Grade 11-C · Transfer request',
            'reason'        => 'Leaving student',
            'status'        => 'Issued',
            'requestedOn'   => '20 Jul 2026',
            'academic'      => 'David is a hardworking student who excels in mathematics.',
            'conduct'       => 'He is respectful and works well with his peers.',
            'activities'    => 'Math Olympiad · Chess Club',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/0303',
            'name'          => 'Emma Watson',
            'cohort'        => 'Grade 13-B · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Pending review',
            'requestedOn'   => '23 Jul 2026',
            'academic'      => 'Emma is a brilliant student with a passion for literature.',
            'conduct'       => 'She is kind, helpful, and a great leader.',
            'activities'    => 'Book Club President · Student Council',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2023/0404',
            'name'          => 'Oliver Brown',
            'cohort'        => 'Grade 9-A · Relocation',
            'reason'        => 'Leaving student',
            'status'        => 'Pending review',
            'requestedOn'   => '24 Jul 2026',
            'academic'      => 'Oliver is a curious student who loves science.',
            'conduct'       => 'He is energetic and participates actively in class.',
            'activities'    => 'Science Club · Junior Football',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/0505',
            'name'          => 'Sophia Davis',
            'cohort'        => 'Grade 13-C · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Issued',
            'requestedOn'   => '15 Jul 2026',
            'academic'      => 'Sophia is a dedicated student with strong analytical skills.',
            'conduct'       => 'She is responsible and always willing to help others.',
            'activities'    => 'Robotics Club · Volunteer Group',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2022/0606',
            'name'          => 'Lucas Miller',
            'cohort'        => 'Grade 10-B · Transfer request',
            'reason'        => 'Leaving student',
            'status'        => 'Pending review',
            'requestedOn'   => '25 Jul 2026',
            'academic'      => 'Lucas is a creative student who excels in arts.',
            'conduct'       => 'He is polite and gets along well with everyone.',
            'activities'    => 'Art Club · School Band',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/0707',
            'name'          => 'Mia Wilson',
            'cohort'        => 'Grade 13-A · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Issued',
            'requestedOn'   => '10 Jul 2026',
            'academic'      => 'Mia is an outstanding student with a strong academic record.',
            'conduct'       => 'She is a role model for her peers and shows great maturity.',
            'activities'    => 'Swimming Team Captain · Student Council',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2023/0808',
            'name'          => 'Ethan Moore',
            'cohort'        => 'Grade 8-C · Relocation',
            'reason'        => 'Leaving student',
            'status'        => 'Pending review',
            'requestedOn'   => '26 Jul 2026',
            'academic'      => 'Ethan is a bright student who enjoys history and research.',
            'conduct'       => 'He is respectful and always eager to learn.',
            'activities'    => 'History Club · Junior Debate',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2022/0910',
            'name'          => 'Priya Nair',
            'cohort'        => 'Grade 11-A · Transfer request',
            'reason'        => 'Leaving student',
            'status'        => 'Pending review',
            'requestedOn'   => '24 Jul 2026',
            'academic'      => 'Priya applies herself thoughtfully and consistently across her subjects.',
            'conduct'       => 'She is a considerate and dependable member of her class.',
            'activities'    => 'Environmental Council · Netball',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/1011',
            'name'          => 'Daniel Silva',
            'cohort'        => 'Grade 13-C · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Issued',
            'requestedOn'   => '14 Jul 2026',
            'academic'      => 'Daniel has shown strong initiative and perseverance throughout senior school.',
            'conduct'       => 'He contributes positively to group work and school life.',
            'activities'    => 'Athletics · Science Society',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2023/1112',
            'name'          => 'Zara Ahmed',
            'cohort'        => 'Grade 9-B · Relocation',
            'reason'        => 'Leaving student',
            'status'        => 'Pending review',
            'requestedOn'   => '27 Jul 2026',
            'academic'      => 'Zara is an engaged learner who approaches new challenges with curiosity.',
            'conduct'       => 'She is kind, conscientious, and well regarded by her peers.',
            'activities'    => 'Art Club · School Choir',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/0999',
            'name'          => 'Alexander Hamilton',
            'cohort'        => 'Grade 13-A · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Pending review',
            'requestedOn'   => '28 Jul 2026',
            'academic'      => 'Alexander has demonstrated an extraordinary aptitude for learning across all disciplines.',
            'conduct'       => 'His conduct is exemplary. Alexander is a natural leader who inspires those around him.',
            'activities'    => 'President of Debate Society · Economics Club · Track and Field',
            'gender'        => 'male',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ],
        [
            'id'            => '2021/0888',
            'name'          => 'Isabella Martinez',
            'cohort'        => 'Grade 13-B · Class of 2026',
            'reason'        => 'Graduating student',
            'status'        => 'Pending review',
            'requestedOn'   => '29 Jul 2026',
            'academic'      => 'Isabella is an exceptionally gifted student whose academic journey has been marked by continuous excellence.',
            'conduct'       => 'Throughout her time at L’École, Isabella has been a paragon of good character and mature conduct.',
            'activities'    => 'Science Olympiad Team · First Violin · Student Council VP',
            'gender'        => 'female',
            'avatar'        => null,
            'missingRecordRequests' => [],
            'certificateRequests' => []
        ]
    ];

    public static function getAll(): array {
        return self::$certificates;
    }

    public static function getById(string $id): ?array {
        foreach (self::$certificates as $cert) {
            if ($cert['id'] === $id) {
                return $cert;
            }
        }
        return null;
    }
}
