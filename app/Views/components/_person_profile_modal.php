<?php
/**
 * =========================================================================
 * L'ÉCOLE — MASTER PERSON PROFILE MODAL COMPONENT (VIEW & EDIT)
 * =========================================================================
 * Clean Master Modal Shell supporting Students, Teachers, Parents, and
 * Management Panel staff in both 'view' and 'edit' modes.
 *
 * Modularly includes:
 *   1. `_profile_information_tab.php` -> Structured Information Cards & Details
 *   2. `_digital_record_book.php`      -> Academics (2 Top Dropdowns & Marks)
 *   3. `_profile_activities_tab.php`   -> Extracurriculars & Achievements
 * =========================================================================
 */
$allowEdit = $allowEdit ?? true;
?>

<!-- Master Profile Dialog Container -->
<div class="c-modal-overlay j-profile-modal-overlay" id="j-profile-modal" data-allow-edit="<?= $allowEdit ? 'true' : 'false' ?>" style="display: none;" role="dialog" aria-modal="true" aria-hidden="true">
  
  <!-- Dimmed Backdrop Scrim -->
  <button type="button" class="c-modal-scrim j-modal-close" aria-label="Close profile modal"></button>

  <!-- Dialog Card -->
  <div class="c-modal-card" id="j-profile-card">
    
    <!-- Modal Header (Dynamic role theme: sky, sunshine, terracotta, maroon) -->
    <header class="c-modal-header c-header-sky" id="j-modal-header">
      <div class="c-modal-header-row">
        <div class="c-modal-identity">
          <div class="c-modal-avatar bg-sand text-midnight" id="j-modal-avatar">U</div>
          <div style="min-width: 0;">
            <p class="c-modal-eyebrow" id="j-modal-eyebrow">Student</p>
            <!-- View Mode Title -->
            <h2 class="c-modal-name c-font-display" id="j-modal-name-view">User Name</h2>
            <?php if ($allowEdit): ?>
            <!-- Edit Mode Input -->
            <div id="j-modal-name-edit-wrap" style="display: none;">
              <input type="text" class="c-modal-name-input" id="j-modal-name-input" placeholder="Full name..." />
              <p class="j-profile-name-error" style="display: none; margin: 0.25rem 0 0; font-size: 0.75rem; font-weight: 600; color: var(--maroon, #7F0303);"></p>
            </div>
            <?php endif; ?>
            <p class="c-modal-subtitle" id="j-modal-subtitle">Details</p>
          </div>
        </div>

        <!-- Close Button -->
        <button type="button" class="c-modal-close j-modal-close" id="j-modal-close-btn" aria-label="Close profile">
          <svg class="c-icon" width="20" height="20" aria-hidden="true"><use href="#icon-close"/></svg>
        </button>
      </div>

      <!-- Meta Pill Row -->
      <div class="c-modal-meta-row">
        <span class="c-id-pill" id="j-modal-id-pill" style="background: rgba(127, 199, 204, 0.2); color: var(--midnight, #0F414A);">ID-000</span>
        
        <!-- View Status Pill -->
        <span class="c-status-pill c-status-active" id="j-modal-status-pill">Active</span>

        <!-- Unknown record pill (Directory record unavailable) -->
        <span class="c-status-pill c-status-unknown-pill" id="j-modal-unknown-pill" style="display: none;">Directory record unavailable</span>
        
        <?php if ($allowEdit): ?>
        <!-- Edit Status Dropdown (Shown in edit mode) -->
        <div id="j-modal-status-select-wrap" style="display: none; min-width: 8.5rem;">
          <?php
          $dropdownId    = 'j-modal-status-dropdown';
          $options       = [
              ['value' => 'Active', 'label' => 'Active'],
              ['value' => 'Deactivated', 'label' => 'Deactivated']
          ];
          $selectedValue = 'Active';
          $dropdownLabel = 'Change account status';
          $dropdownClass = 'c-dropdown--status c-dropdown--status-active';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>
        <?php endif; ?>
      </div>
    </header>

    <!-- Modal Body Scroll Area -->
    <div class="c-modal-body" id="j-modal-body">
      
      <!-- Record Alert Banner (shown when isKnown === false) -->
      <div class="c-record-alert" id="j-modal-record-alert" style="display: none; margin-bottom: 1.25rem;">
        This person is referenced in the directory but does not have a full current-year record. Available details are shown below.
      </div>
      
      <!-- ===================================================================
           1. STUDENT PROFILE CONTAINER
           =================================================================== -->
      <div class="j-profile-role-section j-role-section-student" id="j-section-student" style="display: none;">
        
        <!-- 4 Student Sub-Tabs Capsule -->
        <div class="c-subtab-list" id="j-student-subtabs" role="tablist" aria-label="Student details navigation">
          <button type="button" role="tab" class="c-subtab-btn j-subtab-btn is-active-subtab" data-subtab="information" aria-selected="true">
            <span>Information</span>
          </button>
          <button type="button" role="tab" class="c-subtab-btn j-subtab-btn" data-subtab="academics" aria-selected="false">
            <span>Academics</span>
          </button>
          <button type="button" role="tab" class="c-subtab-btn j-subtab-btn" data-subtab="extracurriculars" aria-selected="false">
            <span>Extracurriculars</span>
          </button>
          <button type="button" role="tab" class="c-subtab-btn j-subtab-btn" data-subtab="achievements" aria-selected="false">
            <span>Achievements</span>
          </button>
        </div>

        <!-- Injected Component 1: Information Details (Student Information Tab) -->
        <?php 
        $renderInformationSection = 'student';
        require __DIR__ . '/_profile_information_tab.php'; 
        ?>

        <!-- Injected Component 2: Academics Sub-Tab Panel (Digital Record Book) -->
        <div class="c-subtab-panel j-subtab-panel" id="j-panel-academics" style="display: none;">
          <?php require __DIR__ . '/_digital_record_book.php'; ?>
        </div>

        <!-- Injected Component 3: Activities & Achievements Sub-Tab Panels -->
        <?php require __DIR__ . '/_profile_activities_tab.php'; ?>

      </div>

      <!-- ===================================================================
           2. TEACHER, PARENT & MANAGEMENT ROLE INFORMATION
           =================================================================== -->
      <?php 
      $renderInformationSection = 'roles';
      require __DIR__ . '/_profile_information_tab.php'; 
      ?>

    </div>

    <!-- Modal Footer Actions -->
    <footer class="c-modal-footer" id="j-modal-footer" style="<?= !$allowEdit ? 'display: none !important;' : '' ?>">
      <?php if ($allowEdit): ?>
      <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; width: 100%;">
        <!-- View Mode: Edit Profile Button -->
        <button type="button" class="c-btn-dark j-edit-profile-btn" id="j-btn-edit-profile">
          <svg class="c-icon" width="15" height="15"><use href="#icon-edit"/></svg>
          <span>Edit Profile</span>
        </button>

        <!-- Edit Mode Actions -->
        <button type="button" class="c-btn-ghost j-cancel-edit-btn" id="j-btn-cancel-profile" style="display: none;">
          <span>Cancel</span>
        </button>
        <button type="button" class="c-btn-accent c-tone-sky j-save-profile-btn" id="j-btn-save-profile" style="display: none;">
          <svg class="c-icon" width="15" height="15"><use href="#icon-check"/></svg>
          <span>Save Changes</span>
        </button>
      </div>
      <?php endif; ?>
    </footer>

  </div>
</div>
