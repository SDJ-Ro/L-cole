<?php
/**
 * =========================================================================
 * L'ÉCOLE — UNIVERSAL FORM CARD COMPONENT
 * =========================================================================
 * Master reusable form wrapper component across all L'École portals.
 * 
 * Features:
 *   - Constant clean white body container (#ffffff)
 *   - Configurable role/section themed header:
 *       'sky'        : Canonical student & post-notice blue
 *       'sand'       : Warm cream/sand from Total Students dashboard card
 *       'sunshine'   : Teacher golden orange
 *       'terracotta' : Parent warm brick red
 *       'maroon'     : Management leadership crimson
 *       'moss'       : System deep forest green
 *   - Optional back navigation button
 *   - Optional draft pill indicator
 *   - Slots for body fields and action buttons
 * 
 * Expects:
 *   - $formId         (string) Form DOM element ID
 *   - $headerTheme    (string) Color variant ('sky' | 'sand' | 'sunshine' | 'terracotta' | 'maroon' | 'moss')
 *   - $formTitle      (string) Main title in the header
 *   - $formSubtitle   (string, optional) Descriptive subtitle in the header
 *   - $backLabel      (string, optional) E.g. 'Back to Notice Board', 'Back to Sports & Clubs'
 *   - $backActionClass(string, optional) JS class for click listener (e.g. 'j-go-notice-board')
 *   - $formAction     (string, optional) Target URL
 *   - $isMultipart    (bool, optional) Whether enctype="multipart/form-data" is required
 *   - $draftText      (string, optional) Text for draft badge pill
 * =========================================================================
 */

$formId          = $formId ?? 'j-form-card';
$headerTheme     = $headerTheme ?? 'sky';
$formTitle       = $formTitle ?? 'Form Details';
$formSubtitle    = $formSubtitle ?? '';
$backLabel       = $backLabel ?? null;
$backActionClass = $backActionClass ?? 'j-form-back';
$formAction      = $formAction ?? '';
$isMultipart     = $isMultipart ?? false;
$draftText       = $draftText ?? null;
$headerRightSlot = $headerRightSlot ?? null;
$isModal         = $isModal ?? false;
$cardClass       = $cardClass ?? '';
$headerEyebrow   = $headerEyebrow ?? null;
$headerIcon      = $headerIcon ?? null;
$headerIconClass = $headerIconClass ?? 'c-modal__icon-badge--sky';
?>

<?php if (!$isModal): ?>
<div class="c-form-page">
  
  <?php if (!empty($backLabel)): ?>
    <button type="button" class="c-form-back <?= htmlspecialchars($backActionClass) ?>">
      <svg class="c-icon" width="16" height="16"><use href="#icon-arrowLeft"/></svg>
      <span><?= htmlspecialchars($backLabel === 'Back' ? 'Back' : $backLabel) ?></span>
    </button>
  <?php endif; ?>
<?php endif; ?>

  <form class="c-form-card <?= htmlspecialchars($cardClass) ?>" id="<?= htmlspecialchars($formId) ?>" action="<?= htmlspecialchars($formAction) ?>" method="POST" <?= $isMultipart ? 'enctype="multipart/form-data"' : '' ?> novalidate>
    
    <!-- Role-Themed Header (Supports both standard cards and modal headers matching Calendar) -->
    <header class="c-form-header c-modal__header c-form-header--<?= htmlspecialchars($headerTheme) ?>" <?= $isModal ? 'style="display: flex !important; flex-direction: row !important; align-items: flex-start !important; justify-content: space-between !important; gap: 1rem !important;"' : '' ?>>
      <div class="c-modal__heading-group">
        <?php if (!empty($headerIcon)): ?>
          <div class="c-modal__icon-badge <?= htmlspecialchars($headerIconClass) ?>" aria-hidden="true">
            <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#<?= htmlspecialchars($headerIcon) ?>"/>
            </svg>
          </div>
        <?php endif; ?>
        <div>
          <?php if (!empty($headerEyebrow)): ?>
            <p class="c-modal__eyebrow"><?= htmlspecialchars($headerEyebrow) ?></p>
          <?php endif; ?>
          <h2 class="c-form-header-title c-modal__title c-font-display"><?= htmlspecialchars($formTitle) ?></h2>
          <?php if (!empty($formSubtitle)): ?>
            <p class="c-form-header-subtitle c-modal__description"><?= htmlspecialchars($formSubtitle) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div style="display: flex; align-items: flex-start; gap: 0.5rem; flex-shrink: 0; margin-left: auto;">
        <?php if (!empty($draftText)): ?>
          <span class="c-draft-pill"><?= htmlspecialchars($draftText) ?></span>
        <?php endif; ?>
        <?php if (!empty($headerRightSlot)): ?>
          <?= $headerRightSlot ?>
        <?php endif; ?>
      </div>
    </header>

    <!-- Constant Clean White Body Surface (#ffffff) -->
    <div class="c-form-body">
      <?php if (isset($formBodySlot)): ?>
        <?= $formBodySlot ?>
      <?php endif; ?>
    </div>

    <!-- Actions Footer -->
    <?php if (isset($formFooterSlot)): ?>
      <footer class="c-form-footer">
        <?= $formFooterSlot ?>
      </footer>
    <?php endif; ?>

  </form>

<?php if (!$isModal): ?>
</div>
<?php endif; ?>
