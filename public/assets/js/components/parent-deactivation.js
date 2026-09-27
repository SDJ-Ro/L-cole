/**
 * =========================================================================
 * L'ÉCOLE — PARENT DEACTIVATION CONTROLLER
 * Enforces Sibling-Guard business rules upon parent account deactivation.
 * =========================================================================
 */
(function () {
  'use strict';

  const dialog = document.getElementById('deactivate-parent-dialog');
  if (!dialog) return;

  const confirmBtn = document.getElementById('confirm-parent-deactivation');
  const cancelBtn = document.getElementById('cancel-parent-deactivation');
  const errorEl = document.getElementById('deactivate-parent-error');
  const nameEl = document.getElementById('deactivate-parent-name');
  const codeEl = document.getElementById('deactivate-parent-code');
  const descEl = document.getElementById('deactivate-parent-description');

  const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
  let targetParentCode = '';
  let triggeringDropdown = null;
  let isSubmitting = false;

  function getCsrfToken() {
    return document.querySelector('input[name="_csrf_token"]')?.value ||
           document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
           '';
  }

  function openDeactivationDialog(parent, dropdownSource = null) {
    if (!parent) return;
    targetParentCode = parent.id || parent.parent_id;
    triggeringDropdown = dropdownSource;

    if (nameEl) nameEl.textContent = parent.name || parent.full_name;
    if (codeEl) codeEl.textContent = targetParentCode;

    const children = parent.children || [];
    if (descEl) {
      if (children.length > 0) {
        descEl.innerHTML = `This parent is currently linked to student(s): <strong>${children.map(c => typeof c === 'string' ? c : (c.full_name || c.name)).join(', ')}</strong>.<br><br><strong>Sibling-Guard Policy:</strong> Every enrolled student must have an active guardian. You cannot deactivate this parent until all linked students are either deactivated, withdrawn, or reassigned to another guardian.`;
      } else {
        descEl.textContent = 'This will immediately revoke portal access and block future logins for this parent account. Historical academic records and relationships will remain archived in the database.';
      }
    }

    if (errorEl) {
      errorEl.style.display = 'none';
      errorEl.textContent = '';
    }

    dialog.showModal();
  }

  // 1. Direct button triggers
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.j-deactivate-parent');
    if (btn) {
      const code = btn.dataset.id || btn.dataset.parentCode;
      const parent = (window.__PEOPLE_DATA__?.parents || []).find(p => (p.id || p.parent_id) === code);
      if (parent) openDeactivationDialog(parent);
    }
  });

  // 2. Intercept status dropdown change on parent rows
  document.addEventListener('dropdown:change', function (e) {
    const dropdown = e.target.closest('.c-dropdown--status');
    if (!dropdown) return;

    // Check if this dropdown belongs to a parent
    const row = dropdown.closest('tr[data-role="parent"], tr[data-parent-id]');
    if (!row) return;

    const selectedValue = e.detail?.value || '';
    if (selectedValue.toLowerCase() === 'deactivated' || selectedValue.toLowerCase() === 'inactive') {
      const parentCode = row.getAttribute('data-id') || row.getAttribute('data-parent-id');
      const parent = (window.__PEOPLE_DATA__?.parents || []).find(p => (p.id || p.parent_id) === parentCode);
      if (parent) {
        openDeactivationDialog(parent, dropdown);
      }
    }
  });

  // 3. Confirm deactivation
  if (confirmBtn) {
    confirmBtn.addEventListener('click', async function () {
      if (isSubmitting || !targetParentCode) return;
      isSubmitting = true;
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Deactivating...';

      if (errorEl) {
        errorEl.style.display = 'none';
        errorEl.textContent = '';
      }

      const body = new FormData();
      body.set('parentCode', targetParentCode);
      body.set('confirmed', 'yes');
      body.set('_csrf_token', getCsrfToken());

      try {
        const response = await fetch(`${basePath}/deactivateParent`, {
          method: 'POST',
          body: body,
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(data.error || 'Failed to deactivate parent account.');
        }

        // Successfully deactivated: update UI
        dialog.close();

        // Update local dataset
        const parent = (window.__PEOPLE_DATA__?.parents || []).find(p => (p.id || p.parent_id) === targetParentCode);
        if (parent) parent.status = 'Deactivated';

        // Update status in table row
        const row = document.querySelector(`tr[data-id="${targetParentCode}"]`);
        if (row) {
          const pill = row.querySelector('.c-status-pill, .c-dropdown--status .c-select__trigger');
          if (pill) {
            pill.textContent = 'Deactivated';
            pill.classList.remove('c-status-active');
            pill.classList.add('c-status-inactive');
          }
        }

        // Show toast or alert
        alert(`Parent account ${targetParentCode} has been deactivated.`);
        window.location.reload();

      } catch (err) {
        if (errorEl) {
          errorEl.textContent = err.message;
          errorEl.style.display = 'block';
        }
      } finally {
        isSubmitting = false;
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Deactivate Account';
      }
    });
  }

  // 4. Cancel
  if (cancelBtn) {
    cancelBtn.addEventListener('click', function () {
      dialog.close();
      // Revert dropdown if opened via dropdown
      if (triggeringDropdown) {
        const triggerSpan = triggeringDropdown.querySelector('.c-select__trigger span');
        if (triggerSpan) triggerSpan.textContent = 'Active';
      }
    });
  }

  document.querySelectorAll('.j-deactivate-parent-close').forEach(function (btn) {
    btn.addEventListener('click', () => dialog.close());
  });

})();
