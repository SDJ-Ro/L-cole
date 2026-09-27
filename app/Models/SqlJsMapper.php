<?php
/**
 * =========================================================================
 * L'ÉCOLE — SQL ↔ JAVASCRIPT / UI DATA MAPPER
 * =========================================================================
 * Centralized transformer model that bridges MySQL relational rows (snake_case)
 * to frontend JavaScript / UI JSON objects (camelCase), and vice-versa.
 *
 * Guarantees zero blank fields across directory tables, cards, and modals.
 * =========================================================================
 */

class SqlJsMapper {

    /**
     * Map a MySQL student row (+ joined parent details) to a camelCase JavaScript object.
     *
     * @param array $row Raw MySQL row from students + user_accounts + parents
     * @return array Standardized camelCase student object
     */
    public static function studentToJs(array $row): array {
        $statusRaw = strtoupper($row['account_status'] ?? $row['activation_status'] ?? 'ACTIVE');
        $statusLabel = match ($statusRaw) {
            'INACTIVE' => 'Deactivated',
            default    => 'Active'
        };

        $fullName = trim($row['full_name'] ?? '');
        $firstName = $row['first_name'] ?? (explode(' ', $fullName)[0] ?? '');
        $lastName = $row['last_name'] ?? (implode(' ', array_slice(explode(' ', $fullName), 1)) ?: '');
        $initials = '';
        if ($firstName !== '') $initials .= strtoupper(substr($firstName, 0, 1));
        if ($lastName !== '') $initials .= strtoupper(substr($lastName, 0, 1));
        if ($initials === '') $initials = strtoupper(substr($fullName, 0, 2)) ?: 'ST';

        $gradeRaw = $row['grade'] ?? 'Grade 6';
        $gradeNum = preg_replace('/[^0-9]/', '', $gradeRaw) ?: '6';
        $classSec = $row['class_section'] ?? $row['class_name'] ?? '6-A';
        $indexNo  = $row['index_no'] ?? $row['student_id'] ?? $row['id'] ?? '';

        $parentId   = $row['parent_id'] ?? '';
        $parentName = $row['parent_name'] ?? 'Unassigned';
        $parentEmail = $row['parent_email'] ?? $row['personal_email'] ?? '';
        $parentPhone = $row['parent_phone'] ?? $row['mobile_phone'] ?? '';
        $parentRel   = $row['relationship'] ?? 'Parent / Guardian';

        $guardianObj = null;
        if (!empty($parentId) || !empty($parentName)) {
            $pInitials = '';
            $pParts = explode(' ', trim($parentName));
            if (!empty($pParts[0])) $pInitials .= strtoupper(substr($pParts[0], 0, 1));
            if (isset($pParts[1])) $pInitials .= strtoupper(substr($pParts[1], 0, 1));

            $guardianObj = [
                'id'            => $parentId,
                'name'          => $parentName,
                'relationship'  => $parentRel,
                'relation'      => $parentRel,
                'email'         => $parentEmail,
                'phone'         => $parentPhone,
                'initials'      => $pInitials ?: 'PG',
                'accountAccess' => 'Account connected',
                'status'        => 'Active',
                'isAvailable'   => true
            ];
        }

        return [
            'persisted'               => true,
            'id'                      => (string)$indexNo,
            'index'                   => (string)$indexNo,
            'name'                    => $fullName,
            'firstName'               => $firstName,
            'lastName'                => $lastName,
            'initials'                => $initials,
            'gender'                  => $row['gender'] ?? 'Not specified',
            'dateOfBirth'             => $row['date_of_birth'] ?? '',
            'grade'                   => $gradeRaw,
            'gradeId'                 => 'g' . $gradeNum,
            'className'               => $classSec,
            'class'                   => 'Class ' . $classSec,
            'address'                 => $row['home_address'] ?? $row['address'] ?? '',
            'homeAddress'             => $row['home_address'] ?? $row['address'] ?? '',
            'zone'                    => $row['educational_zone'] ?? 'Colombo',
            'educationalZone'         => $row['educational_zone'] ?? 'Colombo',
            'district'                => $row['district'] ?? 'Colombo',
            'province'                => $row['province'] ?? 'Western',
            'bloodGroup'              => $row['blood_group'] ?? 'Not provided',
            'religion'                => $row['religion'] ?? '',
            'nationality'             => $row['nationality'] ?? 'Sri Lankan',
            'birthCertificateNumber'  => $row['birth_certificate_number'] ?? '',
            'previousSchool'          => $row['previous_school'] ?? 'None',
            'medicalNotes'            => $row['medical_notes'] ?? 'None recorded',
            'admissionDate'           => $row['admission_date'] ?? date('Y-m-d'),
            'activities'              => !empty($row['activities']) ? (is_array($row['activities']) ? $row['activities'] : explode(',', $row['activities'])) : [],
            'status'                  => $statusLabel,
            'activationStatus'        => $statusRaw,
            'avatar'                  => 'bg-sand text-midnight',
            'role'                    => 'student',
            // Contact & Guardian linkage (Students do not have their own email)
            'email'                   => $parentEmail ?: 'Not recorded',
            'parentEmail'             => $parentEmail ?: 'Not recorded',
            'phone'                   => $parentPhone ?: 'Not recorded',
            'parentPhone'             => $parentPhone ?: 'Not recorded',
            'parentName'              => $parentName,
            'parentId'                => (string)$parentId,
            'guardian'                => $guardianObj
        ];
    }

    /**
     * Map a MySQL parent row (+ linked students) to a camelCase JavaScript object.
     *
     * @param array $row Raw MySQL row from parents + user_accounts
     * @param array $linkedStudents Array of linked student associative items
     * @return array Standardized camelCase parent object
     */
    public static function parentToJs(array $row, array $linkedStudents = []): array {
        $statusRaw = strtoupper($row['account_status'] ?? $row['activation_status'] ?? 'ACTIVE');
        $statusLabel = match ($statusRaw) {
            'ACTIVE'   => 'Active',
            'INACTIVE' => 'Deactivated',
            default    => 'Pending'
        };

        $fullName = trim($row['full_name'] ?? '');
        $firstName = $row['first_name'] ?? (explode(' ', $fullName)[0] ?? '');
        $lastName = $row['last_name'] ?? (implode(' ', array_slice(explode(' ', $fullName), 1)) ?: '');
        $initials = '';
        if ($firstName !== '') $initials .= strtoupper(substr($firstName, 0, 1));
        if ($lastName !== '') $initials .= strtoupper(substr($lastName, 0, 1));
        if ($initials === '') $initials = strtoupper(substr($fullName, 0, 2)) ?: 'PG';

        $childrenLabels = [];
        $normalizedLinkedStudents = [];
        foreach ($linkedStudents as $st) {
            $stName = $st['name'] ?? $st['full_name'] ?? '';
            $stClass = $st['className'] ?? $st['class_section'] ?? '';
            $stId = $st['id'] ?? $st['index_no'] ?? '';
            $childrenLabels[] = "{$stName} — {$stClass}";
            $normalizedLinkedStudents[] = [
                'id'        => (string)$stId,
                'name'      => $stName,
                'className' => $stClass
            ];
        }

        // Concurrency version hash matching ParentModel::version
        $profileVersion = ParentModel::version($row);

        return [
            'persisted'        => true,
            'profileVersion'   => $profileVersion,
            'id'               => (string)($row['parent_id'] ?? ''),
            'parent_id'        => (string)($row['parent_id'] ?? ''),
            'name'             => $fullName,
            'firstName'        => $firstName,
            'lastName'         => $lastName,
            'initials'         => $initials,
            'relation'         => $row['relationship'] ?? 'Parent / Guardian',
            'relationship'     => $row['relationship'] ?? 'Parent / Guardian',
            'nic'              => $row['nic'] ?? '',
            'passport'         => $row['passport'] ?? '',
            'dateOfBirth'      => $row['date_of_birth'] ?? '',
            'email'            => $row['personal_email'] ?? '',
            'personalEmail'    => $row['personal_email'] ?? '',
            'phone'            => $row['mobile_phone'] ?? '',
            'mobile'           => $row['mobile_phone'] ?? '',
            'secondaryContact' => $row['home_phone'] ?? '',
            'homePhone'        => $row['home_phone'] ?? '',
            'officePhone'      => $row['office_phone'] ?? '',
            'occupation'       => $row['occupation'] ?? '',
            'employer'         => $row['employer'] ?? '',
            'officeAddress'    => $row['office_address'] ?? '',
            'address'          => $row['home_address'] ?? '',
            'homeAddress'      => $row['home_address'] ?? '',
            'emergencyName'    => $row['emergency_name'] ?? '',
            'emergencyContact' => $row['emergency_contact'] ?? '',
            'guardianStatus'   => 'Active',
            'status'           => $statusLabel,
            'activationStatus' => $statusRaw,
            'tone'             => 'bg-terracotta text-white',
            'role'             => 'parent',
            'children'         => $childrenLabels,
            'linkedStudents'   => $normalizedLinkedStudents
        ];
    }

    /**
     * Map a MySQL calendar event row to a standardized camelCase JavaScript object.
     * Provides both camelCase and snake_case scope properties for total interoperability.
     *
     * @param array $row Raw MySQL row from calendar_events
     * @return array Standardized camelCase event object
     */
    public static function calendarEventToJs(array $row): array {
        $id = (string)($row['id'] ?? '0');
        $date = !empty($row['event_date'])
            ? date('Y-m-d', strtotime($row['event_date']))
            : ($row['date'] ?? date('Y-m-d'));

        $startTime = $row['start_time'] ?? null;
        $endTime = $row['end_time'] ?? null;
        $timeStr = $row['time'] ?? '';
        if (empty($timeStr) && $startTime) {
            $s = date('H:i', strtotime($startTime));
            $timeStr = $endTime ? ($s . '–' . date('H:i', strtotime($endTime))) : $s;
        }

        $scopeType = $row['scope_type'] ?? ($row['scopeType'] ?? 'schoolwide');
        $rawScopeId = $row['scope_id'] ?? ($row['scopeId'] ?? null);
        $scopeId = null;
        if ($rawScopeId !== null && $rawScopeId !== '') {
            $scopeId = ($scopeType === 'grade') ? (string)$rawScopeId : (is_numeric($rawScopeId) ? (int)$rawScopeId : (string)$rawScopeId);
        }

        // Parse audience
        $rawAud = $row['audience'] ?? '["All"]';
        $audienceList = is_array($rawAud) ? $rawAud : json_decode($rawAud, true);
        if (!is_array($audienceList) || empty($audienceList)) {
            $audienceList = ['All'];
        }

        // Clean audience label (plain typography, zero emojis)
        $audLower = array_map('strtolower', $audienceList);
        if (in_array('all', $audLower, true)) {
            $audienceLabel = 'All School';
        } elseif (in_array('teachers', $audLower, true) && (in_array('management', $audLower, true) || count($audienceList) === 1)) {
            $audienceLabel = 'Staff Only';
        } elseif (in_array('students', $audLower, true) && in_array('parents', $audLower, true)) {
            $audienceLabel = 'Students & Parents';
        } elseif (in_array('parents', $audLower, true)) {
            $audienceLabel = 'Parents Only';
        } elseif (in_array('students', $audLower, true)) {
            $audienceLabel = 'Students Only';
        } else {
            $audienceLabel = implode(', ', $audienceList);
        }

        $authorRole = strtolower($row['author_role'] ?? 'admin');
        $authorName = trim($row['author_name'] ?? '');
        if (empty($authorName)) {
            $authorName = match ($authorRole) {
                'management' => 'Academic Management Board',
                'teacher'    => 'Faculty Staff',
                default      => 'Admin Office'
            };
        }

        return [
            'id'                    => $id,
            'date'                  => $date,
            'time'                  => $timeStr ?: '09:00',
            'title'                 => trim($row['title'] ?? ''),
            'details'               => trim($row['details'] ?? ''),
            'category'              => $row['category'] ?? 'Academic',
            'scopeType'             => $scopeType,
            'scopeId'               => $scopeId,
            'scope_type'            => $scopeType,
            'scope_id'              => $scopeId,
            'audience'              => $audienceList,
            'audienceLabel'         => $audienceLabel,
            'authorRole'            => $authorRole,
            'author_role'           => $authorRole,
            'authorName'            => $authorName,
            'author_name'           => $authorName,
            'authorLabel'           => 'Posted by ' . $authorName,
            'created_by_account_id' => (int)($row['created_by_account_id'] ?? 0)
        ];
    }

    /**
     * Map a MySQL teacher row to a standardized frontend teacher object.
     */
    public static function teacherToJs(array $row, array $qualifications = [], ?string $homeroom = null, array $clubs = [], array $sports = []): array {
        $statusRaw = strtoupper($row['activation_status'] ?? $row['account_status'] ?? 'ACTIVE');
        $statusLabel = match ($statusRaw) {
            'ACTIVE'   => 'Active',
            'INACTIVE' => 'Deactivated',
            default    => 'Pending'
        };

        $fullName = trim($row['full_name'] ?? '');
        $firstName = $row['first_name'] ?? (explode(' ', $fullName)[0] ?? '');
        $lastName = $row['last_name'] ?? (implode(' ', array_slice(explode(' ', $fullName), 1)) ?: '');
        $initials = '';
        if ($firstName !== '') $initials .= strtoupper(substr($firstName, 0, 1));
        if ($lastName !== '') $initials .= strtoupper(substr($lastName, 0, 1));
        if ($initials === '') $initials = strtoupper(substr($fullName, 0, 2)) ?: 'TE';

        $staffId = $row['staff_id'] ?? ('T-' . str_pad((string)($row['id'] ?? '1'), 3, '0', STR_PAD_LEFT));
        $classTeacherOf = $homeroom ?? ($row['homeroom_class_name'] ?? null);

        $ticList = array_merge($clubs, $sports);
        $primaryTic = !empty($ticList) ? $ticList[0] : ($row['tic'] ?? 'None');
        $finalQualifications = !empty($qualifications) ? $qualifications : ($row['qualifications'] ?? []);

        return [
            'id'                   => $staffId,
            'staffId'              => $staffId,
            'dbId'                 => (int)($row['id'] ?? 0),
            'accountId'            => (int)($row['account_id'] ?? 0),
            'name'                 => $fullName,
            'fullName'             => $fullName,
            'firstName'            => $firstName,
            'lastName'             => $lastName,
            'initials'             => $initials,
            'nic'                  => $row['nic'] ?? '',
            'dateOfBirth'          => $row['date_of_birth'] ?? '',
            'dob'                  => $row['date_of_birth'] ?? '',
            'officeAddress'        => $row['office_address'] ?? '',
            'phone'                => $row['phone'] ?? '',
            'mobileNumber'         => $row['phone'] ?? '',
            'email'                => $row['institutional_email'] ?? '',
            'institutionalEmail'   => $row['institutional_email'] ?? '',
            'personalEmail'        => $row['personal_email'] ?? '',
            'subject'              => $row['subjects'] ?? 'General',
            'subjects'             => $row['subjects'] ?? 'General',
            'experience'           => (string)($row['experience_years'] ?? '0'),
            'experienceYears'      => (int)($row['experience_years'] ?? 0),
            'joinDate'             => $row['join_date'] ?? date('Y-m-d'),
            'joiningDate'          => $row['join_date'] ?? date('Y-m-d'),
            'emergencyName'        => $row['emergency_name'] ?? '',
            'emergencyPhone'       => $row['emergency_phone'] ?? '',
            'emergencyContact'     => $row['emergency_phone'] ?? '',
            'status'               => $statusLabel,
            'accountStatus'        => $statusRaw,
            'role'                 => $classTeacherOf ? 'Class Teacher' : 'Subject Teacher',
            'classTeacherOf'       => $classTeacherOf,
            'classes'              => $classTeacherOf ? [$classTeacherOf] : [],
            'tic'                  => $primaryTic,
            'ticRole'              => $primaryTic !== 'None' ? 'Teacher in Charge & Faculty Mentor' : 'None',
            'extracurriculars'     => $ticList,
            'qualifications'       => $finalQualifications,
            'tone'                 => 'bg-sunshine text-white',
            'avatar'               => 'bg-sunshine text-white',
            'persisted'            => true
        ];
    }

    /**
     * Map a MySQL management row to a standardized frontend management object.
     */
    public static function managementToJs(array $row): array {
        $statusRaw = strtoupper($row['activation_status'] ?? $row['account_status'] ?? 'ACTIVE');
        $statusLabel = match ($statusRaw) {
            'ACTIVE'   => 'Active',
            'INACTIVE' => 'Deactivated',
            default    => 'Pending'
        };

        $fullName = trim($row['full_name'] ?? '');
        $firstName = $row['first_name'] ?? (explode(' ', $fullName)[0] ?? '');
        $lastName = $row['last_name'] ?? (implode(' ', array_slice(explode(' ', $fullName), 1)) ?: '');
        $initials = '';
        if ($firstName !== '') $initials .= strtoupper(substr($firstName, 0, 1));
        if ($lastName !== '') $initials .= strtoupper(substr($lastName, 0, 1));
        if ($initials === '') $initials = strtoupper(substr($fullName, 0, 2)) ?: 'MG';

        $staffId = $row['staff_id'] ?? ('M-' . str_pad((string)($row['id'] ?? '1'), 3, '0', STR_PAD_LEFT));

        return [
            'id'                   => $staffId,
            'staffId'              => $staffId,
            'dbId'                 => (int)($row['id'] ?? 0),
            'accountId'            => (int)($row['account_id'] ?? 0),
            'name'                 => $fullName,
            'fullName'             => $fullName,
            'firstName'            => $firstName,
            'lastName'             => $lastName,
            'initials'             => $initials,
            'nic'                  => $row['nic'] ?? '',
            'phone'                => $row['phone'] ?? '',
            'contactNumber'        => $row['phone'] ?? '',
            'email'                => $row['institutional_email'] ?? '',
            'institutionalEmail'   => $row['institutional_email'] ?? '',
            'personalEmail'        => $row['personal_email'] ?? '',
            'jobTitle'             => $row['title'] ?? 'Executive Staff',
            'title'                => $row['title'] ?? 'Executive Staff',
            'officeLocation'       => $row['office_location'] ?? '',
            'joinDate'             => $row['join_date'] ?? date('Y-m-d'),
            'joiningDate'          => $row['join_date'] ?? date('Y-m-d'),
            'emergencyName'        => $row['emergency_name'] ?? '',
            'emergencyPhone'       => $row['emergency_phone'] ?? '',
            'emergencyContact'     => $row['emergency_phone'] ?? '',
            'status'               => $statusLabel,
            'accountStatus'        => $statusRaw,
            'tone'                 => 'bg-maroon text-white',
            'avatar'               => 'bg-maroon text-white',
            'persisted'            => true
        ];
    }

    /**
     * Map a MySQL notice row to a standardized frontend notice object.
     *
     * @param array $row Raw MySQL row from notices
     * @return array Standardized notice object
     */
    public static function noticeToJs(array $row): array {
        require_once __DIR__ . '/NoticeModel.php';
        return NoticeModel::formatRow($row);
    }
}
