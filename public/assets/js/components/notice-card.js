/**
 * =========================================================================
 * L'ÉCOLE — NOTICE CARD COMPONENT CONTROLLER
 * =========================================================================
 * Handles notice card interactions:
 *  - Real-time Pin/Unpin with backend persistence & max-pin validation
 *  - Universal confirmation modal and backend Soft Delete
 *  - Toast alerts and dynamic grid updates
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

  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      || document.querySelector('input[name="_csrf_token"]')?.value
      || window.LECOLE_CSRF_TOKEN
      || '';
  }

  function getBasePath() {
    const seg = window.location.pathname.split('/')[1] || 'admin';
    return '/' + (['admin', 'management', 'teacher'].includes(seg) ? seg : 'admin');
  }

  document.addEventListener('click', async function (e) {
    // -----------------------------------------------------------------------
    // 1. PIN TOGGLE ACTION
    // -----------------------------------------------------------------------
    const pinBtn = e.target.closest('.j-notice-pin');
    if (pinBtn) {
      e.preventDefault();
      e.stopPropagation();

      const card = pinBtn.closest('.c-notice-card');
      if (!card) return;

      const noticeId = card.getAttribute('data-notice-id');
      if (!noticeId) return;

      const originalHtml = pinBtn.innerHTML;
      pinBtn.disabled = true;

      try {
        const basePath = getBasePath();
        const csrf = getCsrfToken();

        const resp = await fetch(`${basePath}/togglePinNotice`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf
          },
          body: JSON.stringify({
            id: noticeId,
            _csrf_token: csrf
          })
        });

        const res = await resp.json();

        if (!resp.ok || !res.success) {
          const errMsg = res.error || 'Failed to update pin state.';
          if (window.showFeedbackBanner) {
            window.showFeedbackBanner(errMsg, 'error');
          } else {
            alert(errMsg);
          }
          pinBtn.disabled = false;
          pinBtn.innerHTML = originalHtml;
          return;
        }

        const isPinned = Boolean(res.pinned);
        card.setAttribute('data-pinned', isPinned ? 'true' : 'false');
        pinBtn.setAttribute('aria-label', isPinned ? 'Unpin notice' : 'Pin notice');
        pinBtn.innerHTML = isPinned ? ICON_PIN_BTN_FILLED : ICON_PIN_BTN_OUTLINE;

        let pinBadge = card.querySelector('.c-notice-card__pin');
        if (isPinned) {
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

        if (window.showFeedbackBanner) {
          window.showFeedbackBanner(res.message || 'Pin status updated.', 'success');
        }
      } catch (err) {
        console.error('[Notice Pin Error]', err);
        if (window.showFeedbackBanner) {
          window.showFeedbackBanner('Could not communicate with the server. Please try again.', 'error');
        }
      } finally {
        pinBtn.disabled = false;
      }
      return;
    }

    // -----------------------------------------------------------------------
    // 2. DELETE TRIGGER -> Universal confirmation modal + Soft Delete API
    // -----------------------------------------------------------------------
    const deleteBtn = e.target.closest('.j-notice-delete');
    if (deleteBtn) {
      e.preventDefault();
      e.stopPropagation();

      const card = deleteBtn.closest('.c-notice-card');
      if (!card) return;

      const noticeId = card.getAttribute('data-notice-id');
      if (!noticeId) return;

      const titleEl = card.querySelector('.c-notice-card__title');
      const noticeTitle = titleEl ? titleEl.textContent.trim() : 'this announcement';

      if (typeof window.openUniversalDeleteModal === 'function') {
        window.openUniversalDeleteModal({
          title: 'Delete Notice?',
          description: `Are you sure you want to delete "${noticeTitle}"? This will remove it from the central Notice Board.`,
          buttonText: 'Delete notice',
          onConfirm: async () => {
            const basePath = getBasePath();
            const csrf = getCsrfToken();

            try {
              const resp = await fetch(`${basePath}/deleteNotice`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-CSRF-Token': csrf
                },
                body: JSON.stringify({
                  id: noticeId,
                  _csrf_token: csrf
                })
              });

              const res = await resp.json();

              if (!resp.ok || !res.success) {
                const errMsg = res.error || 'Failed to delete notice.';
                if (window.showFeedbackBanner) {
                  window.showFeedbackBanner(errMsg, 'error');
                } else {
                  alert(errMsg);
                }
                return;
              }

              // Smoothly animate out and remove from DOM
              card.style.transition = 'all 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
              card.style.opacity = '0';
              card.style.transform = 'scale(0.92)';

              setTimeout(() => {
                const grid = card.closest('.c-notice-grid');
                card.remove();
                if (grid && grid.querySelectorAll('.c-notice-card').length === 0) {
                  const emptyState = document.getElementById('j-empty-state');
                  if (emptyState) emptyState.hidden = false;
                }
              }, 250);

              if (window.showFeedbackBanner) {
                window.showFeedbackBanner(res.message || 'Notice deleted successfully.', 'success');
              }
            } catch (err) {
              console.error('[Notice Delete Error]', err);
              if (window.showFeedbackBanner) {
                window.showFeedbackBanner('Could not communicate with the server to delete notice.', 'error');
              }
            }
          }
        });
      }
      return;
    }
  });
})();
