<?php
/**
 * =========================================================================
 * L'ÉCOLE — REUSABLE IMAGE PICKER COMPONENT
 * =========================================================================
 * Standardized component for selecting/uploading a single image from the
 * user's computer with live preview, styled trigger button, and hidden file input.
 * 
 * Variables:
 *   - $pickerId           (string, required) Base ID prefix (e.g. 'j-new-ach-cover')
 *   - $pickerLabel        (string, optional) Section heading text (default: 'Cover photo')
 *   - $pickerHint         (string, optional) Helper text below button (default: 'Select an image file to display.')
 *   - $buttonText         (string, optional) Button label (default: 'Choose Image')
 *   - $initialImage       (string, optional) Existing image data URL or path (default: '')
 *   - $inputName          (string, optional) Form input name attribute (default: '')
 *   - $accept             (string, optional) File type filter (default: 'image/*')
 *   - $showSectionHeading (bool, optional)   Whether to render the section header (default: true)
 *   - $headingIcon        (string, optional) Sprite icon ID for header (default: 'icon-image')
 * =========================================================================
 */

$pickerId           = $pickerId ?? 'j-image-picker-' . uniqid();
$pickerLabel        = $pickerLabel ?? 'Cover photo';
$pickerHint         = $pickerHint ?? 'Select an image file to display on the card.';
$buttonText         = $buttonText ?? 'Choose Cover Photo';
$initialImage       = $initialImage ?? '';
$inputName          = $inputName ?? '';
$accept             = $accept ?? 'image/*';
$showSectionHeading = $showSectionHeading ?? true;
$headingIcon        = $headingIcon ?? 'icon-image';
?>

<section class="j-ex-94 c-image-picker-wrap" id="<?= htmlspecialchars($pickerId) ?>-wrap">
  <?php if ($showSectionHeading): ?>
    <div class="c-section-heading">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <use href="#<?= htmlspecialchars($headingIcon) ?>"/>
      </svg>
      <h2><?= htmlspecialchars($pickerLabel) ?></h2>
    </div>
  <?php endif; ?>

  <div class="j-ex-117">
    <!-- Live Preview Box -->
    <div class="j-ex-118 c-image-picker__preview" id="<?= htmlspecialchars($pickerId) ?>-preview">
      <?php if (!empty($initialImage)): ?>
        <img class="j-ex-124" src="<?= htmlspecialchars($initialImage) ?>" alt="Image preview" />
      <?php else: ?>
        <span class="j-ex-119">No cover photo selected</span>
      <?php endif; ?>
    </div>

    <!-- Actions & Hidden Input -->
    <div>
      <label class="c-btn c-btn--ghost c-btn--sm j-ex-120" for="<?= htmlspecialchars($pickerId) ?>-input">
        <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <use href="#icon-imagePlus"/>
        </svg>
        <span><?= htmlspecialchars($buttonText) ?></span>
      </label>

      <input 
        class="c-visually-hidden" 
        id="<?= htmlspecialchars($pickerId) ?>-input" 
        type="file" 
        accept="<?= htmlspecialchars($accept) ?>" 
        <?= !empty($inputName) ? 'name="' . htmlspecialchars($inputName) . '"' : '' ?> 
      />

      <p class="j-ex-121"><?= htmlspecialchars($pickerHint) ?></p>
    </div>
  </div>
</section>
