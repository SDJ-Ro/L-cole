<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT READ MODEL
 * =========================================================================
 * Strictly handles read queries, lookups, search, and directory listings
 * for students. Zero mutations or writes.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/SqlJsMapper.php';

class StudentModel {

    /**
     * Get all students formatted for People Directory table.
     */
    public static function getStudents(): array {
        $db = Database::getConnection();

        $rows = $db->query(
            "SELECT s.*, 
                    COALESCE(c.section_name, s.class_section) AS class_section,
                    COALESCE(g.name, s.grade) AS grade,
                    u.activation_status AS account_status,
                    p.parent_id, p.full_name AS parent_name, p.relationship, p.personal_email, p.mobile_phone
             FROM students s
             JOIN user_accounts u ON u.id = s.account_id
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN grades g ON g.id = c.grade_id
             LEFT JOIN student_parents sp ON sp.student_id = s.id AND sp.is_primary = 1
             LEFT JOIN parents p ON p.id = sp.parent_id
             ORDER BY s.id DESC"
        )->fetchAll();

        return array_map(fn($r) => SqlJsMapper::studentToJs($r), $rows);
    }

    /**
     * Find a student row by index_no.
     */
    public static function findByIndex(string $indexNo): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT s.*, 
                    COALESCE(c.section_name, s.class_section) AS class_section,
                    COALESCE(g.name, s.grade) AS grade,
                    u.activation_status AS account_status,
                    p.parent_id, p.full_name AS parent_name, p.relationship, p.personal_email, p.mobile_phone
             FROM students s
             JOIN user_accounts u ON u.id = s.account_id
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN grades g ON g.id = c.grade_id
             LEFT JOIN student_parents sp ON sp.student_id = s.id AND sp.is_primary = 1
             LEFT JOIN parents p ON p.id = sp.parent_id
             WHERE s.index_no = ?"
        );
        $stmt->execute([$indexNo]);
        return $stmt->fetch() ?: null;
    }
}
