/**
 * =========================================================================
 * L'ÉCOLE — NOTICE CARD COMPONENT CONTROLLER
 * =========================================================================
 * Handles notice card interactions: card clicks, pin toggling,
 * edit pre-filling, and specific delete confirmation popup.
 * =========================================================================
 */

(function () {
  'use strict';

  const ICON_PIN_FILLED = `
    <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <use href="#icon-pinFilled"/>
    </svg>`;

  const ICON_PIN_BTN_FILLED = `
    <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <use href="#icon-pinFilled"/>
    </svg>`;

  const ICON_PIN_BTN_OUTLINE = `
    <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <use href="#icon-pin"/>
    </svg>`;

  document.addEventListener('click', function (e) {
    // 1. Pin toggle action
    const pinBtn = e.target.closest('.j-notice-pin');
    if (pinBtn) {
      e.preventDefault();
      e.stopPropagation();
      const card = pinBtn.closest('.c-notice-card');
      if (!card) return;

      const isCurrentlyPinned = card.getAttribute('data-pinned') === 'true';
      const newPinned = !isCurrentlyPinned;

      card.setAttribute('data-pinned', newPinned ? 'true' : 'false');
      pinBtn.setAttribute('aria-label', newPinned ? 'Unpin notice' : 'Pin notice');
      pinBtn.innerHTML = newPinned ? ICON_PIN_BTN_FILLED : ICON_PIN_BTN_OUTLINE;

      let pinBadge = card.querySelector('.c-notice-card__pin');
      if (newPinned) {
        if (!pinBadge) {
          pinBadge = document.createElement('span');
          pinBadge.className = 'c-notice-card__pin';
          pinBadge.setAttribute('aria-label', 'Pinned notice');
          pinBadge.innerHTML = ICON_PIN_FILLED;
          card.prepend(pinBadge);
        }
        // Move to top of notice grid
        const grid = card.closest('.c-notice-grid');
        if (grid && grid.firstElementChild !== card) {
          grid.prepend(card);
        }
      } else {
        if (pinBadge) pinBadge.remove();
      }
      return;
    }

    // 2. Delete trigger -> Opens universal confirmation popup
    const deleteBtn = e.target.closest('.j-notice-delete');
    if (deleteBtn) {
      e.preventDefault();
      e.stopPropagation();
      const card = deleteBtn.closest('.c-notice-card');
      if (!card) return;

      if (typeof window.openUniversalDeleteModal === 'function') {
        window.openUniversalDeleteModal({
          title: 'Delete notice?',
          description: 'This will remove the notice from the central Notice Board.',
          buttonText: 'Delete notice',
          onConfirm: () => {
            card.style.transition = 'all 0.2s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.92)';
            setTimeout(() => {
              const grid = card.closest('.c-notice-grid');
              card.remove();
              if (grid && grid.querySelectorAll('.c-notice-card').length === 0) {
                const emptyState = document.getElementById('j-empty-state');
                if (emptyState) emptyState.hidden = false;
              }
            }, 200);
          }
        });
      }
      return;
    }
  });
})();
