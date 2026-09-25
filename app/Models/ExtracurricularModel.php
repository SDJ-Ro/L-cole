<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR MODEL
 * =========================================================================
 * Mock data layer for sports, societies, and clubs across all roles.
 * Provides data access for cards, detailed profiles, coaches, and awards.
 * When integrating a real database later, this Model's methods will query PDO.
 * =========================================================================
 */

class ExtracurricularModel {

    /**
     * Complete seed dataset ported from original Admin & Teacher modules.
     */
    private static array $clubs = [
        [
            'id'        => 1,
            'name'      => 'Varsity Football Club',
            'type'      => 'Sports',
            'category'  => 'Athletics',
            'theme'     => 'main-sport',
            'bgIcon'    => 'trophy',
            'desc'      => 'Competitive league training and internal tournaments for senior grades.',
            'status'    => 'Active',
            'createdAt' => '15 Jan 2024',
            'image'     => 'https://images.unsplash.com/photo-1518605368461-1e1e38ce1548?w=800&h=400&fit=crop',
            'tic'       => [
                'name'    => 'Mr. Weerasinghe',
                'avatar'  => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces',
                'email'   => 'weerasinghe@lecole.edu',
                'subject' => 'Physical Education'
            ],
            'coach'     => [
                'name'      => 'Coach Dinesh',
                'avatar'    => 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=100&h=100&fit=crop&crop=faces',
                'phone'     => '+94 77 123 4567',
                'specialty' => 'UEFA B Licensed'
            ],
            'schedule'  => 'Mondays & Wednesdays, 3:30 – 5:30 PM',
            'location'  => 'Main Football Pitch',
            'ageGroups' => ['Under 15', 'Under 19'],
            'stats'     => ['members' => 32, 'teams' => 2, 'trophies' => 4],
            'unassignedStudents' => [
                ['id' => 'u1', 'name' => 'Sahan Peiris',    'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&h=100&fit=crop', 'ageGroup' => 'Under 19'],
                ['id' => 'u2', 'name' => 'Kushan Silva',    'grade' => 'Grade 9',  'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'ageGroup' => 'Under 15'],
                ['id' => 'u3', 'name' => 'Hasindu Bandara', 'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop', 'ageGroup' => 'Under 19'],
            ],
            'squadTitle' => 'Varsity Football Squad',
            'team'       => [
                ['name' => 'Kavindu Perera',    'grade' => 'Grade 11', 'role' => 'Captain / Striker'],
                ['name' => 'Sanjula Silva',     'grade' => 'Grade 11', 'role' => 'Vice Captain / Midfield'],
                ['name' => 'Jason Perera',      'grade' => 'Grade 11', 'role' => 'Midfielder'],
                ['name' => 'Isuru Bandara',     'grade' => 'Grade 11', 'role' => 'Goalkeeper'],
                ['name' => 'Ravindu Alwis',     'grade' => 'Grade 9',  'role' => 'Center Back'],
                ['name' => 'Tharindu Jay',      'grade' => 'Grade 10', 'role' => 'Winger'],
                ['name' => 'Sahan Peiris',      'grade' => 'Grade 11', 'role' => 'Left Back'],
                ['name' => 'Kushan Silva',      'grade' => 'Grade 9',  'role' => 'Right Back'],
                ['name' => 'Hasindu Bandara',   'grade' => 'Grade 11', 'role' => 'Defensive Midfield'],
                ['name' => 'Dineth Wijesinghe', 'grade' => 'Grade 10', 'role' => 'Attacking Midfield'],
                ['name' => 'Malith Perera',     'grade' => 'Grade 12', 'role' => 'Forward'],
                ['name' => 'Nipuna Senaratne',  'grade' => 'Grade 10', 'role' => 'Right Winger'],
                ['name' => 'Charith De Silva',  'grade' => 'Grade 12', 'role' => 'Defender'],
                ['name' => 'Anjana Samarakoon', 'grade' => 'Grade 9',  'role' => 'Reserve Goalkeeper'],
                ['name' => 'Devinda Fernando',  'grade' => 'Grade 11', 'role' => 'Forward'],
            ],
            'teams'     => [
                [
                    'name'       => 'Team A (Senior)',
                    'ageGroup'   => 'Under 19',
                    'coverImage' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'r1', 'name' => 'Kavindu Perera', 'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop', 'position' => 'Captain / Striker'],
                        ['id' => 'r2', 'name' => 'Sanjula Silva',  'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop', 'position' => 'Vice Captain / Midfield'],
                        ['id' => 'r3', 'name' => 'Isuru Bandara',  'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop', 'position' => 'Goalkeeper'],
                    ]
                ],
                [
                    'name'       => 'Team B (Junior)',
                    'ageGroup'   => 'Under 15',
                    'coverImage' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'r4', 'name' => 'Ravindu Alwis', 'grade' => 'Grade 9',  'avatar' => 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=100&h=100&fit=crop', 'position' => 'Defender'],
                        ['id' => 'r5', 'name' => 'Tharindu Jay',   'grade' => 'Grade 10', 'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'position' => 'Winger'],
                    ]
                ]
            ],
            'notices'   => [
                [
                    'id'       => 'n-1',
                    'title'    => 'New Training Kit Distribution',
                    'date'     => '22 OCT 2024',
                    'body'     => 'Collect the new season kit from the sports office before Friday afternoon.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Coach Dinesh',
                    'pinned'   => true
                ],
                [
                    'id'       => 'n-2',
                    'title'    => 'Fitness Assessment Week',
                    'date'     => '18 OCT 2024',
                    'body'     => 'Mandatory fitness screening for all Team A & B players.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Coach Dinesh',
                    'pinned'   => false
                ]
            ],
            'awards'    => [
                [
                    'id'             => 'f-aw-1',
                    'title'          => 'Div-1 League Runners Up',
                    'year'           => '2023',
                    'level'          => 'Provincial',
                    'kind'           => 'Team',
                    'tournament'     => 'Provincial Div-1 Football League',
                    'date'           => 'Nov 18, 2023',
                    'venue'          => 'SSC Grounds, Colombo',
                    'place'          => 'Runners Up',
                    'colours'        => 'Silver Medal',
                    'ageGroup'       => 'Under 19',
                    'organisedBy'    => 'Western Province Schools Football Association',
                    'participants'   => ['Kavindu Perera', 'Sanjula Silva', 'Isuru Bandara', 'Ravindu Alwis', 'Tharindu Jay'],
                    'bio'            => 'After an undefeated group stage, the senior squad battled through a tense semi-final on penalties to reach the provincial final. The team finished as runners up — the club’s best league result in over a decade.',
                    'details'        => 'A 10-team provincial league contested over the season. The squad finished top of the group stage before falling narrowly in the final, 2–1 after extra time.',
                    'image'          => 'https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=800&h=400&fit=crop',
                    'gallery'        => [
                        'https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?w=600&h=400&fit=crop',
                        'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=600&h=400&fit=crop'
                    ]
                ],
                [
                    'id'             => 'f-aw-2',
                    'title'          => 'Best Striker of the Season',
                    'year'           => '2023',
                    'level'          => 'Inter-School',
                    'kind'           => 'Individual',
                    'tournament'     => 'Inter-School Football Circuit',
                    'date'           => 'Dec 02, 2023',
                    'venue'          => 'Colombo',
                    'place'          => 'Golden Boot Winner',
                    'colours'        => 'Gold Medal',
                    'ageGroup'       => 'Under 19',
                    'organisedBy'    => 'Inter-School Football Circuit Committee',
                    'recipient'      => 'Kavindu Perera',
                    'participants'   => ['Kavindu Perera'],
                    'bio'            => 'Kavindu topped the scoring charts with 18 goals in 14 matches, earning the season’s Best Striker recognition and a call-up to the provincial youth training pool.',
                    'details'        => 'Awarded to the highest-scoring player across the inter-school circuit, judged on goals, assists, and overall offensive contribution.',
                    'image'          => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&h=400&fit=crop',
                    'gallery'        => []
                ]
            ]
        ],
        [
            'id'        => 2,
            'name'      => 'Digital Arts Collective',
            'type'      => 'Clubs and Societies',
            'category'  => 'Creative Arts',
            'theme'     => 'creative-arts',
            'bgIcon'    => 'palette',
            'desc'      => 'Exploring digital media, graphic design, 3D modelling, and animation.',
            'status'    => 'Pending',
            'createdAt' => '22 Oct 2024',
            'image'     => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=800&h=400&fit=crop',
            'tic'       => [
                'name'    => 'Mr. Silva',
                'avatar'  => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=faces',
                'email'   => 'silva.art@lecole.edu',
                'phone'   => '+94 77 555 1234',
                'subject' => 'Art & Design'
            ],
            'coach'     => [
                'name'      => 'Ms. Natalie Fernando',
                'avatar'    => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=faces',
                'phone'     => '+94 77 555 1234',
                'specialty' => 'Digital Illustration & UI/UX'
            ],
            'schedule'  => 'Fridays, 3:00 – 4:30 PM',
            'location'  => 'Media Lab 2',
            'ageGroups' => ['Grade 7 – 12'],
            'stats'     => ['members' => 18, 'teams' => 1, 'trophies' => 1],
            'squadTitle' => 'Digital Arts Guild',
            'team'       => [
                ['name' => 'Ishara Wickramasinghe', 'grade' => 'Grade 11', 'role' => 'Guild Lead / 3D'],
                ['name' => 'Nethmi Fernando',       'grade' => 'Grade 10', 'role' => 'Lead UI Designer'],
                ['name' => 'Jason Perera',          'grade' => 'Grade 11', 'role' => 'Concept Artist'],
                ['name' => 'Raveen Jayawardena',    'grade' => 'Grade 12', 'role' => 'Digital Illustrator'],
                ['name' => 'Shenali Peiris',        'grade' => 'Grade 10', 'role' => 'Motion Graphics'],
                ['name' => 'Kaveen Dias',           'grade' => 'Grade 11', 'role' => 'VFX & Compositor'],
                ['name' => 'Anuki Senanayake',      'grade' => 'Grade 9',  'role' => 'Vector Artist'],
                ['name' => 'Dulitha Bandara',       'grade' => 'Grade 12', 'role' => 'Game Asset Designer'],
                ['name' => 'Methmi Perera',         'grade' => 'Grade 10', 'role' => 'Character Modeller'],
                ['name' => 'Praveen Fonseka',       'grade' => 'Grade 11', 'role' => 'Typography Specialist'],
                ['name' => 'Sanuki Alwis',          'grade' => 'Grade 9',  'role' => 'Graphic Designer'],
                ['name' => 'Minura Rathnayake',     'grade' => 'Grade 12', 'role' => 'Video Editor'],
                ['name' => 'Thejana Silva',         'grade' => 'Grade 11', 'role' => 'Storyboard Artist'],
            ],
            'teams'     => [
                [
                    'name'       => 'Digital Design Studio',
                    'ageGroup'   => 'Grade 7 – 12',
                    'coverImage' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'da-1', 'name' => 'Ishara Wickramasinghe', 'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop', 'position' => 'Lead Animator'],
                        ['id' => 'da-2', 'name' => 'Nethmi Fernando',       'grade' => 'Grade 10', 'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'position' => 'UI Designer'],
                    ]
                ]
            ],
            'notices'   => [
                [
                    'id'       => 'da-n1',
                    'title'    => 'Showcase Submission Deadline',
                    'date'     => '20 OCT 2024',
                    'body'     => 'Submit your digital portfolios by this Friday for entry into the National Youth Media Showcase.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Mr. Silva',
                    'pinned'   => true
                ]
            ],
            'awards'    => [
                [
                    'id'           => 'd-aw-1',
                    'title'        => 'National Digital Media Showcase',
                    'year'         => '2023',
                    'level'        => 'National',
                    'kind'         => 'Individual',
                    'tournament'   => 'Sri Lanka Young Digital Designers Showcase',
                    'date'         => 'Oct 14, 2023',
                    'venue'        => 'Exhibition Centre, BMICH',
                    'place'        => 'Merit Award',
                    'colours'      => 'Bronze Medal',
                    'ageGroup'     => 'Grade 10 – 12',
                    'organisedBy'  => 'Design Education Council',
                    'recipient'    => 'Ishara Wickramasinghe',
                    'participants' => ['Ishara Wickramasinghe'],
                    'bio'          => 'Created an interactive 3D virtual tour of historic Sri Lankan heritage sites using Blender and WebGL.',
                    'details'      => 'Judged by leading industry practitioners across visual storytelling, technical complexity, and UI design.',
                    'image'        => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=800&h=400&fit=crop',
                    'gallery'      => []
                ]
            ]
        ],
        [
            'id'        => 3,
            'name'      => 'Cricket Club',
            'type'      => 'Sports',
            'category'  => 'Athletics',
            'theme'     => 'main-sport',
            'bgIcon'    => 'medal',
            'desc'      => 'Premier inter-school cricket program for U13, U15, and U17 age divisions.',
            'status'    => 'Active',
            'createdAt' => '10 Jan 2024',
            'image'     => 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800&h=400&fit=crop',
            'tic'       => [
                'name'    => 'Mr. Weerasinghe',
                'avatar'  => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces',
                'email'   => 'weerasinghe@lecole.edu',
                'subject' => 'Physical Education'
            ],
            'coach'     => [
                'name'      => 'Coach Dinesh',
                'avatar'    => 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=100&h=100&fit=crop&crop=faces',
                'phone'     => '+94 77 123 4567',
                'specialty' => 'Level 3 High Performance Coach'
            ],
            'schedule'  => 'Tuesdays & Thursdays, 3:30 – 5:30 PM',
            'location'  => 'Main Cricket Grounds',
            'ageGroups' => ['Under 13', 'Under 15', 'Under 17'],
            'stats'     => ['members' => 45, 'teams' => 3, 'trophies' => 8],
            'joinRequests' => [
                [
                    'id'            => 'p1',
                    'name'          => 'Ravindu Silva',
                    'grade'         => 'Grade 9',
                    'ageGroup'      => 'U15',
                    'indexNo'       => '2022/0301',
                    'dateSubmitted' => '2024-06-12',
                    'parentStatus'  => 'PARENT: APPROVED',
                    'avatar'        => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&h=100&fit=crop'
                ],
                [
                    'id'            => 'p2',
                    'name'          => 'Kavindu Rathnayake',
                    'grade'         => 'Grade 7',
                    'ageGroup'      => 'U13',
                    'indexNo'       => '2021/0177',
                    'dateSubmitted' => '2024-06-10',
                    'parentStatus'  => 'PARENT: APPROVED',
                    'avatar'        => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop'
                ],
                [
                    'id'            => 'p3',
                    'name'          => 'Sanhinda Perera',
                    'grade'         => 'Grade 10',
                    'ageGroup'      => 'U17',
                    'indexNo'       => '2020/0412',
                    'dateSubmitted' => '2024-06-14',
                    'parentStatus'  => 'PARENT: APPROVED',
                    'avatar'        => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=100&h=100&fit=crop'
                ],
                [
                    'id'            => 'p4',
                    'name'          => 'Danula Bandara',
                    'grade'         => 'Grade 8',
                    'ageGroup'      => 'U15',
                    'indexNo'       => '2022/0589',
                    'dateSubmitted' => '2024-06-15',
                    'parentStatus'  => 'PARENT: APPROVED',
                    'avatar'        => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop'
                ],
                [
                    'id'            => 'p5',
                    'name'          => 'Sachith Fernando',
                    'grade'         => 'Grade 9',
                    'ageGroup'      => 'U15',
                    'indexNo'       => '2022/0124',
                    'dateSubmitted' => '2024-06-16',
                    'parentStatus'  => 'PARENT: APPROVED',
                    'avatar'        => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop'
                ]
            ],
            'squadTitle' => 'U17 Cricket Squad',
            'team'       => [
                ['name' => 'Ravindu Jayasekara',    'grade' => 'Grade 12', 'role' => 'Captain'],
                ['name' => 'Dinuka Wickramasinghe', 'grade' => 'Grade 12', 'role' => 'Vice-Captain'],
                ['name' => 'Jason Perera',          'grade' => 'Grade 11', 'role' => 'All-rounder'],
                ['name' => 'Tharindu Madushan',     'grade' => 'Grade 11', 'role' => 'Fast Bowler'],
                ['name' => 'Sachintha Bandara',     'grade' => 'Grade 10', 'role' => 'Wicketkeeper'],
                ['name' => 'Kavindu Rathnayake',    'grade' => 'Grade 10', 'role' => 'Batsman'],
                ['name' => 'Nipun Senanayake',      'grade' => 'Grade 12', 'role' => 'Batsman'],
                ['name' => 'Yohan Fernando',        'grade' => 'Grade 11', 'role' => 'Fast Bowler'],
                ['name' => 'Malith Gunasekara',     'grade' => 'Grade 11', 'role' => 'Spin Bowler'],
                ['name' => 'Chamika Abeywardena',   'grade' => 'Grade 10', 'role' => 'All-rounder'],
                ['name' => 'Ruwan Dissanayake',     'grade' => 'Grade 10', 'role' => 'Batsman'],
                ['name' => 'Hasitha Karunaratne',   'grade' => 'Grade 9',  'role' => 'Spin Bowler'],
                ['name' => 'Isuru Weligama',        'grade' => 'Grade 9',  'role' => 'Fielder'],
                ['name' => 'Nadeesha Perera',       'grade' => 'Grade 9',  'role' => 'All-rounder'],
                ['name' => 'Chathura Ekanayake',    'grade' => 'Grade 12', 'role' => 'Fielder'],
            ],
            'teams'     => [
                [
                    'name'       => '1st XI (Senior)',
                    'ageGroup'   => 'Under 17',
                    'coverImage' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'cr-1', 'name' => 'Dilan Jayasuriya', 'grade' => 'Grade 12', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop', 'position' => 'Captain / All-Rounder'],
                        ['id' => 'cr-2', 'name' => 'Kavindu Perera',   'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop', 'position' => 'Vice Captain / Batsman'],
                        ['id' => 'cr-3', 'name' => 'Sanjula Silva',    'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop', 'position' => 'Wicketkeeper'],
                    ]
                ],
                [
                    'name'       => '2nd XI (Junior)',
                    'ageGroup'   => 'Under 15',
                    'coverImage' => 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'cr-4', 'name' => 'Sachith Fernando', 'grade' => 'Grade 9',  'avatar' => 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=100&h=100&fit=crop', 'position' => 'Pace Bowler'],
                        ['id' => 'cr-5', 'name' => 'Tharindu Jay',     'grade' => 'Grade 10', 'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'position' => 'Opening Batsman'],
                    ]
                ]
            ],
            'notices'   => [
                [
                    'id'       => 'cn-1',
                    'title'    => 'Net Practice Schedule',
                    'date'     => '15 OCT 2024',
                    'body'     => 'Net practice sessions begin promptly at 3:30 PM on Tuesdays and Thursdays on the turf wickets.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Coach Dinesh',
                    'pinned'   => true
                ],
                [
                    'id'       => 'cn-2',
                    'title'    => 'Division-1 Selection Matches',
                    'date'     => '18 OCT 2024',
                    'body'     => 'Internal trial matches for the upcoming All-Island tournament will be conducted this Saturday morning at 8:00 AM.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students', 'Teachers'],
                    'author'   => 'Mr. Weerasinghe',
                    'pinned'   => false
                ]
            ],
            'awards'    => [
                [
                    'id'           => 'c-aw-1',
                    'title'        => 'All-Island U17 Trophy Champions',
                    'year'         => '2024',
                    'level'        => 'National',
                    'kind'         => 'Team',
                    'tournament'   => 'National Schools Cricket Championship',
                    'date'         => 'Mar 14, 2024',
                    'venue'        => 'Asgiriya Stadium, Kandy',
                    'place'        => 'Champions',
                    'colours'      => 'Gold Trophy',
                    'ageGroup'     => 'Under 17',
                    'organisedBy'  => 'Sri Lanka Schools Cricket Association',
                    'participants' => ['Kavindu Perera', 'Dilan Jayasuriya', 'Sanjula Silva', 'Tharindu Jay'],
                    'bio'          => 'Dominant tournament performance remaining undefeated through 6 knockout rounds, concluding with a 4-wicket triumph in the grand final.',
                    'details'      => 'Chased 214 in 44.2 overs with disciplined batting partnerships and exceptional middle-overs bowling.',
                    'image'        => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&h=400&fit=crop',
                    'gallery'      => [
                        'https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=600&h=400&fit=crop'
                    ]
                ]
            ]
        ],
        [
            'id'        => 4,
            'name'      => 'Model United Nations & Debating',
            'type'      => 'Clubs and Societies',
            'category'  => 'Academic Club',
            'theme'     => 'academic-club',
            'bgIcon'    => 'usersRound',
            'desc'      => 'Developing public speaking, diplomatic debate, and international relations research.',
            'status'    => 'Active',
            'createdAt' => '05 Feb 2024',
            'image'     => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=400&fit=crop',
            'tic'       => [
                'name'    => 'Mrs. Nilmini Rajapaksa',
                'avatar'  => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&h=100&fit=crop&crop=faces',
                'email'   => 'rajapaksa.n@lecole.edu',
                'subject' => 'English & World Affairs'
            ],
            'coach'     => [
                'name'      => 'Mrs. Nilmini Rajapaksa',
                'avatar'    => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&h=100&fit=crop&crop=faces',
                'phone'     => '+94 77 889 9112',
                'specialty' => 'National Debate Adjudicator'
            ],
            'schedule'  => 'Thursdays, 3:15 – 5:00 PM',
            'location'  => 'Senior Lecture Hall',
            'ageGroups' => ['Grade 8 – 12'],
            'stats'     => ['members' => 26, 'teams' => 2, 'trophies' => 6],
            'teams'     => [
                [
                    'name'       => 'Senior Debate Delegation',
                    'ageGroup'   => 'Grade 8 – 12',
                    'coverImage' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'mun-1', 'name' => 'Amara Silva',       'grade' => 'Grade 12', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop', 'position' => 'Head Delegate'],
                        ['id' => 'mun-2', 'name' => 'Dinuka Fernando',   'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop', 'position' => 'Deputy Delegate'],
                        ['id' => 'mun-3', 'name' => 'Anuki Senaratne',   'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop', 'position' => 'Rapporteur'],
                    ]
                ],
                [
                    'name'       => 'Junior MUN Cohort',
                    'ageGroup'   => 'Grade 8 – 12',
                    'coverImage' => 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'mun-4', 'name' => 'Senali De Silva',   'grade' => 'Grade 9',  'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'position' => 'Delegate'],
                        ['id' => 'mun-5', 'name' => 'Ranul Jayasinghe',  'grade' => 'Grade 9',  'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop', 'position' => 'Delegate'],
                    ]
                ]
            ],
            'notices'   => [
                [
                    'id'       => 'dn-1',
                    'title'    => 'SLMUN Position Paper Deadline',
                    'date'     => '14 OCT 2024',
                    'body'     => 'All delegates must submit their finalized draft position papers to Mrs. Rajapaksa by 4 PM this Friday.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Mrs. Nilmini Rajapaksa',
                    'pinned'   => true
                ]
            ],
            'awards'    => [
                [
                    'id'           => 'm-aw-1',
                    'title'        => 'Best Delegation Award',
                    'year'         => '2024',
                    'level'        => 'National',
                    'kind'         => 'Team',
                    'tournament'   => 'Sri Lanka Model United Nations (SLMUN)',
                    'date'         => 'Aug 22, 2024',
                    'venue'        => 'BMICH, Colombo',
                    'place'        => 'Best Delegation',
                    'colours'      => 'Honorary Gavel',
                    'ageGroup'     => 'Open',
                    'organisedBy'  => 'SLMUN Secretariat',
                    'participants' => ['Amara Silva', 'Dinuka Fernando', 'Anuki Senaratne'],
                    'bio'          => 'The L\'École delegation captured the overall Best Delegation title, securing 4 individual Gavels and 2 Higher Commendations across general assembly committees.',
                    'details'      => 'Over 80 school delegations participated in the 3-day conference.',
                    'image'        => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=400&fit=crop',
                    'gallery'      => []
                ]
            ]
        ],
        [
            'id'        => 5,
            'name'      => 'Varsity Basketball Team',
            'type'      => 'Sports',
            'category'  => 'Team Sport',
            'theme'     => 'team-sport',
            'bgIcon'    => 'trophy',
            'desc'      => 'Fast-paced court training, shooting drills, and All-Island tournament representation.',
            'status'    => 'Active',
            'createdAt' => '20 Feb 2024',
            'image'     => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&h=400&fit=crop',
            'tic'       => [
                'name'    => 'Mr. K. Perera',
                'avatar'  => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces',
                'email'   => 'perera.k@lecole.edu',
                'subject' => 'Sports Administration'
            ],
            'coach'     => [
                'name'      => 'Coach Marcus Fernando',
                'avatar'    => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop&crop=faces',
                'phone'     => '+94 77 334 5566',
                'specialty' => 'FIBA Level 2 Coach'
            ],
            'schedule'  => 'Tuesdays & Saturdays, 6:30 – 8:00 AM',
            'location'  => 'Indoor Sports Complex',
            'ageGroups' => ['Under 17', 'Under 19'],
            'stats'     => ['members' => 22, 'teams' => 2, 'trophies' => 3],
            'teams'     => [
                [
                    'name'       => 'Varsity A Squad',
                    'ageGroup'   => 'Under 19',
                    'coverImage' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'bb-1', 'name' => 'Thejan Fernando',  'grade' => 'Grade 12', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop', 'position' => 'Point Guard / Captain'],
                        ['id' => 'bb-2', 'name' => 'Devin Wickrama',   'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop', 'position' => 'Shooting Guard'],
                    ]
                ],
                [
                    'name'       => 'Development Squad',
                    'ageGroup'   => 'Under 17',
                    'coverImage' => 'https://images.unsplash.com/photo-1519861531473-9200262188bf?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'bb-3', 'name' => 'Kusal Jayawardena', 'grade' => 'Grade 10', 'avatar' => 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=100&h=100&fit=crop', 'position' => 'Small Forward'],
                    ]
                ]
            ],
            'notices'   => [
                [
                    'id'       => 'bb-n-1',
                    'title'    => 'Morning Court Practice',
                    'date'     => '20 OCT 2024',
                    'body'     => 'Morning shooting practice resumes tomorrow at 6:30 AM sharp in the indoor court.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Coach Marcus Fernando',
                    'pinned'   => true
                ]
            ],
            'awards'    => []
        ],
        [
            'id'        => 6,
            'name'      => 'Junior Robotics & STEM League',
            'type'      => 'Clubs and Societies',
            'category'  => 'STEM Club',
            'theme'     => 'stem-club',
            'bgIcon'    => 'sparkles',
            'desc'      => 'Hands-on Arduino programming, robot building, and national STEM Olympiad training.',
            'status'    => 'Pending',
            'createdAt' => '18 Oct 2024',
            'image'     => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop',
            'tic'       => [
                'name'    => 'Dr. Aruna Senanayake',
                'avatar'  => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop&crop=faces',
                'email'   => 'senanayake.a@lecole.edu',
                'phone'   => '+94 77 667 8899',
                'subject' => 'Physics & Computer Science'
            ],
            'coach'     => [
                'name'      => 'Eng. Malith Wickramasinghe',
                'avatar'    => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop&crop=faces',
                'phone'     => '+94 77 667 8899',
                'specialty' => 'Robotics Systems Engineer'
            ],
            'schedule'  => 'Wednesdays, 3:30 – 5:00 PM',
            'location'  => 'Innovation & Robotics Lab',
            'ageGroups' => ['Grade 6 – 9'],
            'stats'     => ['members' => 15, 'teams' => 1, 'trophies' => 0],
            'teams'     => [
                [
                    'name'       => 'Robo-Warriors Alpha',
                    'ageGroup'   => 'Grade 6 – 9',
                    'coverImage' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop',
                    'roster'     => [
                        ['id' => 'rob-1', 'name' => 'Thilina Gamage', 'grade' => 'Grade 8', 'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop', 'position' => 'Hardware Lead'],
                        ['id' => 'rob-2', 'name' => 'Kavisha Perera', 'grade' => 'Grade 7', 'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'position' => 'Firmware Dev'],
                    ]
                ]
            ],
            'notices'   => [
                [
                    'id'       => 'rn-1',
                    'title'    => 'Arduino Kit Collection',
                    'date'     => '19 OCT 2024',
                    'body'     => 'New Arduino Uno R4 starter kits have arrived. Collect them at the Innovation Lab on Wednesday.',
                    'category' => 'Extracurricular',
                    'audience' => ['Students'],
                    'author'   => 'Eng. Malith Wickramasinghe',
                    'pinned'   => false
                ]
            ],
            'awards'    => []
        ]
    ];

    /**
     * Retrieve all extracurricular activities with optional type and search filtering.
     */
    public static function getAll(string $type = 'All', string $search = ''): array {
        $items = self::$clubs;

        // Filter by Type: All, Sports, Clubs and Societies
        if ($type !== 'All') {
            $items = array_filter($items, function ($c) use ($type) {
                if ($type === 'Sports') return $c['type'] === 'Sports';
                if ($type === 'Clubs and Societies' || $type === 'Clubs') return $c['type'] !== 'Sports';
                return true;
            });
        }

        // Search by name, category, TIC, or coach
        if (!empty($search)) {
            $q = strtolower(trim($search));
            $items = array_filter($items, function ($c) use ($q) {
                return str_contains(strtolower($c['name']), $q)
                    || str_contains(strtolower($c['category']), $q)
                    || str_contains(strtolower($c['tic']['name'] ?? ''), $q)
                    || str_contains(strtolower($c['coach']['name'] ?? ''), $q);
            });
        }

        // Prioritize: Active first, then Pending
        usort($items, function ($a, $b) {
            $priA = ($a['status'] === 'Active') ? 1 : (($a['status'] === 'Pending') ? 3 : 2);
            $priB = ($b['status'] === 'Active') ? 1 : (($b['status'] === 'Pending') ? 3 : 2);
            return $priA <=> $priB;
        });

        return array_values($items);
    }

    /**
     * Retrieve single club by ID.
     */
    public static function getById(int $id): ?array {
        foreach (self::$clubs as $club) {
            if ($club['id'] === $id) {
                return $club;
            }
        }
        return null;
    }

    /**
     * Retrieve awards list for a specific club by ID.
     */
    public static function getClubAwards(int $clubId): array {
        $club = self::getById($clubId);
        return $club['awards'] ?? [];
    }

    /**
     * Retrieve extracurricular activities tailored for the logged-in student (Jason Perera).
     * Includes enrolled status, enrollment date, and requested interest status.
     */
    public static function getForStudent(string $type = 'All', string $search = ''): array {
        // Enrolled club IDs for student Jason Perera (Cricket Club & Digital Arts Collective)
        $enrolledClubIds = [2, 3]; // Digital Arts Collective & Cricket Club
        // Requested club IDs (e.g. Model UN)
        $requestedClubIds = [4];

        $items = self::$clubs;

        // In student portal, pending unapproved clubs are hidden or shown as active activities
        $items = array_map(function ($club) use ($enrolledClubIds, $requestedClubIds) {
            $c = $club;
            if (in_array($c['id'], $enrolledClubIds)) {
                $c['status']        = 'Enrolled';
                $c['enrolled']      = true;
                $c['enrolledSince'] = ($c['id'] === 3) ? 'Grade 9' : 'Grade 8';
            } else {
                $c['status']    = 'Active';
                $c['enrolled']  = false;
                $c['requested'] = in_array($c['id'], $requestedClubIds);
            }
            return $c;
        }, $items);

        // Filter by Type
        if ($type !== 'All') {
            $items = array_filter($items, function ($c) use ($type) {
                if ($type === 'Sports') return $c['type'] === 'Sports';
                if ($type === 'Clubs and Societies' || $type === 'Clubs') return $c['type'] !== 'Sports';
                return true;
            });
        }

        // Filter by Search
        if (!empty($search)) {
            $q = strtolower(trim($search));
            $items = array_filter($items, function ($c) use ($q) {
                return str_contains(strtolower($c['name']), $q)
                    || str_contains(strtolower($c['category']), $q)
                    || str_contains(strtolower($c['tic']['name'] ?? ''), $q);
            });
        }

        // Sort: Enrolled first (Cricket Club id 3 prioritized), then others
        usort($items, function ($a, $b) {
            if ($a['id'] === 3 && !empty($a['enrolled'])) return -1;
            if ($b['id'] === 3 && !empty($b['enrolled'])) return 1;
            $enrA = !empty($a['enrolled']) ? 1 : 0;
            $enrB = !empty($b['enrolled']) ? 1 : 0;
            if ($enrA !== $enrB) {
                return $enrB <=> $enrA;
            }
            return $a['id'] <=> $b['id'];
        });

        return array_values($items);
    }
}

