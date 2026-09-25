<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR SCHEDULE & EVENTS PANEL COMPONENT
 * =========================================================================
 * Shared panel rendered inside the extracurricular club detail view.
 * Eliminates ~40 lines of duplicated HTML across admin, teacher, and
 * management extracurricular views.
 *
 * Expects (from parent scope):
 *   - $calendarConfig : array  — passed through to _calendar.php
 *   - $canEdit        : bool   — passed through to _extracurricular_scheduleandevents_details.php
 * =========================================================================
 */
?>

<!-- Schedule & Events Section -->
<section class="c-panel" id="j-schedule-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
  <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
    <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--midnight, #0F414A);">
      <use href="#icon-calendarDays"/>
    </svg>
    <h2 class="c-font-display" style="font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0;">Schedule &amp; Events</h2>
  </div>

  <div class="c-schedule-grid">
    <!-- Left Column: Calendar Component -->
    <?php require __DIR__ . '/_calendar.php'; ?>

    <!-- Right Column: Schedule & Events Details Component -->
    <?php require __DIR__ . '/_extracurricular_scheduleandevents_details.php'; ?>
  </div>
</section>
