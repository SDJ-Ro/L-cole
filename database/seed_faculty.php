<?php
/**
 * Faculty Seed Script
 */
require_once __DIR__ . '/../core/Database.php';

$db = Database::getConnection();

$faculty = [
    [
        'email' => 'james_wilson@lecole.edu',
        'staff_id' => 'TEA-2026-0002',
        'name' => 'James Wilson',
        'first' => 'James',
        'last' => 'Wilson',
        'subjects' => 'Science, Chemistry',
        'phone' => '+94 77 111 2233'
    ],
    [
        'email' => 'sarah_peiris@lecole.edu',
        'staff_id' => 'TEA-2026-0003',
        'name' => 'Sarah Peiris',
        'first' => 'Sarah',
        'last' => 'Peiris',
        'subjects' => 'English Language, English Literature',
        'phone' => '+94 77 222 3344'
    ],
    [
        'email' => 'rohan_dias@lecole.edu',
        'staff_id' => 'TEA-2026-0004',
        'name' => 'Rohan Dias',
        'first' => 'Rohan',
        'last' => 'Dias',
        'subjects' => 'Mathematics, Statistics',
        'phone' => '+94 77 333 4455'
    ],
    [
        'email' => 'priya_desilva@lecole.edu',
        'staff_id' => 'TEA-2026-0005',
        'name' => 'Priya De Silva',
        'first' => 'Priya',
        'last' => 'De Silva',
        'subjects' => 'Visual Arts',
        'phone' => '+94 77 444 5566'
    ],
    [
        'email' => 'anura_wijesinghe@lecole.edu',
        'staff_id' => 'TEA-2026-0006',
        'name' => 'Anura Wijesinghe',
        'first' => 'Anura',
        'last' => 'Wijesinghe',
        'subjects' => 'Geography, Environmental Science',
        'phone' => '+94 77 555 6677'
    ],
    [
        'email' => 'sofia_fernando@lecole.edu',
        'staff_id' => 'TEA-2026-0007',
        'name' => 'Sofia Fernando',
        'first' => 'Sofia',
        'last' => 'Fernando',
        'subjects' => 'Biology, Life Sciences',
        'phone' => '+94 77 666 7788'
    ],
    [
        'email' => 'shanthi_silva@lecole.edu',
        'staff_id' => 'TEA-2026-0008',
        'name' => 'Shanthi Silva',
        'first' => 'Shanthi',
        'last' => 'Silva',
        'subjects' => 'Computer Science, ICT',
        'phone' => '+94 77 777 8899'
    ],
    [
        'email' => 'madhavi_fernando@lecole.edu',
        'staff_id' => 'TEA-2026-0009',
        'name' => 'Madhavi Fernando',
        'first' => 'Madhavi',
        'last' => 'Fernando',
        'subjects' => 'English Literature, Drama',
        'phone' => '+94 77 888 9900'
    ]
];

$stmtUser = $db->prepare("INSERT IGNORE INTO user_accounts (identifier, role, password_hash, activation_status, activated_at) VALUES (?, 'teacher', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW())");
$stmtTeacher = $db->prepare("INSERT INTO teachers (account_id, staff_id, full_name, first_name, last_name, nic, date_of_birth, phone, personal_email, institutional_email, subjects, experience_years, join_date, emergency_name, emergency_phone)
VALUES (?, ?, ?, ?, ?, '198512345678V', '1985-05-15', ?, ?, ?, ?, 5, '2022-01-10', 'Family Contact', '+94 70 000 0000')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), subjects = VALUES(subjects)");

foreach ($faculty as $f) {
    $stmtUser->execute([$f['email']]);
    $userId = $db->query("SELECT id FROM user_accounts WHERE identifier = " . $db->quote($f['email']))->fetchColumn();
    if ($userId) {
        $stmtTeacher->execute([
            $userId,
            $f['staff_id'],
            $f['name'],
            $f['first'],
            $f['last'],
            $f['phone'],
            str_replace('@lecole.edu', '@gmail.com', $f['email']),
            $f['email'],
            $f['subjects']
        ]);
    }
}

echo "Faculty seeded successfully!\n";
