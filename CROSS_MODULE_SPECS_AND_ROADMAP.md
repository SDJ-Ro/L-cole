# L'École — Cross-Module Architecture, Lifecycle Specs & Engineering Roadmap

**Target Delivery Date:** September 27, 2026  
**Document Status:** Approved Architecture Blueprint  
**Scope:** Cross-cutting lifecycle rules, dependency guards, white-labeling foundation, and backend/frontend standards across Academic, People (Staff/Students/Parents), and System Administration.

---

## 1. Core Architecture Standards (Flat MVC Discipline)

### 1.1 Model Separation Strategy
To prevent monolithic models while maintaining consistency across the team's flat MVC structure (avoiding nested `Modules/` folders):
- **`app/Models/{Feature}Model.php`**: Strictly handles **read queries**, data retrieval, view aggregations, and statistics.
- **`app/Models/{Feature}Actions.php`**: Strictly handles **state mutations** (Create, Edit, Delete, Assign, Cascade).

### 1.2 The 6-Step Write Discipline
Every mutation method in any `{Feature}Actions.php` must follow this exact sequential shape:
1. **Input Sanitization & Validation**: Type checks, regex formatting, boundary guards (e.g. min 15, max 40).
2. **Business-Rule Verification**: Cap enforcement, uniqueness verification, foreign key dependency checks.
3. **Fail-Fast Early Exit**: If any check fails, immediately return `['success' => false, 'error' => '...']` without opening a database transaction.
4. **Transaction Boundary**: Wrap all database writes in `$db->beginTransaction()` and `$db->commit()`.
5. **Integrated Audit Logging**: Invoke `AuditModel::record(...)` / `activity_logs` inside the same transaction.
6. **Consistent Return Payload**: Return `['success' => true, 'data' => ...]`.

### 1.3 Self-Contained Controller Traits
Shared AJAX controllers (e.g., `AcademicCrudTrait`, `PeopleCrudTrait`, `NoticeCrudTrait`) must maintain their own local copies of request parsing and response helpers (`getRequestPayload()`, `sendJson()`, `getActorDetails()`).  
*Rationale:* Avoids a shared base trait file that acts as a bottleneck and causes Git merge conflicts in a multi-developer team.

---

## 2. Academic Module Lifecycle & Guards

### 2.1 Grade Deletion Guard (The Enrolment Guard)
- **Constraint:** A Grade record can **never** be deleted while enrolled students exist (`COUNT(students) > 0`).
- **Behavior:** The system throws an administrative block:  
  *"Cannot delete [Grade Name]. There are currently [N] students enrolled. Reassign or graduate students before removing this grade."*
- **Deletion Allowed:** Only when student count is `0`. The transaction cascades to remove empty class sections, teacher assignments, and the grade row atomically.

### 2.2 Student Capacity vs. Enrolment Headcount
- **`classes.student_count` (Administrative Capacity):** Manual capacity set by the admin (Min: 15, Max: 40). Represents physical room size / planning cap.
- **`COUNT(*) FROM students WHERE class_section = ...` (Enrolment Headcount):** The true live count of enrolled students.
- **Status Indicators:**
  - `enrolled < capacity`: Normal / Open.
  - `enrolled == capacity`: At Capacity (Yellow warning badge).
  - `enrolled >= 40`: Hard Full (Red badge; admissions dropdown locks section).

### 2.3 Cohort Progression vs. Shuffling
- **Year-End Promotion:** Default is cohort continuity (`6-A` rolls over directly to `7-A`).
- **No Automated Random Shuffling:** Real schools do not randomly shuffle classes due to sibling arrangements, carpools, behavior balancing, and parent expectations.
- **Manual Adjustments:** Staff can manually move individual students between sections in the Students directory.

### 2.4 Curriculum Overlap & Deadlock Prevention
- **1 Grade = Exactly 1 Curriculum Stage:** A grade cannot have conflicting curriculums.
- **Auto-Transfer / Steal on Expand:** When an admin expands a curriculum stage (e.g., "Years 10–11" to "Years 9–11"), Grade 9 is cleanly transferred to the new stage without a chicken-and-egg error block.
- **Unassigned Curriculum Status:** If a curriculum stage is shrunk (e.g., "Years 6–9" down to "Years 6–8"), Grade 9 is not deleted; it temporarily receives `group_id = NULL` and displays a polite dashboard badge:  
  `⚠️ Grade 9: No curriculum assigned. Click to assign a curriculum stage.`
- **Non-Empty Curriculum Rule:** A curriculum stage must always retain at least 1 subject and at least 1 grade.

### 2.5 Teacher Assignment Constraints
- **1:1 Homeroom Exclusivity:** A teacher cannot be the class teacher of more than 1 class section. Reassigning them automatically clears their previous section and flags it as pending.
- **5-Subject Workload Cap:** A teacher cannot be assigned to more than 5 subjects across all class sections.

---

## 3. People / User Lifecycle Specifications

### 3.1 Global Inactivation Policy (Zero Hard Deletes)
- **Rule:** User records (`teachers`, `students`, `parents`, `user_accounts`) are **never hard-deleted** from the database.
- **Rationale:** Historical attendance records, report cards, fee receipts, and audit logs require foreign key integrity and legal auditability.
- **Mechanism:** Status is toggled to `'inactive'` / `'deactivated'`, disabling portal login while retaining full historical records.

### 3.2 Teacher Inactivation Handover Workflow
When an administrator sets a teacher's status to `Deactivated`:
1. System queries active roles:
   - Homeroom class assignments (`class_teachers`)
   - Subject teaching assignments (`class_subject_teachers`)
   - Extracurricular / Club advisor roles
2. The UI renders the **Handover Modal**:
   - For each active role, admin can:
     - Select a replacement teacher from an available dropdown.
     - OR choose **"Leave Unassigned"**.
3. If left unassigned, the Academic Dashboard displays an alert banner:  
   `⚠️ Class 6-A has no Class Teacher. Click to assign.`

### 3.3 Parent & Guardian Dependency Guard
- **Constraint:** Every enrolled student must have at least one active primary emergency contact/guardian.
- **Single Guardian Inactivation:** If a parent is the sole linked guardian for an active student, the system blocks deactivation:  
  *"Cannot deactivate parent. Student [Student Name] has no other registered guardian. Please link a secondary contact first."*
- **Multiple Guardians:** If a secondary guardian exists, deactivating the primary guardian automatically promotes the secondary guardian to primary.

### 3.4 Student Withdrawal & Archival
- **Seat Release:** Marking a student `'withdrawn'` or `'graduated'` immediately decrements the active headcount of their class section.
- **No Handover Required:** Students are leaf entities; removing or withdrawing a student does not break other system roles.

### 3.5 System Administrator Security Guard ("Last Admin Standing")
- **Constraint:** The system strictly blocks deactivating or revoking the admin role from the final active Administrator:  
  `SELECT COUNT(*) FROM user_accounts WHERE role = 'Admin' AND status = 'active'` must remain $\ge 1$.
- **Alert:** *"Operation forbidden: The system requires at least one active Administrator account."*

---

## 4. White-Labeling & Universal Naming Foundation

### 4.1 Schema Flexibility
Database columns for naming entities are defined as generic strings (`VARCHAR(50)`), avoiding hardcoded assumptions:
- `grades.name`: Can store `"Grade 6"`, `"Year 6"`, `"Kindergarten"`, `"Pre-K"`, or `"Batch 2026"`.
- `classes.section_name`: Can store `"6-A"`, `"Lily"`, `"Year 10-Beta"`, or `"Room 101"`.

### 4.2 Future Phase 2 Enhancements
- Institutional profile configuration (School Name, Crest/Logo, Color Palette tokens).
- Dynamic naming template tokens (`{grade_label}`, `{section_label}`).
- Dedicated School Settings management tab.

---

## 5. Universal System-Wide Audit Trail Specification (All Roles & Compliance)

### 5.1 Audit Data Architecture & Ingestion Discipline
All audit events are permanently stored in the immutable `activity_logs` table. Every mutation method in `{Feature}Actions.php` or `UserModel.php` must call `AuditModel::record(...)` within its atomic transaction boundary.

```sql
-- Core activity_logs schema
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NULL,
    identifier VARCHAR(191) NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_account_id (account_id),
    INDEX idx_created_at (created_at)
);
```

### 5.2 Comprehensive Role-by-Role Action Catalog

#### A. Administrator Events (`c-theme-admin` — Moss Green `#4B5B34`)
| Action Key | Trigger / Context | Details Recorded |
| :--- | :--- | :--- |
| `ACCOUNT_LOCKED` | Automated 5 failed attempts or admin manual lock | Account email, lockout duration, trigger reason |
| `ACCOUNT_UNLOCKED_OTP` | User verified 6-digit OTP code | Email, timestamp, lockout cleared |
| `ACCOUNT_UNLOCKED_LOGIN`| User verified OTP + password and signed in | Email, session created |
| `ADMIN_CLI_UNLOCK` | Break-glass CLI command executed | Admin ID, target account identifier |
| `PASSWORD_RESET_COMPLETED` | Password reset via OTP or Admin direct reset | Account identifier, reset type |
| `USER_CREATED` | New Teacher / Student / Parent added | Full name, role, system ID, email |
| `USER_DEACTIVATED` | User account deactivated | Target account, role, active allocations |
| `TEACHER_HANDOVER_EXECUTED`| Deactivating teacher with reassignments | Source teacher, replacement teacher, class/subject list |
| `GRADE_CREATED` | New grade added (e.g. Grade 12) | Grade ID, name, linked curriculum group |
| `GRADE_DELETED` | Grade deleted | Grade ID, name, cascade-cleaned classes |
| `CLASS_CREATED` | New class added (e.g. 12-B) | Grade ID, section name, student capacity, homeroom teacher |
| `CLASS_UPDATED` | Class renamed or capacity modified | Old section -> new section, capacity, teacher |
| `CLASS_DELETED` | Class section removed | Section name, grade ID, cleared students |
| `CURRICULUM_GROUP_CREATED` | New curriculum stage created | Range label, description, initial subjects |
| `CURRICULUM_GROUP_UPDATED` | Stage edited / subjects removed | Range label, added/removed subjects count |
| `CURRICULUM_GROUP_DELETED` | Empty curriculum stage deleted | Range label, ID |
| `HOMEROOM_TEACHER_ASSIGNED`| Class teacher allocated to class | Section name, teacher name, reassigned-from class |
| `SUBJECT_TEACHER_ASSIGNED` | Subject teacher allocated to class | Section name, subject, teacher name |
| `NOTICE_PUBLISHED` | Admin publishes broadcast notice | Title, target audience (All/Staff/Parents/Students), pinned status |
| `NOTICE_DELETED` | Notice removed from noticeboard | Notice ID, title |
| `DOCUMENT_VERIFIED` | Admissions document review | Student ID, document type, status (Approved/Rejected) |
| `CLUB_CREATED` | New extracurricular club added | Club name, category, initial TIC |
| `CLUB_TIC_ASSIGNED` | TIC reassigned on club card | Club name, new Teacher in Charge |

#### B. Management Events (`c-theme-management` — Maroon `#7F0303`)
| Action Key | Trigger / Context | Details Recorded |
| :--- | :--- | :--- |
| `CERTIFICATE_APPROVED` | Management approves Character Certificate | Student name, admission number, conduct rating |
| `CERTIFICATE_ISSUED` | Final certificate issued and stamped | Certificate ID, student name, reference number |
| `COMPLAINT_RESOLVED` | Management marks complaint resolved | Complaint ID, category, resolution notes |
| `COMPLAINT_ESCALATED` | Complaint escalated for board review | Complaint ID, escalated-to department |
| `EXECUTIVE_NOTICE_POSTED`| Management broadcasts high-level notice | Title, priority, expiration date |
| `CURRICULUM_AUDIT_EXPORT`| Management exports academic compliance report | Export format (PDF/CSV), grade range |

#### C. Teacher Events (`c-theme-teacher` — Sunshine Orange `#EA8913`)
| Action Key | Trigger / Context | Details Recorded |
| :--- | :--- | :--- |
| `TERM_MARKS_ENTERED` | Teacher enters/updates student marks | Subject, class section, term (e.g. Term 1), student count |
| `TERM_MARKS_BATCH_SAVED`| Bulk spreadsheet save for subject grades | Subject, class section, updated row count |
| `CERTIFICATE_REMARKS_EDITED`| Class teacher edits character evaluation | Student ID, evaluation remarks, extracurricular citations |
| `CALENDAR_EVENT_CREATED`| Teacher adds exam/class event | Event title, date, start/end time, target class |
| `CLUB_NOTICE_POSTED` | TIC posts announcement in club noticeboard | Club name, announcement heading |
| `CLUB_SCHEDULE_UPDATED` | TIC updates practice/meeting schedule | Club name, day/time, location |
| `CLUB_ROSTER_UPDATED` | TIC adds/removes squad member | Club name, student name, squad role |
| `JOIN_REQUEST_APPROVED` | TIC accepts student club join request | Club name, student name |
| `JOIN_REQUEST_REJECTED` | TIC rejects student club join request | Club name, student name, rejection reason |
| `ATTENDANCE_RECORDED` | Class teacher submits daily roll call | Class section, present count, absent count |

#### D. Student Events (`c-theme-student` — Sky Blue `#7FC7CC`)
| Action Key | Trigger / Context | Details Recorded |
| :--- | :--- | :--- |
| `ACHIEVEMENT_SUBMITTED` | Student submits award for recognition | Title, category, award level, proof file name |
| `CLUB_JOIN_REQUESTED` | Student requests enrollment in club/sport | Club name, student ID, personal note |
| `CERTIFICATE_REQUESTED` | Student applies for Character Certificate | Student ID, purpose of request, submission date |
| `STUDENT_PASSWORD_RESET`| Student resets password via 6-digit OTP | Student identifier, IP address |

#### E. Parent Events (`c-theme-parent` — Terracotta `#AF5031`)
| Action Key | Trigger / Context | Details Recorded |
| :--- | :--- | :--- |
| `CONSENT_FORM_APPROVED` | Parent approves excursion/trip permission | Child name, trip title, emergency contact confirmed |
| `CONSENT_FORM_DECLINED` | Parent declines permission slip | Child name, trip title, reason |
| `LEAVE_REQUEST_SUBMITTED`| Parent submits student absence request | Child name, dates from-to, medical/personal reason |
| `COMPLAINT_SUBMITTED` | Parent files inquiry/concern to school | Subject, category, student linked |
| `GUARDIAN_CONTACT_UPDATED`| Parent updates phone / address | Parent identifier, updated fields |

#### F. System Events (`c-theme-system` — Mineral Tan `#B48D61`)
| Action Key | Trigger / Context | Details Recorded |
| :--- | :--- | :--- |
| `SESSION_EXPIRED` | Inactivity timeout triggered (900s) | Identifier, role, session age |
| `OTP_DISPATCHED` | System sends 6-digit OTP email | Recipient address, purpose (unlock/reset) |
| `HOURLY_CRON_EXECUTED` | Scheduled background maintenance | Task name, duration, records processed |
| `BACKUP_SNAPSHOT_SAVED` | Automated database snapshot generated | Archive name, file size |

---

### 5.3 UI & Search Engine Enhancements for Audit Module
1. **Dynamic Filter Generation**:
   - `AuditModel::getActivityOptions()` must query `SELECT DISTINCT action FROM activity_logs ORDER BY action ASC` so all new activities (academic, extracurricular, term marks) appear in the dropdown automatically without manual hardcoding.
2. **Date Range Filter**:
   - Add a date range picker (Start Date -> End Date) next to the Activity and Actor dropdowns.
3. **Pagination & Query Limits**:
   - Upgrade from `LIMIT 50`/`100` to a 50-per-page paginated or "Load More" infinite scroll view so historical events (like account lock/unlocks from previous days) are always reachable.
4. **Enhanced Search Indexing**:
   - The client search index (`data-search`) indexes actor name, actor email, action key, details text, IP address, and role for instant instant client-side filtering.

