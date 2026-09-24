<?php
/**
 * MVC/app/Views/components/_student_certificate_modals.php
 * Reusable modal popups for the Student Character Certificate view.
 * 100% reuses the universal _form_card.php component with 'sand' header theme,
 * universal c-modal-layer, and embedded _dropdown.php components.
 */
?>

<!-- ============ REQUEST CHARACTER CERTIFICATE MODAL 1: FORM POP-UP ============ -->
<div class="c-modal-layer" id="rcc-overlay" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" id="rcc-backdrop" aria-label="Close modal"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="rcc-form-title">
    <?php
    ob_start();
    ?>
    <button type="button" class="c-modal__close-btn j-modal-close" id="rcc-close" aria-label="Close modal">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <use href="#icon-x"/>
      </svg>
    </button>
    <?php
    $headerRightSlot = ob_get_clean();

    ob_start();
    ?>
      <!-- Purpose Dropdown Reusing _dropdown.php -->
      <div class="c-form-field">
        <label class="c-form-field-label">Purpose <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
        <?php
        $dropdownId    = 'rcc-purpose-dropdown';
        $dropdownLabel = 'Purpose';
        $placeholder   = 'Select a purpose...';
        $options       = [
            ['value' => 'university',  'label' => 'University / Higher Education Admission'],
            ['value' => 'employment',  'label' => 'Employment / Job Application'],
            ['value' => 'visa',        'label' => 'Visa / Immigration'],
            ['value' => 'scholarship', 'label' => 'Scholarship Application'],
            ['value' => 'other',       'label' => 'Other']
        ];
        $selectedValue = '';
        $name          = 'purpose';
        require __DIR__ . '/_dropdown.php';
        ?>
        <p class="c-field-error" id="rcc-purpose-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Please select a purpose.</p>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <!-- Number of Copies -->
        <div class="c-form-field">
          <label class="c-form-field-label" for="rcc-copies">Number of Copies <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
          <input type="number" class="c-form-input" id="rcc-copies" min="1" max="5" value="1" required />
          <p class="c-field-error" id="rcc-copies-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Enter between 1 and 5 copies.</p>
        </div>

        <!-- Delivery Method Dropdown Reusing _dropdown.php -->
        <div class="c-form-field">
          <label class="c-form-field-label">Delivery Method <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
          <?php
          $dropdownId    = 'rcc-delivery-dropdown';
          $dropdownLabel = 'Delivery Method';
          $placeholder   = 'Select delivery method...';
          $options       = [
              ['value' => 'office', 'label' => 'Collect from School Office'],
              ['value' => 'email',  'label' => 'Email as Signed PDF']
          ];
          $selectedValue = '';
          $name          = 'delivery';
          require __DIR__ . '/_dropdown.php';
          ?>
          <p class="c-field-error" id="rcc-delivery-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Please select delivery method.</p>
        </div>
      </div>

      <!-- Additional Notes -->
      <div class="c-form-field">
        <label class="c-form-field-label" for="rcc-notes">Additional Notes</label>
        <textarea class="c-form-textarea" id="rcc-notes" rows="3" placeholder="Anything the Principal's Office should know (optional)..."></textarea>
      </div>
    <?php
    $formBodySlot = ob_get_clean();

    ob_start();
    ?>
      <button type="button" class="btn btn-outline j-modal-close" id="rcc-cancel-btn">Cancel</button>
      <button type="submit" class="btn btn-maroon" id="rcc-submit-btn">Submit Request</button>
    <?php
    $formFooterSlot = ob_get_clean();

    $formId        = 'rcc-form';
    $headerTheme   = 'cream';
    $headerIcon    = 'icon-fileCheck';
    $headerEyebrow = 'Student Portal · Official Document';
    $formTitle     = 'Request Character Certificate';
    $formSubtitle  = "Request your official, signed certificate reviewed by the Principal's Office.";
    $isModal       = true;
    $cardClass     = 'c-form-card--modal';
    require __DIR__ . '/_form_card.php';
    ?>
  </section>
</div>

<!-- ============ REQUEST CHARACTER CERTIFICATE MODAL 2: SUCCESS POP-UP ============ -->
<div class="c-modal-layer" id="rcc-success-overlay" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" id="rcc-success-backdrop" aria-label="Close modal"></button>
  <section class="c-modal" role="dialog" aria-modal="true" style="width: min(28rem, 90vw); max-width: 28rem; padding: 2rem; background: #ffffff; border-radius: var(--radius-2xl, 1.25rem); text-align: center; box-shadow: 0 20px 45px rgba(15, 65, 74, 0.22); border: 1px solid var(--color-border, #EFE8DF);">
    <div style="display: flex; flex-direction: column; align-items: center;">
      <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(228, 203, 169, 0.4); color: #0F414A; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
        <svg class="c-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-check"/>
        </svg>
      </div>
      <h3 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #0F414A;">Request Submitted</h3>
      <p style="font-size: 0.875rem; color: rgba(15, 65, 74, 0.75); line-height: 1.5; margin: 0 0 1.25rem 0;">Your request has been sent to the Principal's Office for review and signing. This usually takes 5–7 working days.</p>
      <div id="rcc-ref" style="font-size: 0.8125rem; font-weight: 700; color: #0F414A; background: #FAF7F2; border: 1px solid #E4CBA9; border-radius: 999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">Reference: CCR-2026-0412</div>
      <button type="button" class="btn btn-dark j-modal-close" id="rcc-done-btn" style="min-width: 120px;">Done</button>
    </div>
  </section>
</div>

<!-- ============ MISSING RECORD REQUEST MODAL 1: FORM POP-UP ============ -->
<div class="c-modal-layer" id="mrr-overlay" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" id="mrr-backdrop" aria-label="Close modal"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="mrr-form-title">
    <?php
    ob_start();
    ?>
    <button type="button" class="c-modal__close-btn j-modal-close" id="mrr-close" aria-label="Close modal">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <use href="#icon-x"/>
      </svg>
    </button>
    <?php
    $headerRightSlot = ob_get_clean();

    ob_start();
    ?>
      <!-- Record Type Dropdown Reusing _dropdown.php -->
      <div class="c-form-field">
        <label class="c-form-field-label">Record Type <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
        <?php
        $dropdownId    = 'mrr-type-dropdown';
        $dropdownLabel = 'Record Type';
        $placeholder   = 'Select a record type...';
        $options       = [
            ['value' => 'academic',    'label' => 'Academic Achievement'],
            ['value' => 'sports',      'label' => 'Sports / Club Enrollment or Position'],
            ['value' => 'certificate', 'label' => 'Certificate or Award'],
            ['value' => 'attendance',  'label' => 'Attendance Correction'],
            ['value' => 'other',       'label' => 'Other']
        ];
        $selectedValue = '';
        $name          = 'type';
        require __DIR__ . '/_dropdown.php';
        ?>
        <p class="c-field-error" id="mrr-type-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Please select a record type.</p>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="c-form-field">
          <label class="c-form-field-label" for="mrr-year">Academic Year / Grade <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
          <input type="text" class="c-form-input" id="mrr-year" placeholder="e.g. 2025 or Grade 12" required />
          <p class="c-field-error" id="mrr-year-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Please specify the year.</p>
        </div>

        <div class="c-form-field">
          <label class="c-form-field-label" for="mrr-item-title">Title / Role <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
          <input type="text" class="c-form-input" id="mrr-item-title" placeholder="e.g. Under-17 Captain" required />
          <p class="c-field-error" id="mrr-item-title-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Please enter a title or role.</p>
        </div>
      </div>

      <div class="c-form-field">
        <label class="c-form-field-label" for="mrr-desc">Details & Reason <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
        <textarea class="c-form-textarea" id="mrr-desc" rows="3" placeholder="Explain what is missing and why it should appear on your character certificate..." required></textarea>
        <p class="c-field-error" id="mrr-desc-error" style="display: none; color: var(--terracotta, #AF5031); font-size: 0.75rem; margin-top: 0.25rem;">Please provide details.</p>
      </div>

      <!-- Automatic Routing Notice -->
      <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #0F414A; background: #F7F3EC; border: 1px solid #E4CBA9; border-radius: var(--radius-md, 0.5rem); padding: 0.75rem 1rem;">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-info"/>
        </svg>
        <span>This request will be automatically routed to your Class Teacher (M. Perera) for initial verification.</span>
      </div>
    <?php
    $formBodySlot = ob_get_clean();

    ob_start();
    ?>
      <button type="button" class="btn btn-outline j-modal-close" id="mrr-cancel-btn">Cancel</button>
      <button type="submit" class="btn btn-maroon" id="mrr-submit-btn">Submit Request</button>
    <?php
    $formFooterSlot = ob_get_clean();

    $formId        = 'mrr-form';
    $headerTheme   = 'cream';
    $headerIcon    = 'icon-award';
    $headerEyebrow = 'Student Portal · Record Correction';
    $formTitle     = 'Submit Missing Record Request';
    $formSubtitle  = "Tell us what's missing from your record. We'll automatically route it to the right teacher.";
    $isModal       = true;
    $cardClass     = 'c-form-card--modal';
    require __DIR__ . '/_form_card.php';
    ?>
  </section>
</div>

<!-- ============ MISSING RECORD REQUEST MODAL 2: SUCCESS POP-UP ============ -->
<div class="c-modal-layer" id="mrr-success-overlay" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" id="mrr-success-backdrop" aria-label="Close modal"></button>
  <section class="c-modal" role="dialog" aria-modal="true" style="width: min(28rem, 90vw); max-width: 28rem; padding: 2rem; background: #ffffff; border-radius: var(--radius-2xl, 1.25rem); text-align: center; box-shadow: 0 20px 45px rgba(15, 65, 74, 0.22); border: 1px solid var(--color-border, #EFE8DF);">
    <div style="display: flex; flex-direction: column; align-items: center;">
      <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(228, 203, 169, 0.4); color: #0F414A; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
        <svg class="c-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-check"/>
        </svg>
      </div>
      <h3 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #0F414A;">Record Request Submitted</h3>
      <p style="font-size: 0.875rem; color: rgba(15, 65, 74, 0.75); line-height: 1.5; margin: 0 0 1.25rem 0;">Your request has been routed to your teacher for review. Once verified, it will be added to your official character certificate.</p>
      <div id="mrr-ref" style="font-size: 0.8125rem; font-weight: 700; color: #0F414A; background: #FAF7F2; border: 1px solid #E4CBA9; border-radius: 999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">Reference: MRR-2026-0815</div>
      <button type="button" class="btn btn-dark j-modal-close" id="mrr-done-btn" style="min-width: 120px;">Done</button>
    </div>
  </section>
</div>
