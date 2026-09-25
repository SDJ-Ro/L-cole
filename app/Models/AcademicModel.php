<?php
/**
 * =========================================================================
 * L'ÉCOLE — ACADEMIC MODEL
 * =========================================================================
 * Full relational database integration for Academic Structure & Curriculum.
 * Supports complete CRUD for Grades, Classes, Teachers & Curriculum Stages.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/AuditModel.php';
require_once __DIR__ . '/AcademicActions.php';

class AcademicModel extends Model {

    /**
     * Retrieve all active grades along with their class sections from the DB.
     */
    public static function getGrades(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT g.id, g.name, g.sort_order, g.group_id, c.section_name 
                FROM grades g
                LEFT JOIN classes c ON g.id = c.grade_id
                ORDER BY g.sort_order ASC, g.id ASC, c.section_name ASC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return self::getFallbackGrades();
            }

            $gradesMap = [];
            foreach ($rows as $row) {
                $gid = $row['id'];
                if (!isset($gradesMap[$gid])) {
                    $gradesMap[$gid] = [
                        'id'            => $gid,
                        'name'          => $row['name'],
                        'group_id'      => $row['group_id'],
                        'classes'       => [],
                        'subjectScores' => self::getDefaultSubjectScores()
                    ];
                }
                if (!empty($row['section_name'])) {
                    $gradesMap[$gid]['classes'][] = $row['section_name'];
                }
            }

            return array_values($gradesMap);
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getGrades: " . $e->getMessage());
            return self::getFallbackGrades();
        }
    }

    /**
     * Retrieve all distinct subject names across curriculum groups.
     */
    public static function getSubjects(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT DISTINCT subject_name 
                FROM curriculum_group_subjects 
                ORDER BY sort_order ASC, subject_name ASC
            ");
            $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subjects)) {
                return $subjects;
            }
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getSubjects: " . $e->getMessage());
        }
        return ['Mathematics', 'English', 'Science', 'History', 'Sinhala / Tamil', 'ICT'];
    }

    /**
     * Standard academic terms.
     */
    public static function getTerms(): array {
        return ['Term 1', 'Term 2', 'Term 3'];
    }

    /**
     * Scheduled examination events.
     */
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

    /**
     * Performance data for charts.
     */
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
            $selectedGrade = $grades[0] ?? ['classes' => ['6-A', '6-B']];
        }

        $scores = $selectedGrade['subjectScores'][$subject] ?? [];
        $termOffset = ($term === 'Term 2') ? 3 : (($term === 'Term 3') ? 6 : 0);
        $classAvg = !empty($scores) ? (int)round(array_sum($scores) / count($scores)) : 78;

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

    /**
     * Retrieve all curriculum groups and their subjects.
     */
    public static function getCurriculumGroups(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT cg.id, cg.range_label, cg.description, cgs.subject_name 
                FROM curriculum_groups cg
                LEFT JOIN curriculum_group_subjects cgs ON cg.id = cgs.group_id
                ORDER BY cg.id ASC, cgs.sort_order ASC, cgs.id ASC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return self::getFallbackCurriculumGroups();
            }

            $groupsMap = [];
            foreach ($rows as $r) {
                $gid = $r['id'];
                if (!isset($groupsMap[$gid])) {
                    $groupsMap[$gid] = [
                        'id'          => $gid,
                        'range'       => $r['range_label'],
                        'description' => $r['description'] ?? '',
                        'subjects'    => []
                    ];
                }
                if (!empty($r['subject_name'])) {
                    $groupsMap[$gid]['subjects'][] = $r['subject_name'];
                }
            }

            return array_values($groupsMap);
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getCurriculumGroups: " . $e->getMessage());
            return self::getFallbackCurriculumGroups();
        }
    }

    /**
     * Retrieve class teacher assignments mapping: section_name => teacher_name.
     */
    public static function getClassTeachers(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT c.section_name, ct.teacher_name 
                FROM classes c
                LEFT JOIN class_teachers ct ON c.id = ct.class_id
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $r) {
                $result[$r['section_name']] = !empty($r['teacher_name']) ? $r['teacher_name'] : 'Assignment pending';
            }
            return $result;
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getClassTeachers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve class enrollments mapping: section_name => student_count.
     */
    public static function getClassEnrollments(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT section_name, student_count FROM classes");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $r) {
                $result[$r['section_name']] = (int)$r['student_count'];
            }
            return $result;
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getClassEnrollments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve subject teacher assignments mapping: section_name => [subject_name => teacher_name].
     */
    public static function getSubjectTeachers(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT c.section_name, cst.subject_name, cst.teacher_name 
                FROM classes c
                JOIN class_subject_teachers cst ON c.id = cst.class_id
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $r) {
                $sec = $r['section_name'];
                if (!isset($result[$sec])) {
                    $result[$sec] = [];
                }
                $result[$sec][$r['subject_name']] = $r['teacher_name'];
            }
            return $result;
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getSubjectTeachers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve full staff directory with live workload details.
     */
    public static function getStaffAssignments(): array {
        try {
            $db = Database::getConnection();

            // Load teachers from database
            $stmt = $db->query("
                SELECT id, staff_id, full_name, subjects, photo_url 
                FROM teachers 
                ORDER BY full_name ASC
            ");
            $dbTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Load all current class teacher assignments
            $stmtCt = $db->query("
                SELECT ct.teacher_name, c.section_name 
                FROM class_teachers ct
                JOIN classes c ON ct.class_id = c.id
            ");
            $ctMap = [];
            while ($row = $stmtCt->fetch(PDO::FETCH_ASSOC)) {
                $ctMap[$row['teacher_name']] = $row['section_name'];
            }

            // Load all subject assignments
            $stmtCst = $db->query("
                SELECT cst.teacher_name, cst.subject_name, c.section_name 
                FROM class_subject_teachers cst
                JOIN classes c ON cst.class_id = c.id
                ORDER BY cst.subject_name ASC, c.section_name ASC
            ");
            $cstMap = [];
            while ($row = $stmtCst->fetch(PDO::FETCH_ASSOC)) {
                $tName = $row['teacher_name'];
                $sName = $row['subject_name'];
                $cName = $row['section_name'];

                if (!isset($cstMap[$tName])) {
                    $cstMap[$tName] = [];
                }
                if (!isset($cstMap[$tName][$sName])) {
                    $cstMap[$tName][$sName] = [];
                }
                $cstMap[$tName][$sName][] = $cName;
            }

            $extrasMap = [
                'James Wilson'     => ['Science Society'],
                'Sarah Peiris'     => ['Debate Society'],
                'Rohan Dias'       => ['Chess Club'],
                'Priya De Silva'   => ['Art Circle'],
                'Anura Wijesinghe' => [],
                'Sofia Fernando'   => ['Eco Club'],
                'Shanthi Silva'    => ['Robotics & AI Lab'],
                'Madhavi Fernando' => ["L'École Philharmonic"],
                'Alex Benjamin'    => ['Astronomy Society'],
                'Mr. Weerasinghe'  => ['Varsity Football Club', 'Cricket Club']
            ];

            if (class_exists('ExtracurricularModel')) {
                try {
                    $allClubs = ExtracurricularModel::getAll();
                    foreach ($allClubs as $club) {
                        $ticName = $club['tic']['name'] ?? '';
                        $cName   = $club['name'] ?? '';
                        if ($ticName && $cName) {
                            if (!isset($extrasMap[$ticName])) {
                                $extrasMap[$ticName] = [];
                            }
                            if (!in_array($cName, $extrasMap[$ticName], true)) {
                                $extrasMap[$ticName][] = $cName;
                            }
                        }
                    }
                } catch (\Throwable $ex) {}
            }

            $staff = [];
            foreach ($dbTeachers as $t) {
                $name = $t['full_name'];
                $tId = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

                // Structure subjects for preview
                $tSubjects = [];
                if (isset($cstMap[$name])) {
                    foreach ($cstMap[$name] as $sTitle => $cList) {
                        $tSubjects[] = [
                            'subject' => $sTitle,
                            'classes' => array_values(array_unique($cList))
                        ];
                    }
                }

                $staff[] = [
                    'id'               => $tId,
                    'name'             => $name,
                    'qualification'    => $t['subjects'] ?: 'Faculty',
                    'subject'          => $t['subjects'] ?: 'General',
                    'classTeacher'     => $ctMap[$name] ?? '',
                    'extracurriculars' => $extrasMap[$name] ?? [],
                    'extras'           => $extrasMap[$name] ?? [],
                    'subjects'         => $tSubjects,
                    'classes'          => array_merge(...array_map(fn($s) => $s['classes'], $tSubjects) ?: [[]])
                ];
            }

            return $staff;
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] getStaffAssignments: " . $e->getMessage());
            return self::getFallbackStaffAssignments();
        }
    }

    // =========================================================================
    // MUTATION DELEGATIONS (FORWARDING TO AcademicActions)
    // =========================================================================
    // All actual write logic, business-rule validation, transactions, and audit
    // logging now reside in App\Models\AcademicActions. These forwarding methods
    // guarantee 100% backward compatibility for any existing callers.

    public static function addGrade(string $name, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::addGrade($name, $actorId, $actorIdentifier);
    }

    public static function deleteGrade(string $gradeId, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::deleteGrade($gradeId, $actorId, $actorIdentifier);
    }

    public static function addClass(string $gradeId, string $sectionName, int $studentCount, ?string $teacherName = null, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::addClass($gradeId, $sectionName, $studentCount, $teacherName, $actorId, $actorIdentifier);
    }

    public static function editClass(string $gradeId, string $oldSectionName, string $newSectionName, int $studentCount, ?string $teacherName = null, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::editClass($gradeId, $oldSectionName, $newSectionName, $studentCount, $teacherName, $actorId, $actorIdentifier);
    }

    public static function deleteClass(string $sectionName, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::deleteClass($sectionName, $actorId, $actorIdentifier);
    }

    public static function assignClassTeacher(string $sectionName, ?string $teacherName, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::assignClassTeacher($sectionName, $teacherName, $actorId, $actorIdentifier);
    }

    public static function assignSubjectTeacher(string $sectionName, string $subjectName, ?string $teacherName, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::assignSubjectTeacher($sectionName, $subjectName, $teacherName, $actorId, $actorIdentifier);
    }

    public static function addCurriculumGroup(string $rangeLabel, ?string $description, array $subjects, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::addCurriculumGroup($rangeLabel, $description, $subjects, $actorId, $actorIdentifier);
    }

    public static function editCurriculumGroup(string $rangeLabel, ?string $newRangeLabel, ?string $description, array $subjects, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::editCurriculumGroup($rangeLabel, $newRangeLabel, $description, $subjects, $actorId, $actorIdentifier);
    }

    public static function deleteCurriculumGroup(string $rangeLabel, ?int $actorId = null, ?string $actorIdentifier = null): array {
        return AcademicActions::deleteCurriculumGroup($rangeLabel, $actorId, $actorIdentifier);
    }


    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Find matching curriculum group ID based on a grade number (e.g. 7 -> Years 6–9).
     */
    public static function findCurriculumGroupIdForGradeNumber(int $gradeNum): ?int {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT id, range_label FROM curriculum_groups");
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($groups as $g) {
                $bounds = self::parseRangeBounds($g['range_label']);
                if ($bounds && $gradeNum >= $bounds['min'] && $gradeNum <= $bounds['max']) {
                    return (int)$g['id'];
                }
            }
        } catch (\Throwable $e) {
            error_log("[AcademicModel Error] findCurriculumGroupIdForGradeNumber: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Parses numeric bounds from range labels like "Years 6–9" or "Grade 10-11".
     */
    public static function parseRangeBounds(string $rangeLabel): ?array {
        if (preg_match_all('/\d+/', $rangeLabel, $matches)) {
            $nums = $matches[0];
            if (count($nums) >= 2) {
                return ['min' => (int)$nums[0], 'max' => (int)$nums[1]];
            } elseif (count($nums) === 1) {
                return ['min' => (int)$nums[0], 'max' => (int)$nums[0]];
            }
        }
        return null;
    }

    private static function getDefaultSubjectScores(): array {
        return [
            'Mathematics'     => [76, 82],
            'English'         => [80, 78],
            'Science'         => [75, 80],
            'History'         => [72, 76],
            'Sinhala / Tamil' => [79, 77],
            'ICT'             => [84, 80],
        ];
    }

    private static function getFallbackGrades(): array {
        return [
            ['id' => 'g6', 'name' => 'Grade 6', 'classes' => ['6-A', '6-B', '6-C', '6-D'], 'subjectScores' => self::getDefaultSubjectScores()],
            ['id' => 'g7', 'name' => 'Grade 7', 'classes' => ['7-A', '7-B', '7-C'], 'subjectScores' => self::getDefaultSubjectScores()],
            ['id' => 'g8', 'name' => 'Grade 8', 'classes' => ['8-A', '8-B', '8-C', '8-D'], 'subjectScores' => self::getDefaultSubjectScores()],
            ['id' => 'g9', 'name' => 'Grade 9', 'classes' => ['9-A', '9-B', '9-C'], 'subjectScores' => self::getDefaultSubjectScores()],
            ['id' => 'g10', 'name' => 'Grade 10', 'classes' => ['10-A', '10-B', '10-C', '10-D'], 'subjectScores' => self::getDefaultSubjectScores()],
            ['id' => 'g11', 'name' => 'Grade 11', 'classes' => ['11-A', '11-B', '11-C'], 'subjectScores' => self::getDefaultSubjectScores()],
        ];
    }

    private static function getFallbackCurriculumGroups(): array {
        return [
            [
                'id'          => 1,
                'range'       => 'Years 6–9',
                'description' => 'Lower secondary core foundation curriculum.',
                'subjects'    => ['English', 'Mathematics', 'Science', 'Humanities', 'Sinhala / Tamil', 'ICT'],
            ],
            [
                'id'          => 2,
                'range'       => 'Years 10–11',
                'description' => 'Senior secondary exam preparation and specialization.',
                'subjects'    => ['English Language', 'Mathematics', 'Science', 'History', 'Business Studies', 'ICT'],
            ],
        ];
    }

    private static function getFallbackStaffAssignments(): array {
        return [
            [
                'id' => 'james-wilson', 'name' => 'James Wilson', 'qualification' => 'Science & Chemistry',
                'subject' => 'Science', 'classTeacher' => '6-A', 'extracurriculars' => ['Science Society'], 'extras' => ['Science Society'],
                'subjects' => [['subject' => 'Science', 'classes' => ['6-A', '7-B', '8-C']]], 'classes' => ['6-A', '7-B', '8-C']
            ],
            [
                'id' => 'sarah-peiris', 'name' => 'Sarah Peiris', 'qualification' => 'English Language & Literature',
                'subject' => 'English', 'classTeacher' => '6-B', 'extracurriculars' => ['Debate Society'], 'extras' => ['Debate Society'],
                'subjects' => [['subject' => 'English', 'classes' => ['6-B', '7-A']]], 'classes' => ['6-B', '7-A']
            ],
            [
                'id' => 'rohan-dias', 'name' => 'Rohan Dias', 'qualification' => 'Pure Mathematics & Statistics',
                'subject' => 'Mathematics', 'classTeacher' => '7-B', 'extracurriculars' => ['Chess Club'], 'extras' => ['Chess Club'],
                'subjects' => [['subject' => 'Mathematics', 'classes' => ['9-A', '10-B', '11-C']]], 'classes' => ['9-A', '10-B', '11-C']
            ],
        ];
    }
}
