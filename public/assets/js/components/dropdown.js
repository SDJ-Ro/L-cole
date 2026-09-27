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

    // 2. Vertical check (flip upwards if bottom overflows container or viewport)
    const scrollContainer = root.closest('.c-table-scroll, .c-people-panel');
    let maxContainerBottom = bounds.maxBottom;
    if (scrollContainer) {
      const cRect = scrollContainer.getBoundingClientRect();
      if (cRect.bottom < maxContainerBottom) {
        maxContainerBottom = cRect.bottom - 4;
      }
    }

    const menuHeight = menuRect.height || 85;
    const spaceBelow = maxContainerBottom - triggerRect.bottom;
    const spaceAbove = triggerRect.top - bounds.minTop;

    if ((menuRect.bottom > maxContainerBottom || spaceBelow < menuHeight) && spaceAbove >= menuHeight) {
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
    document.querySelectorAll('.c-has-open-dropdown').forEach((el) => {
      el.classList.remove('c-has-open-dropdown');
    });
  }

  function getDropdownOptionLabel(root, val) {
    if (!root) return val;
    const opt = root.querySelector(`[data-value="${val}"]`);
    if (opt) {
      const sp = opt.querySelector('span') || opt.querySelector('.c-dropdown__option-label');
      return (sp ? sp.textContent : opt.textContent).trim();
    }
    return val;
  }

  function toggleMultiSelectOption(root, targetVal, forceState) {
    if (!root) return;
    const inputName = root.getAttribute('data-name') || 'audience[]';
    const hiddenWrap = root.querySelector('.j-dropdown-hidden-inputs') || root;
    const hasAllOption = Boolean(root.querySelector('[data-value="All"], [data-value="all"]'));
    const isAll = String(targetVal).toLowerCase() === 'all';

    let currentValues = Array.from(root.querySelectorAll('.c-select__option.c-is-selected, .c-dropdown__option.c-is-selected'))
      .map((opt) => opt.getAttribute('data-value') ?? opt.textContent.trim());

    const isCurrentlySelected = currentValues.includes(String(targetVal));
    const shouldSelect = typeof forceState === 'boolean' ? forceState : !isCurrentlySelected;

    if (hasAllOption && isAll) {
      if (shouldSelect) {
        currentValues = ['All'];
      }
    } else {
      if (hasAllOption) {
        currentValues = currentValues.filter((v) => String(v).toLowerCase() !== 'all');
      }
      const strTarget = String(targetVal);
      if (shouldSelect) {
        if (!currentValues.includes(strTarget)) currentValues.push(strTarget);
      } else {
        currentValues = currentValues.filter((v) => v !== strTarget);
      }
      if (hasAllOption && currentValues.length === 0) {
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
        chipsContainer.innerHTML = currentValues.map((val) => {
          const label = getDropdownOptionLabel(root, val);
          return `
            <span class="c-chip">
              <span>${label}</span>
              <span role="button" tabindex="0" class="c-chip__remove j-chip-remove" data-val="${val}" aria-label="Remove ${label}">
                <svg width="10" height="10"><use href="#icon-close"/></svg>
              </span>
            </span>
          `;
        }).join('');
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
    const root = typeof dropdownId === 'string' ? document.getElementById(dropdownId) : dropdownId;
    if (!root) return;
    const hasAllOption = Boolean(root.querySelector('[data-value="All"], [data-value="all"]'));
    let vals = Array.isArray(targetValues) ? targetValues.map(v => String(v)) : (targetValues ? [String(targetValues)] : []);
    if (vals.length === 0 && hasAllOption) {
      vals = ['All'];
    }

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
        chipsContainer.innerHTML = vals.map((val) => {
          const label = getDropdownOptionLabel(root, val);
          return `
            <span class="c-chip">
              <span>${label}</span>
              <span role="button" tabindex="0" class="c-chip__remove j-chip-remove" data-val="${val}" aria-label="Remove ${label}">
                <svg width="10" height="10"><use href="#icon-close"/></svg>
              </span>
            </span>
          `;
        }).join('');
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

        // Hoist table row and cell to prevent being cut off by sibling rows
        const parentRow = root.closest('tr');
        const parentCell = root.closest('td');
        if (parentRow) parentRow.classList.add('c-has-open-dropdown');
        if (parentCell) parentCell.classList.add('c-has-open-dropdown');

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

    if (Array.isArray(targetVal) || root.classList.contains('c-dropdown--multi') || root.classList.contains('c-select--multi') || root.getAttribute('data-multi') === 'true') {
      return setDropdownMultiValues(root, targetVal);
    }

    const options = root.querySelectorAll('.c-select__option, .c-dropdown__option');
    let matched = null;
    const targetStr = String(targetVal ?? '').toLowerCase();

    options.forEach((opt) => {
      const val = opt.getAttribute('data-value') ?? opt.textContent.trim();
      if (val.toLowerCase() === targetStr) {
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
    if (root.classList.contains('c-dropdown--multi') || root.classList.contains('c-select--multi') || root.getAttribute('data-multi') === 'true') {
      const hiddenInputs = root.querySelectorAll('input[type="hidden"]');
      if (hiddenInputs.length > 0) {
        return Array.from(hiddenInputs).map(i => i.value).filter(Boolean);
      }
      const selectedOpts = root.querySelectorAll('.c-select__option.c-is-selected, .c-dropdown__option.c-is-selected');
      if (selectedOpts.length > 0) {
        return Array.from(selectedOpts).map(o => o.getAttribute('data-value') ?? o.textContent.trim());
      }
      return [];
    }
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
