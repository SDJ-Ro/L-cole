/**
 * =========================================================================
 * L'ÉCOLE — COMMON FUNCTIONALITY: DIALOGS & POPUPS
 * =========================================================================
 * PURPOSE:
 *   Universal, standalone controller for all dialog popups, alerts, and modal
 *   overlays across all 5 roles (Admin, Teacher, Management, Parent, Student).
 *
 * GLOBAL FUNCTIONS:
 *   - openModal(modalIdOrElement)  : Opens popup, enables backdrop, locks body scroll.
 *   - closeModal(modalIdOrElement) : Closes popup, restores body scrolling.
 *
 * AUTOMATIC GLOBAL BEHAVIORS:
 *   - Clicking any button with class `.j-modal-close` or attribute `[data-modal-close]`
 *     automatically closes its enclosing modal.
 *   - Clicking the dark background `.j-modal-backdrop` automatically closes the modal.
 *   - Pressing the [Escape] key automatically closes the currently open modal.
 *
 * HOW TO USE IN ANY COMPONENT:
 *   openModal('#rcc-overlay');     // opens the modal
 *   closeModal('#rcc-overlay');    // closes the modal
 *   closeModal();                  // closes whichever modal is currently open
 * =========================================================================
 */

(function () {
  'use strict';

  let openModalsStack = [];

  function resolveElement(target) {
    if (!target) return null;
    if (typeof target === 'string') {
      if (!target.startsWith('#') && !target.startsWith('.')) {
        return document.getElementById(target) || document.querySelector(target);
      }
      return document.querySelector(target);
    }
    return target instanceof HTMLElement ? target : null;
  }

  function openModal(target) {
    const modalEl = resolveElement(target);
    if (!modalEl) return;

    modalEl.style.display = 'flex';
    // Force a microtask/reflow so CSS transitions trigger smoothly
    requestAnimationFrame(() => {
      modalEl.classList.add('c-is-open');
    });

    modalEl.setAttribute('aria-hidden', 'false');
    modalEl.removeAttribute('hidden');

    if (!openModalsStack.includes(modalEl)) {
      openModalsStack.push(modalEl);
    }

    // Lock page background scrolling
    document.body.style.overflow = 'hidden';

    // Focus the first sensible interactive element inside for accessibility
    const focusable = modalEl.querySelector('input:not([type="hidden"]), select, textarea, button:not(.c-modal__close-btn)');
    if (focusable) {
      setTimeout(() => focusable.focus(), 50);
    }

    // Fire a custom event for any component listening
    modalEl.dispatchEvent(new CustomEvent('modal:opened', { bubbles: true }));
  }

  function closeModal(target) {
    let modalEl = resolveElement(target);

    // If target is inside a modal layer/overlay, ascend to the outer layer that owns the backdrop
    if (modalEl) {
      const outerLayer = modalEl.closest('.c-modal-layer, .c-modal-overlay, .c-notices-modal-layer');
      if (outerLayer) {
        modalEl = outerLayer;
      }
    }

    // If no specific modal target is given, close the topmost active modal
    if (!modalEl) {
      modalEl = openModalsStack.pop() || document.querySelector('.c-modal-layer.c-is-open, .c-modal-overlay.c-is-open, .c-notices-modal-layer.c-is-open');
    } else {
      openModalsStack = openModalsStack.filter(m => m !== modalEl);
    }

    if (!modalEl) return;

    modalEl.classList.remove('c-is-open');
    modalEl.setAttribute('aria-hidden', 'true');

    // Smooth transition before hiding display
    setTimeout(() => {
      if (!modalEl.classList.contains('c-is-open')) {
        modalEl.style.display = 'none';
        modalEl.setAttribute('hidden', '');
      }
    }, 200);

    // Restore body scroll only if no other modals remain open
    const remainingOpen = document.querySelectorAll('.c-modal-layer.c-is-open, .c-modal-overlay.c-is-open, .c-notices-modal-layer.c-is-open');
    if (remainingOpen.length <= 1) {
      document.body.style.overflow = '';
    }

    modalEl.dispatchEvent(new CustomEvent('modal:closed', { bubbles: true }));
  }

  // -------------------------------------------------------------------------
  // GLOBAL EVENT LISTENERS (DELEGATED)
  // -------------------------------------------------------------------------

  // 1. Click-to-open listener: covers triggers with [data-modal-open] or [data-modal-target]
  document.addEventListener('click', function (e) {
    const openTrigger = e.target.closest('[data-modal-open], [data-modal-target]');
    if (openTrigger) {
      e.preventDefault();
      const target = openTrigger.getAttribute('data-modal-open') || openTrigger.getAttribute('data-modal-target');
      if (target) {
        openModal(target);
      }
    }
  });

  // 2. Click-to-close listener: covers close buttons, cancel buttons, and backdrops
  document.addEventListener('click', function (e) {
    const closeTrigger = e.target.closest('.j-modal-close, [data-modal-close], .j-modal-backdrop, .c-modal-backdrop, .j-notices-modal-close');
    if (closeTrigger) {
      e.preventDefault();
      // Always find the outer modal layer/overlay first
      const parentModal = closeTrigger.closest('.c-modal-layer, .c-modal-overlay, .c-notices-modal-layer')
                       || closeTrigger.closest('[role="dialog"], [aria-modal="true"]');
      if (parentModal) {
        closeModal(parentModal);
      } else {
        closeModal();
      }
    }
  });

  // 3. Escape key listener: closes topmost open modal
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      const activeModal = document.querySelector('.c-modal-layer.c-is-open, .c-modal-overlay.c-is-open, .c-notices-modal-layer.c-is-open') || openModalsStack[openModalsStack.length - 1];
      if (activeModal) {
        closeModal(activeModal);
      }
    }
  });

  function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  }

  function applyCardDecisionOverlay(cardEl, { status = 'approved', label = 'Approved', delay = 1100, onComplete = null } = {}) {
    if (!cardEl) return;
    cardEl.style.position = 'relative';
    const isApp = !status.toLowerCase().includes('reject') && !status.toLowerCase().includes('declin');
    const el = document.createElement('div');
    el.className = `c-card-overlay ${isApp ? 'c-card-overlay--approved' : 'c-card-overlay--declined'}`;
    el.innerHTML = `<div class="c-card-overlay__badge"><svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#${isApp ? 'icon-check' : 'icon-x'}"/></svg><span>${escapeHtml(label)}</span></div>`;
    cardEl.appendChild(el);

    setTimeout(() => {
      // Trigger decision completion callback (updates status dataset, badges, counts)
      if (onComplete) onComplete(cardEl);

      // Smoothly fade out and remove celebratory banner overlay so original content is revealed
      el.style.transition = 'opacity 300ms ease';
      el.style.opacity = '0';
      setTimeout(() => {
        el.remove();
        cardEl.classList.remove('c-is-decision-locked');
      }, 300);
    }, delay);
  }

  function showFormBottomToast(container, { message = 'Saved successfully!', durationMs = 1000, onComplete = null } = {}) {
    const el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return;
    let toast = el.querySelector('.c-form-bottom-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'c-form-bottom-toast';
      const slot = el.querySelector('.c-modal__footer, footer, [class*="actions"]') || el;
      slot.parentNode.insertBefore(toast, slot.nextSibling);
    }
    toast.innerHTML = `<svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-check"/></svg><span>${escapeHtml(message)}</span>`;
    toast.style.display = 'flex';
    setTimeout(() => {
      if (onComplete) onComplete(el);
      setTimeout(() => { toast.style.display = 'none'; }, 250);
    }, durationMs);
  }

  // Lightweight fallback for showFeedbackBanner in case feedback-banners.js is omitted
  if (typeof window.showFeedbackBanner !== 'function') {
    window.showFeedbackBanner = function (message, type = 'success') {
      console.log(`[Feedback: ${type}] ${message}`);
    };
  }

  // Expose clean, globally accessible functions
  window.openModal = openModal;
  window.closeModal = closeModal;
  window.escapeHtml = escapeHtml;
  window.applyCardDecisionOverlay = applyCardDecisionOverlay;
  window.showFormBottomToast = showFormBottomToast;
})();
