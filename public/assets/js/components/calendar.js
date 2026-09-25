/**
 * Shared Calendar Component Logic
 * Supports full interactive calendar with role-aware permissions:
 * - canAddEvent = true: Admin, Teacher, Management (edit/delete events, dashed Add Event button, Event Editor Modal)
 * - canAddEvent = false: Student, Parent (view-only cards, Day Schedule "View all" modal)
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('j-calendar');
    if (!calendarEl) return;

    // Read brand token for add-event button SVG stroke (mirrors --sunshine in global.css)
    const SUNSHINE = getComputedStyle(document.documentElement).getPropertyValue('--sunshine').trim() || '#EA8913';

    const canAdd = calendarEl.dataset.canAdd === 'true';
    const initialDateStr = calendarEl.dataset.initialDate || '2026-06-17';
    const viewDateStr = calendarEl.dataset.viewDate || '2026-06-01';

    function parseDate(str) {
      const parts = str.split('-');
      if (parts.length === 3) {
        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
      }
      return new Date(2026, 5, 17);
    }

    const MONTH_NAMES = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const WEEKDAY_LABELS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
    const MIN_CALENDAR_YEAR = 2024;
    const MAX_CALENDAR_YEAR = 2028;

    const state = {
      viewDate: parseDate(viewDateStr),
      selectedDate: parseDate(initialDateStr),
      calendarEvents: []
    };

    // Load server-passed events
    try {
      const rawEvents = calendarEl.dataset.events;
      if (rawEvents) {
        const parsed = JSON.parse(rawEvents);
        parsed.forEach(e => {
          state.calendarEvents.push({
            id: e.id || `ev-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`,
            date: parseDate(e.date),
            time: e.time || '08:30–10:30',
            title: e.title || 'Event',
            details: e.details || '',
            category: e.category || 'Academic',
            source: 'initial'
          });
        });
      }
    } catch (err) {
      console.warn('[Calendar] Could not parse initial events:', err);
    }

    // Load shared user-created events from localStorage
    function loadSharedEvents() {
      try {
        const stored = localStorage.getItem('lecole_shared_events');
        if (stored) {
          const parsed = JSON.parse(stored);
          return parsed.map(e => ({
            ...e,
            date: new Date(e.date)
          }));
        }
      } catch (err) {}
      return [];
    }

    function saveSharedEvents() {
      try {
        const userEvents = state.calendarEvents.filter(e => e.source === 'user');
        localStorage.setItem('lecole_shared_events', JSON.stringify(userEvents));
      } catch (err) {}
    }

    // Merge shared events without duplicates
    loadSharedEvents().forEach(sharedEv => {
      if (!state.calendarEvents.some(e => e.id === sharedEv.id)) {
        state.calendarEvents.push(sharedEv);
      }
    });

    // Date math helpers
    function sameCalendarDay(a, b) {
      return a.getFullYear() === b.getFullYear() &&
             a.getMonth() === b.getMonth() &&
             a.getDate() === b.getDate();
    }
    function startOfMonth(date) {
      return new Date(date.getFullYear(), date.getMonth(), 1);
    }
    function daysInMonth(date) {
      return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    }
    function formatLongDate(date) {
      const w = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      return `${w[date.getDay()]}, ${MONTH_NAMES[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
    }
    function formatMonthDay(date) {
      return `${MONTH_NAMES[date.getMonth()]} ${date.getDate()}`;
    }
    function formatMonthDayYear(date) {
      return `${MONTH_NAMES[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
    }
    function changeCalendarView(cur, next) {
      const norm = startOfMonth(next);
      return new Date(norm.getFullYear(), norm.getMonth(), Math.min(cur.getDate(), daysInMonth(norm)));
    }

    function getEventsOnDate(date) {
      return state.calendarEvents.filter(ev => sameCalendarDay(ev.date, date));
    }

    // Render Weekday Header
    const weekdaysEl = document.getElementById('j-calendar-weekdays');
    if (weekdaysEl) {
      weekdaysEl.innerHTML = WEEKDAY_LABELS.map(day => `<span class="c-calendar__weekday">${day}</span>`).join('');
    }

    // Render Calendar Grid
    function renderCalendarGrid() {
      const daysEl = document.getElementById('j-calendar-days');
      if (!daysEl) return;

      const monthStart = startOfMonth(state.viewDate);
      const weekStartsOn = 1; // Monday
      const leadingBlanks = (monthStart.getDay() - weekStartsOn + 7) % 7;
      const totalDays = daysInMonth(state.viewDate);
      const dateCells = Array.from({ length: totalDays }, (_, i) => new Date(monthStart.getFullYear(), monthStart.getMonth(), i + 1));
      const neededRows = Math.ceil((leadingBlanks + totalDays) / 7);
      const cellCount = neededRows * 7;
      const finalTrailing = cellCount - leadingBlanks - dateCells.length;

      const cells = [
        ...Array.from({ length: leadingBlanks }, () => null),
        ...dateCells,
        ...Array.from({ length: finalTrailing }, () => null)
      ];

      daysEl.innerHTML = '';
      cells.forEach(date => {
        if (!date) {
          const blank = document.createElement('span');
          blank.className = 'c-calendar__day-blank';
          blank.setAttribute('aria-hidden', 'true');
          daysEl.appendChild(blank);
          return;
        }

        const dayEvents = getEventsOnDate(date);
        const hasEvents = dayEvents.length > 0;
        const isSelected = sameCalendarDay(state.selectedDate, date);
        const dateLabel = formatMonthDayYear(date);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'c-calendar__day';
        if (hasEvents) btn.classList.add('c-has-events');
        if (isSelected) btn.classList.add('c-is-selected');
        btn.setAttribute('aria-pressed', String(isSelected));
        btn.setAttribute('aria-label', hasEvents
          ? `View ${dayEvents.length} ${dayEvents.length === 1 ? 'event' : 'events'} for ${dateLabel}`
          : `View ${dateLabel}, no events scheduled`);
        btn.innerHTML = `<span style="line-height:1">${date.getDate()}</span>`;
        if (hasEvents && !isSelected) {
          btn.innerHTML += '<span class="c-calendar__day-dot" aria-hidden="true"></span>';
        }

        btn.addEventListener('click', () => {
          state.selectedDate = date;
          renderCalendarGrid();
          renderCalendarDayDetail();
        });

        daysEl.appendChild(btn);
      });
    }

    // Render Day Detail
    let currentEditingEvent = null;

    function renderCalendarDayDetail() {
      const countEl = document.getElementById('j-calendar-event-count');
      const detailEl = document.getElementById('j-calendar-day-detail');
      if (!countEl || !detailEl) return;

      const dayEvents = getEventsOnDate(state.selectedDate);
      countEl.textContent = `${dayEvents.length} event${dayEvents.length === 1 ? '' : 's'} scheduled`;

      if (canAdd) {
        // Roles with Add Event (Admin, Teacher, Management): ALWAYS TWO BOXES of identical height!
        if (dayEvents.length) {
          // Exactly 1 event card shown above Add event box (remaining in View all)
          const recentEvent = dayEvents[dayEvents.length - 1];
          detailEl.innerHTML = `
            <div class="c-calendar__day-events">
              <article class="c-day-event-card">
                <p class="c-day-event-card__eyebrow">${formatMonthDay(state.selectedDate)} · ${recentEvent.time}</p>
                <h3 class="c-day-event-card__title">${recentEvent.title}</h3>
                <p class="c-day-event-card__details">${recentEvent.details || ''}</p>
                <div style="position: absolute; right: 12px; top: 12px; display: flex; gap: 8px; align-items: center;">
                  <button type="button" class="j-edit-event-btn" data-event-id="${recentEvent.id}" aria-label="Edit event" style="background:none;border:none;cursor:pointer;color:white;padding:4px;">
                    <svg class="c-icon" width="13" height="13"><use href="#icon-edit"/></svg>
                  </button>
                  <button type="button" class="j-delete-event-btn" data-event-id="${recentEvent.id}" aria-label="Delete event" style="background:none;border:none;cursor:pointer;color:#ff8888;padding:4px;">
                    <svg class="c-icon" width="13" height="13"><use href="#icon-trash"/></svg>
                  </button>
                </div>
              </article>
              <button type="button" class="c-calendar__add-event-btn j-open-event-editor-btn" aria-label="Add event">
                <svg width="18" height="18" stroke="${SUNSHINE}"><use href="#icon-plus"/></svg>
                <span>Add event</span>
              </button>
            </div>`;

          detailEl.querySelector('.j-edit-event-btn')?.addEventListener('click', () => {
            const ev = state.calendarEvents.find(e => e.id === recentEvent.id);
            if (ev) openEventEditorModal(ev);
          });

          detailEl.querySelector('.j-delete-event-btn')?.addEventListener('click', () => {
            const ev = state.calendarEvents.find(e => e.id === recentEvent.id) || recentEvent;
            const evTitle = ev.title || 'this event';
            if (typeof window.openUniversalDeleteModal === 'function') {
              window.openUniversalDeleteModal({
                title: `Delete '${evTitle}'?`,
                description: 'This event will be permanently removed from the school calendar schedule.',
                buttonText: 'Delete Event',
                onConfirm: () => {
                  state.calendarEvents = state.calendarEvents.filter(e => e.id !== recentEvent.id);
                  saveSharedEvents();
                  renderCalendarGrid();
                  renderCalendarDayDetail();
                }
              });
            } else {
              state.calendarEvents = state.calendarEvents.filter(e => e.id !== recentEvent.id);
              saveSharedEvents();
              renderCalendarGrid();
              renderCalendarDayDetail();
            }
          });
        } else {
          // No events scheduled: TWO BOXES of identical height: Box 1 says No events, Box 2 is Add event!
          detailEl.innerHTML = `
            <div class="c-calendar__day-events">
              <div class="c-calendar__empty-box">
                <p class="c-calendar__empty-box-text">No events scheduled for ${formatMonthDay(state.selectedDate)}.</p>
              </div>
              <button type="button" class="c-calendar__add-event-btn j-open-event-editor-btn" aria-label="Add event">
                <svg width="18" height="18" stroke="${SUNSHINE}"><use href="#icon-plus"/></svg>
                <span>Add event</span>
              </button>
            </div>`;
        }

        detailEl.querySelector('.j-open-event-editor-btn')?.addEventListener('click', () => {
          openEventEditorModal(null);
        });
      } else {
        // Roles without Add Event (Student, Parent):
        // 2 events max shown; if 1 event: second box is "No more events today"; if 0 events: expanded box fits the whole part
        if (dayEvents.length >= 2) {
          const visibleEvents = dayEvents.slice(0, 2);
          detailEl.innerHTML = `
            <div class="c-calendar__day-events">
              ${visibleEvents.map(ev => `
                <article class="c-day-event-card">
                  <p class="c-day-event-card__eyebrow">${formatMonthDay(state.selectedDate)} · ${ev.time}</p>
                  <h3 class="c-day-event-card__title">${ev.title}</h3>
                  <p class="c-day-event-card__details">${ev.details || ''}</p>
                </article>
              `).join('')}
            </div>`;
        } else if (dayEvents.length === 1) {
          const ev = dayEvents[0];
          detailEl.innerHTML = `
            <div class="c-calendar__day-events">
              <article class="c-day-event-card">
                <p class="c-day-event-card__eyebrow">${formatMonthDay(state.selectedDate)} · ${ev.time}</p>
                <h3 class="c-day-event-card__title">${ev.title}</h3>
                <p class="c-day-event-card__details">${ev.details || ''}</p>
              </article>
              <div class="c-calendar__no-more-events" aria-label="No more events today">
                <svg class="c-icon" width="16" height="16" style="color: rgba(255,255,255,0.6);"><use href="#icon-checkCircle2"/></svg>
                <span>No more events today</span>
              </div>
            </div>`;
        } else {
          // 0 events: Single expanded box spanning the full two-box height
          detailEl.innerHTML = `
            <div class="c-calendar__day-events">
              <div class="c-calendar__empty-box c-calendar__empty-box--expanded">
                <svg class="c-icon" width="22" height="22" style="color: rgba(255,255,255,0.4); margin-bottom: 0.5rem;"><use href="#icon-calendar"/></svg>
                <p style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #fff;">No events scheduled</p>
                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: rgba(255,255,255,0.55);">${formatMonthDay(state.selectedDate)} is clear.</p>
              </div>
            </div>`;
        }
      }

      // Height synchronization: when two boxes exist, ensure both match each other in height
      const boxes = detailEl.querySelectorAll('.c-calendar__day-events > *');
      if (boxes.length === 2) {
        const h0 = boxes[0].offsetHeight;
        const h1 = boxes[1].offsetHeight;
        const maxH = Math.max(h0, h1, 88);
        boxes[0].style.minHeight = `${maxH}px`;
        boxes[1].style.minHeight = `${maxH}px`;
      }
    }

    // Month & Year Selector Components
    function renderCalendarHeaderValues() {
      const curMonth = state.viewDate.getMonth();
      const curYear = state.viewDate.getFullYear();

      const monthValEl = document.querySelector('#j-select-month .j-select-value');
      if (monthValEl) monthValEl.textContent = MONTH_NAMES[curMonth];

      const yearValEl = document.querySelector('#j-select-year .j-select-value');
      if (yearValEl) yearValEl.textContent = String(curYear);

      const prevBtn = document.getElementById('j-calendar-prev');
      const nextBtn = document.getElementById('j-calendar-next');
      if (prevBtn) prevBtn.disabled = (curYear === MIN_CALENDAR_YEAR && curMonth === 0);
      if (nextBtn) nextBtn.disabled = (curYear === MAX_CALENDAR_YEAR && curMonth === 11);
    }

    function setupSelect(rootId, options, currentValueGetter, onChoose) {
      const root = document.getElementById(rootId);
      if (!root) return;
      const trigger = root.querySelector('.c-select__trigger');
      const menu = root.querySelector('.c-select__menu');

      function renderMenu() {
        const curVal = currentValueGetter();
        menu.innerHTML = options.map(opt => `
          <button type="button" class="c-select__option ${opt.value === curVal ? 'c-is-selected' : ''}" data-value="${opt.value}" role="option">
            <span>${opt.label}</span>
          </button>
        `).join('');

        menu.querySelectorAll('.c-select__option').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            onChoose(btn.dataset.value);
            root.classList.remove('c-is-open');
          });
        });
      }

      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = root.classList.contains('c-is-open');
        document.querySelectorAll('.c-select.c-is-open').forEach(s => s.classList.remove('c-is-open'));
        if (!isOpen) {
          renderMenu();
          root.classList.add('c-is-open');
        }
      });
    }

    // Setup Month Dropdown
    setupSelect(
      'j-select-month',
      MONTH_NAMES.map((name, idx) => ({ value: String(idx), label: name })),
      () => String(state.viewDate.getMonth()),
      (newMonthStr) => {
        const nextView = new Date(state.viewDate.getFullYear(), Number(newMonthStr), 1);
        state.selectedDate = changeCalendarView(state.selectedDate, nextView);
        state.viewDate = nextView;
        refreshCalendar();
      }
    );

    // Setup Year Dropdown
    const yearOptions = [];
    for (let yr = MIN_CALENDAR_YEAR; yr <= MAX_CALENDAR_YEAR; yr++) {
      yearOptions.push({ value: String(yr), label: String(yr) });
    }
    setupSelect(
      'j-select-year',
      yearOptions,
      () => String(state.viewDate.getFullYear()),
      (newYearStr) => {
        const nextView = new Date(Number(newYearStr), state.viewDate.getMonth(), 1);
        state.selectedDate = changeCalendarView(state.selectedDate, nextView);
        state.viewDate = nextView;
        refreshCalendar();
      }
    );

    // Close select on outside click
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.c-select')) {
        document.querySelectorAll('.c-select.c-is-open').forEach(s => s.classList.remove('c-is-open'));
      }
    });

    // Prev / Next Month Buttons
    document.getElementById('j-calendar-prev')?.addEventListener('click', () => {
      const nextView = new Date(state.viewDate.getFullYear(), state.viewDate.getMonth() - 1, 1);
      state.selectedDate = changeCalendarView(state.selectedDate, nextView);
      state.viewDate = nextView;
      refreshCalendar();
    });

    document.getElementById('j-calendar-next')?.addEventListener('click', () => {
      const nextView = new Date(state.viewDate.getFullYear(), state.viewDate.getMonth() + 1, 1);
      state.selectedDate = changeCalendarView(state.selectedDate, nextView);
      state.viewDate = nextView;
      refreshCalendar();
    });

    function refreshCalendar() {
      renderCalendarHeaderValues();
      renderCalendarGrid();
      renderCalendarDayDetail();
    }

    // Modal helpers (delegates to universal dialogs-and-popups.js)
    function openModal(modalEl) {
      if (!modalEl) return;
      if (typeof window.openModal === 'function') {
        window.openModal(modalEl);
      } else {
        modalEl.classList.add('c-is-open');
      }
    }
    function closeModal(modalEl) {
      if (!modalEl) return;
      if (typeof window.closeModal === 'function') {
        window.closeModal(modalEl);
      } else {
        modalEl.classList.remove('c-is-open');
      }
    }

    // Wire Day Schedule Modal ("View all")
    const dayScheduleModal = document.getElementById('j-modal-day-schedule');
    const dayScheduleTitle = document.getElementById('j-day-schedule-title');
    const dayScheduleDesc = document.getElementById('j-day-schedule-description');
    const dayScheduleBody = document.getElementById('j-day-schedule-body');

    function openDayScheduleModal() {
      if (!dayScheduleModal) return;
      const dayEvents = getEventsOnDate(state.selectedDate);
      const longDate = formatLongDate(state.selectedDate);

      if (dayScheduleTitle) dayScheduleTitle.textContent = longDate;
      if (dayScheduleDesc) {
        dayScheduleDesc.textContent = dayEvents.length === 1
          ? '1 event is scheduled for this day.'
          : `${dayEvents.length} events are scheduled for this day.`;
      }

      if (dayScheduleBody) {
        if (dayEvents.length) {
          dayScheduleBody.innerHTML = `
            <ol class="c-day-schedule__list">
              ${dayEvents.map((event, index) => `
                <li class="c-day-schedule__item" style="animation-delay:${index * 35}ms">
                  <div class="c-day-schedule__item-top">
                    <div>
                      <p class="c-day-schedule__time">
                        <svg class="c-icon" width="13" height="13"><use href="#icon-clock"/></svg>
                        ${event.time}
                      </p>
                      <h3 class="c-day-schedule__title">${event.title}</h3>
                    </div>
                    ${event.category ? `<span class="c-day-schedule__type">${event.category}</span>` : ''}
                  </div>
                  ${event.details ? `
                    <p class="c-day-schedule__details">
                      <svg class="c-icon c-day-schedule__details-icon" width="13" height="13"><use href="#icon-mapPin"/></svg>
                      <span>${event.details}</span>
                    </p>
                  ` : ''}
                </li>
              `).join('')}
            </ol>`;
        } else {
          dayScheduleBody.innerHTML = `
            <div class="c-day-schedule__empty">
              <span class="c-day-schedule__empty-icon" aria-hidden="true">
                <svg class="c-icon" width="27" height="27"><use href="#icon-calendar"/></svg>
              </span>
              <h3 class="c-day-schedule__empty-title">No events scheduled</h3>
              <p class="c-day-schedule__empty-text">${longDate} is clear.</p>
            </div>`;
        }
      }

      openModal(dayScheduleModal);
    }

    document.getElementById('j-open-day-schedule')?.addEventListener('click', openDayScheduleModal);

    // Wire Event Editor Modal (if user can add events)
    const eventEditorModal = document.getElementById('j-modal-event-editor');
    const eventForm = document.getElementById('j-event-form');

    function openEventEditorModal(eventToEdit) {
      if (!eventEditorModal) return;
      currentEditingEvent = eventToEdit;

      const titleInput = document.getElementById('j-field-title');
      const timeInput = document.getElementById('j-field-time');
      const detailsInput = document.getElementById('j-field-details');
      const categoryInput = document.getElementById('j-field-category');
      const modalEyebrow = document.getElementById('j-event-editor-month');
      const modalTitle = document.getElementById('j-event-editor-title');
      const submitLabel = document.getElementById('j-event-form-submit-label');

      if (modalEyebrow) modalEyebrow.textContent = formatMonthDayYear(state.selectedDate);

      if (eventToEdit) {
        if (modalTitle) modalTitle.textContent = 'Edit event';
        if (submitLabel) submitLabel.textContent = 'Save changes';
        if (titleInput) titleInput.value = eventToEdit.title || '';
        if (timeInput) timeInput.value = eventToEdit.time || '';
        if (detailsInput) detailsInput.value = eventToEdit.details || '';
        if (window.setDropdownValue) {
          window.setDropdownValue('j-field-category', eventToEdit.category || 'Academic');
        } else if (categoryInput) {
          categoryInput.value = eventToEdit.category || 'Academic';
        }
      } else {
        if (modalTitle) modalTitle.textContent = 'Add an event';
        if (submitLabel) submitLabel.textContent = 'Save event';
        if (titleInput) titleInput.value = '';
        if (timeInput) timeInput.value = '09:00 AM';
        if (detailsInput) detailsInput.value = '';
        if (window.setDropdownValue) {
          window.setDropdownValue('j-field-category', 'Academic');
        } else if (categoryInput) {
          categoryInput.value = 'Academic';
        }
      }

      // Clear error states
      document.getElementById('j-event-form-error-banner')?.classList.remove('c-is-visible');
      document.getElementById('j-field-title-error')?.classList.remove('c-is-visible');
      document.getElementById('j-field-time-error')?.classList.remove('c-is-visible');

      openModal(eventEditorModal);
    }

    if (eventForm) {
      eventForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const titleInput = document.getElementById('j-field-title');
        const timeInput = document.getElementById('j-field-time');
        const detailsInput = document.getElementById('j-field-details');
        const categoryInput = document.getElementById('j-field-category');

        const title = titleInput ? titleInput.value.trim() : '';
        const time = timeInput ? timeInput.value.trim() : '';
        const details = detailsInput ? detailsInput.value.trim() : '';
        const category = window.getDropdownValue 
          ? (window.getDropdownValue('j-field-category') || 'Academic')
          : (categoryInput ? categoryInput.value : 'Academic');

        let hasError = false;
        if (!title) {
          document.getElementById('j-field-title-error')?.classList.add('c-is-visible');
          hasError = true;
        } else {
          document.getElementById('j-field-title-error')?.classList.remove('c-is-visible');
        }

        if (!time) {
          document.getElementById('j-field-time-error')?.classList.add('c-is-visible');
          hasError = true;
        } else {
          document.getElementById('j-field-time-error')?.classList.remove('c-is-visible');
        }

        if (hasError) {
          document.getElementById('j-event-form-error-banner')?.classList.add('c-is-visible');
          return;
        }

        if (currentEditingEvent) {
          currentEditingEvent.title = title;
          currentEditingEvent.time = time;
          currentEditingEvent.details = details;
          currentEditingEvent.category = category;
        } else {
          const newEvent = {
            id: `user-event-${Date.now()}`,
            date: new Date(state.selectedDate),
            time,
            title,
            details,
            category,
            source: 'user'
          };
          state.calendarEvents.push(newEvent);
        }

        saveSharedEvents();
        renderCalendarGrid();
        renderCalendarDayDetail();
        closeModal(eventEditorModal);
      });
    }

    // Modal dismissal handlers (close buttons, backdrops, Escape key)
    document.querySelectorAll('.c-modal-layer').forEach(layer => {
      layer.querySelectorAll('.j-modal-close, .j-modal-backdrop').forEach(el => {
        el.addEventListener('click', () => closeModal(layer));
      });
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.c-modal-layer.c-is-open').forEach(closeModal);
      }
    });

    // Initial render
    refreshCalendar();
  });
})();
