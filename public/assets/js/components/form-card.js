/**
 * =========================================================================
 * L'ÉCOLE — UNIVERSAL FORM CARD COMPONENT CONTROLLER
 * =========================================================================
 * Shared logic for forms across L'École:
 * - Multi-select audience / category chips with automatic hidden inputs
 * - Custom checkbox handling
 * - File upload dropzone preview
 * - Field validation & error banners
 * =========================================================================
 */

(function () {
  'use strict';

  /**
   * Generic Checkbox Toggle Initializer
   */
  document.addEventListener('click', (e) => {
    const checkbox = e.target.closest('.j-checkbox');
    if (!checkbox) return;

    const isChecked = checkbox.classList.toggle('c-is-checked');
    checkbox.setAttribute('aria-checked', String(isChecked));

    const hiddenInput = checkbox.parentElement.querySelector('input[type="hidden"]');
    if (hiddenInput) {
      hiddenInput.value = isChecked ? '1' : '0';
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === ' ' || e.key === 'Enter') {
      const checkbox = e.target.closest('.j-checkbox');
      if (checkbox) {
        e.preventDefault();
        checkbox.click();
      }
    }
  });

  /**
   * Generic File Upload Dropzone Initializer
   */
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.j-attachment-trigger');
    if (!trigger) return;

    const fileInput = trigger.parentElement.querySelector('input[type="file"]');
    if (fileInput) {
      fileInput.click();
    }
  });

  document.addEventListener('change', (e) => {
    if (e.target.matches('input[type="file"]')) {
      const input = e.target;
      const trigger = input.parentElement.querySelector('.j-attachment-trigger');
      if (!trigger) return;

      const labelEl = trigger.querySelector('.j-attachment-name');
      if (!labelEl) return;

      if (input.files && input.files.length > 0) {
        labelEl.textContent = input.files[0].name;
      } else {
        labelEl.textContent = 'Click to upload a file';
      }
    }
  });

})();
