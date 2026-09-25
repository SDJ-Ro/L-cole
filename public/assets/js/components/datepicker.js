/**
 * =========================================================================
 * L'ÉCOLE — REUSABLE DATEPICKER CONTROLLER
 * =========================================================================
 * Extracted 1:1 from L'École UI component (DatePicker.tsx / app.js)
 * Supports:
 *   - Formatted label trigger ("24 Oct 2024" or placeholder)
 *   - Floating popup calendar with month/year navigation
 *   - Quick month dropdown & year dropdown (1950 - 2035)
 *   - Day selection with tone accent (sky, sunshine, terracotta, maroon)
 *   - Hidden ISO value (yyyy-mm-dd) input for form submissions
 *   - Emits custom event 'datepicker:change' with { value: 'yyyy-mm-dd' }
 * =========================================================================
 */

(function () {
  'use strict';

  const WEEKDAYS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
  const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  function parseDate(val) {
    if (!val) return new Date();
    if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
      const d = new Date(val + 'T00:00:00');
      if (!isNaN(d.getTime())) return d;
    }
    const d = new Date(val);
    return isNaN(d.getTime()) ? new Date() : d;
  }

  function formatDisplayDate(val) {
    if (!val) return '';
    const date = parseDate(val);
    if (isNaN(date.getTime())) return '';
    const day = String(date.getDate()).padStart(2, '0');
    const month = date.toLocaleString('en-GB', { month: 'short' });
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
  }

  function toIso(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function startOfMonth(date) {
    return new Date(date.getFullYear(), date.getMonth(), 1);
  }

  function mondayIndex(date) {
    return (date.getDay() + 6) % 7;
  }

  function addDays(date, amount) {
    const n = new Date(date);
    n.setDate(n.getDate() + amount);
    return n;
  }

  function calendarDays(month) {
    const first = startOfMonth(month);
    const offset = mondayIndex(first);
    const gridStart = addDays(first, -offset);
    const days = [];
    for (let i = 0; i < 42; i++) {
      days.push(addDays(gridStart, i));
    }
    return days;
  }

  function initDatePicker(rootEl) {
    if (rootEl.__dp_initialized) return;
    rootEl.__dp_initialized = true;

    const trigger = rootEl.querySelector('.j-dp-trigger');
    const hiddenInput = rootEl.querySelector('.j-dp-input');
    const placeholderText = trigger.getAttribute('data-placeholder') || 'Select date';
    const tone = rootEl.getAttribute('data-tone') || 'sky';

    let currentValue = hiddenInput ? hiddenInput.value : '';
    let visibleMonth = startOfMonth(parseDate(currentValue));
    let popupEl = null;

    function updateTriggerLabel() {
      const labelEl = trigger.querySelector('.j-dp-label');
      if (!labelEl) return;
      if (currentValue) {
        labelEl.textContent = formatDisplayDate(currentValue);
        labelEl.classList.remove('c-dp-placeholder');
      } else {
        labelEl.textContent = placeholderText;
        labelEl.classList.add('c-dp-placeholder');
      }
    }

    function closePopup() {
      if (popupEl) {
        popupEl.remove();
        popupEl = null;
      }
      document.removeEventListener('mousedown', onOutsideClick, true);
      document.removeEventListener('keydown', onKeyDown, true);
    }

    function onOutsideClick(e) {
      if (!rootEl.contains(e.target) && !(popupEl && popupEl.contains(e.target))) {
        closePopup();
      }
    }

    function onKeyDown(e) {
      if (e.key === 'Escape') {
        closePopup();
        trigger.focus();
      }
    }

    function selectDate(date) {
      currentValue = toIso(date);
      if (hiddenInput) {
        hiddenInput.value = currentValue;
      }
      updateTriggerLabel();
      visibleMonth = startOfMonth(date);
      closePopup();

      rootEl.dispatchEvent(new CustomEvent('datepicker:change', {
        bubbles: true,
        detail: { value: currentValue }
      }));
    }

    function renderPopup() {
      if (popupEl) popupEl.remove();

      popupEl = document.createElement('div');
      popupEl.className = 'c-dp-calendar';

      const days = calendarDays(visibleMonth);
      const selIso = currentValue || '';
      const todayIso = toIso(new Date());

      let daysHtml = '';
      days.forEach(date => {
        const iso = toIso(date);
        const inMonth = date.getMonth() === visibleMonth.getMonth();
        const isSel = iso === selIso;
        const isToday = iso === todayIso;
        daysHtml += `
          <button type="button" 
            class="c-dp-day j-dp-day-btn ${isSel ? 'c-dp-selected-' + tone : 'c-dp-hover-' + tone} ${inMonth ? '' : 'c-dp-outside'} ${isToday ? 'is-today-day' : ''}" 
            data-iso="${iso}">
            ${date.getDate()}
          </button>
        `;
      });

      const currentYear = visibleMonth.getFullYear();
      const currentMonthIndex = visibleMonth.getMonth();

      popupEl.innerHTML = `
        <div class="c-dp-header">
          <button type="button" class="c-dp-nav-btn j-dp-prev c-dp-hover-${tone}" aria-label="Previous month">
            <svg class="c-icon" width="16" height="16"><use href="#icon-chevronLeft"/></svg>
          </button>
          <div class="c-dp-month-year">
            <div class="c-dp-header-select">
              <button type="button" class="c-dp-header-trigger j-dp-month-trigger">
                <span>${MONTH_NAMES[currentMonthIndex]}</span>
                <svg class="c-icon" width="12" height="12"><use href="#icon-chevronDown"/></svg>
              </button>
              <div class="c-dp-header-menu j-dp-month-menu" style="display: none;">
                ${MONTH_NAMES.map((m, idx) => `
                  <button type="button" class="c-dp-menu-item ${idx === currentMonthIndex ? 'is-selected' : ''}" data-month="${idx}">${m}</button>
                `).join('')}
              </div>
            </div>

            <div class="c-dp-header-select">
              <button type="button" class="c-dp-header-trigger c-dp-year j-dp-year-trigger">
                <span>${currentYear}</span>
                <svg class="c-icon" width="12" height="12"><use href="#icon-chevronDown"/></svg>
              </button>
              <div class="c-dp-header-menu c-dp-year-menu j-dp-year-menu" style="display: none;">
                ${Array.from({ length: 86 }, (_, i) => 1950 + i).map(y => `
                  <button type="button" class="c-dp-menu-item ${y === currentYear ? 'is-selected' : ''}" data-year="${y}">${y}</button>
                `).join('')}
              </div>
            </div>
          </div>
          <button type="button" class="c-dp-nav-btn c-dp-next j-dp-next c-dp-hover-${tone}" aria-label="Next month">
            <svg class="c-icon" width="16" height="16"><use href="#icon-chevronRight"/></svg>
          </button>
        </div>

        <div class="c-dp-weekdays">
          ${WEEKDAYS.map(w => `<div class="c-dp-weekday">${w}</div>`).join('')}
        </div>

        <div class="c-dp-days">
          ${daysHtml}
        </div>

        <div class="c-dp-footer">
          <span class="c-dp-hint">Select a date</span>
          <button type="button" class="c-dp-today-btn j-dp-today">Today</button>
        </div>
      `;

      // Event listeners inside popup
      popupEl.querySelector('.j-dp-prev').addEventListener('click', () => {
        visibleMonth.setMonth(visibleMonth.getMonth() - 1);
        renderPopup();
      });

      popupEl.querySelector('.j-dp-next').addEventListener('click', () => {
        visibleMonth.setMonth(visibleMonth.getMonth() + 1);
        renderPopup();
      });

      popupEl.querySelector('.j-dp-today').addEventListener('click', () => {
        selectDate(new Date());
      });

      // Month dropdown toggle
      const monthTrigger = popupEl.querySelector('.j-dp-month-trigger');
      const monthMenu = popupEl.querySelector('.j-dp-month-menu');
      monthTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = monthMenu.style.display !== 'none';
        popupEl.querySelectorAll('.c-dp-header-menu').forEach(m => m.style.display = 'none');
        monthMenu.style.display = isOpen ? 'none' : 'block';
      });

      monthMenu.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-month]');
        if (btn) {
          visibleMonth.setMonth(parseInt(btn.getAttribute('data-month'), 10));
          renderPopup();
        }
      });

      // Year dropdown toggle
      const yearTrigger = popupEl.querySelector('.j-dp-year-trigger');
      const yearMenu = popupEl.querySelector('.j-dp-year-menu');
      yearTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = yearMenu.style.display !== 'none';
        popupEl.querySelectorAll('.c-dp-header-menu').forEach(m => m.style.display = 'none');
        yearMenu.style.display = isOpen ? 'none' : 'block';
        if (!isOpen) {
          const selectedYearBtn = yearMenu.querySelector('.is-selected');
          if (selectedYearBtn) selectedYearBtn.scrollIntoView({ block: 'center' });
        }
      });

      yearMenu.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-year]');
        if (btn) {
          visibleMonth.setFullYear(parseInt(btn.getAttribute('data-year'), 10));
          renderPopup();
        }
      });

      // Day clicks
      popupEl.querySelector('.c-dp-days').addEventListener('click', (e) => {
        const dayBtn = e.target.closest('.j-dp-day-btn');
        if (dayBtn) {
          const iso = dayBtn.getAttribute('data-iso');
          if (iso) selectDate(parseDate(iso));
        }
      });

      // Portal calendar to document.body with position:fixed to escape overflow:hidden ancestors
      document.body.appendChild(popupEl);

      // Position calendar below the trigger using fixed coordinates
      const triggerRect = trigger.getBoundingClientRect();
      const calW = popupEl.offsetWidth || 320;
      const vw = window.innerWidth || document.documentElement.clientWidth;
      const vh = window.innerHeight || document.documentElement.clientHeight;

      // Preferred: align left edge to trigger left
      let calLeft = triggerRect.left;
      let calTop  = triggerRect.bottom + 6;

      // Flip right if overflow right
      if (calLeft + calW > vw - 8) {
        calLeft = triggerRect.right - calW;
      }
      // Clamp to left edge
      if (calLeft < 8) calLeft = 8;

      // Flip up if calendar would overflow below viewport
      if (calTop + 400 > vh) {
        calTop = triggerRect.top - 6 - (popupEl.offsetHeight || 370);
      }

      popupEl.style.position = 'fixed';
      popupEl.style.top   = calTop + 'px';
      popupEl.style.left  = calLeft + 'px';
      popupEl.style.zIndex = '9999';

      document.addEventListener('mousedown', onOutsideClick, true);
      document.addEventListener('keydown', onKeyDown, true);
    }

    trigger.addEventListener('click', () => {
      if (popupEl) {
        closePopup();
      } else {
        renderPopup();
      }
    });

    updateTriggerLabel();
  }

  // Auto-init on DOM ready
  function initAll() {
    document.querySelectorAll('.c-datepicker').forEach(initDatePicker);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  window.initDatePicker = initDatePicker;
  window.initAllDatePickers = initAll;
})();
