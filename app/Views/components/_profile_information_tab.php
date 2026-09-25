<?php
/**
 * =========================================================================
 * L'ÉCOLE — PROFILE INFORMATION TAB COMPONENT
 * =========================================================================
 * HTML element IDs in this file MUST exactly match what profile-modal.js
 * injects via setCardVal / setFieldVal / renderTeacherDetails /
 * renderParentDetails / renderManagementDetails.
 *
 * Naming convention used throughout:
 *   Read-only view  →  id="j-card-{rolekey}-{field}"    (set via setCardVal)
 *   View + edit     →  id="j-card-{key}"  +  id="j-input-{key}"  (setFieldVal)
 *   Dropdown        →  id="j-select-{key}"
 *   Datepicker      →  id="j-dp-{key}"
 *
 * Where {key} always matches the first argument to setFieldVal / setCardVal.
 * =========================================================================
 */

$sectionTarget = $renderInformationSection ?? 'all';

if (!function_exists('renderProfileDropdown')) {
    function renderProfileDropdown(string $id, array $opts, string $selected = '', string $label = ''): void {
        $dropdownId    = $id;
        $options       = array_map(fn($o) => ['value' => $o, 'label' => $o], $opts);
        $selectedValue = $selected;
        $dropdownLabel = $label ?: $id;
        $dropdownClass = 'c-dropdown--compact';
        require __DIR__ . '/_dropdown.php';
    }
}

if (!function_exists('renderProfileDatepicker')) {
    function renderProfileDatepicker(string $id, string $inputName, string $selected = '', string $tone = 'sky', string $placeholder = 'Select date'): void {
        $datepickerId  = $id;
        $selectedValue = $selected;
        require __DIR__ . '/_datepicker.php';
    }
}
?>

<?php if ($sectionTarget === 'all' || $sectionTarget === 'student'): ?>
<!-- =======================================================================
     A. STUDENT INFORMATION SUB-TAB PANEL
     JS: renderStudentDetails() → setCardVal / setFieldVal with keys:
         student-index, student-email, student-grade, student-class,
         student-bc, student-dob, student-gender, student-admission,
         student-blood, student-nationality, student-religion,
         student-prevschool, student-address, student-zone,
         student-district, student-province, student-medical
     ======================================================================= -->
<div class="c-subtab-panel j-subtab-panel" id="j-panel-information">

  <!-- 1. Account -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" viewBox="0 0 24 24"><use href="#icon-users"/></svg></span>
      <h3 class="c-section-title">Account</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Reg. Number</p>
        <p class="c-info-card-value" id="j-card-student-index">STU-2026-0142</p>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-mail"/></svg> Student Email</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-email">student@lecole.edu</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-student-email" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 2. Student Information -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-userCheck"/></svg></span>
      <h3 class="c-section-title">Student Information</h3>
    </div>
    <div class="c-info-grid c-cols-4" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Grade</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-grade">Grade 6</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-grade', ['Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11'], 'Grade 6', 'Grade'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Class / Section</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-class">Class 6-A</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-class', ['Class 6-A','Class 6-B','Class 6-C','Class 7-A','Class 7-B','Class 8-A','Class 9-A','Class 10-A','Class 11-A'], 'Class 6-A', 'Class / Section'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Birth Certificate No.</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-bc">2014/COL/00123</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-student-bc" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Date of Birth</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-dob">2014-03-18</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDatepicker('j-dp-student-dob', 'student_dob', '2014-03-18', 'sky', 'Select birth date'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Gender</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-gender">Female</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-gender', ['Female','Male','Prefer not to say'], 'Female', 'Gender'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Admission Date</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-admission">2021-01-04</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDatepicker('j-dp-student-admission', 'student_admission', '2021-01-04', 'sky', 'Select admission date'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Blood Group</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-blood">O+</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-blood', ['A+','A-','B+','B-','AB+','AB-','O+','O-','Not provided'], 'O+', 'Blood Group'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Nationality</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-nationality">Sri Lankan</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-student-nationality" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Religion</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-religion">Buddhism</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-religion', ['Buddhism','Christianity','Hinduism','Islam','Other','Prefer not to say'], 'Buddhism', 'Religion'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Previous School</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-prevschool">Royal College Primary</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-student-prevschool" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 3. Residential Details -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-mapPin"/></svg></span>
      <h3 class="c-section-title">Residential Details</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card" style="grid-column: 1 / -1;">
        <p class="c-info-card-label">Residential Address</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-address">45 Galle Road, Wellawatte, Colombo 06</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-student-address" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Educational Zone</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-zone">Colombo Zone 3</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-zone', ['Colombo Zone 1','Colombo Zone 2','Colombo Zone 3','Sri Jayawardenepura','Piliyandala','Homagama'], 'Colombo Zone 3', 'Educational Zone'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">District</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-district">Colombo</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-district', ['Colombo','Gampaha','Kalutara','Kandy','Galle','Matara','Kurunegala'], 'Colombo', 'District'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Province</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-province">Western</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-student-province', ['Western','Central','Southern','North Western','Sabaragamuwa','Eastern','Northern','Uva','North Central'], 'Western', 'Province'); ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. Medical Notes -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-shieldAlert"/></svg></span>
      <h3 class="c-section-title">Medical Notes</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card" style="grid-column: 1 / -1;">
        <p class="c-info-card-label">Medical Conditions / Notes</p>
        <p class="c-info-card-value j-view-only" id="j-card-student-medical">None recorded.</p>
        <textarea class="c-info-card-input j-edit-only" id="j-input-student-medical" rows="2" style="display: none;"></textarea>
      </div>
    </div>
  </section>

  <?php if (!isset($showGuardian) || $showGuardian): ?>
  <!-- 5. Connected Parent / Guardian Account -->
  <section class="c-tinted-section c-tint-sky">
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" viewBox="0 0 24 24"><use href="#icon-users"/></svg></span>
      <h3 class="c-section-title">Connected parent / guardian account</h3>
    </div>
    <div class="c-guardian-card-wrap" id="j-student-guardian-wrap">
      <div style="min-width: 0;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <div class="c-avatar c-avatar-sm bg-terracotta text-white" id="j-guardian-avatar" style="width: 2rem; height: 2rem; font-size: 10px;">SP</div>
          <div style="min-width: 0;">
            <p style="font-size: 0.875rem; font-weight: 700; color: var(--midnight, #0F414A); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin: 0;" id="j-card-guardian-name">Suresh Perera</p>
            <p class="j-view-only" style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(15, 65, 74, 0.55); margin: 0.15rem 0 0;" id="j-card-guardian-rel">Father · Account connected</p>
            <div class="j-edit-only" style="display: none; margin-top: 0.25rem; min-width: 140px;">
              <?php renderProfileDropdown('j-select-guardian-rel', ['Father','Mother','Legal Guardian','Other'], 'Father', 'Relationship'); ?>
            </div>
          </div>
        </div>
        <div style="margin-top: 0.75rem; display: grid; gap: 0.25rem 1.25rem; font-size: 0.75rem; color: rgba(15, 65, 74, 0.7); grid-template-columns: 1fr 1fr;">
          <span class="c-contact-line"><svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-mail"/></svg><span id="j-card-guardian-email">s.perera@gmail.com</span></span>
          <span class="c-contact-line"><svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-phone"/></svg><span id="j-card-guardian-phone">+94 77 234 5678</span></span>
        </div>
      </div>
      <button type="button" class="c-btn-solid-tone c-tone-terracotta j-jump-parent-profile" id="j-btn-view-parent" data-parent-id="P-045" style="flex-shrink: 0;">
        Open parent account
      </button>
      <span class="c-status-pill c-status-unknown-pill" id="j-guardian-unavailable-pill" style="display: none; width: fit-content;">Account unavailable</span>
    </div>
    <div class="c-guardian-empty" id="j-student-guardian-empty" style="display: none;">
      <p style="font-size: 0.75rem; font-weight: 600; color: rgba(15, 65, 74, 0.7); margin: 0;">No connected parent or guardian account is available for this student.</p>
      <p style="margin-top: 0.25rem; font-size: 11px; line-height: 1.6; color: rgba(15, 65, 74, 0.55); margin-bottom: 0;">A parent account can be linked when guardian enrollment details are confirmed.</p>
    </div>
  </section>
  <?php endif; ?>

</div>
<?php endif; ?>

<?php if ($sectionTarget === 'all' || $sectionTarget === 'roles'): ?>
<!-- =======================================================================
     B. TEACHER PROFILE VIEW / EDIT
     Mirrors original Admin/people/app.js renderReadOnlyProfileSections() +
     renderTeacherProfileSection() + renderEditableProfileSections().
     ======================================================================= -->
<div class="j-profile-role-section j-role-section-teacher" id="j-section-teacher" style="display: none;">

  <!-- 1. Contact & account -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-usersRound"/></svg></span>
      <h3 class="c-section-title">Contact &amp; account</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Reg. Number</p>
        <p class="c-info-card-value" id="j-card-teacher-id">T-001</p>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-mail"/></svg> Institutional mail</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-email">teacher@lecole.edu</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-teacher-email" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-mail"/></svg> Personal mail</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-pemail">Not provided</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-teacher-pemail" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-phone"/></svg> Mobile number</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-phone">077-9988776</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-teacher-phone" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 2. Class teacher responsibility -->
  <!-- 2. Academic Leadership (Class Teacher) -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-bookOpen"/></svg></span>
      <h3 class="c-section-title">Class teacher responsibility</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Class Teacher Role</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-classincharge">Class Teacher — In charge of Class 6-A</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-teacher-classincharge', ['Class 6-A','Class 6-B','Class 7-A','Class 7-B','Class 8-A','Class 8-B','Class 9-A','Class 10-A','Class 11-A','None'], 'Class 6-A', 'Class Teacher (In charge of class)'); ?>
        </div>
      </div>
      <!-- Edit Mode: Subject Specialization -->
      <div class="c-info-card j-edit-only" style="display: none;">
        <p class="c-info-card-label">Subject</p>
        <div style="margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-teacher-subject', ['Mathematics','Science','English','History','ICT','Arts','Commerce','Health & Physical Education'], 'Mathematics', 'Subject'); ?>
        </div>
      </div>
      <!-- Edit Mode: Workload summary -->
      <div class="c-info-card j-edit-only" style="display: none;">
        <p class="c-info-card-label">Workload summary</p>
        <input type="text" class="c-info-card-input" id="j-input-teacher-workload" placeholder="e.g. Total weekly workload: 22 instructional periods." />
      </div>
    </div>
  </section>

  <!-- 2.5 Extracurricular Assignment (Teacher in Charge) -->
  <section style="margin-top: 1.5rem;">
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-trophy"/></svg></span>
      <h3 class="c-section-title">Extracurricular assignment (Teacher in Charge)</h3>
    </div>
    <div class="c-info-grid c-cols-2" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Assigned Programme (TIC)</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-tic">Debating Society</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-teacher-tic', [
            'Varsity Football Club',
            'Digital Arts Collective',
            'Cricket Club',
            'Science Society',
            'Chess Club',
            'Debating Society',
            'Art Circle',
            'Eco Club',
            'Robotics & AI Lab',
            "L'École Philharmonic",
            'Astronomy Society',
            'None'
          ], 'Debating Society', 'TIC programme'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Leadership Designation</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-tic-role">Teacher in Charge &amp; Faculty Mentor</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-teacher-tic-role', ['Teacher in Charge & Faculty Mentor', 'Assistant Coordinator', 'Staff Advisor', 'Head Coordinator', 'None'], 'Teacher in Charge & Faculty Mentor', 'Designation'); ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. Personal & employment -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-mapPin"/></svg></span>
      <h3 class="c-section-title">Personal &amp; employment</h3>
    </div>
    <div class="c-info-grid c-cols-3" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Full name</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-fullname">Mrs. Ishara Gunasekara</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-teacher-fullname" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">NIC</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-nic">198574102938</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-teacher-nic" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Date of birth</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-dob">1985-06-22</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDatepicker('j-dp-teacher-dob', 'teacher_dob', '1985-06-22', 'sunshine', 'Select birth date'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-mail"/></svg> Personal email</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-pemail2">ishara.personal@gmail.com</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-teacher-pemail2" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Experience (years)</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-exp">8 years</p>
        <input type="number" class="c-info-card-input j-edit-only" id="j-input-teacher-exp" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Join date</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-joindate">2018-01-15</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDatepicker('j-dp-teacher-joindate', 'teacher_joindate', '2018-01-15', 'sunshine', 'Select join date'); ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. Emergency contact details -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-phone"/></svg></span>
      <h3 class="c-section-title">Emergency contact details</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Emergency Contact Name</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-emname">Nimal Gunasekara</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-teacher-emname" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Emergency Contact Number</p>
        <p class="c-info-card-value j-view-only" id="j-card-teacher-emphone">+94 77 456 7890</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-teacher-emphone" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 5. Subject teaching assignments (Tinted Sunshine Box) -->
  <section class="c-tinted-section c-tint-sunshine">
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16"><use href="#icon-calendar"/></svg></span>
      <h3 class="c-section-title">Subject teaching assignments</h3>
    </div>
    <!-- View mode: Dynamic assignment pill grid -->
    <div class="j-view-only" style="margin-top: 0.75rem;">
      <div class="c-assignment-grid" id="j-teacher-assignments">
        <!-- Rendered dynamically by profile-modal.js renderTeacherDetails() -->
      </div>
      <p class="c-workload-note" id="j-teacher-workload">Total weekly workload: 22 instructional periods.</p>
    </div>
    <!-- Edit mode: Interactive assignment editor -->
    <div class="j-edit-only" id="j-teacher-assignment-edit-wrap" style="display: none; margin-top: 0.75rem;">
      <div id="j-teacher-assignment-edit-list" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
      <button type="button" class="c-btn-solid-tone c-tone-sunshine j-add-teacher-assignment-btn" id="j-btn-add-teacher-assignment" style="align-self: flex-start; margin-top: 0.5rem;">
        + Add Assignment
      </button>
    </div>
  </section>

  <!-- 6. Qualifications & Experience -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
      <h3 class="c-section-title">Qualifications &amp; Experience</h3>
    </div>
    <!-- View mode: Qualifications list -->
    <div class="j-view-only" id="j-teacher-qualifications" style="margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
      <!-- Rendered dynamically by profile-modal.js renderTeacherDetails() -->
    </div>
    <!-- Edit mode: Qualifications editor -->
    <div class="j-edit-only" id="j-teacher-qual-edit-wrap" style="display: none; margin-top: 0.75rem;">
      <div id="j-teacher-qual-edit-list" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
      <button type="button" class="c-btn-solid-tone c-tone-sunshine j-add-teacher-qual-btn" id="j-btn-add-teacher-qual" style="align-self: flex-start; margin-top: 0.5rem;">
        + Add Qualification
      </button>
    </div>
  </section>

</div>

<!-- =======================================================================
     C. PARENT PROFILE VIEW / EDIT
     Mirrors original Admin/people/app.js renderReadOnlyProfileSections() +
     renderParentProfileSection() + renderEditableProfileSections().
     ======================================================================= -->
<div class="j-profile-role-section j-role-section-parent" id="j-section-parent" style="display: none;">

  <!-- 1. Contact & account -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-usersRound"/></svg></span>
      <h3 class="c-section-title">Contact &amp; account</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Reg. Number</p>
        <p class="c-info-card-value" id="j-card-parent-id">PAR-001</p>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-mail"/></svg> Email</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-email">parent@gmail.com</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-parent-email" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-phone"/></svg> Mobile number</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-phone">071-2233445</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-parent-phone" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-phone"/></svg> Home number</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-sphone">011-2345678</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-parent-sphone" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 2. Personal details -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-usersRound"/></svg></span>
      <h3 class="c-section-title">Personal details</h3>
    </div>
    <div class="c-info-grid c-cols-3" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Full name</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-fullname">Suresh Perera</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-fullname" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Relationship</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-relation">Father</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-parent-relation', ['Father','Mother','Guardian','Other'], 'Father', 'Relationship'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">NIC</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-nic">197829103948</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-nic" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Passport</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-passport">N1298492</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-passport" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Date of birth</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-dob">1978-04-12</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDatepicker('j-dp-parent-dob', 'parent_dob', '1978-04-12', 'terracotta', 'Select birth date'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Emergency name</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-emname">Kumari Perera</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-emname" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Emergency contact</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-emcontact">+94 77 998 8776</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-parent-emcontact" style="display: none;" />
      </div>
      <!-- Edit Mode: Guardian status -->
      <div class="c-info-card j-edit-only" style="display: none;">
        <p class="c-info-card-label">Guardian status</p>
        <div style="margin-top: 0.375rem;">
          <?php renderProfileDropdown('j-select-parent-guardianstatus', ['Living','Deceased','Unknown'], 'Living', 'Guardian status'); ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. Enrollment details -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-userCheck"/></svg></span>
      <h3 class="c-section-title">Enrollment details</h3>
    </div>
    <div class="c-info-grid c-cols-3" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Occupation</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-occupation">Chartered Engineer</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-occupation" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Employer / workplace</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-employer">Civil Engineering Bureau</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-employer" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Office contact number</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-officephone">011-2334455</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-parent-officephone" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Office address</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-officeaddress">Level 4, World Trade Centre, Colombo 01</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-officeaddress" style="display: none;" />
      </div>
      <div class="c-info-card" style="grid-column: 1 / -1;">
        <p class="c-info-card-label">Residential address</p>
        <p class="c-info-card-value j-view-only" id="j-card-parent-address">45 Galle Road, Wellawatte, Colombo 06</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-parent-address" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 4. Linked student accounts (Tinted Terracotta Box) -->
  <section class="c-tinted-section c-tint-terracotta">
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
      <h3 class="c-section-title">Linked student accounts</h3>
    </div>
    <!-- View mode: clickable linked student cards -->
    <div class="j-view-only" style="margin-top: 0.75rem;">
      <div class="c-linked-grid" id="j-parent-linked-students">
        <!-- Rendered dynamically by profile-modal.js renderParentDetails() -->
      </div>
    </div>
    <!-- Edit mode: list editor for linked students -->
    <div class="j-edit-only" id="j-parent-linked-edit-wrap" style="display: none; margin-top: 0.75rem;">
      <div id="j-parent-linked-edit-list" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
      <button type="button" class="c-btn-solid-tone c-tone-terracotta j-add-parent-linked-btn" id="j-btn-add-parent-linked" style="align-self: flex-start; margin-top: 0.5rem;">
        + Add Linked Student
      </button>
    </div>
  </section>

</div>

<!-- =======================================================================
     D. MANAGEMENT PROFILE VIEW / EDIT
     Mirrors original Admin/people/app.js renderReadOnlyProfileSections() +
     renderManagementProfileSection() + renderEditableProfileSections().
     ======================================================================= -->
<div class="j-profile-role-section j-role-section-management" id="j-section-management" style="display: none;">

  <!-- 1. Contact & account -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-usersRound"/></svg></span>
      <h3 class="c-section-title">Contact &amp; account</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Reg. Number</p>
        <p class="c-info-card-value" id="j-card-mgmt-id">M-001</p>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-mail"/></svg> Institutional mail</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-email">management@lecole.com</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-mgmt-email" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-phone"/></svg> Mobile number</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-phone">+94 77 000 0001</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-mgmt-phone" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 2. Employment -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-userCheck"/></svg></span>
      <h3 class="c-section-title">Employment</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Job title</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-title">Principal &amp; Executive Director</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-mgmt-title" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Joining date</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-joining">2018-01-15</p>
        <div class="j-edit-only" style="display: none; margin-top: 0.375rem;">
          <?php renderProfileDatepicker('j-dp-mgmt-joining', 'mgmt_joining', '2018-01-15', 'maroon', 'Select join date'); ?>
        </div>
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Office address</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-office">Main Building · Admissions Desk</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-mgmt-office" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 3. Personal information -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-mapPin"/></svg></span>
      <h3 class="c-section-title">Personal information</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Full name</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-fullname">Dr. Malik Samarasinghe</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-mgmt-fullname" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">NIC</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-nic">197039201948</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-mgmt-nic" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label"><svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-mail"/></svg> Personal email</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-pemail">malik.personal@gmail.com</p>
        <input type="email" class="c-info-card-input j-edit-only" id="j-input-mgmt-pemail" style="display: none;" />
      </div>
      <div class="c-info-card" style="grid-column: 1 / -1;">
        <p class="c-info-card-label">Residential address</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-resaddress">14 Palm Grove, Colombo 07</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-mgmt-resaddress" style="display: none;" />
      </div>
    </div>
  </section>

  <!-- 4. Emergency account -->
  <section>
    <div class="c-section-title-row">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-usersRound"/></svg></span>
      <h3 class="c-section-title">Emergency account</h3>
    </div>
    <div class="c-info-grid" style="margin-top: 0.75rem;">
      <div class="c-info-card">
        <p class="c-info-card-label">Emergency name</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-emname">Emma Thompson</p>
        <input type="text" class="c-info-card-input j-edit-only" id="j-input-mgmt-emname" style="display: none;" />
      </div>
      <div class="c-info-card">
        <p class="c-info-card-label">Emergency contact</p>
        <p class="c-info-card-value j-view-only" id="j-card-mgmt-emergency">+94 77 000 0091</p>
        <input type="tel" class="c-info-card-input j-edit-only" id="j-input-mgmt-emergency" style="display: none;" />
      </div>
    </div>
  </section>

</div>
<?php endif; ?>
