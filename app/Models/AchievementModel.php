<?php
// MVC/app/Models/AchievementModel.php

class AchievementModel {

    public static function getAll(): array {
        return [
            [
                'id'            => 'a3',
                'title'         => 'Top of the Grade — Term 2',
                'category'      => 'academic',
                'categoryLabel' => 'Academic',
                'date'          => 'Aug 2025',
                'year'          => 2025,
                'description'   => 'Ranked 1st out of 142 students in Grade 10, Term 2, with a subject average of 91%.'
            ],
            [
                'id'            => 'a4',
                'title'         => 'Best Mural — Inter-School Art Fest',
                'category'      => 'clubs',
                'categoryLabel' => 'Clubs',
                'date'          => 'Jun 2025',
                'year'          => 2025,
                'description'   => 'Awarded Best Mural for a collaborative piece submitted with the Art & Design Club at the annual Inter-School Art Fest.'
            ],
            [
                'id'            => 'a11',
                'title'         => 'Zonal Runners-Up — Cricket 2025 Season',
                'category'      => 'sports',
                'categoryLabel' => 'Sports',
                'date'          => 'Apr 2025',
                'year'          => 2025,
                'description'   => 'Helped the U17 Cricket squad finish as Zonal Runners-Up in a closely contested final.'
            ],
            [
                'id'            => 'a2',
                'title'         => 'Player of the Series',
                'category'      => 'sports',
                'categoryLabel' => 'Sports',
                'date'          => 'Mar 2025',
                'year'          => 2025,
                'description'   => 'Named Player of the Series at the Inter-School U17 Cricket Tournament for consistent performances with bat and ball.'
            ],
            [
                'id'            => 'a5',
                'title'         => 'House Prefect — Teal House',
                'category'      => 'leadership',
                'categoryLabel' => 'Leadership',
                'date'          => 'Jan 2025',
                'year'          => 2025,
                'description'   => 'Appointed House Prefect for Teal House, responsible for coordinating inter-house sports and events for the year.'
            ],
            [
                'id'            => 'a1',
                'title'         => 'Regional Winner — Cricket',
                'category'      => 'sports',
                'categoryLabel' => 'Sports',
                'date'          => 'Oct 2024',
                'year'          => 2024,
                'description'   => 'Won first place representing the school at the Regional Inter-School Cricket Championship, U17 division.'
            ],
            [
                'id'            => 'a8',
                'title'         => 'Distinction — Grade 9 Year-End Exams',
                'category'      => 'academic',
                'categoryLabel' => 'Academic',
                'date'          => 'Mar 2024',
                'year'          => 2024,
                'description'   => 'Achieved distinction-level passes across all subjects in the Grade 9 year-end examinations.'
            ],
            [
                'id'            => 'a10',
                'title'         => 'Selected — Inter-School Art Exhibition',
                'category'      => 'clubs',
                'categoryLabel' => 'Clubs',
                'date'          => 'Sep 2023',
                'year'          => 2023,
                'description'   => 'Two original pieces selected for display at the Colombo Inter-School Art Exhibition.'
            ],
            [
                'id'            => 'a12',
                'title'         => 'Merit Award — Science Quiz',
                'category'      => 'academic',
                'categoryLabel' => 'Academic',
                'date'          => 'May 2023',
                'year'          => 2023,
                'description'   => 'Placed among the top three teams at the Inter-School Science Quiz, earning a merit award for the school.'
            ],
            [
                'id'            => 'a9',
                'title'         => 'Captain — Junior Cricket Team',
                'category'      => 'leadership',
                'categoryLabel' => 'Leadership',
                'date'          => 'Feb 2023',
                'year'          => 2023,
                'description'   => 'Appointed Captain of the Junior Cricket Team, leading the squad through the inter-house tournament season.'
            ],
        ];
    }

    public static function getMetrics(?array $achievements = null): array {
        $items = $achievements ?? self::getAll();
        $total = count($items);

        $earnedLastYear = 0;
        $extracurriculars = 0;
        $academic = 0;

        foreach ($items as $item) {
            if (($item['year'] ?? 0) >= 2025) {
                $earnedLastYear++;
            }
            if (in_array($item['category'], ['sports', 'clubs'])) {
                $extracurriculars++;
            }
            if ($item['category'] === 'academic') {
                $academic++;
            }
        }

        return [
            [
                'color'   => 'sand',
                'icon'    => 'icon-trophy',
                'value'   => (string)$total,
                'label'   => 'Total Achievements',
                'delay'   => 0,
                'valueId' => 'ach-metric-total',
            ],
            [
                'color'   => 'moss',
                'icon'    => 'icon-award',
                'value'   => (string)$earnedLastYear,
                'label'   => 'Earned in Last Year',
                'delay'   => 50,
                'valueId' => 'ach-metric-last-year',
            ],
            [
                'color'   => 'maroon',
                'icon'    => 'icon-extracurricular',
                'value'   => (string)$extracurriculars,
                'label'   => 'Extracurriculars',
                'delay'   => 100,
                'valueId' => 'ach-metric-extra',
            ],
            [
                'color'   => 'light-blue',
                'icon'    => 'icon-graduationCap',
                'value'   => (string)$academic,
                'label'   => 'Academic Honours',
                'delay'   => 150,
                'valueId' => 'ach-metric-academic',
            ],
        ];
    }
}

if (!class_exists('App\\Models\\AchievementModel', false)) {
    class_alias('AchievementModel', 'App\\Models\\AchievementModel');
}
