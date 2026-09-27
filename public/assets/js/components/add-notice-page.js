/**
 * =========================================================================
 * L'ÉCOLE — ADD / EDIT NOTICE PAGE COMPONENT CONTROLLER
 * =========================================================================
 * Handles:
 *  - View switching between board and form (Post New & Edit Notice)
 *  - Audience tag chips & dropdown selection
 *  - Category selection
 *  - File attachment preview
 *  - Pin checkbox toggling
 *  - Pre-filling form with card data when clicking Edit
 *  - Updating existing card or creating new card in DOM on form submission
 * =========================================================================
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const boardView = document.getElementById('j-view-board');
    const postView = document.getElementById('j-view-post-notice');
    const form = document.getElementById('j-post-notice-form');

    function applyDropdownValue(dropdownId, targetVal) {
      if (typeof window.setDropdownValue === 'function') {
        window.setDropdownValue(dropdownId, targetVal);
      }
    }

    // -----------------------------------------------------------------------
    // Audience Multi-Select Dropdown Management (uses _dropdown.php & dropdown.js)
    // -----------------------------------------------------------------------
    const audienceDropdown = document.getElementById('j-select-post-audience');
    let audiences = ['All'];

    function syncAudiencesFromDOM() {
      const root = document.getElementById('j-select-post-audience');
      if (!root) return;
      const selectedOpts = root.querySelectorAll('.c-select__option.c-is-selected, .c-dropdown__option.c-is-selected');
      audiences = Array.from(selectedOpts).map((opt) => opt.getAttribute('data-value') || opt.textContent.trim());
      if (audiences.length === 0) audiences = ['All'];
    }

    if (audienceDropdown) {
      audienceDropdown.addEventListener('dropdown:change', function (e) {
        if (e.detail && e.detail.values) {
          audiences = e.detail.values;
        } else {
          syncAudiencesFromDOM();
        }
      });
    }

    function setAudienceValues(targetAudiences) {
      const vals = Array.isArray(targetAudiences) ? targetAudiences : [targetAudiences];
      audiences = vals.length > 0 ? vals : ['All'];
      if (window.setDropdownMultiValues) {
        window.setDropdownMultiValues('j-select-post-audience', audiences);
      }
    }

    // -----------------------------------------------------------------------
    // Dynamic Scope Field Visibility (Extracurricular vs Academic)
    // -----------------------------------------------------------------------
    function syncScopeVisibility(catVal) {
      const extraScope = document.querySelector('.j-scope-extracurricular');
      const acadScope = document.querySelector('.j-scope-academic');
      if (extraScope) extraScope.style.display = (catVal === 'Extracurricular') ? 'flex' : 'none';
      if (acadScope) acadScope.style.display = (catVal === 'Academic') ? 'flex' : 'none';
    }

    const catDropdown = document.getElementById('j-select-post-category');
    if (catDropdown) {
      catDropdown.addEventListener('dropdown:change', function (e) {
        const val = e.detail ? (e.detail.value || e.detail.values?.[0]) : null;
        if (val) syncScopeVisibility(val);
      });
      catDropdown.addEventListener('click', function () {
        setTimeout(() => {
          const selected = catDropdown.querySelector('.c-select__option.c-is-selected, .c-dropdown__option.c-is-selected');
          const val = selected ? (selected.getAttribute('data-value') || selected.textContent.trim()) : 'Academic';
          syncScopeVisibility(val);
        }, 50);
      });
    }

    // -----------------------------------------------------------------------
    // Pin Checkbox Management (Visual state & programmatic sync)
    // -----------------------------------------------------------------------
    const pinCheckbox = document.querySelector('.j-checkbox');
    const hiddenPin = document.getElementById('j-post-pinned');

    function setPinState(isPinned) {
      if (!pinCheckbox) return;
      pinCheckbox.classList.toggle('c-is-checked', !!isPinned);
      pinCheckbox.setAttribute('aria-checked', isPinned ? 'true' : 'false');
      if (hiddenPin) hiddenPin.value = isPinned ? '1' : '0';
    }

    if (pinCheckbox) {
      setPinState(false);
    }

    // -----------------------------------------------------------------------
    // File Attachment Reference (Handled centrally by form-card.js)
    // -----------------------------------------------------------------------
    const fileInput = document.getElementById('j-post-attachment-input');
    const fileNameEl = document.querySelector('.j-attachment-name');

    // -----------------------------------------------------------------------
    // Helper: Reset Form back to "Post New Notice" mode
    // -----------------------------------------------------------------------
    function resetPostForm() {
      if (!form) return;
      form.removeAttribute('data-editing-id');

      const titleHeader = form.querySelector('.c-notice-form__header-title');
      if (titleHeader) titleHeader.textContent = 'Post New Notice';

      const subtitleHeader = form.querySelector('.c-notice-form__header-subtitle');
      if (subtitleHeader) subtitleHeader.textContent = 'Create and publish an announcement to the central school portal.';

      const submitBtnSpan = form.querySelector('button[type="submit"] span');
      if (submitBtnSpan) submitBtnSpan.textContent = 'Publish Notice';

      const titleInput = document.getElementById('j-post-title');
      if (titleInput) titleInput.value = '';

      const bodyInput = document.getElementById('j-post-body');
      if (bodyInput) bodyInput.value = '';

      // Reset Category Dropdown to first option or Academic
      const catSelect = document.getElementById('j-select-post-category');
      if (catSelect) {
        const firstOption = catSelect.querySelector('.c-select__option');
        const firstVal = firstOption ? (firstOption.getAttribute('data-value') || firstOption.textContent.trim()) : 'Academic';
        applyDropdownValue('j-select-post-category', firstVal);
        syncScopeVisibility(firstVal);
      } else {
        syncScopeVisibility('Academic');
      }

      // Reset Target Club and Target Class dropdowns
      applyDropdownValue('j-select-post-club', 'All');
      applyDropdownValue('j-select-post-class', 'All');

      // Reset Audiences
      setAudienceValues(['All']);

      // Reset Pin
      setPinState(false);

      // Reset Attachment
      if (fileInput) fileInput.value = '';
      if (fileNameEl) fileNameEl.textContent = 'Click to upload a file';

      // Reset Time Slot Dates (Reusable DatePickers)
      setDatePickerValue('j-post-publish-datepicker', '');
      setDatePickerValue('j-post-expires-datepicker', '');
    }

    function setDatePickerValue(dpId, dateStr) {
      const rootEl = document.getElementById(dpId);
      if (!rootEl) return;
      const input = rootEl.querySelector('.j-dp-input');
      const trigger = rootEl.querySelector('.j-dp-trigger');
      const label = trigger ? trigger.querySelector('.j-dp-label') : null;
      const placeholder = trigger ? (trigger.getAttribute('data-placeholder') || 'Select date') : 'Select date';
      const val = dateStr ? dateStr.trim().split(' ')[0] : '';
      if (input) input.value = val;
      if (label) {
        if (val && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
          const d = new Date(val + 'T00:00:00');
          if (!isNaN(d.getTime())) {
            const day = String(d.getDate()).padStart(2, '0');
            const month = d.toLocaleString('en-GB', { month: 'short' });
            const year = d.getFullYear();
            label.textContent = `${day} ${month} ${year}`;
            label.classList.remove('c-dp-placeholder');
          } else {
            label.textContent = placeholder;
            label.classList.add('c-dp-placeholder');
          }
        } else {
          label.textContent = placeholder;
          label.classList.add('c-dp-placeholder');
        }
      }
    }

    // -----------------------------------------------------------------------
    // View Switching & Action Handlers
    // -----------------------------------------------------------------------
    document.addEventListener('click', function (e) {
      // 1. Click "Post Notice" button (top bar) -> Open clean form
      if (e.target.closest('.j-go-post-notice')) {
        e.preventDefault();
        resetPostForm();
        if (boardView) boardView.style.display = 'none';
        if (postView) postView.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      // 2. Click "Back to Notice Board" or "Cancel" -> Return to board
      if (e.target.closest('.j-go-notice-board, .j-cancel-post-notice')) {
        e.preventDefault();
        resetPostForm();
        if (postView) postView.style.display = 'none';
        if (boardView) boardView.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      // 3. Click "Edit" on a Notice Card -> Pre-fill form and open Edit view
      const editBtn = e.target.closest('.j-notice-edit');
      if (editBtn) {
        e.preventDefault();
        e.stopPropagation();

        const card = editBtn.closest('.c-notice-card');
        if (!card || !form) return;

        const noticeId = card.getAttribute('data-notice-id') || '';
        const title = card.querySelector('.c-notice-card__title')?.textContent?.trim() || '';
        const body = card.querySelector('.c-notice-card__body')?.textContent?.trim() || '';
        const category = card.getAttribute('data-category') || card.querySelector('.c-tag--category')?.textContent?.trim() || 'Academic';
        const isPinned = card.getAttribute('data-pinned') === 'true';

        // Extract audience tags
        const audienceTags = Array.from(card.querySelectorAll('.c-tag--audience'))
          .map((tag) => tag.textContent.trim())
          .filter(Boolean);

        // Put form in Edit mode
        form.setAttribute('data-editing-id', noticeId);

        const titleHeader = form.querySelector('.c-notice-form__header-title');
        if (titleHeader) titleHeader.textContent = 'Edit Notice';

        const subtitleHeader = form.querySelector('.c-notice-form__header-subtitle');
        if (subtitleHeader) subtitleHeader.textContent = 'Update announcement details and modify target audience.';

        const submitBtnSpan = form.querySelector('button[type="submit"] span');
        if (submitBtnSpan) submitBtnSpan.textContent = 'Save Changes';

        // Pre-fill Title & Body
        const titleInput = document.getElementById('j-post-title');
        if (titleInput) titleInput.value = title;

        const bodyInput = document.getElementById('j-post-body');
        if (bodyInput) bodyInput.value = body;

        // Pre-fill Category Dropdown
        applyDropdownValue('j-select-post-category', category);

        // Pre-fill Target Club & Class
        const targetClub = card.getAttribute('data-target-club') || 'All';
        const targetClass = card.getAttribute('data-target-class') || 'All';
        applyDropdownValue('j-select-post-club', targetClub || 'All');
        applyDropdownValue('j-select-post-class', targetClass || 'All');
        syncScopeVisibility(category);

        // Pre-fill Audience Chips
        setAudienceValues(audienceTags.length > 0 ? audienceTags : ['All']);

        // Pre-fill Pin Checkbox
        setPinState(isPinned);

        // Pre-fill Time Slot Dates (Reusable DatePickers)
        const pubDate = (card.getAttribute('data-publish-at') || '').split(' ')[0];
        const expDate = (card.getAttribute('data-expires-at') || '').split(' ')[0];
        setDatePickerValue('j-post-publish-datepicker', pubDate);
        setDatePickerValue('j-post-expires-datepicker', expDate);

        // Switch to form view
        if (boardView) boardView.style.display = 'none';
        if (postView) postView.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
    });

    // -----------------------------------------------------------------------
    // Form Submit Handler (Save Edits or Publish New Notice via AJAX)
    // -----------------------------------------------------------------------
    if (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const titleInput = document.getElementById('j-post-title');
        const bodyInput = document.getElementById('j-post-body');
        const titleVal = titleInput ? titleInput.value.trim() : '';
        const bodyVal = bodyInput ? bodyInput.value.trim() : '';

        // Validate required fields
        if (!titleVal) {
          if (titleInput) {
            titleInput.focus();
            titleInput.classList.add('c-is-invalid');
            setTimeout(() => titleInput.classList.remove('c-is-invalid'), 2000);
          }
          return;
        }

        if (!bodyVal) {
          if (bodyInput) {
            bodyInput.focus();
            bodyInput.classList.add('c-is-invalid');
            setTimeout(() => bodyInput.classList.remove('c-is-invalid'), 2000);
          }
          return;
        }

        // Extract Category
        const catInput = form.querySelector('input[name="category"]');
        const catVal = catInput && catInput.value
          ? catInput.value
          : (form.querySelector('#j-select-post-category .j-select-value')?.textContent?.trim() || 'Academic');

        // Extract Audiences
        const audInputs = form.querySelectorAll('input[name="audience[]"]');
        if (audInputs.length > 0) {
          audiences = Array.from(audInputs).map((i) => i.value).filter(Boolean);
        }

        // Extract Pin State
        const isPinned = hiddenPin ? hiddenPin.value === '1' : false;
        const editingId = form.getAttribute('data-editing-id');

        const submitBtn = form.querySelector('button[type="submit"]');
        const origSubmitText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span>Saving...</span>';
        }

        const seg = window.location.pathname.split('/')[1] || 'admin';
        const basePath = '/' + (['admin', 'management', 'teacher'].includes(seg) ? seg : 'admin');
        const targetUrl = editingId ? `${basePath}/updateNotice` : `${basePath}/createNotice`;

        const clubInput = form.querySelector('input[name="target_club_id"]');
        const classInput = form.querySelector('input[name="target_class_section"]');
        const targetClubVal = clubInput ? clubInput.value : 'All';
        const targetClassVal = classInput ? classInput.value : 'All';

        const pubInput = form.querySelector('input[name="publish_at"]');
        const expInput = form.querySelector('input[name="expires_at"]');

        const formData = new FormData(form);
        if (editingId) {
          formData.append('id', editingId);
        }
        formData.delete('audience[]');
        audiences.forEach(a => formData.append('audience[]', a));
        formData.set('category', catVal);
        formData.set('target_club_id', targetClubVal);
        formData.set('target_class_section', targetClassVal);
        if (pubInput && pubInput.value) formData.set('publish_at', pubInput.value);
        if (expInput && expInput.value) formData.set('expires_at', expInput.value);
        formData.set('pinned', isPinned ? '1' : '0');

        // Ensure CSRF token
        const csrf = document.querySelector('input[name="_csrf_token"]')?.value ||
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                     window.LECOLE_CSRF_TOKEN || '';
        if (csrf && !formData.has('_csrf_token')) {
          formData.append('_csrf_token', csrf);
        }

        try {
          const headers = {
            'X-Requested-With': 'XMLHttpRequest'
          };
          if (csrf) {
            headers['X-CSRF-Token'] = csrf;
          }

          const resp = await fetch(targetUrl, {
            method: 'POST',
            body: formData,
            headers
          });

          const res = await resp.json();

          if (!resp.ok || !res.success) {
            const errorMsg = res.error || 'Failed to save announcement.';
            if (window.showFeedbackBanner) {
              window.showFeedbackBanner(errorMsg, 'error');
            } else {
              alert(errorMsg);
            }
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = origSubmitText;
            }
            return;
          }

          const savedNotice = res.notice || {};
          const noticeId = savedNotice.id || editingId;
          const displayAuthor = savedNotice.author || 'Admin Office';
          const displayDate = savedNotice.date || 'TODAY';
          const attName = savedNotice.attachment_name || null;
          const attPath = savedNotice.attachment_path || null;

          let scopeBadgeHtml = '<span class="c-tag c-tag--scope" data-scope="school">School-Wide</span>';
          if (catVal === 'Extracurricular' && targetClubVal && targetClubVal !== 'All') {
            const clubLabel = targetClubVal.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
            scopeBadgeHtml = `<span class="c-tag c-tag--scope" data-scope="club">${clubLabel}</span>`;
          } else if (catVal === 'Academic' && targetClassVal && targetClassVal !== 'All') {
            scopeBadgeHtml = `<span class="c-tag c-tag--scope" data-scope="class">${targetClassVal}</span>`;
          }

          if (editingId) {
            // =================================================================
            // A. UPDATE EXISTING CARD IN DOM
            // =================================================================
            const card = document.querySelector(`.c-notice-card[data-notice-id="${editingId}"]`);
            if (card) {
              // Update Title & Body
              const titleEl = card.querySelector('.c-notice-card__title');
              if (titleEl) titleEl.textContent = titleVal;

              const bodyEl = card.querySelector('.c-notice-card__body');
              if (bodyEl) bodyEl.textContent = bodyVal;

              // Update Category & Audience attributes
              card.setAttribute('data-category', catVal);
              card.setAttribute('data-audience', audiences.map((a) => a.toLowerCase()).join(','));
              card.setAttribute('data-target-club', targetClubVal !== 'All' ? targetClubVal : '');
              card.setAttribute('data-target-class', targetClassVal !== 'All' ? targetClassVal : '');
              card.setAttribute('data-publish-at', pubInput?.value || '');
              card.setAttribute('data-expires-at', expInput?.value || '');

              let expiryPill = '';
              if (expInput && expInput.value) {
                const expD = new Date(expInput.value);
                const expLabel = !isNaN(expD.getTime()) ? expD.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) : expInput.value;
                expiryPill = `<span class="c-tag c-tag--expiry" style="background: rgba(175, 80, 49, 0.12); color: #AF5031;">Expires ${expLabel}</span>`;
              }

              // Update Tags Row
              const tagsWrap = card.querySelector('.c-notice-card__tags');
              if (tagsWrap) {
                tagsWrap.innerHTML = `
                  <span class="c-tag c-tag--category">${catVal}</span>
                  ${scopeBadgeHtml}
                  ${expiryPill}
                  ${audiences.map((aud) => `<span class="c-tag c-tag--audience">${aud}</span>`).join('')}
                `;
              }

              // Update Attachment Pill
              let attWrap = card.querySelector('.c-notice-card__attachment');
              if (attPath) {
                if (!attWrap) {
                  attWrap = document.createElement('div');
                  attWrap.className = 'c-notice-card__attachment';
                  attWrap.style.margin = '0.5rem 0 0.75rem';
                  const bodyTarget = card.querySelector('.c-notice-card__body');
                  if (bodyTarget && bodyTarget.nextSibling) {
                    card.insertBefore(attWrap, bodyTarget.nextSibling);
                  } else {
                    card.appendChild(attWrap);
                  }
                }
                attWrap.innerHTML = `
                  <a href="${attPath}" download="${attName || 'attachment'}" class="c-tag c-tag--attachment" target="_blank" style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; text-decoration: none; padding: 0.3rem 0.75rem; border-radius: 9999px; background: rgba(127, 199, 204, 0.2); color: #0F414A; font-weight: 600; border: 1px solid rgba(127, 199, 204, 0.4);">
                    <svg class="c-icon" width="13" height="13" style="color: #207C82;"><use href="#icon-paperclip"/></svg>
                    <span>${attName || 'Download Attachment'}</span>
                  </a>
                `;
              } else if (attWrap) {
                attWrap.remove();
              }

              // Update Pin State
              card.setAttribute('data-pinned', isPinned ? 'true' : 'false');
              let pinBadge = card.querySelector('.c-notice-card__pin');
              const pinBtn = card.querySelector('.j-notice-pin');

              if (isPinned) {
                if (!pinBadge) {
                  pinBadge = document.createElement('span');
                  pinBadge.className = 'c-notice-card__pin';
                  pinBadge.setAttribute('aria-label', 'Pinned notice');
                  pinBadge.innerHTML = `
                    <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <use href="#icon-pinFilled"/>
                    </svg>`;
                  card.prepend(pinBadge);
                }
                if (pinBtn) {
                  pinBtn.setAttribute('aria-label', 'Unpin notice');
                  pinBtn.innerHTML = `
                    <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <use href="#icon-pinFilled"/>
                    </svg>`;
                }

                // Move to top of grid
                const grid = card.closest('.c-notice-grid');
                if (grid && grid.firstElementChild !== card) {
                  grid.prepend(card);
                }
              } else {
                if (pinBadge) pinBadge.remove();
                if (pinBtn) {
                  pinBtn.setAttribute('aria-label', 'Pin notice');
                  pinBtn.innerHTML = `
                    <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <use href="#icon-pin"/>
                    </svg>`;
                }
              }

              // Visual feedback highlight
              card.style.transition = 'box-shadow 0.3s ease';
              card.style.boxShadow = '0 0 0 3px rgba(127, 199, 204, 0.6)';
              setTimeout(() => {
                card.style.boxShadow = '';
              }, 1200);
            }
          } else {
            // =================================================================
            // B. CREATE NEW NOTICE CARD IN DOM
            // =================================================================
            const grid = document.getElementById('j-notice-grid');
            if (grid) {
              const initials = displayAuthor.split(' ').map((s) => s[0]).join('').slice(0, 2).toUpperCase();

              const cardEl = document.createElement('article');
              cardEl.className = 'c-notice-card';
              cardEl.setAttribute('data-category', catVal);
              cardEl.setAttribute('data-audience', audiences.map((a) => a.toLowerCase()).join(','));
              cardEl.setAttribute('data-target-club', targetClubVal !== 'All' ? targetClubVal : '');
              cardEl.setAttribute('data-target-class', targetClassVal !== 'All' ? targetClassVal : '');
              const curRole = window.location.pathname.split('/')[1] || 'admin';
              cardEl.setAttribute('data-author-role', curRole);
              cardEl.setAttribute('data-notice-id', noticeId);
              cardEl.setAttribute('data-pinned', isPinned ? 'true' : 'false');
              cardEl.setAttribute('data-publish-at', pubInput?.value || '');
              cardEl.setAttribute('data-expires-at', expInput?.value || '');
              cardEl.tabIndex = 0;

              let expiryPillNew = '';
              if (expInput && expInput.value) {
                const expD = new Date(expInput.value);
                const expLabel = !isNaN(expD.getTime()) ? expD.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) : expInput.value;
                expiryPillNew = `<span class="c-tag c-tag--expiry" style="background: rgba(175, 80, 49, 0.12); color: #AF5031;">Expires ${expLabel}</span>`;
              }

              cardEl.innerHTML = `
                ${isPinned ? `
                  <span class="c-notice-card__pin" aria-label="Pinned notice">
                    <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <use href="#icon-pinFilled"/>
                    </svg>
                  </span>` : ''}
                <div class="c-notice-card__tags">
                  <span class="c-tag c-tag--category">${catVal}</span>
                  ${scopeBadgeHtml}
                  ${expiryPillNew}
                  ${audiences.map((aud) => `<span class="c-tag c-tag--audience">${aud}</span>`).join('')}
                </div>
                <h2 class="c-notice-card__title">${titleVal}</h2>
                <p class="c-notice-card__body">${bodyVal}</p>
                ${attPath ? `
                  <div class="c-notice-card__attachment" style="margin: 0.5rem 0 0.75rem;">
                    <a href="${attPath}" download="${attName || 'attachment'}" class="c-tag c-tag--attachment" target="_blank" style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; text-decoration: none; padding: 0.3rem 0.75rem; border-radius: 9999px; background: rgba(127, 199, 204, 0.2); color: #0F414A; font-weight: 600; border: 1px solid rgba(127, 199, 204, 0.4);">
                      <svg class="c-icon" width="13" height="13" style="color: #207C82;"><use href="#icon-paperclip"/></svg>
                      <span>${attName || 'Download Attachment'}</span>
                    </a>
                  </div>` : ''}
                <footer class="c-notice-card__footer">
                  <div class="c-notice-card__author">
                    <span class="c-avatar">${initials}</span>
                    <div>
                      <p class="c-notice-card__author-name">${displayAuthor}</p>
                      <p class="c-notice-card__date">${displayDate}</p>
                    </div>
                  </div>
                  <div class="c-notice-card__actions">
                    <button type="button" class="c-icon-btn j-notice-pin" data-notice-id="${noticeId}" aria-label="${isPinned ? 'Unpin notice' : 'Pin notice'}">
                      <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="${isPinned ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="${isPinned ? '#icon-pinFilled' : '#icon-pin'}"/>
                      </svg>
                    </button>
                    <button type="button" class="c-icon-btn j-notice-edit" data-notice-id="${noticeId}" aria-label="Edit notice">
                      <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#icon-edit"/>
                      </svg>
                    </button>
                    <button type="button" class="c-icon-btn c-icon-btn--danger j-notice-delete" data-notice-id="${noticeId}" aria-label="Delete notice">
                      <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#icon-trash"/>
                      </svg>
                    </button>
                  </div>
                </footer>
              `;

              if (isPinned || grid.children.length === 0) {
                grid.prepend(cardEl);
              } else {
                const firstUnpinned = grid.querySelector('.c-notice-card[data-pinned="false"]');
                if (firstUnpinned) {
                  grid.insertBefore(cardEl, firstUnpinned);
                } else {
                  grid.appendChild(cardEl);
                }
              }

              // Hide empty state if present
              const emptyState = document.getElementById('j-empty-state');
              if (emptyState) emptyState.hidden = true;
            }
          }

          if (window.showFeedbackBanner) {
            window.showFeedbackBanner(res.message || 'Announcement saved successfully.', 'success');
          }

          // Return to Notice Board view
          resetPostForm();
          if (postView) postView.style.display = 'none';
          if (boardView) boardView.style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });

        } catch (err) {
          console.error('[Notice Submit Error]', err);
          if (window.showFeedbackBanner) {
            window.showFeedbackBanner('Could not communicate with the server. Please try again.', 'error');
          }
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origSubmitText;
          }
        }
      });
    }
  });
})();
