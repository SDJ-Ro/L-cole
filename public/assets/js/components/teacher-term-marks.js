/**
 * =========================================================================
 * L'ÉCOLE — TEACHER TERM MARKS SCRIPT
 * =========================================================================
 * Handles master-detail split workspace:
 * - Clicking "Edit Marks" opens the right-docked panel and reflows grid to 2 cols
 * - Closing panel returns grid to 4 cols
 * - Subject filtering inside panel
 * - Live marks update and feedback
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  const workspace = document.querySelector('.j-term-marks-workspace');
  const panelSide = document.querySelector('.j-term-marks-side');
  const panel = document.querySelector('.j-term-marks-panel');
  const cards = document.querySelectorAll('.j-student-achievement-card');

  if (!workspace || !panelSide || !panel) return;

  // Active student state
  let activeCard = null;

  // Mineral tone classes to cycle/reset on the panel banner
  const ALL_TONES = [
    'c-bg-card-slate',
    'c-bg-card-steel',
    'c-bg-card-taupe',
    'c-bg-card-sand',
    'c-bg-card-seafoam',
    'c-bg-card-sage'
  ];

  // Mapping from card mineral tone to panel faded background tint
  const TONE_TO_PANEL_TINT = {
    'c-bg-card-slate': 'c-panel-tint--slate',
    'c-bg-card-steel': 'c-panel-tint--steel',
    'c-bg-card-taupe': 'c-panel-tint--taupe',
    'c-bg-card-sand': 'c-panel-tint--sand',
    'c-bg-card-seafoam': 'c-panel-tint--seafoam',
    'c-bg-card-sage': 'c-panel-tint--sage'
  };
  const ALL_PANEL_TINTS = Object.values(TONE_TO_PANEL_TINT);

  // Panel Elements
  const bannerEl      = panel.querySelector('.j-panel-student-banner');
  const nameEl        = panel.querySelector('.j-panel-student-name');
  const indexEl       = panel.querySelector('.j-panel-student-index');
  const classEl       = panel.querySelector('.j-panel-student-class');
  const initialsEl    = panel.querySelector('.j-panel-student-initials');
  const avatarImgEl   = panel.querySelector('.j-panel-student-avatar');
  const feedbackEl    = panel.querySelector('.j-mark-feedback');
  const toastEl       = panel.querySelector('.j-marks-toast');
  const inputsGrid    = panel.querySelector('.j-subject-inputs-container');
  const markInputs    = panel.querySelectorAll('.j-mark-input');
  const subjectRows   = panel.querySelectorAll('.j-subject-row');

  // -------------------------------------------------------------------------
  // Open Marks Panel for a Student Card
  // -------------------------------------------------------------------------
  function openMarksPanel(card) {
    if (!card) return;

    // Clear active state on previously selected card
    if (activeCard) {
      activeCard.classList.remove('c-student-card--active-editing');
    }

    activeCard = card;
    activeCard.classList.add('c-student-card--active-editing');

    // Extract student data from dataset
    const name      = card.dataset.studentName || 'Student';
    const index     = card.dataset.studentIndex || '—';
    const sClass    = card.dataset.studentClass || 'Class 6-A';
    const avatar    = card.dataset.studentAvatar || '';
    const initials  = card.dataset.studentInitials || 'ST';
    const tone      = card.dataset.studentTone || 'c-bg-card-slate';
    const feedback  = card.dataset.studentFeedback || '';

    let marks = {};
    try {
      marks = JSON.parse(card.dataset.studentMarks || '{}');
    } catch (e) {
      marks = {};
    }

    // Update panel background to faded tint of the student card
    ALL_PANEL_TINTS.forEach(t => panel.classList.remove(t));
    const panelTint = TONE_TO_PANEL_TINT[tone] || 'c-panel-tint--slate';
    panel.classList.add(panelTint);

    // Update banner metadata & tone
    if (bannerEl) {
      ALL_TONES.forEach(t => bannerEl.classList.remove(t));
      bannerEl.classList.add(tone);
    }

    if (nameEl) nameEl.textContent = name;
    if (indexEl) indexEl.textContent = index;
    if (classEl) classEl.textContent = sClass;

    // Avatar vs Initials
    if (avatar && avatarImgEl) {
      avatarImgEl.src = avatar;
      avatarImgEl.style.display = 'block';
      if (initialsEl) initialsEl.style.display = 'none';
    } else {
      if (avatarImgEl) avatarImgEl.style.display = 'none';
      if (initialsEl) {
        initialsEl.textContent = initials;
        initialsEl.style.display = 'flex';
      }
    }

    // Populate marks inputs
    markInputs.forEach(input => {
      const subj = input.dataset.subject;
      input.value = (marks[subj] !== undefined && marks[subj] !== null) ? marks[subj] : '';
    });

    // Populate feedback
    if (feedbackEl) {
      feedbackEl.value = feedback;
    }

    // Reset sort dropdown to "All"
    resetPanelSubjectFilter();

    // Show panel & trigger split layout
    panelSide.style.display = 'block';
    workspace.classList.add('c-term-marks-workspace--panel-open');

    // Scroll panel into view smoothly if on mobile
    if (window.innerWidth < 960) {
      panelSide.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  // -------------------------------------------------------------------------
  // Close Marks Panel
  // -------------------------------------------------------------------------
  function closeMarksPanel() {
    panelSide.style.display = 'none';
    workspace.classList.remove('c-term-marks-workspace--panel-open');

    if (activeCard) {
      activeCard.classList.remove('c-student-card--active-editing');
      activeCard = null;
    }

    if (toastEl) toastEl.style.display = 'none';
  }

  // Bind close buttons (Cross button and Cancel button)
  const closeBtns = document.querySelectorAll('.j-close-marks-panel');
  closeBtns.forEach(btn => {
    btn.addEventListener('click', closeMarksPanel);
  });

  // Close on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && panelSide.style.display !== 'none') {
      closeMarksPanel();
    }
  });

  // Bind Edit Marks buttons on cards
  cards.forEach(card => {
    const editBtn = card.querySelector('.j-edit-marks');
    if (editBtn) {
      editBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        openMarksPanel(card);
      });
    }
  });

  // -------------------------------------------------------------------------
  // Subject Filter (Sort Dropdown) Inside Panel
  // -------------------------------------------------------------------------
  const panelFilter = document.getElementById('j-panel-subject-filter');
  function resetPanelSubjectFilter() {
    if (!panelFilter) return;
    const valueEl = panelFilter.querySelector('.j-panel-subject-value');
    if (valueEl) valueEl.textContent = 'All';

    panelFilter.querySelectorAll('.c-select__option').forEach(opt => {
      const isAll = opt.dataset.value === 'All';
      opt.classList.toggle('c-is-selected', isAll);
      const existingCheck = opt.querySelector('.c-select__option-check');
      if (isAll && !existingCheck) {
        opt.insertAdjacentHTML('beforeend', '<svg class="c-icon c-select__option-check" width="14" height="14"><use href="#icon-check"/></svg>');
      } else if (!isAll && existingCheck) {
        existingCheck.remove();
      }
    });

    subjectRows.forEach(row => { row.style.display = 'flex'; });
    if (inputsGrid) inputsGrid.classList.remove('c-term-marks-panel__grid--single');
  }

  if (panelFilter) {
    const trigger = panelFilter.querySelector('.j-panel-subject-trigger');
    const valueEl = panelFilter.querySelector('.j-panel-subject-value');
    const options = panelFilter.querySelectorAll('.c-select__option');

    if (trigger) {
      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        panelFilter.classList.toggle('c-is-open');
        const isOpen = panelFilter.classList.contains('c-is-open');
        trigger.setAttribute('aria-expanded', isOpen);
      });
    }

    options.forEach(opt => {
      opt.addEventListener('click', function (e) {
        e.stopPropagation();
        const selectedVal = opt.dataset.value;
        if (valueEl) valueEl.textContent = selectedVal;

        options.forEach(o => {
          o.classList.remove('c-is-selected');
          const check = o.querySelector('.c-select__option-check');
          if (check) check.remove();
        });
        opt.classList.add('c-is-selected');
        opt.insertAdjacentHTML('beforeend', '<svg class="c-icon c-select__option-check" width="14" height="14"><use href="#icon-check"/></svg>');

        panelFilter.classList.remove('c-is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');

        // Apply filter to subject rows
        if (selectedVal === 'All') {
          subjectRows.forEach(row => { row.style.display = 'flex'; });
          if (inputsGrid) inputsGrid.classList.remove('c-term-marks-panel__grid--single');
        } else {
          subjectRows.forEach(row => {
            row.style.display = (row.dataset.subject === selectedVal) ? 'flex' : 'none';
          });
          if (inputsGrid) inputsGrid.classList.add('c-term-marks-panel__grid--single');
        }
      });
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
      if (!panelFilter.contains(e.target)) {
        panelFilter.classList.remove('c-is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // -------------------------------------------------------------------------
  // Page-Level Filter Bar: Search Input
  // -------------------------------------------------------------------------
  const searchInput = document.querySelector('.j-search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = searchInput.value.toLowerCase().trim();
      cards.forEach(card => {
        const name = (card.dataset.studentName || '').toLowerCase();
        const idx = (card.dataset.studentIndex || '').toLowerCase();
        if (!q || name.includes(q) || idx.includes(q)) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }

  // -------------------------------------------------------------------------
  // Page-Level Filter Bar: Term Dropdown
  // -------------------------------------------------------------------------
  const termSelect = document.getElementById('j-select-term-filter');
  if (termSelect) {
    const trigger = termSelect.querySelector('.c-select__trigger');
    const valEl = termSelect.querySelector('.j-select-value-term') || termSelect.querySelector('.c-select__value');
    const options = termSelect.querySelectorAll('.c-select__option');

    if (trigger) {
      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        termSelect.classList.toggle('c-is-open');
        trigger.setAttribute('aria-expanded', termSelect.classList.contains('c-is-open'));
      });
    }

    options.forEach(opt => {
      opt.addEventListener('click', function (e) {
        e.stopPropagation();
        if (valEl) valEl.textContent = opt.dataset.value;
        options.forEach(o => o.classList.remove('c-is-selected'));
        opt.classList.add('c-is-selected');
        termSelect.classList.remove('c-is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('click', function (e) {
      if (!termSelect.contains(e.target)) {
        termSelect.classList.remove('c-is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // -------------------------------------------------------------------------
  // Submit Marks Handling
  // -------------------------------------------------------------------------
  const submitBtn = panel.querySelector('.j-submit-marks');
  if (submitBtn) {
    submitBtn.addEventListener('click', function () {
      if (!activeCard) return;

      const updatedMarks = {};
      markInputs.forEach(input => {
        const subj = input.dataset.subject;
        const val = input.value.trim();
        if (val !== '') {
          updatedMarks[subj] = parseInt(val, 10);
        }
      });

      const updatedFeedback = feedbackEl ? feedbackEl.value.trim() : '';

      // Save back to active card dataset
      activeCard.dataset.studentMarks = JSON.stringify(updatedMarks);
      activeCard.dataset.studentFeedback = updatedFeedback;

      // Show bottom success toast in marks panel
      if (toastEl) {
        toastEl.style.display = 'flex';
        setTimeout(() => {
          toastEl.style.display = 'none';
        }, 2600);
      }
    });
  }
});
