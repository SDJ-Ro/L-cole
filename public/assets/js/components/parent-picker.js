/**
 * =========================================================================
 * L'ÉCOLE — PARENT PICKER COMPONENT CONTROLLER
 * Searches, displays, and links registered parents to admitted students.
 * Supports both Inline Form Search & Modal Dialog with live sibling preview.
 * =========================================================================
 */
(function () {
  'use strict';

  function getLocalParents() {
    let data = window.__PEOPLE_DATA__;
    if (!data) {
      const jsonEl = document.getElementById('j-people-data');
      if (jsonEl) {
        try { data = JSON.parse(jsonEl.textContent); } catch (e) {}
      }
    }
    return (data?.parents || []).filter(function (p) {
      return (p.status || '').toLowerCase() !== 'deactivated';
    });
  }

  function selectParent(parent) {
    if (!parent) return;

    const parentCode = parent.id || parent.parent_id || '';
    const parentName = parent.name || parent.full_name || '';
    const parentEmail = parent.email || parent.personal_email || '';
    const parentPhone = parent.phone || parent.mobile || parent.mobile_phone || '';
    const parentRel = parent.relation || parent.relationship || 'Guardian';
    const parentAddr = parent.homeAddress || parent.home_address || parent.address || parent.officeAddress || '';
    const children = parent.children || [];

    // Set hidden input value for form submission
    const parentInput = document.getElementById('existing-parent-id');
    if (parentInput) parentInput.value = parentCode;

    // Populate card details
    const nameEl = document.getElementById('selected-parent-name');
    if (nameEl) nameEl.textContent = parentName;

    const codeEl = document.getElementById('selected-parent-code');
    if (codeEl) codeEl.textContent = parentCode;

    const emailEl = document.getElementById('selected-parent-email');
    if (emailEl) emailEl.textContent = parentEmail || 'No email recorded';

    const phoneEl = document.getElementById('selected-parent-phone');
    if (phoneEl) phoneEl.textContent = parentPhone || 'No phone recorded';

    const relEl = document.getElementById('selected-parent-relation');
    if (relEl) relEl.textContent = parentRel;

    const initialsEl = document.getElementById('selected-parent-initials');
    if (initialsEl) {
      const parts = parentName.trim().split(/\s+/);
      const initials = parts.length > 1 ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() : (parts[0][0] || 'P').toUpperCase();
      initialsEl.textContent = initials;
    }

    // Address sync & preview
    const addrWrap = document.getElementById('selected-parent-address-wrap');
    const addrEl = document.getElementById('selected-parent-address');
    const syncCheckbox = document.getElementById('j-sync-parent-address');
    const studentAddressInput = document.querySelector('textarea[name="homeAddress"]');

    if (parentAddr) {
      if (addrEl) addrEl.textContent = parentAddr;
      if (addrWrap) addrWrap.style.display = 'flex';
      if (syncCheckbox && syncCheckbox.checked && studentAddressInput && !studentAddressInput.value.trim()) {
        studentAddressInput.value = parentAddr;
      }
    } else {
      if (addrWrap) addrWrap.style.display = 'none';
    }

    // Children / Sibling tags
    const childWrap = document.getElementById('selected-parent-children-wrap');
    const childContainer = document.getElementById('selected-parent-children');
    if (childContainer) {
      childContainer.innerHTML = '';
      if (children.length > 0) {
        children.forEach(function (c) {
          const tag = document.createElement('span');
          tag.className = 'c-sibling-tag';
          tag.textContent = typeof c === 'string' ? c : (c.full_name || c.name || '');
          childContainer.appendChild(tag);
        });
        if (childWrap) childWrap.style.display = 'flex';
      } else {
        if (childWrap) childWrap.style.display = 'none';
      }
    }

    // Toggle container visibilities
    const inlinePickerWrap = document.getElementById('j-inline-parent-picker');
    if (inlinePickerWrap) inlinePickerWrap.style.display = 'none';

    const promptCard = document.getElementById('j-existing-parent-prompt');
    if (promptCard) promptCard.style.display = 'none';

    const selectedCard = document.getElementById('selected-parent-card');
    if (selectedCard) selectedCard.style.display = 'block';

    // Close modal if open
    const dialog = document.getElementById('parent-picker');
    if (dialog && typeof dialog.close === 'function' && dialog.open) {
      dialog.close();
    }
  }

  function initInlineParentPicker() {
    const searchInput = document.getElementById('j-inline-parent-search');
    const listContainer = document.getElementById('j-inline-parent-list');
    const countEl = document.getElementById('j-inline-parent-count');
    const parentIdInput = document.getElementById('existing-parent-id');
    const pickerWrap = document.getElementById('j-inline-parent-picker');
    const cardWrap = document.getElementById('selected-parent-card');
    const changeBtn = document.getElementById('j-change-selected-parent');
    const addressSyncCheckbox = document.getElementById('j-sync-parent-address');
    const studentAddressInput = document.querySelector('textarea[name="homeAddress"]');

    if (!listContainer) return;

    function renderList(query = '') {
      const q = query.trim().toLowerCase();
      const parents = getLocalParents();
      const filtered = q ? parents.filter(function (p) {
        const name = (p.name || p.full_name || '').toLowerCase();
        const id = (p.id || p.parent_id || '').toLowerCase();
        const phone = (p.phone || p.mobile || p.mobile_phone || '').toLowerCase();
        const email = (p.email || p.personal_email || '').toLowerCase();
        const children = (p.children || []).map(c => typeof c === 'string' ? c : (c.name || c.full_name || '')).join(' ').toLowerCase();
        return name.includes(q) || id.includes(q) || phone.includes(q) || email.includes(q) || children.includes(q);
      }) : parents;

      if (countEl) {
        countEl.textContent = filtered.length > 0 
          ? `Showing ${filtered.length} active registered parent(s)`
          : 'No matching active parents found. Try a different search, or switch to "Create New Guardian".';
      }

      if (filtered.length === 0) {
        listContainer.innerHTML = '<div style="padding:1rem;text-align:center;color:rgba(15,65,74,0.6);font-size:0.8125rem;">No registered parents match your search.</div>';
        return;
      }

      listContainer.innerHTML = filtered.map(function (p) {
        const pId = p.id || p.parent_id;
        const pName = p.name || p.full_name;
        const pRel = p.relation || p.relationship || 'Guardian';
        const pEmail = p.email || p.personal_email || '';
        const pPhone = p.phone || p.mobile || p.mobile_phone || '';
        const children = (p.children || []).map(c => typeof c === 'string' ? c : (c.name || c.full_name || '')).filter(Boolean);
        const childText = children.length > 0 ? `Siblings: ${children.join(', ')}` : '';

        return `
          <div class="c-parent-inline-item">
            <div class="c-parent-inline-info">
              <div class="c-parent-inline-top">
                <span class="c-parent-inline-name">${pName}</span>
                <span class="c-badge-pill c-badge-terracotta">${pId}</span>
              </div>
              <div class="c-parent-inline-meta">${pRel} · ${pPhone} · ${pEmail}</div>
              ${childText ? `<div class="c-parent-inline-children">${childText}</div>` : ''}
            </div>
            <button type="button" class="c-btn-solid-sky c-btn-sm j-select-inline-parent" data-parent-id="${pId}">
              Select Parent
            </button>
          </div>
        `;
      }).join('');
    }

    // Initial render
    renderList(searchInput ? searchInput.value : '');

    if (searchInput && !searchInput.dataset.bound) {
      searchInput.dataset.bound = 'true';
      searchInput.addEventListener('input', function (e) {
        renderList(e.target.value);
      });
    }

    if (!listContainer.dataset.bound) {
      listContainer.dataset.bound = 'true';
      listContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.j-select-inline-parent');
        if (!btn) return;
        const pid = btn.dataset.parentId;
        const p = getLocalParents().find(x => (x.id === pid || x.parent_id === pid));
        if (p) selectParent(p);
      });
    }

    if (changeBtn && !changeBtn.dataset.bound) {
      changeBtn.dataset.bound = 'true';
      changeBtn.addEventListener('click', function () {
        if (parentIdInput) parentIdInput.value = '';
        if (cardWrap) cardWrap.style.display = 'none';
        if (pickerWrap) {
          pickerWrap.style.display = 'block';
          renderList(searchInput ? searchInput.value : '');
        }
      });
    }

    if (addressSyncCheckbox && !addressSyncCheckbox.dataset.bound) {
      addressSyncCheckbox.dataset.bound = 'true';
      addressSyncCheckbox.addEventListener('change', function () {
        if (addressSyncCheckbox.checked && parentIdInput && parentIdInput.value) {
          const p = getLocalParents().find(x => (x.id === parentIdInput.value || x.parent_id === parentIdInput.value));
          const pAddress = p?.homeAddress || p?.address || p?.officeAddress || '';
          if (pAddress && studentAddressInput) {
            studentAddressInput.value = pAddress;
          }
        }
      });
    }
  }

  // Modal Dialog Support (Fallback if modal dialog is triggered)
  const dialog = document.getElementById('parent-picker');
  const chooseButton = document.getElementById('choose-existing-parent');
  if (dialog) {
    if (chooseButton) {
      chooseButton.addEventListener('click', function () {
        dialog.showModal();
      });
    }
    document.querySelectorAll('.j-parent-picker-close').forEach(function (btn) {
      btn.addEventListener('click', function () {
        dialog.close();
      });
    });
  }

  // Auto-init on DOMContentLoaded or immediate execution
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInlineParentPicker);
  } else {
    initInlineParentPicker();
  }

  // Expose on window for people-directory.js and dynamic form toggles
  window.selectParent = selectParent;
  window.initInlineParentPicker = initInlineParentPicker;

})();
