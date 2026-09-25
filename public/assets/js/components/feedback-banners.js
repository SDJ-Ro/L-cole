/**
 * =========================================================================
 * L'ÉCOLE — COMMON FUNCTIONALITY: FEEDBACK BANNERS (TOAST NOTIFICATIONS)
 * =========================================================================
 * PURPOSE:
 *   Universal notification system for quick feedback messages across all
 *   roles and views (e.g. changes saved, record requested, form errors).
 *
 * GLOBAL FUNCTIONS:
 *   - showFeedbackBanner(message, type, durationMs)
 *       - message    : string (e.g. 'Character certificate request submitted.')
 *       - type       : 'success' (green) | 'error' (orange-red) | 'info' (sky blue)
 *       - durationMs : milliseconds before auto-dismiss (default: 3500)
 *
 * HOW TO USE IN ANY COMPONENT:
 *   showFeedbackBanner('Changes saved successfully!', 'success');
 *   showFeedbackBanner('Please fill in all required fields.', 'error');
 *   showFeedbackBanner('Processing request...', 'info', 2000);
 * =========================================================================
 */

(function () {
  'use strict';

  const ICONS = {
    success: `<svg class="c-feedback-banner__icon" width="18" height="18"><use href="#icon-check"/></svg>`,
    error: `<svg class="c-feedback-banner__icon" width="18" height="18"><use href="#icon-alertCircle"/></svg>`,
    info: `<svg class="c-feedback-banner__icon" width="18" height="18"><use href="#icon-info"/></svg>`
  };

  function getOrCreateContainer() {
    let container = document.getElementById('j-feedback-banner-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'j-feedback-banner-container';
      container.className = 'c-feedback-banner-container';
      container.setAttribute('aria-live', 'polite');
      document.body.appendChild(container);
    }
    return container;
  }

  function showFeedbackBanner(message, type = 'success', duration = 3500) {
    if (!message) return;
    const container = getOrCreateContainer();
    const validTypes = ['success', 'error', 'info'];
    const bannerType = validTypes.includes(type) ? type : 'info';

    // Create banner element
    const banner = document.createElement('div');
    banner.className = `c-feedback-banner c-feedback-banner--${bannerType}`;
    banner.setAttribute('role', 'alert');

    const iconHtml = ICONS[bannerType] || ICONS.info;

    banner.innerHTML = `
      <div class="c-feedback-banner__icon-wrap">
        ${iconHtml}
      </div>
      <div class="c-feedback-banner__text">
        ${String(message).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]))}
      </div>
      <button type="button" class="c-feedback-banner__close-btn" aria-label="Dismiss notification">
        <svg width="14" height="14"><use href="#icon-close"/></svg>
      </button>
    `;

    container.appendChild(banner);

    // Trigger smooth entry transition
    requestAnimationFrame(() => {
      banner.classList.add('c-is-visible');
    });

    let dismissTimer = null;

    function dismiss() {
      if (dismissTimer) clearTimeout(dismissTimer);
      banner.classList.remove('c-is-visible');
      banner.classList.add('c-is-leaving');
      setTimeout(() => {
        if (banner.parentElement) {
          banner.parentElement.removeChild(banner);
        }
      }, 300);
    }

    // Auto-dismiss after duration
    if (duration > 0) {
      dismissTimer = setTimeout(dismiss, duration);
    }

    // Manual close button
    const closeBtn = banner.querySelector('.c-feedback-banner__close-btn');
    if (closeBtn) {
      closeBtn.addEventListener('click', dismiss);
    }

    // Pause on hover
    banner.addEventListener('mouseenter', () => {
      if (dismissTimer) clearTimeout(dismissTimer);
    });
    banner.addEventListener('mouseleave', () => {
      if (duration > 0) dismissTimer = setTimeout(dismiss, duration);
    });

    return banner;
  }

  window.showFeedbackBanner = showFeedbackBanner;
})();
