/**
 * =========================================================================
 * L'ÉCOLE ADMIN — APPROVALS & VERIFICATIONS COMPONENT CONTROLLER
 * =========================================================================
 * Handles tab switching, status filtering via shared dropdown,
 * card approval/rejection mutations, live pending counters, and reject modal.
 * Ported 1:1 from Admin/verify/script.js.
 * =========================================================================
 */

(function () {
  'use strict';

  const EMPTY_STATE_NOUN = {
    Teachers: 'teacher accounts',
    Extracurriculars: 'extracurricular cards',
    Notices: 'notices'
  };

  const state = {
    activeTab: 'Teachers',
    statusFilter: 'Pending'
  };

  function singularize(pluralType) {
    if (pluralType.endsWith('ies')) return pluralType.slice(0, -3) + 'y';
    if (pluralType.endsWith('s')) return pluralType.slice(0, -1);
    return pluralType;
  }

  // -------------------------------------------------------------------------
  // 1. Tab Switching
  // -------------------------------------------------------------------------
  function initTabs() {
    const tabCards = document.querySelectorAll('.j-tab-card');
    const tabPanels = document.querySelectorAll('.j-tab-panel');

    function applyActiveTab() {
      tabCards.forEach((card) => {
        const isActive = card.dataset.tabName === state.activeTab;
        card.classList.toggle('c-is-active', isActive);
        card.setAttribute('aria-pressed', String(isActive));
      });
      tabPanels.forEach((panel) => {
        const isActive = panel.dataset.tabPanel === state.activeTab;
        panel.classList.toggle('c-is-active', isActive);
        panel.hidden = !isActive;
      });
      applyCardVisibility();
    }

    tabCards.forEach((card) => {
      card.addEventListener('click', () => {
        state.activeTab = card.dataset.tabName;
        applyActiveTab();
      });
    });

    applyActiveTab();
  }

  // -------------------------------------------------------------------------
  // 2. Status Filter (Integrated with shared dropdown component)
  // -------------------------------------------------------------------------
  function initStatusFilter() {
    const filterLabelEl = document.querySelector('.j-filter-label');

    function applyStatusFilter() {
      if (filterLabelEl) {
        filterLabelEl.textContent = `${state.statusFilter === 'All' ? 'All' : state.statusFilter} submissions`;
      }
      applyCardVisibility();
    }

    // Listen for dropdown:change event from shared _dropdown component
    document.addEventListener('dropdown:change', function (e) {
      const dropdown = e.target.closest('#j-select-status-filter');
      if (dropdown && e.detail && e.detail.value) {
        state.statusFilter = e.detail.value;
        applyStatusFilter();
      }
    });

    applyStatusFilter();
  }

  // -------------------------------------------------------------------------
  // 3. Card Visibility & Counters
  // -------------------------------------------------------------------------
  function cardMatchesFilter(cardEl) {
    return state.statusFilter === 'All' || cardEl.dataset.itemStatus === state.statusFilter;
  }

  function applyCardVisibility() {
    document.querySelectorAll('.j-tab-panel').forEach((panel) => {
      const cards = panel.querySelectorAll('.j-approval-card');
      let visibleCount = 0;

      cards.forEach((cardEl) => {
        const matches = cardMatchesFilter(cardEl);
        cardEl.hidden = !matches;
        if (matches) visibleCount += 1;
      });

      updateEmptyState(panel, visibleCount);
    });
  }

  function updateEmptyState(panelEl, visibleCount) {
    const emptyStateEl = panelEl.querySelector('.j-empty-state');
    const emptyStateTextEl = panelEl.querySelector('.j-empty-state-text');
    if (emptyStateEl) emptyStateEl.hidden = visibleCount !== 0;
    if (visibleCount === 0 && emptyStateTextEl) {
      const noun = EMPTY_STATE_NOUN[panelEl.dataset.tabPanel] || 'items';
      const filterWord = state.statusFilter !== 'All' ? `${state.statusFilter.toLowerCase()} ` : '';
      emptyStateTextEl.textContent = `No ${filterWord}${noun} to show.`;
    }
  }

  function updateTabCount(itemType) {
    const countEl = document.querySelector(`.j-tab-count[data-count-for="${itemType}"]`);
    if (!countEl) return;
    const panel = document.querySelector(`.j-tab-panel[data-tab-panel="${itemType}"]`);
    if (!panel) return;
    const pendingCount = panel.querySelectorAll('.j-approval-card[data-item-status="Pending"]').length;
    countEl.textContent = String(pendingCount);
  }

  function setCardStatus(cardEl, status) {
    cardEl.dataset.itemStatus = status;

    // Clean up any decision overlays and unlock card
    cardEl.querySelectorAll('.c-card-overlay').forEach(o => o.remove());
    cardEl.classList.remove('c-is-decision-locked');

    const badgeEl = cardEl.querySelector('.j-status-badge');
    if (badgeEl) {
      badgeEl.textContent = status;
      badgeEl.className = `c-status-badge j-status-badge c-status-badge--${status.toLowerCase()}`;
    }

    const actionsEl = cardEl.querySelector('.j-approval-actions');
    if (actionsEl) actionsEl.hidden = status !== 'Pending';

    updateTabCount(cardEl.dataset.itemType);
    applyCardVisibility();
  }

  function showFeedbackNote(cardEl, feedbackText) {
    const noteEl = cardEl.querySelector('.j-feedback-note');
    if (noteEl) {
      const textEl = noteEl.querySelector('.j-feedback-text');
      if (textEl) textEl.textContent = feedbackText;
      noteEl.hidden = false;
    }
  }

  // -------------------------------------------------------------------------
  // 4. One-Click Approval Action
  // -------------------------------------------------------------------------
  function initApprovalActions() {
    document.addEventListener('click', function (e) {
      const approveBtn = e.target.closest('.j-approve-btn');
      if (approveBtn) {
        e.preventDefault();
        const cardEl = approveBtn.closest('.j-approval-card');
        if (cardEl && !cardEl.classList.contains('c-is-decision-locked')) {
          cardEl.classList.add('c-is-decision-locked');
          if (typeof applyCardDecisionOverlay === 'function') {
            applyCardDecisionOverlay(cardEl, {
              status: 'approved',
              label: 'Approved',
              delay: 1100,
              onComplete: () => {
                setCardStatus(cardEl, 'Approved');
              }
            });
          } else {
            setCardStatus(cardEl, 'Approved');
          }
        }
        return;
      }

      const rejectBtn = e.target.closest('.j-reject-btn');
      if (rejectBtn) {
        e.preventDefault();
        const cardEl = rejectBtn.closest('.j-approval-card');
        if (cardEl) openRejectModal(cardEl);
        return;
      }
    });
  }

  // -------------------------------------------------------------------------
  // 5. Rejection Modal
  // -------------------------------------------------------------------------
  let cardPendingRejection = null;

  function openRejectModal(cardEl) {
    cardPendingRejection = cardEl;

    const modalEl = document.getElementById('j-modal-reject');
    if (!modalEl) return;

    const typeLabelEl = modalEl.querySelector('.j-reject-modal-type');
    const itemNameEl = modalEl.querySelector('.j-reject-modal-item-name');
    const feedbackInput = document.getElementById('j-reject-feedback-input');
    const confirmBtn = document.getElementById('j-reject-confirm-btn');

    if (typeLabelEl) typeLabelEl.textContent = singularize(cardEl.dataset.itemType || 'Item');
    if (itemNameEl) itemNameEl.textContent = cardEl.dataset.itemTitle || '';
    if (feedbackInput) feedbackInput.value = '';
    if (confirmBtn) confirmBtn.disabled = true;

    if (typeof openModal === 'function') {
      openModal(modalEl);
    } else {
      modalEl.classList.add('c-is-open');
    }
    if (feedbackInput) setTimeout(() => feedbackInput.focus(), 100);
  }

  function closeRejectModal() {
    const modalEl = document.getElementById('j-modal-reject');
    if (modalEl) {
      if (typeof closeModal === 'function') {
        closeModal(modalEl);
      } else {
        modalEl.classList.remove('c-is-open');
      }
    }
    cardPendingRejection = null;
  }

  function confirmReject() {
    if (!cardPendingRejection) return;
    const targetCard = cardPendingRejection;
    const feedbackInput = document.getElementById('j-reject-feedback-input');
    const feedbackText = feedbackInput ? feedbackInput.value.trim() : '';
    if (!feedbackText) return;

    closeRejectModal();

    if (typeof applyCardDecisionOverlay === 'function') {
      applyCardDecisionOverlay(targetCard, {
        status: 'declined',
        label: 'Rejected',
        delay: 1100,
        onComplete: () => {
          setCardStatus(targetCard, 'Rejected');
          showFeedbackNote(targetCard, feedbackText);
        }
      });
    } else {
      setCardStatus(targetCard, 'Rejected');
      showFeedbackNote(targetCard, feedbackText);
    }
  }

  function initRejectModal() {
    const modalEl = document.getElementById('j-modal-reject');
    if (!modalEl) return;

    const feedbackInput = document.getElementById('j-reject-feedback-input');
    const confirmBtn = document.getElementById('j-reject-confirm-btn');

    if (feedbackInput && confirmBtn) {
      feedbackInput.addEventListener('input', () => {
        confirmBtn.disabled = feedbackInput.value.trim().length === 0;
      });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('click', confirmReject);
    }

    modalEl.addEventListener('modal:closed', () => {
      cardPendingRejection = null;
    });
  }

  // -------------------------------------------------------------------------
  // Initialization
  // -------------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initStatusFilter();
    initApprovalActions();
    initRejectModal();
  });
})();
