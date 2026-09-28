<?php
/**
 * Shared Calendar Component
 * Role configurable via $calendarConfig:
 * - $calendarConfig['canAddEvent']: bool (true for Admin, Teacher, Management; false for Student, Parent)
 * - $calendarConfig['events']: array of initial events
 * - $calendarConfig['initialDate']: string YYYY-MM-DD
 * - $calendarConfig['viewDate']: string YYYY-MM-DD
 */
$canAddEvent = $calendarConfig['canAddEvent'] ?? false;
$initialEvents = $calendarConfig['events'] ?? [];
$initialDate = $calendarConfig['initialDate'] ?? date('Y-m-d');
$viewDate = $calendarConfig['viewDate'] ?? date('Y-m-01');
$scopeType = $calendarConfig['scopeType'] ?? 'school';
$scopeId = $calendarConfig['scopeId'] ?? null;
$calendarRole = $calendarConfig['role'] ?? ($currentRole ?? '');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (empty($_SESSION['_csrf_token'])) {
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['_csrf_token'];

require_once __DIR__ . '/../../Models/CalendarEventModel.php';
$resolvedRole = !empty($calendarRole) ? $calendarRole : ($_SESSION['user']['role'] ?? 'teacher');
$scopeStructure = $calendarConfig['scopeStructure'] ?? ($canAddEvent ? CalendarEventModel::getScopeStructure($resolvedRole, (int)($_SESSION['user']['id'] ?? 0)) : []);
?>

<!-- Dark Month Calendar -->
<section class="c-calendar" id="j-calendar" aria-label="Examination calendar"
         data-can-add="<?= $canAddEvent ? 'true' : 'false' ?>"
         data-initial-date="<?= htmlspecialchars($initialDate, ENT_QUOTES, 'UTF-8') ?>"
         data-view-date="<?= htmlspecialchars($viewDate, ENT_QUOTES, 'UTF-8') ?>"
         data-events="<?= htmlspecialchars(json_encode($initialEvents), ENT_QUOTES, 'UTF-8') ?>"
         data-role="<?= htmlspecialchars($calendarRole, ENT_QUOTES, 'UTF-8') ?>"
         data-scope-type="<?= htmlspecialchars($scopeType, ENT_QUOTES, 'UTF-8') ?>"
         data-scope-id="<?= htmlspecialchars((string)($scopeId ?? ''), ENT_QUOTES, 'UTF-8') ?>"
         data-scope-options="<?= htmlspecialchars(json_encode($calendarConfig['scopeOptions'] ?? []), ENT_QUOTES, 'UTF-8') ?>"
         data-scope-structure="<?= htmlspecialchars(json_encode($scopeStructure), ENT_QUOTES, 'UTF-8') ?>"
         data-fixed-scope="<?= htmlspecialchars(json_encode($calendarConfig['fixedScope'] ?? null), ENT_QUOTES, 'UTF-8') ?>"
         data-csrf="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
  <header class="c-calendar__header">
    <div class="c-calendar__header-row">
      <div class="c-calendar__nav">
        <button type="button" class="c-calendar__nav-btn" id="j-calendar-prev" aria-label="Previous month">
          <svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-chevronLeft"/></svg>
        </button>
        <button type="button" class="c-calendar__nav-btn" id="j-calendar-next" aria-label="Next month">
          <svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
        </button>
      </div>

      <div class="c-calendar__month-year">
        <!-- month select -->
        <div class="c-select c-select--month" id="j-select-month">
          <button type="button" class="c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
            <span class="j-select-value">June</span>
            <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
          </button>
          <div class="c-select__menu" role="listbox" aria-label="Choose calendar month"></div>
        </div>
        <!-- year select -->
        <div class="c-select c-select--year" id="j-select-year">
          <button type="button" class="c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
            <span class="j-select-value">2026</span>
            <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
          </button>
          <div class="c-select__menu" role="listbox" aria-label="Choose calendar year"></div>
        </div>
      </div>
    </div>
  </header>

  <div class="c-calendar__weekdays" id="j-calendar-weekdays"></div>
  <div class="c-calendar__days" id="j-calendar-days"></div>

  <div class="c-calendar__footer">
    <div class="c-calendar__footer-row">
      <p class="c-calendar__event-count" id="j-calendar-event-count" aria-live="polite">0 events scheduled</p>
      <div style="display: flex; gap: 8px; align-items: center;">
        <button type="button" class="c-calendar__view-all-btn" id="j-open-day-schedule">View all</button>
      </div>
    </div>
    <div class="c-calendar__day-detail" id="j-calendar-day-detail"></div>
  </div>
</section>

<!-- MODAL: FULL DAY SCHEDULE ("View all") -->
<div class="c-modal-layer c-modal-layer--day-schedule" id="j-modal-day-schedule" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close day schedule"></button>
  <section class="c-modal c-modal--day-schedule c-form-card c-form-card--modal" role="dialog" aria-modal="true" aria-labelledby="j-day-schedule-title" tabindex="-1">
    <header class="c-form-header c-form-header--sand c-modal__header" style="background: #F7F3EC !important; border-bottom: 1px solid #E5DFD7; padding: 1.25rem 1.75rem; display: flex !important; flex-direction: row !important; align-items: flex-start !important; justify-content: space-between !important; gap: 1rem !important;">
      <div class="c-modal__heading-group" style="display: flex; align-items: flex-start; gap: 0.875rem;">
        <span class="c-modal__icon-badge" aria-hidden="true" style="flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: var(--radius-xl, 0.875rem); background: rgba(255, 255, 255, 0.85); border: 1px solid rgba(184, 151, 108, 0.3); color: #8C5A24; display: flex; align-items: center; justify-content: center;">
          <svg class="c-icon" width="20" height="20"><use href="#icon-calendar"/></svg>
        </span>
        <div>
          <p class="c-modal__eyebrow" style="margin: 0; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.55);">Day schedule</p>
          <h2 class="c-form-header-title c-modal__title c-font-display" id="j-day-schedule-title" style="margin: 0.15rem 0 0 0; font-size: 1.25rem; font-weight: 800; color: #0F414A;"></h2>
          <p class="c-form-header-subtitle c-modal__description" id="j-day-schedule-description" style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: rgba(15, 65, 74, 0.7);"></p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close day schedule" style="background: none; border: none; padding: 0.25rem; cursor: pointer; color: rgba(15,65,74,0.6); display: flex; align-items: center; justify-content: center; border-radius: 50%; width: 32px; height: 32px; transition: background-color 150ms ease;">
        <svg class="c-icon" width="18" height="18" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>
    <div class="c-day-schedule__body" id="j-day-schedule-body"></div>
  </section>
</div>

<?php if ($canAddEvent): ?>
<!-- MODAL: EVENT EDITOR -->
<div class="c-modal-layer" id="j-modal-event-editor" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close event editor"></button>
  <section class="c-modal c-modal--event-editor c-form-card c-form-card--modal" role="dialog" aria-modal="true" aria-labelledby="j-event-editor-title" style="max-width: 36rem; width: 100%; max-height: 90vh; border-radius: 1.25rem; overflow: hidden; display: flex; flex-direction: column;">
    <header class="c-form-header c-form-header--sand c-modal__header" style="flex-shrink: 0; background: #F7F3EC !important; border-bottom: 1px solid #E5DFD7; padding: 1.25rem 1.75rem; display: flex !important; flex-direction: row !important; align-items: flex-start !important; justify-content: space-between !important; gap: 1rem !important;">
      <div class="c-modal__heading-group" style="display: flex; align-items: flex-start; gap: 0.875rem;">
        <div class="c-modal__icon-badge" aria-hidden="true" style="flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: var(--radius-xl, 0.875rem); background: rgba(255, 255, 255, 0.85); border: 1px solid rgba(184, 151, 108, 0.3); color: #8C5A24; display: flex; align-items: center; justify-content: center;">
          <svg class="c-icon" width="20" height="20"><use href="#icon-calendarPlus"/></svg>
        </div>
        <div>
          <p class="c-modal__eyebrow" id="j-event-editor-month" style="margin: 0; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(15, 65, 74, 0.55);">Calendar event</p>
          <h2 class="c-form-header-title c-modal__title c-font-display" id="j-event-editor-title" style="margin: 0.15rem 0 0 0; font-size: 1.25rem; font-weight: 800; color: #0F414A;">Add an event</h2>
          <p class="c-form-header-subtitle c-modal__description" id="j-event-editor-description" style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: rgba(15, 65, 74, 0.7);">This event is saved to the dashboard calendar for this session.</p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close event editor" style="background: none; border: none; padding: 0.25rem; cursor: pointer; color: rgba(15,65,74,0.6); display: flex; align-items: center; justify-content: center; border-radius: 50%; width: 32px; height: 32px; transition: background-color 150ms ease;">
        <svg class="c-icon" width="18" height="18" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>

    <form class="c-event-form" id="j-event-form" novalidate style="display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden; background: #ffffff;">
      <div class="c-form-body c-event-form__body" style="overflow-y: auto; flex: 1 1 auto; min-height: 0; padding: 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 1.25rem;">
        <input type="hidden" name="_csrf_token" id="j-calendar-csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="scope_type" id="j-calendar-scope-type" value="<?= htmlspecialchars($scopeType, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="scope_id" id="j-calendar-scope-id" value="<?= htmlspecialchars((string)($scopeId ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" id="j-calendar-event-id" value="">
        
        <div class="c-event-form__error-banner" id="j-event-form-error-banner" style="border-radius: var(--radius-lg, 0.5rem); border: 1px solid rgba(127, 3, 3, 0.25); background: rgba(127, 3, 3, 0.05); padding: 0.75rem 1rem; font-size: 0.8125rem; font-weight: 500; color: var(--maroon, #7F0303);">
          Complete the highlighted event details before saving.
        </div>

        <div>
          <label class="c-field-label">Event category</label>
          <?php
          $isTeacher = ($resolvedRole === 'teacher' || $calendarRole === 'teacher');
          $dropdownId    = 'j-field-category';
          $dropdownLabel = 'Event category';
          $placeholder   = 'Select category';
          if ($isTeacher) {
              // Teacher: strictly NO school-wide option; only teacher-relevant categories
              $options = [
                  ['value' => 'Academic', 'label' => 'Academic'],
                  ['value' => 'Extracurricular', 'label' => 'Extracurricular'],
              ];
              $selectedValue = ($scopeType === 'club' || $scopeType === 'sport') ? 'Extracurricular' : 'Academic';
          } else {
              $options = [
                  ['value' => 'General', 'label' => 'General / School-wide'],
                  ['value' => 'Academic', 'label' => 'Academic'],
                  ['value' => 'Extracurricular', 'label' => 'Extracurricular'],
              ];
              $selectedValue = 'General';
          }
          $name          = 'category';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <!-- Progressive Scope Level 2A: Academic Scope Section -->
        <div id="j-scope-academic-section" style="<?= ($isTeacher && $selectedValue === 'Academic') ? 'display: block;' : 'display: none;' ?> padding: 1.125rem 1.25rem; border-radius: 0.625rem; background: #FAF7F2; border: 1px solid #EFE8DF;">
          <?php if (!$isTeacher): ?>
          <label class="c-field-label">Academic scope level</label>
          <div style="display: flex; gap: 1.5rem; margin-top: 0.45rem; margin-bottom: 0.95rem;">
            <label class="c-radio-label">
              <input type="radio" name="academic_level" value="grade" id="j-academic-level-grade" checked class="c-radio-input">
              <span class="c-radio-text">Entire grade(s)</span>
            </label>
            <label class="c-radio-label">
              <input type="radio" name="academic_level" value="class" id="j-academic-level-class" class="c-radio-input">
              <span class="c-radio-text">Specific class(es)</span>
            </label>
          </div>

          <!-- Grade Multi-Select -->
          <div id="j-academic-grades-container">
            <label class="c-field-label">Select grade(s)</label>
            <div class="c-select c-dropdown c-dropdown--multi" id="j-select-academic-grades" data-multi="true" data-name="grades[]" aria-label="Select grades">
              <button type="button" class="c-select__trigger c-dropdown__trigger j-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false" style="width: 100%;">
                <span class="j-dropdown-chips j-tag-chips"></span>
                <span class="j-select-value c-dropdown__value j-dropdown-placeholder">Select grades...</span>
                <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
              </button>
              <div class="c-select__menu c-dropdown__menu j-dropdown-menu" role="listbox" aria-label="Grades list" style="max-height: 180px; overflow-y: auto;"></div>
              <div class="j-dropdown-hidden-inputs"></div>
            </div>
          </div>
          <?php else: ?>
            <input type="radio" name="academic_level" value="class" id="j-academic-level-class" checked style="display: none;">
            <div id="j-academic-grades-container" style="display: none;"></div>
          <?php endif; ?>

          <!-- Class Selection (Grade filter + Class multi-select) -->
          <div id="j-academic-classes-container" style="<?= $isTeacher ? 'display: block;' : 'display: none;' ?>">
            <?php if (!$isTeacher): ?>
            <div style="margin-bottom: 0.75rem;">
              <label class="c-field-label">Filter classes by grade</label>
              <div class="c-select c-dropdown" id="j-filter-class-grade" aria-label="Filter grade">
                <button type="button" class="c-select__trigger c-dropdown__trigger j-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false" style="width: 100%;">
                  <span class="j-select-value c-dropdown__value">All grades</span>
                  <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
                </button>
                <div class="c-select__menu c-dropdown__menu j-dropdown-menu" role="listbox" style="max-height: 180px; overflow-y: auto;"></div>
              </div>
            </div>
            <?php endif; ?>

            <div>
              <label class="c-field-label"><?= $isTeacher ? 'Select your class(es)' : 'Select class(es)' ?></label>
              <div class="c-select c-dropdown c-dropdown--multi" id="j-select-academic-classes" data-multi="true" data-name="classes[]" aria-label="Select classes">
                <button type="button" class="c-select__trigger c-dropdown__trigger j-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false" style="width: 100%;">
                  <span class="j-dropdown-chips j-tag-chips"></span>
                  <span class="j-select-value c-dropdown__value j-dropdown-placeholder">Select classes...</span>
                  <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
                </button>
                <div class="c-select__menu c-dropdown__menu j-dropdown-menu" role="listbox" aria-label="Classes list" style="max-height: 180px; overflow-y: auto;"></div>
                <div class="j-dropdown-hidden-inputs"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Progressive Scope Level 2B: Extracurricular Scope Section -->
        <div id="j-scope-extracurricular-section" style="display: none; padding: 1.125rem 1.25rem; border-radius: 0.625rem; background: #FAF7F2; border: 1px solid #EFE8DF;">
          <label class="c-field-label">Select club(s) or sport(s)</label>
          <div class="c-select c-dropdown c-dropdown--multi" id="j-select-extracurricular" data-multi="true" data-name="extracurricular[]" aria-label="Select extracurriculars">
            <button type="button" class="c-select__trigger c-dropdown__trigger j-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false" style="width: 100%;">
              <span class="j-dropdown-chips j-tag-chips"></span>
              <span class="j-select-value c-dropdown__value j-dropdown-placeholder">Select clubs or sports...</span>
              <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
            </button>
            <div class="c-select__menu c-dropdown__menu j-dropdown-menu" role="listbox" aria-label="Clubs and Sports list" style="max-height: 200px; overflow-y: auto;"></div>
            <div class="j-dropdown-hidden-inputs"></div>
          </div>
        </div>

        <div>
          <label class="c-field-label">Target audience</label>
          <?php
          $userRole = strtolower($calendarRole ?: ($_SESSION['user']['role'] ?? 'teacher'));
          $allowedAudiences = in_array($userRole, ['admin', 'management'], true)
              ? ['All', 'Students', 'Parents', 'Teachers', 'Management']
              : ['Students', 'Parents'];
          $dropdownId    = 'j-field-audience';
          $dropdownLabel = 'Target audience';
          $placeholder   = 'Select audience...';
          $options       = array_map(fn($a) => ['value' => $a, 'label' => $a], $allowedAudiences);
          $selectedValue = ['All'];
          $name          = 'audience[]';
          $isMultiSelect = true;
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <div class="c-form-row">
          <div class="c-time-picker-field">
            <label class="c-field-label" for="j-drum-display-value">Event Time</label>

            <!-- 1. The Interactive Time Display Bar (No text box, click to expand) -->
            <div class="c-drum-trigger" id="j-time-drum-trigger" role="button" tabindex="0" aria-expanded="false" aria-controls="j-drum-drawer">
              <div class="c-drum-trigger__left">
                <span class="c-drum-trigger__icon-wrap">
                  <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#icon-clock"/>
                  </svg>
                </span>
                <div class="c-drum-trigger__meta">
                  <span class="c-drum-trigger__label">Scheduled Slot</span>
                  <span class="c-drum-trigger__value" id="j-drum-display-value">09:00 AM</span>
                </div>
              </div>
              <div class="c-drum-trigger__right">
                <span class="c-drum-trigger__hint" id="j-drum-toggle-hint">Tap to adjust</span>
                <svg class="c-icon c-drum-trigger__chevron" id="j-drum-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <use href="#icon-chevronDown"/>
                </svg>
              </div>
            </div>

            <!-- Hidden Input for Form Submission, Live Conflict Checks & Backend Compatibility -->
            <input type="hidden" id="j-field-time" name="time" value="09:00 AM" />
            <p class="c-field-error" id="j-field-time-error">Please select the event time.</p>

            <!-- 2. The In-Place Expanding Drum Tumbler Drawer -->
            <div class="c-drum-drawer" id="j-drum-drawer" style="display: none;">
              <!-- Mode Tabs: Single Time vs Time Range -->
              <div class="c-drum-mode-row">
                <div class="c-drum-mode-tabs">
                  <button type="button" class="c-drum-mode-btn is-active" id="j-drum-mode-single" data-mode="single">Single Time</button>
                  <button type="button" class="c-drum-mode-btn" id="j-drum-mode-range" data-mode="range">Time Range</button>
                </div>
                <div class="c-drum-range-tabs" id="j-drum-range-tabs" style="display: none;">
                  <button type="button" class="c-drum-subtab is-active" id="j-drum-subtab-start">Start Time</button>
                  <span class="c-drum-subtab-arrow">&rarr;</span>
                  <button type="button" class="c-drum-subtab" id="j-drum-subtab-end">End Time</button>
                </div>
              </div>

              <!-- Range Validation Warning Banner -->
              <div class="c-drum-range-warning" id="j-drum-range-warning" style="display: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span id="j-drum-range-warning-text">End time must be later than start time (check AM / PM).</span>
              </div>

              <!-- Drum Cylinder Stage with Fixed Selection Lens -->
              <div class="c-drum-stage">
                <!-- Center Fixed Selection Lens Band -->
                <div class="c-drum-lens"></div>

                <!-- Top & Bottom Soft Fading Masks -->
                <div class="c-drum-fade c-drum-fade--top"></div>
                <div class="c-drum-fade c-drum-fade--bottom"></div>

                <!-- Three Cyclic Columns: Hours, Minutes, AM/PM -->
                <div class="c-drum-wheels">
                  <!-- Hours Cylinder (Cyclic 1-12) -->
                  <div class="c-drum-column j-drum-col" data-col="hour" tabindex="0" aria-label="Hour selection">
                    <span class="c-drum-col-header">Hour</span>
                    <button type="button" class="c-drum-arrow j-drum-prev" data-col="hour" aria-label="Previous hour">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronUp"/></svg>
                    </button>
                    <div class="c-drum-slots j-drum-slots-hour"></div>
                    <button type="button" class="c-drum-arrow j-drum-next" data-col="hour" aria-label="Next hour">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronDown"/></svg>
                    </button>
                  </div>

                  <div class="c-drum-divider">:</div>

                  <!-- Minutes Cylinder (Cyclic 00-59) -->
                  <div class="c-drum-column j-drum-col" data-col="minute" tabindex="0" aria-label="Minute selection">
                    <span class="c-drum-col-header">Min</span>
                    <button type="button" class="c-drum-arrow j-drum-prev" data-col="minute" aria-label="Previous minute">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronUp"/></svg>
                    </button>
                    <div class="c-drum-slots j-drum-slots-minute"></div>
                    <button type="button" class="c-drum-arrow j-drum-next" data-col="minute" aria-label="Next minute">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronDown"/></svg>
                    </button>
                  </div>

                  <!-- Period Cylinder (Cyclic AM/PM) -->
                  <div class="c-drum-column c-drum-column--ampm j-drum-col" data-col="ampm" tabindex="0" aria-label="AM or PM selection">
                    <span class="c-drum-col-header">Period</span>
                    <button type="button" class="c-drum-arrow j-drum-prev" data-col="ampm" aria-label="Toggle AM/PM">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronUp"/></svg>
                    </button>
                    <div class="c-drum-slots j-drum-slots-ampm"></div>
                    <button type="button" class="c-drum-arrow j-drum-next" data-col="ampm" aria-label="Toggle AM/PM">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-chevronDown"/></svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Drawer Footer -->
              <div class="c-drum-footer">
                <span class="c-drum-footer-hint">Scroll mouse wheel or tap arrows to cycle</span>
                <button type="button" class="c-btn c-btn--solid c-btn--sm" id="j-drum-btn-done">Done</button>
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="c-field-label" for="j-field-title">Event title</label>
          <input class="c-field-input" id="j-field-title" placeholder="e.g. Mathematics examination" type="text" />
          <p class="c-field-error" id="j-field-title-error">Enter an event title.</p>
        </div>

        <div>
          <label class="c-field-label" for="j-field-details">Details or location</label>
          <textarea class="c-field-input c-field-input--textarea" id="j-field-details" placeholder="e.g. Grades 6–8 · Respective classrooms"></textarea>
          <p class="c-field-error" id="j-field-details-error">Add details or a location.</p>
        </div>

        <!-- Schedule Conflict Soft-Lock Warning Banner -->
        <div class="c-event-form__warning-banner" id="j-event-form-warning-banner" style="display: none; padding: 0.875rem 1rem; border-radius: var(--radius-lg, 0.5rem); border: 1px solid #D8BA98; background: #FAF7F2; color: #0F414A; font-size: 0.8125rem; line-height: 1.45;">
        </div>

        <!-- Parallel Schedule Acknowledgment Checkbox -->
        <div id="j-parallel-container" style="display: none; align-items: center; gap: 0.6rem;">
          <input type="checkbox" id="j-field-allow-parallel" name="allow_parallel" value="1" class="c-checkbox-input" />
          <label for="j-field-allow-parallel" style="font-size: 0.8125rem; color: #0F414A; cursor: pointer; font-weight: 500;">Allow parallel schedule for this time slot</label>
        </div>
      </div>

      <footer class="c-form-footer c-event-form__footer" style="flex-shrink: 0; background: #FAF7F2; border-top: 1px solid #EFE8DF; padding: 1.125rem 1.75rem; display: flex; justify-content: space-between; align-items: center; border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem;">
        <div>
          <button type="button" class="c-btn c-btn--danger-zone" id="j-event-editor-delete-btn" style="display: none; padding: 0.5rem 0.85rem; font-size: 0.8125rem; border-radius: 0.375rem; border: 1px solid rgba(185, 28, 28, 0.3); background: #fef2f2; color: #b91c1c; font-weight: 600; cursor: pointer;">
            <svg class="c-icon" width="14" height="14" style="vertical-align: -2px; margin-right: 4px;"><use href="#icon-trash"/></svg>
            Delete Event
          </button>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
          <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
          <button type="submit" class="c-btn c-btn--solid">
            <svg class="c-icon" width="15" height="15"><use href="#icon-calendarPlus"/></svg>
            <span id="j-event-form-submit-label">Save event</span>
          </button>
        </div>
      </footer>
    </form>
  </section>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/_delete_modal.php'; ?>
