(function () {
  'use strict';

  const TEACHER_DIRECTORY = [
    { id: 'james-wilson',     name: 'James Wilson',     subject: 'Science',            classes: ['6-A', '7-B', '8-C'], extras: ['Science Society'] },
    { id: 'sarah-peiris',     name: 'Sarah Peiris',     subject: 'English',            classes: ['6-B', '7-A'],         extras: ['Debate Society'] },
    { id: 'rohan-dias',       name: 'Rohan Dias',       subject: 'Mathematics',        classes: ['9-A', '10-B'],        extras: ['Chess Club'] },
    { id: 'priya-de-silva',   name: 'Priya De Silva',   subject: 'Visual Arts',        classes: [],                     extras: [] },
    { id: 'anura-wijesinghe', name: 'Anura Wijesinghe', subject: 'Geography',          classes: ['8-A', '9-B'],         extras: [] },
    { id: 'sofia-fernando',   name: 'Sofia Fernando',   subject: 'Pending allocation', classes: [],                     extras: ['Eco Club'] },
    { id: 'shanthi-silva',    name: 'Shanthi Silva',    subject: 'Computer Science',   classes: [],                     extras: ['Robotics & AI Lab'] },
    { id: 'madhavi-fernando', name: 'Madhavi Fernando', subject: 'English Literature', classes: ['7-C', '11-A'],        extras: ["L'École Philharmonic"] }
  ];

  const escapeHtml = window.escapeHtml || function (str) {
    return String(str || '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  };

// -------------------------------------------------------------------------
// 1. INLINE CLASS EDITOR (Templates sourced from _grade_class_editor.php)
// -------------------------------------------------------------------------
function createClassFormElement({ gradeId, className = '', teacherName = '', mode = 'add', placement = 'right' }) {
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
      studentsInput.value = '30';
    }

    const teacherField = form.querySelector('.j-teacher-field');
    if (teacherField) {
      teacherField.dataset.previewPlacement = placement;
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

  // Fallback if template is not available
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
      }
      return;
    }

    const cancelBtn = e.target.closest('.j-cancel-class-btn');
    if (cancelBtn) {
      const form = cancelBtn.closest('.j-inline-class-form');
      if (form) {
        if (form.dataset.mode === 'edit') { const prev = form.previousElementSibling; if (prev) prev.style.display = ''; }
        form.remove();
      }
      return;
    }

    const editBtn = e.target.closest('.j-edit-class-btn');
    if (editBtn) {
      e.stopPropagation();
      const classRow    = editBtn.closest('.c-class-details');
      const gradeCard   = editBtn.closest('.c-grade-card');
      const rawTeacher  = classRow.querySelector('.c-class-row__teacher-name')?.textContent.trim() || '';
      const teacherName = rawTeacher === 'Assignment pending' ? '' : rawTeacher;
      const placement   = (Array.from(document.querySelectorAll('.c-grade-card')).indexOf(gradeCard) % 2 === 1) ? 'left' : 'right';
      const form = createClassFormElement({ gradeId: editBtn.dataset.gradeId, className: editBtn.dataset.className, teacherName, mode: 'edit', placement });
      if (form) {
        classRow.style.display = 'none';
        classRow.after(form);
        wireTeacherField(form.querySelector('.j-teacher-field'));
      }
    }
  });

  document.addEventListener('submit', (e) => {
    const form = e.target.closest('.j-inline-class-form');
    if (!form) return;
    e.preventDefault();

    const secName     = form.querySelector('.j-class-name-input').value.trim();
    const triggerVal  = form.querySelector('.j-teacher-trigger-value');
    const rawVal      = triggerVal ? triggerVal.textContent.trim() : '';
    const isPlaceholder = triggerVal?.classList.contains('c-is-placeholder') || !rawVal || rawVal === 'Assignment pending';
    const teacherName = isPlaceholder ? 'Assignment pending' : rawVal;
    if (!secName) return;

    if (form.dataset.mode === 'edit') {
      const originalRow = form.previousElementSibling;
      if (originalRow) {
        const badge = originalRow.querySelector('.c-class-row__badge');
        if (badge) badge.textContent = secName;
        const teacherEl = originalRow.querySelector('.c-class-row__teacher-name');
        if (teacherEl) {
          teacherEl.textContent = teacherName;
          teacherEl.classList.toggle('c-is-pending', isPlaceholder);
        }
        originalRow.style.display = '';
      }
      form.remove();
    } else {
      const gradeId   = form.dataset.gradeId;
      const letter    = secName.slice(-1).toUpperCase();
      const toneMap   = { A: 'c-tone-a', B: 'c-tone-b', C: 'c-tone-c', D: 'c-tone-d', E: 'c-tone-e' };
      const badgeTone = toneMap[letter] || 'c-tone-default';
      form.insertAdjacentHTML('beforebegin', `
        <details class="c-class-details" data-class-name="${escapeHtml(secName)}">
          <summary class="c-class-row" data-class-name="${escapeHtml(secName)}">
            <span class="c-class-row__badge ${badgeTone}">${escapeHtml(secName)}</span>
            <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
              <div>
                <p class="c-class-row__teacher-label">Class teacher</p>
                <p class="c-class-row__teacher-name ${isPlaceholder ? 'c-is-pending' : ''}" title="Click to assign class teacher">${escapeHtml(teacherName)}</p>
              </div>
              <button type="button" class="c-class-row__edit-btn j-edit-class-btn" data-grade-id="${escapeHtml(gradeId)}" data-class-name="${escapeHtml(secName)}" aria-label="Edit ${escapeHtml(secName)}">
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
              <p style="font-size:11px;color:rgba(15,65,74,0.6);">Assign subject teachers once curriculum is confirmed.</p>
            </div>
          </div>
        </details>`);
      form.remove();
    }
  });
}

// -------------------------------------------------------------------------
// 2. TEACHER FIELD — popover + workload hover preview
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

function wireTeacherField(fieldEl) {
  if (!fieldEl || fieldEl.dataset.wired) return;
  fieldEl.dataset.wired = 'true';

  const trigger   = fieldEl.querySelector('.j-teacher-trigger, .j-subject-teacher-trigger');
  const valueSpan = fieldEl.querySelector('.j-teacher-trigger-value, .j-subject-teacher-trigger-value');
  const popover   = fieldEl.querySelector('.j-teacher-popover, .j-subject-teacher-popover');
  const placement = fieldEl.dataset.previewPlacement || 'right';
  if (!trigger || !valueSpan || !popover) return;

  function renderList() {
    const curVal = valueSpan.textContent.trim();
    const isPending = !curVal || curVal === 'Assignment pending' || valueSpan.classList.contains('c-is-placeholder');

    let html = `<ul class="c-teacher-menu"><li><button type="button" class="c-teacher-menu__item j-opt-teacher${isPending ? ' c-is-selected' : ''}" data-name="" data-id=""><div class="c-teacher-menu__initials" style="background:rgba(15,65,74,0.2);color:var(--midnight);">--</div><div class="c-teacher-menu__item-meta"><span class="c-teacher-menu__item-name">Assignment pending</span><span class="c-teacher-menu__item-status">Leave unassigned</span></div></button></li>`;
    TEACHER_DIRECTORY.forEach(t => {
      const initials = t.name.split(' ').map(w => w[0]).join('').slice(0, 2);
      const isSelected = !isPending && t.name === curVal;
      html += `<li><button type="button" class="c-teacher-menu__item j-opt-teacher${isSelected ? ' c-is-selected' : ''}" data-name="${escapeHtml(t.name)}" data-id="${escapeHtml(t.id)}"><div class="c-teacher-menu__initials">${escapeHtml(initials)}</div><div class="c-teacher-menu__item-meta"><span class="c-teacher-menu__item-name">${escapeHtml(t.name)}</span><span class="c-teacher-menu__item-status">${escapeHtml(t.subject)} · ${t.classes.length} classes</span></div></button></li>`;
    });
    html += `</ul><div class="c-workload-preview j-workload-preview c-workload-preview--${placement}"></div>`;
    popover.innerHTML = html;

    const preview = popover.querySelector('.j-workload-preview');
    popover.querySelectorAll('.j-opt-teacher').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const name = btn.dataset.name;
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
      });
      btn.addEventListener('mouseenter', () => {
        const teacher = TEACHER_DIRECTORY.find(t => t.id === btn.dataset.id);
        if (!teacher) { preview.classList.remove('c-is-visible'); return; }
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
          const subjEl = clone.querySelector('.j-preview-subject');
          if (subjEl) subjEl.textContent = teacher.subject;

          const classesContainer = clone.querySelector('.j-preview-classes');
          if (classesContainer) {
            classesContainer.innerHTML = teacher.classes.length
              ? teacher.classes.map(c => `<span class="c-workload-tag">${escapeHtml(c)}</span>`).join('')
              : '<span class="c-workload-tag--empty">None</span>';
          }

          const extrasContainer = clone.querySelector('.j-preview-extras');
          if (extrasContainer) {
            extrasContainer.innerHTML = teacher.extras.length
              ? teacher.extras.map(x => `<span class="c-workload-tag" style="background:rgba(234,137,19,0.25);color:var(--midnight);">${escapeHtml(x)}</span>`).join('')
              : '<span class="c-workload-tag--empty">None</span>';
          }

          preview.innerHTML = '';
          preview.appendChild(head);
          preview.appendChild(body);
        }
        preview.classList.add('c-is-visible');
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
    }
  });
}

// -------------------------------------------------------------------------
// 3. GRADE / CURRICULUM DELETE — delegates to openUniversalDeleteModal
// -------------------------------------------------------------------------
function initGradeDeleteTriggers() {
  document.addEventListener('click', (e) => {
    const delGradeBtn = e.target.closest('.j-delete-grade-btn');
    if (delGradeBtn) {
      const gradeCard = delGradeBtn.closest('.c-grade-card');
      const gradeName = gradeCard.dataset.gradeName || 'Grade';
      const classes   = Array.from(gradeCard.querySelectorAll('.c-class-details')).map(d => d.dataset.className);
      const gradeWrap = document.getElementById('j-del-grade-pill-wrap');
      const classWrap = document.getElementById('j-del-class-pill-wrap');

      window.openUniversalDeleteModal?.({
        title: 'Delete Academic Structures',
        description: 'Select the grade or the classes you wish to remove.',
        buttonText: 'Delete Selected',
        customSlotRenderer: (slot, cBtn) => {
          if (cBtn) cBtn.disabled = true;
          if (gradeWrap) gradeWrap.innerHTML = `<button type="button" class="c-btn-plain j-del-pill" data-type="grade" style="border:1px solid var(--alabaster);border-radius:var(--radius-lg);background:#fff;padding:0.35rem 0.65rem;font-size:0.75rem;font-weight:700;">All of ${escapeHtml(gradeName)}</button>`;
          if (classWrap) classWrap.innerHTML = classes.map(c => `<button type="button" class="c-btn-plain j-del-pill" data-type="class" data-class="${escapeHtml(c)}" style="border:1px solid var(--alabaster);border-radius:var(--radius-lg);background:#fff;padding:0.35rem 0.65rem;font-size:0.75rem;font-weight:700;">${escapeHtml(c)}</button>`).join('');
        },
        onConfirm: () => {
          const modal = document.getElementById('j-universal-delete-modal');
          if (!modal) return;
          if (modal.querySelector('.j-del-pill[data-type="grade"].c-is-selected')) {
            gradeCard.remove();
          } else {
            modal.querySelectorAll('.j-del-pill[data-type="class"].c-is-selected').forEach(p => {
              const row = gradeCard.querySelector(`.c-class-details[data-class-name="${p.dataset.class}"]`);
              if (row) row.remove();
            });
          }
        }
      });
      return;
    }

    const delCurrBtn = e.target.closest('.j-delete-curriculum-btn');
    if (delCurrBtn) {
      const currCard = delCurrBtn.closest('.c-curriculum-card');
      const range    = delCurrBtn.dataset.range || currCard?.dataset?.range || 'this';
      if (typeof window.openUniversalDeleteModal === 'function') {
        window.openUniversalDeleteModal({
          title: `Delete ${range} Curriculum?`,
          description: `This will remove the curriculum group for ${range} and all its subjects.`,
          buttonText: 'Delete',
          onConfirm: () => currCard?.remove()
        });
      } else {
        if (confirm(`Delete ${range} Curriculum? This will remove all its subjects.`)) {
          currCard?.remove();
        }
      }
      return;
    }

    // Pill toggle inside modal
    const pill = e.target.closest('.j-del-pill');
    if (pill) {
      const modal = pill.closest('#j-universal-delete-modal');
      if (!modal) return;
      pill.classList.toggle('c-is-selected');
      pill.style.background = pill.classList.contains('c-is-selected') ? '#7f0303' : '#fff';
      pill.style.color      = pill.classList.contains('c-is-selected') ? '#fff' : '';
      const confirmBtn = modal.querySelector('#j-universal-delete-confirm');
      if (confirmBtn) confirmBtn.disabled = modal.querySelectorAll('.j-del-pill.c-is-selected').length === 0;
    }
  });
}

// -------------------------------------------------------------------------
// 4. CURRICULUM INLINE EDITOR & CREATOR
// -------------------------------------------------------------------------
function initCurriculumEditors() {
  document.addEventListener('click', (e) => {
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
              <span class="c-removable-chip" data-subject="English">English<button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove English"><svg width="10" height="10"><use href="#icon-close"/></svg></button></span>
              <span class="c-removable-chip" data-subject="Mathematics">Mathematics<button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove Mathematics"><svg width="10" height="10"><use href="#icon-close"/></svg></button></span>
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

      const chips = Array.from(card.querySelectorAll('.c-removable-chip')).map(c => c.dataset.subject || c.textContent.trim());
      if (chips.length === 0) chips.push('General Studies');

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
                <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-edit"/>
                </svg>
              </button>
              <button type="button" class="c-curriculum-card__edit-btn j-delete-curriculum-btn" data-range="${escapeHtml(range)}" aria-label="Delete ${escapeHtml(range)}">
                <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-trash"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="c-curriculum-card__subjects j-curriculum-subjects">
            ${chips.map((s, i) => `<span class="c-subject-chip c-subject-tone-${i % 5}">${escapeHtml(s)}</span>`).join('')}
          </div>
        </article>`;
      card.insertAdjacentHTML('beforebegin', permanentCard);
      card.remove();
      return;
    }

    // 4.3 Cancel new curriculum creation
    const cancelNewBtn = e.target.closest('.j-new-curr-cancel-btn');
    if (cancelNewBtn) {
      cancelNewBtn.closest('.j-new-curriculum-card')?.remove();
      return;
    }

    // 4.4 Edit existing curriculum subjects
    const editBtn = e.target.closest('.j-edit-curriculum-btn');
    if (editBtn) {
      const card = editBtn.closest('.c-curriculum-card');
      if (!card || card.querySelector('.j-curriculum-editor')) return;
      const subjectsWrap = card.querySelector('.j-curriculum-subjects');
      if (!subjectsWrap) return;
      const curSubjects  = Array.from(subjectsWrap.querySelectorAll('.c-subject-chip')).map(c => c.textContent.trim());
      subjectsWrap.style.display = 'none';
      card.insertAdjacentHTML('beforeend', `
        <div class="c-curriculum-editor j-curriculum-editor" style="margin-top:1rem;">
          <div class="c-curriculum-editor__chips j-curr-chips">
            ${curSubjects.map(s => `<span class="c-removable-chip" data-subject="${escapeHtml(s)}">${escapeHtml(s)}<button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove ${escapeHtml(s)}"><svg width="10" height="10"><use href="#icon-close"/></svg></button></span>`).join('')}
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
    if (removeChip) { removeChip.closest('.c-removable-chip').remove(); return; }

    // 4.6 Add chip inside curriculum editor
    const addChipBtn = e.target.closest('.j-curr-add-btn');
    if (addChipBtn) {
      const editor = addChipBtn.closest('.j-curriculum-editor');
      const input  = editor?.querySelector('.j-curr-add-input');
      const val    = input?.value.trim();
      if (val) {
        editor.querySelector('.j-curr-chips').insertAdjacentHTML('beforeend', `<span class="c-removable-chip" data-subject="${escapeHtml(val)}">${escapeHtml(val)}<button type="button" class="c-removable-chip__remove j-remove-curr-chip" aria-label="Remove ${escapeHtml(val)}"><svg width="10" height="10"><use href="#icon-close"/></svg></button></span>`);
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

    // 4.8 Save edited curriculum subjects
    const saveCurrBtn = e.target.closest('.j-curr-save-btn');
    if (saveCurrBtn) {
      const card         = saveCurrBtn.closest('.c-curriculum-card');
      const editor       = saveCurrBtn.closest('.j-curriculum-editor');
      const chips        = Array.from(editor.querySelectorAll('.c-removable-chip')).map(c => c.dataset.subject || c.textContent.trim());
      const subjectsWrap = card?.querySelector('.j-curriculum-subjects');
      if (subjectsWrap) {
        subjectsWrap.innerHTML = chips.map((s, i) => `<span class="c-subject-chip c-subject-tone-${i % 5}">${escapeHtml(s)}</span>`).join('');
        subjectsWrap.style.display = '';
      }
      const countEl = card?.querySelector('.c-curriculum-card__count');
      if (countEl) countEl.textContent = `${chips.length} Subjects`;
      editor.remove();
    }
  });

  // Support Enter key inside subject add inputs
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      const input = e.target.closest('.j-curr-add-input');
      if (input) {
        e.preventDefault();
        const editor = input.closest('.j-curriculum-editor');
        const addBtn = editor?.querySelector('.j-curr-add-btn');
        addBtn?.click();
      }
    }
  });
}

// -------------------------------------------------------------------------
// 5. ADD GRADE MODAL
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
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const gradeName = nameInput ? nameInput.value.trim() : '';
      if (!gradeName) return;

      const gradeGrid = document.getElementById('j-grade-grid') || document.querySelector('.c-grade-grid');
      if (gradeGrid) {
        const gradeNumMatch = gradeName.match(/\d+/);
        const gradeNum = gradeNumMatch ? gradeNumMatch[0] : (gradeGrid.querySelectorAll('.c-grade-card').length + 6);
        const gradeId = 'g' + gradeNum;
        const initialClass = `${gradeNum}-A`;

        const newGradeCardHtml = `
          <article class="c-grade-card j-grade-card" data-grade-id="${gradeId}">
            <div class="c-grade-card__top">
              <div class="c-grade-card__head">
                <span class="c-grade-card__badge">${escapeHtml(gradeName)}</span>
                <span class="c-grade-card__count j-class-count">1 Class</span>
              </div>
              <div class="c-class-list j-class-list">
                <div class="c-class-row j-class-row" data-class-name="${initialClass}" data-enrolled="30">
                  <div class="c-class-row__left">
                    <span class="c-class-row__pill">${initialClass}</span>
                    <button type="button" class="c-class-row__teacher-name j-edit-class-btn" data-grade-id="${gradeId}" data-class-name="${initialClass}" data-teacher-name="Assignment pending" title="Click to assign class teacher">
                      <span class="c-class-row__teacher-label">Class teacher</span>
                      Assignment pending
                    </button>
                  </div>
                  <div class="c-class-row__right">
                    <span class="c-class-row__enrollment"><span class="j-enrolled-count">30</span> enrolled</span>
                    <button type="button" class="c-class-row__action-btn c-class-row__action-btn--delete j-del-class-btn" data-class-name="${initialClass}" data-grade-id="${gradeId}" aria-label="Delete class ${initialClass}" title="Delete class">
                      <svg class="c-icon" width="12" height="12"><use href="#icon-trash"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="c-grade-card__footer">
              <button type="button" class="c-btn-add-class j-add-class-btn" data-grade-id="${gradeId}">
                <svg class="c-icon" width="11" height="11"><use href="#icon-plus"/></svg>
                Add class
              </button>
            </div>
          </article>
        `;
        gradeGrid.insertAdjacentHTML('beforeend', newGradeCardHtml);
      }

      if (typeof closeModal === 'function') {
        closeModal(modal);
      } else {
        modal.classList.remove('c-is-open');
        modal.style.display = 'none';
      }
    });
  }
}

// -------------------------------------------------------------------------
// INIT
// -------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  initGradeClassEditors();
  initTeacherFields();
  initGradeDeleteTriggers();
  initCurriculumEditors();
  initAddGradeModal();
});

})();


