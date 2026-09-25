/**
 * =========================================================================
 * L'ÉCOLE — PARENT APPROVALS CONTROLLER
 * =========================================================================
 * Handles:
 *   1. Status tab card switching (Pending / Approved / Rejected)
 *   2. Card visibility filtered by active status
 *   3. One-click Approve action (parent grants consent)
 *   4. Decline action with optional reason note (parent declines)
 *   5. Live counter updates on each status metric card
 * =========================================================================
 */

(function () {
  'use strict';

  const state = {
    activeStatus: 'Pending'
  };

  // -------------------------------------------------------------------------
  // 1. STATUS TAB CARDS
  // -------------------------------------------------------------------------
  function initStatusTabs() {
    const tabCards = document.querySelectorAll('.j-parent-tab-card');

    if (!tabCards.length) return;

    function applyTabState() {
      tabCards.forEach((card) => {
        const isActive = card.dataset.statusName === state.activeStatus;
        card.classList.toggle('c-is-active', isActive);
        card.setAttribute('aria-pressed', String(isActive));
      });
      applyCardVisibility();
    }

    tabCards.forEach((card) => {
      card.addEventListener('click', () => {
        state.activeStatus = card.dataset.statusName;
        applyTabState();
      });
    });

    applyTabState();
  }

  // -------------------------------------------------------------------------
  // 2. CARD VISIBILITY
  // -------------------------------------------------------------------------
  function applyCardVisibility() {
    const grid = document.getElementById('j-parent-approval-grid');
    const emptyState = document.getElementById('j-parent-empty-state');
    const summaryEl = document.getElementById('j-parent-summary-label');

    if (!grid) return;

    const cards = grid.querySelectorAll('.j-parent-approval-card');
    let visibleCount = 0;

    cards.forEach((cardEl) => {
      const matches = cardEl.dataset.itemStatus === state.activeStatus;
      cardEl.hidden = !matches;
      if (matches) visibleCount++;
    });

    if (emptyState) {
      emptyState.hidden = visibleCount > 0;
    }

    if (summaryEl) {
      const noun = state.activeStatus.toLowerCase();
      summaryEl.textContent = `${visibleCount} ${noun} request${visibleCount !== 1 ? 's' : ''}`;
    }
  }

  // -------------------------------------------------------------------------
  // 3. SET CARD STATUS (approve / decline)
  // -------------------------------------------------------------------------
  function setCardStatus(cardEl, status) {
    cardEl.dataset.itemStatus = status;

    // Clean up any decision overlays and unlock card
    cardEl.querySelectorAll('.c-card-overlay').forEach(o => o.remove());
    cardEl.classList.remove('c-is-decision-locked');

    // Update status badge
    const badgeEl = cardEl.querySelector('.j-status-badge');
    if (badgeEl) {
      badgeEl.textContent = status;
      badgeEl.className = `c-status-badge j-status-badge c-status-badge--${status.toLowerCase()}`;
    }

    // Hide approve/decline action row
    const actionsEl = cardEl.querySelector('.j-parent-approval-actions');
    if (actionsEl) actionsEl.hidden = true;

    // Update the metric tab counter for the relevant status
    updateStatusCount(status);
    updateStatusCount('Pending');

    // Re-apply visibility so card disappears from current filter view
    applyCardVisibility();
  }

  function updateStatusCount(status) {
    const grid = document.getElementById('j-parent-approval-grid');
    if (!grid) return;

    const count = grid.querySelectorAll(`.j-parent-approval-card[data-item-status="${status}"]`).length;
    const countEl = document.querySelector(`.j-parent-tab-card[data-status-name="${status}"] .j-pa-count`);
    if (countEl) countEl.textContent = String(count);
  }

  function showDeclineNote(cardEl, noteText) {
    const noteEl = cardEl.querySelector('.j-pa-decline-note');
    if (noteEl) {
      const textEl = noteEl.querySelector('.j-pa-decline-text');
      if (textEl) textEl.textContent = noteText || 'Request declined by parent.';
      noteEl.hidden = false;
    }
  }

  // -------------------------------------------------------------------------
  // 4. ONE-CLICK APPROVE
  // -------------------------------------------------------------------------
  function initApproveAction() {
    document.addEventListener('click', function (e) {
      const approveBtn = e.target.closest('.j-pa-approve-btn');
      if (!approveBtn) return;
      e.preventDefault();
      const cardEl = approveBtn.closest('.j-parent-approval-card');
      if (cardEl && !cardEl.classList.contains('c-is-decision-locked')) {
        cardEl.classList.add('c-is-decision-locked');
        if (typeof applyCardDecisionOverlay === 'function') {
          applyCardDecisionOverlay(cardEl, {
            status: 'approved',
            label: 'Consent Granted',
            delay: 1100,
            onComplete: () => {
              setCardStatus(cardEl, 'Approved');
            }
          });
        } else {
          setCardStatus(cardEl, 'Approved');
        }
      }
    });
  }

  // -------------------------------------------------------------------------
  // 5. DECLINE MODAL
  // -------------------------------------------------------------------------
  let cardPendingDecline = null;

  function openDeclineModal(cardEl) {
    cardPendingDecline = cardEl;
    const modalEl = document.getElementById('j-pa-decline-modal');
    if (!modalEl) return;

    const itemNameEl = modalEl.querySelector('.j-pa-modal-item-name');
    const feedbackInput = document.getElementById('j-pa-decline-reason');

    if (itemNameEl) itemNameEl.textContent = cardEl.dataset.itemTitle || 'this request';
    if (feedbackInput) feedbackInput.value = '';

    if (typeof openModal === 'function') {
      openModal(modalEl);
    } else {
      modalEl.classList.add('c-is-open');
    }
    if (feedbackInput) setTimeout(() => feedbackInput.focus(), 120);
  }

  function closeDeclineModal() {
    const modalEl = document.getElementById('j-pa-decline-modal');
    if (modalEl) {
      if (typeof closeModal === 'function') {
        closeModal(modalEl);
      } else {
        modalEl.classList.remove('c-is-open');
      }
    }
    cardPendingDecline = null;
  }

  function confirmDecline() {
    if (!cardPendingDecline) return;
    const targetCard = cardPendingDecline;
    const feedbackInput = document.getElementById('j-pa-decline-reason');
    const reasonText = feedbackInput ? feedbackInput.value.trim() : '';

    closeDeclineModal();

    if (typeof applyCardDecisionOverlay === 'function') {
      applyCardDecisionOverlay(targetCard, {
        status: 'declined',
        label: 'Declined',
        delay: 1100,
        onComplete: () => {
          setCardStatus(targetCard, 'Rejected');
          showDeclineNote(targetCard, reasonText || 'Request declined.');
        }
      });
    } else {
      setCardStatus(targetCard, 'Rejected');
      showDeclineNote(targetCard, reasonText || 'Request declined.');
    }
  }

  function initDeclineModal() {
    const modalEl = document.getElementById('j-pa-decline-modal');
    if (!modalEl) return;

    const confirmBtn = document.getElementById('j-pa-decline-confirm-btn');
    if (confirmBtn) confirmBtn.addEventListener('click', confirmDecline);

    modalEl.querySelectorAll('.j-pa-modal-close, .j-pa-modal-backdrop').forEach((el) => {
      el.addEventListener('click', closeDeclineModal);
    });

    // Delegated click for opening decline modal
    document.addEventListener('click', function (e) {
      const declineBtn = e.target.closest('.j-pa-decline-btn');
      if (!declineBtn) return;
      e.preventDefault();
      const cardEl = declineBtn.closest('.j-parent-approval-card');
      if (cardEl) openDeclineModal(cardEl);
    });
  }

  // -------------------------------------------------------------------------
  // INIT
  // -------------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', function () {
    initStatusTabs();
    initApproveAction();
    initDeclineModal();
  });
})();
