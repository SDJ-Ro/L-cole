/**
 * =========================================================================
 * L'ÉCOLE — UNIVERSAL FEEDBACK COMPONENT JAVASCRIPT
 * =========================================================================
 * Handles:
 *   1. Search filtering across feedback cards
 *   2. Dropdown filtering (All Types, Positive, Constructive, Negative)
 *   3. Opening feedback detail modal (identically formatted expanded card)
 *   4. Teacher "New Feedback" popup modal open/close & card submission
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  const grid = document.getElementById('j-feedback-grid');
  const searchInput = document.getElementById('j-feedback-search-input');
  const detailModal = document.getElementById('j-feedback-detail-modal');
  const emptyState = document.getElementById('j-feedback-empty-state');

  // 1. FILTERING LOGIC
  let currentSearch = '';
  let currentCategory = 'All';

  function applyFilters() {
    if (!grid) return;
    const cards = grid.querySelectorAll('.j-feedback-card:not(.c-feedback-detail-card)');
    let visibleCount = 0;

    cards.forEach(card => {
      const type = (card.getAttribute('data-type') || '').toLowerCase();
      const subject = (card.getAttribute('data-subject') || '').toLowerCase();
      const teacher = (card.getAttribute('data-teacher') || '').toLowerCase();
      const student = (card.getAttribute('data-student') || '').toLowerCase();
      const fullText = (card.getAttribute('data-full-text') || '').toLowerCase();

      const matchesSearch = !currentSearch ||
        subject.includes(currentSearch) ||
        teacher.includes(currentSearch) ||
        student.includes(currentSearch) ||
        fullText.includes(currentSearch);

      const matchesCategory = currentCategory === 'all' ||
        currentCategory === 'all types' ||
        type === currentCategory;

      if (matchesSearch && matchesCategory) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  // Search Listener
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      currentSearch = e.target.value.trim().toLowerCase();
      applyFilters();
    });
  }

  // Listen to Custom Dropdown selections
  document.addEventListener('dropdown-change', (e) => {
    if (e.detail && e.detail.dropdownId === 'j-feedback-type-filter') {
      currentCategory = (e.detail.value || 'All').trim().toLowerCase();
      applyFilters();
    }
  });

  // Native select fallback listener if used
  const nativeFilter = document.querySelector('[name="feedback_type_filter"]');
  if (nativeFilter) {
    nativeFilter.addEventListener('change', (e) => {
      currentCategory = (e.target.value || 'All').trim().toLowerCase();
      applyFilters();
    });
  }

  // 2. DETAIL MODAL LOGIC (EXPANDS THE EXACT SAME CARD)
  function openDetailModal(card) {
    if (!detailModal) return;

    const id = card.getAttribute('data-feedback-id');
    const type = card.getAttribute('data-type') || 'Positive';
    const subject = card.getAttribute('data-subject') || '';
    const teacher = card.getAttribute('data-teacher') || '';
    const teacherRole = card.getAttribute('data-teacher-role') || '';
    const student = card.getAttribute('data-student') || '';
    const parent = card.getAttribute('data-parent') || '';
    const date = card.getAttribute('data-date') || '';
    const fullText = card.getAttribute('data-full-text') || '';

    const typeNormalized = type.toLowerCase().trim();

    // Elements inside the expanded card modal
    const cardContainer = document.getElementById('j-detail-card-container');
    const badgeEl = document.getElementById('j-detail-badge');
    const dateEl = document.getElementById('j-detail-date');
    const parentLineEl = document.getElementById('j-detail-parent-line');
    const subjectEl = document.getElementById('j-detail-subject');
    const teacherEl = document.getElementById('j-detail-teacher');
    const teacherSepEl = document.getElementById('j-detail-teacher-sep');
    const teacherRoleEl = document.getElementById('j-detail-teacher-role');
    const textEl = document.getElementById('j-detail-fulltext');
    const footerTint = document.getElementById('j-detail-footer-tint');
    const footerDate = document.getElementById('j-detail-footer-date');

    // Sync card container type class
    if (cardContainer) {
      cardContainer.className = `c-feedback-card c-feedback-detail-card c-feedback-card--${typeNormalized}`;
    }

    // Badge styling
    if (badgeEl) {
      badgeEl.className = `c-type-badge c-type-badge--${typeNormalized}`;
      badgeEl.innerHTML = `
        ${typeNormalized === 'positive' 
          ? '<svg class="c-type-badge__icon" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><use href="#icon-star"/></svg>'
          : typeNormalized === 'constructive'
          ? '<svg class="c-type-badge__icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-trendingUp"/></svg>'
          : '<svg class="c-type-badge__icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-alertCircle" /></svg>'
        }
        <span>${type}</span>
      `;
    }

    if (dateEl) dateEl.textContent = date;

    if (parentLineEl) {
      if (parent) {
        parentLineEl.textContent = `PARENT: ${parent}${student ? ' | PARENT OF ' + student : ''}`;
        parentLineEl.style.display = 'block';
      } else {
        parentLineEl.style.display = 'none';
      }
    }

    if (subjectEl) subjectEl.textContent = subject;
    if (teacherEl) teacherEl.textContent = teacher || 'School Academic Staff';
    if (teacherRoleEl) {
      if (teacherRole) {
        teacherRoleEl.textContent = teacherRole;
        teacherRoleEl.style.display = 'inline';
        if (teacherSepEl) teacherSepEl.style.display = 'inline';
      } else {
        teacherRoleEl.style.display = 'none';
        if (teacherSepEl) teacherSepEl.style.display = 'none';
      }
    }

    if (textEl) textEl.textContent = fullText;

    // Apply color tint to modal footer
    if (footerTint) {
      footerTint.className = `c-feedback-card__footer c-feedback-card__footer--${typeNormalized}`;
    }
    if (footerDate) footerDate.textContent = date;

    if (typeof window.openModal === 'function') {
      window.openModal(detailModal);
    } else {
      detailModal.style.display = 'flex';
      detailModal.classList.add('c-is-open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeDetailModal() {
    if (!detailModal) return;
    if (typeof window.closeModal === 'function') {
      window.closeModal(detailModal);
    } else {
      detailModal.classList.remove('c-is-open');
      detailModal.style.display = 'none';
      document.body.style.overflow = '';
    }
  }

  // Card click delegated to grid
  if (grid) {
    grid.addEventListener('click', (e) => {
      const card = e.target.closest('.j-feedback-card');
      if (card && !card.classList.contains('c-feedback-detail-card')) {
        openDetailModal(card);
      }
    });
  }

  // Close Detail Modal on backdrop or close button
  if (detailModal) {
    detailModal.querySelectorAll('.j-feedback-modal-close, .j-feedback-modal-backdrop').forEach(btn => {
      btn.addEventListener('click', closeDetailModal);
    });
  }

  // 3. TEACHER "NEW FEEDBACK" MODAL
  const teacherModal = document.getElementById('j-teacher-feedback-modal');

  function openTeacherModal() {
    if (!teacherModal) return;
    teacherModal.style.display = 'flex';
    requestAnimationFrame(() => {
      teacherModal.classList.add('c-is-open');
    });
    document.body.style.overflow = 'hidden';
  }

  function closeTeacherModal() {
    if (!teacherModal) return;
    teacherModal.classList.remove('c-is-open');
    setTimeout(() => {
      if (!teacherModal.classList.contains('c-is-open')) {
        teacherModal.style.display = 'none';
      }
    }, 180);
    document.body.style.overflow = '';
  }

  // Delegated click on document for opening New Feedback modal
  document.addEventListener('click', (e) => {
    if (e.target.closest('.j-open-new-feedback-btn')) {
      e.preventDefault();
      openTeacherModal();
    }
  });

  if (teacherModal) {
    teacherModal.querySelectorAll('.j-teacher-modal-close, .j-teacher-modal-backdrop').forEach(btn => {
      btn.addEventListener('click', closeTeacherModal);
    });

    // Form submission
    const submitBtn = teacherModal.querySelector('.j-submit-teacher-feedback');
    if (submitBtn) {
      submitBtn.addEventListener('click', () => {
        const studentInput = teacherModal.querySelector('input[name="student"]');
        const categoryInput = teacherModal.querySelector('input[name="category"]');
        const subjectInput = teacherModal.querySelector('input[name="subject"]');
        const titleInput = document.getElementById('j-fb-title-input');
        const contentInput = document.getElementById('j-fb-content-input');

        const student = studentInput ? studentInput.value : 'Student';
        const category = categoryInput ? categoryInput.value : 'Positive';
        const subject = subjectInput ? subjectInput.value : 'General';
        const title = titleInput ? titleInput.value.trim() : '';
        const content = contentInput ? contentInput.value.trim() : '';

        if (!title || !content) {
          alert('Please fill in both the Feedback Title and Feedback Content.');
          return;
        }

        const studentClean = student.split(' (')[0];
        const categoryNormalized = category.toLowerCase();
        const dateStr = 'Just Now';

        // Prepend new card to the grid
        if (grid) {
          const cardHtml = `
            <article class="c-feedback-card c-feedback-card--${categoryNormalized} j-feedback-card"
                     data-feedback-id="new-${Date.now()}"
                     data-type="${category}"
                     data-subject="${title}"
                     data-teacher="You (Teacher)"
                     data-teacher-role="${subject} Teacher"
                     data-student="${studentClean}"
                     data-date="${dateStr}"
                     data-full-text="${content}">
              <div class="c-feedback-card__body">
                <div class="c-feedback-card__top">
                  <span class="c-type-badge c-type-badge--${categoryNormalized}">
                    <span>${category}</span>
                  </span>
                  <span class="c-feedback-card__date">${dateStr}</span>
                </div>
                <div class="c-feedback-card__header-info">
                  <h3 class="c-feedback-card__subject c-font-display">${title}</h3>
                  <p class="c-feedback-card__teacher-meta">
                    <span class="c-feedback-card__teacher-name">Student: ${studentClean}</span>
                    <span class="c-feedback-card__teacher-sep">•</span>
                    <span class="c-feedback-card__teacher-role">${subject}</span>
                  </p>
                </div>
                <p class="c-feedback-card__preview">“${content}”</p>
              </div>
              <div class="c-feedback-card__footer c-feedback-card__footer--${categoryNormalized}">
                <div class="c-read-more">
                  <span>Read Full Feedback</span>
                  <svg class="c-icon c-read-more__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <use href="#icon-chevronRight" />
                  </svg>
                </div>
              </div>
            </article>`;

          grid.insertAdjacentHTML('afterbegin', cardHtml);
        }

        // Reset form & close modal
        if (titleInput) titleInput.value = '';
        if (contentInput) contentInput.value = '';
        closeTeacherModal();
      });
    }
  }

  // Keyboard Escape listener
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeDetailModal();
      closeTeacherModal();
    }
  });
});
