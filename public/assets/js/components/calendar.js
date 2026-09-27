/**
 * Shared Calendar Component Logic
 * Supports full interactive calendar with role-aware permissions:
 * - canAddEvent = true: Admin, Teacher, Management (edit/delete events, dashed Add Event button, Event Editor Modal)
 * - canAddEvent = false: Student, Parent (view-only cards, Day Schedule "View all" modal)
 *
 * Persisted via REST JSON endpoints:
 *   POST /<role>/addCalendarEvent
 *   POST /<role>/updateCalendarEvent
 *   POST /<role>/deleteCalendarEvent
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

    // Role and Scope metadata
    let apiRole = calendarEl.dataset.role || '';
    if (!apiRole || !['admin', 'teacher', 'management', 'student', 'parent'].includes(apiRole.toLowerCase())) {
      const path = window.location.pathname.toLowerCase();
      if (path.includes('/admin')) apiRole = 'admin';
      else if (path.includes('/management')) apiRole = 'management';
      else if (path.includes('/teacher')) apiRole = 'teacher';
      else if (path.includes('/student')) apiRole = 'student';
      else if (path.includes('/parent')) apiRole = 'parent';
      else apiRole = 'teacher';
    }
    apiRole = apiRole.toLowerCase();

    const csrfToken = calendarEl.dataset.csrf || document.querySelector('input[name="_csrf_token"]')?.value || '';
    const defaultScopeType = calendarEl.dataset.scopeType || 'schoolwide';
    const defaultScopeId = calendarEl.dataset.scopeId || '';
    let fixedScope = JSON.parse(calendarEl.dataset.fixedScope || 'null');
    const scopeOptions = JSON.parse(calendarEl.dataset.scopeOptions || '[]');
    let scopeStructure = {};
    try {
      scopeStructure = JSON.parse(calendarEl.dataset.scopeStructure || '{}');
    } catch (e) {
      console.warn('[Calendar] Invalid scopeStructure JSON:', e);
    }

    function populateProgressiveScopeMenus() {
      // 1. Populate Grades
      const gradesMenu = document.querySelector('#j-select-academic-grades .j-dropdown-menu');
      if (gradesMenu && scopeStructure.grades) {
        gradesMenu.innerHTML = scopeStructure.grades.map(g => `
          <button type="button" class="c-select__option c-dropdown__option" data-value="${g.id}">
            <span>${g.name}</span>
          </button>
        `).join('');
      }

      // 2. Populate Grade filter for classes
      const filterGradeMenu = document.querySelector('#j-filter-class-grade .j-dropdown-menu');
      if (filterGradeMenu && scopeStructure.grades) {
        filterGradeMenu.innerHTML = `
          <button type="button" class="c-select__option c-dropdown__option c-is-selected" data-value="all">
            <span>All grades</span>
          </button>
        ` + scopeStructure.grades.map(g => `
          <button type="button" class="c-select__option c-dropdown__option" data-value="${g.id}">
            <span>${g.name}</span>
          </button>
        `).join('');
      }

      // 3. Populate Classes
      const classesMenu = document.querySelector('#j-select-academic-classes .j-dropdown-menu');
      if (classesMenu && scopeStructure.classes) {
        classesMenu.innerHTML = scopeStructure.classes.map(c => `
          <button type="button" class="c-select__option c-dropdown__option" data-value="${c.id}" data-grade="${c.grade_id}">
            <span>${c.name}</span>
          </button>
        `).join('');
      }

      // 4. Populate Extracurriculars
      const extraMenu = document.querySelector('#j-select-extracurricular .j-dropdown-menu');
      if (extraMenu) {
        let html = '';
        if (scopeStructure.clubs && scopeStructure.clubs.length > 0) {
          html += `<div style="padding: 6px 12px; font-size: 11px; font-weight: 700; color: #EA8913; text-transform: uppercase; letter-spacing: 0.05em;">Clubs & Societies</div>`;
          html += scopeStructure.clubs.map(cl => `
            <button type="button" class="c-select__option c-dropdown__option" data-value="club:${cl.id}">
              <span>${cl.name}</span>
            </button>
          `).join('');
        }
        if (scopeStructure.sports && scopeStructure.sports.length > 0) {
          html += `<div style="padding: 6px 12px; font-size: 11px; font-weight: 700; color: #EA8913; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px;">Sports Programs</div>`;
          html += scopeStructure.sports.map(sp => `
            <button type="button" class="c-select__option c-dropdown__option" data-value="sport:${sp.id}">
              <span>${sp.name}</span>
            </button>
          `).join('');
        }
        extraMenu.innerHTML = html;
      }

      // Role restriction: If teacher cannot create whole-grade events:
      if (scopeStructure.can_grade === false) {
        const gradeRadio = document.getElementById('j-academic-level-grade')?.closest('label');
        if (gradeRadio) gradeRadio.style.display = 'none';
        const classRadio = document.getElementById('j-academic-level-class');
        if (classRadio) classRadio.checked = true;
        const gradesCont = document.getElementById('j-academic-grades-container');
        if (gradesCont) gradesCont.style.display = 'none';
        const classesCont = document.getElementById('j-academic-classes-container');
        if (classesCont) classesCont.style.display = 'block';
      }
    }

    function updateProgressiveScopeUI(category) {
      const academicSec = document.getElementById('j-scope-academic-section');
      const extraSec = document.getElementById('j-scope-extracurricular-section');

      if (category === 'Academic') {
        if (academicSec) academicSec.style.display = 'block';
        if (extraSec) extraSec.style.display = 'none';
        if (window.setDropdownMultiValues) {
          window.setDropdownMultiValues('j-field-audience', ['Students', 'Parents']);
        }
      } else if (category === 'Extracurricular') {
        if (academicSec) academicSec.style.display = 'none';
        if (extraSec) extraSec.style.display = 'block';
        if (window.setDropdownMultiValues) {
          window.setDropdownMultiValues('j-field-audience', ['Students', 'Parents']);
        }
      } else {
        // General / School-wide
        if (academicSec) academicSec.style.display = 'none';
        if (extraSec) extraSec.style.display = 'none';
        if (window.setDropdownMultiValues) {
          window.setDropdownMultiValues('j-field-audience', ['All']);
        }
      }
    }

    // Wire progressive listeners once
    document.getElementById('j-field-category')?.addEventListener('dropdown:change', (e) => {
      const cat = e.detail?.value || 'General';
      updateProgressiveScopeUI(cat);
    });

    document.querySelectorAll('input[name="academic_level"]').forEach(radio => {
      radio.addEventListener('change', () => {
        const isGrade = document.getElementById('j-academic-level-grade')?.checked;
        const gradesCont = document.getElementById('j-academic-grades-container');
        const classesCont = document.getElementById('j-academic-classes-container');
        if (gradesCont) gradesCont.style.display = isGrade ? 'block' : 'none';
        if (classesCont) classesCont.style.display = isGrade ? 'none' : 'block';
      });
    });

    document.getElementById('j-filter-class-grade')?.addEventListener('dropdown:change', (e) => {
      const selectedGrade = e.detail?.value || 'all';
      const classOptions = document.querySelectorAll('#j-select-academic-classes .j-dropdown-menu .c-select__option');
      classOptions.forEach(opt => {
        const optGrade = opt.dataset.grade;
        if (selectedGrade === 'all' || optGrade === selectedGrade) {
          opt.style.display = 'flex';
        } else {
          opt.style.display = 'none';
        }
      });
    });

    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function parseDate(str) {
      if (!str) return new Date();
      if (str instanceof Date) return isNaN(str.getTime()) ? new Date() : str;
      const s = String(str).trim();
      const datePart = s.split('T')[0].split(' ')[0];
      const parts = datePart.split('-');
      if (parts.length === 3) {
        const y = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10) - 1;
        const d = parseInt(parts[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
          return new Date(y, m, d);
        }
      }
      const timestamp = Date.parse(s);
      if (!isNaN(timestamp)) {
        return new Date(timestamp);
      }
      return new Date();
    }

    function formatIsoDate(date) {
      if (!date) return '';
      if (typeof date === 'string') {
        if (/^\d{4}-\d{2}-\d{2}$/.test(date)) return date;
        date = parseDate(date);
      }
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }

    function resolveAudienceBadgeLabel(e) {
      if (e.audienceLabel && e.audienceLabel.trim() && e.audienceLabel !== 'All') return e.audienceLabel;
      const list = Array.isArray(e.audience) ? e.audience : (e.audience ? [e.audience] : ['All']);
      const lower = list.map(a => String(a).toLowerCase());
      if (lower.includes('all')) return 'All School';
      if (lower.includes('students') && lower.includes('parents')) return 'Students & Parents';
      if (lower.includes('teachers') && (lower.includes('management') || lower.length === 1)) return 'Staff Only';
      if (lower.includes('students')) return 'Students Only';
      if (lower.includes('parents')) return 'Parents Only';
      return list.join(', ');
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
            id: String(e.id),
            date: parseDate(e.date || e.event_date),
            time: e.time || e.time_range || '08:30–10:30',
            title: e.title || 'Event',
            details: e.details || '',
            category: e.category || 'Academic',
            scope_type: e.scopeType || e.scope_type || defaultScopeType,
            scope_id: e.scopeId ?? e.scope_id ?? defaultScopeId,
            audience: e.audience || ['All'],
            audienceLabel: resolveAudienceBadgeLabel(e),
            authorRole: e.authorRole || e.author_role || 'admin',
            authorName: e.authorName || e.author_name || 'Admin Office',
            authorLabel: e.authorLabel || ('Posted by ' + (e.authorName || 'Admin Office')),
            created_by_account_id: e.created_by_account_id || 0
          });
        });
      }
    } catch (err) {
      console.warn('[Calendar] Could not parse initial events:', err);
    }

    syncStateToEvents();

    function refetchAndRender() {
      let fetchUrl = `/${apiRole}/getCalendarEvents`;
      if (fixedScope && fixedScope.type && fixedScope.id) {
        fetchUrl += `?scope_type=${encodeURIComponent(fixedScope.type)}&scope_id=${encodeURIComponent(fixedScope.id)}`;
      }

      fetch(fetchUrl, {
        headers: { 'Accept': 'application/json' }
      })
      .then(res => res.json())
      .then(res => {
        if (!res.success) throw new Error(res.error || 'Failed to load events');
        state.calendarEvents = (res.events || []).map(e => ({
          id: String(e.id),
          date: parseDate(e.date || e.event_date),
          time: e.time || '09:00',
          title: e.title || 'Event',
          details: e.details || '',
          category: e.category || 'Academic',
          scope_type: e.scopeType || e.scope_type || defaultScopeType,
          scope_id: e.scopeId ?? e.scope_id ?? defaultScopeId,
          audience: e.audience || ['All'],
          audienceLabel: resolveAudienceBadgeLabel(e),
          authorRole: e.authorRole || e.author_role || 'admin',
          authorName: e.authorName || e.author_name || 'Admin Office',
          authorLabel: e.authorLabel || ('Posted by ' + (e.authorName || 'Admin Office')),
          created_by_account_id: e.created_by_account_id || 0
        }));
        refreshCalendar();
      })
      .catch(err => {
        console.warn('[Calendar] Could not refresh events:', err);
        window.showFeedbackBanner?.('Could not refresh the calendar. Showing last-known data.', 'error');
      });
    }

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

    function selectBestEventDateInMonth(vDate) {
      const eventsInMonth = state.calendarEvents.filter(ev =>
        ev.date.getFullYear() === vDate.getFullYear() &&
        ev.date.getMonth() === vDate.getMonth()
      );
      return eventsInMonth.length > 0 ? eventsInMonth[0].date : null;
    }

    function syncStateToEvents() {
      if (state.calendarEvents.length === 0) return;
      if (!state.selectedDate || isNaN(state.selectedDate.getTime())) {
        state.selectedDate = state.calendarEvents[0].date;
      }
      if (!state.viewDate || isNaN(state.viewDate.getTime())) {
        state.viewDate = startOfMonth(state.selectedDate);
      }
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

    // Event deletion execution
    function executeDeleteEvent(eventId, evTitle) {
      const doDelete = () => {
        fetch(`/${apiRole}/deleteCalendarEvent`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            _csrf_token: csrfToken,
            id: eventId
          })
        })
        .then(res => {
          if (res.status === 401) {
            window.location.reload();
            return null;
          }
          return res.json().then(data => ({ status: res.status, data }));
        })
        .then(result => {
          if (!result) return;
          const { data } = result;
          if (data && data.success) {
            refetchAndRender();
            window.showFeedbackBanner?.('Event deleted.', 'success');
          } else {
            alert(data?.error || 'Failed to delete event.');
          }
        })
        .catch(err => {
          console.error('[Calendar] Error deleting event:', err);
          alert('Network error while deleting event.');
        });
      };

      if (typeof window.openUniversalDeleteModal === 'function') {
        window.openUniversalDeleteModal({
          title: `Delete '${evTitle}'?`,
          description: 'This event will be permanently removed from the school calendar schedule.',
          buttonText: 'Delete Event',
          onConfirm: doDelete
        });
      } else {
        if (confirm(`Delete '${evTitle}'? This event will be permanently removed.`)) {
          doDelete();
        }
      }
    }

    // Render Day Detail
    let currentEditingEvent = null;

    function canUserModify(ev) {
      if (apiRole === 'admin') return true;
      if (apiRole === 'management') return ev.authorRole !== 'admin';
      if (apiRole === 'teacher') return ev.authorRole === 'teacher';
      return false;
    }

    function buildDayEventCardHtml(ev) {
      const canModify = canUserModify(ev);
      const actionButtons = canModify ? `
        <div style="position: absolute; right: 10px; top: 10px; display: flex; gap: 6px; align-items: center;">
          <button type="button" class="j-edit-event-btn" data-event-id="${escapeHtml(ev.id)}" aria-label="Edit event" style="background:none;border:none;cursor:pointer;color:white;padding:3px;">
            <svg class="c-icon" width="13" height="13"><use href="#icon-edit"/></svg>
          </button>
          <button type="button" class="j-delete-event-btn" data-event-id="${escapeHtml(ev.id)}" aria-label="Delete event" style="background:none;border:none;cursor:pointer;color:#ff8888;padding:3px;">
            <svg class="c-icon" width="13" height="13"><use href="#icon-trash"/></svg>
          </button>
        </div>` : '';

      const audBadge = ev.audienceLabel ? `
        <span class="c-day-event-card__badge">${escapeHtml(ev.audienceLabel)}</span>` : '';

      return `
        <article class="c-day-event-card" style="position: relative; cursor: pointer;">
          <div style="display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; margin-bottom: 2px; padding-right: ${canModify ? '52px' : '0'}; overflow: hidden;">
            <p class="c-day-event-card__eyebrow" style="margin: 0; flex-shrink: 0;">${formatMonthDay(state.selectedDate)} · ${escapeHtml(ev.time)}</p>
            ${audBadge}
          </div>
          <h3 class="c-day-event-card__title" title="${escapeHtml(ev.title)}" style="padding-right: ${canModify ? '50px' : '0'};">${escapeHtml(ev.title)}</h3>
          ${ev.details ? `<p class="c-day-event-card__details" title="${escapeHtml(ev.details)}" style="padding-right: ${canModify ? '50px' : '0'};">${escapeHtml(ev.details)}</p>` : ''}
          ${actionButtons}
        </article>`;
    }

    function renderCalendarDayDetail() {
      const countEl = document.getElementById('j-calendar-event-count');
      const detailEl = document.getElementById('j-calendar-day-detail');
      const viewAllBtn = document.getElementById('j-open-day-schedule');
      if (!countEl || !detailEl) return;

      const dayEvents = getEventsOnDate(state.selectedDate);
      dayEvents.sort((a, b) => (a.time || '').localeCompare(b.time || ''));

      countEl.textContent = `${dayEvents.length} event${dayEvents.length === 1 ? '' : 's'} scheduled`;
      if (viewAllBtn) {
        viewAllBtn.style.display = dayEvents.length > 0 ? 'inline-block' : 'none';
      }

      if (canAdd) {
        let eventsHtml = '';
        if (dayEvents.length > 0) {
          eventsHtml = dayEvents.map(buildDayEventCardHtml).join('');
        } else {
          eventsHtml = `
            <div class="c-calendar__empty-box">
              <p class="c-calendar__empty-box-text">No events scheduled for ${formatMonthDay(state.selectedDate)}.</p>
            </div>`;
        }

        detailEl.innerHTML = `
          <div class="c-calendar__day-events">
            ${eventsHtml}
            <button type="button" class="c-calendar__add-event-btn j-open-event-editor-btn" aria-label="Add event">
              <svg width="18" height="18" stroke="${SUNSHINE}"><use href="#icon-plus"/></svg>
              <span>Add event</span>
            </button>
          </div>`;

        detailEl.querySelectorAll('.c-day-event-card').forEach(card => {
          card.addEventListener('click', (e) => {
            if (e.target.closest('.j-edit-event-btn') || e.target.closest('.j-delete-event-btn')) {
              return;
            }
            openDayScheduleModal();
          });
        });

        detailEl.querySelectorAll('.j-edit-event-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.eventId;
            const ev = state.calendarEvents.find(e => String(e.id) === String(id));
            if (ev) openEventEditorModal(ev);
          });
        });

        detailEl.querySelectorAll('.j-delete-event-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.eventId;
            const ev = state.calendarEvents.find(e => String(e.id) === String(id)) || { id, title: 'this event' };
            executeDeleteEvent(id, ev.title);
          });
        });

        detailEl.querySelectorAll('.j-open-event-editor-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openEventEditorModal(null);
          });
        });
      } else {
        // Roles without Add Event (Student, Parent):
        if (dayEvents.length > 0) {
          const eventsHtml = dayEvents.map(buildDayEventCardHtml).join('');
          detailEl.innerHTML = `
            <div class="c-calendar__day-events">
              ${eventsHtml}
              <div class="c-calendar__no-more-events" aria-label="No more events today">
                <svg class="c-icon" width="16" height="16" style="color: rgba(255,255,255,0.6);"><use href="#icon-checkCircle2"/></svg>
                <span>No more events today</span>
              </div>
            </div>`;

          detailEl.querySelectorAll('.c-day-event-card').forEach(card => {
            card.addEventListener('click', () => {
              openDayScheduleModal();
            });
          });
        } else {
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
        const best = selectBestEventDateInMonth(nextView);
        state.selectedDate = best || changeCalendarView(state.selectedDate, nextView);
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
        const best = selectBestEventDateInMonth(nextView);
        state.selectedDate = best || changeCalendarView(state.selectedDate, nextView);
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
      const best = selectBestEventDateInMonth(nextView);
      state.selectedDate = best || changeCalendarView(state.selectedDate, nextView);
      state.viewDate = nextView;
      refreshCalendar();
    });

    document.getElementById('j-calendar-next')?.addEventListener('click', () => {
      const nextView = new Date(state.viewDate.getFullYear(), state.viewDate.getMonth() + 1, 1);
      const best = selectBestEventDateInMonth(nextView);
      state.selectedDate = best || changeCalendarView(state.selectedDate, nextView);
      state.viewDate = nextView;
      refreshCalendar();
    });

    function refreshCalendar() {
      renderCalendarHeaderValues();
      renderCalendarGrid();
      renderCalendarDayDetail();
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
              ${dayEvents.map((event, index) => {
                const canModify = canUserModify(event);
                return `
                <li class="c-day-schedule__item" style="animation-delay:${index * 35}ms">
                  <div class="c-day-schedule__item-top">
                    <div style="min-width: 0; flex: 1;">
                      <p class="c-day-schedule__time">
                        <svg class="c-icon" width="13" height="13"><use href="#icon-clock"/></svg>
                        <span>${escapeHtml(event.time)}</span>
                      </p>
                      <h3 class="c-day-schedule__title" style="word-break: break-word;">${escapeHtml(event.title)}</h3>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                      ${event.category ? `<span class="c-day-schedule__type">${escapeHtml(event.category)}</span>` : ''}
                      ${canModify ? `
                        <button type="button" class="j-modal-edit-btn" data-event-id="${escapeHtml(event.id)}" aria-label="Edit event" style="background:none;border:none;cursor:pointer;color:var(--midnight,#0F414A);opacity:0.7;padding:3px 5px;border-radius:4px;display:inline-flex;align-items:center;">
                          <svg class="c-icon" width="14" height="14"><use href="#icon-edit"/></svg>
                        </button>
                        <button type="button" class="j-modal-delete-btn" data-event-id="${escapeHtml(event.id)}" aria-label="Delete event" style="background:none;border:none;cursor:pointer;color:#AF5031;padding:3px 5px;border-radius:4px;display:inline-flex;align-items:center;">
                          <svg class="c-icon" width="14" height="14"><use href="#icon-trash"/></svg>
                        </button>
                      ` : ''}
                    </div>
                  </div>
                  ${event.details ? `
                    <p class="c-day-schedule__details" style="word-break: break-word; white-space: normal;">
                      <svg class="c-icon c-day-schedule__details-icon" width="13" height="13"><use href="#icon-mapPin"/></svg>
                      <span>${escapeHtml(event.details)}</span>
                    </p>
                  ` : ''}
                  ${(event.audienceLabel || event.authorLabel) ? `
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 8px; padding-top: 6px; border-top: 1px dashed rgba(15, 65, 74, 0.12);">
                      ${event.audienceLabel ? `<span class="c-day-event-card__badge" style="font-size: 10px;">${escapeHtml(event.audienceLabel)}</span>` : ''}
                      ${event.authorLabel ? `<span style="font-size: 11px; color: rgba(15, 65, 74, 0.65);">${escapeHtml(event.authorLabel)}</span>` : ''}
                    </div>
                  ` : ''}
                </li>`;
              }).join('')}
            </ol>`;

          if (canAdd) {
            dayScheduleBody.querySelectorAll('.j-modal-edit-btn').forEach(btn => {
              btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const evId = btn.dataset.eventId;
                const ev = state.calendarEvents.find(e => String(e.id) === String(evId));
                closeModal(dayScheduleModal);
                if (ev) openEventEditorModal(ev);
              });
            });

            dayScheduleBody.querySelectorAll('.j-modal-delete-btn').forEach(btn => {
              btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const evId = btn.dataset.eventId;
                const ev = state.calendarEvents.find(e => String(e.id) === String(evId));
                const evTitle = ev ? ev.title : 'this event';
                closeModal(dayScheduleModal);
                executeDeleteEvent(evId, evTitle);
              });
            });
          }
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
    document.addEventListener('click', (e) => {
      const addBtn = e.target.closest('.j-open-event-editor-btn');
      if (addBtn) {
        e.preventDefault();
        e.stopPropagation();
        openEventEditorModal(null);
      }
    });

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

      const scopeContainer = document.getElementById('j-event-scope-container');
      const scopeSelect = document.getElementById('j-select-scope');
      const scopeBtn = scopeSelect?.querySelector('.j-select-scope-btn');
      const scopeLabel = document.getElementById('j-select-scope-label');
      const scopeMenu = document.getElementById('j-select-scope-menu');
      const hiddenScopeType = document.getElementById('j-calendar-scope-type');
      const hiddenScopeId = document.getElementById('j-calendar-scope-id');

      if (modalEyebrow) modalEyebrow.textContent = formatMonthDayYear(eventToEdit ? (eventToEdit.date || state.selectedDate) : state.selectedDate);

      populateProgressiveScopeMenus();

      if (eventToEdit) {
        if (modalTitle) modalTitle.textContent = 'Edit event';
        if (submitLabel) submitLabel.textContent = 'Save changes';
        if (titleInput) titleInput.value = eventToEdit.title || '';
        if (timeInput) timeInput.value = eventToEdit.time || '';
        if (detailsInput) detailsInput.value = eventToEdit.details || '';

        const editCat = eventToEdit.category || 'General';
        if (window.setDropdownValue) {
          window.setDropdownValue('j-field-category', editCat);
        }
        updateProgressiveScopeUI(editCat);

        // Pre-fill scopes for editing
        const editScopes = eventToEdit.scopes || (eventToEdit.scope_type ? [{ type: eventToEdit.scope_type, id: eventToEdit.scope_id }] : []);
        if (editCat === 'Academic') {
          const hasClasses = editScopes.some(s => s.type === 'class');
          if (hasClasses) {
            const classRadio = document.getElementById('j-academic-level-class');
            if (classRadio) classRadio.checked = true;
            const gradesCont = document.getElementById('j-academic-grades-container');
            const classesCont = document.getElementById('j-academic-classes-container');
            if (gradesCont) gradesCont.style.display = 'none';
            if (classesCont) classesCont.style.display = 'block';
            const classIds = editScopes.filter(s => s.type === 'class').map(s => String(s.id));
            if (window.setDropdownMultiValues) {
              window.setDropdownMultiValues('j-select-academic-classes', classIds);
            }
          } else {
            const gradeRadio = document.getElementById('j-academic-level-grade');
            if (gradeRadio) gradeRadio.checked = true;
            const gradesCont = document.getElementById('j-academic-grades-container');
            const classesCont = document.getElementById('j-academic-classes-container');
            if (gradesCont) gradesCont.style.display = 'block';
            if (classesCont) classesCont.style.display = 'none';
            const gradeIds = editScopes.filter(s => s.type === 'grade').map(s => String(s.id));
            if (window.setDropdownMultiValues) {
              window.setDropdownMultiValues('j-select-academic-grades', gradeIds);
            }
          }
        } else if (editCat === 'Extracurricular') {
          const extraVals = editScopes.filter(s => s.type === 'club' || s.type === 'sport').map(s => `${s.type}:${s.id}`);
          if (window.setDropdownMultiValues) {
            window.setDropdownMultiValues('j-select-extracurricular', extraVals);
          }
        }
      } else {
        if (modalTitle) modalTitle.textContent = 'Add an event';
        if (submitLabel) submitLabel.textContent = 'Save event';
        if (titleInput) titleInput.value = '';
        if (timeInput) timeInput.value = '09:00 AM';
        if (detailsInput) detailsInput.value = '';

        const defaultCat = fixedScope ? (fixedScope.type === 'club' || fixedScope.type === 'sport' ? 'Extracurricular' : 'Academic') : 'General';
        if (window.setDropdownValue) {
          window.setDropdownValue('j-field-category', defaultCat);
        }
        updateProgressiveScopeUI(defaultCat);

        // Reset multi-selects and filters
        if (window.setDropdownMultiValues) {
          window.setDropdownMultiValues('j-select-academic-grades', []);
          window.setDropdownMultiValues('j-select-academic-classes', []);
          window.setDropdownMultiValues('j-select-extracurricular', []);
        }
        if (window.setDropdownValue) {
          window.setDropdownValue('j-filter-class-grade', 'all');
        }
        document.querySelectorAll('#j-select-academic-classes .j-dropdown-menu .c-select__option').forEach(opt => {
          opt.style.display = 'flex';
        });
      }

      // Clear error and warning states
      hideFormError();
      document.getElementById('j-field-title-error')?.classList.remove('c-is-visible');
      document.getElementById('j-field-time-error')?.classList.remove('c-is-visible');

      const warnBanner = document.getElementById('j-event-form-warning-banner');
      if (warnBanner) {
        warnBanner.style.display = 'none';
        warnBanner.textContent = '';
      }
      const parallelBox = document.getElementById('j-parallel-container');
      const parallelCheckbox = document.getElementById('j-field-allow-parallel');
      if (parallelBox) parallelBox.style.display = 'none';
      if (parallelCheckbox) parallelCheckbox.checked = false;

      // Prefill audience
      try {
        let targetAud = ['All'];
        if (eventToEdit && eventToEdit.audience) {
          targetAud = eventToEdit.audience;
        } else if (apiRole === 'teacher') {
          targetAud = ['Students', 'Parents'];
        }
        if (typeof window.setDropdownMultiValues === 'function') {
          window.setDropdownMultiValues('j-field-audience', targetAud);
        } else if (typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-field-audience', targetAud);
        }
      } catch (err) {
        console.warn('[Calendar] Could not prefill audience dropdown:', err);
      }

      openModal(eventEditorModal);
      checkLiveFormClash();
    }

    function showFormError(msg) {
      const banner = document.getElementById('j-event-form-error-banner');
      if (banner) {
        banner.textContent = msg;
        banner.classList.add('c-is-visible');
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }

    function hideFormError() {
      const banner = document.getElementById('j-event-form-error-banner');
      if (banner) {
        banner.classList.remove('c-is-visible');
      }
    }

    function parseTimeToMinutes(tStr) {
      if (!tStr) return null;
      const clean = String(tStr).trim();
      const match = clean.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(am|pm)?$/i);
      if (!match) return null;
      let h = parseInt(match[1], 10);
      const m = parseInt(match[2], 10);
      const meridiem = match[3] ? match[3].toLowerCase() : null;
      if (meridiem === 'pm' && h < 12) h += 12;
      if (meridiem === 'am' && h === 12) h = 0;
      return h * 60 + m;
    }

    function parseTimeRangeMinutes(rawTime) {
      if (!rawTime) return null;
      const parts = String(rawTime).split(/[-–—]/);
      const startMin = parseTimeToMinutes(parts[0]);
      if (startMin === null) return null;
      let endMin = parts[1] ? parseTimeToMinutes(parts[1]) : null;
      if (endMin === null || endMin <= startMin) {
        endMin = startMin + 60; // Default 1 hour duration
      }
      return { start: startMin, end: endMin };
    }

    function checkLiveFormClash() {
      const timeInput = document.getElementById('j-field-time');
      const timeVal = timeInput ? timeInput.value.trim() : '';
      const range = parseTimeRangeMinutes(timeVal);
      const warnBanner = document.getElementById('j-event-form-warning-banner');
      const parallelBox = document.getElementById('j-parallel-container');
      const parallelCheckbox = document.getElementById('j-field-allow-parallel');

      if (!range) {
        if (!parallelCheckbox?.checked && warnBanner) {
          warnBanner.style.display = 'none';
          if (parallelBox) parallelBox.style.display = 'none';
        }
        return null;
      }

      const isEditing = Boolean(currentEditingEvent);
      const activeDateStr = formatIsoDate(isEditing ? (currentEditingEvent.date || state.selectedDate) : state.selectedDate);
      const excludeId = isEditing ? String(currentEditingEvent.id) : null;

      // Find overlapping event on the same date
      const collidingEvent = (state.calendarEvents || []).find(ev => {
        if (excludeId && String(ev.id) === excludeId) return false;
        if (ev.date !== activeDateStr) return false;
        const evRange = parseTimeRangeMinutes(ev.time);
        if (!evRange) return false;
        return range.start < evRange.end && range.end > evRange.start;
      });

      if (collidingEvent) {
        if (warnBanner) {
          warnBanner.textContent = `Schedule conflict: '${collidingEvent.title}' is already scheduled for ${collidingEvent.time}. Check 'Allow parallel schedule' below to proceed.`;
          warnBanner.style.display = 'block';
        }
        if (parallelBox) {
          parallelBox.style.display = 'flex';
        }
        return collidingEvent;
      } else {
        if (!parallelCheckbox?.checked) {
          if (warnBanner) warnBanner.style.display = 'none';
          if (parallelBox) parallelBox.style.display = 'none';
        }
        return null;
      }
    }

    // -------------------------------------------------------------------------
    // SCROLLABLE WHEEL TIME PICKER COMPONENT CONTROLLER
    // -------------------------------------------------------------------------
    function initTimePickerComponent() {
      const trigger = document.getElementById('j-time-trigger');
      const timeInput = document.getElementById('j-field-time');
      const popover = document.getElementById('j-time-picker-popover');
      const modeSingle = document.getElementById('j-tp-mode-single');
      const modeRange = document.getElementById('j-tp-mode-range');
      const rangeTabs = document.getElementById('j-tp-range-tabs');
      const tabStart = document.getElementById('j-tp-tab-start');
      const tabEnd = document.getElementById('j-tp-tab-end');
      const preview = document.getElementById('j-tp-preview');
      const btnApply = document.getElementById('j-tp-btn-apply');
      const btnClear = document.getElementById('j-tp-btn-clear');

      if (!trigger || !timeInput || !popover) return;

      let isRange = false;
      let activeSubtab = 'start'; // 'start' | 'end'
      let startTime = { hour: '09', minute: '00', ampm: 'AM' };
      let endTime = { hour: '10', minute: '30', ampm: 'AM' };

      function togglePopover(show) {
        const isShown = (typeof show === 'boolean') ? show : (popover.style.display !== 'none');
        if (isShown) {
          popover.style.display = 'none';
          trigger.setAttribute('aria-expanded', 'false');
        } else {
          popover.style.display = 'block';
          trigger.setAttribute('aria-expanded', 'true');
          syncFromInput();
          updateSelectionUI();
        }
      }

      function parseTimeString(str) {
        if (!str) return null;
        const match = str.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (!match) return null;
        return {
          hour: String(match[1]).padStart(2, '0'),
          minute: String(match[2]).padStart(2, '0'),
          ampm: match[3].toUpperCase()
        };
      }

      function syncFromInput() {
        const val = timeInput.value.trim();
        if (val.includes('–') || val.includes('-')) {
          const parts = val.split(/[-–]/);
          const p1 = parseTimeString(parts[0]);
          const p2 = parseTimeString(parts[1]);
          if (p1) startTime = p1;
          if (p2) endTime = p2;
          setMode(true);
        } else if (val) {
          const p1 = parseTimeString(val);
          if (p1) startTime = p1;
          setMode(false);
        }
      }

      function formatTime(t) {
        return `${t.hour}:${t.minute} ${t.ampm}`;
      }

      function updatePreview() {
        if (!preview) return;
        if (isRange) {
          preview.textContent = `${formatTime(startTime)} – ${formatTime(endTime)}`;
        } else {
          preview.textContent = formatTime(startTime);
        }
      }

      function getCurrentTarget() {
        return (!isRange || activeSubtab === 'start') ? startTime : endTime;
      }

      function updateSelectionUI() {
        const cur = getCurrentTarget();
        ['hour', 'minute', 'ampm'].forEach(col => {
          const wheel = popover.querySelector(`.j-tp-wheel-${col}`);
          if (!wheel) return;
          const targetVal = (col === 'hour') ? cur.hour : ((col === 'minute') ? cur.minute : cur.ampm);
          wheel.querySelectorAll('.c-tp-item').forEach(item => {
            const isMatch = (item.dataset.val === targetVal);
            item.classList.toggle('is-selected', isMatch);
            if (isMatch) {
              item.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
            }
          });
        });
        updatePreview();
      }

      function setMode(range) {
        isRange = range;
        if (isRange) {
          modeRange?.classList.add('is-active');
          modeSingle?.classList.remove('is-active');
          if (rangeTabs) rangeTabs.style.display = 'flex';
        } else {
          modeSingle?.classList.add('is-active');
          modeRange?.classList.remove('is-active');
          if (rangeTabs) rangeTabs.style.display = 'none';
        }
        updateSelectionUI();
      }

      modeSingle?.addEventListener('click', (e) => { e.stopPropagation(); setMode(false); });
      modeRange?.addEventListener('click', (e) => { e.stopPropagation(); setMode(true); });

      tabStart?.addEventListener('click', (e) => {
        e.stopPropagation();
        activeSubtab = 'start';
        tabStart.classList.add('is-active');
        tabEnd?.classList.remove('is-active');
        updateSelectionUI();
      });

      tabEnd?.addEventListener('click', (e) => {
        e.stopPropagation();
        activeSubtab = 'end';
        tabEnd.classList.add('is-active');
        tabStart?.classList.remove('is-active');
        updateSelectionUI();
      });

      popover.querySelectorAll('.c-tp-item').forEach(item => {
        item.addEventListener('click', (e) => {
          e.stopPropagation();
          const col = item.closest('.c-tp-wheel')?.dataset.col;
          const val = item.dataset.val;
          const cur = getCurrentTarget();
          if (col === 'hour') cur.hour = val;
          if (col === 'minute') cur.minute = val;
          if (col === 'ampm') cur.ampm = val;
          updateSelectionUI();
        });
      });

      function stepColumn(col, dir) {
        const wheel = popover.querySelector(`.j-tp-wheel-${col}`);
        if (!wheel) return;
        const items = Array.from(wheel.querySelectorAll('.c-tp-item'));
        const curIdx = items.findIndex(it => it.classList.contains('is-selected'));
        if (curIdx === -1) return;
        let nextIdx = curIdx + dir;
        if (nextIdx < 0) nextIdx = items.length - 1;
        if (nextIdx >= items.length) nextIdx = 0;
        items[nextIdx].click();
      }

      popover.querySelectorAll('.j-tp-arrow-up').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          stepColumn(btn.dataset.col, -1);
        });
      });

      popover.querySelectorAll('.j-tp-arrow-down').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          stepColumn(btn.dataset.col, 1);
        });
      });

      popover.querySelectorAll('.c-tp-wheel').forEach(wheel => {
        wheel.addEventListener('wheel', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const col = wheel.dataset.col;
          stepColumn(col, e.deltaY > 0 ? 1 : -1);
        }, { passive: false });
      });

      btnApply?.addEventListener('click', (e) => {
        e.stopPropagation();
        timeInput.value = preview ? preview.textContent : formatTime(startTime);
        document.getElementById('j-field-time-error')?.classList.remove('c-is-visible');
        timeInput.dispatchEvent(new Event('input', { bubbles: true }));
        timeInput.dispatchEvent(new Event('change', { bubbles: true }));
        togglePopover(true);
      });

      btnClear?.addEventListener('click', (e) => {
        e.stopPropagation();
        timeInput.value = '';
        timeInput.dispatchEvent(new Event('input', { bubbles: true }));
        timeInput.dispatchEvent(new Event('change', { bubbles: true }));
        togglePopover(true);
      });

      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        togglePopover();
      });

      document.addEventListener('click', (e) => {
        if (!popover.contains(e.target) && !trigger.contains(e.target)) {
          popover.style.display = 'none';
          trigger.setAttribute('aria-expanded', 'false');
        }
      });
    }

    initTimePickerComponent();

    // Attach live clash check listeners to time input
    const timeInputEl = document.getElementById('j-field-time');
    timeInputEl?.addEventListener('input', checkLiveFormClash);
    timeInputEl?.addEventListener('change', checkLiveFormClash);

    if (eventForm) {
      eventForm.addEventListener('submit', (e) => {
        e.preventDefault();
        hideFormError();

        const titleInput = document.getElementById('j-field-title');
        const timeInput = document.getElementById('j-field-time');
        const detailsInput = document.getElementById('j-field-details');
        const categoryInput = document.getElementById('j-field-category');

        const title = titleInput ? titleInput.value.trim() : '';
        const time = timeInput ? timeInput.value.trim() : '';
        const details = detailsInput ? detailsInput.value.trim() : '';
        const category = window.getDropdownValue 
          ? (window.getDropdownValue('j-field-category') || 'General')
          : (categoryInput ? categoryInput.value : 'General');

        const audienceVal = window.getDropdownValue 
          ? window.getDropdownValue('j-field-audience')
          : (document.getElementById('j-field-audience')?.value || ['All']);

        const allowParallelVal = Boolean(document.getElementById('j-field-allow-parallel')?.checked);

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

        // Resolve multi-scopes from progressive UI
        let scopes = [];
        if (category === 'Academic') {
          const isGrade = document.getElementById('j-academic-level-grade')?.checked;
          if (isGrade) {
            const selectedGrades = window.getDropdownValue ? window.getDropdownValue('j-select-academic-grades') : [];
            const gradeArr = Array.isArray(selectedGrades) ? selectedGrades : (selectedGrades ? [selectedGrades] : []);
            if (gradeArr.length === 0) {
              showFormError('Please select at least one grade.');
              return;
            }
            scopes = gradeArr.map(g => ({ type: 'grade', id: g }));
          } else {
            const selectedClasses = window.getDropdownValue ? window.getDropdownValue('j-select-academic-classes') : [];
            const classArr = Array.isArray(selectedClasses) ? selectedClasses : (selectedClasses ? [selectedClasses] : []);
            if (classArr.length === 0) {
              showFormError('Please select at least one class.');
              return;
            }
            scopes = classArr.map(c => ({ type: 'class', id: c }));
          }
        } else if (category === 'Extracurricular') {
          if (fixedScope && (fixedScope.type === 'club' || fixedScope.type === 'sport') && fixedScope.id) {
            scopes = [{ type: fixedScope.type, id: fixedScope.id }];
          } else {
            const selectedExtra = window.getDropdownValue ? window.getDropdownValue('j-select-extracurricular') : [];
            const extraArr = Array.isArray(selectedExtra) ? selectedExtra : (selectedExtra ? [selectedExtra] : []);
            if (extraArr.length === 0) {
              showFormError('Please select at least one club or sport.');
              return;
            }
            scopes = extraArr.map(v => {
              const parts = String(v).split(':', 2);
              return { type: parts[0], id: parts[1] || null };
            });
          }
        } else if (fixedScope && fixedScope.type && fixedScope.id) {
          scopes = [{ type: fixedScope.type, id: fixedScope.id }];
        } else {
          // General / School-wide
          scopes = [{ type: 'schoolwide', id: null }];
        }

        if (hasError) {
          showFormError('Complete the highlighted event details before saving.');
          return;
        }

        const submitBtn = eventForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const isEditing = Boolean(currentEditingEvent);
        const endpoint = isEditing ? `/${apiRole}/updateCalendarEvent` : `/${apiRole}/addCalendarEvent`;
        const eventDateStr = formatIsoDate(isEditing ? (currentEditingEvent.date || state.selectedDate) : state.selectedDate);

        const primaryScope = scopes[0] || { type: 'schoolwide', id: null };

        const payload = isEditing ? {
          _csrf_token: csrfToken,
          id: currentEditingEvent.id,
          title: title,
          details: details,
          category: category,
          audience: audienceVal,
          allow_parallel: allowParallelVal,
          scopes: scopes,
          scope_type: primaryScope.type,
          scope_id: primaryScope.id,
          time: time,
          time_range: time
        } : {
          _csrf_token: csrfToken,
          title: title,
          details: details,
          category: category,
          audience: audienceVal,
          allow_parallel: allowParallelVal,
          scopes: scopes,
          scope_type: primaryScope.type,
          scope_id: primaryScope.id,
          date: eventDateStr,
          event_date: eventDateStr,
          time: time,
          time_range: time
        };

        fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(payload)
        })
        .then(res => {
          if (res.status === 401) {
            window.location.reload();
            return null;
          }
          return res.json().then(data => ({ status: res.status, data }));
        })
        .then(result => {
          if (submitBtn) submitBtn.disabled = false;
          if (!result) return;
          const { status, data } = result;

          if (status === 409 || (data && data.clash)) {
            // Soft-Lock: collision detected
            const warnBanner = document.getElementById('j-event-form-warning-banner');
            const parallelBox = document.getElementById('j-parallel-container');
            if (warnBanner) {
              warnBanner.textContent = data?.error || 'A schedule conflict was detected for this time window.';
              warnBanner.style.display = 'block';
              warnBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            if (parallelBox) {
              parallelBox.style.display = 'flex';
            }
            return;
          }

          if (data && data.success) {
            closeModal(eventEditorModal);
            refetchAndRender();
            window.showFeedbackBanner?.('Event saved successfully.', 'success');
          } else {
            showFormError(data?.error || 'Could not save the event. Please try again.');
          }
        })
        .catch(err => {
          if (submitBtn) submitBtn.disabled = false;
          console.error('[Calendar] Error persisting event:', err);
          showFormError('A network error occurred. Please try again.');
        });
      });
    }

    // Expose global scope refresher for dynamic activity tabs
    window.refreshCalendarScope = function(newScope) {
      fixedScope = newScope;
      calendarEl.dataset.fixedScope = JSON.stringify(newScope || null);
      refetchAndRender();
    };

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
