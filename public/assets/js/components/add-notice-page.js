/**
 * =========================================================================
 * L'ÉCOLE — ADD / EDIT NOTICE PAGE COMPONENT CONTROLLER
 * =========================================================================
 * Handles:
 *  - View switching between board and form (Post New & Edit Notice)
 *  - Audience tag chips & dropdown selection
 *  - Category selection, file attachment preview, pin checkbox toggling
 *  - Pre-filling form with card data when clicking Edit
 *  - Sending fetch() POST requests to /admin/save-notice (create & update)
 *  - Updating the DOM card or inserting a new card on success
 * =========================================================================
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const cfg      = window.LECOLE_NOTICE || {};
    const CSRF     = cfg.csrfToken    || '';
    const SAVE_URL = cfg.saveUrl      || '/admin/save-notice';

    const boardView = document.getElementById('j-view-board');
    const postView  = document.getElementById('j-view-post-notice');
    const form      = document.getElementById('j-post-notice-form');

    // -----------------------------------------------------------------------
    // Shared helper: set a dropdown value (uses existing dropdown.js API)
    // -----------------------------------------------------------------------
    function applyDropdownValue(dropdownId, targetVal) {
      if (typeof window.setDropdownValue === 'function') {
        window.setDropdownValue(dropdownId, targetVal);
      }
    }

    // -----------------------------------------------------------------------
    // Audience Multi-Select Dropdown Management
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
    // Pin Checkbox Management
    // -----------------------------------------------------------------------
    const pinCheckbox = document.querySelector('.j-checkbox');
    const hiddenPin   = document.getElementById('j-post-pinned');

    function setPinState(isPinned) {
      if (!pinCheckbox) return;
      pinCheckbox.classList.toggle('c-is-checked', !!isPinned);
      pinCheckbox.setAttribute('aria-checked', isPinned ? 'true' : 'false');
      if (hiddenPin) hiddenPin.value = isPinned ? '1' : '0';
    }

    if (pinCheckbox) setPinState(false);

    // -----------------------------------------------------------------------
    // File Attachment Preview
    // -----------------------------------------------------------------------
    const fileInput  = document.getElementById('j-post-attachment-input');
    const fileNameEl = document.querySelector('.j-attachment-name');

    // -----------------------------------------------------------------------
    // Status message helper
    // -----------------------------------------------------------------------
    function showFormMsg(text, isError) {
      const msgEl = form && form.querySelector('.j-post-form-message');
      if (!msgEl) return;
      msgEl.textContent = text;
      msgEl.style.display = 'block';
      msgEl.style.color = isError ? '#c0392b' : '#1a7a4a';
      if (!isError) {
        setTimeout(() => { msgEl.style.display = 'none'; }, 3000);
      }
    }

    // -----------------------------------------------------------------------
    // Submit button loading state
    // -----------------------------------------------------------------------
    function setSubmitting(isLoading) {
      if (!form) return;
      const btn  = form.querySelector('button[type="submit"]');
      const span = btn && btn.querySelector('span');
      if (!btn) return;
      btn.disabled = isLoading;
      if (span) span.textContent = isLoading ? 'Saving…' : (form.getAttribute('data-editing-id') ? 'Save Changes' : 'Publish Notice');
    }

    // -----------------------------------------------------------------------
    // Reset form back to "Post New Notice" state
    // -----------------------------------------------------------------------
    function resetPostForm() {
      if (!form) return;
      form.removeAttribute('data-editing-id');

      const titleHeader    = form.querySelector('.c-notice-form__header-title');
      const subtitleHeader = form.querySelector('.c-notice-form__header-subtitle');
      const submitBtnSpan  = form.querySelector('button[type="submit"] span');

      if (titleHeader)    titleHeader.textContent    = 'Post New Notice';
      if (subtitleHeader) subtitleHeader.textContent = 'Create and publish an announcement to the central school portal.';
      if (submitBtnSpan)  submitBtnSpan.textContent  = 'Publish Notice';

      const titleInput = document.getElementById('j-post-title');
      if (titleInput) titleInput.value = '';

      const bodyInput = document.getElementById('j-post-body');
      if (bodyInput) bodyInput.value = '';

      const catSelect = document.getElementById('j-select-post-category');
      if (catSelect) {
        const firstOption = catSelect.querySelector('.c-select__option');
        const firstVal    = firstOption ? (firstOption.getAttribute('data-value') || firstOption.textContent.trim()) : 'Academic';
        applyDropdownValue('j-select-post-category', firstVal);
      }

      setAudienceValues(['All']);
      setPinState(false);

      if (fileInput)  fileInput.value = '';
      if (fileNameEl) fileNameEl.textContent = 'Click to upload a file';

      showFormMsg('', false);
    }

    // -----------------------------------------------------------------------
    // Build a card DOM element from a notice data object (returned from API)
    // -----------------------------------------------------------------------
    function buildCardElement(notice) {
      const audArray = Array.isArray(notice.audience)
        ? notice.audience
        : (notice.audience || 'All').split(',').map(s => s.trim());

      const isPinned   = !!(notice.pinned || notice.is_pinned);
      const authorName = notice.author || 'Admin Office';
      const initials   = authorName.split(' ').map(s => s[0]).join('').slice(0, 2).toUpperCase();

      const cardEl = document.createElement('article');
      cardEl.className = 'c-notice-card';
      cardEl.setAttribute('data-category',  notice.category || 'General');
      cardEl.setAttribute('data-audience',  audArray.map(a => a.toLowerCase()).join(','));
      cardEl.setAttribute('data-notice-id', String(notice.id));
      cardEl.setAttribute('data-pinned',    isPinned ? 'true' : 'false');
      cardEl.tabIndex = 0;

      cardEl.innerHTML = `
        ${isPinned ? `<span class="c-notice-card__pin" aria-label="Pinned notice">
          <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-pinFilled"/></svg>
        </span>` : ''}
        <div class="c-notice-card__tags">
          <span class="c-tag c-tag--category">${notice.category || 'General'}</span>
          ${audArray.map(aud => `<span class="c-tag c-tag--audience">${aud}</span>`).join('')}
        </div>
        <h2 class="c-notice-card__title">${notice.title}</h2>
        <p class="c-notice-card__body">${notice.body}</p>
        <footer class="c-notice-card__footer">
          <div class="c-notice-card__author">
            <span class="c-avatar">${initials}</span>
            <div>
              <p class="c-notice-card__author-name">${authorName}</p>
              <p class="c-notice-card__date">${notice.date || 'TODAY'}</p>
            </div>
          </div>
          <div class="c-notice-card__actions">
            <button type="button" class="c-icon-btn j-notice-pin" data-notice-id="${notice.id}" aria-label="${isPinned ? 'Unpin notice' : 'Pin notice'}">
              <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="${isPinned ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="${isPinned ? '#icon-pinFilled' : '#icon-pin'}"/></svg>
            </button>
            <button type="button" class="c-icon-btn j-notice-edit" data-notice-id="${notice.id}" aria-label="Edit notice">
              <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
            </button>
            <button type="button" class="c-icon-btn c-icon-btn--danger j-notice-delete" data-notice-id="${notice.id}" aria-label="Delete notice">
              <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-trash"/></svg>
            </button>
          </div>
        </footer>
      `;

      return cardEl;
    }

    // -----------------------------------------------------------------------
    // View Switching & Click Action Handlers
    // -----------------------------------------------------------------------
    document.addEventListener('click', function (e) {
      // 1. "Post Notice" → open clean form
      if (e.target.closest('.j-go-post-notice')) {
        e.preventDefault();
        resetPostForm();
        if (boardView) boardView.style.display = 'none';
        if (postView)  postView.style.display  = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      // 2. "Back" / "Cancel" → return to board
      if (e.target.closest('.j-go-notice-board, .j-cancel-post-notice')) {
        e.preventDefault();
        resetPostForm();
        if (postView)  postView.style.display  = 'none';
        if (boardView) boardView.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      // 3. "Edit" on a Notice Card → pre-fill form
      const editBtn = e.target.closest('.j-notice-edit');
      if (editBtn) {
        e.preventDefault();
        e.stopPropagation();

        const card = editBtn.closest('.c-notice-card');
        if (!card || !form) return;

        const noticeId = card.getAttribute('data-notice-id') || '';
        const title    = card.querySelector('.c-notice-card__title')?.textContent?.trim() || '';
        const body     = card.querySelector('.c-notice-card__body')?.textContent?.trim()  || '';
        const category = card.getAttribute('data-category') || card.querySelector('.c-tag--category')?.textContent?.trim() || 'Academic';
        const isPinned = card.getAttribute('data-pinned') === 'true';

        const audienceTags = Array.from(card.querySelectorAll('.c-tag--audience'))
          .map(tag => tag.textContent.trim())
          .filter(Boolean);

        form.setAttribute('data-editing-id', noticeId);

        const titleHeader    = form.querySelector('.c-notice-form__header-title');
        const subtitleHeader = form.querySelector('.c-notice-form__header-subtitle');
        const submitBtnSpan  = form.querySelector('button[type="submit"] span');

        if (titleHeader)    titleHeader.textContent    = 'Edit Notice';
        if (subtitleHeader) subtitleHeader.textContent = 'Update announcement details and modify target audience.';
        if (submitBtnSpan)  submitBtnSpan.textContent  = 'Save Changes';

        const titleInput = document.getElementById('j-post-title');
        if (titleInput) titleInput.value = title;

        const bodyInput = document.getElementById('j-post-body');
        if (bodyInput) bodyInput.value = body;

        applyDropdownValue('j-select-post-category', category);
        setAudienceValues(audienceTags.length > 0 ? audienceTags : ['All']);
        setPinState(isPinned);

        if (boardView) boardView.style.display = 'none';
        if (postView)  postView.style.display  = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
    });

    // -----------------------------------------------------------------------
    // Form Submit → fetch() POST to /admin/save-notice
    // -----------------------------------------------------------------------
    if (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const titleInput = document.getElementById('j-post-title');
        const bodyInput  = document.getElementById('j-post-body');
        const titleVal   = titleInput ? titleInput.value.trim() : '';
        const bodyVal    = bodyInput  ? bodyInput.value.trim()  : '';

        // Client-side validation
        if (!titleVal) {
          if (titleInput) { titleInput.focus(); titleInput.classList.add('c-is-invalid'); setTimeout(() => titleInput.classList.remove('c-is-invalid'), 2000); }
          return;
        }
        if (!bodyVal) {
          if (bodyInput) { bodyInput.focus(); bodyInput.classList.add('c-is-invalid'); setTimeout(() => bodyInput.classList.remove('c-is-invalid'), 2000); }
          return;
        }

        // Extract Category
        const catInput = form.querySelector('input[name="category"]');
        const catVal   = catInput && catInput.value
          ? catInput.value
          : (form.querySelector('#j-select-post-category .j-select-value')?.textContent?.trim() || 'Academic');

        // Extract Audiences from hidden inputs injected by dropdown.js
        const audInputs = form.querySelectorAll('input[name="audience[]"]');
        if (audInputs.length > 0) {
          audiences = Array.from(audInputs).map(i => i.value).filter(Boolean);
        }

        const isPinned  = hiddenPin ? hiddenPin.value === '1' : false;
        const editingId = form.getAttribute('data-editing-id') || '';

        // Build FormData (supports file upload)
        const fd = new FormData();
        fd.append('_csrf_token', CSRF);
        if (editingId) fd.append('id', editingId);
        fd.append('title',    titleVal);
        fd.append('body',     bodyVal);
        fd.append('category', catVal);
        fd.append('pinned',   isPinned ? '1' : '0');
        audiences.forEach(aud => fd.append('audience[]', aud));

        if (fileInput && fileInput.files && fileInput.files[0]) {
          fd.append('attachment', fileInput.files[0]);
        }

        setSubmitting(true);
        showFormMsg('', false);

        try {
          const resp = await fetch(SAVE_URL, { method: 'POST', body: fd });
          const json = await resp.json();

          if (!json.success) {
            showFormMsg(json.error || 'Something went wrong. Please try again.', true);
            setSubmitting(false);
            return;
          }

          const notice = json.notice;
          const grid   = document.getElementById('j-notice-grid');

          if (json.action === 'update' && editingId) {
            // ---------------------------------------------------------------
            // UPDATE existing card in DOM
            // ---------------------------------------------------------------
            const existingCard = grid && grid.querySelector(`.c-notice-card[data-notice-id="${editingId}"]`);
            if (existingCard && grid) {
              const newCard = buildCardElement(notice);
              grid.insertBefore(newCard, existingCard);
              existingCard.remove();

              // Re-sort: pinned cards first
              if (notice.pinned || notice.is_pinned) {
                grid.prepend(newCard);
              }

              // Highlight animation
              newCard.style.transition = 'box-shadow 0.3s ease';
              newCard.style.boxShadow  = '0 0 0 3px rgba(127, 199, 204, 0.6)';
              setTimeout(() => { newCard.style.boxShadow = ''; }, 1200);
            }
          } else {
            // ---------------------------------------------------------------
            // INSERT new card
            // ---------------------------------------------------------------
            if (grid && notice) {
              const newCard    = buildCardElement(notice);
              const firstUnpin = grid.querySelector('.c-notice-card[data-pinned="false"]');

              if (notice.pinned || notice.is_pinned || !firstUnpin) {
                grid.prepend(newCard);
              } else {
                grid.insertBefore(newCard, firstUnpin);
              }

              const emptyState = document.getElementById('j-empty-state');
              if (emptyState) emptyState.hidden = true;
            }
          }

          resetPostForm();
          if (postView)  postView.style.display  = 'none';
          if (boardView) boardView.style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });

        } catch (err) {
          console.error('[Notice save error]', err);
          showFormMsg('Network error. Please check your connection and try again.', true);
        } finally {
          setSubmitting(false);
        }
      });
    }
  });
})();
