<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT ACTIONS (MUTATIONS & BUSINESS RULES)
 * =========================================================================
 * Strictly handles student state mutations:
 *   - Student Admission / Registration (with guardian co-creation/linking)
 *   - Student Profile Updates
 *   - Student Status Toggling (Active / Deactivated with sibling cascade)
 *   - Guardian Reassignment
 *
 * Adheres strictly to the 6-Step Discipline:
 *   1. Input validation & sanitization
 *   2. Business-rule verification (capacity caps, parent status)
 *   3. Fail-fast early exit
 *   4. Transaction boundary ($db->beginTransaction)
 *   5. Integrated Audit Logging (AuditModel::record)
 *   6. Standardized JSON response
 * =========================================================================
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/MailService.php';
require_once __DIR__ . '/StudentModel.php';
require_once __DIR__ . '/ParentModel.php';
require_once __DIR__ . '/AcademicModel.php';
require_once __DIR__ . '/AuditModel.php';
require_once __DIR__ . '/SqlJsMapper.php';

class StudentActions {

    /**
     * 1. Validate student admission form input.
     */
    public static function validate(array $input): array {
        $limits = [
            'fullName'               => 150,
            'firstName'              => 75,
            'lastName'               => 75,
            'dateOfBirth'            => 10,
            'dobOverride'            => 10,
            'gender'                 => 20,
            'nationalId'             => 30,
            'birthCertificateNumber' => 50,
            'grade'                  => 30,
            'classSection'           => 20,
            'religion'               => 50,
            'homeAddress'            => 2000,
            'admissionDate'          => 10,
            'nationality'            => 50,
            'educationalZone'        => 50,
            'district'               => 50,
            'province'               => 50,
            'previousSchool'         => 150,
            'bloodGroup'             => 15,
            'medicalNotes'           => 2000
        ];

        $student = [];
        foreach ($limits as $field => $limit) {
            $val = $input[$field] ?? '';
            if (!is_string($val)) {
                throw new InvalidArgumentException("Invalid value for {$field}.");
            }
            $student[$field] = trim($val);
            if (strlen($student[$field]) > $limit) {
                throw new InvalidArgumentException("Value too long for {$field}.");
            }
        }

        if (empty($student['nationality'])) {
            $student['nationality'] = 'Sri Lankan';
        }

        // Required fields
        $required = [
            'fullName', 'firstName', 'lastName', 'dateOfBirth', 'gender',
            'birthCertificateNumber', 'grade', 'classSection', 'homeAddress',
            'admissionDate', 'nationality'
        ];
        foreach ($required as $field) {
            if ($student[$field] === '') {
                throw new InvalidArgumentException("Please complete the required field: {$field}.");
            }
        }

        // Validate name format
        if (!preg_match('/^[A-Za-z\s.\'-]{2,150}$/', $student['fullName'])) {
            throw new InvalidArgumentException("Student full name should only contain letters, spaces, hyphens, and dots.");
        }
        if (preg_match('/[0-9]/', $student['fullName']) || preg_match('/[0-9]/', $student['firstName']) || preg_match('/[0-9]/', $student['lastName'])) {
            throw new InvalidArgumentException("Student names cannot contain numbers.");
        }

        // Dates
        $dob = DateTimeImmutable::createFromFormat('!Y-m-d', $student['dateOfBirth']);
        if (!$dob || $dob->format('Y-m-d') !== $student['dateOfBirth']) {
            throw new InvalidArgumentException("Enter a valid student date of birth.");
        }

        $admission = DateTimeImmutable::createFromFormat('!Y-m-d', $student['admissionDate']);
        if (!$admission || $admission->format('Y-m-d') !== $student['admissionDate']) {
            throw new InvalidArgumentException("Enter a valid admission date.");
        }

        if ($dob >= new DateTimeImmutable('today') || $dob >= $admission) {
            throw new InvalidArgumentException("Student date of birth must be in the past and before the admission date.");
        }

        // Age limits & Override check
        $age = (new DateTimeImmutable('today'))->diff($dob)->y;
        $override = !empty($student['dobOverride']) && $student['dobOverride'] === '1';

        if ($age < 3) {
            throw new InvalidArgumentException("Student age is {$age}, which is below school entry age.");
        }
        if ($age > 19 && !$override) {
            throw new InvalidArgumentException("Student age is {$age}. Please check the age override checkbox to confirm this exception.");
        }

        if (!in_array($student['gender'], ['Male', 'Female', 'Prefer not to say'], true)) {
            throw new InvalidArgumentException("Select a valid gender.");
        }

        // Duplicate Birth Certificate check
        $db = Database::getConnection();
        $bcStmt = $db->prepare("SELECT id FROM students WHERE birth_certificate_number = ? LIMIT 1");
        $bcStmt->execute([$student['birthCertificateNumber']]);
        if ($bcStmt->fetch()) {
            throw new InvalidArgumentException("A student with Birth Certificate number '{$student['birthCertificateNumber']}' is already registered.");
        }

        return $student;
    }

    /**
     * 2. Atomically register a student and link/create their guardian.
     */
    public static function register(array $input, int $actorId): array {
        $student = self::validate($input);

        $mode = $input['guardianMode'] ?? 'existing';
        $requestKey = trim($input['admissionKey'] ?? '');
        if ($requestKey === '' || !preg_match('/^[a-f0-9]{32}$/', $requestKey)) {
            $requestKey = md5(uniqid('adm_', true));
        }

        if (!in_array($mode, ['existing', 'new'], true)) {
            throw new InvalidArgumentException("Please choose either an existing parent or create a new guardian.");
        }

        $parent = null;
        if ($mode === 'new') {
            if (!isset($input['guardian']) || !is_array($input['guardian'])) {
                throw new InvalidArgumentException("Please provide the guardian information.");
            }
            if (empty(trim($input['guardian']['emergencyName'] ?? ''))) {
                throw new InvalidArgumentException("Secondary emergency contact name is compulsory.");
            }
            if (empty(trim($input['guardian']['emergencyContact'] ?? '')) && !empty(trim($input['guardian']['emergencyNumber'] ?? ''))) {
                $cCode = trim($input['guardianEmergencyCountryCode'] ?? '+94');
                $mNum  = ltrim(trim($input['guardian']['emergencyNumber']), '0');
                $input['guardian']['emergencyContact'] = $cCode . $mNum;
            }
            if (empty(trim($input['guardian']['emergencyContact'] ?? ''))) {
                throw new InvalidArgumentException("Secondary emergency contact phone number is compulsory.");
            }
            if (empty(trim($input['guardian']['mobile'] ?? '')) && !empty(trim($input['guardian']['mobileNumber'] ?? ''))) {
                $cCode = trim($input['guardianCountryCode'] ?? '+94');
                $mNum  = ltrim(trim($input['guardian']['mobileNumber']), '0');
                $input['guardian']['mobile'] = $cCode . $mNum;
            }
            $parent = ParentModel::validate($input['guardian']);
        }

        $parentCode = trim($input['existingParent'] ?? '');

        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // Concurrency Lock: Idempotency check
            $lockStmt = $db->prepare("SELECT id FROM admission_idempotency_keys WHERE idempotency_key = ? FOR UPDATE");
            $lockStmt->execute([$requestKey]);
            if ($lockStmt->fetch()) {
                throw new RuntimeException("This admission application was already processed.", 409);
            }

            // Scope Shape: Verify classSection belongs to the selected grade and find class_id
            $classGradeCheck = $db->prepare(
                "SELECT c.id as class_id, g.name as grade_name, g.id as grade_id 
                 FROM classes c 
                 JOIN grades g ON c.grade_id = g.id 
                 WHERE c.section_name = ? AND (g.name = ? OR g.id = ?) LIMIT 1"
            );
            $classGradeCheck->execute([$student['classSection'], $student['grade'], $student['grade']]);
            $gradeRow = $classGradeCheck->fetch(PDO::FETCH_ASSOC);
            if (!$gradeRow) {
                $secCheck = $db->prepare("SELECT g.name as grade_name FROM classes c JOIN grades g ON c.grade_id = g.id WHERE c.section_name = ? LIMIT 1");
                $secCheck->execute([$student['classSection']]);
                $otherGrade = $secCheck->fetch(PDO::FETCH_ASSOC);
                if ($otherGrade) {
                    throw new InvalidArgumentException("Class section '{$student['classSection']}' does not belong to {$student['grade']}.");
                }
            }
            $targetClassId = $gradeRow ? (int)$gradeRow['class_id'] : null;

            // Verify Section Enrollment Cap (Max: 40) via class_id join (Denial Auditing)
            $capStmt = $db->prepare(
                $targetClassId 
                    ? "SELECT COUNT(s.id) as current_count, c.student_count as max_capacity 
                       FROM classes c 
                       LEFT JOIN students s ON s.class_id = c.id 
                       WHERE c.id = ? 
                       GROUP BY c.id, c.student_count FOR UPDATE"
                    : "SELECT COUNT(s.id) as current_count, c.student_count as max_capacity 
                       FROM classes c 
                       LEFT JOIN students s ON s.class_id = c.id 
                       WHERE c.section_name = ? 
                       GROUP BY c.id, c.student_count FOR UPDATE"
            );
            $capStmt->execute([$targetClassId ?: $student['classSection']]);
            $capRow = $capStmt->fetch();
            if ($capRow && $capRow['current_count'] >= 40) {
                AuditModel::record(
                    $actorId,
                    $student['classSection'],
                    'SECURITY_CLASS_CAPACITY_BLOCKED',
                    "Admission for {$student['fullName']} blocked: Class {$student['classSection']} reached maximum hard capacity (40 students)."
                );
                throw new InvalidArgumentException("Class section '{$student['classSection']}' is at maximum hard capacity (40 students). Please select another section.");
            }

            // Resolve or Create Parent
            $parentId = null;
            $parentEmail = '';
            $parentFullName = '';
            if ($mode === 'new') {
                $parentId = ParentModel::insertForAdmission($db, $parent);
                $parentEmail = $parent['email'];
                $parentFullName = $parent['fullName'];
            } else {
                if ($parentCode === '') {
                    throw new InvalidArgumentException("Please select an existing parent.");
                }
                $pStmt = $db->prepare(
                    "SELECT p.id, p.full_name, p.personal_email, u.activation_status 
                     FROM parents p 
                     JOIN user_accounts u ON u.id = p.account_id 
                     WHERE p.parent_id = ? FOR UPDATE"
                );
                $pStmt->execute([$parentCode]);
                $existing = $pStmt->fetch();
                if (!$existing || strtoupper($existing['activation_status']) === 'INACTIVE') {
                    throw new InvalidArgumentException("Selected parent was not found or is currently inactive.");
                }
                $parentId = (int)$existing['id'];
                $parentEmail = $existing['personal_email'];
                $parentFullName = $existing['full_name'];
            }

            // Create Student User Account
            $accountStmt = $db->prepare(
                "INSERT INTO user_accounts (identifier, role, activation_status, password_hash) VALUES (?, 'student', 'PENDING', NULL)"
            );
            $tempIdent = 'STU-TEMP-' . bin2hex(random_bytes(4));
            $accountStmt->execute([$tempIdent]);
            $accountId = (int)$db->lastInsertId();

            // Unique Student Index
            $indexNo = 'STU-' . date('Y') . '-' . str_pad((string)$accountId, 4, '0', STR_PAD_LEFT);
            $fixIdent = $db->prepare("UPDATE user_accounts SET identifier = ? WHERE id = ?");
            $fixIdent->execute([$indexNo, $accountId]);

            // Insert Student
            $stuStmt = $db->prepare(
                "INSERT INTO students (
                    account_id, index_no, full_name, first_name, last_name, date_of_birth,
                    gender, national_id, birth_certificate_number, class_id, grade, class_section,
                    religion, home_address, admission_date, nationality, educational_zone,
                    district, province, previous_school, blood_group, medical_notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stuStmt->execute([
                $accountId,
                $indexNo,
                $student['fullName'],
                $student['firstName'],
                $student['lastName'],
                $student['dateOfBirth'],
                $student['gender'],
                $student['nationalId'] ?: null,
                $student['birthCertificateNumber'],
                $targetClassId,
                $student['grade'],
                $student['classSection'],
                $student['religion'] ?: null,
                $student['homeAddress'],
                $student['admissionDate'],
                $student['nationality'],
                $student['educationalZone'] ?: 'Colombo',
                $student['district'] ?: 'Colombo',
                $student['province'] ?: 'Western',
                $student['previousSchool'] ?: null,
                $student['bloodGroup'] ?: 'Not provided',
                $student['medicalNotes'] ?: null
            ]);
            $studentId = (int)$db->lastInsertId();

            // Link Student to Parent
            $linkStmt = $db->prepare("INSERT INTO student_parents (student_id, parent_id, is_primary) VALUES (?, ?, 1)");
            $linkStmt->execute([$studentId, $parentId]);

            // Save Idempotency Key
            $idempStmt = $db->prepare("INSERT INTO admission_idempotency_keys (idempotency_key, student_id) VALUES (?, ?)");
            $idempStmt->execute([$requestKey, $studentId]);

            // Audit
            AuditModel::record(
                $actorId,
                $indexNo,
                'STUDENT_ADMITTED',
                "Student {$student['fullName']} ({$indexNo}) admitted to Grade {$student['grade']} ({$student['classSection']})."
            );
            AuditModel::record(
                $actorId,
                $indexNo,
                'PARENT_LINKED_TO_STUDENT',
                "Parent #{$parentId} linked as guardian to Student {$student['fullName']} ({$indexNo})."
            );

            // Generate Activation OTP for Student Account
            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));
            $setOtp = $db->prepare("
                INSERT INTO password_reset_otps (account_id, otp_hash, attempts_left, expires_at)
                VALUES (?, ?, 3, ?)
            ");
            $setOtp->execute([$accountId, password_hash($otp, PASSWORD_BCRYPT), $expiresAt]);

            $db->commit();

            // Dispatch Admission Notification Email to Parent (containing Student Index Number and instructions)
            try {
                if (!empty($parentEmail)) {
                    if ($mode === 'new') {
                        MailService::sendNewAdmissionWithGuardian(
                            $parentEmail,
                            $parentFullName,
                            [
                                'index'        => $indexNo,
                                'name'         => $student['fullName'],
                                'grade'        => $student['grade'],
                                'classSection' => $student['classSection']
                            ]
                        );
                    } else {
                        MailService::sendSiblingAdmissionToExistingGuardian(
                            $parentEmail,
                            $parentFullName,
                            [
                                'index'        => $indexNo,
                                'name'         => $student['fullName'],
                                'grade'        => $student['grade'],
                                'classSection' => $student['classSection']
                            ]
                        );
                    }
                    AuditModel::record(
                        $actorId,
                        $indexNo,
                        'ADMISSION_MAIL_DISPATCHED',
                        "Admission email with student index number ({$indexNo}) dispatched to parent email: {$parentEmail}."
                    );
                }
            } catch (\Throwable $mailEx) {
                error_log('[StudentActions] Admission mail dispatch error: ' . $mailEx->getMessage());
            }

            return [
                'success'      => true,
                'studentId'    => $studentId,
                'index'        => $indexNo,
                'indexNo'      => $indexNo,
                'studentName'  => $student['fullName'],
                'grade'        => $student['grade'],
                'classSection' => $student['classSection'],
                'parentEmail'  => $parentEmail,
                'parentName'   => $parentFullName,
                'otpCode'      => $otp
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 3. Change a student's legal guardian.
     */
    public static function changeGuardian(string $studentIndex, string $parentCode, int $actorId): array {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $sStmt = $db->prepare("SELECT id, index_no, full_name FROM students WHERE index_no = ? FOR UPDATE");
            $sStmt->execute([$studentIndex]);
            $student = $sStmt->fetch();
            if (!$student) {
                throw new InvalidArgumentException("Student with index {$studentIndex} not found.");
            }

            $pStmt = $db->prepare(
                "SELECT p.id, p.parent_id, p.full_name, u.activation_status AS parent_status
                 FROM parents p
                 JOIN user_accounts u ON u.id = p.account_id
                 WHERE p.parent_id = ? FOR UPDATE"
            );
            $pStmt->execute([$parentCode]);
            $newParent = $pStmt->fetch();
            if (!$newParent || strtoupper($newParent['parent_status']) === 'INACTIVE') {
                throw new InvalidArgumentException("Please select an active parent account.");
            }

            // Find current guardian
            $currStmt = $db->prepare(
                "SELECT p.id, p.parent_id, p.full_name
                 FROM student_parents sp
                 JOIN parents p ON p.id = sp.parent_id
                 WHERE sp.student_id = ? AND sp.is_primary = 1 FOR UPDATE"
            );
            $currStmt->execute([$student['id']]);
            $currentGuardian = $currStmt->fetch();

            if ($currentGuardian && (int)$currentGuardian['id'] === (int)$newParent['id']) {
                throw new InvalidArgumentException("This parent is already the registered guardian for {$student['full_name']}.");
            }

            // Update guardian link
            $delOld = $db->prepare("DELETE FROM student_parents WHERE student_id = ?");
            $delOld->execute([$student['id']]);

            $insNew = $db->prepare("INSERT INTO student_parents (student_id, parent_id, is_primary) VALUES (?, ?, 1)");
            $insNew->execute([$student['id'], $newParent['id']]);

            // Audit
            $oldDesc = $currentGuardian ? "{$currentGuardian['full_name']} ({$currentGuardian['parent_id']})" : "None";
            AuditModel::record(
                $actorId,
                $student['index_no'],
                'GUARDIAN_CHANGED',
                "Guardian for {$student['full_name']} ({$student['index_no']}) changed from {$oldDesc} to {$newParent['full_name']} ({$newParent['parent_id']})."
            );

            // Check if old parent has any remaining active children
            $oldParentRemaining = 0;
            if ($currentGuardian) {
                $remStmt = $db->prepare(
                    "SELECT COUNT(*) FROM student_parents sp
                     JOIN students s ON s.id = sp.student_id
                     JOIN user_accounts u ON u.id = s.account_id
                     WHERE sp.parent_id = ? AND u.activation_status = 'ACTIVE'"
                );
                $remStmt->execute([$currentGuardian['id']]);
                $oldParentRemaining = (int)$remStmt->fetchColumn();
            }

            $db->commit();
            return [
                'success'            => true,
                'studentIndex'       => $student['index_no'],
                'studentName'        => $student['full_name'],
                'newParentId'        => $newParent['parent_id'],
                'newParentName'      => $newParent['full_name'],
                'oldParentRemaining' => $oldParentRemaining,
                'oldParentCode'      => $currentGuardian['parent_id'] ?? null
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 4. Update an existing student's profile details.
     */
    public static function updateProfile(string $indexNo, array $data, int $actorId): array {
        $db = Database::getConnection();
        $student = StudentModel::findByIndex($indexNo);
        if (!$student) {
            throw new InvalidArgumentException("Student with index {$indexNo} not found.");
        }

        $fullName = trim($data['fullName'] ?? $data['name'] ?? $student['full_name']);
        if (empty($fullName)) {
            throw new InvalidArgumentException("Full name is required.");
        }

        $names = explode(' ', $fullName);
        $firstName = $names[0];
        $lastName = implode(' ', array_slice($names, 1)) ?: $firstName;

        $targetGrade = !empty($data['grade']) ? $data['grade'] : $student['grade'];
        $targetSection = !empty($data['classSection']) ? $data['classSection'] : $student['class_section'];

        // Scope Shape: Verify target section belongs to target grade and resolve class_id
        $targetClassId = $student['class_id'] ?? null;
        if ($targetGrade && $targetSection) {
            $classGradeCheck = $db->prepare(
                "SELECT c.id as class_id, g.name as grade_name, g.id as grade_id 
                 FROM classes c 
                 JOIN grades g ON c.grade_id = g.id 
                 WHERE c.section_name = ? AND (g.name = ? OR g.id = ?) LIMIT 1"
            );
            $classGradeCheck->execute([$targetSection, $targetGrade, $targetGrade]);
            $gradeRow = $classGradeCheck->fetch(PDO::FETCH_ASSOC);
            if ($gradeRow) {
                $targetClassId = (int)$gradeRow['class_id'];
            } else {
                $secCheck = $db->prepare("SELECT g.name as grade_name FROM classes c JOIN grades g ON c.grade_id = g.id WHERE c.section_name = ? LIMIT 1");
                $secCheck->execute([$targetSection]);
                $otherGrade = $secCheck->fetch(PDO::FETCH_ASSOC);
                if ($otherGrade) {
                    throw new InvalidArgumentException("Class section '{$targetSection}' does not belong to {$targetGrade}.");
                }
            }
        }

        $db->beginTransaction();
        try {
            $updateSql = "UPDATE students SET
                full_name = :full_name,
                first_name = :first_name,
                last_name = :last_name,
                date_of_birth = :date_of_birth,
                gender = :gender,
                national_id = :national_id,
                birth_certificate_number = :birth_certificate_number,
                class_id = :class_id,
                grade = :grade,
                class_section = :class_section,
                religion = :religion,
                home_address = :home_address,
                educational_zone = :educational_zone,
                district = :district,
                province = :province,
                previous_school = :previous_school,
                blood_group = :blood_group,
                medical_notes = :medical_notes,
                updated_at = NOW()
                WHERE id = :id";

            $stmt = $db->prepare($updateSql);
            $stmt->execute([
                ':full_name'                => $fullName,
                ':first_name'               => $firstName,
                ':last_name'                => $lastName,
                ':date_of_birth'            => !empty($data['dateOfBirth']) ? $data['dateOfBirth'] : $student['date_of_birth'],
                ':gender'                   => !empty($data['gender']) ? $data['gender'] : $student['gender'],
                ':national_id'              => $data['nationalId'] ?? $student['national_id'],
                ':birth_certificate_number' => $data['birthCertificateNumber'] ?? $student['birth_certificate_number'],
                ':class_id'                 => $targetClassId,
                ':grade'                    => !empty($data['grade']) ? $data['grade'] : $student['grade'],
                ':class_section'            => !empty($data['classSection']) ? $data['classSection'] : $student['class_section'],
                ':religion'                 => $data['religion'] ?? $student['religion'],
                ':home_address'             => $data['homeAddress'] ?? $student['home_address'],
                ':educational_zone'         => $data['educationalZone'] ?? $student['educational_zone'],
                ':district'                 => $data['district'] ?? $student['district'],
                ':province'                 => $data['province'] ?? $student['province'],
                ':previous_school'          => $data['previousSchool'] ?? $student['previous_school'],
                ':blood_group'              => $data['bloodGroup'] ?? $student['blood_group'],
                ':medical_notes'            => $data['medicalNotes'] ?? $student['medical_notes'],
                ':id'                       => $student['id']
            ]);

            // If status is passed, also update user_accounts activation_status
            if (!empty($data['status'])) {
                $statusMap = ['Active' => 'ACTIVE', 'Deactivated' => 'INACTIVE', 'Pending' => 'PENDING'];
                $newStatus = $statusMap[$data['status']] ?? null;
                if ($newStatus) {
                    $uStmt = $db->prepare("UPDATE user_accounts SET activation_status = ? WHERE id = ?");
                    $uStmt->execute([$newStatus, $student['account_id']]);
                }
            }

            AuditModel::record(
                $actorId,
                $indexNo,
                'STUDENT_PROFILE_UPDATED',
                "Student profile updated for {$fullName} ({$indexNo})."
            );

            $db->commit();

            $updatedStudent = StudentModel::findByIndex($indexNo);
            return [
                'success' => true,
                'message' => 'Student profile updated successfully.',
                'student' => $updatedStudent ? SqlJsMapper::studentToJs($updatedStudent) : null
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    /**
     * 5. Update a student's active status.
     */
    public static function updateStatus(string $indexNo, string $status, int $actorId): array {
        $db = Database::getConnection();
        $student = StudentModel::findByIndex($indexNo);
        if (!$student) {
            throw new InvalidArgumentException("Student with index {$indexNo} not found.");
        }

        $statusNormalized = ucfirst(strtolower(trim($status)));
        $dbStatus = match ($statusNormalized) {
            'Active'      => 'ACTIVE',
            'Deactivated' => 'INACTIVE',
            'Pending'     => 'PENDING',
            default       => throw new InvalidArgumentException("Invalid status: {$status}.")
        };

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE user_accounts SET activation_status = ? WHERE id = ?");
            $stmt->execute([$dbStatus, $student['account_id']]);

            $actionKey = ($dbStatus === 'ACTIVE') ? 'USER_ACTIVATED' : 'USER_DEACTIVATED';
            AuditModel::record(
                $actorId,
                $indexNo,
                $actionKey,
                "Student {$student['full_name']} ({$indexNo}) status updated to {$statusNormalized}."
            );

            $db->commit();
            return [
                'success' => true,
                'status'  => $statusNormalized,
                'message' => "Student {$indexNo} status set to {$statusNormalized}."
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
