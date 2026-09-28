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

$countryCodeOptions = [
    ['value' => '+94',  'label' => '🇱🇰 +94 (LK)'],
    ['value' => '+44',  'label' => '🇬🇧 +44 (UK)'],
    ['value' => '+1',   'label' => '🇺🇸 +1 (US)'],
    ['value' => '+61',  'label' => '🇦🇺 +61 (AU)'],
    ['value' => '+971', 'label' => '🇦🇪 +971 (AE)'],
    ['value' => '+65',  'label' => '🇸🇬 +65 (SG)'],
    ['value' => '+91',  'label' => '🇮🇳 +91 (IN)'],
    ['value' => '+60',  'label' => '🇲🇾 +60 (MY)'],
    ['value' => '+974', 'label' => '🇶🇦 +974 (QA)'],
    ['value' => '+966', 'label' => '🇸🇦 +966 (SA)'],
    ['value' => '+64',  'label' => '🇳🇿 +64 (NZ)'],
    ['value' => '+81',  'label' => '🇯🇵 +81 (JP)'],
    ['value' => '+49',  'label' => '🇩🇪 +49 (DE)']
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
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? ($_SESSION['_csrf_token'] ?? '')) ?>" />
      
      <!-- Role-Themed Header matching 1:1 original -->
      <header class="c-form-header c-header-<?= htmlspecialchars($cfg['headerTheme']) ?>">
        <div class="c-form-header-row">
          <div class="c-form-header-title-wrap" style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 class="c-form-header-title c-font-display"><?= htmlspecialchars($cfg['title']) ?></h1>
            <?php if (!empty($cfg['draftText'])): ?>
              <span id="j-enrollment-draft-pill" class="c-draft-pill"><?= htmlspecialchars($cfg['draftText']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Top-Right: Saved Drafts Dropdown Button -->
          <div class="c-saved-drafts-wrap j-saved-drafts-wrap" data-role="<?= htmlspecialchars($role) ?>">
            <button type="button" class="c-saved-drafts-btn j-saved-drafts-toggle" data-role="<?= htmlspecialchars($role) ?>" aria-expanded="false" title="View presaved drafts">
              <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-fileText"/></svg>
              <span>Saved Drafts</span>
              <span class="c-drafts-badge j-drafts-count">0</span>
              <svg class="c-icon c-drafts-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronDown"/></svg>
            </button>

            <!-- Small Fixed-Size Dropdown with Scrollbar -->
            <div class="c-saved-drafts-dropdown j-saved-drafts-dropdown" style="display: none;">
              <div class="c-saved-drafts-dropdown__head">
                <span class="c-saved-drafts-dropdown__title">Presaved Drafts</span>
                <span class="c-saved-drafts-dropdown__sub j-drafts-count-label">0 drafts</span>
              </div>
              <div class="c-saved-drafts-dropdown__list j-saved-drafts-list" tabindex="0">
                <!-- Injected dynamically by people-directory.js -->
              </div>
              <div class="c-saved-drafts-dropdown__foot">
                <button type="button" class="c-btn-save-draft-inline j-btn-save-draft" data-role="<?= htmlspecialchars($role) ?>">
                  <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-plus"/></svg>
                  <span>Save current form as draft</span>
                </button>
              </div>
            </div>
          </div>
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
            <p class="c-form-section-desc">Enter these details once; index number and email are generated automatically.</p>
          </div>

          <div class="c-form-grid">
            <!-- 1. Full Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Full Name (with initials) <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input j-full-name-input j-name-letters" name="fullName" placeholder="e.g. M. A. Jayarathne" required />
            </div>

            <!-- 2. First Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input j-first-name-input j-name-letters" name="firstName" placeholder="e.g. Malsha" required />
            </div>

            <!-- 3. Last Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
              <input type="text" class="c-form-input j-enrollment-input j-last-name-input j-name-letters" name="lastName" placeholder="e.g. Jayarathne" required />
            </div>

            <!-- 4. Date of Birth -->
            <div class="c-form-field">
              <div class="c-field-header-row" style="display:flex; justify-content:space-between; align-items:center;">
                <label class="c-form-field-label">Date of Birth <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span></label>
                <button type="button" id="j-unlock-student-dob" class="c-btn-text-override" title="Unlock full calendar range" style="background:none; border:none; color:var(--sky, #207C82); font-size:0.75rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem;">
                  <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>
                  <span id="j-unlock-student-dob-text">Unlock range</span>
                </button>
              </div>
              <?php
              $datepickerId    = 'j-student-dob';
              $inputName       = 'dateOfBirth';
              $selectedValue   = '';
              $placeholder     = 'Select date of birth';
              $tone            = 'sky';
              $required        = true;
              $extraAttributes = 'data-min-age="3" data-max-age="19"';
              require __DIR__ . '/_datepicker.php';
              ?>
              <p class="c-field-hint" id="j-student-dob-hint" style="font-size:0.75rem; color:rgba(15,65,74,0.6); margin-top:0.25rem;">Standard student age range (3–19 yrs) enforced.</p>
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
              <span id="j-student-grade-hint" class="c-field-hint" style="display:none; font-size:0.75rem; font-weight:600; color:var(--sky, #207C82); margin-top:0.25rem;"></span>
            </div>

            <!-- 9. Class / Section -->
            <div class="c-form-field">
              <label class="c-form-field-label">Class / Section <span class="c-required-mark" style="color:var(--skyblue, #7FC7CC);">*</span><em class="c-field-helper" id="j-student-class-helper">Choose a Grade 6 class</em></label>
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
                <input type="file" accept="image/jpeg,image/png,image/webp" class="c-visually-hidden j-photo-input" id="j-student-photo-input" name="photo" style="display:none;" />
                <button type="button" class="c-photo-choose-btn j-photo-choose">
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

          <!-- 21. Guardian Information & Sibling Linkage -->
          <?php require __DIR__ . '/_admission_guardian.php'; ?>

          <!-- Actions Footer (1:1 with original enrollment) -->
          <footer class="c-form-footer">
            <div id="j-enrollment-footer-notice" class="c-form-footer-notice" style="display: none;"></div>
            <p class="c-required-note">Fields marked <span class="c-req-star" style="color:var(--skyblue, #7FC7CC);">*</span> are required to enroll a student.</p>
            <div class="c-form-footer-actions">
              <button id="j-enrollment-save-draft" class="c-btn-outline-sky j-btn-save-draft" type="button">
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
              <input type="text" class="c-form-input j-form-input j-autofill-full j-name-letters" name="fullName" placeholder="e.g. Sarah Peiris" required />
            </div>

            <!-- First Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-first j-name-letters" name="firstName" placeholder="e.g. Sarah" required />
            </div>

            <!-- Last Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-last j-name-letters" name="lastName" placeholder="e.g. Peiris" required />
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
              <div class="c-phone-input-group">
                <?php
                $dropdownId    = 'j-teacher-country-code';
                $name          = 'teacherCountryCode';
                $options       = $countryCodeOptions;
                $selectedValue = '+94';
                $placeholder   = 'Code';
                $dropdownLabel = 'Country Code';
                $dropdownClass = 'c-select-sunshine c-phone-code-dropdown';
                require __DIR__ . '/_dropdown.php';
                ?>
                <input type="tel" class="c-form-input j-form-input j-phone-digits" id="j-teacher-phone-input" name="phoneNumber" placeholder="70 456 7890" maxlength="9" required />
                <input type="hidden" name="phone" id="j-teacher-phone-full" />
              </div>
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
                <input type="text" class="c-form-input j-form-input j-name-letters" name="emergencyName" placeholder="e.g. John Doe" required />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Contact mobile number <span class="c-required-mark" style="color:var(--sunshine, #EA8913);">*</span></label>
                <div class="c-phone-input-group">
                  <?php
                  $dropdownId    = 'j-teacher-em-country-code';
                  $name          = 'teacherEmergencyCountryCode';
                  $options       = $countryCodeOptions;
                  $selectedValue = '+94';
                  $placeholder   = 'Code';
                  $dropdownLabel = 'Country Code';
                  $dropdownClass = 'c-select-sunshine c-phone-code-dropdown';
                  require __DIR__ . '/_dropdown.php';
                  ?>
                  <input type="tel" class="c-form-input j-form-input j-phone-digits" id="j-teacher-emphone-input" name="emergencyPhoneNumber" placeholder="77 123 4567" maxlength="9" required />
                  <input type="hidden" name="emergencyPhone" id="j-teacher-emphone-full" />
                </div>
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

          <!-- Submit Button Row with Save Draft -->
          <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1rem;">
            <button id="j-teacher-save-draft" class="c-btn-outline-sunshine j-btn-save-draft" data-role="teacher" type="button">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save draft</span>
            </button>
            <button class="c-btn-solid-tone c-tone-sunshine j-btn-submit-person" type="submit" id="j-teacher-submit">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            </button>
          </div>

        <?php elseif ($role === 'parent'): ?>
          <!-- ===============================================================
               17. PARENT ENROLLMENT POLICY CARD
               Parents & guardians are registered alongside their child during student admission
               =============================================================== -->
          <div class="c-info-card c-tinted-section c-tint-terracotta" style="margin: 1.5rem 0; padding: 2rem; border-radius: var(--radius-lg, 0.75rem); background: rgba(175, 80, 49, 0.06); border: 1px solid rgba(175, 80, 49, 0.2);">
            <div style="display: flex; align-items: flex-start; gap: 1rem;">
              <span class="c-icon-accent" style="color: var(--terracotta, #AF5031); padding: 0.5rem; background: rgba(175, 80, 49, 0.12); border-radius: 50%;">
                <svg class="c-icon" width="24" height="24" viewBox="0 0 24 24"><use href="#icon-users"/></svg>
              </span>
              <div style="flex: 1;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 700; color: var(--terracotta, #AF5031);">Parent & Guardian Registration Policy</h3>
                <p style="margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--midnight, #0F414A); line-height: 1.6;">
                  In L'École, parents and legal guardians are enrolled alongside their child during student admission. This ensures accurate family record associations, sibling linkage, and academic record access.
                </p>
                <button type="button" class="c-btn-accent c-tone-sky j-open-add-student-btn" onclick="openAddPersonForm('student')">
                  <span>Go to Student Admission Form</span>
                </button>
              </div>
            </div>
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
              <input type="text" class="c-form-input j-form-input j-autofill-full j-name-letters" name="fullName" placeholder="e.g. Alex Thompson" required />
            </div>

            <!-- First Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">First Name <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-first j-name-letters" name="firstName" placeholder="e.g. Alex" required />
            </div>

            <!-- Last Name -->
            <div class="c-form-field">
              <label class="c-form-field-label">Last Name <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input j-autofill-last j-name-letters" name="lastName" placeholder="e.g. Thompson" required />
            </div>

            <!-- NIC -->
            <div class="c-form-field">
              <label class="c-form-field-label">NIC <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <input type="text" class="c-form-input j-form-input" name="nic" placeholder="e.g. 198512345678V" required />
            </div>

            <!-- Contact Number -->
            <div class="c-form-field">
              <label class="c-form-field-label">Contact Number <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
              <div class="c-phone-input-group">
                <?php
                $dropdownId    = 'j-mgmt-country-code';
                $name          = 'mgmtCountryCode';
                $options       = $countryCodeOptions;
                $selectedValue = '+94';
                $placeholder   = 'Code';
                $dropdownLabel = 'Country Code';
                $dropdownClass = 'c-select-maroon c-phone-code-dropdown';
                require __DIR__ . '/_dropdown.php';
                ?>
                <input type="tel" class="c-form-input j-form-input j-phone-digits" id="j-mgmt-phone-input" name="phoneNumber" placeholder="77 123 4567" maxlength="9" required />
                <input type="hidden" name="phone" id="j-mgmt-phone-full" />
              </div>
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
                <input type="text" class="c-form-input j-form-input j-name-letters" name="emergencyName" placeholder="e.g. Jane Doe" required />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Contact Number <span class="c-required-mark" style="color:var(--maroon, #7F0303);">*</span></label>
                <div class="c-phone-input-group">
                  <?php
                  $dropdownId    = 'j-mgmt-em-country-code';
                  $name          = 'mgmtEmergencyCountryCode';
                  $options       = $countryCodeOptions;
                  $selectedValue = '+94';
                  $placeholder   = 'Code';
                  $dropdownLabel = 'Country Code';
                  $dropdownClass = 'c-select-maroon c-phone-code-dropdown';
                  require __DIR__ . '/_dropdown.php';
                  ?>
                  <input type="tel" class="c-form-input j-form-input j-phone-digits" id="j-mgmt-emphone-input" name="emergencyPhoneNumber" placeholder="77 123 4567" maxlength="9" required />
                  <input type="hidden" name="emergencyPhone" id="j-mgmt-emphone-full" />
                </div>
              </div>
            </div>
          </section>

          <!-- Warning Notice Box -->
          <p class="c-form-warning-box">A temporary password would be sent to the personal email after verification.</p>

          <!-- Submit Button Row with Save Draft -->
          <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; border-top: 1px solid var(--color-border, #EFE8DF); padding-top: 1rem;">
            <button id="j-mgmt-save-draft" class="c-btn-outline-maroon j-btn-save-draft" data-role="management" type="button">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save draft</span>
            </button>
            <button class="c-btn-solid-tone c-tone-maroon j-btn-submit-person" type="submit" id="j-mgmt-submit">
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            </button>
          </div>
        <?php endif; ?>

      </div>
    </form>
  </div>
</section>

<!-- Parent Picker Modal for Sibling Linking -->
<?php require_once __DIR__ . '/_parent_picker.php'; ?>
