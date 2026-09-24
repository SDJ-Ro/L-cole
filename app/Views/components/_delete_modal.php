<?php
/**
 * =========================================================================
 * L'ÉCOLE — UNIVERSAL DELETE CONFIRMATION MODAL COMPONENT
 * =========================================================================
 * Single universal delete dialog reused across all academic, management,
 * and future portal sections (Grades, Curriculum, Calendar Events, Notices).
 * =========================================================================
 */
?>
<div class="c-modal-layer" id="j-universal-delete-modal" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close dialog"></button>
  <section class="c-modal c-modal--confirm c-modal--confirm-wide" role="dialog" aria-modal="true" aria-labelledby="j-universal-delete-title">
    <div class="c-confirm-delete__icon" aria-hidden="true">
      <svg class="c-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-trash"/>
      </svg>
    </div>
    <h2 class="c-confirm-delete__title" id="j-universal-delete-title">Delete Academic Structures</h2>
    <p class="c-confirm-delete__description" id="j-universal-delete-desc">Select the grade or the classes you wish to remove.</p>

    <!-- Optional Granular Slot (e.g. Grade/Class pills) - Hidden by default for standard confirms -->
    <div class="c-del-structure__body" id="j-universal-delete-slot" style="display: none;">
      <p class="c-del-structure__label">Grade option</p>
      <div class="c-del-structure__pills" id="j-del-grade-pill-wrap"></div>
      <p class="c-del-structure__label" style="margin-top: 1rem;">Class options</p>
      <div class="c-del-structure__pills" id="j-del-class-pill-wrap"></div>
    </div>

    <div class="c-confirm-delete__actions">
      <button type="button" class="c-btn-plain j-modal-close">Cancel</button>
      <button type="button" class="c-btn-danger" id="j-universal-delete-confirm">Delete</button>
    </div>
  </section>
</div>

<script>
(function() {
  if (window.openUniversalDeleteModal) return;
  window.openUniversalDeleteModal = function({ title, description, buttonText, onConfirm, customSlotRenderer }) {
    const modal = document.getElementById('j-universal-delete-modal');
    if (!modal) return;
    const titleEl = document.getElementById('j-universal-delete-title');
    const descEl  = document.getElementById('j-universal-delete-desc');
    const btnEl   = document.getElementById('j-universal-delete-confirm');
    const slotEl  = document.getElementById('j-universal-delete-slot');

    if (titleEl) titleEl.textContent = title || 'Confirm deletion';
    if (descEl)  descEl.textContent  = description || 'Are you sure? This action cannot be undone.';
    if (btnEl)   {
      btnEl.textContent = buttonText || 'Delete';
      btnEl.disabled = false;
    }

    if (slotEl) {
      if (typeof customSlotRenderer === 'function') {
        slotEl.style.display = '';
        customSlotRenderer(slotEl, btnEl);
      } else {
        slotEl.style.display = 'none';
      }
    }

    const close = () => {
      if (typeof window.closeModal === 'function') {
        window.closeModal(modal);
      } else {
        document.body.style.overflow = '';
        modal.classList.remove('c-is-open');
      }
    };

    if (typeof window.openModal === 'function') {
      window.openModal(modal);
    } else {
      document.body.style.overflow = 'hidden';
      modal.classList.add('c-is-open');

      modal.querySelectorAll('.j-modal-close, .j-modal-backdrop').forEach(b => {
        b.onclick = (e) => { e.preventDefault(); close(); };
      });

      const onEsc = (e) => {
        if (e.key === 'Escape' && modal.classList.contains('c-is-open')) {
          close();
          document.removeEventListener('keydown', onEsc);
        }
      };
      document.addEventListener('keydown', onEsc);
    }

    btnEl.onclick = (e) => {
      e.preventDefault();
      if (typeof onConfirm === 'function') onConfirm();
      close();
    };
  };
})();
</script>
