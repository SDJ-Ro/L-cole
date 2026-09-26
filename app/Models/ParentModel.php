<?php
class ParentModel {
    public static function validate(array $input): array {
        $limits = ['fullName'=>150,'firstName'=>75,'lastName'=>75,'nic'=>30,'dateOfBirth'=>10,'occupation'=>100,'mobile'=>30,'email'=>191,'relationship'=>50,'passport'=>50,'employer'=>150,'homePhone'=>30,'officePhone'=>30,'officeAddress'=>255,'emergencyName'=>150,'emergencyContact'=>30];
        $data = [];
        foreach ($limits as $key=>$max) {
            if (isset($input[$key]) && !is_string($input[$key])) throw new InvalidArgumentException('Invalid field: '.$key);
            $data[$key] = trim($input[$key] ?? '');
            if (strlen($data[$key]) > $max) throw new InvalidArgumentException('Value too long: '.$key);
        }
        foreach (['fullName','firstName','lastName','nic','dateOfBirth','occupation','mobile','email','relationship'] as $key) {
            if ($data[$key] === '') throw new InvalidArgumentException('Please complete '.$key.'.');
        }
        $data['email'] = strtolower($data['email']);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
        if (!in_array($data['relationship'], ['Father','Mother','Guardian'], true)) throw new InvalidArgumentException('Select a valid relationship.');
        $dob = DateTimeImmutable::createFromFormat('!Y-m-d', $data['dateOfBirth']);
        if (!$dob || $dob->format('Y-m-d') !== $data['dateOfBirth'] || $dob >= new DateTimeImmutable('today')) throw new InvalidArgumentException('Enter a valid past date of birth.');
        foreach (['mobile','homePhone','officePhone','emergencyContact'] as $key) {
            if ($data[$key] !== '' && !preg_match('/^\+?[0-9 ()-]{7,30}$/', $data[$key])) throw new InvalidArgumentException('Enter a valid phone number for '.$key.'.');
        }
        return $data;
    }
    public static function insertForAdmission(PDO $db, array $data): int {
        if (!$db->inTransaction()) {
            throw new LogicException('Parent creation requires a student admission transaction.');
        }
        $account = $db->prepare(
            "INSERT INTO user_accounts (identifier, role, activation_status) VALUES (?, 'parent', 'PENDING')"
        );
        $account->execute([$data['email']]);
        $accountId = (int) $db->lastInsertId();
        $parentCode = 'PAR-' . date('Y') . '-' . str_pad((string) $accountId, 6, '0', STR_PAD_LEFT);

        $profile = $db->prepare('INSERT INTO parents
            (account_id, parent_id, relationship, full_name, first_name, last_name, nic,
             date_of_birth, occupation, mobile_phone, personal_email, passport, employer,
             home_phone, office_phone, office_address, emergency_name, emergency_contact)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $profile->execute([
            $accountId, $parentCode, $data['relationship'], $data['fullName'],
            $data['firstName'], $data['lastName'], $data['nic'], $data['dateOfBirth'],
            $data['occupation'], $data['mobile'], $data['email'], $data['passport'],
            $data['employer'], $data['homePhone'], $data['officePhone'],
            $data['officeAddress'], $data['emergencyName'], $data['emergencyContact']
        ]);
        return (int) $db->lastInsertId();
    }
    public static function findByCode(string $code): ?array {
        $query = Database::getConnection()->prepare("SELECT p.* FROM parents p
            JOIN user_accounts u ON u.id=p.account_id WHERE p.parent_id=? AND u.role='parent'");
        $query->execute([$code]);
        return $query->fetch() ?: null;
    }

    public static function version(array $parent): string {
        return hash('sha256', json_encode($parent, JSON_THROW_ON_ERROR));
    }

    public static function editValues(array $parent): array {
        return [
            'fullName'=>$parent['full_name'], 'firstName'=>$parent['first_name'],
            'lastName'=>$parent['last_name'], 'nic'=>$parent['nic'],
            'dateOfBirth'=>$parent['date_of_birth'], 'relationship'=>$parent['relationship'],
            'occupation'=>$parent['occupation'], 'mobile'=>$parent['mobile_phone'],
            'passport'=>$parent['passport'] ?? '', 'employer'=>$parent['employer'] ?? '',
            'homePhone'=>$parent['home_phone'] ?? '', 'officePhone'=>$parent['office_phone'] ?? '',
            'officeAddress'=>$parent['office_address'] ?? '', 'homeAddress'=>$parent['home_address'] ?? '',
            'emergencyName'=>$parent['emergency_name'] ?? '', 'emergencyContact'=>$parent['emergency_contact'] ?? ''
        ];
    }

    public static function updateProfile(string $code, array $input, int $actorId): void {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $query = $db->prepare("SELECT p.* FROM parents p JOIN user_accounts u ON u.id=p.account_id
                WHERE p.parent_id=? AND u.role='parent' FOR UPDATE");
            $query->execute([$code]);
            $parent = $query->fetch();
            if (!$parent) throw new InvalidArgumentException('Parent not found.');
            $version = $input['version'] ?? '';
            if (!is_string($version) || !hash_equals(self::version($parent), $version)) {
                throw new RuntimeException('This parent was changed after you opened the form. Reload the latest details before editing again.', 409);
            }
            foreach (['email','role','status','account_id','parent_id','children','linkedStudents'] as $protected) {
                if (array_key_exists($protected, $input)) {
                    throw new InvalidArgumentException('Account access and child links cannot be changed in this form.');
                }
            }
            foreach (['fullName'=>'full_name', 'firstName'=>'first_name', 'lastName'=>'last_name'] as $field=>$column) {
                if (array_key_exists($field, $input) && $input[$field] !== $parent[$column]) {
                    throw new InvalidArgumentException('Parent names cannot be changed through profile editing.');
                }
                $input[$field] = $parent[$column];
            }
            if (array_key_exists('relationship', $input) && $input['relationship'] !== $parent['relationship']) {
                throw new InvalidArgumentException('The parent relationship cannot be changed through profile editing.');
            }
            $input['relationship'] = $parent['relationship'];

            $data = self::validate(array_merge($input, ['email'=>$parent['personal_email']]));
            $address = $input['homeAddress'] ?? '';
            if (!is_string($address) || strlen($address) > 2000) {
                throw new InvalidArgumentException('Home address must be text of at most 2000 characters.');
            }
            $query = $db->prepare('UPDATE parents SET full_name=?,first_name=?,last_name=?,nic=?,
                date_of_birth=?,relationship=?,occupation=?,mobile_phone=?,passport=?,employer=?,
                home_phone=?,office_phone=?,office_address=?,home_address=?,emergency_name=?,emergency_contact=?
                WHERE id=?');
            $query->execute([
                $data['fullName'],$data['firstName'],$data['lastName'],$data['nic'],
                $data['dateOfBirth'],$data['relationship'],$data['occupation'],$data['mobile'],
                $data['passport'],$data['employer'],$data['homePhone'],$data['officePhone'],
                $data['officeAddress'],trim($address),$data['emergencyName'],$data['emergencyContact'],$parent['id']
            ]);
            $audit = $db->prepare('INSERT INTO activity_logs (account_id,action,ip_address,details) VALUES (?,?,?,?)');
            $audit->execute([$actorId,'PARENT_PROFILE_UPDATED',substr($_SERVER['REMOTE_ADDR'] ?? '',0,45),
                'Updated parent profile '.$code]);
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public static function deactivate(string $code, int $actorId): void {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {

            $query = $db->prepare("SELECT p.id,p.account_id,u.activation_status
                FROM parents p JOIN user_accounts u ON u.id=p.account_id
                WHERE p.parent_id=? AND u.role='parent' FOR UPDATE");
            $query->execute([$code]);
            $parent = $query->fetch();
            if (!$parent) throw new InvalidArgumentException('Parent not found.');

            $students = $db->prepare("SELECT u.activation_status
                FROM student_parents sp
                JOIN students s ON s.id=sp.student_id
                JOIN user_accounts u ON u.id=s.account_id AND u.role='student'
                WHERE sp.parent_id=? FOR UPDATE");
            $students->execute([$parent['id']]);
            foreach ($students->fetchAll(PDO::FETCH_COLUMN) as $studentStatus) {
                if ($studentStatus !== 'INACTIVE') {
                    throw new RuntimeException('This parent has an active or pending student. Deactivate every linked student before deactivating the parent account.', 409);
                }
            }
            if ($parent['activation_status'] === 'INACTIVE') {
                $db->commit();
                return;
            }
            $update = $db->prepare("UPDATE user_accounts SET activation_status='INACTIVE' WHERE id=?");
            $update->execute([$parent['account_id']]);
            $audit = $db->prepare('INSERT INTO activity_logs (account_id,action,ip_address,details) VALUES (?,?,?,?)');
            $audit->execute([$actorId,'PARENT_DEACTIVATED',substr($_SERVER['REMOTE_ADDR'] ?? '',0,45),
                'Deactivated parent '.$code.'; profile and history retained']);
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public static function getAll(): array {
        $db=Database::getConnection();
        $rows=$db->query("SELECT p.*, u.activation_status FROM parents p JOIN user_accounts u ON u.id=p.account_id WHERE u.role='parent' ORDER BY p.id DESC")->fetchAll();
        $links = [];
        $linkedStudents = [];
        $children = $db->query('SELECT sp.parent_id,s.index_no,s.full_name,s.class_section
            FROM student_parents sp JOIN students s ON s.id=sp.student_id ORDER BY s.id')->fetchAll();
        foreach ($children as $child) {
            $links[$child['parent_id']][] = $child['full_name'].' — '.$child['class_section'];
            $linkedStudents[$child['parent_id']][] = [
                'id'=>$child['index_no'], 'name'=>$child['full_name'], 'className'=>$child['class_section']
            ];
        }
        return array_map(function($r) use($links, $linkedStudents) {
            $profile = $r;
            unset($profile['activation_status']);
            return ['persisted'=>true,'profileVersion'=>self::version($profile),'id'=>$r['parent_id'],'name'=>$r['full_name'],'firstName'=>$r['first_name'],'lastName'=>$r['last_name'],'email'=>$r['personal_email'],'phone'=>$r['mobile_phone'],'relation'=>$r['relationship'],'nic'=>$r['nic'],'dateOfBirth'=>$r['date_of_birth'],'passport'=>$r['passport'],'occupation'=>$r['occupation'],'employer'=>$r['employer'],'secondaryPhone'=>$r['home_phone'],'officePhone'=>$r['office_phone'],'homeAddress'=>$r['home_address'] ?? '', 'officeAddress'=>$r['office_address'],'emergencyName'=>$r['emergency_name'],'emergencyContact'=>$r['emergency_contact'],'children'=>$links[$r['id']] ?? [],'linkedStudents'=>$linkedStudents[$r['id']] ?? [],'status'=>match($r['activation_status']) {'ACTIVE'=>'Active','INACTIVE'=>'Deactivated',default=>'Pending'},'tone'=>'bg-deepsea text-white'];
        },$rows);
    }
}
