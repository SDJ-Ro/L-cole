/**
 * =========================================================================
 * L'ÉCOLE — COMPLAINTS & INQUIRIES JAVASCRIPT
 * =========================================================================
 * Handles live search, category and status filtering, inline resolution
 * workflow for Management, and the New Inquiry modal for Parent.
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // Core containers
  const listContainer = document.getElementById('j-complaints-list');
  const emptyState    = document.getElementById('j-complaints-empty');
  const searchInput   = document.getElementById('j-complaints-search');
  const countBadge    = document.getElementById('complaints-count') || document.getElementById('j-complaints-count');

  if (!listContainer) return;

  // Filter state
  let currentSearch   = '';
  let currentCategory = 'All';
  let currentStatus   = 'All';

  // -------------------------------------------------------------------------
  // Filtering Engine
  // -------------------------------------------------------------------------
  function applyFilters() {
    const cards = listContainer.querySelectorAll('.j-complaint-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const cardCategory = card.getAttribute('data-category') || '';
      const cardStatus   = card.getAttribute('data-status') || '';
      const cardSearch   = (card.getAttribute('data-search') || '').toLowerCase();

      const matchCategory = (currentCategory === 'All' || currentCategory === 'All Categories' || cardCategory.toLowerCase() === currentCategory.toLowerCase());
      const matchStatus   = (currentStatus === 'All' || currentStatus === 'All Statuses' || cardStatus.toLowerCase() === currentStatus.toLowerCase());
      const matchSearch   = !currentSearch || cardSearch.includes(currentSearch);

      if (matchCategory && matchStatus && matchSearch) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
    }

    if (countBadge) {
      countBadge.textContent = `${visibleCount} ${visibleCount === 1 ? 'Record' : 'Records'}`;
    }
  }

  // Bind Search Input
  if (searchInput) {
    searchInput.addEventListener('input', function (e) {
      currentSearch = e.target.value.trim().toLowerCase();
      applyFilters();
    });
  }

  // -------------------------------------------------------------------------
  // Dropdown Integration (Category & Status)
  // -------------------------------------------------------------------------
  const categoryDropdown = document.getElementById('filter-category');
  if (categoryDropdown) {
    categoryDropdown.addEventListener('dropdown:change', function (e) {
      currentCategory = e.detail?.value || 'All';
      applyFilters();
    });
    categoryDropdown.querySelectorAll('.c-select__option, .c-dropdown__option, .j-dropdown-option').forEach(option => {
      option.addEventListener('click', function () {
        currentCategory = this.getAttribute('data-value') || this.textContent.trim() || 'All';
        applyFilters();
      });
    });
  }

  const statusDropdown = document.getElementById('filter-status');
  if (statusDropdown) {
    statusDropdown.addEventListener('dropdown:change', function (e) {
      currentStatus = e.detail?.value || 'All';
      applyFilters();
    });
    statusDropdown.querySelectorAll('.c-select__option, .c-dropdown__option, .j-dropdown-option').forEach(option => {
      option.addEventListener('click', function () {
        currentStatus = this.getAttribute('data-value') || this.textContent.trim() || 'All';
        applyFilters();
      });
    });
  }

  // -------------------------------------------------------------------------
  // Management Inline Resolution Workflow
  // -------------------------------------------------------------------------
  listContainer.addEventListener('click', function (e) {
    // Click "Mark as Resolved" button
    const startBtn = e.target.closest('.j-start-resolve-btn');
    if (startBtn) {
      const card = startBtn.closest('.j-complaint-card');
      if (!card) return;

      const resolveBar = card.querySelector('.j-resolve-bar');
      const input = card.querySelector('.j-resolve-input');

      if (resolveBar) {
        resolveBar.style.display = 'flex';
        startBtn.style.display = 'none';
        if (input) {
          input.focus();
        }
      }
      return;
    }

    // Click "Cancel" button on resolve bar
    const cancelBtn = e.target.closest('.j-cancel-resolve-btn');
    if (cancelBtn) {
      const card = cancelBtn.closest('.j-complaint-card');
      if (!card) return;

      const resolveBar = card.querySelector('.j-resolve-bar');
      const startBtn = card.querySelector('.j-start-resolve-btn');
      const input = card.querySelector('.j-resolve-input');

      if (resolveBar) resolveBar.style.display = 'none';
      if (startBtn) startBtn.style.display = '';
      if (input) input.value = '';
      return;
    }

    // Click "Resolve" send button
    const sendBtn = e.target.closest('.j-send-resolve-btn');
    if (sendBtn) {
      const card = sendBtn.closest('.j-complaint-card');
      if (!card) return;

      const input = card.querySelector('.j-resolve-input');
      const note = input ? input.value.trim() : '';
      if (!note) return;

      completeResolution(card, note);
    }
  });

  // Enable/disable send button as user types
  listContainer.addEventListener('input', function (e) {
    const input = e.target.closest('.j-resolve-input');
    if (!input) return;

    const card = input.closest('.j-complaint-card');
    if (!card) return;

    const sendBtn = card.querySelector('.j-send-resolve-btn');
    if (sendBtn) {
      sendBtn.disabled = !input.value.trim();
    }
  });

  // Handle Enter key on resolve input
  listContainer.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      const input = e.target.closest('.j-resolve-input');
      if (!input) return;

      const card = input.closest('.j-complaint-card');
      if (!card) return;

      const note = input.value.trim();
      if (!note) return;

      e.preventDefault();
      completeResolution(card, note);
    }
  });

  function completeResolution(card, note) {
    // Update card classes & attributes
    card.classList.remove('c-complaint-card--inprogress');
    card.classList.add('c-complaint-card--resolved');
    card.setAttribute('data-status', 'Resolved');

    // Update Status Badge DOM
    const badge = card.querySelector('.c-status-badge');
    if (badge) {
      badge.className = 'c-status-badge c-status-badge--resolved';
      badge.innerHTML = `
        <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-checkCircle2"/>
        </svg>
        <span class="j-status-text">Resolved</span>
      `;
    }

    // Hide resolve bar and start button
    const resolveBar = card.querySelector('.j-resolve-bar');
    const startBtn   = card.querySelector('.j-start-resolve-btn');
    if (resolveBar) resolveBar.style.display = 'none';
    if (startBtn) startBtn.style.display = 'none';

    // Show and update resolution footer
    const footer = card.querySelector('.j-resolution-footer');
    const noteEl = card.querySelector('.j-resolution-note');
    if (footer && noteEl) {
      noteEl.textContent = note;
      footer.style.display = '';
    }

    // Update data-search attribute
    const currentSearchAttr = card.getAttribute('data-search') || '';
    card.setAttribute('data-search', currentSearchAttr + ' resolved ' + note.toLowerCase());

    // Re-filter if filtered by status
    applyFilters();
  }

  // -------------------------------------------------------------------------
  // Parent "+ New Inquiry" Modal Handling
  // -------------------------------------------------------------------------
  const newInquiryBtn  = document.getElementById('j-new-inquiry-btn');
  const modalLayer     = document.getElementById('j-modal-new-inquiry') || document.getElementById('j-inquiry-modal-layer');
  const closeModalBtns = document.querySelectorAll('.j-close-inquiry-modal, .j-modal-close, .j-modal-backdrop');
  const inquiryForm    = document.getElementById('j-new-inquiry-form');

  if (newInquiryBtn && modalLayer) {
    newInquiryBtn.addEventListener('click', function () {
      if (typeof openModal === 'function') {
        openModal(modalLayer);
      } else {
        modalLayer.classList.add('c-is-open');
      }
      const subjectInput = document.getElementById('j-inquiry-subject');
      if (subjectInput) subjectInput.focus();
    });

    if (inquiryForm) {
      inquiryForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const subjectInput  = document.getElementById('j-inquiry-subject');
        const messageInput  = document.getElementById('j-inquiry-message');

        const catDropdown = document.getElementById('inquiry-form-category');
        const stuDropdown = document.getElementById('inquiry-form-student');

        const subject  = subjectInput ? subjectInput.value.trim() : 'Inquiry';
        const category = (window.getDropdownValue && window.getDropdownValue('inquiry-form-category'))
          || catDropdown?.querySelector('input[type="hidden"]')?.value
          || catDropdown?.querySelector('.c-select__value')?.textContent.trim()
          || 'Academic';
        const student = (window.getDropdownValue && window.getDropdownValue('inquiry-form-student'))
          || stuDropdown?.querySelector('input[type="hidden"]')?.value
          || stuDropdown?.querySelector('.c-select__value')?.textContent.trim()
          || 'Nethmi Perera (Grade 10-A)';
        const message  = messageInput ? messageInput.value.trim() : '';

        if (!subject || !message) return;

        // Build new card DOM matching _complaint_card.php
        const todayFormatted = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date());
        const catKey = category.toLowerCase().replace(/[^a-z0-9_-]/g, '');
        const newCard = document.createElement('article');
        newCard.className = `c-complaint-card c-complaint-card--inprogress c-complaint-card--cat-${catKey} j-complaint-card`;
        newCard.setAttribute('data-status', 'In Progress');
        newCard.setAttribute('data-category', category);
        newCard.setAttribute('data-search', `${subject} ${message} ${category} in progress ${student} ${todayFormatted}`.toLowerCase());

        newCard.innerHTML = `
          <div class="c-complaint-card__body">
            <div class="c-complaint-card__main">
              <div class="c-complaint-card__meta">
                <span class="c-status-badge c-status-badge--inprogress">
                  <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#icon-clock"/>
                  </svg>
                  <span class="j-status-text">In Progress</span>
                </span>
                <span class="c-category-badge">${escapeHtml(category)}</span>
                <span class="c-dot-sep">•</span>
                <span class="c-complaint-card__date">${todayFormatted}</span>
              </div>
              <h3 class="c-complaint-card__subject">${escapeHtml(subject)}</h3>
              <p class="c-complaint-card__message">${escapeHtml(message).replace(/\n/g, '<br>')}</p>
              <div class="c-complaint-card__context">
                <span class="c-complaint-card__student"><strong>Student:</strong> ${escapeHtml(student)}</span>
              </div>
            </div>
          </div>
          <div class="c-complaint-card__resolution-footer j-resolution-footer" style="display: none;">
            <p class="c-complaint-card__resolution-label">School Response:</p>
            <p class="c-complaint-card__resolution-text j-resolution-note"></p>
          </div>
        `;

        // Prepend to list
        listContainer.insertBefore(newCard, listContainer.firstChild);

        // Show bottom toast inside modal before closing
        if (typeof showFormBottomToast === 'function') {
          showFormBottomToast(inquiryForm, {
            message: 'Inquiry submitted! Reference: INQ-2026',
            durationMs: 900,
            onComplete: () => {
              inquiryForm.reset();
              if (typeof closeModal === 'function') {
                closeModal(modalLayer);
              } else {
                modalLayer.classList.remove('c-is-open');
              }
              applyFilters();
            }
          });
        } else {
          inquiryForm.reset();
          if (typeof closeModal === 'function') {
            closeModal(modalLayer);
          } else {
            modalLayer.classList.remove('c-is-open');
          }
          applyFilters();
        }
      });
    }
  }

  const escapeHtml = window.escapeHtml || function (str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  };
});
