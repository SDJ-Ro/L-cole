<?php
/**
 * =========================================================================
 * L'ÉCOLE — PARENT READ MODEL
 * =========================================================================
 * Strictly handles read queries, lookups, search, and directory listings
 * for parents. Mutation methods are cleanly delegated to ParentActions.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/SqlJsMapper.php';
require_once __DIR__ . '/ParentActions.php';

class ParentModel {

    /**
     * Lookup parent by parent_id code (e.g. PAR-2026-0001).
     */
    public static function findByCode(string $code): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.*, u.activation_status AS account_status, u.id AS user_account_id
             FROM parents p
             JOIN user_accounts u ON u.id = p.account_id
             WHERE p.parent_id = ? AND u.role = 'parent'"
        );
        $stmt->execute([trim($code)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Search parents for Sibling Linking modal.
     */
    public static function searchForSiblingLink(string $query): array {
        $db = Database::getConnection();
        $q = '%' . trim($query) . '%';

        $stmt = $db->prepare(
            "SELECT p.id, p.parent_id, p.full_name, p.relationship, p.personal_email, 
                    p.mobile_phone, p.home_address, u.activation_status AS account_status
             FROM parents p
             JOIN user_accounts u ON u.id = p.account_id
             WHERE u.role = 'parent'
               AND (p.full_name LIKE ? OR p.parent_id LIKE ? OR p.personal_email LIKE ? OR p.nic LIKE ? OR p.mobile_phone LIKE ?)
             ORDER BY p.id DESC
             LIMIT 15"
        );
        $stmt->execute([$q, $q, $q, $q, $q]);
        $parents = $stmt->fetchAll();

        // Attach linked children to each parent
        $childStmt = $db->prepare(
            "SELECT s.index_no, s.full_name, COALESCE(c.section_name, s.class_section) AS class_section, u.activation_status AS student_status
             FROM student_parents sp
             JOIN students s ON s.id = sp.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             JOIN user_accounts u ON u.id = s.account_id
             WHERE sp.parent_id = ?"
        );

        foreach ($parents as &$p) {
            $childStmt->execute([$p['id']]);
            $p['children'] = $childStmt->fetchAll();
        }

        return $parents;
    }

    /**
     * Concurrency version hash.
     */
    public static function version(array $parent): string {
        $canonical = [
            'parent_id'     => (string)($parent['parent_id'] ?? ''),
            'full_name'     => (string)($parent['full_name'] ?? ''),
            'nic'           => (string)($parent['nic'] ?? ''),
            'mobile_phone'  => (string)($parent['mobile_phone'] ?? ''),
            'personal_email'=> (string)($parent['personal_email'] ?? ''),
            'home_address'  => (string)($parent['home_address'] ?? ''),
            'updated_at'    => (string)($parent['updated_at'] ?? '')
        ];
        return hash('sha256', json_encode($canonical));
    }

    /**
     * Extract values for the edit form.
     */
    public static function editValues(array $parent): array {
        return [
            'fullName'        => $parent['full_name'],
            'firstName'       => $parent['first_name'],
            'lastName'        => $parent['last_name'],
            'nic'             => $parent['nic'] ?? '',
            'passport'        => $parent['passport'] ?? '',
            'dateOfBirth'     => $parent['date_of_birth'],
            'relationship'    => $parent['relationship'],
            'occupation'      => $parent['occupation'],
            'mobile'          => $parent['mobile_phone'],
            'employer'        => $parent['employer'] ?? '',
            'homePhone'       => $parent['home_phone'] ?? '',
            'officePhone'     => $parent['office_phone'] ?? '',
            'officeAddress'   => $parent['office_address'] ?? '',
            'homeAddress'     => $parent['home_address'] ?? '',
            'emergencyName'   => $parent['emergency_name'] ?? '',
            'emergencyContact'=> $parent['emergency_contact'] ?? ''
        ];
    }

    /**
     * Get all parents formatted for People Directory table.
     */
    public static function getAll(): array {
        $db = Database::getConnection();

        $rows = $db->query(
            "SELECT p.*, u.activation_status AS account_status
             FROM parents p
             JOIN user_accounts u ON u.id = p.account_id
             WHERE u.role = 'parent'
             ORDER BY p.id DESC"
        )->fetchAll();

        // Linked children map
        $childRows = $db->query(
            "SELECT sp.parent_id, s.index_no, s.full_name, COALESCE(c.section_name, s.class_section) AS class_section
             FROM student_parents sp
             JOIN students s ON s.id = sp.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             ORDER BY s.id"
        )->fetchAll();

        $links = [];
        $linkedStudents = [];
        foreach ($childRows as $c) {
            $pId = $c['parent_id'];
            $links[$pId][] = "{$c['full_name']} — {$c['class_section']}";
            $linkedStudents[$pId][] = [
                'id'        => $c['index_no'],
                'name'      => $c['full_name'],
                'className' => $c['class_section']
            ];
        }

        return array_map(function ($r) use ($linkedStudents) {
            $pId = $r['id'];
            return SqlJsMapper::parentToJs($r, $linkedStudents[$pId] ?? []);
        }, $rows);
    }

    // =========================================================================
    // MUTATION FORWARDERS (Delegates to ParentActions for 100% backward compat)
    // =========================================================================

    public static function validate(array $input, bool $isUpdate = false): array {
        return ParentActions::validate($input, $isUpdate);
    }

    public static function normalizePhone(string $phone, string $countryCode = '+94'): string {
        return ParentActions::normalizePhone($phone, $countryCode);
    }

    public static function insertForAdmission(PDO $db, array $data): int {
        return ParentActions::insertForAdmission($db, $data);
    }

    public static function updateProfile(string $code, array $input, int $actorId): void {
        ParentActions::updateProfile($code, $input, $actorId);
    }

    public static function deactivate(string $code, int $actorId): void {
        ParentActions::deactivate($code, $actorId);
    }

    public static function checkParentAfterStudentDeactivation(int $studentId, int $actorId): array {
        return ParentActions::checkParentAfterStudentDeactivation($studentId, $actorId);
    }
}
