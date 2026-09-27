# L'ÉCOLE PLATFORM — COMPREHENSIVE ARCHITECTURAL STANDARDS, SYSTEM WORKFLOWS & FUTURE ROADMAP (`worked.md`)

> **Permanent Developer Reference, Architectural Blueprint & Future Roadmap**  
> **Repository:** `/Users/sdj-ro/Downloads/GodHelpMEplease/MVC`  
> **Last Comprehensive Audit:** September 2026  
> **Notice:** This document is the ultimate source of truth for the L'École codebase. Before building, refactoring, or extending any feature, you MUST review and adhere to these guidelines. Never violate these rules without explicit user consent.

---

## 1. Core Technology Stack & Strict Zero-Framework Doctrine

| Layer | Standard | Strict Boundary & Constraints |
| :--- | :--- | :--- |
| **Backend Language** | **Vanilla PHP 8.x** | Pure Object-Oriented MVC. **STRICTLY NO frameworks** (NO Laravel, Symfony, CodeIgniter, Slim, CakePHP). |
| **Frontend Markup** | **Vanilla HTML5** | Semantic tags (`<main>`, `<section>`, `<header>`, `<footer>`, `<dialog>`, `<fieldset>`, `<figure>`). |
| **Styling** | **Vanilla CSS3** | Custom design tokens, CSS variables, CSS grid/flexbox. **STRICTLY NO TailwindCSS, Bootstrap, or CSS frameworks.** |
| **Client Scripting** | **Vanilla Modern JavaScript (ES6+)** | Native DOM APIs, `fetch`, native `<dialog>` APIs. **STRICTLY NO React, Vue, Angular, Alpine, jQuery, or npm bundlers.** |
| **External Libraries** | **Zero-Dependency Policy** | **NO external packages or composer libraries** unless explicitly approved by the user. |
| **Approved Library Exception** | **PHPMailer** | Permitted **only** for sending real OTP verification codes, password onboarding emails, and credentials via SMTP. |

---

## 2. Database Architecture: The Two-File Doctrine

The database directory (`database/`) must maintain a strict, unbreakable separation between the database schema and starter data. Never mix table creation with data insertion.

### File 1: `database/schema.sql` (The Blueprint / Structure ONLY)
- **Role:** Empty Architecture / Clean School Deployment Blueprint.
- Contains **only** DDL: `CREATE TABLE`, `ALTER TABLE`, foreign key constraints, column defaults, indexes, and triggers.
- **Rules:**
  - **Zero fake/demo users or seed records.**
  - All tables must explicitly declare `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`.
  - Used when provisioning a brand-new, clean, pristine school installation.

### File 2: `database/seeds.sql` (The Starter Data / Furniture ONLY)
- **Role:** Test & Demo Environment Furnishing.
- Contains **only** DML: `INSERT INTO ... ON DUPLICATE KEY UPDATE`.
- Contains initial starter test accounts for all 5 roles:
  - Admin: `david_silva_admin@lecole.edu` (or `it.admin@lecole.edu`)
  - Management: `sarah_vance@lecole.edu`
  - Teacher: `alex_benjamin@lecole.edu`
  - Student: `STU-2026-0001`
  - Parent: `suresh_perera@gmail.com` (`PAR-2026-0001`)
- Default seed password for all starter accounts: `Password@123` (hashed with `PASSWORD_BCRYPT`).
- **Rules:**
  - Can be re-executed at any time without dropping tables or destroying schema structure.
  - Allows resetting test accounts after testing lockouts, failed logins, or student deactivations in 1 second.

---

## 3. Flat MVC Architecture & Model Separation Strategy

To prevent monolithic model files while maintaining clean consistency across the team's flat MVC structure (avoiding nested `Modules/` folders):

### 3.1 Model Separation Strategy
- **`app/Models/{Feature}Model.php` (Read Engine):**
  - Strictly handles **read queries**, data retrieval, aggregations, formatting, and statistics.
  - Returns clean, normalized data structures for views and APIs.
- **`app/Models/{Feature}Actions.php` (Write Engine):**
  - Strictly handles **state mutations** (Create, Update, Delete, Assign, Unassign, Cascade).

### 3.2 The 6-Step Mutation Discipline
Every mutation method in any `{Feature}Actions.php` or model mutation method must follow this exact sequential shape:
1. **Input Sanitization & Validation:** Strict type checks, regex validation, length boundaries, enum checks.
2. **Business-Rule Verification:** Enrolment caps, uniqueness verification, foreign key dependency checks.
3. **Fail-Fast Early Exit:** If any check fails, immediately throw an `InvalidArgumentException` or return `['success' => false, 'error' => '...']` without opening a database transaction.
4. **Transaction Boundary:** Wrap all database writes in `$db->beginTransaction()`, `$db->commit()`, and `$db->rollBack()`.
5. **Integrated Audit Logging:** Call `AuditModel::record(...)` within the same transaction.
6. **Consistent Return Payload:** Return `['success' => true, 'data' => ...]`.

### 3.3 Core Engine Foundation
- **`core/App.php`:** URL routing and dispatching. Resolves `/controller/method/params` transparently, automatically supporting both camelCase and kebab-case method candidates.
- **`core/Controller.php`:** Base controller providing:
  - CSRF token generation and validation (`generateCsrf()`, `validateCsrf()`).
  - Session and authentication verification (`getUser()`, `requireAuth()`).
  - View rendering (`$this->view(...)`).
  - Standardized JSON responses (`$this->sendJson(...)`).
- **`core/Database.php`:** Singleton PDO MySQL database connection.

### 3.4 Admin & Management Portal Parity
- **The Parity Rule:** School Administrators (`/admin/people`, `/admin/academic`, `/admin/extracurricular`) and School Management (`/management/...`) share the exact same underlying functionality.
- **Shared Controller Traits:** Common domain endpoints reside in self-contained traits (`ParentCrudTrait.php`, `AcademicCrudTrait.php`) included in both `AdminController.php` and `ManagementController.php`.
- **Dynamic URL Base Path:** Client scripts must never hardcode `/admin/` or `/management/`:
  ```javascript
  const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
  ```

---

## 4. Frontend Engineering, Component Reuse & Design Tokens

### 4.1 Component Reuse & Zero Redundancy
- **Component Folder:** `app/Views/components/` (files prefixed with `_component_name.php`).
- **Atomic & Molecular Hierarchy:** Every reusable UI element (buttons, dropdowns, datepickers, modals, cards, table rows) must be written as a reusable atom or molecule.
- **Zero Duplicate Markup:** Never duplicate form structures, table rows, or modal layouts across multiple view pages.
- **Dead Code Policy:** Always remove obsolete, unused, or conflicting legacy code during refactoring.

### 4.2 Class Naming & Separation of Concerns
- **`c-` Prefix for CSS Component Styles:**
  - e.g., `c-btn-solid-sky`, `c-table`, `c-badge-pill`, `c-avatar`, `c-card`, `c-select__trigger`.
- **`j-` Prefix for JavaScript DOM Hooks ONLY:**
  - e.g., `j-btn-add-account`, `j-guardian-mode-radio`, `j-parent-picker-close`.
  - **Rule:** Never apply CSS style rules directly to `j-` classes. JavaScript must target `j-` selectors for event listeners and data binding.

### 4.3 Design Tokens & Role Color System
The platform uses a tailored, cohesive color palette reflecting user roles and platform states:
- **`--midnight` (`#0F414A`):** Deep brand color, primary headers, body text.
- **`--sky` (`#7FC7CC` / `#207C82`):** Student portal tone, informational badges.
- **`--sunshine` (`#EA8913`):** Teacher portal tone, warnings, achievements.
- **`--terracotta` (`#AF5031`):** Parent portal tone, primary admission buttons.
- **`--maroon` (`#7F0303`):** Management portal tone, danger, critical alerts.
- **`--moss` (`#4B5B34`):** Verification, success states, green indicators.
- **`--sand` (`#EFE8DF`) & `--alabaster` (`#FFFFFF`):** Backgrounds, card surfaces, borders.

### 4.4 Icons & SVG Sprite
- All system icons are centralized in the SVG sprite: `app/Views/components/_icon_logos.php`.
- Icon usage: `<svg class="c-icon" width="16" height="16"><use href="#icon-name"/></svg>`.
- Any newly required icon must be added directly into `_icon_logos.php` before use.

### 4.5 Modal Dialog Trigger Uniformity
- Standardized using HTML5 native `<dialog>` elements combined with `dialogs-and-popups.js`.
- Never use ad-hoc inline styles or fragmented `classList.add('c-is-open')` for modal triggers.
- Always provide uniform backdrop handling (`::backdrop`), body scroll locking, and Escape key listeners.

---

## 5. Domain Rules & System Workflows

### 5.1 Student Admission, Guardian Co-Creation & Sibling Linking
1. **Single Legal Guardian Rule:** Every enrolled student must have exactly 1 primary legal guardian in `student_parents`.
2. **Guardian Modes:**
   - **Mode A: "Use Existing Parent (Sibling)"**:
     - Searches active registered parents via live search or `#parent-picker`.
     - Displays parent preview card (name, code, contact details, currently enrolled siblings).
     - **Address Synchronization:** Checkbox `[x] Use parent's residential address for this student` auto-populates `homeAddress`.
   - **Mode B: "Create New Guardian"**:
     - Captures relationship (`Father`, `Mother`, `Guardian`), names, occupation, contact details.
     - **White-Label Identity:** Dual toggle between:
       - 🇱🇰 **Sri Lankan NIC:** Formatted for 9 digits + V/X or modern 12 digits.
       - 🌐 **Foreign Passport:** Formatted for 6–20 alphanumeric characters (supporting international families without forcing dummy NICs).
     - **Date of Birth Calendar Lock & Override:**
       - Student DOB locked to 3–19 years by default.
       - Parent DOB locked to 18–80 years by default.
       - Dedicated `[ 🔓 Unlock range / Override ]` button allows expanding to full calendar past dates for repeating students or non-standard entries.
     - **Phone Country Code Selector:**
       - Dropdown with international dialing codes (default `🇱🇰 +94`, plus UK, US, AU, UAE, SG, IN, etc.) paired with number input, normalized to E.164 (`+947XXXXXXXX`).
3. **First-Time Parent Account Generation (No Admin Passwords):**
   - Administrators do **not** enter passwords for parents.
   - The parent user account is created with `activation_status = 'PENDING'` and `password_hash = NULL`.
   - On admission, an activation token/OTP is emailed to the parent's personal email via PHPMailer.
   - The parent completes onboarding, verifies their email, and sets their own secret password.

### 5.2 Sibling-Guard Parent Deactivation Rules
1. **Manual Deactivation from Parents Directory:**
   - If an administrator attempts to deactivate a parent who is linked to any student with `activation_status IN ('ACTIVE', 'PENDING')`, the system **blocks** the request with HTTP `409 Conflict`.
   - Error notice lists all active enrolled siblings: *"Cannot deactivate parent: This parent is the active guardian for enrolled student(s): [Name 1], [Name 2]. Reassign guardian or withdraw students first."*
2. **Automatic Student Withdrawal Cascade:**
   - When a student is withdrawn or deactivated, `ParentModel::checkParentAfterStudentDeactivation()` counts remaining active siblings.
   - **If active siblings remain:** Parent account remains `ACTIVE`.
   - **If last remaining child is deactivated:** Parent account automatically cascades to `activation_status = 'INACTIVE'`.

### 5.3 Faculty Directory Single Source of Truth & 3-Tier Teacher Workload
1. **Single Source of Truth:** Faculty records in `seed_faculty.php` and `teachers` table are synced across Academics and Extracurriculars.
2. **3-Tier Workload Card (`_grade_teacher_hover.php`):**
   Hovering over any teacher assignment dropdown displays their complete live workload:
   - Tier 1: Subject Teacher assignments.
   - Tier 2: Class Teacher assignments (enforcing that a teacher cannot be class teacher for multiple classes).
   - Tier 3: Extracurricular TIC assignments.
3. **Teacher Deactivation Handover Workflow:**
   When a teacher is deactivated, the UI opens the **Handover Modal**, allowing reassigning their classes, subjects, and clubs, or choosing "Leave Unassigned" (which triggers an alert banner on the Academic dashboard).

### 5.4 Academic Module Lifecycle & Guards
1. **Grade Deletion Guard (The Enrolment Guard):**
   - A Grade record can **never** be deleted while enrolled students exist (`COUNT(students) > 0`).
   - Block message: *"Cannot delete [Grade Name]. There are currently [N] students enrolled. Reassign or graduate students before removing this grade."*
   - Allowed only when student count is `0`.
2. **Student Capacity vs Enrolment Headcount:**
   - `classes.student_count` = Administrative Planning Capacity (Min: 15, Max: 40).
   - `COUNT(*) FROM students` = Live Enrolment Headcount.
   - Statuses: Open (`enrolled < capacity`), At Capacity (`enrolled == capacity`), Hard Full (`enrolled >= 40`).
3. **Cohort Progression vs Shuffling:**
   - Cohort continuity: `6-A` rolls over directly to `7-A` at year-end.
   - No automated random shuffling of students.
4. **Curriculum Overlap & Deadlock Prevention:**
   - 1 Grade = Exactly 1 Curriculum Stage.
   - Auto-Transfer on stage expansion.
   - If a curriculum stage shrinks, detached grades receive `group_id = NULL` with a polite dashboard badge.
   - A curriculum stage must retain at least 1 subject and 1 grade.
5. **Teacher Assignment Constraints:**
   - 1:1 Homeroom exclusivity.
   - 5-subject workload cap across class sections.

### 5.5 Extra-Curricular Activities & Sports
1. **Activity Types:** Sports, Clubs, Societies.
2. **Assignments:** Teacher-in-Charge (TIC) and optional Coach.
3. **Card Interactivity:** Dropdown to edit TIC directly on the extracurricular card with live workload checks.
4. **Roster Management:** Squad member tracking, student join requests, and approval workflow.

### 5.6 Global Inactivation Policy (Zero Hard Deletes)
- User records (`teachers`, `students`, `parents`, `user_accounts`) are **never hard-deleted** from MySQL.
- Status is toggled to `'inactive'` / `'deactivated'`, disabling portal login while retaining full historical records, receipts, marks, and audit logs.
- **"Last Admin Standing" Guard:** The system strictly forbids deactivating or revoking the admin role from the final active Administrator (`COUNT(*) WHERE role = 'Admin' AND status = 'active'` must remain $\ge 1$).

### 5.7 Character Certificate Generation & Workflow
1. Student submits a certificate request specifying the purpose.
2. Class Teacher reviews conduct history, edits academic & extracurricular remarks.
3. Management approves, seals, and issues the official A4 stamped certificate with verifiable serial number.

---

## 6. System-Wide Immutable Audit Trail Catalog

All events are recorded in `activity_logs` via `AuditModel::record(...)`.

| Role | Theme Color | Key Audit Actions |
| :--- | :--- | :--- |
| **Admin** | Moss Green (`#4B5B34`) | `ACCOUNT_LOCKED`, `ACCOUNT_UNLOCKED_OTP`, `PASSWORD_RESET_COMPLETED`, `STUDENT_REGISTERED`, `STUDENT_ADMITTED`, `GUARDIAN_CHANGED`, `PARENT_PROFILE_UPDATED`, `PARENT_DEACTIVATED`, `PARENT_AUTO_DEACTIVATED`, `USER_CREATED`, `USER_DEACTIVATED`, `GRADE_CREATED`, `GRADE_DELETED`, `CLASS_CREATED`, `CLASS_DELETED`, `CURRICULUM_GROUP_CREATED`, `NOTICE_PUBLISHED` |
| **Management** | Maroon (`#7F0303`) | `CERTIFICATE_APPROVED`, `CERTIFICATE_ISSUED`, `COMPLAINT_RESOLVED`, `EXECUTIVE_NOTICE_POSTED`, `CURRICULUM_AUDIT_EXPORT` |
| **Teacher** | Sunshine Orange (`#EA8913`) | `TERM_MARKS_ENTERED`, `TERM_MARKS_BATCH_SAVED`, `CERTIFICATE_REMARKS_EDITED`, `CALENDAR_EVENT_CREATED`, `CLUB_NOTICE_POSTED`, `CLUB_SCHEDULE_UPDATED`, `JOIN_REQUEST_APPROVED`, `ATTENDANCE_RECORDED` |
| **Student** | Sky Blue (`#7FC7CC`) | `ACHIEVEMENT_SUBMITTED`, `CLUB_JOIN_REQUESTED`, `CERTIFICATE_REQUESTED`, `STUDENT_PASSWORD_RESET` |
| **Parent** | Terracotta (`#AF5031`) | `CONSENT_FORM_APPROVED`, `LEAVE_REQUEST_SUBMITTED`, `COMPLAINT_SUBMITTED`, `GUARDIAN_CONTACT_UPDATED` |
| **System** | Mineral Tan (`#B48D61`) | `SESSION_EXPIRED`, `OTP_DISPATCHED`, `HOURLY_CRON_EXECUTED`, `BACKUP_SNAPSHOT_SAVED` |

---

## 7. Email Configuration & Real-World SMTP Testing Cover

- **Frontend Masking:** The UI displays official school identities (`@lecole.edu` / `@lecole.lk`), while parent personal emails (`@gmail.com`, etc.) are kept private.
- **Testing Cover Strategy:**
  - For local development and demonstration, outgoing emails use real Gmail SMTP credentials via PHPMailer to route emails to designated test inboxes.
  - Role test inboxes receive OTPs and activation links without exposing private testing infrastructure in the frontend.

---

## 8. Development Discipline & Git Workflow

1. **Part-by-Part Approval:** Always discuss and build complex features part-by-part with user confirmation.
2. **Explicit Git Instructions:** **NEVER** run `git add`, `git commit`, or `git push` unless the user explicitly commands it.
3. **Untracked Sensitive Files:** Maintain strict untracked status for working roadmap files such as `CROSS_MODULE_SPECS_AND_ROADMAP.md`.
4. **Pre-Commit Verification:** Always test execution in Docker (`mvc-php-1`, `mvc-web-1`, `mvc-db-1`) and run `php -l` linting across all modified files before declaring work complete.

---

## 9. Future Tasks, Planned Improvements & Technical Debt Roadmap

### 9.1 Extracurricular Persistence (DB vs In-Memory Hybrid)
- **Current State:** 
  - Relational schema `extracurricular_activities` created and active in MySQL:
    - Primary Key: `activity_id VARCHAR(50)` (e.g. `'CHESS'`, `'ROBOTICS'`, `'CHOIR'`).
    - Foreign Key: `teacher_id INT NULL` referencing `teachers(id)` (Teacher-In-Charge / TIC).
    - Columns: `activity_name`, `category`, `description`, `venue`, `meeting_schedule`, `created_at`, `updated_at`.
- **Target:** Full CRUD interface for activities, member rosters, and attendance:
  - Tables: `extracurricular_members`, `extracurricular_schedules`, `extracurricular_events`.
  - Wire TIC assignment directly into `extracurricular_activities.teacher_id` with foreign key integrity.

### 9.2 Universal Account Lifecycle & Verification Doctrine (All 4 Roles)
- **Applicability:** Applies universally to Students, Parents, Teachers, and Management Staff.
- **Workflow:**
  1. **Profile Creation:** Account is inserted by Admin or Management into `user_accounts` with `activation_status = 'PENDING'`.
  2. **Notification Dispatch:** An automated welcome notice/email is triggered containing instructions and verification details.
  3. **First-Time Sign Up / Activation:**
     - The user visits the portal and clicks "Sign Up / First-Time Login".
     - User enters their identifier (Index No / Staff ID / Email / Phone).
     - User inputs the secure 6-digit verification code (OTP).
     - User sets their private password.
     - Upon confirmation, account status transitions from `'PENDING'` to `'ACTIVE'`.
  4. **Frictionless Local Testing:** For local development and demonstration, verification codes are rendered in demo notices/consoles so external SMTP failure never blocks onboarding tests.

### 9.3 SqlJsMapper Architecture Doctrine
- **Location:** `app/Models/SqlJsMapper.php`.
- **Doctrine:**
  - Database columns are strictly SQL `snake_case` (`emergency_phone`, `blood_group`, `admission_date`, `guardian_id`).
  - Frontend JavaScript receives strictly `camelCase` (`emergencyContact`, `bloodGroup`, `admissionDate`, `parentId`).
  - `SqlJsMapper` acts as the single source of truth for entity transformations, eliminating ad-hoc fallback checks across views and scripts.

### 9.4 White-Labeling Phase 2 (School Settings & Customization)
- Dedicated "School Settings" administration tab.
- Configurable school name, crest/logo, motto, academic year dates.
- Dynamic naming tokens (`{grade_label}`, `{section_label}`) for international school nomenclature (e.g., "Year" vs "Grade", "Form" vs "Class").

### 9.5 Audit Module UI Enhancements
- **Dynamic Filter Dropdown:** Replace hardcoded audit filter options with `SELECT DISTINCT action FROM activity_logs ORDER BY action ASC`.
- **Date Range Picker:** Add dual-date range filter (Start Date -> End Date) next to Activity and Actor dropdowns.
- **Pagination & Query Limits:** Upgrade from `LIMIT 50`/`100` to a 50-per-page paginated view or infinite scroll so historical events are always accessible.

### 9.6 Character Certificate Verification Portal
- Public verification endpoint (`/verify-certificate?id=...`) with QR code validation for universities and employers.

### 9.7 Automated Sibling Enrolment Headcount Alerts
- Dashboard notifications when class sections reach 38/40 students, reserving priority seats for incoming siblings.

---

## 10. Completed Production CRUDs & Unified Architecture Status

### 10.1 Parent CRUD (100% Production Ready)
- **Create**: Co-creation during student admission (Mode B: Sri Lankan NIC / Foreign Passport, auto-generated PENDING user account, OTP dispatch). Sibling linking mode (Mode A: `#parent-picker`).
- **Read**: People Directory table, search, role filters, view mode in `_person_profile_modal.php`.
- **Update**: Modal edit mode with backend persistence (`updateParentProfile`), address synchronization, emergency contacts, phone normalization.
- **Delete / Inactivation**: Sibling dependency guard blocking deactivation if enrolled children remain (`_deactivate_parent_dialog.php` with 409 Conflict). Auto-cascading when last student is withdrawn.

### 10.2 Student CRUD & Lifecycle (100% Production Ready)
- **Create**: Student Admission multi-step form (`_add_person_page.php` with role `student`), birth certificate tracking, blood group, medical notes, class capacity allocation (15–40 check).
- **Read**: People Directory (`admin/people.php` & `management/people.php`), full modal tabs (Information, Academics, Extracurriculars, Achievements).
- **Update**: Modal edit mode with backend persistence (`updateStudentProfile`), updating `students` and `user_accounts` in MySQL with `STUDENT_PROFILE_UPDATED` audit record.
- **Status Toggle**: Active / Inactive dropdown directly in People Directory rows and modal footer with atomic transaction, audit logging (`USER_ACTIVATED`, `USER_DEACTIVATED`), and automatic parent cascade check.

### 10.3 Master Unified Academic Section (`_academic_section.php`) (100% Production Ready)
- Unified 2-column layout extracted into `app/Views/components/_academic_section.php`:
  - **Left**: Digital Record Book (`_digital_record_book.php`) with Grade (6–11) and Term (1–3) selectors, subject marks, teacher feedback.
  - **Right**: Scaled Total Marks Metric Card (`total-marks-card`) + Multi-grade Performance Trend Carousel (`trend-panel`) with interactive SVG clustered bar charts and tooltips.
- Seamlessly reused across:
  1. `_person_profile_modal.php` (Academics subtab `#j-panel-academics`)
  2. `student/academic.php` (Student Academic Records portal)
  3. `parent/child-profile.php` (Parent Child Profile portal)

### 10.4 Grade, Class & Curriculum Stage CRUD (100% Production Ready)
- **Grades**: Create grade modal, grade cards with live student enrollment vs capacity indicators. Enrolment Guard blocking deletion if `students > 0`.
- **Classes**: Class creation, class teacher 1:1 homeroom exclusivity, subject teacher assignments, capacity limits (15–40).
- **Curriculum Stages**: Multi-grade stage groupings, auto-transfer on expansion, orphaned grade warning badge when stage shrinks.

### 10.5 Teacher CRUD & Lifecycle (100% Production Ready)
- **Create**: Teacher registration form (`_add_person_page.php` with role `teacher`), sequential ID generation (`TEA-2026-XXXX`), institutional email provisioning (`first_last@lecole.edu`), Sri Lankan NIC validation (12-digit or 9+V/X), age bounds check (21–65 years), anti-self-reference for emergency contacts, and optional qualifications (0 or more rows supported).
- **Read**: People Directory Teachers table (`#j-table-teacher`), full view mode in `_person_profile_modal.php` (Contact, Class Responsibility, Extracurricular TIC, Personal & Employment, Emergency Contact, Subject Assignments, Qualifications & Experience).
- **Update**: Modal edit mode with backend persistence (`updateTeacherProfile`), updating `teachers`, `teacher_qualifications`, and `user_accounts` in MySQL with `TEACHER_PROFILE_UPDATED` audit record.
- **Status Toggle**: Active / Inactive dropdown directly in People Directory rows and modal footer with atomic transaction and audit logging (`USER_ACTIVATED`, `USER_DEACTIVATED`).

### 10.6 Management Panel Staff CRUD (100% Production Ready)
- **Create**: Management registration form (`_add_person_page.php` with role `management`), admin-only privilege guard, sequential ID generation (`MAN-2026-XXXX`), institutional email provisioning (`first_last@lecole.edu`), Sri Lankan NIC validation, anti-self-reference checks for emergency contacts.
- **Read**: People Directory Management table (`#j-table-management`), full view mode in `_person_profile_modal.php` (Contact & Account, Employment & Title, Personal Information, Emergency Contact).
- **Update**: Modal edit mode with backend persistence (`updateManagementProfile`), updating `management_profiles` and `user_accounts` in MySQL with `MANAGEMENT_PROFILE_UPDATED` audit record.
- **Status Toggle**: Active / Inactive dropdown directly in People Directory rows and modal footer with atomic transaction and audit logging.

---

## 11. Committed & Pushed File Register / Changelog

> **Audit Trail of Production Commits to Branch `develop`**  
> **Committer:** Sandali (`masdjayarathne@gmail.com`)  
> **Date:** September 27, 2026  

| Batch | Commit Hash | Scope & Message | Files Included in Commit |
| :--- | :--- | :--- | :--- |
| **Batch 1** | `1a05fa2` | `feat(db): update core schemas, calendar tables, and seed data` | • `database/schema.sql`<br>• `database/seeds.sql`<br>• `database/academic_schema.sql`<br>• `database/calendar_schema.sql`<br>• `database/seed_academic.php`<br>• `database/README.md`<br>• `database/migrations/001_link_students_to_classes.sql`<br>• `database/migrations/002_enforce_teacher_fk_in_class_teachers.sql` |
| **Batch 2** | `e195cab` | `feat(core): enhance routing, mail service, sql-js mapping, and audit logging` | • `core/App.php`<br>• `core/Controller.php`<br>• `core/MailService.php`<br>• `console.php`<br>• `app/Models/SqlJsMapper.php`<br>• `app/Models/AuditModel.php`<br>• `app/Models/ProfileModel.php` |
| **Batch 3** | `a00d04f` | `feat(auth): validate credentials against DB, add alert banners and role templates` | • `app/Models/UserModel.php`<br>• `app/Models/UserActions.php`<br>• `app/Controllers/AuthController.php`<br>• `public/assets/css/auth-shared.css`<br>• `public/assets/js/auth-shared.js`<br>• `app/Views/sign-in-up_page/access.php`<br>• `app/Views/sign-in-up_page/sign-in-template.php`<br>• `app/Views/sign-in-up_page/sign-up-template.php` |
| **Batch 4** | `06e6f5c` | `feat(people): implement student, parent, teacher, and management CRUD models and trait` | • `app/Controllers/PeopleCrudTrait.php`<br>• `app/Models/PeopleModel.php`<br>• `app/Models/StudentModel.php`<br>• `app/Models/StudentActions.php`<br>• `app/Models/ParentModel.php`<br>• `app/Models/ParentActions.php`<br>• `app/Models/TeacherActions.php`<br>• `app/Models/ManagementActions.php` |
| **Batch 5** | `1b2274d` | `feat(people-ui): add profile modals, admission components, and directory panels` | • `app/Views/components/_add_person_page.php`<br>• `app/Views/components/_admission_guardian.php`<br>• `app/Views/components/_deactivate_parent_dialog.php`<br>• `app/Views/components/_parent_picker.php`<br>• `app/Views/components/_people_directory_panel.php`<br>• `app/Views/components/_person_profile_modal.php`<br>• `app/Views/components/_profile_information_tab.php`<br>• `app/Views/admin/people.php`<br>• `app/Views/management/people.php`<br>• `app/Views/parent/child-profile.php`<br>• `app/Views/teacher/students.php`<br>• `public/assets/css/components/people-directory.css`<br>• `public/assets/css/components/profile-modal.css`<br>• `public/assets/js/components/people-directory.js`<br>• `public/assets/js/components/profile-modal.js`<br>• `public/assets/js/components/profile-page.js`<br>• `public/assets/js/components/parent-deactivation.js`<br>• `public/assets/js/components/parent-picker.js` |
| **Batch 6** | `b06bebb` | `feat(academic): unify academic section, digital record book, and grade management` | • `app/Controllers/AcademicCrudTrait.php`<br>• `app/Models/AcademicModel.php`<br>• `app/Models/AcademicActions.php`<br>• `app/Views/components/_academic_section.php`<br>• `app/Views/admin/academic.php`<br>• `app/Views/management/academic.php`<br>• `app/Views/student/academic.php`<br>• `public/assets/js/components/grade-card.js`<br>• `public/assets/js/components/student-academic.js` |
| **Batch 7** | `9f02cb7` | `feat(calendar): implement role-scoped calendar events and datepicker component` | • `app/Controllers/CalendarEventCrudTrait.php`<br>• `app/Models/CalendarEventModel.php`<br>• `app/Models/CalendarEventActions.php`<br>• `app/Views/components/_calendar.php`<br>• `app/Views/components/_datepicker.php`<br>• `public/assets/css/components/calendar.css`<br>• `public/assets/css/components/datepicker.css`<br>• `public/assets/js/components/calendar.js`<br>• `public/assets/js/components/datepicker.js` |
| **Batch 8** | `71d75b1` | `feat(notices): add role-targeted notice board, CRUD traits, and filters` | • `app/Controllers/NoticeCrudTrait.php`<br>• `app/Models/NoticeModel.php`<br>• `app/Models/NoticeActions.php`<br>• `app/Views/components/_add_notice_page.php`<br>• `app/Views/components/_notice_card.php`<br>• `app/Views/admin/notice.php`<br>• `app/Views/management/notice.php`<br>• `app/Views/teacher/notice.php`<br>• `public/assets/css/components/notice-board.css`<br>• `public/assets/css/components/notice-card.css`<br>• `public/assets/js/components/add-notice-page.js`<br>• `public/assets/js/components/notice-card.js`<br>• `public/assets/js/components/notice-filter.js` |
| **Batch 9** | `4256c63` | `feat(extracurricular): add clubs, sports rosters, and extracurricular panels` | • `app/Models/ExtracurricularModel.php`<br>• `app/Views/components/_extracurricular_card.php`<br>• `app/Views/components/_extracurricular_card_header.php`<br>• `app/Views/components/_extracurricular_noticeboard.php`<br>• `app/Views/components/_extracurricular_scheduleandevents_details.php`<br>• `app/Views/components/_extracurricular_teams_panel.php`<br>• `app/Views/admin/extracurricular.php`<br>• `app/Views/management/extracurricular.php`<br>• `app/Views/student/extracurricular.php`<br>• `app/Views/teacher/extracurricular.php`<br>• `public/assets/js/components/extracurricular-card.js` |
| **Batch 10** | `ee36e62` | `feat(ui): update role controllers, navigation sidebar, landing page, and UI widgets` | • `app/Controllers/AdminController.php`<br>• `app/Controllers/ManagementController.php`<br>• `app/Controllers/TeacherController.php`<br>• `app/Controllers/StudentController.php`<br>• `app/Controllers/ParentController.php`<br>• `app/Views/components/_sidebar.php`<br>• `app/Views/components/_achievements_timeline.php`<br>• `app/Views/landing_page/landing.php`<br>• `app/Views/landing_page/achievements.php`<br>• `app/Views/errors/404.php`<br>• `public/assets/css/global.css`<br>• `public/assets/css/landing.css`<br>• `public/assets/css/components/form-card.css`<br>• `public/assets/css/components/sidebar.css`<br>• `public/assets/js/components/dialogs-and-popups.js`<br>• `public/assets/js/components/dropdown.js`<br>• `public/assets/js/components/feedback-banners.js`<br>• `public/assets/images/logo.png`<br>• `public/assets/images/lecole-crest.jpg` |
| **Batch 11** | `ba09838` | `docs: update documentation and committed file register in worked.md` | • `README.md`<br>• `worked.md`<br>• `CROSS_MODULE_SPECS_AND_ROADMAP.md` |
| **Batch 12** | `8053b27` | `fix(db): close extracurricular_activities table definition in schema.sql` | • `database/schema.sql`<br>• `worked.md` |



