<?php
/**
 * =========================================================================
 * L'ÉCOLE — MANAGEMENT ACTIONS MODEL
 * =========================================================================
 * Handles atomic registration, profile updates, status changes,
 * field sanitization, and security audit logging for Management Panel Staff.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/AuditModel.php';
require_once __DIR__ . '/SqlJsMapper.php';

class ManagementActions extends Model {

    /**
     * Atomically registers a new management staff member and creates their login user account.
     * Admin-only execution.
     *
     * @param array $data Form payload from #j-add-management-form
     * @param int $actorAccountId The user performing the creation (Must be Admin)
     * @param string|null $actorIdentifier The username/identifier of the actor
     * @param string $actorRole The role of the actor
     * @return array Standardized result with created staff ID and management object
     */
    public static function register(array $data, int $actorAccountId, ?string $actorIdentifier = null, string $actorRole = 'admin'): array {
        if ($actorRole !== 'admin') {
            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Staff', 'MANAGEMENT_CREATE_DENIED',
                'Attempted unauthorized management staff creation (Admin only).');
            throw new RuntimeException('Only the Administrator can create Management Panel accounts.', 403);
        }

        $db = Database::getConnection();

        // 1. Sanitize & extract inputs
        $fullName = trim($data['fullName'] ?? '');
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $nic = strtoupper(trim($data['nic'] ?? ''));
        $phone = trim($data['phone'] ?? ($data['contactNumber'] ?? ''));
        $personalEmail = strtolower(trim($data['personalEmail'] ?? ($data['email'] ?? '')));
        $title = trim($data['title'] ?? ($data['jobTitle'] ?? 'Executive Staff'));
        $officeLocation = trim($data['officeLocation'] ?? ($data['office_location'] ?? ''));
        $joinDate = trim($data['joinDate'] ?? date('Y-m-d'));
        $emergencyName = trim($data['emergencyName'] ?? '');
        $emergencyPhone = trim($data['emergencyPhone'] ?? '');

        // 2. Validate Required Fields
        if (empty($fullName)) throw new InvalidArgumentException('Full name is required.');
        if (empty($firstName)) throw new InvalidArgumentException('First name is required.');
        if (empty($lastName)) throw new InvalidArgumentException('Last name is required.');
        if (empty($nic)) throw new InvalidArgumentException('National ID (NIC) or Passport is required.');
        if (empty($phone)) throw new InvalidArgumentException('Contact phone number is required.');
        if (empty($personalEmail)) throw new InvalidArgumentException('Personal email is required.');
        if (empty($emergencyName)) throw new InvalidArgumentException('Emergency contact name is required.');
        if (empty($emergencyPhone)) throw new InvalidArgumentException('Emergency contact phone is required.');

        // 3. Format Validations
        if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Personal email address is invalid.');
        }

        $is12 = preg_match('/^[0-9]{12}$/', $nic);
        $is9v = preg_match('/^[0-9]{9}[vVxX]$/', $nic);
        $isPassport = preg_match('/^[A-Z0-9]{6,12}$/i', $nic);
        if (!$is12 && !$is9v && !$isPassport) {
            throw new InvalidArgumentException('Invalid National ID format. Must be 12 digits or 9 digits with V/X.');
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        if (strlen(preg_replace('/[^0-9]/', '', $cleanPhone)) < 9) {
            throw new InvalidArgumentException('Contact phone number must contain at least 9 digits.');
        }

        // Anti-Self-Reference Check
        if (strcasecmp($fullName, $emergencyName) === 0 || strcasecmp($firstName . ' ' . $lastName, $emergencyName) === 0) {
            throw new InvalidArgumentException('Emergency contact cannot have the same name as the staff member.');
        }
        $digitsPhone = substr(preg_replace('/[^0-9]/', '', $phone), -7);
        $digitsEmPhone = substr(preg_replace('/[^0-9]/', '', $emergencyPhone), -7);
        if (!empty($digitsPhone) && $digitsPhone === $digitsEmPhone) {
            throw new InvalidArgumentException('Emergency contact phone cannot be the same as personal contact number.');
        }

        // 4. Duplicate Collisions Check
        $stmtNic = $db->prepare("SELECT 1 FROM management_profiles WHERE nic = ? LIMIT 1");
        $stmtNic->execute([$nic]);
        if ($stmtNic->fetchColumn()) {
            throw new RuntimeException("A staff member with NIC/Passport '{$nic}' already exists.", 409);
        }

        $stmtEmail = $db->prepare("SELECT 1 FROM management_profiles WHERE personal_email = ? LIMIT 1");
        $stmtEmail->execute([$personalEmail]);
        if ($stmtEmail->fetchColumn()) {
            throw new RuntimeException("Personal email '{$personalEmail}' is already registered to another staff member.", 409);
        }

        // 5. Generate Next Sequential Staff ID (e.g. MAN-2026-0002)
        $year = date('Y');
        $stmtId = $db->prepare("SELECT staff_id FROM management_profiles WHERE staff_id LIKE ? ORDER BY id DESC LIMIT 1");
        $stmtId->execute(["MAN-{$year}-%"]);
        $lastStaffId = $stmtId->fetchColumn();
        $nextNum = 1;
        if ($lastStaffId && preg_match('/MAN-\d{4}-(\d+)/', $lastStaffId, $m)) {
            $nextNum = ((int)$m[1]) + 1;
        } else {
            $stmtCount = $db->query("SELECT COUNT(*) FROM management_profiles");
            $nextNum = ((int)$stmtCount->fetchColumn()) + 1;
        }
        $newStaffId = sprintf('MAN-%s-%04d', $year, $nextNum);

        // 6. Generate Unique Institutional Email
        $cleanFirst = preg_replace('/[^a-z0-9]/', '', strtolower($firstName));
        $cleanLast = preg_replace('/[^a-z0-9]/', '', strtolower($lastName));
        $baseInstEmail = "{$cleanFirst}_{$cleanLast}@lecole.edu";
        $instEmail = $baseInstEmail;
        $counter = 1;
        while (true) {
            $checkStmt = $db->prepare("SELECT 1 FROM user_accounts WHERE identifier = ? LIMIT 1");
            $checkStmt->execute([$instEmail]);
            if (!$checkStmt->fetchColumn()) {
                break;
            }
            $instEmail = "{$cleanFirst}_{$cleanLast}{$counter}@lecole.edu";
            $counter++;
        }

        // 7. Atomic Database Transaction
        try {
            $db->beginTransaction();

            $tempPass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $stmtUser = $db->prepare("
                INSERT INTO user_accounts (identifier, role, password_hash, activation_status, first_login_required)
                VALUES (?, 'management', ?, 'PENDING', 1)
            ");
            $stmtUser->execute([$instEmail, $tempPass]);
            $accountId = (int)$db->lastInsertId();

            $stmtMgmt = $db->prepare("
                INSERT INTO management_profiles (
                    account_id, staff_id, full_name, first_name, last_name, nic,
                    phone, personal_email, institutional_email, title, join_date,
                    office_location, emergency_name, emergency_phone
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtMgmt->execute([
                $accountId, $newStaffId, $fullName, $firstName, $lastName, $nic,
                $phone, $personalEmail, $instEmail, $title, $joinDate,
                $officeLocation, $emergencyName, $emergencyPhone
            ]);

            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Admin', 'MANAGEMENT_STAFF_REGISTERED',
                "Registered new management staff '{$fullName}' ({$newStaffId}, {$title}) with email {$instEmail}.");

            $newRow = self::fetchManagementByStaffId($db, $newStaffId);

            return [
                'success'    => true,
                'id'         => $newStaffId,
                'staffId'    => $newStaffId,
                'management' => $newRow ? SqlJsMapper::managementToJs($newRow) : null
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[ManagementActions] register error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Updates an existing management staff profile.
     */
    public static function updateProfile(string $staffId, array $data, int $actorAccountId, ?string $actorIdentifier = null): array {
        $db = Database::getConnection();
        $mgmt = self::fetchManagementByStaffId($db, $staffId);
        if (!$mgmt) {
            throw new RuntimeException("Management staff record '{$staffId}' not found.", 404);
        }

        $mgmtId = (int)$mgmt['id'];
        $accountId = (int)$mgmt['account_id'];

        $fullName       = trim($data['fullName'] ?? ($data['name'] ?? $mgmt['full_name']));
        $firstName      = trim($data['firstName'] ?? (explode(' ', $fullName)[0] ?? $mgmt['first_name']));
        $lastName       = trim($data['lastName'] ?? (implode(' ', array_slice(explode(' ', $fullName), 1)) ?: $mgmt['last_name']));
        $nic            = strtoupper(trim($data['nic'] ?? $mgmt['nic']));
        $phone          = trim($data['phone'] ?? $mgmt['phone']);
        $personalEmail  = strtolower(trim($data['personalEmail'] ?? ($data['email'] ?? $mgmt['personal_email'])));
        $title          = trim($data['title'] ?? ($data['jobTitle'] ?? $mgmt['title']));
        $officeLocation = trim($data['officeLocation'] ?? ($data['office_location'] ?? ($mgmt['office_location'] ?? '')));
        $joinDate       = trim($data['joinDate'] ?? ($data['joiningDate'] ?? $mgmt['join_date']));
        $emergencyName  = trim($data['emergencyName'] ?? $mgmt['emergency_name']);
        $emergencyPhone = trim($data['emergencyPhone'] ?? ($data['emergencyContact'] ?? $mgmt['emergency_phone']));

        if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Personal email address is invalid.');
        }

        $stmtNic = $db->prepare("SELECT 1 FROM management_profiles WHERE nic = ? AND id != ? LIMIT 1");
        $stmtNic->execute([$nic, $mgmtId]);
        if ($stmtNic->fetchColumn()) {
            throw new RuntimeException("A staff member with NIC '{$nic}' already exists.", 409);
        }

        $stmtEmail = $db->prepare("SELECT 1 FROM management_profiles WHERE personal_email = ? AND id != ? LIMIT 1");
        $stmtEmail->execute([$personalEmail, $mgmtId]);
        if ($stmtEmail->fetchColumn()) {
            throw new RuntimeException("Personal email '{$personalEmail}' is already registered to another staff member.", 409);
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                UPDATE management_profiles SET
                    full_name = ?, first_name = ?, last_name = ?, nic = ?,
                    phone = ?, personal_email = ?, title = ?, join_date = ?,
                    office_location = ?, emergency_name = ?, emergency_phone = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $fullName, $firstName, $lastName, $nic,
                $phone, $personalEmail, $title, $joinDate,
                $officeLocation, $emergencyName, $emergencyPhone,
                $mgmtId
            ]);

            if (!empty($data['status'])) {
                $accStatus = (strcasecmp($data['status'], 'Active') === 0) ? 'ACTIVE' : 'INACTIVE';
                $db->prepare("UPDATE user_accounts SET activation_status = ? WHERE id = ?")->execute([$accStatus, $accountId]);
            }

            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Staff', 'MANAGEMENT_PROFILE_UPDATED',
                "Updated profile details for management staff '{$fullName}' ({$staffId}).");

            $updatedRow = self::fetchManagementByStaffId($db, $staffId);

            return [
                'success'    => true,
                'management' => $updatedRow ? SqlJsMapper::managementToJs($updatedRow) : null
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[ManagementActions] updateProfile error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Toggles management staff activation status.
     */
    public static function updateStatus(string $staffId, string $status, int $actorAccountId, ?string $actorIdentifier = null): array {
        $db = Database::getConnection();
        $mgmt = self::fetchManagementByStaffId($db, $staffId);
        if (!$mgmt) {
            throw new RuntimeException("Management staff '{$staffId}' not found.", 404);
        }

        $isActive = (strcasecmp($status, 'Active') === 0);
        $newStatus = $isActive ? 'ACTIVE' : 'INACTIVE';
        $accountId = (int)$mgmt['account_id'];

        $db->prepare("UPDATE user_accounts SET activation_status = ? WHERE id = ?")->execute([$newStatus, $accountId]);

        $action = $isActive ? 'USER_ACTIVATED' : 'USER_DEACTIVATED';
        AuditModel::record($actorAccountId, $actorIdentifier ?? 'Staff', $action,
            "Management account {$staffId} ('{$mgmt['full_name']}') status changed to {$newStatus}.");

        return [
            'success' => true,
            'status'  => $isActive ? 'Active' : 'Deactivated'
        ];
    }

    public static function fetchManagementByStaffId(PDO $db, string $staffId): ?array {
        $stmt = $db->prepare("
            SELECT m.*, u.activation_status, u.role as user_role
            FROM management_profiles m
            JOIN user_accounts u ON u.id = m.account_id
            WHERE m.staff_id = ?
            LIMIT 1
        ");
        $stmt->execute([$staffId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
