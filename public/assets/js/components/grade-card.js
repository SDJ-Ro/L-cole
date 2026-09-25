/**
 * =========================================================================
 * L'ÉCOLE — ACADEMIC MODULE FRONTEND LOGIC
 * =========================================================================
 * Full asynchronous AJAX wiring for Grades, Classes, Teachers & Curriculum.
 * Works seamlessly across both Admin and Management portals.
 * =========================================================================
 */
(function () {
  'use strict';

  // 1. Runtime State & Constants
  let TEACHER_DIRECTORY = Array.isArray(window.LECOLE_STAFF_DIRECTORY) && window.LECOLE_STAFF_DIRECTORY.length > 0
    ? window.LECOLE_STAFF_DIRECTORY
    : [
        {
          id: 'james-wilson', name: 'James Wilson', qualification: 'Science & Chemistry',
          subject: 'Science', classTeacher: '6-A', extras: ['Science Society'],
          subjects: [{ subject: 'Science', classes: ['6-A', '7-B', '8-C'] }], classes: ['6-A', '7-B', '8-C']
        },
        {
          id: 'sarah-peiris', name: 'Sarah Peiris', qualification: 'English Language & Literature',
          subject: 'English', classTeacher: '6-B', extras: ['Debate Society'],
          subjects: [{ subject: 'English', classes: ['6-B', '7-A'] }], classes: ['6-B', '7-A']
        },
        {
          id: 'rohan-dias', name: 'Rohan Dias', qualification: 'Pure Mathematics & Statistics',
          subject: 'Mathematics', classTeacher: '7-B', extras: ['Chess Club'],
          subjects: [{ subject: 'Mathematics', classes: ['9-A', '10-B', '11-C'] }], classes: ['9-A', '10-B', '11-C']
        }
      ];

  const escapeHtml = window.escapeHtml || function (str) {
    return String(str || '').replace(/[&<>"']/g, ch => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[ch]));
  };

  function parseRangeBounds(label) {
    const matches = (label || '').match(/\d+/g);
    if (!matches || matches.length === 0) return null;
    const nums = matches.map(Number);
    return {
      min: nums[0],
      max: nums.length > 1 ? nums[1] : nums[0]
    };
  }

  function getCurriculumSubjectsForGrade(gradeNum) {
    let foundSubjects = [];
    document.querySelectorAll('.c-curriculum-card').forEach(currCard => {
      const bounds = parseRangeBounds(currCard.dataset.range || '');
      if (bounds && gradeNum >= bounds.min && gradeNum <= bounds.max) {
        const chips = Array.from(currCard.querySelectorAll('.c-subject-chip')).map(c => c.textContent.trim());
        chips.forEach(s => {
          if (s && !foundSubjects.includes(s)) foundSubjects.push(s);
        });
      }
    });
    return foundSubjects;
  }

  function syncCurriculumToGradeCards(rangeLabel, subjects) {
    const bounds = parseRangeBounds(rangeLabel);
    if (!bounds) return;

    document.querySelectorAll('.c-grade-card').forEach(gradeCard => {
      const gName = gradeCard.dataset.gradeName || gradeCard.querySelector('.c-grade-card__name')?.textContent || '';
      const numMatch = gName.match(/\d+/);
      if (!numMatch) return;
      const gNum = parseInt(numMatch[0], 10);
      if (gNum >= bounds.min && gNum <= bounds.max) {
        gradeCard.querySelectorAll('.c-class-details').forEach(classDetails => {
          const className = classDetails.dataset.className || classDetails.querySelector('.c-class-row__badge')?.textContent.trim() || '';
          const subjectsList = classDetails.querySelector('.c-class-subjects__list');
          if (!subjectsList) return;

          if (!subjects || subjects.length === 0) {
            subjectsList.innerHTML = '<p class="c-no-subjects-note" style="font-size:11px;color:rgba(15,65,74,0.6);padding:0.5rem 0;">No subjects in curriculum yet. Add subjects to the curriculum stage to assign teachers.</p>';
            return;
          }

          // Remove empty note if present
          subjectsList.querySelector('.c-no-subjects-note')?.remove();

          // Map existing rows
          const existingRows = Array.from(subjectsList.querySelectorAll('.c-subject-assignment-row'));
          const existingMap = new Map();
          existingRows.forEach(row => {
            const sName = row.querySelector('.c-subject-assignment-row__name')?.textContent.trim();
            if (sName) {
              existingMap.set(sName, row);
            }
          });

          // Remove rows not in updated subjects list
          existingMap.forEach((row, sName) => {
            if (!subjects.includes(sName)) {
              row.remove();
            }
          });

          // Append any newly added subjects
          subjects.forEach(subj => {
            if (!existingMap.has(subj)) {
              const rowHtml = `
                <div class="c-subject-assignment-row">
                  <div class="c-subject-assignment-row__name">${escapeHtml(subj)}</div>
                  <div class="c-teacher-field j-subject-teacher-field" data-preview-placement="right" data-class-name="${escapeHtml(className)}" data-subject="${escapeHtml(subj)}">
                    <div style="position: relative;">
                      <button type="button" class="c-teacher-field__trigger j-subject-teacher-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="c-teacher-field__trigger-value j-subject-teacher-trigger-value c-is-placeholder">Assignment pending</span>
                        <svg class="c-icon c-teacher-field__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
                      </button>
                      <div class="c-teacher-field__popover j-subject-teacher-popover"></div>
                    </div>
                  </div>
                </div>`;
              subjectsList.insertAdjacentHTML('beforeend', rowHtml);
              const newField = subjectsList.lastElementChild.querySelector('.j-subject-teacher-field');
              if (newField) {
                wireTeacherField(newField);
              }
            }
          });
        });
      }
    });
  }

  function getApiUrl(action) {
    const role = window.LECOLE_CURRENT_ROLE || (window.location.pathname.includes('/management') ? 'management' : 'admin');
    return `/${role}/${action}`;
  }

  async function apiPost(action, payload) {
    const url = getApiUrl(action);
    const headers = {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    };
    if (window.LECOLE_CSRF_TOKEN) {
      headers['X-CSRF-Token'] = window.LECOLE_CSRF_TOKEN;
    }

    const response = await fetch(url, {
      method: 'POST',
      headers,
      body: JSON.stringify(payload)
    });

    const data = await response.json().catch(() => ({ success: false, error: 'Network communication error.' }));
    return data;
  }

  function showToast(message, type = 'success') {
    let layer = document.getElementById('j-toast-layer');
    if (!layer) {
      layer = document.createElement('div');
      layer.id = 'j-toast-layer';
      layer.className = 'c-toast-layer';
      document.body.appendChild(layer);
    }

    const toast = document.createElement('div');
    toast.className = `c-toast c-toast--${type}`;
    const icon = type === 'success' ? '#icon-check' : '#icon-alertTriangle';
    toast.innerHTML = `
      <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <use href="${icon}"/>
      </svg>
      <span>${escapeHtml(message)}</span>
    `;
    layer.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(8px)';
      setTimeout(() => toast.remove(), 250);
    }, 3800);
  }

  function updateGradeCardStats(gradeCard) {
    if (!gradeCard) return;
    const classRows = gradeCard.querySelectorAll('.c-class-details');
    const classCount = classRows.length;
    let totalStudents = 0;
    classRows.forEach(row => {
      const cnt = parseInt(row.dataset.studentCount || '30', 10);
      totalStudents += isNaN(cnt) ? 30 : cnt;
    });

    const metaEl = gradeCard.querySelector('.c-grade-card__meta');
    if (metaEl) {
      metaEl.innerHTML = `
        <svg class="c-icon" width="14" height="14"><use href="#icon-usersRound"/></svg>
        ${totalStudents.toLocaleString()} students · ${classCount} class${classCount === 1 ? '' : 'es'}
      `;
    }
  }

  // -------------------------------------------------------------------------
  // 1. INLINE CLASS EDITOR & AJAX SUBMISSIONS
  // -------------------------------------------------------------------------
  function createClassFormElement({ gradeId, className = '', studentCount = 30, teacherName = '', mode = 'add', placement = 'right' }) {
    const isEdit = mode === 'edit';
    if (teacherName === 'Assignment pending') teacherName = '';
    const tmpl = document.getElementById('tmpl-grade-class-editor');

    if (tmpl) {
      const clone = tmpl.content.cloneNode(true);
      const form = clone.querySelector('.j-inline-class-form');
      form.dataset.gradeId = gradeId;
      form.dataset.mode = mode;
      if (isEdit) {
        form.dataset.className = className;
        delete form.dataset.draftKey;
      } else {
        form.dataset.draftKey = `${gradeId}-new`;
      }

      const nameInput = form.querySelector('.j-class-name-input');
      if (nameInput) {
        nameInput.value = className;
        nameInput.placeholder = `e.g. ${gradeId.replace('g', '')}-C`;
      }

      const studentsInput = form.querySelector('.j-class-students-input');
      if (studentsInput) {
        studentsInput.value = String(studentCount || 30);
      }

      const teacherField = form.querySelector('.j-teacher-field');
      if (teacherField) {
        teacherField.dataset.previewPlacement = placement;
        teacherField.dataset.currentClass = className || (gradeId.replace('g', '') + '-?');
        const triggerVal = teacherField.querySelector('.j-teacher-trigger-value');
        if (triggerVal) {
          triggerVal.textContent = teacherName || 'Assignment pending';
          triggerVal.classList.toggle('c-is-placeholder', !teacherName);
        }
        const summary = teacherField.querySelector('.j-teacher-summary');
        if (summary) {
          summary.innerHTML = teacherName
            ? `<div class="c-teacher-summary"><div class="c-teacher-summary__head"><svg class="c-icon" width="13" height="13" style="stroke: var(--sky-blue);"><use href="#icon-check"/></svg>${escapeHtml(teacherName)} selected</div></div>`
            : `<p class="c-teacher-field__empty-note">No teacher selected — assignment can remain pending.</p>`;
        }
      }
      return form;
    }

    const fallback = document.createElement('form');
    fallback.className = 'c-inline-editor j-inline-class-form';
    fallback.dataset.gradeId = gradeId;
    fallback.dataset.mode = mode;
    return fallback;
  }

  function initGradeClassEditors() {
    document.addEventListener('click', (e) => {
      // Clicking teacher name/label in class row triggers edit mode
      const teacherNameClick = e.target.closest('.c-class-row__teacher-name, .c-class-row__teacher-label');
      if (teacherNameClick) {
        const row = teacherNameClick.closest('.c-class-row');
        const editBtn = row?.querySelector('.j-edit-class-btn');
        if (editBtn) {
          e.preventDefault();
          e.stopPropagation();
          editBtn.click();
          return;
        }
      }

      // Add class trigger
      const addBtn = e.target.closest('.j-add-class-btn');
      if (addBtn) {
        const gradeCard = addBtn.closest('.c-grade-card');
        const classList = gradeCard.querySelector('.j-class-list');
        if (classList.querySelector('.j-inline-class-form[data-mode="add"]')) return;
        const placement = (Array.from(document.querySelectorAll('.c-grade-card')).indexOf(gradeCard) % 2 === 1) ? 'left' : 'right';
        const form = createClassFormElement({ gradeId: addBtn.dataset.gradeId, mode: 'add', placement });
        if (form) {
          classList.appendChild(form);
          wireTeacherField(form.querySelector('.j-teacher-field'));
          form.querySelector('.j-class-name-input')?.focus();
        }
        return;
      }

      // Cancel button
      const cancelBtn = e.target.closest('.j-cancel-class-btn');
      if (cancelBtn) {
        const form = cancelBtn.closest('.j-inline-class-form');
        if (form) {
          if (form.dataset.mode === 'edit') {
            const prev = form.previousElementSibling;
            if (prev) prev.style.display = '';
          }
          form.remove();
        }
        return;
      }

      // Edit class trigger
      const editBtn = e.target.closest('.j-edit-class-btn');
      if (editBtn) {
        e.stopPropagation();
        const classRow   = editBtn.closest('.c-class-details');
        const gradeCard  = editBtn.closest('.c-grade-card');
        const rawTeacher = classRow.querySelector('.c-class-row__teacher-name')?.textContent.trim() || '';
        const teacherName = rawTeacher === 'Assignment pending' ? '' : rawTeacher;
        const studentCount = parseInt(classRow.dataset.studentCount || '30', 10);
        const placement  = (Array.from(document.querySelectorAll('.c-grade-card')).indexOf(gradeCard) % 2 === 1) ? 'left' : 'right';

        const form = createClassFormElement({
          gradeId: editBtn.dataset.gradeId,
          className: editBtn.dataset.className,
          studentCount,
          teacherName,
          mode: 'edit',
          placement
        });

        if (form) {
          classRow.style.display = 'none';
          classRow.after(form);
          wireTeacherField(form.querySelector('.j-teacher-field'));
          form.querySelector('.j-class-name-input')?.focus();
        }
      }
    });

    // Form submission (Add & Edit)
    document.addEventListener('submit', async (e) => {
      const form = e.target.closest('.j-inline-class-form');
      if (!form) return;
      e.preventDefault();

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.dataset.originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span>Saving...</span>';
      }

      const gradeId       = form.dataset.gradeId;
      const mode          = form.dataset.mode || 'add';
      const isEdit        = mode === 'edit';
      const oldSection    = form.dataset.className || '';
      const secInput      = form.querySelector('.j-class-name-input');
      const countInput    = form.querySelector('.j-class-students-input');
      const secName       = secInput?.value.trim() || '';
      const studentCount  = parseInt(countInput?.value.trim() || '30', 10);
      const triggerVal    = form.querySelector('.j-teacher-trigger-value');
      const rawVal        = triggerVal ? triggerVal.textContent.trim() : '';
      const isPlaceholder = triggerVal?.classList.contains('c-is-placeholder') || !rawVal || rawVal === 'Assignment pending';
      const teacherName   = isPlaceholder ? '' : rawVal;

      if (!secName) {
        if (secInput) secInput.focus();
        if (submitBtn) submitBtn.disabled = false;
        return;
      }

      // Soft validation for min 15 / max 40
      if (studentCount < 15) {
        showToast(`Note: Class ${secName} has ${studentCount} students (under recommended minimum of 15).`, 'error');
      } else if (studentCount > 40) {
        showToast(`Warning: Class ${secName} exceeds maximum capacity (40 students).`, 'error');
      }

      if (isEdit) {
        const payload = {
          grade_id: gradeId,
          old_section_name: oldSection,
          new_section_name: secName,
          student_count: studentCount,
          teacher_name: teacherName
        };

        const res = await apiPost('editClass', payload);
        if (!res.success) {
          showToast(res.error || 'Failed to update class section.', 'error');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.dataset.originalText || 'Save';
          }
          return;
        }

        // Update DOM row
        const originalRow = form.previousElementSibling;
        if (originalRow) {
          originalRow.dataset.className = secName;
          originalRow.dataset.studentCount = String(studentCount);

          const badge = originalRow.querySelector('.c-class-row__badge');
          if (badge) {
            badge.textContent = secName;
            const letter = secName.slice(-1).toUpperCase();
            badge.className = `c-class-row__badge c-tone-${letter.toLowerCase()}`;
          }

          const teacherEl = originalRow.querySelector('.c-class-row__teacher-name');
          if (teacherEl) {
            teacherEl.textContent = teacherName || 'Assignment pending';
            teacherEl.classList.toggle('c-is-pending', !teacherName);
          }

          const editBtn = originalRow.querySelector('.j-edit-class-btn');
          if (editBtn) {
            editBtn.dataset.className = secName;
          }

          // If reassigned from another class, update that class row
          if (res.class?.reassigned_from) {
            const prevRow = document.querySelector(`.c-class-details[data-class-name="${res.class.reassigned_from}"]`);
            if (prevRow) {
              const prevT = prevRow.querySelector('.c-class-row__teacher-name');
              if (prevT) {
                prevT.textContent = 'Assignment pending';
                prevT.classList.add('c-is-pending');
              }
            }
          }

          originalRow.style.display = '';
        }

        // Sync TEACHER_DIRECTORY with new class teacher assignment
        TEACHER_DIRECTORY.forEach(t => {
          if (t.classTeacher === secName || (oldSection && t.classTeacher === oldSection)) {
            t.classTeacher = '';
          }
          if (teacherName && t.name === teacherName) {
            t.classTeacher = secName;
          }
        });
        if (Array.isArray(window.LECOLE_STAFF_DIRECTORY)) {
          window.LECOLE_STAFF_DIRECTORY.forEach(t => {
            if (t.classTeacher === secName || (oldSection && t.classTeacher === oldSection)) {
              t.classTeacher = '';
            }
            if (teacherName && t.name === teacherName) {
              t.classTeacher = secName;
            }
          });
        }

        form.remove();
        updateGradeCardStats(form.closest('.c-grade-card'));
        showToast(`Class ${secName} updated successfully.`);
      } else {
        // Add new class
        const payload = {
          grade_id: gradeId,
          section_name: secName,
          student_count: studentCount,
          teacher_name: teacherName
        };

        const res = await apiPost('addClass', payload);
        if (!res.success) {
          showToast(res.error || 'Failed to add class section.', 'error');
          if (submitBtn) submitBtn.disabled = false;
          return;
        }

        const letter = secName.slice(-1).toUpperCase();
        const toneMap = { A: 'c-tone-a', B: 'c-tone-b', C: 'c-tone-c', D: 'c-tone-d', E: 'c-tone-e' };
        const badgeTone = toneMap[letter] || 'c-tone-default';

        // Extract subjects for this grade from existing sibling class rows or curriculum card
        const gradeCard = form.closest('.c-grade-card');
        const siblingSubjects = Array.from(gradeCard.querySelectorAll('.c-subject-assignment-row__name')).map(el => el.textContent.trim());
        let uniqueSubjects = Array.from(new Set(siblingSubjects));

        if (uniqueSubjects.length === 0) {
          const gName = gradeCard.dataset.gradeName || gradeCard.querySelector('.c-grade-card__name')?.textContent || '';
          const gMatch = gName.match(/\d+/);
          if (gMatch) {
            uniqueSubjects = getCurriculumSubjectsForGrade(parseInt(gMatch[0], 10));
          }
        }

        const subjectsHtml = uniqueSubjects.length > 0
          ? uniqueSubjects.map(subj => `
              <div class="c-subject-assignment-row">
                <div class="c-subject-assignment-row__name">${escapeHtml(subj)}</div>
                <div class="c-teacher-field j-subject-teacher-field" data-preview-placement="right" data-class-name="${escapeHtml(secName)}" data-subject="${escapeHtml(subj)}">
                  <div style="position: relative;">
                    <button type="button" class="c-teacher-field__trigger j-subject-teacher-trigger" aria-haspopup="listbox" aria-expanded="false">
                      <span class="c-teacher-field__trigger-value j-subject-teacher-trigger-value c-is-placeholder">Assignment pending</span>
                      <svg class="c-icon c-teacher-field__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
                    </button>
                    <div class="c-teacher-field__popover j-subject-teacher-popover"></div>
                  </div>
                </div>
              </div>
            `).join('')
          : '<p class="c-no-subjects-note" style="font-size:11px;color:rgba(15,65,74,0.6);padding:0.5rem 0;">No subjects in curriculum yet. Add subjects to the curriculum stage to assign teachers.</p>';

        const newRowHtml = `
          <details class="c-class-details" data-class-name="${escapeHtml(secName)}" data-student-count="${studentCount}">
            <summary class="c-class-row" data-class-name="${escapeHtml(secName)}">
              <span class="c-class-row__badge ${badgeTone}">${escapeHtml(secName)}</span>
              <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
                <div>
                  <p class="c-class-row__teacher-label">Class teacher</p>
                  <p class="c-class-row__teacher-name ${!teacherName ? 'c-is-pending' : ''}" title="Click to assign class teacher">${escapeHtml(teacherName || 'Assignment pending')}</p>
                </div>
                <button type="button" class="c-class-row__edit-btn j-edit-class-btn" data-grade-id="${escapeHtml(gradeId)}" data-class-name="${escapeHtml(secName)}" aria-label="Edit ${escapeHtml(secName)}" style="margin-left: 0.25rem;">
                  <svg class="c-icon" width="14" height="14"><use href="#icon-edit"/></svg>
                </button>
              </div>
              <div style="margin-left:auto;display:flex;align-items:center;gap:0.375rem;color:rgba(15,65,74,0.7);font-size:0.8125rem;font-weight:600;">
                <span>Subject teachers</span>
                <div class="c-class-row__expand-icon" style="display:flex;align-items:center;">
                  <svg class="c-icon" width="16" height="16"><use href="#icon-chevronDown"/></svg>
                </div>
              </div>
            </summary>
            <div class="c-class-subjects">
              <h4 class="c-class-subjects__title">Subject Assignments</h4>
              <div class="c-class-subjects__list">
                ${subjectsHtml}
              </div>
            </div>
          </details>
        `;

        form.insertAdjacentHTML('beforebegin', newRowHtml);
        const newRow = form.previousElementSibling;
        if (newRow) {
          newRow.querySelectorAll('.j-subject-teacher-field').forEach(wireTeacherField);
        }

        // If teacher was reassigned from another class, update that class row
        if (res.class?.reassigned_from) {
          const prevRow = document.querySelector(`.c-class-details[data-class-name="${res.class.reassigned_from}"]`);
          if (prevRow) {
            const prevT = prevRow.querySelector('.c-class-row__teacher-name');
            if (prevT) {
              prevT.textContent = 'Assignment pending';
              prevT.classList.add('c-is-pending');
            }
          }
        }

        if (teacherName) {
          TEACHER_DIRECTORY.forEach(t => {
            if (t.name === teacherName) {
              t.classTeacher = secName;
            }
          });
          if (Array.isArray(window.LECOLE_STAFF_DIRECTORY)) {
            window.LECOLE_STAFF_DIRECTORY.forEach(t => {
              if (t.name === teacherName) {
                t.classTeacher = secName;
              }
            });
          }
        }

        form.remove();
        updateGradeCardStats(gradeCard);
        showToast(`Class ${secName} created successfully.`);
      }
    });
  }

  // -------------------------------------------------------------------------
  // 2. TEACHER FIELD — POPOVER + WORKLOAD HOVER PREVIEW + INLINE CONFLICT WARNING
  // -------------------------------------------------------------------------
  function initTeacherFields() {
    document.querySelectorAll('.j-subject-teacher-field, .j-teacher-field').forEach(field => wireTeacherField(field));

    // Global outside click dismissal
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.c-teacher-field')) {
        document.querySelectorAll('.c-teacher-field.c-is-open').forEach(f => {
          f.classList.remove('c-is-open');
          const trigger = f.querySelector('.j-teacher-trigger, .j-subject-teacher-trigger');
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
      }
    });

    // Global Escape key dismissal
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.c-teacher-field.c-is-open').forEach(f => {
          f.classList.remove('c-is-open');
          const trigger = f.querySelector('.j-teacher-trigger, .j-subject-teacher-trigger');
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
      }
    });
  }

  function getSafeBounds() {
    if (typeof window.getSafeViewportBounds === 'function') {
      return window.getSafeViewportBounds();
    }
    const sidebar = document.querySelector('.c-sidebar');
    let sidebarRight = 0;
    if (sidebar && window.getComputedStyle(sidebar).display !== 'none') {
      sidebarRight = sidebar.getBoundingClientRect().right;
    }
    return {
      minLeft: Math.max(sidebarRight + 16, 16),
      maxRight: window.innerWidth - 16,
      minTop: 16,
      maxBottom: window.innerHeight - 16
    };
  }

  function adjustTeacherPopover(fieldEl, popover) {
    if (!fieldEl || !popover) return;
    popover.style.left = '';
    popover.style.right = '';
    popover.style.top = '';
    popover.style.bottom = '';
    popover.style.marginTop = '';
    popover.style.marginBottom = '';

    const bounds = getSafeBounds();
    const trigger = fieldEl.querySelector('.j-teacher-trigger, .j-subject-teacher-trigger') || fieldEl;
    const triggerRect = trigger.getBoundingClientRect();
    const popoverRect = popover.getBoundingClientRect();

    if (popoverRect.left < bounds.minLeft) {
      popover.style.left = '0';
      popover.style.right = 'auto';
    } else if (popoverRect.right > bounds.maxRight) {
      popover.style.left = 'auto';
      popover.style.right = '0';
    }

    const spaceBelow = bounds.maxBottom - triggerRect.bottom;
    const spaceAbove = triggerRect.top - bounds.minTop;
    if (spaceBelow < 220 && spaceAbove > spaceBelow) {
      popover.style.top = 'auto';
      popover.style.bottom = '100%';
      popover.style.marginTop = '0';
      popover.style.marginBottom = '0.375rem';
    }
  }

  function wireTeacherField(fieldEl) {
    if (!fieldEl || fieldEl.dataset.wired) return;
    fieldEl.dataset.wired = 'true';

    const trigger   = fieldEl.querySelector('.j-teacher-trigger, .j-subject-teacher-trigger');
    const valueSpan = fieldEl.querySelector('.j-teacher-trigger-value, .j-subject-teacher-trigger-value');
    const popover   = fieldEl.querySelector('.j-teacher-popover, .j-subject-teacher-popover');
    const placement = fieldEl.dataset.previewPlacement || 'right';
    const isSubjectField = fieldEl.classList.contains('j-subject-teacher-field');
    const currentClass = fieldEl.dataset.className || fieldEl.dataset.currentClass || '';
    const subjectName = fieldEl.dataset.subject || '';

    if (!trigger || !valueSpan || !popover) return;

    function renderList() {
      const curVal = valueSpan.textContent.trim();
      const isPending = !curVal || curVal === 'Assignment pending' || valueSpan.classList.contains('c-is-placeholder');
      const isClassTeacher = !isSubjectField;

      let html = `<ul class="c-teacher-menu"><li><button type="button" class="c-teacher-menu__item j-opt-teacher${isPending ? ' c-is-selected' : ''}" data-name="" data-id=""><div class="c-teacher-menu__initials" style="background:rgba(15,65,74,0.2);color:var(--midnight);">--</div><div class="c-teacher-menu__item-meta"><span class="c-teacher-menu__item-name">Assignment pending</span><span class="c-teacher-menu__item-status">Leave unassigned</span></div></button></li>`;

      TEACHER_DIRECTORY.forEach(t => {
        const initials = t.name.split(' ').map(w => w[0]).join('').slice(0, 2);
        const isSelected = !isPending && t.name === curVal;
        const isAlreadyAssigned = isClassTeacher && !!t.classTeacher && t.classTeacher !== currentClass;
        const subtitle = isAlreadyAssigned
          ? `Already assigned: Class ${t.classTeacher}`
          : (t.qualification || t.subject || 'Faculty');

        html += `<li><button type="button" class="c-teacher-menu__item j-opt-teacher${isSelected ? ' c-is-selected' : ''}${isAlreadyAssigned ? ' c-is-disabled' : ''}" data-name="${escapeHtml(t.name)}" data-id="${escapeHtml(t.id)}" ${isAlreadyAssigned ? 'disabled title="Already class teacher of Class ' + escapeHtml(t.classTeacher) + '" style="opacity:0.45;cursor:not-allowed;"' : ''}><div class="c-teacher-menu__initials">${escapeHtml(initials)}</div><div class="c-teacher-menu__item-meta"><span class="c-teacher-menu__item-name">${escapeHtml(t.name)}</span><span class="c-teacher-menu__item-status" ${isAlreadyAssigned ? 'style="color:var(--carnation,#e05252);font-weight:600;"' : ''}>${escapeHtml(subtitle)}</span></div></button></li>`;
      });
      html += `</ul><div class="c-workload-preview j-workload-preview c-workload-preview--${placement}"></div>`;
      popover.innerHTML = html;

      const preview = popover.querySelector('.j-workload-preview');

      popover.querySelectorAll('.j-opt-teacher').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (btn.disabled || btn.classList.contains('c-is-disabled')) {
            return;
          }
          const name = btn.dataset.name;
          const prevValue = valueSpan.textContent;
          const prevPending = valueSpan.classList.contains('c-is-placeholder');

          // Optimistic UI update
          valueSpan.textContent = name || 'Assignment pending';
          valueSpan.classList.toggle('c-is-placeholder', !name);
          fieldEl.classList.remove('c-is-open');
          trigger.setAttribute('aria-expanded', 'false');

          const summary = fieldEl.querySelector('.j-teacher-summary');
          if (summary) {
            summary.innerHTML = name
              ? `<div class="c-teacher-summary"><div class="c-teacher-summary__head"><svg class="c-icon" width="13" height="13" style="stroke: var(--sky-blue);"><use href="#icon-check"/></svg>${escapeHtml(name)} selected</div></div>`
              : `<p class="c-teacher-field__empty-note">No teacher selected — assignment can remain pending.</p>`;
          }

          // If this is a standalone subject teacher assignment dropdown on an active class row:
          if (isSubjectField && currentClass && subjectName) {
            const res = await apiPost('assignSubjectTeacher', {
              section_name: currentClass,
              subject_name: subjectName,
              teacher_name: name
            });

            if (!res.success) {
              // Rollback UI
              valueSpan.textContent = prevValue;
              valueSpan.classList.toggle('c-is-placeholder', prevPending);
              showToast(res.error || 'Failed to update subject teacher.', 'error');
            } else {
              showToast(name ? `Assigned ${name} to ${subjectName} in Class ${currentClass}.` : `Subject teacher cleared for ${subjectName}.`);
            }
          }
        });

        // Hover workload preview + conflict warning (Q6 requirement)
        btn.addEventListener('mouseenter', () => {
          const teacher = TEACHER_DIRECTORY.find(t => t.id === btn.dataset.id);
          if (!teacher) {
            preview.classList.remove('c-is-visible');
            return;
          }
          const initials = teacher.name.split(' ').map(w => w[0]).join('').slice(0, 2);

          const hoverTmpl = document.getElementById('tmpl-grade-teacher-hover');
          if (hoverTmpl) {
            const clone = hoverTmpl.content.cloneNode(true);
            const head = clone.querySelector('.c-workload-preview__head');
            const body = clone.querySelector('.c-workload-preview__body');

            const initEl = clone.querySelector('.j-preview-initials');
            if (initEl) initEl.textContent = initials;
            const nameEl = clone.querySelector('.j-preview-name');
            if (nameEl) nameEl.textContent = teacher.name;
            const qualEl = clone.querySelector('.j-preview-qualification, .j-preview-subject');
            if (qualEl) qualEl.textContent = teacher.qualification || teacher.subject || 'Faculty';

            // SECTION 1: Class Teacher
            const ctContainer = clone.querySelector('.j-preview-class-teacher');
            if (ctContainer) {
              ctContainer.innerHTML = teacher.classTeacher
                ? `<span class="c-workload-tag c-workload-tag--class-teacher">Class ${escapeHtml(teacher.classTeacher)}</span>`
                : '<span class="c-workload-tag--empty">Unassigned</span>';
            }

            // SECTION 2: Extracurriculars
            const extrasContainer = clone.querySelector('.j-preview-extras');
            if (extrasContainer) {
              const exList = teacher.extras || teacher.extracurriculars || [];
              extrasContainer.innerHTML = (exList.length > 0)
                ? exList.map(x => `<span class="c-workload-tag c-workload-tag--extra">${escapeHtml(x)}</span>`).join('')
                : '<span class="c-workload-tag--empty">None</span>';
            }

            // SECTION 3: Subject Teacher for
            const subjContainer = clone.querySelector('.j-preview-subjects, .j-preview-classes');
            if (subjContainer) {
              if (teacher.subjects && teacher.subjects.length > 0) {
                subjContainer.innerHTML = teacher.subjects.map(s => {
                  const sName = escapeHtml(s.subject);
                  const classTags = (s.classes && s.classes.length > 0)
                    ? s.classes.map(c => `<span class="c-workload-tag">${escapeHtml(c)}</span>`).join('')
                    : '<span class="c-workload-tag--empty">General</span>';
                  return `<div class="c-workload-subject-item"><span class="c-workload-subject-name">${sName}</span><div class="c-workload-subject-classes">${classTags}</div></div>`;
                }).join('');
              } else {
                subjContainer.innerHTML = '<span class="c-workload-tag--empty">No subjects assigned yet</span>';
              }
            }

            // INLINE CONFLICT WARNING (Q6 requirement)
            // If setting a class teacher, and this teacher is already assigned to a different class:
            if (!isSubjectField && teacher.classTeacher && currentClass && teacher.classTeacher !== currentClass) {
              const conflictNotice = document.createElement('div');
              conflictNotice.className = 'c-workload-conflict-warning';
              conflictNotice.innerHTML = `
                <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                  <line x1="12" y1="9" x2="12" y2="13"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Currently class teacher of <strong>Class ${escapeHtml(teacher.classTeacher)}</strong>. Assigning here will remove them from ${escapeHtml(teacher.classTeacher)}.</span>
              `;
              body.prepend(conflictNotice);
            }

            preview.innerHTML = '';
            preview.appendChild(head);
            preview.appendChild(body);
          }

          // Dynamic positioning away from sidebar
          const bounds = getSafeBounds();
          const popoverRect = popover.getBoundingClientRect();
          const previewWidth = 320;

          preview.classList.remove('c-workload-preview--left', 'c-workload-preview--right', 'c-workload-preview--bottom', 'c-workload-preview--top');
          preview.style.top = '';
          preview.style.bottom = '';
          preview.style.left = '';
          preview.style.right = '';

          const spaceRight = bounds.maxRight - popoverRect.right;
          const spaceLeft  = popoverRect.left - bounds.minLeft;

          if (spaceRight >= previewWidth) {
            preview.classList.add('c-workload-preview--right');
          } else if (spaceLeft >= previewWidth) {
            preview.classList.add('c-workload-preview--left');
          } else {
            const spaceBelow = bounds.maxBottom - popoverRect.bottom;
            if (spaceBelow >= 240) {
              preview.classList.add('c-workload-preview--bottom');
            } else {
              preview.classList.add('c-workload-preview--top');
            }
          }

          preview.classList.add('c-is-visible');

          requestAnimationFrame(() => {
            const previewRect = preview.getBoundingClientRect();
            if (previewRect.bottom > bounds.maxBottom) {
              const shiftY = previewRect.bottom - bounds.maxBottom;
              const currentTop = parseFloat(preview.style.top) || 0;
              preview.style.top = `${currentTop - shiftY}px`;
            }
          });
        });

        btn.addEventListener('mouseleave', () => preview.classList.remove('c-is-visible'));
      });
    }

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = fieldEl.classList.contains('c-is-open');
      document.querySelectorAll('.c-teacher-field.c-is-open').forEach(f => {
        if (f !== fieldEl) {
          f.classList.remove('c-is-open');
          const trig = f.querySelector('.j-teacher-trigger, .j-subject-teacher-trigger');
          if (trig) trig.setAttribute('aria-expanded', 'false');
        }
      });
      if (isOpen) {
        fieldEl.classList.remove('c-is-open');
        trigger.setAttribute('aria-expanded', 'false');
      } else {
        renderList();
        fieldEl.classList.add('c-is-open');
        trigger.setAttribute('aria-expanded', 'true');
        adjustTeacherPopover(fieldEl, popover);
      }
    });
  }

  // -------------------------------------------------------------------------
  // 3. GRADE & CLASS DELETE — DELEGATES TO UNIVERSAL DELETE MODAL
  // -------------------------------------------------------------------------
  function initGradeDeleteTriggers() {
    document.addEventListener('click', (e) => {
      const delGradeBtn = e.target.closest('.j-delete-grade-btn');
      if (delGradeBtn) {
        const gradeCard = delGradeBtn.closest('.c-grade-card');
        const gradeId   = gradeCard.dataset.gradeId || delGradeBtn.dataset.gradeId;
        const gradeName = gradeCard.dataset.gradeName || 'Grade';
        const classes   = Array.from(gradeCard.querySelectorAll('.c-class-details')).map(d => d.dataset.className);
        const gradeWrap = document.getElementById('j-del-grade-pill-wrap');
        const classWrap = document.getElementById('j-del-class-pill-wrap');

        window.openUniversalDeleteModal?.({
          title: `Delete Academic Structures (${escapeHtml(gradeName)})`,
          description: 'Select the entire grade or individual class sections you wish to remove.',
          buttonText: 'Delete Selected',
          customSlotRenderer: (slot, cBtn) => {
            if (cBtn) cBtn.disabled = true;
            if (gradeWrap) {
              gradeWrap.innerHTML = `<button type="button" class="c-btn-plain j-del-pill" data-type="grade" data-grade-id="${escapeHtml(gradeId)}" style="border:1px solid var(--alabaster);border-radius:var(--radius-lg);background:#fff;padding:0.35rem 0.65rem;font-size:0.75rem;font-weight:700;">All of ${escapeHtml(gradeName)}</button>`;
            }
            if (classWrap) {
              classWrap.innerHTML = classes.map(c => `
                <button type="button" class="c-btn-plain j-del-pill" data-type="class" data-class="${escapeHtml(c)}" style="border:1px solid var(--alabaster);border-radius:var(--radius-lg);background:#fff;padding:0.35rem 0.65rem;font-size:0.75rem;font-weight:700;">
                  ${escapeHtml(c)}
                </button>
              `).join('');
            }
          },
          onConfirm: async () => {
            const modal = document.getElementById('j-universal-delete-modal');
            if (!modal) return;

            const isGradeSelected = !!modal.querySelector('.j-del-pill[data-type="grade"].c-is-selected');
            if (isGradeSelected) {
              const res = await apiPost('deleteGrade', { grade_id: gradeId });
              if (!res.success) {
                showToast(res.error || 'Failed to delete grade.', 'error');
                return;
              }
              const removedClasses = res.removedClasses || classes;
              TEACHER_DIRECTORY.forEach(t => {
                if (removedClasses.includes(t.classTeacher)) {
                  t.classTeacher = '';
                }
                if (Array.isArray(t.classes)) {
                  t.classes = t.classes.filter(c => !removedClasses.includes(c));
                }
                if (Array.isArray(t.subjects)) {
                  t.subjects.forEach(s => {
                    if (Array.isArray(s.classes)) {
                      s.classes = s.classes.filter(c => !removedClasses.includes(c));
                    }
                  });
                }
              });
              if (Array.isArray(window.LECOLE_STAFF_DIRECTORY)) {
                window.LECOLE_STAFF_DIRECTORY.forEach(t => {
                  if (removedClasses.includes(t.classTeacher)) {
                    t.classTeacher = '';
                  }
                });
              }
              gradeCard.remove();
              showToast(`${gradeName} and its classes removed successfully.`);
            } else {
              const selectedClasses = Array.from(modal.querySelectorAll('.j-del-pill[data-type="class"].c-is-selected')).map(p => p.dataset.class);
              for (const c of selectedClasses) {
                const res = await apiPost('deleteClass', { section_name: c });
                if (res.success) {
                  TEACHER_DIRECTORY.forEach(t => {
                    if (t.classTeacher === c) {
                      t.classTeacher = '';
                    }
                    if (Array.isArray(t.classes)) {
                      t.classes = t.classes.filter(cls => cls !== c);
                    }
                    if (Array.isArray(t.subjects)) {
                      t.subjects.forEach(s => {
                        if (Array.isArray(s.classes)) {
                          s.classes = s.classes.filter(cls => cls !== c);
                        }
                      });
                    }
                  });
                  if (Array.isArray(window.LECOLE_STAFF_DIRECTORY)) {
                    window.LECOLE_STAFF_DIRECTORY.forEach(t => {
                      if (t.classTeacher === c) {
                        t.classTeacher = '';
                      }
                    });
                  }
                  const row = gradeCard.querySelector(`.c-class-details[data-class-name="${c}"]`);
                  if (row) row.remove();
                } else {
                  showToast(res.error || `Failed to delete class ${c}`, 'error');
                }
              }
              updateGradeCardStats(gradeCard);
              showToast(`Selected class sections removed.`);
            }
          }
        });
        return;
      }

      // Delete curriculum group
      const delCurrBtn = e.target.closest('.j-delete-curriculum-btn');
      if (delCurrBtn) {
        const currCard = delCurrBtn.closest('.c-curriculum-card');
        const range    = delCurrBtn.dataset.range || currCard?.dataset?.range || '';

        window.openUniversalDeleteModal?.({
          title: `Delete ${range} Curriculum Stage?`,
          description: `This stage can only be removed if no active grades depend on it.`,
          buttonText: 'Delete Stage',
          onConfirm: async () => {
            const res = await apiPost('deleteCurriculumGroup', { range_label: range });
            if (!res.success) {
              showToast(res.error || 'Cannot delete curriculum stage.', 'error');
            } else {
              currCard?.remove();
              showToast(`Curriculum stage ${range} deleted.`);
            }
          }
        });
        return;
      }

      // Pill toggle in delete modal
      const pill = e.target.closest('.j-del-pill');
      if (pill) {
        const modal = pill.closest('#j-universal-delete-modal');
        if (!modal) return;

        // If selecting "All of Grade", unselect specific classes and vice-versa
        if (pill.dataset.type === 'grade') {
          modal.querySelectorAll('.j-del-pill[data-type="class"]').forEach(p => {
            p.classList.remove('c-is-selected');
            p.style.background = '#fff';
            p.style.color = '';
          });
        } else {
          const allGradePill = modal.querySelector('.j-del-pill[data-type="grade"]');
          if (allGradePill) {
            allGradePill.classList.remove('c-is-selected');
            allGradePill.style.background = '#fff';
            allGradePill.style.color = '';
          }
        }

        pill.classList.toggle('c-is-selected');
        pill.style.background = pill.classList.contains('c-is-selected') ? '#7f0303' : '#fff';
        pill.style.color      = pill.classList.contains('c-is-selected') ? '#fff' : '';

        const confirmBtn = modal.querySelector('#j-universal-delete-confirm');
        if (confirmBtn) {
          confirmBtn.disabled = modal.querySelectorAll('.j-del-pill.c-is-selected').length === 0;
        }
      }
    });
  }

  // -------------------------------------------------------------------------
  // 4. CURRICULUM INLINE EDITOR & CREATOR (WITH REAL AJAX)
  // -------------------------------------------------------------------------
  function initCurriculumEditors() {
    document.addEventListener('click', async (e) => {
      // 4.1 Add new curriculum stage button
      const addCurrBtn = e.target.closest('.j-add-curriculum-btn');
      if (addCurrBtn) {
        const grid = document.querySelector('.j-curriculum-grid') || document.querySelector('.c-curriculum-grid');
        if (!grid) return;
        if (grid.querySelector('.j-new-curriculum-card')) {
          grid.querySelector('.j-new-curr-range')?.focus();
          return;
        }

        const newCardHtml = `
          <article class="c-curriculum-card j-curriculum-card j-new-curriculum-card" style="border: 2px dashed var(--skyblue, #7FC7CC); background: #fafaf8;">
            <div class="c-curriculum-card__top">
              <div style="width: 100%;">
                <div style="margin-bottom: 0.5rem;">
                  <label class="c-field-label-sm" style="font-size: 10px; margin-bottom: 0.25rem;">Curriculum Stage / Range</label>
                  <input class="c-input-sm j-new-curr-range" placeholder="e.g. Years 12–13" style="font-weight: 700; font-size: 0.875rem;" required />
                </div>
                <div style="margin-bottom: 0.5rem;">
                  <label class="c-field-label-sm" style="font-size: 10px; margin-bottom: 0.25rem;">Description</label>
                  <input class="c-input-sm j-new-curr-desc" placeholder="e.g. Advanced level specialization curriculum." style="font-size: 0.75rem;" />
                </div>
              </div>
            </div>
            <div class="c-curriculum-editor j-curriculum-editor" style="margin-top: 0.75rem;">
              <label class="c-field-label-sm" style="font-size: 10px; margin-bottom: 0.25rem;">Subjects</label>
              <div class="c-curriculum-editor__chips j-curr-chips">
                <p class="c-curr-empty-msg" style="font-size:11px; color:rgba(15,65,74,0.6); margin:0.25rem 0;">No subjects added yet. Type a subject below and click &ldquo;+ Add&rdquo;.</p>
              </div>
              <div class="c-curriculum-editor__add-row" style="margin-top: 0.5rem;">
                <input class="c-curriculum-editor__add-input j-curr-add-input" placeholder="Add subject e.g. Economics" />
                <button type="button" class="c-curriculum-editor__add-btn j-curr-add-btn">+ Add</button>
              </div>
              <div class="c-curriculum-editor__footer" style="margin-top: 0.75rem;">
                <button type="button" class="c-btn-plain j-new-curr-cancel-btn">Cancel</button>
                <button type="button" class="c-btn-save j-new-curr-save-btn">
                  <svg class="c-icon" width="13" height="13"><use href="#icon-check"/></svg>
                  Save Curriculum
                </button>
              </div>
            </div>
          </article>`;
        grid.insertAdjacentHTML('beforeend', newCardHtml);
        grid.querySelector('.j-new-curr-range')?.focus();
        return;
      }

      // 4.2 Save newly created curriculum card
      const saveNewBtn = e.target.closest('.j-new-curr-save-btn');
      if (saveNewBtn) {
        const card = saveNewBtn.closest('.j-new-curriculum-card');
        if (!card) return;
        const rangeInput = card.querySelector('.j-new-curr-range');
        const descInput  = card.querySelector('.j-new-curr-desc');
        const range = rangeInput?.value.trim();
        const desc  = descInput?.value.trim() || '';

        if (!range) {
          rangeInput?.focus();
          rangeInput?.classList.add('c-is-invalid');
          return;
        }

        // Auto-capture any pending typed subject in add-input
        const addInput = card.querySelector('.j-curr-add-input');
        if (addInput && addInput.value.trim()) {
          const val = addInput.value.trim();
          card.querySelector('.c-curr-empty-msg')?.remove();
          card.querySelector('.j-curr-chips').insertAdjacentHTML('beforeend', `
            <span class="c-removable-chip" data-subject="${escapeHtml(val)}">
              ${escapeHtml(val)}
              <button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove ${escapeHtml(val)}"><svg width="10" height="10"><use href="#icon-close"/></svg></button>
            </span>
          `);
          addInput.value = '';
        }

        const chips = Array.from(card.querySelectorAll('.c-removable-chip')).map(c => c.dataset.subject || c.textContent.trim());

        saveNewBtn.disabled = true;
        const res = await apiPost('addCurriculumGroup', {
          range_label: range,
          description: desc,
          subjects: chips
        });

        if (!res.success) {
          showToast(res.error || 'Failed to create curriculum stage.', 'error');
          saveNewBtn.disabled = false;
          return;
        }

        const permanentCard = `
          <article class="c-curriculum-card j-curriculum-card" data-range="${escapeHtml(range)}">
            <div class="c-curriculum-card__top">
              <div>
                <h3 class="c-curriculum-card__range">${escapeHtml(range)}</h3>
                ${desc ? `<p class="c-curriculum-card__desc">${escapeHtml(desc)}</p>` : ''}
              </div>
              <div class="c-curriculum-card__badges">
                <span class="c-curriculum-card__count">${chips.length} Subjects</span>
                <button type="button" class="c-curriculum-card__edit-btn j-edit-curriculum-btn" data-range="${escapeHtml(range)}" aria-label="Edit ${escapeHtml(range)} subjects">
                  <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-edit"/></svg>
                </button>
                <button type="button" class="c-curriculum-card__edit-btn j-delete-curriculum-btn" data-range="${escapeHtml(range)}" aria-label="Delete ${escapeHtml(range)}">
                  <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-trash"/></svg>
                </button>
              </div>
            </div>
            <div class="c-curriculum-card__subjects j-curriculum-subjects">
              ${chips.length > 0
                ? chips.map((s, i) => `<span class="c-subject-chip c-subject-tone-${i % 5}">${escapeHtml(s)}</span>`).join('')
                : '<span class="c-curr-empty-badge" style="font-size:11px;color:rgba(15,65,74,0.5);font-style:italic;">No subjects assigned yet</span>'}
            </div>
          </article>`;
        card.insertAdjacentHTML('beforebegin', permanentCard);
        card.remove();

        // Live DOM sync to matching grade cards on the page
        syncCurriculumToGradeCards(range, chips);
        showToast(`Curriculum stage ${range} created successfully.`);
        return;
      }

      // 4.3 Cancel new curriculum creation
      const cancelNewBtn = e.target.closest('.j-new-curr-cancel-btn');
      if (cancelNewBtn) {
        cancelNewBtn.closest('.j-new-curriculum-card')?.remove();
        return;
      }

      // 4.4 Edit existing curriculum subjects & range
      const editBtn = e.target.closest('.j-edit-curriculum-btn');
      if (editBtn) {
        const card = editBtn.closest('.c-curriculum-card');
        if (!card || card.querySelector('.j-curriculum-editor')) return;
        const subjectsWrap = card.querySelector('.j-curriculum-subjects');
        if (!subjectsWrap) return;
        const curRange     = card.dataset.range || '';
        const curSubjects  = Array.from(subjectsWrap.querySelectorAll('.c-subject-chip')).map(c => c.textContent.trim());
        subjectsWrap.style.display = 'none';

        const chipsHtml = curSubjects.length > 0
          ? curSubjects.map(s => `<span class="c-removable-chip" data-subject="${escapeHtml(s)}">${escapeHtml(s)}<button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove ${escapeHtml(s)}"><svg width="10" height="10"><use href="#icon-close"/></svg></button></span>`).join('')
          : '<p class="c-curr-empty-msg" style="font-size:11px; color:rgba(15,65,74,0.6); margin:0.25rem 0;">No subjects added yet. Type a subject below and click &ldquo;+ Add&rdquo;.</p>';

        card.insertAdjacentHTML('beforeend', `
          <div class="c-curriculum-editor j-curriculum-editor" style="margin-top:1rem;">
            <div style="margin-bottom:0.75rem;">
              <label class="c-field-label-sm" style="font-size:10px;margin-bottom:0.25rem;">Curriculum Stage / Range</label>
              <input class="c-input-sm j-curr-range-input" value="${escapeHtml(curRange)}" placeholder="e.g. Years 6–8" style="font-weight:700;font-size:0.875rem;" required />
            </div>
            <label class="c-field-label-sm" style="font-size:10px;margin-bottom:0.25rem;">Subjects</label>
            <div class="c-curriculum-editor__chips j-curr-chips">
              ${chipsHtml}
            </div>
            <div class="c-curriculum-editor__add-row" style="margin-top:0.5rem;">
              <input class="c-curriculum-editor__add-input j-curr-add-input" placeholder="Add a subject e.g. Geography" />
              <button type="button" class="c-curriculum-editor__add-btn j-curr-add-btn">+ Add</button>
            </div>
            <div class="c-curriculum-editor__footer">
              <button type="button" class="c-btn-plain j-curr-cancel-btn">Cancel</button>
              <button type="button" class="c-btn-danger j-curr-save-btn" style="background:var(--maroon,#7F0303);">Save</button>
            </div>
          </div>`);
        card.querySelector('.j-curr-add-input')?.focus();
        return;
      }

      // 4.5 Remove chip inside curriculum editor
      const removeChip = e.target.closest('.j-remove-curr-chip');
      if (removeChip) {
        const chip = removeChip.closest('.c-removable-chip');
        const chipsWrap = chip?.closest('.j-curr-chips');
        chip?.remove();
        if (chipsWrap && chipsWrap.querySelectorAll('.c-removable-chip').length === 0) {
          chipsWrap.innerHTML = '<p class="c-curr-empty-msg" style="font-size:11px; color:rgba(15,65,74,0.6); margin:0.25rem 0;">No subjects added yet. Type a subject below and click &ldquo;+ Add&rdquo;.</p>';
        }
        return;
      }

      // 4.6 Add chip inside curriculum editor
      const addChipBtn = e.target.closest('.j-curr-add-btn');
      if (addChipBtn) {
        const editor = addChipBtn.closest('.j-curriculum-editor');
        const input  = editor?.querySelector('.j-curr-add-input');
        const val    = input?.value.trim();
        if (val) {
          editor.querySelector('.c-curr-empty-msg')?.remove();
          editor.querySelector('.j-curr-chips').insertAdjacentHTML('beforeend', `
            <span class="c-removable-chip" data-subject="${escapeHtml(val)}">
              ${escapeHtml(val)}
              <button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove ${escapeHtml(val)}">
                <svg width="10" height="10"><use href="#icon-close"/></svg>
              </button>
            </span>
          `);
          input.value = '';
          input.focus();
        }
        return;
      }

      // 4.7 Cancel editing existing curriculum
      const cancelCurrBtn = e.target.closest('.j-curr-cancel-btn');
      if (cancelCurrBtn) {
        const card = cancelCurrBtn.closest('.c-curriculum-card');
        const subjectsWrap = card?.querySelector('.j-curriculum-subjects');
        if (subjectsWrap) subjectsWrap.style.display = '';
        cancelCurrBtn.closest('.j-curriculum-editor')?.remove();
        return;
      }

      // 4.8 Save edited curriculum subjects & range
      const saveCurrBtn = e.target.closest('.j-curr-save-btn');
      if (saveCurrBtn) {
        const card         = saveCurrBtn.closest('.c-curriculum-card');
        const editor       = saveCurrBtn.closest('.j-curriculum-editor');
        const oldRange     = card?.dataset.range || '';
        const rangeInput   = editor?.querySelector('.j-curr-range-input');
        const newRange     = rangeInput ? rangeInput.value.trim() : oldRange;

        // Auto-capture any pending typed subject in add-input
        const addInput = editor?.querySelector('.j-curr-add-input');
        if (addInput && addInput.value.trim()) {
          const val = addInput.value.trim();
          editor.querySelector('.c-curr-empty-msg')?.remove();
          editor.querySelector('.j-curr-chips').insertAdjacentHTML('beforeend', `
            <span class="c-removable-chip" data-subject="${escapeHtml(val)}">
              ${escapeHtml(val)}
              <button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove ${escapeHtml(val)}"><svg width="10" height="10"><use href="#icon-close"/></svg></button>
            </span>
          `);
          addInput.value = '';
        }

        const chips        = Array.from(editor.querySelectorAll('.c-removable-chip')).map(c => c.dataset.subject || c.textContent.trim());
        const subjectsWrap = card?.querySelector('.j-curriculum-subjects');

        if (!newRange) {
          rangeInput?.focus();
          return;
        }

        saveCurrBtn.disabled = true;
        const res = await apiPost('editCurriculumGroup', {
          range_label: oldRange,
          new_range_label: newRange,
          subjects: chips
        });

        if (!res.success) {
          showToast(res.error || 'Failed to update curriculum stage.', 'error');
          saveCurrBtn.disabled = false;
          return;
        }

        card.dataset.range = newRange;
        const rangeEl = card.querySelector('.c-curriculum-card__range');
        if (rangeEl) rangeEl.textContent = newRange;

        const editBtnEl = card.querySelector('.j-edit-curriculum-btn');
        if (editBtnEl) editBtnEl.dataset.range = newRange;
        const delBtnEl = card.querySelector('.j-delete-curriculum-btn');
        if (delBtnEl) delBtnEl.dataset.range = newRange;

        if (subjectsWrap) {
          subjectsWrap.innerHTML = chips.length > 0
            ? chips.map((s, i) => `<span class="c-subject-chip c-subject-tone-${i % 5}">${escapeHtml(s)}</span>`).join('')
            : '<span class="c-curr-empty-badge" style="font-size:11px;color:rgba(15,65,74,0.5);font-style:italic;">No subjects assigned yet</span>';
          subjectsWrap.style.display = '';
        }
        const countEl = card?.querySelector('.c-curriculum-card__count');
        if (countEl) countEl.textContent = `${chips.length} Subjects`;
        
        // Live DOM sync to all grade cards covered by newRange
        syncCurriculumToGradeCards(newRange, chips);
        if (oldRange && oldRange !== newRange) {
          syncCurriculumToGradeCards(oldRange, []);
        }

        editor.remove();
        showToast(`Curriculum stage ${newRange} updated successfully.`);
      }
    });


    document.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const input = e.target.closest('.j-curr-add-input');
        if (input) {
          e.preventDefault();
          const editor = input.closest('.j-curriculum-editor');
          editor?.querySelector('.j-curr-add-btn')?.click();
        }
      }
    });
  }

  // -------------------------------------------------------------------------
  // 5. ADD GRADE MODAL (WITH REAL AJAX & CURRICULUM VALIDATION)
  // -------------------------------------------------------------------------
  function initAddGradeModal() {
    const openBtn = document.getElementById('j-open-add-grade') || document.querySelector('.j-open-add-grade');
    const modal = document.getElementById('j-modal-add-grade');
    if (!openBtn || !modal) return;

    const form = modal.querySelector('#j-add-grade-form') || modal.querySelector('form');
    const nameInput = modal.querySelector('#j-add-grade-name') || modal.querySelector('input[type="text"]');
    const submitBtn = modal.querySelector('#j-add-grade-submit') || modal.querySelector('button[type="submit"]');

    openBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (nameInput) {
        nameInput.value = '';
        if (submitBtn) submitBtn.disabled = true;
      }
      if (typeof openModal === 'function') {
        openModal(modal);
      } else {
        modal.style.display = 'flex';
        modal.classList.add('c-is-open');
      }
      setTimeout(() => nameInput?.focus(), 120);
    });

    if (nameInput && submitBtn) {
      nameInput.addEventListener('input', () => {
        submitBtn.disabled = !nameInput.value.trim();
      });
    }

    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const gradeName = nameInput ? nameInput.value.trim() : '';
        if (!gradeName) return;

        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.dataset.originalHtml = submitBtn.innerHTML;
          submitBtn.innerHTML = '<span>Creating...</span>';
        }

        const res = await apiPost('addGrade', { name: gradeName });
        if (!res.success) {
          showToast(res.error || 'Failed to create grade.', 'error');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.dataset.originalHtml || '<svg class="c-icon" width="13" height="13"><use href="#icon-plus"/></svg> Create grade';
          }
          return;
        }

        const gradeGrid = document.getElementById('j-grade-grid') || document.querySelector('.c-grade-grid');
        if (gradeGrid && res.grade) {
          const g = res.grade;
          const initialClass = (g.classes && g.classes[0]) || `${g.id.replace('g', '')}-A`;
          const gNum = parseInt(gradeName.replace(/\D/g, ''), 10);
          const curriculumSubjects = (g.curriculum_subjects && g.curriculum_subjects.length > 0)
            ? g.curriculum_subjects
            : getCurriculumSubjectsForGrade(gNum);

          let subjectsHtml = '';
          if (curriculumSubjects.length > 0) {
            subjectsHtml = curriculumSubjects.map(subj => `
              <div class="c-subject-assignment-row">
                <div class="c-subject-assignment-row__name">${escapeHtml(subj)}</div>
                <div class="c-teacher-field j-subject-teacher-field" data-preview-placement="right" data-class-name="${escapeHtml(initialClass)}" data-subject="${escapeHtml(subj)}">
                  <div style="position: relative;">
                    <button type="button" class="c-teacher-field__trigger j-subject-teacher-trigger" aria-haspopup="listbox" aria-expanded="false">
                      <span class="c-teacher-field__trigger-value j-subject-teacher-trigger-value c-is-placeholder">Assignment pending</span>
                      <svg class="c-icon c-teacher-field__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
                    </button>
                    <div class="c-teacher-field__popover j-subject-teacher-popover"></div>
                  </div>
                </div>
              </div>
            `).join('');
          } else {
            subjectsHtml = '<p class="c-no-subjects-note" style="font-size:11px;color:rgba(15,65,74,0.6);padding:0.5rem 0;">No subjects in curriculum yet. Add subjects to the curriculum stage to assign teachers.</p>';
          }

          const newGradeCardHtml = `
            <article class="c-grade-card" data-grade-id="${escapeHtml(g.id)}" data-grade-name="${escapeHtml(g.name)}">
              <div class="c-grade-card__head">
                <div>
                  <h3 class="c-grade-card__name">${escapeHtml(g.name)}</h3>
                  <p class="c-grade-card__meta">
                    <svg class="c-icon" width="14" height="14"><use href="#icon-usersRound"/></svg>
                    30 students · 1 class
                  </p>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <button type="button" class="c-btn-add c-btn-add--small j-add-class-btn" data-grade-id="${escapeHtml(g.id)}">
                    <svg class="c-icon" width="13" height="13"><use href="#icon-plus"/></svg>
                    Add class
                  </button>
                  <button type="button" class="c-btn-delete-subtle j-delete-grade-btn" data-grade-id="${escapeHtml(g.id)}" aria-label="Delete ${escapeHtml(g.name)}">
                    <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-trash"/></svg>
                    Delete
                  </button>
                </div>
              </div>
              <div class="c-class-list j-class-list">
                <details class="c-class-details" data-class-name="${escapeHtml(initialClass)}" data-student-count="30">
                  <summary class="c-class-row" data-class-name="${escapeHtml(initialClass)}">
                    <span class="c-class-row__badge c-tone-a">${escapeHtml(initialClass)}</span>
                    <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
                      <div>
                        <p class="c-class-row__teacher-label">Class teacher</p>
                        <p class="c-class-row__teacher-name c-is-pending" title="Click to assign class teacher">Assignment pending</p>
                      </div>
                      <button type="button" class="c-class-row__edit-btn j-edit-class-btn" data-grade-id="${escapeHtml(g.id)}" data-class-name="${escapeHtml(initialClass)}" aria-label="Edit ${escapeHtml(initialClass)}" style="margin-left: 0.25rem;">
                        <svg class="c-icon" width="14" height="14"><use href="#icon-edit"/></svg>
                      </button>
                    </div>
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 0.375rem; color: rgba(15, 65, 74, 0.7); font-size: 0.8125rem; font-weight: 600;">
                      <span>Subject teachers</span>
                      <div class="c-class-row__expand-icon" style="display: flex; align-items: center;">
                        <svg class="c-icon" width="16" height="16"><use href="#icon-chevronDown"/></svg>
                      </div>
                    </div>
                  </summary>
                  <div class="c-class-subjects">
                    <h4 class="c-class-subjects__title">Subject Assignments</h4>
                    <div class="c-class-subjects__list">
                      ${subjectsHtml}
                    </div>
                  </div>
                </details>
              </div>
            </article>
          `;
          gradeGrid.insertAdjacentHTML('beforeend', newGradeCardHtml);
          const newCardEl = gradeGrid.lastElementChild;
          if (newCardEl) {
            newCardEl.querySelectorAll('.j-subject-teacher-field').forEach(wireTeacherField);
          }
        }

        if (typeof closeModal === 'function') {
          closeModal(modal);
        } else {
          modal.classList.remove('c-is-open');
          modal.style.display = 'none';
        }

        showToast(`${gradeName} added successfully.`);
      });
    }
  }

  // -------------------------------------------------------------------------
  // INITIALIZATION
  // -------------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', () => {
    initGradeClassEditors();
    initTeacherFields();
    initGradeDeleteTriggers();
    initCurriculumEditors();
    initAddGradeModal();
  });

})();
