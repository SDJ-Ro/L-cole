<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER DETAIL HOVER CARD COMPONENT
 * =========================================================================
 * Workload hover popover for teacher assignment fields.
 * Structured into 3 clear sections:
 *   1. Class teacher
 *   2. Extracurriculars in-charge of
 *   3. Subject teacher for (showing subjects and classes taught)
 * Header displays name + qualified/teaching subjects.
 * =========================================================================
 */
$placement      = $placement ?? 'right';
$tName          = $teacher['name'] ?? '';
$tQualification = $teacher['qualification'] ?? ($teacher['subject'] ?? 'Subject allocation pending');
$tClassTeacher  = $teacher['classTeacher'] ?? '';
$tExtras        = $teacher['extracurriculars'] ?? ($teacher['extras'] ?? []);
$tSubjects      = $teacher['subjects'] ?? [];

// Backward compatibility: if $teacher['subjectClasses'] exists without structured $subjects
if (empty($tSubjects) && !empty($teacher['subjectClasses'])) {
    $tSubjects = [
        [
            'subject' => $teacher['subject'] ?? 'General',
            'classes' => $teacher['subjectClasses'],
        ]
    ];
}

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
      <p class="c-workload-preview__sub j-preview-qualification j-preview-subject"><?= htmlspecialchars($tQualification) ?></p>
    </div>
  </div>

  <div class="c-workload-preview__body">
    <!-- SECTION 1: CLASS TEACHER -->
    <div class="c-workload-group">
      <p class="c-workload-group__label">
        <svg class="c-icon" width="12" height="12"><use href="#icon-graduationCap"/></svg>
        Class Teacher
      </p>
      <div class="c-workload-group__tags j-preview-class-teacher">
        <?php if (!empty($tClassTeacher)): ?>
          <span class="c-workload-tag c-workload-tag--class-teacher">Class <?= htmlspecialchars($tClassTeacher) ?></span>
        <?php else: ?>
          <span class="c-workload-tag--empty">Unassigned</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- SECTION 2: EXTRACURRICULARS IN-CHARGE OF -->
    <div class="c-workload-group">
      <p class="c-workload-group__label">
        <svg class="c-icon" width="12" height="12"><use href="#icon-trophy"/></svg>
        Extracurriculars In-charge of
      </p>
      <div class="c-workload-group__tags j-preview-extras">
        <?php if (!empty($tExtras)): ?>
          <?php foreach ($tExtras as $ext): ?>
            <span class="c-workload-tag c-workload-tag--extra"><?= htmlspecialchars($ext) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="c-workload-tag--empty">None</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- SECTION 3: SUBJECT TEACHER FOR -->
    <div class="c-workload-group">
      <p class="c-workload-group__label">
        <svg class="c-icon" width="12" height="12"><use href="#icon-book"/></svg>
        Subject Teacher for
      </p>
      <div class="c-workload-subjects j-preview-subjects j-preview-classes">
        <?php if (!empty($tSubjects)): ?>
          <?php foreach ($tSubjects as $subjItem): ?>
            <?php 
              $sName    = is_array($subjItem) ? ($subjItem['subject'] ?? '') : (string)$subjItem;
              $sClasses = is_array($subjItem) ? ($subjItem['classes'] ?? []) : [];
            ?>
            <div class="c-workload-subject-item">
              <span class="c-workload-subject-name"><?= htmlspecialchars($sName) ?></span>
              <div class="c-workload-subject-classes">
                <?php if (!empty($sClasses)): ?>
                  <?php foreach ($sClasses as $c): ?>
                    <span class="c-workload-tag"><?= htmlspecialchars($c) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="c-workload-tag--empty">General</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="c-workload-tag--empty">No subjects assigned yet</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
