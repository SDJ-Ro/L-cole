/**
 * =========================================================================
 * L'ÉCOLE — PROFILE PAGE CONTROLLER
 * =========================================================================
 * Handles:
 *  1. Edit / Cancel toggle (show inputs, hide values)
 *  2. Save — client-side only (flashes toast, reverts to view mode)
 *  3. Change Password section toggle
 *  4. Password field visibility toggle (show/hide)
 * =========================================================================
 */
(function () {
  'use strict';

  const page      = document.querySelector('.c-profile-page');
  const editBtn   = document.getElementById('j-profile-edit-btn');
  const saveBtn   = document.getElementById('j-profile-save-btn');
  const cancelBtn = document.getElementById('j-profile-cancel-btn');
  const actionsRow = document.getElementById('j-profile-actions');
  const pwdToggle  = document.getElementById('j-profile-pwd-toggle');
  const pwdSection = document.getElementById('j-profile-pwd-section');
  const toast      = document.getElementById('j-profile-toast');

  // Snapshot of original values so Cancel can restore them
  let snapshot = {};

  // -------------------------------------------------------------------------
  // 1. EDIT MODE
  // -------------------------------------------------------------------------
  if (editBtn) {
    editBtn.addEventListener('click', function () {
      const isEditing = page.classList.contains('c-profile-page--editing');
      if (isEditing) {
        cancelEdit();
      } else {
        enterEdit();
      }
    });
  }

  function enterEdit() {
    // Snapshot current displayed values
    snapshot = {};
    page.querySelectorAll('.c-info-card-input').forEach((input) => {
      const id = input.id || input.name;
      snapshot[id] = input.value;
    });

    page.classList.add('c-profile-page--editing');
    if (editBtn) {
      editBtn.textContent = 'Cancel';
      editBtn.dataset.editing = 'true';
    }
    if (actionsRow) actionsRow.classList.add('c-is-open');
  }

  function cancelEdit() {
    // Restore snapshot values
    page.querySelectorAll('.c-info-card-input').forEach((input) => {
      const id = input.id || input.name;
      if (snapshot[id] !== undefined) input.value = snapshot[id];
    });

    exitEditMode();
  }

  function exitEditMode() {
    page.classList.remove('c-profile-page--editing');
    if (editBtn) {
      editBtn.textContent = 'Edit Profile';
      editBtn.dataset.editing = 'false';
    }
    if (actionsRow) actionsRow.classList.remove('c-is-open');
    if (pwdSection) pwdSection.classList.remove('c-is-open');
  }

  if (cancelBtn) cancelBtn.addEventListener('click', cancelEdit);

  // -------------------------------------------------------------------------
  // 2. SAVE (client-side — mirror input values back to display values)
  // -------------------------------------------------------------------------
  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      // Mirror input → display value for each editable card
      page.querySelectorAll('.c-info-card:not(.c-info-card--readonly)').forEach((card) => {
        const input   = card.querySelector('.c-info-card-input');
        const display = card.querySelector('.c-info-card-value');
        if (input && display && input.value.trim()) {
          display.textContent = input.value.trim();
        }
      });

      exitEditMode();
      showToast('Changes saved successfully.');
    });
  }

  // -------------------------------------------------------------------------
  // 3. CHANGE PASSWORD SECTION
  // -------------------------------------------------------------------------
  if (pwdToggle && pwdSection) {
    pwdToggle.addEventListener('click', function () {
      pwdSection.classList.toggle('c-is-open');
      pwdToggle.textContent = pwdSection.classList.contains('c-is-open')
        ? 'Cancel'
        : 'Change Password';
    });
  }

  const pwdSaveBtn = document.getElementById('j-profile-pwd-save');
  if (pwdSaveBtn && pwdSection) {
    pwdSaveBtn.addEventListener('click', function () {
      const newPwd    = document.getElementById('j-pwd-new');
      const confirmPwd = document.getElementById('j-pwd-confirm');
      if (!newPwd || !confirmPwd) return;

      if (newPwd.value.length < 8) {
        alert('Password must be at least 8 characters.');
        return;
      }
      if (newPwd.value !== confirmPwd.value) {
        alert('Passwords do not match.');
        return;
      }
      // Clear fields and close
      newPwd.value = '';
      confirmPwd.value = '';
      const currentPwd = document.getElementById('j-pwd-current');
      if (currentPwd) currentPwd.value = '';
      pwdSection.classList.remove('c-is-open');
      if (pwdToggle) pwdToggle.textContent = 'Change Password';
      showToast('Password updated.');
    });
  }

  // -------------------------------------------------------------------------
  // 4. SHOW / HIDE PASSWORD TOGGLE (eye buttons)
  // -------------------------------------------------------------------------
  document.addEventListener('click', function (e) {
    const eyeBtn = e.target.closest('.j-pwd-toggle-eye');
    if (!eyeBtn) return;
    const targetId = eyeBtn.dataset.target;
    const input = document.getElementById(targetId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  });

  // -------------------------------------------------------------------------
  // 5. TOAST
  // -------------------------------------------------------------------------
  function showToast(message) {
    if (!toast) return;
    const textEl = toast.querySelector('.j-toast-text');
    if (textEl) textEl.textContent = message;
    toast.classList.add('c-is-visible');
    setTimeout(() => toast.classList.remove('c-is-visible'), 3000);
  }

  // -------------------------------------------------------------------------
  // INIT
  // -------------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', function () {
    // Sync inputs from initial display values on page load
    page && page.querySelectorAll('.c-info-card:not(.c-info-card--readonly)').forEach((card) => {
      const input   = card.querySelector('.c-info-card-input');
      const display = card.querySelector('.c-info-card-value');
      if (input && display && !input.value) {
        input.value = display.textContent.trim();
      }
    });
  });
})();
