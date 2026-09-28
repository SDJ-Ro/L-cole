<?php
/**
 * =========================================================================
 * L'ÉCOLE — PARENT ACTIONS (MUTATIONS & BUSINESS RULES)
 * =========================================================================
 * Strictly handles parent/guardian state mutations:
 *   - Parent creation during admission
 *   - Parent profile updates (with concurrency version checks)
 *   - Parent deactivation with Sibling-Guard
 *   - Auto-deactivation cascade when last student is withdrawn
 *
 * Adheres strictly to the 6-Step Discipline:
 *   1. Input validation & sanitization
 *   2. Business-rule verification (active student checks, duplicate email)
 *   3. Fail-fast early exit
 *   4. Transaction boundary ($db->beginTransaction)
 *   5. Integrated Audit Logging (AuditModel::record)
 *   6. Standardized JSON response
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/ParentModel.php';
require_once __DIR__ . '/AuditModel.php';

class ParentActions {

    /**
     * 1. Validate and sanitize parent input data.
     */
    public static function validate(array $input, bool $isUpdate = false): array {
        $limits = [
            'fullName'        => 150,
            'firstName'       => 75,
            'lastName'        => 75,
            'idType'          => 20,
            'idValue'         => 50,
            'nic'             => 30,
            'passport'        => 50,
            'dateOfBirth'     => 10,
            'dobOverride'     => 10,
            'relationship'    => 50,
            'occupation'      => 100,
            'employer'        => 150,
            'countryCode'     => 10,
            'mobile'          => 30,
            'homePhone'       => 30,
            'officePhone'     => 30,
            'officeAddress'   => 255,
            'homeAddress'     => 2000,
            'email'           => 191,
            'emergencyName'   => 150,
            'emergencyContact'=> 30,
        ];

        $data = [];
        foreach ($limits as $key => $max) {
            $val = $input[$key] ?? '';
            if (!is_string($val)) {
                throw new InvalidArgumentException("Invalid format for {$key}.");
            }
            $data[$key] = trim($val);
            if (strlen($data[$key]) > $max) {
                throw new InvalidArgumentException("Value too long for {$key}.");
            }
        }

        // Mobile phone fallback for admission form
        if (empty($data['mobile']) && !empty($input['mobileNumber'])) {
            $cCode = trim($input['guardianCountryCode'] ?? '+94');
            $mNum  = ltrim(trim($input['mobileNumber']), '0');
            $data['mobile'] = $cCode . $mNum;
        } elseif (empty($data['mobile']) && !empty($input['phone'])) {
            $data['mobile'] = trim($input['phone']);
        }

        // Secondary emergency phone fallback for admission form
        if (empty($data['emergencyContact']) && !empty($input['emergencyNumber'])) {
            $cCode = trim($input['guardianEmergencyCountryCode'] ?? '+94');
            $mNum  = ltrim(trim($input['emergencyNumber']), '0');
            $data['emergencyContact'] = $cCode . $mNum;
        }

        // Mandatory fields for parent creation
        $required = ['fullName', 'firstName', 'lastName', 'relationship', 'occupation', 'mobile', 'email'];
        if (!$isUpdate) {
            $required[] = 'dateOfBirth';
        }
        foreach ($required as $field) {
            if ($data[$field] === '') {
                throw new InvalidArgumentException("Please complete the required field: {$field}.");
            }
        }

        // Name validation (letters, spaces, dots, hyphens, apostrophes - strictly no digits)
        if (!preg_match('/^[A-Za-z\s.\'-]{2,150}$/', $data['fullName'])) {
            throw new InvalidArgumentException("Full name should only contain letters, spaces, hyphens, and dots.");
        }
        if (preg_match('/[0-9]/', $data['fullName']) || preg_match('/[0-9]/', $data['firstName']) || preg_match('/[0-9]/', $data['lastName'])) {
            throw new InvalidArgumentException("Parent/guardian names cannot contain numbers.");
        }
        if (!empty($data['emergencyName']) && preg_match('/[0-9]/', $data['emergencyName'])) {
            throw new InvalidArgumentException("Secondary emergency contact name cannot contain numbers.");
        }

        // Phone format validation
        $cleanMobile = preg_replace('/[^0-9+]/', '', $data['mobile']);
        if (strlen(preg_replace('/[^0-9]/', '', $cleanMobile)) < 9) {
            throw new InvalidArgumentException("Primary mobile number must contain at least 9 digits.");
        }
        if (!empty($data['emergencyContact'])) {
            $cleanEm = preg_replace('/[^0-9+]/', '', $data['emergencyContact']);
            if (strlen(preg_replace('/[^0-9]/', '', $cleanEm)) < 9) {
                throw new InvalidArgumentException("Secondary emergency contact phone must contain at least 9 digits.");
            }
        }

        // Email validation
        $data['email'] = strtolower($data['email']);
        if (strpos($data['email'], '@') === false || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Enter a valid email address containing '@'.");
        }

        // Check duplicate email in user_accounts on new registration
        if (!$isUpdate) {
            $db = Database::getConnection();
            $checkEmail = $db->prepare("SELECT id, role FROM user_accounts WHERE identifier = ?");
            $checkEmail->execute([$data['email']]);
            $existingUser = $checkEmail->fetch();
            if ($existingUser) {
                throw new InvalidArgumentException("An account with the email '{$data['email']}' already exists. If this is a sibling, please select 'Use Existing Parent'.");
            }
        }

        // Relationship
        if (!in_array($data['relationship'], ['Father', 'Mother', 'Guardian'], true)) {
            throw new InvalidArgumentException("Select a valid relationship (Father, Mother, Guardian).");
        }

        // Dual Identity: NIC vs Passport
        $idType = strtolower($data['idType'] ?? 'nic');
        $idValue = $data['idValue'] !== '' ? $data['idValue'] : ($data['nic'] !== '' ? $data['nic'] : $data['passport']);
        
        if (!$isUpdate && empty($idValue)) {
            throw new InvalidArgumentException("Please provide a National ID (NIC) or Passport number.");
        }

        if ($idValue !== '') {
            if ($idType === 'passport' || preg_match('/^[A-Za-z]/', $idValue)) {
                // Foreign Passport
                if (!preg_match('/^[A-Za-z0-9\-]{6,20}$/', $idValue)) {
                    throw new InvalidArgumentException("Passport number must be 6-20 alphanumeric characters.");
                }
                $data['passport'] = strtoupper($idValue);
                $data['nic'] = null;
            } else {
                // Sri Lankan NIC (9 digits + letter or 12 digits)
                if (!preg_match('/^([0-9]{9}[vVxX]|[0-9]{12})$/', $idValue)) {
                    $len = strlen($idValue);
                    throw new InvalidArgumentException("Entered NIC has {$len} characters. A modern Sri Lankan NIC must have exactly 12 digits (e.g. 198012345678) or 9 digits followed by V/X (e.g. 801234567V).");
                }
                $cleanNic = strtoupper($idValue);
                $is12 = preg_match('/^[0-9]{12}$/', $cleanNic);
                $daysVal = $is12 ? (int)substr($cleanNic, 4, 3) : (int)substr($cleanNic, 2, 3);
                if ($daysVal < 1 || ($daysVal > 366 && $daysVal < 501) || $daysVal > 866) {
                    throw new InvalidArgumentException("Invalid Sri Lankan NIC number. Day code ({$daysVal}) is out of range (001–366 for male, 501–866 for female).");
                }
                $data['nic'] = $cleanNic;
                $data['passport'] = null;
            }
        }

        // Residential Address min length (if provided)
        if ($data['homeAddress'] !== '' && mb_strlen(trim($data['homeAddress'])) < 6) {
            throw new InvalidArgumentException("Guardian residential address must be at least 6 characters long.");
        }

        // Date of Birth & Age validation
        if ($data['dateOfBirth'] !== '') {
            $dob = DateTimeImmutable::createFromFormat('!Y-m-d', $data['dateOfBirth']);
            if (!$dob || $dob->format('Y-m-d') !== $data['dateOfBirth'] || $dob >= new DateTimeImmutable('today')) {
                throw new InvalidArgumentException("Enter a valid past date of birth.");
            }
            $age = (new DateTimeImmutable('today'))->diff($dob)->y;
            $override = !empty($data['dobOverride']) && $data['dobOverride'] === '1';

            if ($age < 15) {
                throw new InvalidArgumentException("Parent / guardian date of birth indicates an age under 15, which is invalid.");
            }
            if ($age < 18 && !$override) {
                throw new InvalidArgumentException("Parent age is {$age}. Please confirm the age exception checkbox to proceed.");
            }
            if ($age > 85 && !$override) {
                throw new InvalidArgumentException("Parent age is {$age}. Please confirm the age exception checkbox to proceed.");
            }
        }

        // Normalize Phone Numbers (combine country code if provided)
        $countryCode = $data['countryCode'] !== '' ? $data['countryCode'] : '+94';
        $data['mobile'] = self::normalizePhone($data['mobile'], $countryCode);

        if ($data['homePhone'] !== '') {
            $cleanHome = preg_replace('/\D/', '', $data['homePhone']);
            if (strlen($cleanHome) !== 10) {
                throw new InvalidArgumentException("Guardian home landline must be exactly 10 digits (e.g. 011 289 0123).");
            }
            $data['homePhone'] = self::normalizePhone($data['homePhone'], $countryCode);
        }
        if ($data['officePhone'] !== '') {
            $cleanOffice = preg_replace('/\D/', '', $data['officePhone']);
            if (strlen($cleanOffice) !== 10) {
                throw new InvalidArgumentException("Guardian office phone must be exactly 10 digits (e.g. 011 234 5678).");
            }
            $data['officePhone'] = self::normalizePhone($data['officePhone'], $countryCode);
        }

        // Emergency Contact validation: if name is given, contact phone is mandatory
        if ($data['emergencyName'] !== '' && $data['emergencyContact'] === '') {
            throw new InvalidArgumentException("Please provide a phone number for the alternative emergency contact.");
        }
        if ($data['emergencyContact'] !== '') {
            $data['emergencyContact'] = self::normalizePhone($data['emergencyContact'], $countryCode);
        }

        // Secondary emergency contact cannot be the parent/guardian
        if ($data['emergencyName'] !== '' && strcasecmp(trim($data['emergencyName']), trim($data['fullName'])) === 0) {
            throw new InvalidArgumentException("Secondary emergency contact cannot have the same name as the parent/guardian.");
        }
        if ($data['emergencyContact'] !== '') {
            $emDigits = substr(preg_replace('/\D/', '', $data['emergencyContact']), -7);
            $parentDigits = substr(preg_replace('/\D/', '', $data['mobile']), -7);
            if ($emDigits !== '' && $emDigits === $parentDigits) {
                throw new InvalidArgumentException("Secondary emergency contact phone cannot be the same as the parent's contact number.");
            }
        }

        return $data;
    }

    /**
     * Standardize phone number into E.164 international format.
     */
    public static function normalizePhone(string $phone, string $countryCode = '+94'): string {
        $clean = preg_replace('/[^\d+]/', '', trim($phone));
        if ($clean === '') return '';

        if (preg_match('/^0(7\d{8})$/', $clean, $matches)) {
            return '+94' . $matches[1];
        }
        if (preg_match('/^0(\d{9})$/', $clean, $matches)) {
            return '+94' . $matches[1];
        }
        if (preg_match('/^(7\d{8})$/', $clean)) {
            $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
            return $prefix . $clean;
        }
        if (str_starts_with($clean, '+') && strlen($clean) >= 9 && strlen($clean) <= 18) {
            return $clean;
        }
        if (strlen($clean) >= 7 && strlen($clean) <= 15) {
            $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
            return $prefix . ltrim($clean, '0');
        }

        throw new InvalidArgumentException("Please enter a valid phone number.");
    }

    /**
     * Inserts a parent within an admission transaction.
     */
    public static function insertForAdmission(PDO $db, array $data): int {
        if (!$db->inTransaction()) {
            throw new LogicException("Parent creation requires an active database transaction.");
        }

        // 1. Create User Account (Pending OTP onboarding activation)
        $accountStmt = $db->prepare(
            "INSERT INTO user_accounts (identifier, role, activation_status, password_hash) VALUES (?, 'parent', 'PENDING', NULL)"
        );
        $accountStmt->execute([$data['email']]);
        $accountId = (int)$db->lastInsertId();

        // 2. Generate Unique Parent Code (PAR-YYYY-XXXX)
        $parentCode = 'PAR-' . date('Y') . '-' . str_pad((string)$accountId, 4, '0', STR_PAD_LEFT);

        // 3. Insert Parent Profile
        $profileStmt = $db->prepare(
            "INSERT INTO parents (
                account_id, parent_id, relationship, full_name, first_name, last_name, nic,
                date_of_birth, passport, occupation, employer, mobile_phone, home_phone,
                office_phone, office_address, home_address, personal_email, emergency_name, emergency_contact
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $profileStmt->execute([
            $accountId,
            $parentCode,
            $data['relationship'],
            $data['fullName'],
            $data['firstName'],
            $data['lastName'],
            $data['nic'] ?? null,
            $data['dateOfBirth'],
            $data['passport'] ?? null,
            $data['occupation'],
            $data['employer'] ?? null,
            $data['mobile'],
            $data['homePhone'] ?? null,
            $data['officePhone'] ?? null,
            $data['officeAddress'] ?? null,
            $data['homeAddress'] ?? null,
            $data['email'],
            $data['emergencyName'] ?? null,
            $data['emergencyContact'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Update parent profile with concurrency protection.
     */
    public static function updateProfile(string $code, array $input, int $actorId): void {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                "SELECT p.*, u.activation_status AS account_status
                 FROM parents p
                 JOIN user_accounts u ON u.id = p.account_id
                 WHERE p.parent_id = ? AND u.role = 'parent' FOR UPDATE"
            );
            $stmt->execute([$code]);
            $parent = $stmt->fetch();
            if (!$parent) {
                throw new InvalidArgumentException("Parent profile not found.");
            }

            // Concurrency check (Denial Auditing)
            $version = $input['version'] ?? '';
            if (!is_string($version) || !hash_equals(ParentModel::version($parent), $version)) {
                AuditModel::record(
                    $actorId,
                    $code,
                    'SECURITY_PARENT_CONCURRENCY_COLLISION',
                    "Stale update attempt on parent {$code} ({$parent['full_name']}) rejected. Expected version hash mismatch."
                );
                throw new RuntimeException("This parent record was updated by another administrator. Please reload to see the latest changes.", 409);
            }

            // Validate inputs
            $data = self::validate(array_merge($input, [
                'email'        => $parent['personal_email'],
                'fullName'     => $parent['full_name'],
                'firstName'    => $parent['first_name'],
                'lastName'     => $parent['last_name'],
                'relationship' => $parent['relationship']
            ]), true);

            $update = $db->prepare(
                "UPDATE parents SET
                    occupation = ?, employer = ?, mobile_phone = ?, home_phone = ?,
                    office_phone = ?, office_address = ?, home_address = ?,
                    emergency_name = ?, emergency_contact = ?
                 WHERE id = ?"
            );
            $update->execute([
                $data['occupation'],
                $data['employer'] ?: null,
                $data['mobile'],
                $data['homePhone'] ?: null,
                $data['officePhone'] ?: null,
                $data['officeAddress'] ?: null,
                $data['homeAddress'] ?: null,
                $data['emergencyName'] ?: null,
                $data['emergencyContact'] ?: null,
                $parent['id']
            ]);

            AuditModel::record($actorId, $code, 'PARENT_PROFILE_UPDATED', "Updated contact and workplace details for parent {$code}");
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * Deactivate parent account with Sibling-Guard.
     * Blocks if any enrolled child is active.
     */
    public static function deactivate(string $code, int $actorId): void {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                "SELECT p.id, p.account_id, p.parent_id, p.full_name, u.activation_status
                 FROM parents p
                 JOIN user_accounts u ON u.id = p.account_id
                 WHERE p.parent_id = ? AND u.role = 'parent' FOR UPDATE"
            );
            $stmt->execute([$code]);
            $parent = $stmt->fetch();
            if (!$parent) {
                throw new InvalidArgumentException("Parent account not found.");
            }

            // SIBLING-GUARD: Check active enrolled students
            $checkStudents = $db->prepare(
                "SELECT s.full_name, s.index_no, u.activation_status AS student_status
                 FROM student_parents sp
                 JOIN students s ON s.id = sp.student_id
                 JOIN user_accounts u ON u.id = s.account_id
                 WHERE sp.parent_id = ? FOR UPDATE"
            );
            $checkStudents->execute([$parent['id']]);
            $students = $checkStudents->fetchAll();

            $activeStudents = [];
            foreach ($students as $stu) {
                if (strtoupper($stu['student_status']) === 'ACTIVE' || strtoupper($stu['student_status']) === 'PENDING') {
                    $activeStudents[] = "{$stu['full_name']} ({$stu['index_no']})";
                }
            }

            if (!empty($activeStudents)) {
                $studentList = implode(', ', $activeStudents);
                AuditModel::record(
                    $actorId,
                    $code,
                    'SECURITY_PARENT_DEACTIVATION_BLOCKED',
                    "Attempted deactivation of parent {$code} ({$parent['full_name']}) blocked by Sibling-Guard: active guardian for enrolled student(s): {$studentList}."
                );
                throw new RuntimeException(
                    "Cannot deactivate parent account ({$code}): This parent is the active guardian for enrolled student(s): {$studentList}. Every student must have an active guardian. Please reassign the student's guardian or withdraw the student first.",
                    409
                );
            }

            // Deactivate parent account
            $deactivateStmt = $db->prepare("UPDATE user_accounts SET activation_status = 'INACTIVE' WHERE id = ?");
            $deactivateStmt->execute([$parent['account_id']]);

            AuditModel::record(
                $actorId,
                $code,
                'PARENT_DEACTIVATED',
                "Parent {$code} ({$parent['full_name']}) deactivated; all linked students are inactive/graduated."
            );

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * Automatic check when a student is deactivated/withdrawn.
     */
    public static function checkParentAfterStudentDeactivation(int $studentId, int $actorId): array {
        $db = Database::getConnection();

        $lookupParent = $db->prepare(
            "SELECT p.id, p.parent_id, p.account_id, p.full_name, u.activation_status AS parent_status
             FROM student_parents sp
             JOIN parents p ON p.id = sp.parent_id
             JOIN user_accounts u ON u.id = p.account_id
             WHERE sp.student_id = ? AND sp.is_primary = 1"
        );
        $lookupParent->execute([$studentId]);
        $parent = $lookupParent->fetch();

        if (!$parent) {
            return ['hasParent' => false];
        }

        // Count other active children
        $countActive = $db->prepare(
            "SELECT COUNT(*) FROM student_parents sp
             JOIN students s ON s.id = sp.student_id
             JOIN user_accounts u ON u.id = s.account_id
             WHERE sp.parent_id = ? AND sp.student_id <> ? AND u.activation_status IN ('ACTIVE', 'PENDING')"
        );
        $countActive->execute([$parent['id'], $studentId]);
        $activeSiblings = (int)$countActive->fetchColumn();

        if ($activeSiblings === 0 && in_array(strtoupper($parent['parent_status']), ['ACTIVE', 'PENDING'], true)) {
            // Last child has left -> Deactivate parent
            $deactivate = $db->prepare("UPDATE user_accounts SET activation_status = 'INACTIVE' WHERE id = ?");
            $deactivate->execute([$parent['account_id']]);

            AuditModel::record(
                $actorId,
                $parent['parent_id'],
                'PARENT_AUTO_DEACTIVATED',
                "Parent account {$parent['parent_id']} automatically deactivated as all linked children have left school."
            );

            return [
                'hasParent'         => true,
                'parentCode'        => $parent['parent_id'],
                'parentName'        => $parent['full_name'],
                'parentDeactivated' => true,
                'activeSiblings'    => 0
            ];
        }

        return [
            'hasParent'         => true,
            'parentCode'        => $parent['parent_id'],
            'parentName'        => $parent['full_name'],
            'parentDeactivated' => false,
            'activeSiblings'    => $activeSiblings
        ];
    }
}
