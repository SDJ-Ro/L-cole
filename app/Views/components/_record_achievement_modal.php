<?php
/**
 * =========================================================================
 * L'ÉCOLE — RECORD ACHIEVEMENT, REVIEW ISSUE & VIEW ACHIEVEMENTS MODAL COMPONENT
 * =========================================================================
 * Universal modal layout for viewing student achievements, recording an
 * achievement, or reviewing a reported achievement issue from a student.
 * Fits the canonical L'École form design system (c-form-card c-form-card--modal)
 * with sand header and standard side padding (1.5rem 1.75rem).
 * =========================================================================
 */
?>

<!-- =======================================================================
     1. RECORD ACHIEVEMENT & REVIEW ISSUE MODAL
     ======================================================================= -->
<div class="c-modal-layer j-record-modal" id="j-record-modal" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-close" aria-label="Close dialog"></button>
  
  <div class="c-modal c-form-card c-form-card--modal" style="width: min(38rem, 94vw); max-width: 38rem; margin: auto;">
    
    <!-- Modal Header: Solid Sand Tone matching Canonical Form Component Layout -->
    <header class="c-form-header c-modal__header c-form-header--sand" id="j-record-modal-header" style="display: flex !important; flex-direction: row !important; align-items: flex-start !important; justify-content: space-between !important; gap: 1rem !important; padding: 1.25rem 1.75rem;">
      <div class="c-modal__heading-group" style="display: flex; align-items: flex-start; gap: 0.875rem;">
        <div class="c-modal__icon-badge c-modal__icon-badge--sand" id="j-record-modal-icon-badge" style="flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: var(--radius-xl, 0.875rem); background: rgba(255, 255, 255, 0.65); color: #8C5A24; display: flex; align-items: center; justify-content: center;">
          <svg id="j-rec-icon-award" class="c-icon" width="22" height="22"><use href="#icon-award"/></svg>
          <svg id="j-rec-icon-issue" class="c-icon" width="22" height="22" style="display: none; color: #B91C1C;"><use href="#icon-alertTriangle"/></svg>
        </div>
        <div>
          <p class="c-modal__eyebrow" id="j-record-modal-eyebrow" style="margin: 0; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.55);">Achievement Record</p>
          <h2 class="c-form-header-title c-modal__title c-font-display" id="j-record-modal-title" style="margin: 0.15rem 0 0 0; font-size: 1.25rem; font-weight: 800; color: #0F414A;">Record Achievement</h2>
          <p class="c-form-header-subtitle c-modal__description" id="j-record-modal-desc" style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: rgba(15, 65, 74, 0.7);">Add a verified student honor, award, or recognition.</p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close dialog">
        <svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>

    <form class="c-record-form" id="j-record-achievement-form" novalidate style="display: flex; flex-direction: column; margin: 0;">
      
      <!-- Modal Form Body with Standard 1.5rem 1.75rem Side Padding -->
      <div class="c-form-body" style="padding: 1.5rem 1.75rem; max-height: calc(90vh - 140px); overflow-y: auto;">
        
        <!-- Issue Alert Callout (Shown only in Review Issue mode) -->
        <div id="j-rec-issue-callout" style="display: none; background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: var(--radius-lg, 0.5rem); padding: 0.875rem 1rem; color: #991B1B; margin-bottom: 1.25rem;">
          <div style="display: flex; align-items: flex-start; gap: 0.625rem;">
            <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;"><use href="#icon-alertTriangle"/></svg>
            <div style="font-size: 0.8125rem; line-height: 1.45;">
              <strong style="display: block; font-weight: 700; margin-bottom: 2px;">Reported Issue from Student</strong>
              <span id="j-rec-issue-message">Student reported that their House Prefect appointment was missing from their profile records.</span>
            </div>
          </div>
        </div>
        
        <!-- Visible Student Context Banner -->
        <div class="c-student-context-pill" id="j-rec-student-pill" style="display: flex; align-items: center; justify-content: space-between; background: #FAF8F5; border: 1px solid var(--color-border, #EFE8DF); border-radius: var(--radius-lg, 0.5rem); padding: 0.625rem 1rem; margin-bottom: 1.25rem;">
          <div style="display: flex; align-items: center; gap: 0.625rem;">
            <div style="width: 2.25rem; height: 2.25rem; border-radius: 50%; background: #0F414A; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8125rem;" id="j-rec-display-avatar">AS</div>
            <div>
              <p style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #0F414A;" id="j-rec-display-name">Student Name</p>
              <p style="margin: 0.1rem 0 0 0; font-size: 0.75rem; color: rgba(15, 65, 74, 0.65);" id="j-rec-display-index">Index: S0000/0000</p>
            </div>
          </div>
          <span class="c-tag" id="j-rec-display-type" style="background: #EAE6DF; color: #0F414A; font-weight: 700; font-size: 0.6875rem; padding: 0.2rem 0.5rem; border-radius: 4px;">STUDENT</span>
        </div>

        <!-- Hidden Student Context -->
        <input type="hidden" id="j-rec-student-name" name="student_name" value="" />
        <input type="hidden" id="j-rec-student-index" name="student_index" value="" />

        <!-- Field 1: Achievement Type Dropdown -->
        <div class="c-field-group">
          <label class="c-field-label">
            Achievement Type <span class="c-field-required">*</span>
          </label>
          <?php
          $dropdownId    = 'j-rec-type-dropdown';
          $options       = [
              ['value' => 'Academic',           'label' => 'Academic'],
              ['value' => 'Sports',             'label' => 'Sports'],
              ['value' => 'Clubs & Societies',  'label' => 'Clubs & Societies'],
              ['value' => 'Leadership',         'label' => 'Leadership']
          ];
          $selectedValue = 'Academic';
          $placeholder   = 'Select an achievement type';
          $labelPrefix   = 'Type';
          $dropdownLabel = 'Select an achievement type';
          require __DIR__ . '/_dropdown.php';
          ?>
          <input type="hidden" name="achievement_type" id="j-rec-type-hidden" value="Academic" required />
        </div>

        <!-- Field 2: Title -->
        <div class="c-field-group" style="margin-top: 1rem;">
          <label class="c-field-label" for="j-rec-title">
            Achievement Title <span class="c-field-required">*</span>
          </label>
          <input type="text" id="j-rec-title" name="title" class="c-record-input" placeholder="e.g. Debating Society – Senior Member" required />
        </div>

        <!-- Field 3 & 4: Datepicker & Issuer (2 Columns) -->
        <div class="c-grid-2col" style="margin-top: 1rem;">
          <div class="c-field-group">
            <label class="c-field-label">
              Date it Occurred <span class="c-field-required">*</span>
            </label>
            <?php
            $datepickerId  = 'j-rec-datepicker';
            $inputName     = 'date';
            $selectedValue = date('Y-m-d');
            $placeholder   = 'Select date';
            $tone          = 'sand';
            require __DIR__ . '/_datepicker.php';
            ?>
          </div>
          <div class="c-field-group">
            <label class="c-field-label" for="j-rec-issuer">
              Verified / Issued By
            </label>
            <input type="text" id="j-rec-issuer" name="issuer" class="c-record-input" placeholder="e.g. Mr. Silva, Club Patron" />
          </div>
        </div>

        <!-- Field 5: Description -->
        <div class="c-field-group" style="margin-top: 1rem;">
          <label class="c-field-label" for="j-rec-desc">
            Description <span class="c-field-required">*</span>
          </label>
          <textarea id="j-rec-desc" name="description" rows="3" class="c-record-textarea" placeholder="Explain the achievement details, accomplishments, or missing verification..." required></textarea>
        </div>

        <!-- Field 6A: Upload Section (Active in Record Achievement Mode) -->
        <div class="c-field-group" id="j-rec-proof-upload-section" style="margin-top: 1rem;">
          <label class="c-field-label" style="margin-bottom: 0.25rem;">
            Supporting Proof <span class="c-field-required">*</span>
          </label>
          <div class="c-upload-box j-upload-box" id="j-rec-upload-box" tabindex="0" role="button" aria-label="Upload supporting proof" style="margin-top: 0.375rem;">
            <svg class="c-icon c-upload-box__icon" width="24" height="24"><use href="#icon-upload"/></svg>
            <p class="c-upload-box__title"><strong>Click to upload</strong> or drag and drop</p>
            <p class="c-upload-box__subtitle">Photos or PDFs, up to 10MB each</p>
            <input type="file" id="j-rec-file-input" style="display: none;" accept="image/*,.pdf" />
            <div class="c-upload-box__filename j-upload-filename" id="j-rec-upload-filename" style="display: none;"></div>
          </div>
        </div>

        <!-- Field 6B: Student Submitted Evidence View (Active in Review Issue Mode) -->
        <div class="c-field-group" id="j-rec-proof-view-section" style="display: none; margin-top: 1rem;">
          <label class="c-field-label" style="margin-bottom: 0.25rem;">
            Student's Submitted Evidence
          </label>
          <div class="c-evidence-card" style="display: flex; align-items: center; justify-content: space-between; padding: 0.875rem 1.125rem; background: #FAF8F5; border: 1px solid var(--color-border, #EFE8DF); border-radius: var(--radius-lg, 0.75rem); gap: 1rem; margin-top: 0.375rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
              <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background: rgba(228, 203, 169, 0.4); color: #8C5A24; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20"><use href="#icon-fileText"/></svg>
              </div>
              <div style="min-width: 0;">
                <p id="j-rec-evidence-filename" style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #0F414A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Appointment_Letter_2025.pdf</p>
                <p id="j-rec-evidence-meta" style="margin: 0.125rem 0 0 0; font-size: 0.75rem; color: rgba(15, 65, 74, 0.65);">PDF Document • 1.2 MB • Attached by student</p>
              </div>
            </div>
            <button type="button" id="j-rec-evidence-link" class="c-btn" style="flex-shrink: 0; padding: 0.45rem 1rem; background: #0F414A; color: #ffffff; border: none; border-radius: 9999px; font-size: 0.8125rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.375rem; cursor: pointer; transition: background 150ms ease;">
              <svg width="14" height="14"><use href="#icon-eye"/></svg>
              <span>View File</span>
            </button>
          </div>
        </div>

      </div>

      <!-- Actions Footer with Standard 1rem 1.75rem Padding -->
      <footer class="c-form-footer" style="padding: 1rem 1.75rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; border-top: 1px solid var(--color-border, #EFE8DF); background: #FAF8F5;">
        <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
        <button type="submit" id="j-rec-submit-btn" class="c-btn c-btn--solid c-tone-sunshine">Record Achievement</button>
      </footer>

    </form>
  </div>
</div>

<!-- =======================================================================
     2. STUDENT EVIDENCE PREVIEW MODAL
     ======================================================================= -->
<div class="c-modal-layer" id="j-evidence-preview-modal" role="presentation" style="display: none; z-index: 2010;">
  <button type="button" class="c-modal-backdrop j-close-evidence-preview j-modal-close" aria-label="Close dialog"></button>
  <div class="c-modal c-form-card c-form-card--modal" style="width: min(36rem, 94vw); max-width: 36rem; margin: auto;">
    <header class="c-form-header c-modal__header c-form-header--sand" style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; padding: 1.25rem 1.75rem;">
      <h3 class="c-form-header-title c-modal__title c-font-display" style="font-size: 1.15rem; margin: 0; font-weight: 800; color: #0F414A;">Student Evidence Preview</h3>
      <button type="button" class="c-modal__close-btn j-close-evidence-preview j-modal-close" aria-label="Close preview">
        <svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>
    <div class="c-form-body" style="padding: 1.5rem 1.75rem; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
      <div style="width: 4rem; height: 4rem; border-radius: 50%; background: rgba(228, 203, 169, 0.4); color: #8C5A24; display: flex; align-items: center; justify-content: center;">
        <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-fileText"/></svg>
      </div>
      <div style="text-align: center;">
        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F414A;">Appointment_Letter_2025.pdf</h4>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: rgba(15,65,74,0.7);">Official Appointment as Teal House Prefect (Jan 2025)</p>
      </div>
      <div style="background: #FAF8F5; border: 1px solid var(--color-border, #EFE8DF); border-radius: 0.5rem; padding: 1rem; width: 100%; box-sizing: border-box; text-align: left; font-size: 0.8125rem; color: #334155; line-height: 1.6;">
        <p style="margin: 0 0 0.5rem 0;"><strong>Issued by:</strong> Teal House Master, L'École International</p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Date:</strong> 15th January 2025</p>
        <p style="margin: 0;"><strong>Content:</strong> "This is to certify that Amara Silva has been duly appointed as House Prefect for Teal House for the academic year 2025, with responsibilities including co-curricular leadership and event coordination."</p>
      </div>
    </div>
    <footer class="c-form-footer" style="padding: 1rem 1.75rem; display: flex; justify-content: flex-end; border-top: 1px solid var(--color-border, #EFE8DF); background: #FAF8F5;">
      <button type="button" class="c-btn c-btn--ghost j-close-evidence-preview j-modal-close" style="padding: 0.5rem 1.75rem;">Close Preview</button>
    </footer>
  </div>
</div>
