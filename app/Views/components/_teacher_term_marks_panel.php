<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER TERM MARKS ENTRY PANEL COMPONENT
 * =========================================================================
 * Right-docked side panel for viewing & entering student subject marks.
 * Fixed in place on the side (no panel scrolling).
 * Starts from the top cards' card line part.
 * Background tinted with the faded tone of the active student's card.
 * =========================================================================
 */

$subjects = $subjects ?? [
    'English',
    'Mathematics',
    'Science',
    'Humanities',
    'Sinhala / Tamil',
    'ICT'
];
?>

<aside class="c-term-marks-panel j-term-marks-panel c-panel-tint--slate" id="j-term-marks-panel" aria-label="Student Marks Entry">
  <!-- Top Bar with Close Button -->
  <div class="c-term-marks-panel__top-bar">
    <button type="button" class="c-term-marks-panel__close-btn j-close-marks-panel" aria-label="Close marks panel" title="Close panel">
      <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-close"/>
      </svg>
    </button>
  </div>

  <!-- Student Profile Card Banner -->
  <div class="c-term-marks-panel__student-banner j-panel-student-banner c-bg-card-slate">
    <div class="c-term-marks-panel__avatar-wrap">
      <div class="c-term-marks-panel__avatar-initials j-panel-student-initials">NP</div>
      <img class="c-term-marks-panel__avatar-img j-panel-student-avatar" src="" alt="Student Avatar" style="display: none;" />
    </div>
    <div class="c-term-marks-panel__student-meta">
      <h3 class="c-term-marks-panel__student-name j-panel-student-name">Student Name</h3>
      <div class="c-term-marks-panel__student-sub">
        <span class="c-term-marks-panel__student-index j-panel-student-index">2021/0456</span>
        <span class="c-term-marks-panel__bullet">•</span>
        <span class="c-term-marks-panel__student-class j-panel-student-class">Class 6-A</span>
      </div>
    </div>
  </div>

  <!-- Form Body -->
  <form class="c-term-marks-panel__form j-term-marks-form" onsubmit="return false;">
    <!-- Sort / Subject Filter Dropdown -->
    <div class="c-term-marks-panel__sort-row">
      <span class="c-term-marks-panel__sort-label">FILTER SUBJECT</span>
      <div class="c-select c-select--compact j-panel-subject-select" id="j-panel-subject-filter">
        <button type="button" class="c-select__trigger j-panel-subject-trigger" aria-haspopup="listbox" aria-expanded="false">
          <span class="c-select__value j-panel-subject-value">All</span>
          <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
        </button>
        <div class="c-select__menu j-panel-subject-menu" role="listbox">
          <button type="button" class="c-select__option c-is-selected" data-value="All">
            <span>All</span>
            <svg class="c-icon c-select__option-check" width="14" height="14" aria-hidden="true"><use href="#icon-check"/></svg>
          </button>
          <?php foreach ($subjects as $subj): ?>
            <button type="button" class="c-select__option" data-value="<?= htmlspecialchars($subj) ?>">
              <span><?= htmlspecialchars($subj) ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Subject Marks Grid -->
    <div class="c-term-marks-panel__grid j-subject-inputs-container">
      <?php foreach ($subjects as $subj): ?>
        <div class="c-subject-input-row j-subject-row" data-subject="<?= htmlspecialchars($subj) ?>">
          <span class="c-subject-input-row__name"><?= htmlspecialchars($subj) ?></span>
          <div class="c-subject-input-row__field">
            <input type="number"
                   class="c-mark-input j-mark-input"
                   name="marks[<?= htmlspecialchars($subj) ?>]"
                   data-subject="<?= htmlspecialchars($subj) ?>"
                   min="0"
                   max="100"
                   placeholder="—"
                   autocomplete="off" />
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Feedback / Remarks Section (Increased space on top, tighter space to box) -->
    <div class="c-term-marks-panel__feedback-group">
      <label class="c-term-marks-panel__feedback-label" for="j-term-marks-feedback">
        FEEDBACK ABOUT MARKS
      </label>
      <textarea id="j-term-marks-feedback"
                class="c-term-marks-panel__textarea j-mark-feedback"
                rows="3"
                placeholder="Add feedback about student's marks..."></textarea>
    </div>

    <!-- Actions Bar -->
    <div class="c-term-marks-panel__actions">
      <button type="button" class="c-term-marks-panel__cancel-btn j-close-marks-panel">
        Cancel
      </button>
      <button type="button" class="c-submit-marks-btn j-submit-marks">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-check"/>
        </svg>
        <span>Submit</span>
      </button>
    </div>

    <!-- Status toast message -->
    <div class="c-term-marks-panel__toast j-marks-toast" style="display: none;">
      <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-check"/>
      </svg>
      <span>Marks updated successfully!</span>
    </div>
  </form>
</aside>
