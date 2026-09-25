<?php
/**
 * =========================================================================
 * L'ÉCOLE — CREATE EXTRACURRICULAR MODAL COMPONENT
 * =========================================================================
 * Modal dialog for setting up a new sport, society, or club and defining
 * its leadership structure. Ported directly from the original admin design.
 * 100% reuses the universal master _form_card.php component with 'sand' header theme.
 * =========================================================================
 */

ob_start();
?>
  <div class="c-event-form__error-banner" id="j-create-club-error" style="margin-bottom: 1.25rem;"></div>

  <div class="c-form-row c-form-row--two-col">
    <div class="c-field-span-2">
      <label class="c-field-label" for="j-cc-name">Programme name</label>
      <input class="c-field-input" id="j-cc-name" type="text" placeholder="e.g. Chess Club" required />
    </div>
  </div>

  <div class="c-form-row c-form-row--two-col" style="margin-top: 1.25rem;">
    <div>
      <label class="c-field-label">Type</label>
      <?php
      $dropdownId    = 'j-cc-type';
      $dropdownLabel = 'Type';
      $placeholder   = 'Select type';
      $options       = ['Sports', 'Society', 'Club', 'Arts'];
      $name          = 'type';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
    <div>
      <label class="c-field-label">Teacher in Charge (TIC)</label>
      <?php
      $dropdownId    = 'j-cc-tic';
      $dropdownLabel = 'Teacher in Charge';
      $placeholder   = 'Assign Teacher in Charge';
      $staffList     = $staffAssignments ?? (class_exists('AcademicModel') ? AcademicModel::getStaffAssignments() : []);
      $staffNames    = !empty($staffList) ? array_map(fn($s) => $s['name'], $staffList) : [];
      $fallbackFaculty = [
          'James Wilson', 'Sarah Peiris', 'Rohan Dias', 'Madhavi Fernando',
          'Alex Benjamin', 'Priya De Silva', 'Sofia Fernando', 'Shanthi Silva',
          'Anura Wijesinghe', 'Mr. Weerasinghe', 'David Peris'
      ];
      $options       = array_values(array_unique(array_filter(array_merge($staffNames, $fallbackFaculty))));
      $name          = 'tic_name';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
  </div>

  <div class="c-form-row c-form-row--two-col" style="margin-top: 1.25rem;">
    <div>
      <label class="c-field-label" for="j-cc-age-limit">Limit of students per age group</label>
      <input class="c-field-input" id="j-cc-age-limit" type="number" placeholder="e.g. 30" min="1" />
    </div>
    <div>
      <label class="c-field-label" for="j-cc-team-limit">Students per team (if applicable)</label>
      <input class="c-field-input" id="j-cc-team-limit" type="number" placeholder="e.g. 11" min="1" />
    </div>
  </div>

  <div style="margin-top: 1.25rem;">
    <label class="c-field-label" for="j-cc-description">Description</label>
    <textarea class="c-field-input c-field-input--textarea" id="j-cc-description" placeholder="Describe the extracurricular activities, goals, and membership expectations..."></textarea>
  </div>

  <section class="c-form-section" style="margin-top: 1.25rem;">
    <div class="c-form-section__head">
      <div>
        <h3 class="c-form-section__title">Leadership Positions Needed</h3>
        <p class="c-form-section__hint">Define optional roles such as Captain or Secretary.</p>
      </div>
      <button type="button" class="c-form-section__add-btn" id="j-cc-add-position">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-plus"/>
        </svg>
        Add Position
      </button>
    </div>
    <div id="j-cc-positions" style="display: flex; flex-direction: column; gap: 0.75rem;"></div>
  </section>
<?php
$formBodySlot = ob_get_clean();

ob_start();
?>
  <div style="display: flex; justify-content: flex-end; gap: 0.75rem; width: 100%;">
    <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--solid">Create Programme</button>
  </div>
<?php
$formFooterSlot = ob_get_clean();

ob_start();
?>
  <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close">
    <svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-close"/></svg>
  </button>
<?php
$headerRightSlot = ob_get_clean();

$formId          = 'j-create-club-form';
$headerTheme     = 'sand';
$headerIcon      = 'icon-usersRound';
$headerEyebrow   = 'New programme';
$formTitle       = 'New Extracurricular';
$formSubtitle    = 'Set up a programme and define its leadership structure.';
$isModal         = true;
$cardClass       = 'c-form-card--modal';
?>

<div class="c-modal-layer" id="j-modal-create-club" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close dialog"></button>
  <section class="c-modal c-modal--create-club" role="dialog" aria-modal="true" aria-labelledby="j-create-club-title" tabindex="-1">
    <?php require __DIR__ . '/_form_card.php'; ?>
  </section>
</div>
