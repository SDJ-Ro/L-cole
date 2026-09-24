<?php
// MVC/config/roles.php
// Central navigation configuration for all 5 L'École portals

return [
    'admin' => [
        'title'      => "L'École",
        'subtitle'   => 'Admin',
        'homeHref'   => '/admin/dashboard',
        'badgeIcon'  => 'icon-shield',
        'nav'        => [
            ['label' => 'Dashboard',       'href' => '/admin/dashboard',       'icon' => 'icon-dashboard',      'dataNavName' => 'Dashboard'],
            ['label' => 'Academic',        'href' => '/admin/academic',        'icon' => 'icon-bookOpen',       'dataNavName' => 'Academic'],
            ['label' => 'Extracurricular', 'href' => '/admin/extracurricular', 'icon' => 'icon-extracurricular', 'dataNavName' => 'Extracurricular'],
            ['label' => 'People Directory','href' => '/admin/people',          'icon' => 'icon-usersRound',     'dataNavName' => 'People Directory'],
            ['label' => 'Notice Board',    'href' => '/admin/notice',          'icon' => 'icon-mail',           'dataNavName' => 'Notice Board'],
            ['label' => 'Approvals & Verifications', 'href' => '/admin/verify', 'icon' => 'icon-shieldCheck', 'dataNavName' => 'Approvals & Verifications'],
            ['label' => 'Audit Log',       'href' => '/admin/audit',           'icon' => 'icon-lockKeyhole',    'dataNavName' => 'Audit Log'],
        ],
        'profile'    => [
            'name'   => 'Alex',
            'href'   => '/admin/profile',
            'avatar' => '/assets/images/admin.jpg'
        ]
    ],

    'teacher' => [
        'title'      => "L'École",
        'subtitle'   => 'Teacher',
        'homeHref'   => '/teacher/dashboard',
        'badgeIcon'  => 'icon-bookOpen',
        'nav'        => [
            ['label' => 'Dashboard',       'href' => '/teacher/dashboard',       'icon' => 'icon-dashboard',  'dataNavName' => 'Dashboard'],
            ['label' => 'Student Details', 'href' => '/teacher/students',        'icon' => 'icon-usersRound', 'dataNavName' => 'Student Details'],
            ['label' => 'Term Marks',      'href' => '/teacher/marks',           'icon' => 'icon-bookOpen',   'dataNavName' => 'Term Marks'],
            ['label' => 'Feedback',        'href' => '/teacher/feedback',        'icon' => 'icon-feedback',   'dataNavName' => 'Feedback'],
            ['label' => 'Extracurricular', 'href' => '/teacher/extracurricular', 'icon' => 'icon-extracurricular', 'dataNavName' => 'Extracurricular'],
            ['label' => 'Achievements',    'href' => '/teacher/achievements',    'icon' => 'icon-achievements','dataNavName' => 'Achievements'],
            ['label' => 'Notice Board',    'href' => '/teacher/notice',          'icon' => 'icon-mail',       'dataNavName' => 'Notice Board'],
        ],
        'profile'    => [
            'name'   => 'Havindu',
            'href'   => '/teacher/profile',
            'avatar' => '/assets/images/teacher.jpg'
        ]
    ],

    'management' => [
        'title'      => "L'École",
        'subtitle'   => 'Management',
        'homeHref'   => '/management/dashboard',
        'badgeIcon'  => 'icon-building2',
        'nav'        => [
            ['label' => 'Dashboard',             'href' => '/management/dashboard',             'icon' => 'icon-dashboard',  'dataNavName' => 'Dashboard'],
            ['label' => 'People Directory',      'href' => '/management/people',                'icon' => 'icon-usersRound', 'dataNavName' => 'People Directory'],
            ['label' => 'Academic',              'href' => '/management/academic',              'icon' => 'icon-bookOpen',   'dataNavName' => 'Academic'],
            ['label' => 'Extracurricular',       'href' => '/management/extracurricular',       'icon' => 'icon-extracurricular', 'dataNavName' => 'Extracurricular'],
            ['label' => 'Notice Board',          'href' => '/management/notice',                'icon' => 'icon-mail',       'dataNavName' => 'Notice Board'],
            ['label' => 'Character Certificate', 'href' => '/management/character-certificate', 'icon' => 'icon-certificate', 'dataNavName' => 'Character Certificate'],
            ['label' => 'Complaints',            'href' => '/management/complaints',            'icon' => 'icon-complaints',   'dataNavName' => 'Complaints'],
        ],
        'profile'    => [
            'name'   => 'Leadership',
            'href'   => '/management/profile',
            'avatar' => '/assets/images/management.jpg'
        ]
    ],

    'parent' => [
        'title'      => "L'École",
        'subtitle'   => 'Parent Portal',
        'homeHref'   => '/parent/dashboard',
        'badgeIcon'  => 'icon-heartHandshake',
        'nav'        => [
            ['label' => 'Dashboard',     'href' => '/parent/dashboard',     'icon' => 'icon-dashboard',     'dataNavName' => 'Dashboard'],
            ['label' => 'Child Profile', 'href' => '/parent/child-profile', 'icon' => 'icon-usersRound',    'dataNavName' => 'Child Profile'],
            ['label' => 'Approvals',     'href' => '/parent/approvals',     'icon' => 'icon-checkCircle2',  'dataNavName' => 'Approvals'],
            ['label' => 'Notice Board',  'href' => '/parent/notice-board',  'icon' => 'icon-mail',          'dataNavName' => 'Notice Board'],
            ['label' => 'Feedback',      'href' => '/parent/feedback',      'icon' => 'icon-feedback',      'dataNavName' => 'Feedback'],
            ['label' => 'Complaints',    'href' => '/parent/complaints',    'icon' => 'icon-complaints',    'dataNavName' => 'Complaints'],
        ],
        'profile'    => [
            'name'   => 'Parent',
            'href'   => '/parent/profile',
            'avatar' => '/assets/images/parents.jpg'
        ]
    ],

    'student' => [
        'title'      => "L'École",
        'subtitle'   => 'Student Portal',
        'homeHref'   => '/student/dashboard',
        'badgeIcon'  => 'icon-graduationCap',
        'nav'        => [
            ['label' => 'Dashboard',             'href' => '/student/dashboard',       'icon' => 'icon-dashboard',      'dataNavName' => 'Dashboard'],
            ['label' => 'Academic Records',      'href' => '/student/academic',        'icon' => 'icon-bookOpen',       'dataNavName' => 'Academic Records'],
            ['label' => 'Extracurricular',       'href' => '/student/extracurricular', 'icon' => 'icon-extracurricular', 'dataNavName' => 'Extracurricular'],
            ['label' => 'Achievements',          'href' => '/student/achievements',    'icon' => 'icon-achievements',   'dataNavName' => 'Achievements'],
            ['label' => 'Character Certificate', 'href' => '/student/certificates',    'icon' => 'icon-certificate',    'dataNavName' => 'Character Certificate'],
            ['label' => 'Notice Board',          'href' => '/student/notice',          'icon' => 'icon-mail',           'dataNavName' => 'Notice Board'],
        ],
        'profile'    => [
            'name'   => 'Jason',
            'href'   => '/student/profile',
            'avatar' => '/assets/images/students.jpg'
        ]
    ],
];
