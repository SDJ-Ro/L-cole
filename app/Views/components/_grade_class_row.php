<?php
/**
 * =========================================================================
 * L'ÉCOLE — GRADE CLASS ROW COMPONENT
 * =========================================================================
 * Renders an expandable row for an individual class section (e.g. '6-A').
 * Expects:
 *   - $className        : string e.g. '6-A'
 *   - $gradeId          : string e.g. 'g6'
 *   - $classTeacher     : string (optional)
 *   - $studentCount     : int (optional)
 *   - $subjects         : array of subject strings for this grade
 *   - $subjectTeachers  : array mapping subject => teacherName
 *   - $previewPlacement : 'left' | 'right' (default 'right')
 * =========================================================================
 */
$className        = $className ?? '6-A';
$gradeId          = $gradeId ?? 'g6';
$classTeacher     = $classTeacher ?? 'Assignment pending';
$studentCount     = $studentCount ?? 30;
$subjects         = $subjects ?? [];
$subjectTeachers  = $subjectTeachers ?? [];
$previewPlacement = $previewPlacement ?? 'right';

// Tone helper: 6-A -> tone-a, 6-B -> tone-b, etc.
$letter = strtoupper(substr($className, -1));
$toneMap = [
    'A' => 'c-tone-a',
    'B' => 'c-tone-b',
    'C' => 'c-tone-c',
    'D' => 'c-tone-d',
    'E' => 'c-tone-e',
];
$badgeTone = $toneMap[$letter] ?? 'c-tone-default';
?>

<details class="c-class-details" data-class-name="<?= htmlspecialchars($className) ?>">
  <summary class="c-class-row" data-class-name="<?= htmlspecialchars($className) ?>">
    <span class="c-class-row__badge <?= $badgeTone ?>">
      <?= htmlspecialchars($className) ?>
    </span>

    <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
      <div>
        <p class="c-class-row__teacher-label">Class teacher</p>
        <p class="c-class-row__teacher-name <?= ($classTeacher === 'Assignment pending') ? 'c-is-pending' : '' ?>" title="Click to assign class teacher"><?= htmlspecialchars($classTeacher) ?></p>
      </div>
      <button type="button" 
              class="c-class-row__edit-btn j-edit-class-btn" 
              data-grade-id="<?= htmlspecialchars($gradeId) ?>" 
              data-class-name="<?= htmlspecialchars($className) ?>" 
              aria-label="Edit <?= htmlspecialchars($className) ?>" 
              style="margin-left: 0.25rem;">
        <svg class="c-icon" width="14" height="14"><use href="#icon-edit"/></svg>
      </button>
    </div>

    <div style="margin-left: auto; display: flex; align-items: center; gap: 0.375rem; color: rgba(15, 65, 74, 0.7); font-size: 0.8125rem; font-weight: 600;">
      <span>Subject teachers</span>
      <div class="c-class-row__expand-icon" style="display: flex; align-items: center;">
        <svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
      </div>
    </div>
  </summary>

  <div class="c-class-subjects">
    <h4 class="c-class-subjects__title">Subject Assignments</h4>
    <div class="c-class-subjects__list">
      <?php foreach ($subjects as $subj): 
        $assigned = $subjectTeachers[$subj] ?? '';
      ?>
        <div class="c-subject-assignment-row">
          <div class="c-subject-assignment-row__name"><?= htmlspecialchars($subj) ?></div>
          <div class="c-teacher-field j-subject-teacher-field" 
               data-preview-placement="<?= htmlspecialchars($previewPlacement) ?>" 
               data-class-name="<?= htmlspecialchars($className) ?>" 
               data-subject="<?= htmlspecialchars($subj) ?>">
            <div style="position: relative;">
              <button type="button" class="c-teacher-field__trigger j-subject-teacher-trigger" aria-haspopup="listbox" aria-expanded="false">
                <span class="c-teacher-field__trigger-value j-subject-teacher-trigger-value <?= empty($assigned) ? 'c-is-placeholder' : '' ?>">
                  <?= htmlspecialchars(!empty($assigned) ? $assigned : 'Assignment pending') ?>
                </span>
                <svg class="c-icon c-teacher-field__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
              </button>
              <div class="c-teacher-field__popover j-subject-teacher-popover"></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</details>
