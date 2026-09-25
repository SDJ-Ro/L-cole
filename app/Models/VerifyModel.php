<?php
/**
 * =========================================================================
 * L'ÉCOLE — VERIFICATION & APPROVALS MODEL
 * =========================================================================
 * Central backend provider for pending/approved/rejected verification items.
 * Ported 1:1 from Admin verify dataset.
 * =========================================================================
 */

class VerifyModel {

    protected static $items = [
        'Teachers' => [
            [
                'id'          => '1',
                'type'        => 'Teachers',
                'title'       => 'Mr. David Silva',
                'tag'         => 'New Teacher',
                'color'       => 'sky',
                'meta'        => 'Mathematics • david.s@lecole.edu',
                'description' => '',
                'author'      => 'Management Panel',
                'date'        => 'Oct 24, 2024',
                'status'      => 'Pending',
                'feedback'    => '',
            ],
            [
                'id'          => '2',
                'type'        => 'Teachers',
                'title'       => 'Ms. Sarah Fernando',
                'tag'         => 'New Teacher',
                'color'       => 'sky',
                'meta'        => 'English • sarah.f@lecole.edu',
                'description' => '',
                'author'      => 'Management Panel',
                'date'        => 'Oct 23, 2024',
                'status'      => 'Pending',
                'feedback'    => '',
            ],
        ],
        'Extracurriculars' => [
            [
                'id'          => '1',
                'type'        => 'Extracurriculars',
                'title'       => 'Photography Club',
                'tag'         => 'New Club',
                'color'       => 'sunshine',
                'meta'        => 'TIC: Mr. Perera',
                'description' => 'A club for students interested in digital and film photography. Focuses on both technical skills and creative expression.',
                'author'      => 'Management Panel',
                'date'        => 'Oct 24, 2024',
                'status'      => 'Pending',
                'feedback'    => '',
            ],
            [
                'id'          => '2',
                'type'        => 'Extracurriculars',
                'title'       => 'Under-15 Basketball',
                'tag'         => 'New Sport',
                'color'       => 'sunshine',
                'meta'        => 'TIC: Coach Silva',
                'description' => 'Junior basketball team for upcoming regional tournaments. Practices will be held three times a week.',
                'author'      => 'Management Panel',
                'date'        => 'Oct 22, 2024',
                'status'      => 'Pending',
                'feedback'    => '',
            ],
        ],
        'Notices' => [
            [
                'id'          => '1',
                'type'        => 'Notices',
                'title'       => 'Science Fair Registration',
                'tag'         => 'New Notice',
                'categoryTag' => 'Academic',
                'color'       => 'terracotta',
                'meta'        => 'Audience: Students',
                'description' => 'Registration for the annual science fair is now open. Please submit your project proposals by next Friday to your respective science teachers.',
                'author'      => 'Mr. Weerasinghe (Teacher)',
                'date'        => 'Oct 24, 2024',
                'status'      => 'Pending',
                'feedback'    => '',
            ],
            [
                'id'          => '2',
                'type'        => 'Notices',
                'title'       => 'Staff Meeting Schedule',
                'tag'         => 'New Notice',
                'categoryTag' => 'Administrative',
                'color'       => 'terracotta',
                'meta'        => 'Audience: Teachers',
                'description' => 'The monthly staff meeting will be held this Thursday at 3:30 PM in the main hall. Attendance is mandatory for all academic staff.',
                'author'      => 'Management Panel',
                'date'        => 'Oct 23, 2024',
                'status'      => 'Pending',
                'feedback'    => '',
            ],
        ],
    ];

    public static function getAll(): array {
        return self::$items;
    }

    public static function getByType(string $type): array {
        return self::$items[$type] ?? [];
    }

    public static function getPendingCounts(): array {
        $counts = [];
        foreach (self::$items as $type => $list) {
            $pending = 0;
            foreach ($list as $item) {
                if (($item['status'] ?? '') === 'Pending') {
                    $pending++;
                }
            }
            $counts[$type] = $pending;
        }
        return $counts;
    }

    public static function getStatusOptions(): array {
        return ['All', 'Pending', 'Approved', 'Rejected'];
    }
}
