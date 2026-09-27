/**
 * L'École — Teacher Achievements Interactive Component
 * Handles card search/filtering, timeline view switching, and record/review modals.
 */
(function () {
  'use strict';

  function initTeacherAchievements() {
    // Views
    const mainSection   = document.getElementById('j-achievements-main-section');
    const timelineView  = document.getElementById('j-view-student-timeline');
    const backBtn       = document.getElementById('j-back-to-grid');

    // Grid & Filters
    const searchInput   = document.getElementById('j-search-student-achievements');
    const emptyState    = document.getElementById('j-achievements-empty-state');
    const grid          = document.getElementById('j-view-student-grid');

    // Timeline Banner Elements
    const bannerName      = document.getElementById('j-timeline-student-name');
    const bannerIndex     = document.getElementById('j-timeline-student-index');
    const bannerImg       = document.getElementById('j-timeline-avatar-img');
    const bannerInitials  = document.getElementById('j-timeline-initials');
    const bannerIssueWrap = document.getElementById('j-timeline-issue-action');
    const btnTimelineRec  = document.getElementById('j-timeline-record-btn');

    // Modal Elements
    const modalEl         = document.getElementById('j-record-modal');
    const modalEyebrow    = document.getElementById('j-record-modal-eyebrow');
    const modalTitle      = document.getElementById('j-record-modal-title');
    const modalDesc       = document.getElementById('j-record-modal-desc');
    const iconBadge       = document.getElementById('j-record-modal-icon-badge');
    const iconAward       = document.getElementById('j-rec-icon-award');
    const iconIssue       = document.getElementById('j-rec-icon-issue');
    const issueCallout    = document.getElementById('j-rec-issue-callout');
    const form            = document.getElementById('j-record-achievement-form');
    const inputStudentName  = document.getElementById('j-rec-student-name');
    const inputStudentIndex = document.getElementById('j-rec-student-index');
    const displayAvatar   = document.getElementById('j-rec-display-avatar');
    const displayName     = document.getElementById('j-rec-display-name');
    const displayIndex    = document.getElementById('j-rec-display-index');
    const displayType     = document.getElementById('j-rec-display-type');
    const inputTitle      = document.getElementById('j-rec-title');
    const inputIssuer     = document.getElementById('j-rec-issuer');
    const inputDesc       = document.getElementById('j-rec-desc');
    const inputTypeHidden = document.getElementById('j-rec-type-hidden');
    const submitBtn       = document.getElementById('j-rec-submit-btn');

    // Evidence & Upload Box Elements
    const previewModal    = document.getElementById('j-evidence-preview-modal');
    const evidenceBtn     = document.getElementById('j-rec-evidence-link');
    const uploadSection   = document.getElementById('j-rec-proof-upload-section');
    const viewEvidenceSection = document.getElementById('j-rec-proof-view-section');
    const uploadBox       = document.getElementById('j-rec-upload-box');
    const fileInput       = document.getElementById('j-rec-file-input');
    const filenameLabel   = document.getElementById('j-rec-upload-filename');

    let currentCategory = 'all';
    let currentSearch = '';
    let activeStudent = null;

    /* -----------------------------------------------------------------------
       1. LIVE SEARCH & CATEGORY FILTER ON STUDENT CARDS
       ----------------------------------------------------------------------- */
    function filterCards() {
      const cards = Array.from(document.querySelectorAll('.j-student-achievement-card'));
      let visibleCount = 0;
      const q = currentSearch.trim().toLowerCase();
      const cat = currentCategory.toLowerCase();

      cards.forEach(card => {
        const name = (card.getAttribute('data-student-name') || '').toLowerCase();
        const index = (card.getAttribute('data-student-index') || '').toLowerCase();
        const type = (card.getAttribute('data-item-type') || '').toLowerCase();

        const matchesSearch = !q || name.includes(q) || index.includes(q);
        const matchesCategory = cat === 'all' || type === cat;

        if (matchesSearch && matchesCategory) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }
      if (grid) {
        grid.style.display = visibleCount === 0 ? 'none' : 'grid';
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        currentSearch = this.value;
        filterCards();
      });
    }

    document.addEventListener('dropdown-change', function (e) {
      if (e.detail && e.detail.id === 'j-select-category-filter') {
        currentCategory = e.detail.value;
        filterCards();
      }
    });

    /* -----------------------------------------------------------------------
       HELPER: EXTRACT STUDENT DATA FROM A CARD OR ACTIVE STUDENT
       ----------------------------------------------------------------------- */
    function getStudentDataFromCard(card) {
      if (!card) return null;
      const name = card.getAttribute('data-student-name') || card.querySelector('.c-student-card__name')?.textContent?.trim() || 'Student';
      const index = card.getAttribute('data-student-index') || card.querySelector('.c-student-card__index')?.textContent?.trim() || '';
      const avatarImg = card.querySelector('.c-student-card__avatar');
      const avatarSrc = avatarImg ? avatarImg.getAttribute('src') : (card.getAttribute('data-student-avatar') || '');
      let initials = card.getAttribute('data-student-initials') || '';
      if (!initials && name) {
        const parts = name.trim().split(/\s+/);
        initials = ((parts[0] || 'S')[0] + (parts[1] || '')[0]).toUpperCase();
      }
      const hasIssue = card.getAttribute('data-has-issue') === '1' || card.classList.contains('c-student-card--highlighted');
      const itemType = card.getAttribute('data-item-type') || 'Class';
      const classTxt = card.getAttribute('data-student-class') || 'Class';
      return { name, index, avatarSrc, initials, hasIssue, itemType, classTxt };
    }

    function setRecDatepicker(isoDate) {
      const dp = document.getElementById('j-rec-datepicker');
      if (!dp) return;
      const input = dp.querySelector('.j-dp-input');
      const label = dp.querySelector('.j-dp-label');
      if (input) input.value = isoDate;
      if (label && isoDate) {
        const parts = isoDate.split('-');
        if (parts.length === 3) {
          const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
          label.textContent = new Intl.DateTimeFormat('en-US', { day: 'numeric', month: 'short', year: 'numeric' }).format(d);
          label.classList.remove('c-dp-placeholder');
        }
      }
    }

    function syncModalStudentContext(student) {
      const s = student || activeStudent || { name: 'Student', index: 'S0000/0000', initials: 'ST', itemType: 'Student' };
      if (inputStudentName) inputStudentName.value = s.name || '';
      if (inputStudentIndex) inputStudentIndex.value = s.index || '';
      if (displayName) displayName.textContent = s.name || 'Student Name';
      if (displayIndex) displayIndex.textContent = `Index: ${s.index || 'S0000/0000'}`;
      if (displayAvatar) displayAvatar.textContent = s.initials || 'ST';
      if (displayType) displayType.textContent = (s.itemType || 'Student').toUpperCase();
    }

    /* -----------------------------------------------------------------------
       2. TIMELINE VIEW TOGGLING (Clicking 'View' on any student card)
       ----------------------------------------------------------------------- */
    document.addEventListener('click', function (e) {
      const viewBtn = e.target.closest('.j-view-student');
      if (!viewBtn) return;

      e.preventDefault();
      const card = viewBtn.closest('.j-student-achievement-card');
      const student = getStudentDataFromCard(card);
      if (!student) return;

      activeStudent = student;

      // Update Timeline Banner
      if (bannerName) bannerName.textContent = student.name;
      if (bannerIndex) bannerIndex.textContent = student.index;

      if (student.avatarSrc && bannerImg) {
        bannerImg.src = student.avatarSrc;
        bannerImg.style.display = 'block';
        if (bannerInitials) bannerInitials.style.display = 'none';
      } else if (bannerInitials) {
        bannerInitials.textContent = student.initials;
        bannerInitials.style.display = 'flex';
        if (bannerImg) bannerImg.style.display = 'none';
      }

      // Update right actions in timeline banner
      if (bannerIssueWrap) {
        bannerIssueWrap.style.display = student.hasIssue ? 'block' : 'none';
      }
      if (btnTimelineRec) {
        btnTimelineRec.setAttribute('data-name', student.name);
        btnTimelineRec.setAttribute('data-index', student.index);
      }

      // Switch view: Hide grid, show timeline page
      if (mainSection) mainSection.style.display = 'none';
      if (timelineView) timelineView.style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Back to Achievements Grid
    document.addEventListener('click', function (e) {
      const backTrigger = e.target.closest('#j-back-to-grid, .j-back-to-grid');
      if (!backTrigger) return;
      e.preventDefault();
      if (timelineView) timelineView.style.display = 'none';
      if (mainSection) mainSection.style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* -----------------------------------------------------------------------
       3. RECORD ACHIEVEMENT MODAL (Clicking 'Record' on card OR timeline view)
       ----------------------------------------------------------------------- */
    document.addEventListener('click', function (e) {
      const recordBtn = e.target.closest('.j-record-student');
      if (!recordBtn) return;

      e.preventDefault();
      const card = recordBtn.closest('.j-student-achievement-card');
      const student = card ? getStudentDataFromCard(card) : activeStudent;

      if (student) {
        activeStudent = student;
      }

      const s = activeStudent || { name: 'Student', index: 'S0000/0000', initials: 'ST', itemType: 'Student' };

      // Mode: Record Achievement
      if (modalEyebrow) modalEyebrow.textContent = 'Achievement Record';
      if (modalTitle) modalTitle.textContent = s.name ? `Record Achievement — ${s.name}` : 'Record Achievement';
      if (modalDesc) modalDesc.textContent = 'Add a new verified student honor, award, or recognition.';
      if (iconAward) iconAward.style.display = 'block';
      if (iconIssue) iconIssue.style.display = 'none';
      if (iconBadge) {
        iconBadge.style.background = 'rgba(255, 255, 255, 0.65)';
        iconBadge.style.color = '#8C5A24';
      }
      if (issueCallout) issueCallout.style.display = 'none';
      if (submitBtn) submitBtn.textContent = 'Record Achievement';

      // Section display: Show Upload Box, Hide View Evidence Box
      if (uploadSection) uploadSection.style.display = 'block';
      if (viewEvidenceSection) viewEvidenceSection.style.display = 'none';

      // Sync visible student context and hidden inputs
      syncModalStudentContext(s);

      // Reset form fields
      if (form) form.reset();
      if (typeof window.resetDropdown === 'function') {
        window.resetDropdown('j-rec-type-dropdown', 'Select an achievement type');
      }
      if (inputTypeHidden) inputTypeHidden.value = 'Academic';
      if (inputTitle) inputTitle.value = '';
      setRecDatepicker(new Date().toISOString().split('T')[0]);
      if (inputIssuer) inputIssuer.value = '';
      if (inputDesc) inputDesc.value = '';
      if (filenameLabel) filenameLabel.style.display = 'none';

      if (typeof window.openModal === 'function') {
        window.openModal('#j-record-modal');
      } else if (modalEl) {
        modalEl.style.display = 'flex';
        modalEl.classList.add('c-is-open');
      }
    });

    /* -----------------------------------------------------------------------
       4. REVIEW ISSUE MODAL (Clicking 'View Issue' in Timeline Banner)
       ----------------------------------------------------------------------- */
    document.addEventListener('click', function (e) {
      const issueBtn = e.target.closest('.j-view-issue, #j-btn-view-issue');
      if (!issueBtn) return;

      e.preventDefault();
      const s = activeStudent || { name: 'Student', index: 'S0000/0000', initials: 'ST', itemType: 'Student' };

      // Mode: Review Issue
      if (modalEyebrow) modalEyebrow.textContent = 'Issue Report';
      if (modalTitle) modalTitle.textContent = s.name ? `Review Issue — ${s.name}` : 'Review Issue';
      if (modalDesc) modalDesc.textContent = "Review the student's reported missing record and evidence.";
      if (iconAward) iconAward.style.display = 'none';
      if (iconIssue) iconIssue.style.display = 'block';
      if (iconBadge) {
        iconBadge.style.background = '#FEF2F2';
        iconBadge.style.color = '#B91C1C';
      }
      if (issueCallout) issueCallout.style.display = 'block';
      if (submitBtn) submitBtn.textContent = 'Approve & Record';

      // Section display: Hide Upload Box, Show Student Evidence View
      if (uploadSection) uploadSection.style.display = 'none';
      if (viewEvidenceSection) viewEvidenceSection.style.display = 'block';

      // Sync student context
      syncModalStudentContext(s);

      // Pre-fill student's submitted issue details
      if (inputTitle) inputTitle.value = 'House Prefect — Teal House';
      setRecDatepicker('2025-01-15');
      if (inputIssuer) inputIssuer.value = 'Teal House Master';
      if (inputDesc) inputDesc.value = 'Appointed House Prefect for Teal House, responsible for coordinating inter-house sports and events for the year.';

      if (typeof window.setDropdownValue === 'function') {
        window.setDropdownValue('j-rec-type-dropdown', 'Leadership');
      } else {
        if (inputTypeHidden) inputTypeHidden.value = 'Leadership';
        const ddTrigger = document.querySelector('#j-rec-type-dropdown .c-dropdown__trigger');
        if (ddTrigger) {
          const ddVal = ddTrigger.querySelector('.c-dropdown__value') || ddTrigger;
          ddVal.textContent = 'Leadership';
        }
      }

      if (typeof window.openModal === 'function') {
        window.openModal('#j-record-modal');
      } else if (modalEl) {
        modalEl.style.display = 'flex';
        modalEl.classList.add('c-is-open');
      }
    });

    // Evidence link preview
    if (evidenceBtn && previewModal) {
      evidenceBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (typeof window.openModal === 'function') {
          window.openModal('#j-evidence-preview-modal');
        } else {
          previewModal.style.display = 'flex';
          previewModal.classList.add('c-is-open');
        }
      });
    }

    /* -----------------------------------------------------------------------
       5. SUPPORTING PROOF UPLOAD INTERACTION
       ----------------------------------------------------------------------- */
    if (uploadBox && fileInput) {
      uploadBox.addEventListener('click', function () {
        fileInput.click();
      });

      fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
          const file = this.files[0];
          const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
          if (filenameLabel) {
            filenameLabel.textContent = file.name + ' (' + sizeMb + ' MB)';
            filenameLabel.style.display = 'inline-flex';
          }
        }
      });

      uploadBox.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadBox.classList.add('c-is-dragover');
      });

      uploadBox.addEventListener('dragleave', function () {
        uploadBox.classList.remove('c-is-dragover');
      });

      uploadBox.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadBox.classList.remove('c-is-dragover');
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
          fileInput.files = e.dataTransfer.files;
          const file = e.dataTransfer.files[0];
          const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
          if (filenameLabel) {
            filenameLabel.textContent = file.name + ' (' + sizeMb + ' MB)';
            filenameLabel.style.display = 'inline-flex';
          }
        }
      });
    }

    /* -----------------------------------------------------------------------
       6. FORM SUBMISSION
       ----------------------------------------------------------------------- */
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitBtn) {
          const originalText = submitBtn.textContent;
          submitBtn.textContent = 'Saving...';
          submitBtn.disabled = true;

          setTimeout(function () {
            if (typeof window.closeModal === 'function') {
              window.closeModal('#j-record-modal');
            } else if (modalEl) {
              modalEl.classList.remove('c-is-open');
              modalEl.style.display = 'none';
            }
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            if (form) form.reset();
            if (filenameLabel) filenameLabel.style.display = 'none';
            if (typeof window.showFeedbackBanner === 'function') {
              window.showFeedbackBanner('Achievement recorded successfully!', 'success');
            }
          }, 350);
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTeacherAchievements);
  } else {
    initTeacherAchievements();
  }
})();
