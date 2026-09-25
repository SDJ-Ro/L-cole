/**
 * =========================================================================
 * L'ÉCOLE — SHARED TEACHER WORKLOAD HOVER PREVIEW COMPONENT
 * =========================================================================
 * Single source of truth for the 3-level teacher workload hover card:
 *   1. Class Teacher assignment
 *   2. Extracurriculars In-charge of
 *   3. Subject Teacher for (showing subjects and class sections)
 * 
 * Reused across:
 *   - Academic Grade Card class teacher & subject teacher popovers
 *   - Extracurricular Add Activity modal TIC dropdown
 *   - Extracurricular Cards inline TIC change popover & card TIC row
 *   - Extracurricular Programme details TIC sidebar & edit modal
 * =========================================================================
 */

(function () {
  'use strict';

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  const fallbackDirectory = {
    'james wilson': { name: 'James Wilson', qualification: 'B.Sc. Science / Biology', classTeacher: '6-A', extras: ['Science Society'], subjects: [{ subject: 'Science', classes: ['6-A', '7-B', '8-C'] }] },
    'sarah peiris': { name: 'Sarah Peiris', qualification: 'B.A. English & Literature', classTeacher: '6-B', extras: ['Debating Society'], subjects: [{ subject: 'English', classes: ['6-B', '7-A'] }] },
    'rohan dias': { name: 'Rohan Dias', qualification: 'B.Sc. Mathematics', classTeacher: '9-A', extras: ['Chess Club'], subjects: [{ subject: 'Mathematics', classes: ['9-A', '10-B', '11-C'] }] },
    'madhavi fernando': { name: 'Madhavi Fernando', qualification: 'M.Mus. Performing Arts', classTeacher: '', extras: ["L'École Philharmonic"], subjects: [{ subject: 'Music & Arts', classes: ['6-A', '8-A'] }] },
    'alex benjamin': { name: 'Alex Benjamin', qualification: 'B.Sc. Physics & Astronomy', classTeacher: '', extras: ['Astronomy Society'], subjects: [{ subject: 'Physics', classes: ['10-A', '11-A'] }] },
    'priya de silva': { name: 'Priya De Silva', qualification: 'B.F.A. Fine Arts', classTeacher: '', extras: ['Art Circle'], subjects: [{ subject: 'Arts', classes: ['6-A', '7-A'] }] },
    'sofia fernando': { name: 'Sofia Fernando', qualification: 'M.Sc. Environmental Science', classTeacher: '', extras: ['Eco Club'], subjects: [{ subject: 'Environmental Studies', classes: ['8-A', '9-A'] }] },
    'shanthi silva': { name: 'Shanthi Silva', qualification: 'M.Sc. Robotics & Computer Science', classTeacher: '', extras: ['Robotics & AI Lab'], subjects: [{ subject: 'ICT & Computing', classes: ['7-A', '8-B'] }] },
    'mr. weerasinghe': { name: 'Mr. Weerasinghe', qualification: 'National Diploma in Physical Ed.', classTeacher: '', extras: ['Varsity Football Club', 'Cricket Club'], subjects: [{ subject: 'Physical Education', classes: ['Senior Grades'] }] },
    'anura wijesinghe': { name: 'Anura Wijesinghe', qualification: 'M.A. History', classTeacher: '', extras: [], subjects: [{ subject: 'History', classes: ['7-A', '7-B'] }] },
    'nethmi perera': { name: 'Nethmi Perera', qualification: 'B.Sc. General Science', classTeacher: '', extras: [], subjects: [{ subject: 'Science', classes: ['6-C'] }] },
    'amara silva': { name: 'Amara Silva', qualification: 'B.A. Languages', classTeacher: '', extras: [], subjects: [{ subject: 'Sinhala', classes: ['6-A', '6-B'] }] },
    'kavindi jayasinghe': { name: 'Kavindi Jayasinghe', qualification: 'B.Ed. Primary Ed.', classTeacher: '', extras: [], subjects: [{ subject: 'General', classes: ['6-B'] }] },
    'david peris': { name: 'David Peris', qualification: 'B.Sc. Mathematics', classTeacher: '', extras: [], subjects: [{ subject: 'Mathematics', classes: ['7-A'] }] },
    'ruwan silva': { name: 'Ruwan Silva', qualification: 'B.A. Commerce', classTeacher: '', extras: [], subjects: [{ subject: 'Commerce', classes: ['10-A'] }] }
  };

  /**
   * Find teacher record from window.LECOLE_STAFF_DIRECTORY (Single Source of Truth)
   */
  function findTeacherData(nameOrId) {
    if (!nameOrId) return null;
    const clean = String(nameOrId).trim().toLowerCase();
    const list = Array.isArray(window.LECOLE_STAFF_DIRECTORY) ? window.LECOLE_STAFF_DIRECTORY : [];

    // 1. Direct match on name
    let found = list.find(t => t.name && t.name.trim().toLowerCase() === clean);
    if (found) return found;

    // 2. Direct match on id
    found = list.find(t => t.id && (t.id.toLowerCase() === clean || clean.includes(t.id.toLowerCase())));
    if (found) return found;

    // 3. Normalized slug / partial match
    const slug = clean.replace(/[^a-z0-9]+/g, '-');
    found = list.find(t => {
      const tSlug = (t.name || '').toLowerCase().replace(/[^a-z0-9]+/g, '-');
      return tSlug === slug || (t.id && t.id.toLowerCase() === slug);
    });
    if (found) return found;

    // 4. Known fallback directory
    if (fallbackDirectory[clean]) return fallbackDirectory[clean];

    // 5. Fallback default object for unknown / new faculty
    return {
      name: nameOrId,
      qualification: 'Faculty Member',
      classTeacher: '',
      extras: [],
      subjects: []
    };
  }

  /**
   * Dismiss any open hover preview tooltips
   */
  function dismissAllPreviews() {
    document.querySelectorAll('.c-extracurricular-teacher-preview, .c-workload-preview-floating').forEach(p => p.remove());
  }

  function getOrCreateTemplate() {
    let hoverTmpl = document.getElementById('tmpl-grade-teacher-hover');
    if (!hoverTmpl) {
      hoverTmpl = document.createElement('template');
      hoverTmpl.id = 'tmpl-grade-teacher-hover';
      hoverTmpl.innerHTML = `
        <div class="c-workload-preview j-workload-preview c-workload-preview--right" role="tooltip" aria-label="Teacher workload preview">
          <div class="c-workload-preview__head">
            <div class="c-teacher-menu__initials c-teacher-menu__initials--lg j-preview-initials">T</div>
            <div style="min-width: 0; flex: 1 1 auto;">
              <h4 class="c-workload-preview__name j-preview-name">Teacher Details</h4>
              <p class="c-workload-preview__sub j-preview-qualification j-preview-subject">Faculty</p>
            </div>
          </div>
          <div class="c-workload-preview__body">
            <div class="c-workload-group">
              <p class="c-workload-group__label">
                <svg class="c-icon" width="12" height="12"><use href="#icon-graduationCap"/></svg>
                Class Teacher
              </p>
              <div class="c-workload-group__tags j-preview-class-teacher"></div>
            </div>
            <div class="c-workload-group">
              <p class="c-workload-group__label">
                <svg class="c-icon" width="12" height="12"><use href="#icon-activity"/></svg>
                Extracurriculars In-charge of
              </p>
              <div class="c-workload-group__tags j-preview-extras"></div>
            </div>
            <div class="c-workload-group">
              <p class="c-workload-group__label">
                <svg class="c-icon" width="12" height="12"><use href="#icon-bookOpen"/></svg>
                Subject Teacher for
              </p>
              <div class="c-workload-subject-list j-preview-subjects"></div>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(hoverTmpl);
    }
    return hoverTmpl;
  }

  /**
   * Attach the 3-level workload preview popover to any target interactive element
   *
   * @param {HTMLElement} itemEl Target element triggering hover
   * @param {string|Function} teacherNameOrId Teacher name, ID, or getter function
   * @param {Object} options Optional config: { currentClass, isSubjectField, placement }
   */
  function attachTeacherHoverPreview(itemEl, teacherNameOrId, options = {}) {
    if (!itemEl) return;

    itemEl.addEventListener('mouseenter', () => {
      const resolved = (typeof teacherNameOrId === 'function') ? teacherNameOrId() : teacherNameOrId;
      if (!resolved) return;

      const teacher = findTeacherData(resolved);
      if (!teacher) return;

      const hoverTmpl = getOrCreateTemplate();
      if (!hoverTmpl) return;

      dismissAllPreviews();

      const clone = hoverTmpl.content.cloneNode(true);
      const preview = clone.querySelector('.c-workload-preview');
      if (!preview) return;

      preview.classList.add('c-extracurricular-teacher-preview', 'c-workload-preview-floating', 'c-is-visible');
      preview.style.display = 'block';
      preview.style.position = 'fixed';
      preview.style.zIndex = '999999';
      preview.style.pointerEvents = 'none';

      const initials = (teacher.name || 'T').split(' ').map(w => w[0]).join('').slice(0, 2);
      const initEl = preview.querySelector('.j-preview-initials');
      if (initEl) initEl.textContent = initials;
      const nameEl = preview.querySelector('.j-preview-name');
      if (nameEl) nameEl.textContent = teacher.name;
      const qualEl = preview.querySelector('.j-preview-qualification, .j-preview-subject');
      if (qualEl) qualEl.textContent = teacher.qualification || teacher.subject || 'Faculty';

      // SECTION 1: Class Teacher
      const ctContainer = preview.querySelector('.j-preview-class-teacher');
      if (ctContainer) {
        ctContainer.innerHTML = teacher.classTeacher
          ? `<span class="c-workload-tag c-workload-tag--class-teacher">Class ${escapeHtml(teacher.classTeacher)}</span>`
          : '<span class="c-workload-tag--empty">Unassigned</span>';
      }

      // SECTION 2: Extracurriculars In-charge of
      const extrasContainer = preview.querySelector('.j-preview-extras');
      if (extrasContainer) {
        const exList = teacher.extras || teacher.extracurriculars || [];
        extrasContainer.innerHTML = (exList.length > 0)
          ? exList.map(x => `<span class="c-workload-tag c-workload-tag--extra">${escapeHtml(x)}</span>`).join('')
          : '<span class="c-workload-tag--empty">None</span>';
      }

      // SECTION 3: Subject Teacher for (subjects and classes taught)
      const subjContainer = preview.querySelector('.j-preview-subjects, .j-preview-classes');
      if (subjContainer) {
        if (teacher.subjects && teacher.subjects.length > 0) {
          subjContainer.innerHTML = teacher.subjects.map(s => {
            const sName = escapeHtml(s.subject || s);
            const classTags = (s.classes && s.classes.length > 0)
              ? s.classes.map(c => `<span class="c-workload-tag">${escapeHtml(c)}</span>`).join('')
              : '<span class="c-workload-tag--empty">General</span>';
            return `<div class="c-workload-subject-item"><span class="c-workload-subject-name">${sName}</span><div class="c-workload-subject-classes">${classTags}</div></div>`;
          }).join('');
        } else {
          subjContainer.innerHTML = '<span class="c-workload-tag--empty">No subjects assigned yet</span>';
        }
      }

      // OPTIONAL: Inline conflict warning if assigning class teacher already assigned elsewhere
      if (options.currentClass && !options.isSubjectField && teacher.classTeacher && teacher.classTeacher !== options.currentClass) {
        const body = preview.querySelector('.c-workload-preview__body');
        if (body) {
          const conflictNotice = document.createElement('div');
          conflictNotice.className = 'c-workload-conflict-warning';
          conflictNotice.style.cssText = 'display:flex;align-items:center;gap:0.4rem;padding:0.4rem 0.5rem;background:rgba(234,137,19,0.12);border:1px solid rgba(234,137,19,0.3);border-radius:0.375rem;font-size:10px;color:var(--midnight,#0F414A);font-weight:600;margin-bottom:0.5rem;';
          conflictNotice.innerHTML = `
            <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span>Currently class teacher of <strong>Class ${escapeHtml(teacher.classTeacher)}</strong>.</span>
          `;
          body.prepend(conflictNotice);
        }
      }

      document.body.appendChild(preview);

      // Measure & position relative to itemEl
      const rect = itemEl.getBoundingClientRect();
      const pRect = preview.getBoundingClientRect();

      let left = rect.right + 12;
      let top = rect.top - 10;

      if (left + pRect.width > window.innerWidth - 12) {
        left = rect.left - pRect.width - 12;
      }
      if (left < 12) left = 12;

      if (top + pRect.height > window.innerHeight - 12) {
        top = Math.max(12, window.innerHeight - pRect.height - 12);
      }
      if (top < 12) top = 12;

      preview.style.left = `${left}px`;
      preview.style.top = `${top}px`;
    });

    itemEl.addEventListener('mouseleave', dismissAllPreviews);
  }

  // Global listeners to dismiss tooltips on outside interaction
  document.addEventListener('click', dismissAllPreviews);
  window.addEventListener('scroll', dismissAllPreviews, { passive: true });

  // Export to window
  window.attachTeacherHoverPreview = attachTeacherHoverPreview;
  window.findTeacherData = findTeacherData;
  window.dismissTeacherHoverPreviews = dismissAllPreviews;
})();
