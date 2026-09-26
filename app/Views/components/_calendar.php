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
$initialDate = $calendarConfig['initialDate'] ?? '2026-06-17';
$viewDate = $calendarConfig['viewDate'] ?? '2026-06-01';
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
  <section class="c-modal c-modal--day-schedule" role="dialog" aria-modal="true" aria-labelledby="j-day-schedule-title" tabindex="-1">
    <header class="c-modal__header">
      <div class="c-modal__heading-group">
        <span class="c-modal__icon-badge c-modal__icon-badge--sky-solid" aria-hidden="true">
          <svg class="c-icon" width="19" height="19"><use href="#icon-calendar"/></svg>
        </span>
        <div>
          <p class="c-modal__eyebrow">Day schedule</p>
          <h2 class="c-modal__title" id="j-day-schedule-title"></h2>
          <p class="c-modal__description" id="j-day-schedule-description"></p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close day schedule">
        <svg class="c-icon" width="20" height="20" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>
    <div class="c-day-schedule__body" id="j-day-schedule-body"></div>
  </section>
</div>

<?php if ($canAddEvent): ?>
<!-- MODAL: EVENT EDITOR -->
<div class="c-modal-layer" id="j-modal-event-editor" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close event editor"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-event-editor-title">
    <header class="c-modal__header">
      <div class="c-modal__heading-group">
        <div class="c-modal__icon-badge c-modal__icon-badge--sky" aria-hidden="true">
          <svg class="c-icon" width="19" height="19"><use href="#icon-calendarPlus"/></svg>
        </div>
        <div>
          <p class="c-modal__eyebrow" id="j-event-editor-month">Calendar event</p>
          <h2 class="c-modal__title" id="j-event-editor-title">Add an event</h2>
          <p class="c-modal__description" id="j-event-editor-description">This event is saved to the dashboard calendar for this session.</p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close event editor">
        <svg class="c-icon" width="20" height="20" aria-hidden="true"><use href="#icon-close"/></svg>
      </button>
    </header>

    <form class="c-event-form" id="j-event-form" novalidate>
      <input type="hidden" name="_csrf_token" id="j-calendar-csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="scope_type" id="j-calendar-scope-type" value="<?= htmlspecialchars($scopeType, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="scope_id" id="j-calendar-scope-id" value="<?= htmlspecialchars((string)($scopeId ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="id" id="j-calendar-event-id" value="">
      <div class="c-event-form__error-banner" id="j-event-form-error-banner">
        Complete the highlighted event details before saving.
      </div>

      <div id="j-event-scope-container" style="margin-bottom: 1rem;">
        <span class="c-field-label">Event scope</span>
        <div class="c-select c-select--cream" id="j-select-scope" style="width: 100%;">
          <button type="button" class="c-select__trigger j-select-scope-btn" aria-haspopup="listbox" aria-expanded="false" style="width: 100%; justify-content: space-between;">
            <span class="j-select-value" id="j-select-scope-label">Select scope</span>
            <svg class="c-icon c-select__chevron" width="14" height="14" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
          </button>
          <div class="c-select__menu" id="j-select-scope-menu" role="listbox" aria-label="Choose event scope" style="max-height: 200px; overflow-y: auto;">
          </div>
        </div>
      </div>

      <div>
        <span class="c-field-label">Event category</span>
        <?php
        $dropdownId    = 'j-field-category';
        $dropdownLabel = 'Event category';
        $placeholder   = 'Select category';
        $options       = [
            ['value' => 'Academic', 'label' => 'Academic'],
            ['value' => 'Extracurricular', 'label' => 'Extracurricular'],
            ['value' => 'Other', 'label' => 'General / Other'],
        ];
        $selectedValue = 'Academic';
        $name          = 'category';
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>

      <div style="margin-top: 1rem;">
        <label class="c-field-label" for="j-field-time">Time</label>
        <input class="c-field-input" id="j-field-time" placeholder="e.g. 09:00 AM or 08:30–10:30" type="text" />
        <p class="c-field-error" id="j-field-time-error">Enter the event time.</p>
      </div>

      <div style="margin-top: 1rem;">
        <label class="c-field-label" for="j-field-title">Event title</label>
        <input class="c-field-input" id="j-field-title" placeholder="e.g. Mathematics examination" type="text" />
        <p class="c-field-error" id="j-field-title-error">Enter an event title.</p>
      </div>

      <div style="margin-top: 1rem;">
        <label class="c-field-label" for="j-field-details">Details or location</label>
        <textarea class="c-field-input c-field-input--textarea" id="j-field-details" placeholder="e.g. Grades 6–8 · Respective classrooms"></textarea>
        <p class="c-field-error" id="j-field-details-error">Add details or a location.</p>
      </div>

      <footer class="c-event-form__footer">
        <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
        <button type="submit" class="c-btn c-btn--solid">
          <svg class="c-icon" width="15" height="15"><use href="#icon-calendarPlus"/></svg>
          <span id="j-event-form-submit-label">Save event</span>
        </button>
      </footer>
    </form>
  </section>
</div>
<?php endif; ?>
