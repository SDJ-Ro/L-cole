<?php
/**
 * Academic Seed Script
 */
require_once __DIR__ . '/../core/Database.php';

$db = Database::getConnection();

// Check if curriculum_groups already seeded
$count = $db->query("SELECT COUNT(*) FROM curriculum_groups")->fetchColumn();
if ($count > 0) {
    echo "Curriculum groups already seeded. Skipping seed.\n";
    exit(0);
}

$db->beginTransaction();

try {
    // 1. Seed Curriculum Groups
    $groups = [
        [
            'range' => 'Years 6–9',
            'desc'  => 'Lower secondary core foundation curriculum.',
            'subjects' => ['English', 'Mathematics', 'Science', 'Humanities', 'Sinhala / Tamil', 'ICT']
        ],
        [
            'range' => 'Years 10–11',
            'desc'  => 'Senior secondary exam preparation and specialization.',
            'subjects' => ['English Language', 'Mathematics', 'Science', 'History', 'Business Studies', 'ICT']
        ]
    ];

    $groupIdMap = [];
    $stmtGroup = $db->prepare("INSERT INTO curriculum_groups (range_label, description) VALUES (?, ?)");
    $stmtSubject = $db->prepare("INSERT INTO curriculum_group_subjects (group_id, subject_name, sort_order) VALUES (?, ?, ?)");

    foreach ($groups as $g) {
        $stmtGroup->execute([$g['range'], $g['desc']]);
        $gid = $db->lastInsertId();
        $groupIdMap[$g['range']] = $gid;
        foreach ($g['subjects'] as $i => $subj) {
            $stmtSubject->execute([$gid, $subj, $i]);
        }
    }

    // 2. Seed Grades
    $grades = [
        ['id' => 'g6',  'name' => 'Grade 6',  'group' => 'Years 6–9',   'order' => 6],
        ['id' => 'g7',  'name' => 'Grade 7',  'group' => 'Years 6–9',   'order' => 7],
        ['id' => 'g8',  'name' => 'Grade 8',  'group' => 'Years 6–9',   'order' => 8],
        ['id' => 'g9',  'name' => 'Grade 9',  'group' => 'Years 6–9',   'order' => 9],
        ['id' => 'g10', 'name' => 'Grade 10', 'group' => 'Years 10–11', 'order' => 10],
        ['id' => 'g11', 'name' => 'Grade 11', 'group' => 'Years 10–11', 'order' => 11],
    ];

    $stmtGrade = $db->prepare("INSERT INTO grades (id, name, group_id, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($grades as $gr) {
        $stmtGrade->execute([$gr['id'], $gr['name'], $groupIdMap[$gr['group']], $gr['order']]);
    }

    // 3. Seed Classes and Class Teachers
    $classesData = [
        'g6' => [
            ['name' => '6-A', 'count' => 30, 'teacher' => 'James Wilson'],
            ['name' => '6-B', 'count' => 29, 'teacher' => 'Sarah Peiris'],
            ['name' => '6-C', 'count' => 31, 'teacher' => 'Nethmi Perera'],
            ['name' => '6-D', 'count' => 30, 'teacher' => 'Amara Silva'],
        ],
        'g7' => [
            ['name' => '7-A', 'count' => 44, 'teacher' => 'Kavindi Jayasinghe'],
            ['name' => '7-B', 'count' => 43, 'teacher' => 'Rohan Dias'],
            ['name' => '7-C', 'count' => 43, 'teacher' => 'Madhavi Fernando'],
        ],
        'g8' => [
            ['name' => '8-A', 'count' => 35, 'teacher' => 'Anura Wijesinghe'],
            ['name' => '8-B', 'count' => 34, 'teacher' => 'David Peris'],
            ['name' => '8-C', 'count' => 36, 'teacher' => 'Nimali Wijesekara'],
            ['name' => '8-D', 'count' => 35, 'teacher' => 'Samira Cooray'],
        ],
        'g9' => [
            ['name' => '9-A', 'count' => 50, 'teacher' => 'Ruwan Silva'],
            ['name' => '9-B', 'count' => 49, 'teacher' => 'Nadeesha Pinto'],
            ['name' => '9-C', 'count' => 51, 'teacher' => 'Tharindu Jayasuriya'],
        ],
        'g10' => [
            ['name' => '10-A', 'count' => 40, 'teacher' => 'Chandani Fernando'],
            ['name' => '10-B', 'count' => 40, 'teacher' => 'Mihiran De Silva'],
            ['name' => '10-C', 'count' => 40, 'teacher' => 'Sashika Ramanayake'],
            ['name' => '10-D', 'count' => 40, 'teacher' => 'Rukshan Abeysinghe'],
        ],
        'g11' => [
            ['name' => '11-A', 'count' => 52, 'teacher' => 'Anjali Perera'],
            ['name' => '11-B', 'count' => 51, 'teacher' => 'Pradeep Ratnayake'],
            ['name' => '11-C', 'count' => 52, 'teacher' => 'Harsha Wickramasinghe'],
        ]
    ];

    $stmtClass = $db->prepare("INSERT INTO classes (grade_id, section_name, student_count) VALUES (?, ?, ?)");
    $stmtCT = $db->prepare("INSERT INTO class_teachers (class_id, teacher_name) VALUES (?, ?)");

    $classIdMap = [];
    foreach ($classesData as $gid => $clsList) {
        foreach ($clsList as $cls) {
            $stmtClass->execute([$gid, $cls['name'], $cls['count']]);
            $cid = $db->lastInsertId();
            $classIdMap[$cls['name']] = $cid;
            if (!empty($cls['teacher'])) {
                $stmtCT->execute([$cid, $cls['teacher']]);
            }
        }
    }

    // 4. Seed Subject Teachers
    $stmtCST = $db->prepare("INSERT INTO class_subject_teachers (class_id, subject_name, teacher_name) VALUES (?, ?, ?)");
    $subjectAssignments = [
        '6-A' => ['Science' => 'James Wilson', 'Mathematics' => 'Rohan Dias', 'English' => 'Sarah Peiris', 'ICT' => 'Shanthi Silva'],
        '6-B' => ['Science' => 'James Wilson', 'English' => 'Sarah Peiris', 'Mathematics' => 'Rohan Dias'],
        '7-A' => ['English' => 'Sarah Peiris', 'Computer Science' => 'Shanthi Silva'],
        '7-B' => ['Science' => 'James Wilson', 'Mathematics' => 'Rohan Dias'],
        '7-C' => ['English Literature' => 'Madhavi Fernando'],
        '8-A' => ['Geography' => 'Anura Wijesinghe'],
        '8-B' => ['Computer Science' => 'Shanthi Silva'],
        '8-C' => ['Science' => 'James Wilson'],
        '9-A' => ['Mathematics' => 'Rohan Dias', 'Drama' => 'Madhavi Fernando'],
        '9-B' => ['Geography' => 'Anura Wijesinghe'],
        '10-B' => ['Mathematics' => 'Rohan Dias'],
        '11-A' => ['English Literature' => 'Madhavi Fernando'],
        '11-C' => ['Mathematics' => 'Rohan Dias'],
    ];

    foreach ($subjectAssignments as $cName => $subjs) {
        if (!isset($classIdMap[$cName])) continue;
        $cid = $classIdMap[$cName];
        foreach ($subjs as $sName => $tName) {
            $stmtCST->execute([$cid, $sName, $tName]);
        }
    }

    $db->commit();
    echo "Academic data successfully seeded!\n";
} catch (\Throwable $e) {
    $db->rollBack();
    echo "Seed failed: " . $e->getMessage() . "\n";
}
