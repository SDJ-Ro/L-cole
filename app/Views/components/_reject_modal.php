<?php
/**
 * =========================================================================
 * L'ÉCOLE — REJECT CONFIRMATION MODAL COMPONENT
 * =========================================================================
 * Modal dialog for collecting mandatory feedback when rejecting an item.
 * =========================================================================
 */
?>
<div class="c-modal-layer" id="j-modal-reject" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Cancel rejection"></button>
  <section class="c-modal c-modal--reject" role="dialog" aria-modal="true" aria-labelledby="j-reject-modal-title">
    
    <div class="c-reject-modal__intro">
      <div class="c-modal__icon-badge c-modal__icon-badge--maroon" aria-hidden="true">
        <svg class="c-icon" width="24" height="24"><use href="#icon-alertCircle"/></svg>
      </div>
      <h3 class="c-reject-modal__title c-font-display" id="j-reject-modal-title">Reject <span class="j-reject-modal-type">Teacher</span></h3>
      <p class="c-reject-modal__description">
        You are rejecting <span class="c-reject-modal__item-name j-reject-modal-item-name"></span>. Please provide feedback for the creator.
      </p>
    </div>

    <div class="c-reject-modal__body">
      <label class="c-field-label" for="j-reject-feedback-input">Rejection Feedback</label>
      <textarea class="c-field-input c-field-input--textarea" id="j-reject-feedback-input" rows="4" placeholder="Explain why this was rejected..."></textarea>
    </div>

    <footer class="c-reject-modal__footer">
      <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
      <button type="button" class="c-btn c-btn--solid-maroon j-reject-confirm-btn" id="j-reject-confirm-btn" disabled>
        <svg class="c-icon" width="16" height="16"><use href="#icon-message"/></svg>
        Send Feedback &amp; Reject
      </button>
    </footer>

  </section>
</div>
