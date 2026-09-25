<?php
/**
 * =========================================================================
 * L'ÉCOLE — ADD PERSON / USER ACCOUNT FORM COMPONENT
 * =========================================================================
 * Reusable full-page form views for adding Students (Enrollment), Teachers,
 * Parents, or Management Panel staff.
 *
 * Direct 1:1 extraction from original L'École People Directory forms.
 *
 * Variables:
 *   - $role       : 'student' | 'teacher' | 'parent' | 'management'
 *   - $formAction : string (optional target URL)
 * =========================================================================
 */

$role = $role ?? 'student';

// Role configurations
$configMap = [
    'student' => [
        'pageId'       => 'j-page-add-student',
        'formId'       => 'j-enrollment-form',
        'headerTheme'  => 'sky',
        'title'        => 'Add Student Account',
        'draftText'    => 'Draft',
        'btnColor'     => 'sky',
        'submitLabel'  => 'Enroll student',
        'requiredNote' => 'Fields marked * are required to enroll a student.'
    ],
    'teacher' => [
        'pageId'       => 'j-page-add-teacher',
        'formId'       => 'j-add-teacher-form',
        'headerTheme'  => 'sunshine',
        'title'        => 'Add Teacher Account',
        'draftText'    => '',
        'btnColor'     => 'sunshine',
        'submitLabel'  => 'Save Account',
        'requiredNote' => 'Fields marked * are required to add a teacher.'
    ],
    'parent' => [
        'pageId'       => 'j-page-add-parent',
        'formId'       => 'j-add-parent-form',
        'headerTheme'  => 'terracotta',
        'title'        => 'Add Parent / Guardian Account',
        'draftText'    => '',
        'btnColor'     => 'terracotta',
        'submitLabel'  => 'Save Account',
        'requiredNote' => 'Fields marked * are required to add a parent.'
    ],
    'management' => [
        'pageId'       => 'j-page-add-management',
        'formId'       => 'j-add-management-form',
        'headerTheme'  => 'maroon',
        'title'        => 'Add Management Panel Account',
        'draftText'    => '',
        'btnColor'     => 'maroon',
        'submitLabel'  => 'Save Account',
        'requiredNote' => 'Fields marked * are required to add staff.'
    ]
];

$cfg = $configMap[$role] ?? $configMap['student'];

// Dropdown options
$genderOptions = [
    ['value' => 'Female', 'label' => 'Female'],
    ['value' => 'Male', 'label' => 'Male'],
    ['value' => 'Prefer not to say', 'label' => 'Prefer not to say']
];

$gradeOptions = [
    ['value' => 'Grade 6', 'label' => 'Grade 6'],
    ['value' => 'Grade 7', 'label' => 'Grade 7'],
    ['value' => 'Grade 8', 'label' => 'Grade 8'],
    ['value' => 'Grade 9', 'label' => 'Grade 9'],
    ['value' => 'Grade 10', 'label' => 'Grade 10'],
    ['value' => 'Grade 11', 'label' => 'Grade 11']
];

$initialClassOptions = [
    ['value' => '6-A', 'label' => '6-A'],
    ['value' => '6-B', 'label' => '6-B'],
    ['value' => '6-C', 'label' => '6-C'],
    ['value' => '6-D', 'label' => '6-D']
];

$religionOptions = [
    ['value' => 'Prefer not to say', 'label' => 'Prefer not to say'],
    ['value' => 'Buddhism', 'label' => 'Buddhism'],
    ['value' => 'Catholic', 'label' => 'Catholic'],
    ['value' => 'Hinduism', 'label' => 'Hinduism'],
    ['value' => 'Islam', 'label' => 'Islam'],
    ['value' => 'Other', 'label' => 'Other']
];

$bloodOptions = [
    ['value' => 'Not provided', 'label' => 'Not provided'],
    ['value' => 'A+', 'label' => 'A+'],
    ['value' => 'A-', 'label' => 'A-'],
    ['value' => 'B+', 'label' => 'B+'],
    ['value' => 'B-', 'label' => 'B-'],
    ['value' => 'AB+', 'label' => 'AB+'],
    ['value' => 'AB-', 'label' => 'AB-'],
    ['value' => 'O+', 'label' => 'O+'],
    ['value' => 'O-', 'label' => 'O-']
];

$zoneOptions = [
    ['value' => 'Colombo', 'label' => 'Colombo'],
    ['value' => 'Kandy', 'label' => 'Kandy'],
    ['value' => 'Galle', 'label' => 'Galle'],
    ['value' => 'Gampaha', 'label' => 'Gampaha'],
    ['value' => 'Kurunegala', 'label' => 'Kurunegala'],
    ['value' => 'Jaffna', 'label' => 'Jaffna'],
    ['value' => 'Matara', 'label' => 'Matara']
];

$districtOptions = [
    ['value' => 'Colombo', 'label' => 'Colombo'],
    ['value' => 'Gampaha', 'label' => 'Gampaha'],
    ['value' => 'Kalutara', 'label' => 'Kalutara'],
    ['value' => 'Kandy', 'label' => 'Kandy'],
    ['value' => 'Galle', 'label' => 'Galle'],
    ['value' => 'Matara', 'label' => 'Matara'],
    ['value' => 'Kurunegala', 'label' => 'Kurunegala'],
    ['value' => 'Jaffna', 'label' => 'Jaffna']
];

$provinceOptions = [
    ['value' => 'Western', 'label' => 'Western Province'],
    ['value' => 'Central', 'label' => 'Central Province'],
    ['value' => 'Southern', 'label' => 'Southern Province'],
    ['value' => 'North Western', 'label' => 'North Western Province'],
    ['value' => 'Northern', 'label' => 'Northern Province'],
    ['value' => 'Eastern', 'label' => 'Eastern Province'],
    ['value' => 'Sabaragamuwa', 'label' => 'Sabaragamuwa Province'],
    ['value' => 'Uva', 'label' => 'Uva Province'],
    ['value' => 'North Central', 'label' => 'North Central Province']
];

$parentRelationOptions = [
    ['value' => 'Father', 'label' => 'Father'],
    ['value' => 'Mother', 'label' => 'Mother'],
    ['value' => 'Guardian', 'label' => 'Guardian']
];
?>

<section id="<?= htmlspecialchars($cfg['pageId']) ?>" class="c-add-person-section j-page-add-person" style="display: none;">
  <div class="c-form-page">
    
    <!-- Top Back Link: Uniform Solid Moss Green with exact original arrow SVG -->
    <button type="button" class="c-form-back j-nav-back-directory">
      <svg class="c-icon" width="16" height="16"><use href="#icon-arrowLeft"/></svg>
      <span>Back</span>
    </button>

    <form class="c-form-card c-form-card--<?= htmlspecialchars($cfg['headerTheme']) ?>" id="<?= htmlspecialchars($cfg['formId']) ?>" action="<?= htmlspecialchars($formAction ?? '') ?>" method="POST" enctype="multipart/form-data" novalidate>
      <?= $this->csrfField() ?>
      
      <!-- Role-Themed Header matching 1:1 original -->
      <header class="c-form-header c-header-<?= htmlspecialchars($cfg['headerTheme']) ?>">
        <div class="c-form-header-row">
          <h1 class="c-form-header-title c-font-display"><?= htmlspecialchars($cfg['title']) ?></h1>
          <?php if (!empty($cfg['draftText'])): ?>
            <span id="j-enrollment-draft-pill" class="c-draft-pill"><?= htmlspecialchars($cfg['draftText']) ?></span>
          <?php endif; ?>
        </div>
      </header>

      <div class="c-form-body">
        <!-- Live Form Notice / Error Banner -->
        <div class="c-form-notice j-form-notice" style="display: none; margin-bottom: 1.25rem; padding: 0.75rem 1rem; border-radius: var(--radius-lg, 0.5rem); font-size: 0.8125rem; font-weight: 600;"></div>

        <?php if ($role === 'student'): ?>
          <!-- ===============================================================
               15. ENROLLMENT FORM (STUDENT ACCOUNT)
               Mirrors exact fields & styling from Admin/people/app.js lines 2370-2393
               =============================================================== -->
          <div class="c-form-section-head">
            <h2 class="c-form-section-title">Student personal data</h2>
            <p class="c-form-section-desc">Enter these details once; the student index number is generated when registration is saved.</p>
          </div>

          <div class="c-form-grid">
            <!-- 1. Full Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Full Name <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input j-autofill-full" name="fullName" placeholder="e.g. Malsha Anjali Jayarathne" required />
            </div>

            <!-- 2. First Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input j-autofill-first" name="firstName" placeholder="e.g. Malsha" required />
            </div>

            <!-- 3. Last Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input j-autofill-last" name="lastName" placeholder="e.g. Jayarathne" required />
            </div>

            <!-- 4. Date of Birth -->
            <div class="c-form-field">
              <label class="c-form-field-label">Date of Birth <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $datepickerId  = 'j-student-dob';
              $inputName     = 'dateOfBirth';
              $selectedValue = '';
              $placeholder   = 'Select date of birth';
              $tone          = 'sky';
              $required      = true;
              require __DIR__ . '/_datepicker.php';
              ?>
            </div>

            <!-- 5. Gender -->
            <div class="c-form-field">
              <label class="c-form-field-label">Gender <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $dropdownId    = 'j-student-gender';
              $name          = 'gender';
              $options       = $genderOptions;
              $selectedValue = 'Female';
              $placeholder   = 'Select gender';
              $dropdownLabel = 'Gender';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 6. NIC -->
            <div class="c-form-field">
              <label class="c-form-field-label">NIC <em class="c-field-helper">optional</em></label>
              <input type="text" class="c-form-input j-enrollment-input" name="nationalId" placeholder="NIC if issued" />
            </div>

            <!-- 7. Birth Certificate No. -->
            <div class="c-form-field">
              <label class="c-form-field-label">Birth Certificate No. <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input" name="birthCertificateNumber" placeholder="Birth Cert. No." required />
            </div>

            <!-- 8. Grade -->
            <div class="c-form-field">
              <label class="c-form-field-label">Grade <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $dropdownId    = 'j-student-grade';
              $name          = 'grade';
              $options       = $gradeOptions;
              $selectedValue = 'Grade 6';
              $placeholder   = 'Select grade';
              $dropdownLabel = 'Grade';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 9. Class / Section -->
            <div class="c-form-field">
              <label class="c-form-field-label">Class / Section <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span><em class="c-field-helper">Choose a Grade 6 class</em></label>
              <?php
              $dropdownId    = 'j-student-class';
              $name          = 'classSection';
              $options       = $initialClassOptions;
              $selectedValue = '6-A';
              $placeholder   = 'Select class section';
              $dropdownLabel = 'Class / Section';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 10. Religion -->
            <div class="c-form-field">
              <label class="c-form-field-label">Religion <em class="c-field-helper">optional</em></label>
              <?php
              $dropdownId    = 'j-student-religion';
              $name          = 'religion';
              $options       = $religionOptions;
              $selectedValue = 'Buddhism';
              $placeholder   = 'Select religion';
              $dropdownLabel = 'Religion';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 11. Residential Address (Full Row) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Residential address <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <textarea class="c-form-textarea j-enrollment-textarea" name="homeAddress" rows="3" placeholder="No., Street, Town" required></textarea>
            </div>

            <!-- 12. Admission Date -->
            <div class="c-form-field">
              <label class="c-form-field-label">Admission Date <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $datepickerId  = 'j-student-admission';
              $inputName     = 'admissionDate';
              $selectedValue = date('Y-m-d');
              $placeholder   = 'Select admission date';
              $tone          = 'sky';
              $required      = true;
              require __DIR__ . '/_datepicker.php';
              ?>
            </div>

            <!-- 13. Nationality -->
            <div class="c-form-field">
              <label class="c-form-field-label">Nationality <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input" name="nationality" placeholder="e.g. Sri Lankan" value="Sri Lankan" required />
            </div>

            <!-- 14. Educational Zone -->
            <div class="c-form-field">
              <label class="c-form-field-label">Educational Zone <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $dropdownId    = 'j-student-zone';
              $name          = 'educationalZone';
              $options       = $zoneOptions;
              $selectedValue = 'Colombo';
              $placeholder   = 'Select zone';
              $dropdownLabel = 'Educational Zone';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 15. District -->
            <div class="c-form-field">
              <label class="c-form-field-label">District <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $dropdownId    = 'j-student-district';
              $name          = 'district';
              $options       = $districtOptions;
              $selectedValue = 'Colombo';
              $placeholder   = 'Select district';
              $dropdownLabel = 'District';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 16. Province -->
            <div class="c-form-field">
              <label class="c-form-field-label">Province <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $dropdownId    = 'j-student-province';
              $name          = 'province';
              $options       = $provinceOptions;
              $selectedValue = 'Western';
              $placeholder   = 'Select province';
              $dropdownLabel = 'Province';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 17. Previous School -->
            <div class="c-form-field">
              <label class="c-form-field-label">Previous School <em class="c-field-helper">optional</em></label>
              <input type="text" class="c-form-input j-enrollment-input" name="previousSchool" placeholder="Previous school attended" />
            </div>

            <!-- 18. Blood Group -->
            <div class="c-form-field">
              <label class="c-form-field-label">Blood Group <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <?php
              $dropdownId    = 'j-student-blood';
              $name          = 'bloodGroup';
              $options       = $bloodOptions;
              $selectedValue = 'Not provided';
              $placeholder   = 'Select blood group';
              $dropdownLabel = 'Blood Group';
              $dropdownClass = 'c-select-sky';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- 19. Profile Photo (Exact Original .c-photo-field bar with blue tint button) -->
            <div class="c-form-field">
              <label class="c-form-field-label">Profile Photo <em class="c-field-helper">optional</em></label>
              <div class="c-photo-field">
                <input type="file" accept="image/jpeg,image/png,image/webp" class="c-visually-hidden j-photo-input" id="j-student-photo-input" name="photo" disabled style="display:none;" />
                <button type="button" disabled title="Photo uploads will be added later" class="c-photo-choose-btn j-photo-choose">
                  <svg class="c-icon" width="14" height="14"><use href="#icon-upload"/></svg>
                  <span>Choose file</span>
                </button>
                <span class="c-photo-filename j-photo-filename">No file chosen</span>
              </div>
            </div>

            <!-- 20. Medical Notes / Allergies (Full Row) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Medical Notes / Allergies <em class="c-field-helper">optional — visible to admin and class teacher only, never public</em></label>
              <textarea class="c-form-textarea j-enrollment-textarea" name="medicalNotes" rows="3" placeholder="e.g. Asthma, peanut allergy"></textarea>
            </div>
          </div>

          <?php if (($currentRole ?? '') === 'management') require __DIR__ . '/_admission_guardian.php'; ?>
          <!-- Actions Footer (1:1 with original enrollment) -->
          <footer class="c-form-footer">
            <p class="c-required-note">Fields marked <span class="c-req-star" style="color:var(--skyblue, #7FC7CC);">*</span> are required to enroll a student.</p>
            <div class="c-form-footer-actions">
              <button disabled title="Draft saving is not available yet" id="j-enrollment-save-draft" class="c-btn-outline-sky j-btn-save-draft" type="button">
                <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
                <span>Save draft</span>
              </button>

              <button id="j-enrollment-submit" class="c-btn-solid-sky j-btn-submit-person" type="submit">
                <svg class="c-icon" width="16" height="16"><use href="#icon-checkCircle"/></svg>
                <span id="j-enrollment-submit-label">Enroll student</span>
              </button>
            </div>
          </footer>

        <?php elseif ($role === 'teacher'): ?>
          <!-- ===============================================================
               16. ADD TEACHER ACCOUNT
               Mirrors exact fields & styling from Admin/people/app.js lines 2506-2538
               =============================================================== -->
          <div class="c-form-grid">
            <!-- Full Name (span 2) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Full Name <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-full" name="fullName" placeholder="e.g. Sarah Peiris" required />
            </div>

            <!-- First Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-first" name="firstName" placeholder="e.g. Sarah" required />
            </div>

            <!-- Last Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-last" name="lastName" placeholder="e.g. Peiris" required />
              <p class="c-form-note">Used to create the institutional address.</p>
            </div>

            <!-- NIC -->
            <div class="c-form-field">
              <label class="c-form-field-label">NIC <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="text" class="c-form-input j-form-input" name="nic" placeholder="e.g. 198712345678V" required />
            </div>

            <!-- Date of Birth -->
            <div class="c-form-field">
              <label class="c-form-field-label">Date of Birth <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <?php
              $datepickerId  = 'j-teacher-dob';
              $inputName     = 'dateOfBirth';
              $selectedValue = '';
              $placeholder   = 'Select date of birth';
              $tone          = 'sunshine';
              $required      = true;
              require __DIR__ . '/_datepicker.php';
              ?>
            </div>

            <!-- Office address -->
            <div class="c-form-field">
              <label class="c-form-field-label">Office address</label>
              <input type="text" class="c-form-input j-form-input" name="officeAddress" placeholder="e.g. Main Building, Room 102" />
            </div>

            <!-- Mobile number -->
            <div class="c-form-field">
              <label class="c-form-field-label">Mobile number <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="tel" class="c-form-input j-form-input" name="phone" placeholder="e.g. +94 70 456 7890" required />
            </div>

            <!-- Personal Email -->
            <div class="c-form-field">
              <label class="c-form-field-label">Personal Email <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="email" class="c-form-input j-form-input" name="personalEmail" placeholder="e.g. sarah.p@gmail.com" required />
            </div>

            <!-- Institutional Email (Full Row, Disabled) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Institutional Email</label>
              <input type="text" class="c-form-input j-teacher-inst-email" name="institutionalEmail" disabled placeholder="Generated automatically from name" />
            </div>
          </div>

          <!-- Professional Details Section -->
          <section class="c-form-section" style="border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1.25rem;">
            <h2 class="c-form-section-title" style="margin-bottom: 1rem;">Professional Details</h2>
            <div class="c-form-grid">
              <div class="c-form-field">
                <label class="c-form-field-label">Subjects Qualified to Teach <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
                <input type="text" class="c-form-input j-form-input" name="subjects" placeholder="e.g. Mathematics, Physics" required />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Years of Experience <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
                <input type="number" class="c-form-input j-form-input" name="experience" placeholder="e.g. 5" min="0" required />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Join Date <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
                <?php
                $datepickerId  = 'j-teacher-join';
                $inputName     = 'joinDate';
                $selectedValue = date('Y-m-d');
                $placeholder   = 'Select join date';
                $tone          = 'sunshine';
                $required      = true;
                require __DIR__ . '/_datepicker.php';
                ?>
              </div>
            </div>
          </section>

          <!-- Qualifications & Experience Section -->
          <section class="c-form-section" style="border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1.25rem;">
            <h2 class="c-form-section-title" style="margin-bottom: 1rem;">Qualifications & Experience</h2>
            <div id="j-teacher-qual-fields" style="display: flex; flex-direction: column; gap: 1rem;">
              <div class="j-qual-row" style="display: grid; grid-template-columns: 1fr 1fr 100px auto; gap: 0.75rem; align-items: end;">
                <div class="c-form-field">
                  <label class="c-form-field-label">Title / Degree</label>
                  <input type="text" class="c-form-input j-qual-input" name="qualTitle[]" placeholder="e.g. BSc in Mathematics" />
                </div>
                <div class="c-form-field">
                  <label class="c-form-field-label">Institution</label>
                  <input type="text" class="c-form-input j-qual-input" name="qualInstitution[]" placeholder="e.g. University of Colombo" />
                </div>
                <div class="c-form-field">
                  <label class="c-form-field-label">Year</label>
                  <input type="text" class="c-form-input j-qual-input" name="qualYear[]" placeholder="2018" />
                </div>
                <button type="button" class="c-btn-solid-tone c-tone-maroon j-qual-remove" style="margin-bottom: 0.25rem; padding: 0.625rem 0.875rem;">Remove</button>
              </div>
              <button type="button" class="c-btn-solid-tone c-tone-sunshine j-qual-add" style="align-self: flex-start; padding: 0.5rem 1rem; font-size: 0.75rem;">+ Add Qualification</button>
            </div>
          </section>

          <!-- Emergency Contact Section -->
          <section class="c-form-section" style="border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1.25rem;">
            <h2 class="c-form-section-title" style="margin-bottom: 1rem;">Emergency Contact</h2>
            <div class="c-form-grid">
              <div class="c-form-field">
                <label class="c-form-field-label">Contact Name <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
                <input type="text" class="c-form-input j-form-input" name="emergencyName" placeholder="e.g. John Doe" required />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Contact mobile number <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
                <input type="tel" class="c-form-input j-form-input" name="emergencyPhone" placeholder="e.g. +94 77 123 4567" required />
              </div>
            </div>
          </section>

          <!-- Account Status Note Box -->
          <div class="c-status-note-box">
            <div>
              <p class="c-status-note-title">Account Status</p>
              <p class="c-status-note-desc">New teacher accounts are marked pending verification.</p>
            </div>
            <span class="c-status-note-pill">Pending save</span>
          </div>

          <!-- Submit Button Row -->
          <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1rem;">
            <button class="c-btn-solid-tone c-tone-sunshine j-btn-submit-person" type="submit">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            </button>
          </div>

        <?php elseif ($role === 'parent'): ?>
          <!-- ===============================================================
               17. ADD PARENT / GUARDIAN ACCOUNT
               Mirrors exact fields & styling from Admin/people/app.js lines 2591-2608
               =============================================================== -->
          <div class="c-form-grid">
            <!-- Relationship to Student -->
            <div class="c-form-field">
              <label class="c-form-field-label">Relationship to Student <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <?php
              $dropdownId    = 'j-parent-relation-type';
              $name          = 'relationship';
              $options       = $parentRelationOptions;
              $selectedValue = 'Father';
              $placeholder   = 'Select relationship';
              $dropdownLabel = 'Relationship';
              $dropdownClass = 'c-select-terracotta';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>

            <!-- Full Name (span 2) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Full Name <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-full" name="fullName" placeholder="e.g. Suresh Perera" required />
            </div>

            <!-- First Name (span 2) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-first" name="firstName" placeholder="e.g. Suresh" required />
            </div>

            <!-- Last Name (span 2) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-last" name="lastName" placeholder="e.g. Perera" required />
            </div>

            <!-- NIC -->
            <div class="c-form-field">
              <label class="c-form-field-label">NIC <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <input type="text" class="c-form-input j-form-input" name="nic" placeholder="e.g. 198012345678V" required />
            </div>

            <!-- Date of Birth -->
            <div class="c-form-field">
              <label class="c-form-field-label">Date of Birth <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <?php
              $datepickerId  = 'j-parent-dob';
              $inputName     = 'dateOfBirth';
              $selectedValue = '';
              $placeholder   = 'Select date of birth';
              $tone          = 'terracotta';
              $required      = true;
              require __DIR__ . '/_datepicker.php';
              ?>
            </div>

            <!-- Passport No. (if NIC unavailable) -->
            <div class="c-form-field">
              <label class="c-form-field-label">Passport No. (if NIC unavailable)</label>
              <input type="text" class="c-form-input j-form-input" name="passport" placeholder="e.g. N1234567" />
            </div>

            <!-- Occupation -->
            <div class="c-form-field">
              <label class="c-form-field-label">Occupation <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <input type="text" class="c-form-input j-form-input" name="occupation" placeholder="e.g. Engineer" required />
            </div>

            <!-- Employer / Place of Work -->
            <div class="c-form-field">
              <label class="c-form-field-label">Employer / Place of Work</label>
              <input type="text" class="c-form-input j-form-input" name="employer" placeholder="e.g. Tech Solutions Ltd" />
            </div>

            <!-- Mobile Number -->
            <div class="c-form-field">
              <label class="c-form-field-label">Mobile Number <span class="c-required-mark" style="color:var(--terracotta, #AF5031);">*</span></label>
              <input type="tel" class="c-form-input j-form-input" name="mobile" placeholder="e.g. 07X XXX XXXX" required />
            </div>

            <!-- Home number -->
            <div class="c-form-field">
              <label class="c-form-field-label">Home number</label>
              <input type="tel" class="c-form-input j-form-input" name="homePhone" placeholder="e.g. 0XX XXX XXXX" />
            </div>

            <!-- Office number -->
            <div class="c-form-field">
              <label class="c-form-field-label">Office number</label>
              <input type="tel" class="c-form-input j-form-input" name="officePhone" placeholder="e.g. 0XX XXX XXXX" />
            </div>

            <!-- Office address -->
            <div class="c-form-field">
              <label class="c-form-field-label">Office address</label>
              <input type="text" class="c-form-input j-form-input" name="officeAddress" placeholder="e.g. 123 Office Road" />
            </div>

            <!-- Email Address -->
            <div class="c-form-field">
              <label class="c-form-field-label">Email Address</label>
              <input type="email" class="c-form-input j-form-input" name="email" required placeholder="e.g. parent@email.com" />
            </div>

            <!-- Emergency name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Emergency name</label>
              <input type="text" class="c-form-input j-form-input" name="emergencyName" placeholder="e.g. Amal Perera" />
            </div>

            <!-- Emergency contact -->
            <div class="c-form-field">
              <label class="c-form-field-label">Emergency contact</label>
              <input type="tel" class="c-form-input j-form-input" name="emergencyContact" placeholder="e.g. +94 77 123 4567" />
            </div>
          </div>

          <!-- Submit Button Row -->
          <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1rem;">
            <button class="c-btn-solid-tone c-tone-terracotta j-btn-submit-person" type="submit">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            </button>
          </div>

        <?php elseif ($role === 'management'): ?>
          <!-- ===============================================================
               18. ADD MANAGEMENT PANEL ACCOUNT
               Mirrors exact fields & styling from Admin/people/app.js lines 2639-2655
               =============================================================== -->
          <div class="c-form-grid">
            <!-- Full Name (span 2) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Full Name <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-full" name="fullName" placeholder="e.g. Alex Thompson" required />
            </div>

            <!-- First Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-first" name="firstName" placeholder="e.g. Alex" required />
            </div>

            <!-- Last Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-last" name="lastName" placeholder="e.g. Thompson" required />
            </div>

            <!-- NIC -->
            <div class="c-form-field">
              <label class="c-form-field-label">NIC <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input" name="nic" placeholder="e.g. 198512345678V" required />
            </div>

            <!-- Contact Number -->
            <div class="c-form-field">
              <label class="c-form-field-label">Contact Number <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="tel" class="c-form-input j-form-input" name="phone" placeholder="e.g. +94 77 123 4567" required />
            </div>

            <!-- Personal Email -->
            <div class="c-form-field">
              <label class="c-form-field-label">Personal Email <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="email" class="c-form-input j-form-input" name="personalEmail" placeholder="e.g. alex.t@gmail.com" required />
            </div>

            <!-- Institutional Email (Full Row, Disabled) -->
            <div class="c-form-field c-span-2">
              <label class="c-form-field-label">Institutional Email</label>
              <input type="text" class="c-form-input j-mgmt-inst-email" name="institutionalEmail" disabled placeholder="Generated automatically from name" />
            </div>
          </div>

          <!-- Employment details Section -->
          <section class="c-form-section" style="border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1.25rem;">
            <h2 class="c-form-section-title" style="margin-bottom: 1rem;">Employment details</h2>
            <div class="c-form-grid">
              <div class="c-form-field">
                <label class="c-form-field-label">Join Date <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
                <?php
                $datepickerId  = 'j-mgmt-join';
                $inputName     = 'joinDate';
                $selectedValue = date('Y-m-d');
                $placeholder   = 'Select join date';
                $tone          = 'maroon';
                $required      = true;
                require __DIR__ . '/_datepicker.php';
                ?>
              </div>
            </div>
          </section>

          <!-- Emergency Contact Section -->
          <section class="c-form-section" style="border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1.25rem;">
            <h2 class="c-form-section-title" style="margin-bottom: 1rem;">Emergency Contact</h2>
            <div class="c-form-grid">
              <div class="c-form-field">
                <label class="c-form-field-label">Contact Name <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
                <input type="text" class="c-form-input j-form-input" name="emergencyName" placeholder="e.g. Jane Doe" required />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Contact Number <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
                <input type="tel" class="c-form-input j-form-input" name="emergencyPhone" placeholder="e.g. +94 77 123 4567" required />
              </div>
            </div>
          </section>

          <!-- Warning Notice Box -->
          <p class="c-form-warning-box">A temporary password would be sent to the personal email after verification.</p>

          <!-- Submit Button Row -->
          <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1rem;">
            <button class="c-btn-solid-tone c-tone-maroon j-btn-submit-person" type="submit">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            </button>
          </div>
        <?php endif; ?>

      </div>
    </form>
  </div>
</section>
