<?php
/**
 * =========================================================================
 * L'ÉCOLE — ADD GRADE MODAL COMPONENT
 * =========================================================================
 * Modal dialog for creating a new Grade in the Academic Structure.
 * Compatible with Admin and Management academic overviews.
 * =========================================================================
 */
?>
<div class="c-modal-layer" id="j-modal-add-grade" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close add grade dialog"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-add-grade-title">
    <header class="c-modal__header c-modal__header--grade">
      <div class="c-modal__heading-group">
        <div class="c-modal__icon-badge c-modal__icon-badge--cherry" aria-hidden="true">
          <svg class="c-icon" width="19" height="19"><use href="#icon-graduationCap"/></svg>
        </div>
        <div>
          <p class="c-modal__eyebrow">Academic structure</p>
          <h2 class="c-modal__title" id="j-add-grade-title">Add a grade</h2>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close">
        <svg class="c-icon" width="18" height="18"><use href="#icon-x"/></svg>
      </button>
    </header>

    <form class="c-grade-form" id="j-add-grade-form">
      <div>
        <label class="c-grade-form__field-label" for="j-add-grade-name">Grade name</label>
        <input class="c-grade-form__input" id="j-add-grade-name" placeholder="e.g. Grade 12" type="text" required autocomplete="off" />
        <p class="c-grade-form__hint">The grade will become available in reporting, academic schedules, and the student directory.</p>
      </div>
      <div class="c-grade-form__footer">
        <button type="button" class="c-btn-plain j-modal-close">Cancel</button>
        <button type="submit" class="c-btn-create-grade" id="j-add-grade-submit" disabled>
          <svg class="c-icon" width="13" height="13"><use href="#icon-plus"/></svg>
          Create grade
        </button>
      </div>
    </form>
  </section>
</div>
