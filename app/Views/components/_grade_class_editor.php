<?php
/**
 * =========================================================================
 * L'ÉCOLE — GRADE CLASS INLINE EDITOR COMPONENT
 * =========================================================================
 * Inline extension form rendered when adding or editing a class section.
 * Expects:
 *   - $gradeId          : string e.g. 'g6'
 *   - $mode             : 'add' | 'edit'
 *   - $className        : string e.g. '6-A' (or 'new')
 *   - $studentCount     : int|string
 *   - $teacherName      : string (optional)
 *   - $previewPlacement : 'left' | 'right' (default 'right')
 *   - $staffList        : array of staff assignments (optional)
 * =========================================================================
 */
$mode        = $mode ?? 'add';
$gradeId     = $gradeId ?? 'g6';
$className   = $className ?? '';
$studentCount= $studentCount ?? '';
$teacherName = $teacherName ?? '';
$placement   = $previewPlacement ?? 'right';
$staffList   = $staffList ?? [];
$draftKey    = $gradeId . '-' . ($mode === 'add' ? 'new' : $className);
$prefix      = 'j-class-editor-' . preg_replace('/[^a-zA-Z0-9]/', '-', $draftKey);
?>

<form class="c-inline-editor j-inline-class-form" 
      data-grade-id="<?= htmlspecialchars($gradeId) ?>" 
      data-draft-key="<?= htmlspecialchars($draftKey) ?>" 
      data-mode="<?= htmlspecialchars($mode) ?>">
  <div class="c-inline-editor__grid">
    <div>
      <label class="c-field-label-sm" for="<?= $prefix ?>-section">Section name</label>
      <input class="c-input-sm j-class-name-input" 
             id="<?= $prefix ?>-section" 
             placeholder="e.g. 6-E" 
             value="<?= htmlspecialchars($className !== 'new' ? $className : '') ?>" 
             required />
    </div>

    <div>
      <label class="c-field-label-sm" for="<?= $prefix ?>-students">Students</label>
      <input class="c-input-sm j-class-students-input" 
             id="<?= $prefix ?>-students" 
             inputmode="numeric" 
             pattern="[0-9]*" 
             placeholder="e.g. 30" 
             value="<?= htmlspecialchars((string)$studentCount) ?>" />
      <p class="c-field-error-sm">Enter a non-negative whole number.</p>
    </div>

    <div class="c-teacher-field j-teacher-field" data-preview-placement="<?= htmlspecialchars($placement) ?>">
      <div class="c-teacher-field__head">
        <label class="c-field-label-sm" style="margin-bottom:0">Class teacher</label>
        <span class="c-teacher-field__hint">Hover a teacher to preview workload</span>
      </div>
      <div style="position:relative">
        <button type="button" class="c-teacher-field__trigger j-teacher-trigger" aria-haspopup="listbox" aria-expanded="false">
          <span class="c-teacher-field__trigger-value j-teacher-trigger-value <?= empty($teacherName) ? 'c-is-placeholder' : '' ?>">
            <?= htmlspecialchars($teacherName ?: 'Assignment pending') ?>
          </span>
          <svg class="c-icon c-teacher-field__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
        </button>
        <div class="c-teacher-field__popover j-teacher-popover"></div>
      </div>
      <div class="j-teacher-summary">
        <p class="c-teacher-field__empty-note">
          <?= !empty($teacherName) ? 'Class teacher assigned.' : 'No teacher selected — assignment can remain pending.' ?>
        </p>
      </div>
    </div>

    <div class="c-inline-editor__actions">
      <button type="submit" class="c-btn-save">
        <svg class="c-icon" width="14" height="14"><use href="#icon-check"/></svg>
        Save
      </button>
      <button type="button" class="c-btn-cancel j-cancel-class-btn">
        <svg class="c-icon" width="14" height="14"><use href="#icon-close"/></svg>
        Cancel
      </button>
    </div>
  </div>
</form>
