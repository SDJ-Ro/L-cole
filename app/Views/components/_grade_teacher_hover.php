<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER DETAIL HOVER CARD COMPONENT
 * =========================================================================
 * Reusable workload hover popover for teacher assignment fields.
 * Expects (optional):
 *   - $teacher     : array with keys ['id', 'name', 'subject', 'subjectClasses', 'extracurriculars']
 *   - $placement   : 'left' | 'right' (default 'right')
 * =========================================================================
 */
$placement = $placement ?? 'right';
$tName     = $teacher['name'] ?? '';
$tSubject  = $teacher['subject'] ?? 'Subject allocation pending';
$tClasses  = $teacher['subjectClasses'] ?? [];
$tExtras   = $teacher['extracurriculars'] ?? [];

// Initials
$words    = explode(' ', trim($tName));
$initials = '';
foreach ($words as $w) {
    if (!empty($w)) $initials .= strtoupper($w[0]);
}
$initials = substr($initials, 0, 2);
?>

<div class="c-workload-preview j-workload-preview c-workload-preview--<?= htmlspecialchars($placement) ?>" 
     role="tooltip" 
     aria-label="Teacher workload preview">
  <div class="c-workload-preview__head">
    <div class="c-teacher-menu__initials c-teacher-menu__initials--lg j-preview-initials">
      <?= htmlspecialchars($initials ?: 'T') ?>
    </div>
    <div style="min-width: 0; flex: 1 1 auto;">
      <h4 class="c-workload-preview__name j-preview-name"><?= htmlspecialchars($tName ?: 'Teacher Details') ?></h4>
      <p class="c-workload-preview__sub j-preview-subject"><?= htmlspecialchars($tSubject) ?></p>
    </div>
  </div>

  <div class="c-workload-preview__body">
    <div>
      <p class="c-workload-group__label">
        <svg class="c-icon" width="12" height="12"><use href="#icon-book"/></svg>
        Assigned Classes
      </p>
      <div class="c-workload-group__tags j-preview-classes">
        <?php if (!empty($tClasses)): ?>
          <?php foreach ($tClasses as $cls): ?>
            <span class="c-workload-tag"><?= htmlspecialchars($cls) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="c-workload-tag--empty">No classes assigned yet</span>
        <?php endif; ?>
      </div>
    </div>

    <div>
      <p class="c-workload-group__label">
        <svg class="c-icon" width="12" height="12"><use href="#icon-activity"/></svg>
        Extracurriculars
      </p>
      <div class="c-workload-group__tags j-preview-extras">
        <?php if (!empty($tExtras)): ?>
          <?php foreach ($tExtras as $ext): ?>
            <span class="c-workload-tag"><?= htmlspecialchars($ext) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="c-workload-tag--empty">None</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
