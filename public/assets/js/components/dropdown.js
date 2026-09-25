/**
 * =========================================================================
 * L'ÉCOLE — DROPDOWN COMPONENT CONTROLLER
 * =========================================================================
 * Handles trigger clicks, option selection, keyboard navigation,
 * and click-outside dismissal across all custom dropdowns (both single-select
 * and multi-select with chips inside the trigger box).
 *
 * GLOBAL FUNCTIONS:
 *   - resetDropdown(dropdownId, placeholder) : Clears selection back to placeholder
 *   - setDropdownValue(dropdownId, value)    : Programmatically sets single-select value
 *   - getDropdownValue(dropdownId)           : Reads current selected value
 *   - closeAllDropdowns()                    : Closes all active dropdown menus
 *   - setDropdownMultiValues(id, values)     : Sets multi-select values
 * =========================================================================
 */

(function () {
  'use strict';

  function getSafeViewportBounds() {
    const sidebar = document.querySelector('.c-sidebar');
    let sidebarRight = 0;
    if (sidebar && window.getComputedStyle(sidebar).display !== 'none') {
      const sRect = sidebar.getBoundingClientRect();
      sidebarRight = sRect.right;
    }
    return {
      minLeft: Math.max(sidebarRight + 12, 12),
      maxRight: window.innerWidth - 12,
      minTop: 12,
      maxBottom: window.innerHeight - 12
    };
  }

  function adjustDropdownPosition(root, trigger) {
    const menu = root.querySelector('.c-select__menu, .c-dropdown__menu');
    if (!menu || !trigger) return;

    menu.style.left = '';
    menu.style.right = '';
    menu.style.top = '';
    menu.style.bottom = '';

    const bounds = getSafeViewportBounds();
    const menuRect = menu.getBoundingClientRect();
    const triggerRect = trigger.getBoundingClientRect();

    // 1. Horizontal check against sidebar (left edge)
    if (menuRect.left < bounds.minLeft) {
      const offset = bounds.minLeft - triggerRect.left;
      menu.style.left = `${Math.max(0, offset)}px`;
      menu.style.right = 'auto';
    } else if (menuRect.right > bounds.maxRight) {
      menu.style.left = 'auto';
      menu.style.right = '0';
    }

    // 2. Vertical check (flip upwards if bottom overflows and there's space above)
    const spaceBelow = bounds.maxBottom - triggerRect.bottom;
    const spaceAbove = triggerRect.top - bounds.minTop;
    if (menuRect.bottom > bounds.maxBottom && spaceAbove > spaceBelow) {
      menu.style.top = 'auto';
      menu.style.bottom = 'calc(100% + 4px)';
    }
  }

  function closeAllDropdowns() {
    document.querySelectorAll('.c-select.c-is-open, .c-dropdown.c-is-open').forEach((el) => {
      el.classList.remove('c-is-open');
      const trigger = el.querySelector('.c-select__trigger, .c-dropdown__trigger');
      if (trigger) trigger.setAttribute('aria-expanded', 'false');
      const menu = el.querySelector('.c-select__menu, .c-dropdown__menu');
      if (menu) {
        menu.style.left = '';
        menu.style.right = '';
        menu.style.top = '';
        menu.style.bottom = '';
      }
    });
  }

  function toggleMultiSelectOption(root, targetVal, forceState) {
    if (!root) return;
    const inputName = root.getAttribute('data-name') || 'audience[]';
    const hiddenWrap = root.querySelector('.j-dropdown-hidden-inputs') || root;

    let currentValues = Array.from(root.querySelectorAll('.c-select__option.c-is-selected, .c-dropdown__option.c-is-selected'))
      .map((opt) => opt.getAttribute('data-value') ?? opt.textContent.trim());

    const isCurrentlySelected = currentValues.includes(targetVal);
    const shouldSelect = typeof forceState === 'boolean' ? forceState : !isCurrentlySelected;

    if (targetVal.toLowerCase() === 'all') {
      if (shouldSelect) {
        currentValues = ['All'];
      }
    } else {
      currentValues = currentValues.filter((v) => v.toLowerCase() !== 'all');
      if (shouldSelect) {
        if (!currentValues.includes(targetVal)) currentValues.push(targetVal);
      } else {
        currentValues = currentValues.filter((v) => v !== targetVal);
      }
      if (currentValues.length === 0) {
        currentValues = ['All'];
      }
    }

    // Update options in menu
    root.querySelectorAll('.c-select__option, .c-dropdown__option').forEach((opt) => {
      const val = opt.getAttribute('data-value') ?? opt.textContent.trim();
      const sel = currentValues.includes(val);
      opt.classList.toggle('c-is-selected', sel);
      opt.setAttribute('aria-selected', sel ? 'true' : 'false');
    });

    // Update chips inside trigger
    const chipsContainer = root.querySelector('.j-dropdown-chips, .j-tag-chips');
    const placeholderEl = root.querySelector('.j-dropdown-placeholder, .c-dropdown__placeholder');

    if (chipsContainer) {
      if (currentValues.length === 0) {
        chipsContainer.innerHTML = '';
        if (placeholderEl) placeholderEl.style.display = 'inline';
      } else {
        if (placeholderEl) placeholderEl.style.display = 'none';
        chipsContainer.innerHTML = currentValues.map((val) => `
          <span class="c-chip">
            <span>${val}</span>
            <span role="button" tabindex="0" class="c-chip__remove j-chip-remove" data-val="${val}" aria-label="Remove ${val}">
              <svg width="10" height="10"><use href="#icon-close"/></svg>
            </span>
          </span>
        `).join('');
      }
    }

    // Update hidden inputs
    if (hiddenWrap) {
      const oldInputs = hiddenWrap.querySelectorAll(`input[name="${inputName}"]`);
      oldInputs.forEach((inp) => inp.remove());
      currentValues.forEach((val) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = inputName;
        inp.value = val;
        hiddenWrap.appendChild(inp);
      });
    }

    // Dispatch change event
    root.dispatchEvent(new CustomEvent('dropdown:change', {
      detail: {
        value: currentValues.join(','),
        values: currentValues
      },
      bubbles: true
    }));
  }

  function setDropdownMultiValues(dropdownId, targetValues) {
    const root = document.getElementById(dropdownId);
    if (!root) return;
    let vals = Array.isArray(targetValues) ? targetValues : [targetValues];
    if (vals.length === 0) vals = ['All'];

    const inputName = root.getAttribute('data-name') || 'audience[]';
    const hiddenWrap = root.querySelector('.j-dropdown-hidden-inputs') || root;

    // Update options UI
    root.querySelectorAll('.c-select__option, .c-dropdown__option').forEach((opt) => {
      const val = opt.getAttribute('data-value') ?? opt.textContent.trim();
      const sel = vals.includes(val);
      opt.classList.toggle('c-is-selected', sel);
      opt.setAttribute('aria-selected', sel ? 'true' : 'false');
    });

    // Update chips inside trigger
    const chipsContainer = root.querySelector('.j-dropdown-chips, .j-tag-chips');
    const placeholderEl = root.querySelector('.j-dropdown-placeholder, .c-dropdown__placeholder');

    if (chipsContainer) {
      if (vals.length === 0) {
        chipsContainer.innerHTML = '';
        if (placeholderEl) placeholderEl.style.display = 'inline';
      } else {
        if (placeholderEl) placeholderEl.style.display = 'none';
        chipsContainer.innerHTML = vals.map((val) => `
          <span class="c-chip">
            <span>${val}</span>
            <span role="button" tabindex="0" class="c-chip__remove j-chip-remove" data-val="${val}" aria-label="Remove ${val}">
              <svg width="10" height="10"><use href="#icon-close"/></svg>
            </span>
          </span>
        `).join('');
      }
    }

    // Update hidden inputs
    if (hiddenWrap) {
      const oldInputs = hiddenWrap.querySelectorAll(`input[name="${inputName}"]`);
      oldInputs.forEach((inp) => inp.remove());
      vals.forEach((val) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = inputName;
        inp.value = val;
        hiddenWrap.appendChild(inp);
      });
    }

    root.dispatchEvent(new CustomEvent('dropdown:change', {
      detail: {
        value: vals.join(','),
        values: vals
      },
      bubbles: true
    }));
  }

  // Global delegation
  document.addEventListener('click', function (e) {
    // 1. Remove chip click inside multi-select trigger
    const removeBtn = e.target.closest('.j-chip-remove');
    if (removeBtn) {
      e.preventDefault();
      e.stopPropagation();
      const root = removeBtn.closest('.c-select, .c-dropdown');
      if (!root) return;
      const removeVal = removeBtn.getAttribute('data-val');
      toggleMultiSelectOption(root, removeVal, false);
      return;
    }

    // 2. Trigger click
    const trigger = e.target.closest('.c-select__trigger, .c-dropdown__trigger');
    if (trigger) {
      e.preventDefault();
      e.stopPropagation();
      const root = trigger.closest('.c-select, .c-dropdown');
      if (!root || root.classList.contains('c-is-disabled')) return;

      const wasOpen = root.classList.contains('c-is-open');
      closeAllDropdowns();

      if (!wasOpen) {
        root.classList.add('c-is-open');
        trigger.setAttribute('aria-expanded', 'true');
        adjustDropdownPosition(root, trigger);
      }
      return;
    }

    // 3. Option click
    const option = e.target.closest('.c-select__option, .c-dropdown__option');
    if (option) {
      e.preventDefault();
      e.stopPropagation();
      const root = option.closest('.c-select, .c-dropdown');
      if (!root) return;

      const isMulti = root.hasAttribute('data-multi') || root.classList.contains('c-dropdown--multi');
      const value = option.getAttribute('data-value') ?? option.textContent.trim();
      const label = option.querySelector('.c-dropdown__option-label')?.textContent.trim() || option.textContent.trim();

      if (isMulti) {
        toggleMultiSelectOption(root, value);
        return;
      }

      // Single-select option click
      root.querySelectorAll('.c-select__option, .c-dropdown__option').forEach((opt) => {
        opt.classList.remove('c-is-selected');
      });
      option.classList.add('c-is-selected');

      const trigger = root.querySelector('.c-select__trigger, .c-dropdown__trigger');
      const valueLabel = root.querySelector('.j-select-value, .c-dropdown__value');
      if (valueLabel) {
        valueLabel.textContent = label;
        valueLabel.classList.remove('c-dropdown__placeholder');
      }
      if (trigger) {
        trigger.classList.remove('is-placeholder');
        trigger.classList.add('has-value');
      }

      const hiddenInput = root.querySelector('input[type="hidden"]');
      if (hiddenInput) {
        hiddenInput.value = value;
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
      }

      closeAllDropdowns();
      root.dispatchEvent(new CustomEvent('dropdown:change', { detail: { value, label }, bubbles: true }));
      return;
    }

    // 4. Click outside closes dropdowns
    closeAllDropdowns();
  });

  // Keyboard accessibility
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAllDropdowns();
      return;
    }

    if (e.key === 'Enter' || e.key === ' ') {
      const removeBtn = e.target.closest('.j-chip-remove');
      if (removeBtn) {
        e.preventDefault();
        e.stopPropagation();
        const root = removeBtn.closest('.c-select, .c-dropdown');
        if (!root) return;
        const removeVal = removeBtn.getAttribute('data-val');
        toggleMultiSelectOption(root, removeVal, false);
        return;
      }

      const trigger = e.target.closest('.c-select__trigger, .c-dropdown__trigger');
      if (trigger && !e.target.closest('.j-chip-remove')) {
        e.preventDefault();
        trigger.click();
      }
    }
  });

  function setDropdownValue(dropdownId, targetVal) {
    const root = typeof dropdownId === 'string' ? document.getElementById(dropdownId) : dropdownId;
    if (!root) return;

    const options = root.querySelectorAll('.c-select__option, .c-dropdown__option');
    let matched = null;

    options.forEach((opt) => {
      const val = opt.getAttribute('data-value') ?? opt.textContent.trim();
      if (val.toLowerCase() === (targetVal || '').toLowerCase()) {
        matched = opt;
        opt.classList.add('c-is-selected');
        opt.setAttribute('aria-selected', 'true');
      } else {
        opt.classList.remove('c-is-selected');
        opt.setAttribute('aria-selected', 'false');
      }
    });

    if (matched) {
      const label = matched.textContent.trim();
      const valueLabel = root.querySelector('.j-select-value, .c-dropdown__value');
      if (valueLabel) valueLabel.textContent = label;

      const hiddenInput = root.querySelector('input[type="hidden"]');
      if (hiddenInput) {
        hiddenInput.value = matched.getAttribute('data-value') ?? label;
      }
    }
  }

  function getDropdownValue(dropdownId) {
    const root = typeof dropdownId === 'string' ? document.getElementById(dropdownId) : dropdownId;
    if (!root) return '';
    const hiddenInput = root.querySelector('input[type="hidden"]');
    if (hiddenInput && hiddenInput.value !== '') return hiddenInput.value;
    const selectedOpt = root.querySelector('.c-select__option.c-is-selected, .c-dropdown__option.c-is-selected');
    if (selectedOpt) return selectedOpt.getAttribute('data-value') ?? selectedOpt.textContent.trim();
    return '';
  }

  function resetDropdown(dropdownId, placeholder) {
    const root = typeof dropdownId === 'string' ? document.getElementById(dropdownId) : dropdownId;
    if (!root) return;

    // Reset hidden input
    const hiddenInput = root.querySelector('input[type="hidden"]');
    if (hiddenInput) hiddenInput.value = '';

    // Reset visible text label
    const valSpan = root.querySelector('.j-select-value, .c-dropdown__value');
    if (valSpan) {
      valSpan.textContent = placeholder || 'Select an option...';
      valSpan.classList.add('c-dropdown__placeholder');
    }

    // Reset trigger classes
    const trigger = root.querySelector('.c-select__trigger, .c-dropdown__trigger');
    if (trigger) {
      trigger.classList.add('is-placeholder');
      trigger.classList.remove('has-value');
    }

    // Clear selected state on all options
    root.querySelectorAll('.c-select__option, .c-dropdown__option').forEach((opt) => {
      opt.classList.remove('c-is-selected');
      opt.setAttribute('aria-selected', 'false');
    });

    // Clear any multi-select chips
    const chipsContainer = root.querySelector('.j-dropdown-chips, .j-tag-chips');
    if (chipsContainer) chipsContainer.innerHTML = '';
  }

  window.closeAllDropdowns = closeAllDropdowns;
  window.toggleMultiSelectOption = toggleMultiSelectOption;
  window.setDropdownMultiValues = setDropdownMultiValues;
  window.setDropdownValue = setDropdownValue;
  window.getDropdownValue = getDropdownValue;
  window.resetDropdown = resetDropdown;
  window.getSafeViewportBounds = getSafeViewportBounds;
  window.adjustDropdownPosition = adjustDropdownPosition;
})();
