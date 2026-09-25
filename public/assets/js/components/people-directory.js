/**
 * =========================================================================
 * L'ÉCOLE — PEOPLE / USERS DIRECTORY CONTROLLER
 * =========================================================================
 * Client-side controller for the Users Directory organism component.
 * Handles:
 *   - Role Tab Switching (Students, Teachers, Parents, Management Panel)
 *   - Tone & Tint switching per role
 *   - Table Header & Row switching
 *   - Grade selection & Class chip dynamic rendering
 *   - Class context card updates (Class Title, Enrollment, Class Teacher)
 *   - Search & All Activities filter in-line with Class 6-A header
 *   - Multi-field live search filtering (Name, Reg/ID, Email, Phone, Linked Children)
 *   - Activity / Subject / Relation dropdown filters
 *   - Status dropdown component style synchronization
 * =========================================================================
 */

(function () {
  'use strict';

  // Role metadata mapping
  const ROLE_MAP = {
    'Students': {
      roleKey: 'student',
      toneClass: 'c-tone-sky',
      tintClass: 'c-tint-sky',
      searchPlaceholder: 'Search students...'
    },
    'Teachers': {
      roleKey: 'teacher',
      toneClass: 'c-tone-sunshine',
      tintClass: 'c-tint-sunshine',
      searchPlaceholder: 'Search teachers...'
    },
    'Parents': {
      roleKey: 'parent',
      toneClass: 'c-tone-terracotta',
      tintClass: 'c-tint-terracotta',
      searchPlaceholder: 'Search parents...'
    },
    'Management Panel': {
      roleKey: 'management',
      toneClass: 'c-tone-maroon',
      tintClass: 'c-tint-maroon',
      searchPlaceholder: 'Search staff...'
    }
  };

  // State
  let activeTabName = 'Students';
  let activeGradeId = 'g6';
  let activeClassName = '6-A';
  let selectedActivity = 'all';
  let selectedSubject = 'all';
  let selectedRelation = 'all';
  let searchQuery = '';

  // Cached DOM elements
  let panelEl, toolbarEl, countNumberEl, resultSummaryEl;
  let contextBarEl, contextTitleEl, contextEnrollmentEl, contextTeacherEl;
  let classChipsWrapEl;

  function init() {
    panelEl = document.getElementById('j-people-panel');
    if (!panelEl) return;

    toolbarEl = document.getElementById('j-people-toolbar');
    countNumberEl = document.getElementById('j-count-number');
    resultSummaryEl = document.getElementById('j-result-summary');
    contextBarEl = document.getElementById('j-student-context-bar');
    contextTitleEl = document.getElementById('j-context-class-title');
    contextEnrollmentEl = document.getElementById('j-context-enrollment');
    contextTeacherEl = document.getElementById('j-context-teacher');
    classChipsWrapEl = document.getElementById('j-class-chips');

    // Read initial active tab
    const initialActiveTabBtn = document.querySelector('.j-directory-tab.is-active-tab');
    if (initialActiveTabBtn) {
      activeTabName = initialActiveTabBtn.getAttribute('data-tab') || 'Students';
    }

    bindEvents();
    renderClassChips(activeGradeId);
    updateContextCard(activeClassName);
    applyFilters();
  }

  function bindEvents() {
    // 1. Role Tabs in Top-Right Header
    const tablist = document.getElementById('j-directory-tablist');
    if (tablist) {
      tablist.addEventListener('click', function (e) {
        const btn = e.target.closest('.j-directory-tab');
        if (!btn) return;
        const tab = btn.getAttribute('data-tab');
        if (tab && tab !== activeTabName) {
          switchRoleTab(tab);
        }
      });
    }

    // 2. Class Chips
    if (classChipsWrapEl) {
      classChipsWrapEl.addEventListener('click', function (e) {
        const chip = e.target.closest('.j-class-chip');
        if (!chip) return;
        const cls = chip.getAttribute('data-class');
        if (cls && cls !== activeClassName) {
          activeClassName = cls;
          classChipsWrapEl.querySelectorAll('.j-class-chip').forEach(c => c.classList.remove('is-active-chip'));
          chip.classList.add('is-active-chip');
          updateContextCard(activeClassName);
          applyFilters();
        }
      });
    }

    // 3. Grade Dropdown
    const gradeDropdown = document.getElementById('j-select-grade');
    if (gradeDropdown) {
      gradeDropdown.addEventListener('dropdown:change', function (e) {
        const val = e.detail?.value;
        if (val && val !== activeGradeId) {
          activeGradeId = val;
          renderClassChips(activeGradeId);
          applyFilters();
        }
      });
    }

    // 4. Activity Dropdown (Students)
    const activityDropdown = document.getElementById('j-select-activity');
    if (activityDropdown) {
      activityDropdown.addEventListener('dropdown:change', function (e) {
        selectedActivity = (e.detail?.value || 'all').toLowerCase();
        applyFilters();
      });
    }

    // 5. Subject Dropdown (Teachers)
    const subjectDropdown = document.getElementById('j-select-subject');
    if (subjectDropdown) {
      subjectDropdown.addEventListener('dropdown:change', function (e) {
        selectedSubject = (e.detail?.value || 'all').toLowerCase();
        applyFilters();
      });
    }

    // 6. Relation Dropdown (Parents)
    const relationDropdown = document.getElementById('j-select-relation');
    if (relationDropdown) {
      relationDropdown.addEventListener('dropdown:change', function (e) {
        selectedRelation = (e.detail?.value || 'all').toLowerCase();
        applyFilters();
      });
    }

    // 7. Live Search Inputs across all role toolbars & white context section
    document.addEventListener('input', function (e) {
      if (e.target.matches('.j-role-search-input')) {
        searchQuery = e.target.value.trim().toLowerCase();
        applyFilters();
      }
    });

    // 8. Row Status Dropdown style update
    if (panelEl) {
      panelEl.addEventListener('dropdown:change', function (e) {
        const statusWrap = e.target.closest('.c-dropdown--status');
        if (statusWrap) {
          const val = e.detail?.value;
          statusWrap.classList.remove('c-dropdown--status-active', 'c-dropdown--status-deactivated');
          if (val === 'Active') {
            statusWrap.classList.add('c-dropdown--status-active');
          } else {
            statusWrap.classList.add('c-dropdown--status-deactivated');
          }
        }
      });
    }

    // 9. Add Person Form Navigation
    document.addEventListener('click', function (e) {
      const addBtn = e.target.closest('.j-btn-add-account');
      if (addBtn) {
        const role = addBtn.getAttribute('data-role') || 'student';
        openAddPersonForm(role);
        return;
      }

      const backBtn = e.target.closest('.j-nav-back-directory');
      if (backBtn) {
        closeAddPersonForm();
        return;
      }

      // Save Draft button
      const draftBtn = e.target.closest('.j-btn-save-draft');
      if (draftBtn) {
        const form = draftBtn.closest('form');
        if (form) showFormNotice(form, 'Draft saved successfully.', 'success');
        return;
      }
    });

    // 10. Smart Full Name Autofill
    document.addEventListener('input', function (e) {
      if (e.target.matches('.j-autofill-first, .j-autofill-last')) {
        const form = e.target.closest('form');
        if (!form) return;
        const first = form.querySelector('.j-autofill-first')?.value || '';
        const last = form.querySelector('.j-autofill-last')?.value || '';
        const full = form.querySelector('.j-autofill-full');
        if (full) {
          full.value = [first, last].filter(Boolean).join(' ');
        }

        // Institutional email autofill
        const instEmail = form.querySelector('.j-teacher-inst-email, .j-mgmt-inst-email');
        if (instEmail) {
          const f = first.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
          const l = last.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
          if (f && l) {
            instEmail.value = `${f}.${l}@lecole.com`;
          } else if (f || l) {
            instEmail.value = `${f || l}@lecole.com`;
          } else {
            instEmail.value = '';
          }
        }
      }
    });

    // 11. Profile Photo Choose File Button
    document.addEventListener('click', function (e) {
      const chooseBtn = e.target.closest('.j-photo-choose');
      if (chooseBtn) {
        const photoField = chooseBtn.closest('.c-photo-field');
        const fileInput = photoField?.querySelector('.j-photo-input');
        if (fileInput) fileInput.click();
        return;
      }

      // Add qualification row
      const addQualBtn = e.target.closest('.j-qual-add');
      if (addQualBtn) {
        const container = document.getElementById('j-teacher-qual-fields');
        if (container) {
          const newRow = document.createElement('div');
          newRow.className = 'j-qual-row';
          newRow.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr 100px auto; gap: 0.75rem; align-items: end;';
          newRow.innerHTML = `
            <div class="c-form-field">
              <label class="c-form-field-label">Title / Degree</label>
              <input type="text" class="c-form-input j-qual-input" name="qualTitle[]" placeholder="e.g. BSc in Mathematics" />
            </div>
            <div class="c-form-field">
              <label class="c-form-field-label">Institution</label>
              <input type="text" class="c-form-input j-qual-input" name="qualInstitution[]" placeholder="e.g. University of Colombo" />
            </div>
            <div class="c-form-field">
              <label class="c-form-field-label">Year</label>
              <input type="text" class="c-form-input j-qual-input" name="qualYear[]" placeholder="2018" />
            </div>
            <button type="button" class="c-btn-solid-tone c-tone-maroon j-qual-remove" style="margin-bottom: 0.25rem; padding: 0.625rem 0.875rem;">Remove</button>
          `;
          container.insertBefore(newRow, addQualBtn);
        }
        return;
      }

      // Remove qualification row
      const removeQualBtn = e.target.closest('.j-qual-remove');
      if (removeQualBtn) {
        const row = removeQualBtn.closest('.j-qual-row');
        if (row) row.remove();
        return;
      }
    });

    document.addEventListener('change', function (e) {
      if (e.target.matches('.j-photo-input')) {
        const photoField = e.target.closest('.c-photo-field');
        const filenameEl = photoField?.querySelector('.j-photo-filename');
        if (filenameEl) {
          filenameEl.textContent = e.target.files && e.target.files.length > 0 ? e.target.files[0].name : 'No file chosen';
        }
      }
    });

    // 12. Dynamic Class Section Dropdown based on Grade selection in Student form
    const studentGradeDropdown = document.getElementById('j-student-grade');
    if (studentGradeDropdown) {
      studentGradeDropdown.addEventListener('dropdown:change', function (e) {
        const gradeVal = e.detail?.value || 'Grade 6';
        updateStudentFormClasses(gradeVal);
      });
    }

    // 12. Form Submissions
    document.addEventListener('submit', function (e) {
      const form = e.target.closest('.c-form-card');
      if (!form) return;
      e.preventDefault();

      const requiredInputs = form.querySelectorAll('[required]');
      let hasError = false;
      for (const input of requiredInputs) {
        if (!input.value.trim()) {
          hasError = true;
          input.focus();
          break;
        }
      }

      if (hasError) {
        showFormNotice(form, 'Please complete all required fields (*).', 'error');
      } else {
        showFormNotice(form, 'Account created successfully! Redirecting...', 'success');
        setTimeout(function () {
          closeAddPersonForm();
          form.reset();
        }, 1200);
      }
    });
  }

  function openAddPersonForm(role) {
    if (panelEl) panelEl.style.display = 'none';
    const pageHeader = document.querySelector('.c-page-header');
    if (pageHeader) pageHeader.style.display = 'none';

    document.querySelectorAll('.j-page-add-person').forEach(p => p.style.display = 'none');
    const targetPage = document.getElementById('j-page-add-' + role);
    if (targetPage) {
      targetPage.style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  function closeAddPersonForm() {
    document.querySelectorAll('.j-page-add-person').forEach(p => p.style.display = 'none');
    if (panelEl) panelEl.style.display = 'block';
    const pageHeader = document.querySelector('.c-page-header');
    if (pageHeader) pageHeader.style.display = '';
    const tablist = document.getElementById('j-directory-tablist');
    if (tablist) tablist.style.display = 'inline-flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function showFormNotice(form, message, type) {
    const noticeEl = form.querySelector('.j-form-notice');
    if (!noticeEl) return;
    noticeEl.textContent = message;
    noticeEl.style.display = 'block';
    if (type === 'success') {
      noticeEl.style.background = 'rgba(75, 91, 52, 0.12)';
      noticeEl.style.color = 'var(--moss, #4B5B34)';
      noticeEl.style.border = '1px solid rgba(75, 91, 52, 0.25)';
    } else {
      noticeEl.style.background = 'rgba(175, 80, 49, 0.12)';
      noticeEl.style.color = '#c53030';
      noticeEl.style.border = '1px solid rgba(175, 80, 49, 0.25)';
    }
  }

  function updateStudentFormClasses(gradeVal) {
    const gradeNum = gradeVal.replace(/[^0-9]/g, '');
    const classDropdown = document.getElementById('j-student-class');
    if (!classDropdown) return;
    const menuEl = classDropdown.querySelector('.c-dropdown__menu');
    const labelEl = classDropdown.querySelector('.c-dropdown__label');
    const inputEl = classDropdown.querySelector('.j-dropdown-input');

    const sections = (gradeNum === '7' || gradeNum === '9' || gradeNum === '11')
      ? ['A', 'B', 'C']
      : ['A', 'B', 'C', 'D'];

    const newOptions = sections.map(sec => ({
      value: `${gradeNum}-${sec}`,
      label: `${gradeNum}-${sec}`
    }));

    if (menuEl) {
      menuEl.innerHTML = newOptions.map(opt => `
        <button type="button" class="c-dropdown__item" data-value="${opt.value}">${opt.label}</button>
      `).join('');
    }

    const firstOpt = newOptions[0];
    if (firstOpt) {
      if (labelEl) labelEl.textContent = firstOpt.label;
      if (inputEl) inputEl.value = firstOpt.value;
    }
  }

  function switchRoleTab(newTabName) {
    activeTabName = newTabName;
    const config = ROLE_MAP[activeTabName] || ROLE_MAP['Students'];

    // Update tab buttons (Solid Pill Selection)
    document.querySelectorAll('.j-directory-tab').forEach(btn => {
      const isTarget = btn.getAttribute('data-tab') === activeTabName;
      btn.classList.remove('is-active-tab', 'c-tone-sky', 'c-tone-sunshine', 'c-tone-terracotta', 'c-tone-maroon');
      btn.setAttribute('aria-selected', isTarget ? 'true' : 'false');
      if (isTarget) {
        btn.classList.add('is-active-tab', config.toneClass);
      }
    });

    // Update toolbar tint
    if (toolbarEl) {
      toolbarEl.classList.remove('c-tint-sky', 'c-tint-sunshine', 'c-tint-terracotta', 'c-tint-maroon');
      toolbarEl.classList.add(config.tintClass);
    }

    // Show matching role toolbar
    document.querySelectorAll('.j-role-toolbar').forEach(tb => {
      tb.style.display = 'none';
    });
    const targetToolbar = document.getElementById('j-toolbar-' + config.roleKey);
    if (targetToolbar) {
      targetToolbar.style.display = 'flex';
    }

    // Toggle white context bar (Only for Students)
    if (contextBarEl) {
      contextBarEl.style.display = (config.roleKey === 'student') ? 'block' : 'none';
    }

    // Show matching table container
    document.querySelectorAll('.j-table-container').forEach(tc => {
      tc.style.display = 'none';
    });
    const targetTableContainer = document.querySelector('.j-table-container--' + config.roleKey);
    if (targetTableContainer) {
      targetTableContainer.style.display = 'block';
    }

    // Reset search query
    searchQuery = '';
    document.querySelectorAll('.j-role-search-input').forEach(inp => {
      inp.value = '';
    });

    applyFilters();
  }

  function renderClassChips(gradeId) {
    if (!classChipsWrapEl) return;
    const gradesData = window.__PEOPLE_DATA__?.grades || [
      { id: 'g6', name: 'Grade 6', classes: ['6-A', '6-B', '6-C', '6-D'] },
      { id: 'g7', name: 'Grade 7', classes: ['7-A', '7-B', '7-C'] },
      { id: 'g8', name: 'Grade 8', classes: ['8-A', '8-B', '8-C', '8-D'] },
      { id: 'g9', name: 'Grade 9', classes: ['9-A', '9-B', '9-C'] },
      { id: 'g10', name: 'Grade 10', classes: ['10-A', '10-B', '10-C', '10-D'] },
      { id: 'g11', name: 'Grade 11', classes: ['11-A', '11-B', '11-C'] }
    ];

    const targetGrade = gradesData.find(g => g.id === gradeId) || gradesData[0];
    const classes = targetGrade?.classes || [];

    if (!classes.includes(activeClassName) && activeClassName !== 'Unassigned') {
      activeClassName = classes[0] || '';
    }

    const unassignedCount = Array.from(document.querySelectorAll('#j-table-student .j-person-row')).filter(r => {
      const c = r.getAttribute('data-class');
      return !c || c === 'Unassigned';
    }).length;

    let chipsHtml = classes.map(cls => `
      <button type="button" class="c-class-chip j-class-chip ${cls === activeClassName ? 'is-active-chip' : ''}" data-class="${escapeHtml(cls)}">
        ${escapeHtml(cls)}
      </button>
    `).join('');

    chipsHtml += `
      <button type="button" class="c-class-chip c-class-chip--unassigned j-class-chip ${activeClassName === 'Unassigned' ? 'is-active-chip' : ''}" data-class="Unassigned" style="margin-left: 0.5rem; border-color: rgba(127, 3, 3, 0.3); color: var(--maroon, #7F0303);">
        Unassigned <span class="c-badge-pill j-unassigned-badge" style="margin-left: 0.25rem; background: var(--maroon, #7F0303); color: #fff; padding: 1px 6px; border-radius: 10px; font-size: 10px; font-weight: 700;">${unassignedCount}</span>
      </button>
    `;

    classChipsWrapEl.innerHTML = chipsHtml;
    updateContextCard(activeClassName);
  }

  function updateContextCard(className) {
    if (!contextBarEl) return;
    if (className === 'Unassigned') {
      if (contextTitleEl) contextTitleEl.textContent = 'Unassigned Students';
      const unassignedCount = Array.from(document.querySelectorAll('#j-table-student .j-person-row')).filter(r => {
        const c = r.getAttribute('data-class');
        return !c || c === 'Unassigned';
      }).length;
      if (contextEnrollmentEl) contextEnrollmentEl.textContent = `${unassignedCount} unassigned`;
      if (contextTeacherEl) contextTeacherEl.textContent = 'Awaiting class placement';
      return;
    }

    if (contextTitleEl) {
      contextTitleEl.textContent = 'Class ' + className;
    }

    const contextMap = window.__PEOPLE_DATA__?.context || {
      '6-A': { classTeacher: 'James Wilson' },
      '6-B': { classTeacher: 'Sarah Peiris' },
      '7-B': { classTeacher: 'Class teacher assignment pending' },
      '8-C': { classTeacher: 'Class teacher assignment pending' },
      '9-A': { classTeacher: 'Rohan Dias' }
    };

    const enrollMap = window.__PEOPLE_DATA__?.enrollments || {
      '6-A': 30, '6-B': 29, '6-C': 31, '6-D': 30,
      '7-A': 44, '7-B': 43, '7-C': 43,
      '8-A': 35, '8-B': 34, '8-C': 36, '8-D': 35,
      '9-A': 50, '9-B': 49, '9-C': 51,
      '10-A': 40, '10-B': 40, '10-C': 40, '10-D': 40,
      '11-A': 52, '11-B': 51, '11-C': 52
    };

    const count = enrollMap[className] || 30;
    const teacher = contextMap[className]?.classTeacher || 'Class teacher assignment pending';

    if (contextEnrollmentEl) {
      contextEnrollmentEl.textContent = `${count} students`;
    }
    if (contextTeacherEl) {
      contextTeacherEl.textContent = teacher;
    }
  }

  function applyFilters() {
    const config = ROLE_MAP[activeTabName] || ROLE_MAP['Students'];
    const currentRoleKey = config.roleKey;
    const activeTable = document.querySelector('.j-table-container--' + currentRoleKey);
    if (!activeTable) return;

    const rows = activeTable.querySelectorAll('.j-person-row');
    const emptyRow = activeTable.querySelector('.c-empty-row');
    let visibleCount = 0;

    rows.forEach(row => {
      let isVisible = true;

      // 1. Role-specific filters
      if (currentRoleKey === 'student') {
        const rowGrade = row.getAttribute('data-grade');
        const rowClass = row.getAttribute('data-class');
        const rowActivities = (row.getAttribute('data-activities') || '').toLowerCase();

        if (activeClassName === 'Unassigned') {
          // Match any unassigned student
          if (rowClass && rowClass !== 'Unassigned') {
            isVisible = false;
          }
        } else {
          if (activeGradeId && rowGrade !== activeGradeId) {
            isVisible = false;
          }
          if (isVisible && activeClassName && rowClass !== activeClassName) {
            isVisible = false;
          }
        }

        if (isVisible && selectedActivity !== 'all' && !rowActivities.includes(selectedActivity)) {
          isVisible = false;
        }
      } else if (currentRoleKey === 'teacher') {
        const rowSubject = (row.getAttribute('data-subject') || '').toLowerCase();
        if (selectedSubject !== 'all' && rowSubject !== selectedSubject) {
          isVisible = false;
        }
      } else if (currentRoleKey === 'parent') {
        const rowRelation = (row.getAttribute('data-relation') || '').toLowerCase();
        if (selectedRelation !== 'all' && rowRelation !== selectedRelation) {
          isVisible = false;
        }
      }

      // 2. Live search query match
      if (isVisible && searchQuery) {
        const rowText = row.textContent.toLowerCase();
        const rowId = (row.getAttribute('data-id') || '').toLowerCase();
        if (!rowText.includes(searchQuery) && !rowId.includes(searchQuery)) {
          isVisible = false;
        }
      }

      row.style.display = isVisible ? '' : 'none';
      if (isVisible) visibleCount++;
    });

    if (resultSummaryEl) {
      let roleUnit = 'users';
      if (currentRoleKey === 'student') roleUnit = visibleCount === 1 ? 'student' : 'students';
      else if (currentRoleKey === 'teacher') roleUnit = visibleCount === 1 ? 'teacher' : 'teachers';
      else if (currentRoleKey === 'parent') roleUnit = visibleCount === 1 ? 'parent account' : 'parent accounts';
      else if (currentRoleKey === 'management') roleUnit = visibleCount === 1 ? 'staff member' : 'staff members';
      resultSummaryEl.innerHTML = `<span id="j-count-number">${visibleCount}</span> ${roleUnit} found`;
    } else if (countNumberEl) {
      countNumberEl.textContent = visibleCount;
    }

    if (emptyRow) {
      emptyRow.style.display = visibleCount === 0 ? 'table-row' : 'none';
    }
  }

  const escapeHtml = window.escapeHtml || function (str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  // Initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
