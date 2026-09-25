<?php
/**
 * =========================================================================
 * L'ÉCOLE — NOTICE MODEL
 * =========================================================================
 * Central backend provider for notice announcements across all 5 roles.
 * Replace with PDO/database queries when live SQL is connected.
 * =========================================================================
 */

class NoticeModel {

    protected static $notices = [
        [
            'id'       => 1,
            'title'    => 'Term 2 Examination Schedule — June 2026',
            'category' => 'Academic',
            'audience' => ['All'],
            'body'     => 'Term 2 examinations run from 17–26 June 2026. Students should follow their grade and class section timetable for subject sessions, rooms, and reporting times. The make-up examination session is scheduled for 26 June for approved absences.',
            'author'   => 'Academic Office',
            'date'     => '10 JUN 2026',
            'pinned'   => true,
        ],
        [
            'id'       => 2,
            'title'    => 'Sports Day Rehearsal Schedule',
            'category' => 'Extracurricular',
            'audience' => ['Students', 'Teachers'],
            'body'     => 'Final rehearsal for the annual sports meet will take place on the main grounds this Friday at 14:00. Attendance is mandatory for all participating athletes and event coordinators.',
            'author'   => 'Student Life Office',
            'date'     => '14 JUN 2026',
            'pinned'   => false,
        ],
        [
            'id'       => 3,
            'title'    => 'Library Renovation Notice',
            'category' => 'General',
            'audience' => ['All'],
            'body'     => 'The main library will be closed for digital catalog upgrades starting next Monday. A temporary reading room and borrowing desk has been set up in Hall B for student and faculty use.',
            'author'   => 'Admin Office',
            'date'     => '20 MAY 2026',
            'pinned'   => false,
        ],
        [
            'id'       => 4,
            'title'    => 'Parent-Teacher Conference: Grade 10 & 11',
            'category' => 'Academic',
            'audience' => ['Parents', 'Teachers'],
            'body'     => 'The termly parent-teacher conference for Grade 10 & 11 will be held virtually this Saturday. One-on-one booking links have been dispatched to registered email addresses.',
            'author'   => 'Mrs. Perera',
            'date'     => '18 MAY 2026',
            'pinned'   => false,
        ],
        [
            'id'       => 5,
            'title'    => 'Annual Staff Leadership & Curriculum Review',
            'category' => 'Administrative',
            'audience' => ['Teachers', 'Management'],
            'body'     => 'Departmental curriculum reviews and teaching strategy workshops will convene in the Executive Boardroom on Friday at 16:00. All faculty heads are expected to attend with term assessments.',
            'author'   => 'Dr. Vance',
            'date'     => '12 MAY 2026',
            'pinned'   => false,
        ],
    ];

    public static function getAll(): array {
        $list = self::$notices;
        usort($list, fn($a, $b) => ($b['pinned'] ? 1 : 0) <=> ($a['pinned'] ? 1 : 0));
        return $list;
    }

    public static function getForRole(string $role): array {
        $role = strtolower($role);

        // Admin and Management see all announcements
        if ($role === 'admin' || $role === 'management') {
            return self::getAll();
        }

        $targetAudience = match($role) {
            'student' => 'Students',
            'parent'  => 'Parents',
            'teacher' => 'Teachers',
            default   => 'All'
        };

        $filtered = array_filter(self::$notices, function ($n) use ($targetAudience) {
            $aud = $n['audience'] ?? [];
            return in_array('All', $aud, true) || in_array($targetAudience, $aud, true);
        });

        usort($filtered, fn($a, $b) => ($b['pinned'] ? 1 : 0) <=> ($a['pinned'] ? 1 : 0));
        return array_values($filtered);
    }

    public static function getCategories(): array {
        return ['Academic', 'Extracurricular', 'General', 'Administrative'];
    }

    public static function getAudiences(): array {
        return ['All', 'Students', 'Parents', 'Teachers', 'Management'];
    }
}
