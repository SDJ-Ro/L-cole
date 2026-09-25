<?php
class StudentAdmissionModel {
    public static function changeGuardian(string $studentIndex, string $parentCode, int $actorId): array {
        $studentIndex = trim($studentIndex);
        $parentCode = trim($parentCode);
        if ($studentIndex === '' || $parentCode === '') throw new InvalidArgumentException('Choose a student and a replacement parent.');
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $studentQuery = $db->prepare("SELECT s.id,s.index_no,u.activation_status FROM students s JOIN user_accounts u ON u.id=s.account_id WHERE s.index_no=? AND u.role='student' FOR UPDATE");
            $studentQuery->execute([$studentIndex]);
            $student = $studentQuery->fetch();
            if (!$student) throw new InvalidArgumentException('Student not found.');
            if ($student['activation_status'] === 'INACTIVE') throw new RuntimeException('A deactivated student’s guardian history cannot be changed.', 409);

            $parentQuery = $db->prepare("SELECT p.id,p.parent_id,p.full_name,u.activation_status FROM parents p JOIN user_accounts u ON u.id=p.account_id WHERE p.parent_id=? AND u.role='parent' FOR UPDATE");
            $parentQuery->execute([$parentCode]);
            $parent = $parentQuery->fetch();
            if (!$parent || $parent['activation_status'] === 'INACTIVE') throw new InvalidArgumentException('Choose an active or pending parent account.');

            $currentQuery = $db->prepare("SELECT p.id,p.parent_id FROM student_parents sp JOIN parents p ON p.id=sp.parent_id WHERE sp.student_id=? AND sp.is_primary=1 FOR UPDATE");
            $currentQuery->execute([$student['id']]);
            $current = $currentQuery->fetch();
            if ($current && (int)$current['id'] === (int)$parent['id']) throw new InvalidArgumentException('That parent is already this student’s guardian.');

            $link = $db->prepare('INSERT INTO student_parents (student_id,parent_id,is_primary) VALUES (?,?,1) ON DUPLICATE KEY UPDATE is_primary=1');
            $link->execute([$student['id'],$parent['id']]);
            $removeOld = $db->prepare('DELETE FROM student_parents WHERE student_id=? AND parent_id<>?');
            $removeOld->execute([$student['id'],$parent['id']]);
            $details = 'Changed guardian for '.$student['index_no'].' from '.($current['parent_id'] ?? 'none').' to '.$parent['parent_id'];
            $audit = $db->prepare('INSERT INTO activity_logs (account_id,action,ip_address,details) VALUES (?,?,?,?)');
            $audit->execute([$actorId,'STUDENT_GUARDIAN_CHANGED',substr($_SERVER['REMOTE_ADDR'] ?? '',0,45),$details]);
            $db->commit();
            return ['parentId'=>$parent['parent_id'],'parentName'=>$parent['full_name']];
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public static function getStudents(): array {
        $db = Database::getConnection();
        $rows = $db->query("SELECT s.id,s.index_no,s.full_name,s.first_name,s.last_name,
            s.grade,s.class_section,s.date_of_birth,s.gender,s.home_address,s.admission_date,
            u.activation_status,p.parent_id,p.full_name AS parent_name,p.personal_email,p.mobile_phone
            FROM students s JOIN user_accounts u ON u.id=s.account_id
            LEFT JOIN student_parents sp ON sp.student_id=s.id AND sp.is_primary=1
            LEFT JOIN parents p ON p.id=sp.parent_id ORDER BY s.id DESC")->fetchAll();
        $students = [];
        foreach ($rows as $row) {
            $students[] = [
                'persisted'=>true, 'id'=>$row['index_no'], 'name'=>$row['full_name'],
                'firstName'=>$row['first_name'], 'lastName'=>$row['last_name'],
                'gradeId'=>'g'.str_replace('Grade ', '', $row['grade']),
                'className'=>$row['class_section'], 'activities'=>[],
                'email'=>$row['personal_email'] ?? '', 'parentName'=>$row['parent_name'] ?? '',
                'parentId'=>$row['parent_id'] ?? '', 'phone'=>$row['mobile_phone'] ?? '',
                'dateOfBirth'=>$row['date_of_birth'], 'gender'=>$row['gender'],
                'address'=>$row['home_address'], 'admissionDate'=>$row['admission_date'],
                'status'=>match($row['activation_status']) {
                    'ACTIVE'=>'Active', 'INACTIVE'=>'Deactivated', default=>'Pending'
                }
            ];
        }
        return $students;
    }

    public static function validate(array $input): array {
        $limits = [
            'fullName'=>150, 'firstName'=>75, 'lastName'=>75, 'dateOfBirth'=>10,
            'gender'=>20, 'nationalId'=>30, 'birthCertificateNumber'=>50,
            'grade'=>20, 'classSection'=>10, 'religion'=>50, 'homeAddress'=>2000,
            'admissionDate'=>10, 'nationality'=>50, 'educationalZone'=>50,
            'district'=>50, 'province'=>50, 'previousSchool'=>150,
            'bloodGroup'=>15, 'medicalNotes'=>2000
        ];
        $student = [];
        foreach ($limits as $field=>$limit) {
            if (isset($input[$field]) && !is_string($input[$field])) {
                throw new InvalidArgumentException('Invalid value for ' . $field . '.');
            }
            $student[$field] = trim($input[$field] ?? '');
            if (strlen($student[$field]) > $limit) {
                throw new InvalidArgumentException('Value too long for ' . $field . '.');
            }
        }
        foreach (['fullName','firstName','lastName','dateOfBirth','gender',
                  'birthCertificateNumber','grade','classSection','homeAddress',
                  'admissionDate','nationality','educationalZone','district','province','bloodGroup'] as $field) {
            if ($student[$field] === '') {
                throw new InvalidArgumentException('Please complete the student ' . $field . '.');
            }
        }
        foreach (['dateOfBirth','admissionDate'] as $field) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $student[$field]);
            if (!$date || $date->format('Y-m-d') !== $student[$field]) {
                throw new InvalidArgumentException('Enter a valid student ' . $field . '.');
            }
        }
        if ($student['dateOfBirth'] >= date('Y-m-d') || $student['dateOfBirth'] >= $student['admissionDate']) {
            throw new InvalidArgumentException('Date of birth must be before today and admission.');
        }
        if (!in_array($student['gender'], ['Male','Female','Prefer not to say'], true)) {
            throw new InvalidArgumentException('Select a valid gender.');
        }

        $validClass = false;
        foreach (PeopleModel::getGrades() as $grade) {
            if ($grade['name'] === $student['grade'] && in_array($student['classSection'], $grade['classes'], true)) {
                $validClass = true;
            }
        }
        if (!$validClass) throw new InvalidArgumentException('Select a valid grade and class.');
        return $student;
    }

    public static function register(array $input, int $actorId): array {
        $student = self::validate($input);
        $mode = $input['guardianMode'] ?? '';
        $requestKey = $input['admissionKey'] ?? '';
        if (!is_string($requestKey) || !preg_match('/^[a-f0-9]{32}$/', $requestKey)) {
            throw new InvalidArgumentException('Refresh the form and try again.');
        }
        if (!in_array($mode, ['existing','new'], true)) {
            throw new InvalidArgumentException('Choose an existing parent or a new parent.');
        }
        $parentCode = $input['existingParent'] ?? '';
        if (!is_string($parentCode)) throw new InvalidArgumentException('Select a valid parent.');
        $parent = null;
        if ($mode === 'new') {
            if (!isset($input['guardian']) || !is_array($input['guardian'])) {
                throw new InvalidArgumentException('Enter the guardian details.');
            }
            $parent = ParentModel::validate($input['guardian']);
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {

            $request = $db->prepare('INSERT INTO student_admissions (request_key, created_by) VALUES (?,?)');
            $request->execute([$requestKey, $actorId]);

            if ($mode === 'existing') {
                $lookup = $db->prepare("SELECT p.id, u.activation_status
                    FROM parents p JOIN user_accounts u ON u.id=p.account_id
                    WHERE p.parent_id=? AND u.role='parent' FOR UPDATE");
                $lookup->execute([$parentCode]);
                $existing = $lookup->fetch();
                if (!$existing || $existing['activation_status'] === 'INACTIVE') {
                    throw new InvalidArgumentException('Select an active or pending parent. Inactive accounts cannot receive new students.');
                }
                $parentId = (int) $existing['id'];
            } else {
                $parentId = ParentModel::insertForAdmission($db, $parent);
            }

            $account = $db->prepare("INSERT INTO user_accounts
                (identifier, role, activation_status, first_login_required)
                VALUES (?, 'student', 'PENDING', 1)");
            $account->execute(['admission-' . $requestKey]);
            $accountId = (int) $db->lastInsertId();
            $index = 'STU-' . date('Y') . '-' . str_pad((string) $accountId, 6, '0', STR_PAD_LEFT);
            $rename = $db->prepare('UPDATE user_accounts SET identifier=? WHERE id=?');
            $rename->execute([$index, $accountId]);

            $profile = $db->prepare('INSERT INTO students
                (account_id,index_no,full_name,first_name,last_name,date_of_birth,gender,
                 national_id,birth_certificate_number,grade,class_section,religion,home_address,
                 admission_date,nationality,educational_zone,district,province,previous_school,blood_group,medical_notes)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $profile->execute([
                $accountId,$index,$student['fullName'],$student['firstName'],$student['lastName'],
                $student['dateOfBirth'],$student['gender'],$student['nationalId'],
                $student['birthCertificateNumber'],$student['grade'],$student['classSection'],
                $student['religion'],$student['homeAddress'],$student['admissionDate'],
                $student['nationality'],$student['educationalZone'],$student['district'],
                $student['province'],$student['previousSchool'],$student['bloodGroup'],$student['medicalNotes']
            ]);
            $studentId = (int) $db->lastInsertId();
            $link = $db->prepare('INSERT INTO student_parents (student_id,parent_id,is_primary) VALUES (?,?,1)');
            $link->execute([$studentId,$parentId]);
            $complete = $db->prepare('UPDATE student_admissions SET student_id=? WHERE request_key=?');
            $complete->execute([$studentId,$requestKey]);
            $audit = $db->prepare('INSERT INTO activity_logs (account_id,action,ip_address,details) VALUES (?,?,?,?)');
            $audit->execute([$actorId,'STUDENT_ADMITTED',substr($_SERVER['REMOTE_ADDR'] ?? '',0,45),
                'Student '.$index.' linked to parent row '.$parentId.'; guardian mode: '.$mode]);
            $db->commit();
            return ['index'=>$index,'studentId'=>$studentId];
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            if ($error instanceof PDOException && ($error->errorInfo[1] ?? null) === 1062) {
                $prior = $db->prepare('SELECT s.id,s.index_no FROM student_admissions a
                    JOIN students s ON s.id=a.student_id WHERE a.request_key=? AND a.created_by=?');
                $prior->execute([$requestKey,$actorId]);
                $saved = $prior->fetch();
                if ($saved) return ['index'=>$saved['index_no'],'studentId'=>(int)$saved['id']];
                throw new InvalidArgumentException('An account already uses this email. Select the existing parent instead.');
            }
            throw $error;
        }
    }
}
