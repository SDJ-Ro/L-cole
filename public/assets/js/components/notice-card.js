/**
 * =========================================================================
 * L'ÉCOLE — NOTICE CARD COMPONENT CONTROLLER
 * =========================================================================
 * Handles:
 *  - Pin toggle → fetch() POST to /admin/toggle-pin-notice
 *  - Delete confirmation modal → fetch() POST to /admin/delete-notice
 * =========================================================================
 */

(function () {
  'use strict';

  const cfg          = window.LECOLE_NOTICE || {};
  const CSRF         = cfg.csrfToken    || '';
  const DELETE_URL   = cfg.deleteUrl    || '/admin/delete-notice';
  const PIN_URL      = cfg.togglePinUrl || '/admin/toggle-pin-notice';

  const ICON_PIN_BADGE_FILLED = `
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

    // -----------------------------------------------------------------------
    // 1. PIN TOGGLE → POST to backend, update DOM on success
    // -----------------------------------------------------------------------
    const pinBtn = e.target.closest('.j-notice-pin');
    if (pinBtn) {
      e.preventDefault();
      e.stopPropagation();

      const card = pinBtn.closest('.c-notice-card');
      if (!card) return;

      const noticeId = card.getAttribute('data-notice-id') || '';
      // Skip purely DOM-generated (unsaved) cards
      if (!noticeId || noticeId.startsWith('n-')) {
        // Fallback: pure DOM toggle for unsaved cards
        _domTogglePin(card, pinBtn);
        return;
      }

      pinBtn.disabled = true;

      const fd = new FormData();
      fd.append('_csrf_token', CSRF);
      fd.append('id', noticeId);

      fetch(PIN_URL, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(json => {
          if (!json.success) {
            console.warn('[Notice pin error]', json.error);
            return;
          }
          _domSetPin(card, pinBtn, !!json.pinned);
        })
        .catch(err => console.error('[Notice pin fetch error]', err))
        .finally(() => { pinBtn.disabled = false; });

      return;
    }

    // -----------------------------------------------------------------------
    // 2. DELETE → confirmation modal → POST to backend → remove card
    // -----------------------------------------------------------------------
    const deleteBtn = e.target.closest('.j-notice-delete');
    if (deleteBtn) {
      e.preventDefault();
      e.stopPropagation();

      const card = deleteBtn.closest('.c-notice-card');
      if (!card) return;

      const noticeId = card.getAttribute('data-notice-id') || '';

      if (typeof window.openUniversalDeleteModal === 'function') {
        window.openUniversalDeleteModal({
          title:       'Delete notice?',
          description: 'This will permanently remove the notice from the central Notice Board.',
          buttonText:  'Delete notice',
          onConfirm: () => {
            // Optimistic DOM removal
            _animateRemove(card);

            // Skip backend call for purely DOM-generated unsaved cards
            if (!noticeId || noticeId.startsWith('n-')) return;

            const fd = new FormData();
            fd.append('_csrf_token', CSRF);
            fd.append('id', noticeId);

            fetch(DELETE_URL, { method: 'POST', body: fd })
              .then(r => r.json())
              .then(json => {
                if (!json.success) {
                  console.warn('[Notice delete error]', json.error);
                  // Restore card on failure
                  const grid = document.getElementById('j-notice-grid');
                  if (grid) {
                    card.style.opacity   = '1';
                    card.style.transform = '';
                    grid.appendChild(card);
                  }
                }
              })
              .catch(err => console.error('[Notice delete fetch error]', err));
          }
        });
      } else {
        // Fallback if dialog system unavailable
        if (confirm('Delete this notice?')) {
          _animateRemove(card);
        }
      }
      return;
    }
  });

  // -------------------------------------------------------------------------
  // Helpers
  // -------------------------------------------------------------------------
  function _domTogglePin(card, pinBtn) {
    const isNowPinned = card.getAttribute('data-pinned') !== 'true';
    _domSetPin(card, pinBtn, isNowPinned);
  }

  function _domSetPin(card, pinBtn, isPinned) {
    card.setAttribute('data-pinned', isPinned ? 'true' : 'false');
    pinBtn.setAttribute('aria-label', isPinned ? 'Unpin notice' : 'Pin notice');
    pinBtn.innerHTML = isPinned ? ICON_PIN_BTN_FILLED : ICON_PIN_BTN_OUTLINE;

    let pinBadge = card.querySelector('.c-notice-card__pin');
    if (isPinned) {
      if (!pinBadge) {
        pinBadge = document.createElement('span');
        pinBadge.className = 'c-notice-card__pin';
        pinBadge.setAttribute('aria-label', 'Pinned notice');
        pinBadge.innerHTML = ICON_PIN_BADGE_FILLED;
        card.prepend(pinBadge);
      }
      const grid = card.closest('.c-notice-grid');
      if (grid && grid.firstElementChild !== card) {
        grid.prepend(card);
      }
    } else {
      if (pinBadge) pinBadge.remove();
    }
  }

  function _animateRemove(card) {
    card.style.transition = 'all 0.2s ease';
    card.style.opacity    = '0';
    card.style.transform  = 'scale(0.92)';
    setTimeout(() => {
      const grid = card.closest('.c-notice-grid');
      card.remove();
      if (grid && grid.querySelectorAll('.c-notice-card').length === 0) {
        const emptyState = document.getElementById('j-empty-state');
        if (emptyState) emptyState.hidden = false;
      }
    }, 200);
  }

})();
