<?php
/**
 * =========================================================================
 * L'ÉCOLE — REUSABLE EXPORT PDF BUTTON COMPONENT
 * =========================================================================
 * Standalone button component for exporting reports, academic record books,
 * transcripts, or certificates into an official school PDF document.
 *
 * Parameters:
 *   - $buttonId       : string (optional, defaults to 'j-export-pdf-btn')
 *   - $buttonLabel    : string (optional, defaults to 'Export PDF')
 *   - $targetSelector : string (optional, selector of container to export, e.g. '.j-recordbook-container')
 *   - $documentTitle  : string (optional, title in exported document, e.g. 'Digital Record Book')
 *   - $filename       : string (optional, download filename, e.g. 'Student_Record_Book.pdf')
 *   - $tone           : string (optional: 'sky' | 'maroon' | 'sunshine' | 'terracotta', default: 'sky')
 *   - $buttonClass    : string (optional, extra CSS classes)
 * =========================================================================
 */

$btnId       = $buttonId ?? ('j-export-btn-' . uniqid());
$btnLabel    = $buttonLabel ?? 'Export PDF';
$targetSel   = $targetSelector ?? '.j-recordbook-container';
$docTitle    = $documentTitle ?? 'Official Student Record Book';
$fileTitle   = $filename ?? 'Student_Record_Book.pdf';
$btnTone     = $tone ?? 'sky';
$extraClass  = $buttonClass ?? '';
?>

<button 
  type="button" 
  class="export-btn export-btn--<?= htmlspecialchars($btnTone) ?> j-export-pdf-btn <?= htmlspecialchars($extraClass) ?>" 
  id="<?= htmlspecialchars($btnId) ?>"
  data-action="export-pdf"
  data-export-target="<?= htmlspecialchars($targetSel) ?>"
  data-export-title="<?= htmlspecialchars($docTitle) ?>"
  data-export-filename="<?= htmlspecialchars($fileTitle) ?>"
  data-tone="<?= htmlspecialchars($btnTone) ?>"
  aria-label="Export report as PDF"
>
  <svg class="export-btn__icon" viewBox="0 0 24 24"><use href="#icon-download"/></svg>
  <span class="export-btn__text"><?= htmlspecialchars($btnLabel) ?></span>
</button>
