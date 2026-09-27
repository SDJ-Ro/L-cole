<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER ACTIONS MODEL
 * =========================================================================
 * Handles atomic registration, profile updates, status changes,
 * field sanitization, and security audit logging for Teachers.
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/MailService.php';
require_once __DIR__ . '/AuditModel.php';
require_once __DIR__ . '/SqlJsMapper.php';

class TeacherActions extends Model {

    /**
     * Atomically registers a new teacher and creates their login user account.
     *
     * @param array $data Form payload from #j-add-teacher-form
     * @param int $actorAccountId The user performing the creation (Admin/Management)
     * @param string|null $actorIdentifier The username/identifier of the actor
     * @param string $actorRole The role of the actor
     * @return array Standardized result with created staff ID and teacher object
     */
    public static function register(array $data, int $actorAccountId, ?string $actorIdentifier = null, string $actorRole = 'admin'): array {
        $db = Database::getConnection();

        // 1. Sanitize & extract inputs
        $fullName = trim($data['fullName'] ?? '');
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $nic = strtoupper(trim($data['nic'] ?? ''));
        $dateOfBirth = trim($data['dateOfBirth'] ?? ($data['dob'] ?? ''));
        $officeAddress = trim($data['officeAddress'] ?? '');
        $phone = trim($data['phone'] ?? ($data['mobileNumber'] ?? ''));
        $personalEmail = strtolower(trim($data['personalEmail'] ?? ($data['email'] ?? '')));
        $subjects = trim($data['subjects'] ?? '');
        $experience = isset($data['experience']) ? (int)$data['experience'] : 0;
        $joinDate = trim($data['joinDate'] ?? date('Y-m-d'));
        $emergencyName = trim($data['emergencyName'] ?? '');
        $emergencyPhone = trim($data['emergencyPhone'] ?? '');

        // 2. Validate Required Fields
        if (empty($fullName)) throw new InvalidArgumentException('Teacher full name is required.');
        if (empty($firstName)) throw new InvalidArgumentException('First name is required.');
        if (empty($lastName)) throw new InvalidArgumentException('Last name is required.');
        if (empty($nic)) throw new InvalidArgumentException('National ID (NIC) or Passport is required.');
        if (empty($dateOfBirth)) throw new InvalidArgumentException('Date of birth is required.');
        if (empty($phone)) throw new InvalidArgumentException('Mobile phone number is required.');
        if (empty($personalEmail)) throw new InvalidArgumentException('Personal email is required.');
        if (empty($subjects)) throw new InvalidArgumentException('Qualified subjects are required.');
        if (empty($emergencyName)) throw new InvalidArgumentException('Emergency contact name is required.');
        if (empty($emergencyPhone)) throw new InvalidArgumentException('Emergency contact phone is required.');

        // 3. Format Validations
        if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Personal email address is invalid.');
        }

        // NIC Validation: 12 digits or 9 digits + V/X
        $is12 = preg_match('/^[0-9]{12}$/', $nic);
        $is9v = preg_match('/^[0-9]{9}[vVxX]$/', $nic);
        $isPassport = preg_match('/^[A-Z0-9]{6,12}$/i', $nic);
        if (!$is12 && !$is9v && !$isPassport) {
            throw new InvalidArgumentException('Invalid National ID format. Must be 12 digits or 9 digits with V/X.');
        }

        // Phone Validation (Sri Lankan standard format)
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        if (strlen(preg_replace('/[^0-9]/', '', $cleanPhone)) < 9) {
            throw new InvalidArgumentException('Mobile number must contain at least 9 digits.');
        }

        // Emergency Contact Anti-Self-Reference Guard
        if (strcasecmp($fullName, $emergencyName) === 0 || strcasecmp($firstName . ' ' . $lastName, $emergencyName) === 0) {
            throw new InvalidArgumentException('Emergency contact cannot have the same name as the teacher.');
        }
        $digitsPhone = substr(preg_replace('/[^0-9]/', '', $phone), -7);
        $digitsEmPhone = substr(preg_replace('/[^0-9]/', '', $emergencyPhone), -7);
        if (!empty($digitsPhone) && $digitsPhone === $digitsEmPhone) {
            throw new InvalidArgumentException('Emergency contact phone cannot be the same as teacher\'s personal phone.');
        }

        // Age Verification: Must be between 21 and 65 years old
        $dobTime = strtotime($dateOfBirth);
        if (!$dobTime) throw new InvalidArgumentException('Invalid date of birth.');
        $age = (int)date('Y') - (int)date('Y', $dobTime);
        if (date('md') < date('md', $dobTime)) $age--;
        if ($age < 21 || $age > 65) {
            throw new InvalidArgumentException("Teacher age ({$age} years) must be between 21 and 65 years.");
        }

        // Experience Check
        if ($experience < 0 || $experience > ($age - 20)) {
            throw new InvalidArgumentException("Years of experience ({$experience}) is invalid for a {$age}-year-old teacher.");
        }

        // 4. Duplicate Collisions Check
        $stmtNic = $db->prepare("SELECT 1 FROM teachers WHERE nic = ? LIMIT 1");
        $stmtNic->execute([$nic]);
        if ($stmtNic->fetchColumn()) {
            throw new RuntimeException("A teacher with NIC/Passport '{$nic}' already exists.", 409);
        }

        $stmtEmail = $db->prepare("SELECT 1 FROM teachers WHERE personal_email = ? LIMIT 1");
        $stmtEmail->execute([$personalEmail]);
        if ($stmtEmail->fetchColumn()) {
            throw new RuntimeException("Personal email '{$personalEmail}' is already registered to another teacher.", 409);
        }

        // 5. Generate Next Sequential Staff ID (e.g. TEA-2026-0010)
        $year = date('Y');
        $stmtId = $db->prepare("SELECT staff_id FROM teachers WHERE staff_id LIKE ? ORDER BY id DESC LIMIT 1");
        $stmtId->execute(["TEA-{$year}-%"]);
        $lastStaffId = $stmtId->fetchColumn();
        $nextNum = 1;
        if ($lastStaffId && preg_match('/TEA-\d{4}-(\d+)/', $lastStaffId, $m)) {
            $nextNum = ((int)$m[1]) + 1;
        } else {
            $stmtCount = $db->query("SELECT COUNT(*) FROM teachers");
            $nextNum = ((int)$stmtCount->fetchColumn()) + 1;
        }
        $newStaffId = sprintf('TEA-%s-%04d', $year, $nextNum);

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

            // Insert into user_accounts (PENDING, secure random activation hash)
            $tempPass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $stmtUser = $db->prepare("
                INSERT INTO user_accounts (identifier, role, password_hash, activation_status, first_login_required)
                VALUES (?, 'teacher', ?, 'PENDING', 1)
            ");
            $stmtUser->execute([$instEmail, $tempPass]);
            $accountId = (int)$db->lastInsertId();

            // Insert into teachers
            $stmtTeacher = $db->prepare("
                INSERT INTO teachers (
                    account_id, staff_id, full_name, first_name, last_name, nic,
                    date_of_birth, office_address, phone, personal_email, institutional_email,
                    subjects, experience_years, join_date, emergency_name, emergency_phone
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtTeacher->execute([
                $accountId, $newStaffId, $fullName, $firstName, $lastName, $nic,
                $dateOfBirth, $officeAddress, $phone, $personalEmail, $instEmail,
                $subjects, $experience, $joinDate, $emergencyName, $emergencyPhone
            ]);
            $teacherDbId = (int)$db->lastInsertId();

            // Insert optional qualifications
            if (!empty($data['qualTitle']) && is_array($data['qualTitle'])) {
                $qualStmt = $db->prepare("INSERT INTO teacher_qualifications (teacher_id, degree_title, institution, year_obtained) VALUES (?, ?, ?, ?)");
                $institutions = $data['qualInstitution'] ?? [];
                $years = $data['qualYear'] ?? [];
                foreach ($data['qualTitle'] as $idx => $title) {
                    $t = trim($title);
                    $inst = trim($institutions[$idx] ?? '');
                    $yr = trim($years[$idx] ?? '');
                    if (!empty($t)) {
                        $qualStmt->execute([$teacherDbId, $t, $inst, $yr]);
                    }
                }
            }

            $db->commit();

            // Dispatch Faculty Onboarding Email to Personal Email (containing Staff Index Number and sign-up instructions)
            try {
                if (!empty($personalEmail)) {
                    MailService::sendTeacherOnboarding(
                        $personalEmail,
                        $fullName,
                        $newStaffId,
                        $instEmail,
                        $subjects ?? ''
                    );
                    AuditModel::record(
                        $actorAccountId,
                        $newStaffId,
                        'TEACHER_ONBOARDING_MAIL_DISPATCHED',
                        "Faculty onboarding email with staff index number ({$newStaffId}) dispatched to personal email: {$personalEmail}."
                    );
                }
            } catch (\Throwable $mailEx) {
                error_log('[TeacherActions] Teacher onboarding mail dispatch error: ' . $mailEx->getMessage());
            }

            // 8. Immutable Audit Trail
            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Staff', 'TEACHER_REGISTERED',
                "Registered new teacher '{$fullName}' ({$newStaffId}) with institutional email {$instEmail}.");

            // 9. Fetch newly created teacher row
            $newRow = self::fetchTeacherByStaffId($db, $newStaffId);

            return [
                'success' => true,
                'id'      => $newStaffId,
                'staffId' => $newStaffId,
                'teacher' => $newRow ? SqlJsMapper::teacherToJs($newRow) : null
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[TeacherActions] register error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Updates an existing teacher profile.
     */
    public static function updateProfile(string $staffId, array $data, int $actorAccountId, ?string $actorIdentifier = null): array {
        $db = Database::getConnection();
        $teacher = self::fetchTeacherByStaffId($db, $staffId);
        if (!$teacher) {
            throw new RuntimeException("Teacher record '{$staffId}' not found.", 404);
        }

        $teacherId = (int)$teacher['id'];
        $accountId = (int)$teacher['account_id'];

        $fullName      = trim($data['fullName'] ?? ($data['name'] ?? $teacher['full_name']));
        $firstName     = trim($data['firstName'] ?? (explode(' ', $fullName)[0] ?? $teacher['first_name']));
        $lastName      = trim($data['lastName'] ?? (implode(' ', array_slice(explode(' ', $fullName), 1)) ?: $teacher['last_name']));
        $nic           = strtoupper(trim($data['nic'] ?? $teacher['nic']));
        $phone         = trim($data['phone'] ?? $teacher['phone']);
        $personalEmail = strtolower(trim($data['personalEmail'] ?? ($data['email'] ?? $teacher['personal_email'])));
        $dateOfBirth   = trim($data['dateOfBirth'] ?? ($data['dob'] ?? $teacher['date_of_birth']));
        $officeAddress = trim($data['officeAddress'] ?? ($data['office_address'] ?? ($teacher['office_address'] ?? '')));
        $subjects      = trim($data['subjects'] ?? ($data['subject'] ?? $teacher['subjects']));
        $experience    = isset($data['experience']) ? (int)$data['experience'] : (int)$teacher['experience_years'];
        $joinDate      = trim($data['joinDate'] ?? ($data['joiningDate'] ?? $teacher['join_date']));
        $emergencyName = trim($data['emergencyName'] ?? $teacher['emergency_name']);
        $emergencyPhone = trim($data['emergencyPhone'] ?? ($data['emergencyContact'] ?? $teacher['emergency_phone']));

        // Validations
        if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Personal email address is invalid.');
        }

        // NIC Duplicate Check (excluding current teacher)
        $stmtNic = $db->prepare("SELECT 1 FROM teachers WHERE nic = ? AND id != ? LIMIT 1");
        $stmtNic->execute([$nic, $teacherId]);
        if ($stmtNic->fetchColumn()) {
            throw new RuntimeException("A teacher with NIC '{$nic}' already exists.", 409);
        }

        // Personal Email Duplicate Check
        $stmtEmail = $db->prepare("SELECT 1 FROM teachers WHERE personal_email = ? AND id != ? LIMIT 1");
        $stmtEmail->execute([$personalEmail, $teacherId]);
        if ($stmtEmail->fetchColumn()) {
            throw new RuntimeException("Personal email '{$personalEmail}' is already registered to another teacher.", 409);
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                UPDATE teachers SET
                    full_name = ?, first_name = ?, last_name = ?, nic = ?,
                    date_of_birth = ?, office_address = ?, phone = ?,
                    personal_email = ?, subjects = ?, experience_years = ?,
                    join_date = ?, emergency_name = ?, emergency_phone = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $fullName, $firstName, $lastName, $nic,
                $dateOfBirth, $officeAddress, $phone,
                $personalEmail, $subjects, $experience,
                $joinDate, $emergencyName, $emergencyPhone,
                $teacherId
            ]);

            // Handle optional account status toggle if passed
            if (!empty($data['status'])) {
                $accStatus = (strcasecmp($data['status'], 'Active') === 0) ? 'ACTIVE' : 'INACTIVE';
                $db->prepare("UPDATE user_accounts SET activation_status = ? WHERE id = ?")->execute([$accStatus, $accountId]);
            }

            // Sync qualifications if provided
            if (isset($data['qualifications']) && is_array($data['qualifications'])) {
                $db->prepare("DELETE FROM teacher_qualifications WHERE teacher_id = ?")->execute([$teacherId]);
                $qualStmt = $db->prepare("INSERT INTO teacher_qualifications (teacher_id, degree_title, institution, year_obtained) VALUES (?, ?, ?, ?)");
                foreach ($data['qualifications'] as $q) {
                    $t = trim($q['title'] ?? ($q['degree_title'] ?? ''));
                    $inst = trim($q['institution'] ?? '');
                    $yr = trim($q['year'] ?? ($q['year_obtained'] ?? ''));
                    if (!empty($t)) {
                        $qualStmt->execute([$teacherId, $t, $inst, $yr]);
                    }
                }
            }

            $db->commit();

            AuditModel::record($actorAccountId, $actorIdentifier ?? 'Staff', 'TEACHER_PROFILE_UPDATED',
                "Updated profile details for teacher '{$fullName}' ({$staffId}).");

            $updatedRow = self::fetchTeacherByStaffId($db, $staffId);

            return [
                'success' => true,
                'teacher' => $updatedRow ? SqlJsMapper::teacherToJs($updatedRow) : null
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[TeacherActions] updateProfile error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Toggles teacher activation status (ACTIVE vs INACTIVE/DEACTIVATED).
     */
    public static function updateStatus(string $staffId, string $status, int $actorAccountId, ?string $actorIdentifier = null): array {
        $db = Database::getConnection();
        $teacher = self::fetchTeacherByStaffId($db, $staffId);
        if (!$teacher) {
            throw new RuntimeException("Teacher '{$staffId}' not found.", 404);
        }

        $isActive = (strcasecmp($status, 'Active') === 0);
        $newStatus = $isActive ? 'ACTIVE' : 'INACTIVE';
        $accountId = (int)$teacher['account_id'];

        $db->prepare("UPDATE user_accounts SET activation_status = ? WHERE id = ?")->execute([$newStatus, $accountId]);

        $action = $isActive ? 'USER_ACTIVATED' : 'USER_DEACTIVATED';
        AuditModel::record($actorAccountId, $actorIdentifier ?? 'Staff', $action,
            "Teacher account {$staffId} ('{$teacher['full_name']}') status changed to {$newStatus}.");

        return [
            'success' => true,
            'status'  => $isActive ? 'Active' : 'Deactivated'
        ];
    }

    public static function fetchTeacherByStaffId(PDO $db, string $staffId): ?array {
        $stmt = $db->prepare("
            SELECT t.*, u.activation_status, u.role as user_role,
                   ct.class_id as homeroom_class_id,
                   c.section_name as homeroom_class_name
            FROM teachers t
            JOIN user_accounts u ON u.id = t.account_id
            LEFT JOIN class_teachers ct ON ct.teacher_id = t.id
            LEFT JOIN classes c ON c.id = ct.class_id
            WHERE t.staff_id = ?
            LIMIT 1
        ");
        $stmt->execute([$staffId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $tId = (int)$row['id'];
        $qStmt = $db->prepare("SELECT degree_title, institution, year_obtained FROM teacher_qualifications WHERE teacher_id = ?");
        $qStmt->execute([$tId]);
        $quals = [];
        while ($qr = $qStmt->fetch(PDO::FETCH_ASSOC)) {
            $quals[] = [
                'title'       => $qr['degree_title'],
                'institution' => $qr['institution'],
                'year'        => $qr['year_obtained']
            ];
        }
        $row['qualifications'] = $quals;

        return $row;
    }
}
