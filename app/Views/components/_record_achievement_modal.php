<?php
/**
 * =========================================================================
 * L'ÉCOLE — RECORD ACHIEVEMENT & REVIEW ISSUE MODAL COMPONENT
 * =========================================================================
 * Universal modal for recording a student achievement or reviewing a
 * reported achievement issue from a student.
 * Fits the canonical L'École form design system with light brown/sand header
 * and warm border tones.
 * =========================================================================
 */
?>

<div class="c-modal-layer j-record-modal" id="j-record-modal" role="presentation" style="display: none;">
  <div class="c-modal-backdrop j-modal-close"></div>
  
  <div class="c-modal c-record-dialog" style="overflow: hidden; padding: 0; max-width: 580px; width: 100%; border-radius: 1.25rem; border: 1px solid var(--color-border, #EFE8DF); background: #ffffff;">
    
    <!-- Modal Header: Solid Light Brown Tone matching Canonical System (No Gradient Fade) -->
    <header class="c-modal__header" id="j-record-modal-header" style="background: #EDE8E2; border-bottom: 1px solid #D6CFC6; padding: 1.25rem 1.75rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
      <div class="c-modal__heading-group" style="display: flex; align-items: flex-start; gap: 0.875rem;">
        <div class="c-modal__icon-badge" id="j-record-modal-icon-badge" style="flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: var(--radius-xl, 0.875rem); background: rgba(255, 255, 255, 0.65); color: #8C5A24; display: flex; align-items: center; justify-content: center;">
          <svg id="j-rec-icon-award" class="c-icon" width="22" height="22"><use href="#icon-award"/></svg>
          <svg id="j-rec-icon-issue" class="c-icon" width="22" height="22" style="display: none; color: #B91C1C;"><use href="#icon-alertTriangle"/></svg>
        </div>
        <div>
          <p class="c-modal__eyebrow" id="j-record-modal-eyebrow" style="margin: 0; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.55);">Achievement Record</p>
          <h2 class="c-modal__title" id="j-record-modal-title" style="margin: 0.15rem 0 0 0; font-size: 1.25rem; font-weight: 800; color: #0F414A;">Record Achievement</h2>
          <p class="c-modal__description" id="j-record-modal-desc" style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: rgba(15, 65, 74, 0.7);">Add a verified student honor, award, or recognition.</p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close dialog" style="background: none; border: none; padding: 0.25rem; cursor: pointer; color: rgba(15,65,74,0.6); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
        <svg class="c-icon" width="20" height="20" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>

    <!-- Modal Form Body -->
    <div style="padding: 1.75rem 2rem 2rem 2rem; max-height: calc(90vh - 5.5rem); overflow-y: auto;">
      
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

      <form class="c-record-form" id="j-record-achievement-form" novalidate>
        
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
        <div class="c-field-group">
          <label class="c-field-label" for="j-rec-title">
            Achievement Title <span class="c-field-required">*</span>
          </label>
          <input type="text" id="j-rec-title" name="title" class="c-record-input" placeholder="e.g. Debating Society – Senior Member" required />
        </div>

        <!-- Field 3 & 4: Datepicker & Issuer (2 Columns) -->
        <div class="c-grid-2col">
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
        <div class="c-field-group">
          <label class="c-field-label" for="j-rec-desc">
            Description <span class="c-field-required">*</span>
          </label>
          <textarea id="j-rec-desc" name="description" rows="3" class="c-record-textarea" placeholder="Explain the achievement details, accomplishments, or missing verification..." required></textarea>
        </div>

        <!-- Field 6A: Upload Section (Active in Record Achievement Mode) - Spaced Uniformly -->
        <div class="c-field-group" id="j-rec-proof-upload-section" style="margin-top: 0.5rem;">
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

        <!-- Field 6B: Student Submitted Evidence View (Active in Review Issue Mode) - Spaced Uniformly -->
        <div class="c-field-group" id="j-rec-proof-view-section" style="display: none; margin-top: 0.5rem;">
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

        <!-- Modal Action Buttons -->
        <div class="c-record-actions" style="display: flex; gap: 0.875rem; justify-content: center; margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px solid var(--color-border, #EFE8DF);">
          <button type="button" class="c-record-btn-cancel j-modal-close">Cancel</button>
          <button type="submit" id="j-rec-submit-btn" class="c-record-btn-submit">Record Achievement</button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Simple Document/Image Preview Modal for Evidence Viewing -->
<div class="c-modal-layer" id="j-evidence-preview-modal" role="presentation" style="display: none; z-index: 2010;">
  <div class="c-modal-backdrop j-close-evidence-preview j-modal-close"></div>
  <div class="c-modal c-record-dialog" style="max-width: 600px; width: 100%; padding: 0; overflow: hidden; border-radius: 1.25rem; border: 1px solid var(--color-border, #EFE8DF); background: #ffffff;">
    <header class="c-modal__header" style="background: #EDE8E2; border-bottom: 1px solid #D6CFC6; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
      <h3 class="c-modal__title" style="font-size: 1.125rem; margin: 0; font-weight: 800; color: #0F414A;">Student Evidence Preview</h3>
      <button type="button" class="c-modal__close-btn j-close-evidence-preview j-modal-close" aria-label="Close preview" style="background: none; border: none; padding: 0.25rem; cursor: pointer; color: rgba(15,65,74,0.6); display: flex; align-items: center; justify-content: center;">
        <svg class="c-icon" width="20" height="20" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>
    <div style="padding: 1.75rem 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
      <div style="width: 4rem; height: 4rem; border-radius: 50%; background: rgba(228, 203, 169, 0.4); color: #8C5A24; display: flex; align-items: center; justify-content: center;">
        <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-fileText"/></svg>
      </div>
      <div>
        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F414A;">Appointment_Letter_2025.pdf</h4>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: rgba(15,65,74,0.7);">Official Appointment as Teal House Prefect (Jan 2025)</p>
      </div>
      <div style="background: #FAF8F5; border: 1px solid var(--color-border, #EFE8DF); border-radius: 0.5rem; padding: 1rem; width: 100%; box-sizing: border-box; text-align: left; font-size: 0.8125rem; color: #334155; line-height: 1.6;">
        <p style="margin: 0 0 0.5rem 0;"><strong>Issued by:</strong> Teal House Master, L'École International</p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Date:</strong> 15th January 2025</p>
        <p style="margin: 0;"><strong>Content:</strong> "This is to certify that Amara Silva has been duly appointed as House Prefect for Teal House for the academic year 2025, with responsibilities including co-curricular leadership and event coordination."</p>
      </div>
      <div style="width: 100%; display: flex; justify-content: flex-end; margin-top: 0.5rem;">
        <button type="button" class="c-record-btn-cancel j-close-evidence-preview j-modal-close" style="padding: 0.5rem 1.75rem;">Close Preview</button>
      </div>
    </div>
  </div>
</div>
