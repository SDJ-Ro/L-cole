<?php
/**
 * =========================================================================
 * L'ÉCOLE — ADD NOTICE PAGE COMPONENT
 * =========================================================================
 * Reusable full form view for creating announcements across authorized roles.
 * Exact 1:1 match with original prototype design and functionality.
 * 
 * Expects:
 *   - $currentRole       : string ('admin' | 'management' | 'teacher')
 *   - $allowedCategories : array (optional, defaults from NoticeModel)
 *   - $allowedAudiences  : array (optional, defaults from NoticeModel)
 *   - $formAction        : string (optional target URL)
 * =========================================================================
 */

$role       = $currentRole ?? 'admin';
$categories = $allowedCategories ?? ['Academic', 'Extracurricular', 'General', 'Administrative'];
$audiences  = $allowedAudiences ?? ['All', 'Students', 'Parents', 'Teachers', 'Management'];
$action     = $formAction ?? '';
?>

<div class="c-post-notice">
  <!-- Common Back Button Component (Solid Moss Green, Uniform Across All Forms) -->
  <button type="button" class="c-form-back j-go-notice-board">
    <svg class="c-icon" width="16" height="16"><use href="#icon-arrowLeft"/></svg>
    <span>Back</span>
  </button>

  <form class="c-notice-form" id="j-post-notice-form" action="<?= htmlspecialchars($action) ?>" method="POST" enctype="multipart/form-data" novalidate>
    
    <!-- Form Header (Signature Sky Blue #7FC7CC across all roles, matching original prototype) -->
    <header class="c-notice-form__header" style="background: #7FC7CC; padding: 1.5rem 1.75rem; color: #0F414A; border-top-left-radius: var(--radius-xl, 0.875rem); border-top-right-radius: var(--radius-xl, 0.875rem);">
      <h2 class="c-notice-form__header-title c-font-display" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0F414A;">Post New Notice</h2>
      <p class="c-notice-form__header-subtitle" style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: rgba(15, 65, 74, 0.85); font-weight: 500;">Create and publish an announcement to the school portal.</p>
    </header>

    <div class="c-notice-form__body" style="display: flex; flex-direction: column; gap: 1.5rem; padding: 1.75rem;">
      <!-- Info Banner with Complete Inline SVG -->
      <div class="c-info-banner" style="display: flex; align-items: center; gap: 0.75rem; border-radius: var(--radius-lg, 0.5rem); border: 1px solid rgba(127, 199, 204, 0.5); background: rgba(127, 199, 204, 0.15); padding: 0.875rem 1.125rem; color: #0F414A;">
        <svg class="c-icon c-info-banner__icon" width="20" height="20" aria-hidden="true" style="flex-shrink: 0; color: #3AAFA9;"><use href="#icon-info"/></svg>
        <p class="c-info-banner__text" style="margin: 0; font-size: 0.8125rem; font-weight: 600; color: #0F414A;">Notices appear immediately on the central board for the selected audience.</p>
      </div>

      <p class="c-form-message j-post-form-message" aria-live="polite" style="display: none;"></p>

      <div class="c-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr)); gap: 1.5rem;">
        
        <!-- Notice Title (Spans 2 cols) -->
        <div class="c-form-field" style="grid-column: 1 / -1; display: flex; flex-direction: column; gap: 0.375rem;">
          <label class="c-form-field__label" for="j-post-title" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.75);">Notice Title <span style="color: #7FC7CC;">*</span></label>
          <input type="text" id="j-post-title" name="title" class="c-text-input" placeholder="e.g. End of Term Examinations Schedule" required style="width: 100%; border-radius: var(--radius-lg, 0.5rem); border: 1px solid rgba(127, 199, 204, 0.4); background: #fff; padding: 0.75rem 1rem; font-size: 0.875rem; color: #0F414A; outline: none; box-sizing: border-box;" />
        </div>

        <!-- Category Dropdown -->
        <div class="c-form-field" style="display: flex; flex-direction: column; gap: 0.375rem;">
          <span class="c-form-field__label" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.75);">Category <span style="color: #7FC7CC;">*</span></span>
          <?php
          $dropdownId    = 'j-select-post-category';
          $dropdownLabel = 'Category';
          $placeholder   = 'Select category';
          $options       = $categories;
          $selectedValue = $categories[0] ?? 'Academic';
          $name          = 'category';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <!-- Target Audience Dropdown (Multi-Select using _dropdown.php) -->
        <div class="c-form-field" style="display: flex; flex-direction: column; gap: 0.375rem;">
          <span class="c-form-field__label" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.75);">Target Audience <span style="color: #7FC7CC;">*</span></span>
          <?php
          $dropdownId    = 'j-select-post-audience';
          $dropdownLabel = 'Target Audience';
          $placeholder   = 'Select audience…';
          $options       = $audiences;
          $selectedValue = ['All'];
          $name          = 'audience[]';
          $isMultiSelect = true;
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <!-- Message Body (Spans 2 cols) -->
        <div class="c-form-field" style="grid-column: 1 / -1; display: flex; flex-direction: column; gap: 0.375rem;">
          <label class="c-form-field__label" for="j-post-body" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.75);">Message Body <span style="color: #7FC7CC;">*</span></label>
          <textarea id="j-post-body" name="body" class="c-textarea" rows="6" placeholder="Write your announcement details here..." required style="width: 100%; min-height: 9rem; border-radius: var(--radius-lg, 0.5rem); border: 1px solid rgba(127, 199, 204, 0.4); background: #fff; padding: 0.75rem 1rem; font-size: 0.875rem; color: #0F414A; outline: none; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
        </div>

        <!-- File Upload Dropzone (Spans 2 cols) with Complete Inline Paperclip SVG -->
        <div class="c-form-field" style="grid-column: 1 / -1; display: flex; flex-direction: column; gap: 0.375rem;">
          <span class="c-form-field__label" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.75);">Attachment (Optional)</span>
          <input type="file" id="j-post-attachment-input" name="attachment" style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
          <button type="button" class="c-file-drop j-attachment-trigger" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; border-radius: var(--radius-xl, 0.875rem); border: 2px dashed rgba(127, 199, 204, 0.45); padding: 2rem 1.5rem; text-align: center; color: rgba(15, 65, 74, 0.5); background: transparent; cursor: pointer; transition: border-color 150ms ease, background-color 150ms ease;">
            <svg class="c-icon c-file-drop__icon" width="24" height="24" aria-hidden="true" style="color: #3AAFA9; margin-bottom: 0.5rem;"><use href="#icon-paperclip"/></svg>
            <span class="c-file-drop__filename j-attachment-name" style="font-size: 0.875rem; font-weight: 600; color: #0F414A;">Click to upload a file</span>
            <span class="c-file-drop__hint" style="margin-top: 0.25rem; font-size: 0.75rem; color: rgba(15, 65, 74, 0.5);">PDF, DOCX, JPG or PNG (max 5MB)</span>
          </button>
        </div>

        <!-- Actions Row: "Pin the notice" Checkbox on Left, Publish Button on Right As Is -->
        <div class="c-notice-actions-row" style="grid-column: 1 / -1; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 1.5rem !important; padding-top: 1.25rem; border-top: 1px solid rgba(127, 199, 204, 0.3); margin-top: 0.5rem; width: 100%;">
          
          <!-- Checkbox: Pin the notice -->
          <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; margin: 0 !important; flex-shrink: 0 !important;">
            <input type="hidden" id="j-post-pinned" name="pinned" value="0" />
            <div class="c-checkbox j-checkbox" id="j-post-pin-checkbox" role="checkbox" aria-checked="false" tabindex="0" style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; gap: 0.625rem; cursor: pointer;">
              <div class="c-checkbox__box" style="display: flex; align-items: center; justify-content: center; flex-shrink: 0; height: 1.25rem; width: 1.25rem; border-radius: 0.25rem; border: 1px solid rgba(127, 199, 204, 0.5); transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;">
                <svg class="c-icon" width="14" height="14"><use href="#icon-check"/></svg>
              </div>
              <label class="c-checkbox__label" style="cursor: pointer; user-select: none; font-size: 0.875rem; font-weight: 500; color: #0F414A; margin: 0; white-space: nowrap;">Pin the notice</label>
            </div>
          </div>

          <!-- Publish Notice Button -->
          <button type="submit" class="c-btn c-btn--dark" style="background: #0F414A; color: #ffffff; border: none; border-radius: 9999px; padding: 0.625rem 1.75rem; font-weight: 700; font-size: 0.875rem; display: inline-flex !important; align-items: center !important; gap: 0.5rem; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 65, 74, 0.2); transition: background-color 150ms ease, transform 150ms ease; margin: 0 !important; flex-shrink: 0 !important; white-space: nowrap;">
            <svg class="c-icon" width="16" height="16"><use href="#icon-send"/></svg>
            <span>Publish Notice</span>
          </button>

        </div>

      </div>
    </div>

  </form>
</div>
