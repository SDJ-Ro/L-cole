<!-- =========================================================================
     L'ÉCOLE — STUDENT ADMISSION: GUARDIAN CO-CREATION / LINKING COMPONENT
     Supports Single Guardian Rule, Sibling Linking, White-Labeled Identity (NIC vs Passport),
     Country Code Phone Selector, and DOB Calendar Override.
     ========================================================================= -->
<div class="c-admission-guardian-container">
  <div class="c-form-section-head">
    <div class="c-form-section-badge">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
    </div>
    <div>
      <h3 class="c-form-section-title">Parent / Legal Guardian Information</h3>
      <p class="c-form-section-desc">Every enrolled student must have exactly one legal guardian. Link an existing parent for siblings, or onboard a new guardian below.</p>
    </div>
  </div>

  <!-- Idempotency / Request Key -->
  <input type="hidden" name="admissionKey" id="j-admission-key" value="<?= bin2hex(random_bytes(16)) ?>" />

  <!-- Guardian Mode Selection -->
  <div class="c-guardian-mode-selector">
    <label class="c-form-field-label">Guardian Admission Type <span class="c-required-mark">*</span></label>
    <div class="c-guardian-toggle-group" role="radiogroup" aria-label="Guardian mode">
      <label class="c-guardian-toggle-btn active" id="j-mode-label-existing">
        <input type="radio" name="guardianMode" value="existing" checked class="j-guardian-mode-radio" />
        <span class="c-toggle-icon">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
        </span>
        <span class="c-toggle-text">
          <strong>Use Existing Parent (Sibling)</strong>
          <small>Link to a parent already registered in the school directory</small>
        </span>
      </label>

      <label class="c-guardian-toggle-btn" id="j-mode-label-new">
        <input type="radio" name="guardianMode" value="new" class="j-guardian-mode-radio" />
        <span class="c-toggle-icon">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
        </span>
        <span class="c-toggle-text">
          <strong>Create New Guardian</strong>
          <small>Create a new guardian profile and portal user account</small>
        </span>
      </label>
    </div>
  </div>

  <!-- =========================================================================
       SUB-PANEL A: USE EXISTING PARENT (SIBLING LINK)
       ========================================================================= -->
  <fieldset id="existing-guardian-fields" class="c-guardian-panel c-guardian-panel--existing">
    <input type="hidden" id="existing-parent-id" name="existingParent" value="" />

    <!-- Inline Search & Auto-Filtered Parent List -->
    <div id="j-inline-parent-picker" class="c-inline-parent-picker">
      <div class="c-parent-search-box" style="margin-bottom: 0.75rem;">
        <label class="c-form-field-label" style="font-size: 0.8125rem; font-weight: 600;">Search Registered Parents</label>
        <div style="position: relative;">
          <input type="search" id="j-inline-parent-search" class="c-form-input" placeholder="Type parent name, ID (e.g. PAR-2026-0001), phone, or sibling name..." autocomplete="off" style="padding-left: 2.25rem;" />
          <svg class="c-icon" width="15" height="15" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: rgba(15,65,74,0.45); pointer-events: none;"><use href="#icon-search"/></svg>
        </div>
        <p id="j-inline-parent-count" class="c-field-hint" style="margin-top: 0.35rem; font-size: 0.75rem;">Showing active registered parents</p>
      </div>
      <div id="j-inline-parent-list" class="c-inline-parent-list" style="max-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.25rem;"></div>
    </div>

    <!-- Selected State (Card) -->
    <div id="selected-parent-card" class="c-selected-parent-card" style="display:none;" aria-live="polite">
      <div class="c-selected-parent-header">
        <div class="c-parent-avatar bg-deepsea text-white" id="selected-parent-initials">P</div>
        <div class="c-selected-parent-info">
          <div class="c-selected-parent-top">
            <h4 id="selected-parent-name" class="c-parent-name"></h4>
            <span id="selected-parent-code" class="c-badge-pill c-badge-terracotta"></span>
            <span class="c-badge-pill c-badge-active">Active Guardian</span>
          </div>
          <div class="c-selected-parent-meta">
            <span id="selected-parent-relation" class="c-parent-relation"></span>
            <span class="c-meta-divider">·</span>
            <span id="selected-parent-email" class="c-parent-email"></span>
            <span class="c-meta-divider">·</span>
            <span id="selected-parent-phone" class="c-parent-phone"></span>
          </div>
          <div id="selected-parent-address-wrap" class="c-parent-address-preview" style="display:none;">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span id="selected-parent-address"></span>
          </div>
          <div id="selected-parent-children-wrap" class="c-parent-children-preview">
            <span class="c-children-label">Enrolled children:</span>
            <div id="selected-parent-children" class="c-children-tags"></div>
          </div>
        </div>
        <button type="button" id="j-change-selected-parent" class="c-btn-outline-sky c-btn-sm">
          <span>Change Parent</span>
        </button>
      </div>

      <!-- Sibling Address Copy Option -->
      <div class="c-sibling-address-sync">
        <label class="c-checkbox-item">
          <input type="checkbox" id="j-sync-parent-address" checked />
          <span class="c-checkbox-custom"></span>
          <span class="c-checkbox-text">
            <strong>Use parent's residential address for this student</strong>
            <small>Automatically synchronizes the family home address into the student's record.</small>
          </span>
        </label>
      </div>
    </div>
  </fieldset>

  <!-- =========================================================================
       SUB-PANEL B: CREATE NEW GUARDIAN
       ========================================================================= -->
  <fieldset id="new-guardian-fields" class="c-guardian-panel c-guardian-panel--new" style="display:none;" disabled>
    <div class="c-form-grid">
      <!-- 1. Relationship -->
      <div class="c-form-field">
        <label class="c-form-field-label">Relationship to Student <span class="c-required-mark">*</span></label>
        <?php
          $dropdownId    = 'j-guardian-relationship';
          $name          = 'guardian[relationship]';
          $dropdownLabel = 'Relationship';
          $placeholder   = 'Select relationship';
          $selectedValue = 'Father';
          $dropdownClass = 'c-select-terracotta';
          $options       = [
            ['value' => 'Father',   'label' => 'Father'],
            ['value' => 'Mother',   'label' => 'Mother'],
            ['value' => 'Guardian', 'label' => 'Legal Guardian']
          ];
          require __DIR__ . '/_dropdown.php';
        ?>
      </div>

      <!-- 2. Full Name -->
      <div class="c-form-field c-span-2">
        <label class="c-form-field-label">Full Name <span class="c-required-mark">*</span></label>
        <input type="text" class="c-form-input j-guardian-input j-name-letters" name="guardian[fullName]" id="guardian-fullName" placeholder="e.g. Suresh Kamal Perera" maxlength="150" />
      </div>

      <!-- 3. First Name -->
      <div class="c-form-field">
        <label class="c-form-field-label">First Name <span class="c-required-mark">*</span></label>
        <input type="text" class="c-form-input j-guardian-input j-name-letters" name="guardian[firstName]" id="guardian-firstName" placeholder="e.g. Suresh" maxlength="75" />
      </div>

      <!-- 4. Last Name -->
      <div class="c-form-field">
        <label class="c-form-field-label">Last Name <span class="c-required-mark">*</span></label>
        <input type="text" class="c-form-input j-guardian-input j-name-letters" name="guardian[lastName]" id="guardian-lastName" placeholder="e.g. Perera" maxlength="75" />
      </div>

      <!-- 5. White-Labeled Identity Selector (NIC vs Foreign Passport) -->
      <div class="c-form-field c-span-2">
        <label class="c-form-field-label">Guardian Identification Document <span class="c-required-mark">*</span></label>
        <div class="c-id-type-selector">
          <label class="c-id-radio-label active" id="j-id-type-nic-label">
            <input type="radio" name="guardianIdType" value="nic" checked class="j-id-type-radio" />
            <span>🇱🇰 Sri Lankan National ID (NIC)</span>
          </label>
          <label class="c-id-radio-label" id="j-id-type-passport-label">
            <input type="radio" name="guardianIdType" value="passport" class="j-id-type-radio" />
            <span>🌐 Foreign Passport</span>
          </label>
        </div>

        <!-- NIC Input -->
        <div id="j-guardian-nic-wrap" class="c-id-input-wrap">
          <input type="text" class="c-form-input j-guardian-input" name="guardian[nic]" id="guardian-nic" placeholder="e.g. 198012345678 or 801234567V" maxlength="30" autocomplete="off" />
          <div id="j-guardian-nic-feedback" style="display:flex;justify-content:space-between;align-items:center;font-size:0.75rem;margin-top:0.35rem;">
            <span class="c-field-hint" id="j-guardian-nic-hint" style="margin:0;">Format: 12 digits (e.g. 198012345678) or 9 digits + V/X</span>
            <span id="j-guardian-nic-count" style="font-weight:700;color:var(--text-subtle, #5C7679);">0 / 12 digits</span>
          </div>
        </div>

        <!-- Passport Input -->
        <div id="j-guardian-passport-wrap" class="c-id-input-wrap" style="display:none;">
          <input type="text" class="c-form-input j-guardian-input" name="guardian[passport]" id="guardian-passport" placeholder="e.g. N12345678" maxlength="50" />
          <p class="c-field-hint">Enter valid international passport number (6 to 20 alphanumeric characters).</p>
        </div>
      </div>

      <!-- 6. Date of Birth with Calendar Lock & Override -->
      <div class="c-form-field">
        <div class="c-field-header-row">
          <label class="c-form-field-label">Date of Birth <span class="c-required-mark">*</span></label>
          <button type="button" id="j-unlock-guardian-dob" class="c-btn-text-override" title="Unlock full calendar range">
            <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>
            <span id="j-unlock-guardian-dob-text">Unlock range</span>
          </button>
        </div>
        <?php
        $datepickerId    = 'j-guardian-dob';
        $inputName       = 'guardian[dateOfBirth]';
        $selectedValue   = '';
        $placeholder     = 'Select date of birth';
        $tone            = 'terracotta';
        $required        = true;
        $extraAttributes = 'data-min-age="18" data-max-age="80"';
        require __DIR__ . '/_datepicker.php';
        ?>
        <p class="c-field-hint" id="j-guardian-dob-hint">Standard adult age range (18–80 yrs) enforced.</p>
      </div>

      <!-- 7. Occupation -->
      <div class="c-form-field">
        <label class="c-form-field-label">Occupation <span class="c-required-mark">*</span></label>
        <input type="text" class="c-form-input j-guardian-input" name="guardian[occupation]" id="guardian-occupation" placeholder="e.g. Civil Engineer, Accountant" maxlength="100" />
      </div>

      <!-- 8. Employer -->
      <div class="c-form-field">
        <label class="c-form-field-label">Employer / Organization <em class="c-field-helper">optional</em></label>
        <input type="text" class="c-form-input" name="guardian[employer]" id="guardian-employer" placeholder="e.g. Central Engineering Bureau" maxlength="150" />
      </div>

      <!-- 9. Mobile Phone with Country Code Dropdown -->
      <div class="c-form-field">
        <label class="c-form-field-label">Primary Mobile Phone <span class="c-required-mark">*</span></label>
        <div class="c-phone-input-group">
          <?php
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
          $dropdownId    = 'j-guardian-country-code';
          $name          = 'guardianCountryCode';
          $options       = $countryCodeOptions;
          $selectedValue = '+94';
          $placeholder   = 'Code';
          $dropdownLabel = 'Country Code';
          $dropdownClass = 'c-select-terracotta c-phone-code-dropdown';
          require __DIR__ . '/_dropdown.php';
          ?>
          <input type="tel" class="c-form-input j-guardian-input j-phone-digits" name="guardian[mobileNumber]" id="guardian-mobile" placeholder="77 123 4567" maxlength="9" />
          <input type="hidden" name="guardian[mobile]" id="j-guardian-mobile-full" />
        </div>
        <p class="c-field-hint">Primary number used for school SMS alerts and notifications.</p>
      </div>

      <!-- 10. Email (Used for Portal Account Onboarding) -->
      <div class="c-form-field c-span-2">
        <label class="c-form-field-label">Personal Email for Portal Login & Activation <span class="c-required-mark">*</span></label>
        <input type="email" class="c-form-input j-guardian-input" name="guardian[email]" id="guardian-email" placeholder="e.g. suresh.perera@example.com" maxlength="191" />
        <p class="c-field-hint" style="color:var(--deepsea, #0F414A);">
          <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
          An activation OTP and password setup instructions will be sent to this email upon admission.
        </p>
      </div>

      <!-- 11. Residential Address (Full Row) -->
      <div class="c-form-field c-span-2">
        <label class="c-form-field-label">Home / Residential Address <em class="c-field-helper">optional (defaults to student's address if left blank)</em></label>
        <textarea class="c-form-textarea" name="guardian[homeAddress]" id="guardian-homeAddress" rows="2" placeholder="Leave empty to use student's residential address"></textarea>
      </div>

      <!-- 12. Office Phone & Address -->
      <div class="c-form-field">
        <label class="c-form-field-label">Office Phone <em class="c-field-helper">optional</em></label>
        <input type="tel" class="c-form-input" name="guardian[officePhone]" id="guardian-officePhone" placeholder="e.g. 011 234 5678" maxlength="30" />
      </div>

      <div class="c-form-field">
        <label class="c-form-field-label">Home Landline <em class="c-field-helper">optional</em></label>
        <input type="tel" class="c-form-input" name="guardian[homePhone]" id="guardian-homePhone" placeholder="e.g. 011 289 0123" maxlength="30" />
      </div>

      <!-- 13. Office Address -->
      <div class="c-form-field c-span-2">
        <label class="c-form-field-label">Office Address <em class="c-field-helper">optional</em></label>
        <input type="text" class="c-form-input" name="guardian[officeAddress]" id="guardian-officeAddress" placeholder="Workplace address" maxlength="255" />
      </div>

      <!-- 14. Emergency Contact Info (Compulsory) -->
      <div class="c-form-field">
        <label class="c-form-field-label">Secondary Emergency Contact Name <span class="c-required-mark">*</span></label>
        <input type="text" class="c-form-input j-guardian-input j-name-letters" name="guardian[emergencyName]" id="guardian-emergencyName" placeholder="e.g. Kamal Perera (Uncle)" maxlength="150" required />
        <p class="c-field-hint" id="j-guardian-emname-hint">Alternative contact person (must be different from parent/guardian).</p>
      </div>

      <div class="c-form-field">
        <label class="c-form-field-label">Secondary Emergency Phone <span class="c-required-mark">*</span></label>
        <div class="c-phone-input-group">
          <?php
          $dropdownId    = 'j-guardian-em-country-code';
          $name          = 'guardianEmergencyCountryCode';
          $options       = $countryCodeOptions;
          $selectedValue = '+94';
          $placeholder   = 'Code';
          $dropdownLabel = 'Country Code';
          $dropdownClass = 'c-select-terracotta c-phone-code-dropdown';
          require __DIR__ . '/_dropdown.php';
          ?>
          <input type="tel" class="c-form-input j-guardian-input j-phone-digits" name="guardian[emergencyNumber]" id="guardian-emergencyNumber" placeholder="77 987 6543" maxlength="9" required />
          <input type="hidden" name="guardian[emergencyContact]" id="guardian-emergencyContact" />
        </div>
        <p class="c-field-hint" id="j-guardian-emphone-hint">Alternative phone number (cannot be the parent's contact number).</p>
      </div>
    </div>
  </fieldset>
</div>
