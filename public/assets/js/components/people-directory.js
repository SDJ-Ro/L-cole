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
  let selectedStatus = 'all';
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

    // Restore persistent grade and class selection if available
    try {
      const savedGrade = sessionStorage.getItem('lecole_people_grade');
      const savedClass = sessionStorage.getItem('lecole_people_class');
      if (savedGrade) {
        activeGradeId = savedGrade;
        if (typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-select-grade', savedGrade);
        }
      }
      if (savedClass) {
        activeClassName = savedClass;
      }
    } catch (e) {}

    bindEvents();
    renderClassChips(activeGradeId);
    updateContextCard(activeClassName);
    applyFilters();
    ['student', 'teacher', 'management', 'parent'].forEach(r => updateSavedDraftsUI(r));
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
          try { sessionStorage.setItem('lecole_people_class', activeClassName); } catch (e) {}
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
          try { sessionStorage.setItem('lecole_people_grade', activeGradeId); } catch (e) {}
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

    // 7. Status Filter Tabs (All / Active / Deactivated) across all role toolbars
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.c-status-tab-btn');
      if (!btn) return;
      const group = btn.closest('.c-status-tab-group');
      if (!group) return;
      const st = (btn.getAttribute('data-status-filter') || btn.getAttribute('data-status') || 'all').toLowerCase();
      selectedStatus = st;
      group.querySelectorAll('.c-status-tab-btn').forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      applyFilters();
    });

    // 8. Live Search Inputs across all role toolbars & white context section
    document.addEventListener('input', function (e) {
      if (e.target.matches('.j-role-search-input')) {
        searchQuery = e.target.value.trim().toLowerCase();
        applyFilters();
      }
    });

    // 9. Row Status Dropdown style update & persistence
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

          const row = statusWrap.closest('.j-person-row');
          if (row) {
            row.setAttribute('data-status', val);
            applyFilters();
          }

          const role = row?.getAttribute('data-role');
          const id = row?.getAttribute('data-id');

          if (role === 'student' && id) {
            const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
            const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`${basePath}/updateStudentStatus`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
              },
              body: JSON.stringify({ studentIndex: id, status: val, _csrf_token: csrfToken })
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                const sObj = (window.__PEOPLE_DATA__?.students || []).find(s => s.id === id || s.index === id);
                if (sObj) sObj.status = val;
                if (typeof window.showFeedbackBanner === 'function') {
                  window.showFeedbackBanner(`Student ${id} status set to ${val}.`, 'success');
                }
              } else {
                if (typeof window.showFeedbackBanner === 'function') {
                  window.showFeedbackBanner(data.error || 'Failed to update student status.', 'error');
                }
              }
            })
            .catch(() => {
              if (typeof window.showFeedbackBanner === 'function') {
                window.showFeedbackBanner('Network error updating student status.', 'error');
              }
            });
          } else if (role === 'teacher' && id) {
            const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
            const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`${basePath}/updateTeacherStatus`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
              },
              body: JSON.stringify({ staffId: id, status: val, _csrf_token: csrfToken })
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                const tObj = (window.__PEOPLE_DATA__?.teachers || []).find(t => t.id === id || t.staffId === id);
                if (tObj) tObj.status = val;
                if (typeof window.showFeedbackBanner === 'function') {
                  window.showFeedbackBanner(`Teacher ${id} status set to ${val}.`, 'success');
                }
              } else {
                if (typeof window.showFeedbackBanner === 'function') {
                  window.showFeedbackBanner(data.error || 'Failed to update teacher status.', 'error');
                }
              }
            })
            .catch(() => {
              if (typeof window.showFeedbackBanner === 'function') {
                window.showFeedbackBanner('Network error updating teacher status.', 'error');
              }
            });
          } else if (role === 'management' && id) {
            const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
            const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`${basePath}/updateManagementStatus`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
              },
              body: JSON.stringify({ staffId: id, status: val, _csrf_token: csrfToken })
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                const mObj = (window.__PEOPLE_DATA__?.management || []).find(m => m.id === id || m.staffId === id);
                if (mObj) mObj.status = val;
                if (typeof window.showFeedbackBanner === 'function') {
                  window.showFeedbackBanner(`Staff member ${id} status set to ${val}.`, 'success');
                }
              } else {
                if (typeof window.showFeedbackBanner === 'function') {
                  window.showFeedbackBanner(data.error || 'Failed to update staff status.', 'error');
                }
              }
            })
            .catch(() => {
              if (typeof window.showFeedbackBanner === 'function') {
                window.showFeedbackBanner('Network error updating staff status.', 'error');
              }
            });
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

      // 9a. Save Draft button (top-right header, footer, or dropdown foot)
      const draftBtn = e.target.closest('.j-btn-save-draft');
      if (draftBtn) {
        e.preventDefault();
        const role = draftBtn.getAttribute('data-role') ||
                     draftBtn.closest('.j-saved-drafts-wrap')?.getAttribute('data-role') ||
                     (draftBtn.closest('form')?.id === 'j-enrollment-form' ? 'student' :
                      draftBtn.closest('form')?.id === 'j-add-teacher-form' ? 'teacher' :
                      draftBtn.closest('form')?.id === 'j-add-management-form' ? 'management' : 'student');
        const form = draftBtn.closest('form') || document.querySelector(`#j-page-add-${role} form`);
        if (form) savePersonDraft(form, role);
        return;
      }

      // 9b. Saved Drafts Dropdown Toggle button
      const draftsToggle = e.target.closest('.j-saved-drafts-toggle');
      if (draftsToggle) {
        e.preventDefault();
        e.stopPropagation();
        const wrap = draftsToggle.closest('.j-saved-drafts-wrap');
        if (!wrap) return;
        const role = wrap.getAttribute('data-role') || 'student';
        const dropdown = wrap.querySelector('.j-saved-drafts-dropdown');
        const isCurrentlyOpen = wrap.classList.contains('is-open');

        // Close any other open draft dropdowns
        document.querySelectorAll('.j-saved-drafts-wrap.is-open').forEach(w => {
          if (w !== wrap) {
            w.classList.remove('is-open');
            const d = w.querySelector('.j-saved-drafts-dropdown');
            if (d) d.style.display = 'none';
            const t = w.querySelector('.j-saved-drafts-toggle');
            if (t) t.setAttribute('aria-expanded', 'false');
          }
        });

        if (isCurrentlyOpen) {
          wrap.classList.remove('is-open');
          if (dropdown) dropdown.style.display = 'none';
          draftsToggle.setAttribute('aria-expanded', 'false');
        } else {
          wrap.classList.add('is-open');
          if (dropdown) dropdown.style.display = 'flex';
          draftsToggle.setAttribute('aria-expanded', 'true');
          updateSavedDraftsUI(role);
        }
        return;
      }

      // 9c. Delete Draft item inside dropdown
      const delDraftBtn = e.target.closest('.j-btn-delete-draft');
      if (delDraftBtn) {
        e.preventDefault();
        e.stopPropagation();
        const draftId = delDraftBtn.getAttribute('data-id');
        const role = delDraftBtn.getAttribute('data-role') || 'student';
        if (draftId) deletePersonDraft(draftId, role);
        return;
      }

      // 9d. Click Draft Item to restore
      const draftItem = e.target.closest('.j-saved-draft-item');
      if (draftItem) {
        e.preventDefault();
        e.stopPropagation();
        const draftId = draftItem.getAttribute('data-id');
        const role = draftItem.getAttribute('data-role') || 'student';
        const form = draftItem.closest('form') || document.querySelector(`#j-page-add-${role} form`);
        if (form && draftId) {
          loadPersonDraft(form, draftId, role);
        }
        return;
      }

      // 9e. Outside Click - Close all open draft dropdowns
      if (!e.target.closest('.j-saved-drafts-wrap')) {
        document.querySelectorAll('.j-saved-drafts-wrap.is-open').forEach(w => {
          w.classList.remove('is-open');
          const d = w.querySelector('.j-saved-drafts-dropdown');
          if (d) d.style.display = 'none';
          const t = w.querySelector('.j-saved-drafts-toggle');
          if (t) t.setAttribute('aria-expanded', 'false');
        });
      }
    });

    // Close open draft dropdowns on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.j-saved-drafts-wrap.is-open').forEach(w => {
          w.classList.remove('is-open');
          const d = w.querySelector('.j-saved-drafts-dropdown');
          if (d) d.style.display = 'none';
          const t = w.querySelector('.j-saved-drafts-toggle');
          if (t) t.setAttribute('aria-expanded', 'false');
        });
      }
    });

    // 10. Institutional Email Autofill for Staff Accounts (Full Name is entered independently with initials)
    document.addEventListener('input', function (e) {
      if (e.target.matches('.j-autofill-first, .j-autofill-last, .j-first-name-input, .j-last-name-input')) {
        const form = e.target.closest('form');
        if (!form) return;
        const first = form.querySelector('.j-autofill-first, .j-first-name-input')?.value || '';
        const last = form.querySelector('.j-autofill-last, .j-last-name-input')?.value || '';

        // Institutional email autofill for staff
        const instEmail = form.querySelector('.j-teacher-inst-email, .j-mgmt-inst-email');
        if (instEmail) {
          const f = first.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
          const l = last.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
          if (f && l) {
            instEmail.value = `${f}_${l}@lecole.edu`;
          } else if (f || l) {
            instEmail.value = `${f || l}@lecole.edu`;
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

    // 12. Dynamic Class Section Dropdown & Auto Age-to-Grade Cohort Placement
    const studentGradeDropdown = document.getElementById('j-student-grade');
    const studentDobEl = document.getElementById('j-student-dob');

    if (studentDobEl) {
      studentDobEl.addEventListener('datepicker:change', function (e) {
        const iso = e.detail?.value;
        if (!iso) return;
        const birthDate = new Date(iso + 'T00:00:00');
        const now = new Date();
        let age = now.getFullYear() - birthDate.getFullYear();
        const m = now.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < birthDate.getDate())) {
          age--;
        }

        // Standard Sri Lankan / International Age-to-Grade formula:
        // Age 10-11 -> Grade 6, 12 -> Grade 7, 13 -> Grade 8, 14 -> Grade 9, 15 -> Grade 10, 16+ -> Grade 11
        let suggestedGrade = 'Grade 6';
        if (age <= 11) {
          suggestedGrade = 'Grade 6';
        } else if (age === 12) {
          suggestedGrade = 'Grade 7';
        } else if (age === 13) {
          suggestedGrade = 'Grade 8';
        } else if (age === 14) {
          suggestedGrade = 'Grade 9';
        } else if (age === 15) {
          suggestedGrade = 'Grade 10';
        } else {
          suggestedGrade = 'Grade 11';
        }

        if (typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-student-grade', suggestedGrade);
        }
        updateStudentFormClasses(suggestedGrade);

        const hintEl = document.getElementById('j-student-grade-hint');
        if (hintEl) {
          hintEl.textContent = `Suggested for age ${age} (${suggestedGrade})`;
          hintEl.dataset.suggestedGrade = suggestedGrade;
          hintEl.style.color = 'var(--sky, #207C82)';
          hintEl.style.display = 'inline-block';
        }
      });
    }

    if (studentGradeDropdown) {
      studentGradeDropdown.addEventListener('dropdown:change', function (e) {
        const gradeVal = e.detail?.value || 'Grade 6';
        updateStudentFormClasses(gradeVal);

        const hintEl = document.getElementById('j-student-grade-hint');
        if (hintEl) {
          const suggested = hintEl.dataset.suggestedGrade;
          if (suggested && suggested !== gradeVal) {
            hintEl.textContent = `Custom placement (${gradeVal})`;
            hintEl.style.color = 'var(--sunshine, #EA8913)';
            hintEl.style.display = 'inline-block';
          } else if (suggested) {
            hintEl.textContent = `Suggested by age (${gradeVal})`;
            hintEl.style.color = 'var(--sky, #207C82)';
            hintEl.style.display = 'inline-block';
          }
        }
      });
    }

    // 12. Guardian Co-Creation Controls & Idempotency
    document.addEventListener('change', function (e) {
      if (e.target.matches('.j-guardian-mode-radio')) {
        const mode = e.target.value;
        const existingLabel = document.getElementById('j-mode-label-existing');
        const newLabel = document.getElementById('j-mode-label-new');
        const existingFields = document.getElementById('existing-guardian-fields');
        const newFields = document.getElementById('new-guardian-fields');

        if (mode === 'existing') {
          existingLabel?.classList.add('active');
          newLabel?.classList.remove('active');
          if (existingFields) existingFields.style.display = 'block';
          if (newFields) {
            newFields.style.display = 'none';
            newFields.disabled = true;
          }
        } else {
          newLabel?.classList.add('active');
          existingLabel?.classList.remove('active');
          if (existingFields) existingFields.style.display = 'none';
          if (newFields) {
            newFields.style.display = 'block';
            newFields.disabled = false;
          }
        }
      }

      if (e.target.matches('.j-id-type-radio')) {
        const type = e.target.value;
        const nicLabel = document.getElementById('j-id-type-nic-label');
        const passportLabel = document.getElementById('j-id-type-passport-label');
        const nicWrap = document.getElementById('j-guardian-nic-wrap');
        const passportWrap = document.getElementById('j-guardian-passport-wrap');
        const nicInput = document.getElementById('guardian-nic');
        const passportInput = document.getElementById('guardian-passport');

        if (type === 'nic') {
          nicLabel?.classList.add('active');
          passportLabel?.classList.remove('active');
          if (nicWrap) nicWrap.style.display = 'block';
          if (passportWrap) passportWrap.style.display = 'none';
          if (nicInput) nicInput.required = true;
          if (passportInput) {
            passportInput.required = false;
            passportInput.value = '';
          }
        } else {
          passportLabel?.classList.add('active');
          nicLabel?.classList.remove('active');
          if (nicWrap) nicWrap.style.display = 'none';
          if (passportWrap) passportWrap.style.display = 'block';
          if (passportInput) passportInput.required = true;
          if (nicInput) {
            nicInput.required = false;
            nicInput.value = '';
          }
        }
      }
    });

    // Live NIC Digit Counter & Format Validation Helper
    document.addEventListener('input', function (e) {
      if (e.target && e.target.id === 'guardian-nic') {
        const val = e.target.value.trim();
        const countEl = document.getElementById('j-guardian-nic-count');
        const hintEl = document.getElementById('j-guardian-nic-hint');
        if (!countEl) return;

        const len = val.length;
        const is12Digit = /^[0-9]{12}$/.test(val);
        const isOldNic = /^[0-9]{9}[vVxX]$/.test(val);

        if (len === 0) {
          countEl.textContent = '0 / 12 digits';
          countEl.style.color = 'var(--text-subtle, #5C7679)';
          if (hintEl) hintEl.textContent = 'Format: 12 digits (e.g. 198012345678) or 9 digits + V/X';
        } else if (is12Digit) {
          countEl.textContent = '✓ 12-digit NIC valid';
          countEl.style.color = '#1b7936';
          if (hintEl) hintEl.textContent = 'Valid 12-digit Sri Lankan NIC format';
        } else if (isOldNic) {
          countEl.textContent = '✓ 9-digit + letter valid';
          countEl.style.color = '#1b7936';
          if (hintEl) hintEl.textContent = 'Valid classic Sri Lankan NIC format';
        } else if (len > 12) {
          countEl.textContent = `${len} digits (Too long — Sri Lankan NICs have 12 digits)`;
          countEl.style.color = '#c53030';
          if (hintEl) hintEl.textContent = 'Modern NICs have 12 digits. 16 digits is too long.';
        } else {
          countEl.textContent = `${len} / 12 digits`;
          countEl.style.color = 'var(--text-subtle, #5C7679)';
          if (hintEl) hintEl.textContent = 'Format: 12 digits (e.g. 198012345678) or 9 digits + V/X';
        }
      }
    });

    // Date of Birth Calendar Constraints & Unlock Override
    const guardianDob = document.getElementById('guardian-dateOfBirth');
    // Date of Birth Calendar Constraints & Unlock Override
    const unlockStudentDobBtn = document.getElementById('j-unlock-student-dob');
    if (unlockStudentDobBtn) {
      unlockStudentDobBtn.addEventListener('click', function () {
        const studentDobRoot = document.getElementById('j-student-dob');
        const unlockText = document.getElementById('j-unlock-student-dob-text');
        const hint = document.getElementById('j-student-dob-hint');
        if (!studentDobRoot) return;

        const isCurrentlyUnlocked = studentDobRoot.getAttribute('data-unlocked') === 'true';
        if (isCurrentlyUnlocked) {
          studentDobRoot.setAttribute('data-unlocked', 'false');
          if (unlockText) unlockText.textContent = 'Unlock range';
          if (hint) hint.textContent = 'Standard student age range (3–19 yrs) enforced.';
          unlockStudentDobBtn.style.color = 'var(--sky, #207C82)';
        } else {
          studentDobRoot.setAttribute('data-unlocked', 'true');
          if (unlockText) unlockText.textContent = 'Range unlocked';
          if (hint) hint.textContent = 'Full calendar past date range enabled.';
          unlockStudentDobBtn.style.color = 'var(--terracotta, #AF5031)';
        }
        if (typeof studentDobRoot.refreshDatePicker === 'function') {
          studentDobRoot.refreshDatePicker();
        }
      });
    }

    const unlockGuardianDobBtn = document.getElementById('j-unlock-guardian-dob');
    if (unlockGuardianDobBtn) {
      unlockGuardianDobBtn.addEventListener('click', function () {
        const guardianDobRoot = document.getElementById('j-guardian-dob');
        const unlockText = document.getElementById('j-unlock-guardian-dob-text');
        const hint = document.getElementById('j-guardian-dob-hint');
        if (!guardianDobRoot) return;

        const isCurrentlyUnlocked = guardianDobRoot.getAttribute('data-unlocked') === 'true';
        if (isCurrentlyUnlocked) {
          guardianDobRoot.setAttribute('data-unlocked', 'false');
          if (unlockText) unlockText.textContent = 'Unlock range';
          if (hint) hint.textContent = 'Standard adult age range (18–80 yrs) enforced.';
          unlockGuardianDobBtn.style.color = 'var(--terracotta, #AF5031)';
        } else {
          guardianDobRoot.setAttribute('data-unlocked', 'true');
          if (unlockText) unlockText.textContent = 'Range unlocked';
          if (hint) hint.textContent = 'Full calendar past date range enabled.';
          unlockGuardianDobBtn.style.color = 'var(--midnight, #0F414A)';
        }
        if (typeof guardianDobRoot.refreshDatePicker === 'function') {
          guardianDobRoot.refreshDatePicker();
        }
      });
    }

    // 12. Real-time Name Validation: Block numbers from name inputs
    document.addEventListener('input', function (e) {
      if (e.target.matches('.j-name-letters, input[name="fullName"], input[name="firstName"], input[name="lastName"], input[name="emergencyName"], input[name="guardian[fullName]"], input[name="guardian[firstName]"], input[name="guardian[lastName]"], input[name="guardian[emergencyName]"]')) {
        if (/[0-9]/.test(e.target.value)) {
          e.target.value = e.target.value.replace(/[0-9]/g, '');
        }
      }
    });

    // Real-time Phone Syncing & Digit-Only Filtering for all country-code groups

    document.addEventListener('input', function (e) {
      const group = e.target.closest('.c-phone-input-group');
      if (group) syncPhoneGroup(group);
    });

    document.addEventListener('dropdown:change', function (e) {
      const group = e.target.closest('.c-phone-input-group');
      if (group) syncPhoneGroup(group);
    });

    // Initial sync
    syncAllPhoneGroups();

    // Inline Existing Parent Picker in Student Admission
    initInlineParentPicker();

    // 13. Form Submissions
    document.addEventListener('submit', async function (e) {
      const form = e.target.closest('.c-form-card');
      if (!form) return;
      e.preventDefault();

      if (form.id === 'j-enrollment-form') {
        if (form.dataset.saving === 'true') return;
        clearAllFieldErrors(form);

        const validation = validatePersonForm(form, 'student');
        if (!validation.isValid) {
          showFormNotice(form, validation.error, 'error', validation.targetInput);
          return;
        }

        const fullNameInput = form.querySelector('input[name="fullName"]');
        const firstNameInput = form.querySelector('input[name="firstName"]');
        const lastNameInput = form.querySelector('input[name="lastName"]');

        // 4. In-Flight Submission State
        form.dataset.saving = 'true';
        const submitBtn = form.querySelector('#j-enrollment-submit');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = `
            <svg class="c-icon c-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path></svg>
            <span>Enrolling student...</span>
          `;
        }
        showFormNotice(form, 'Admitting student and processing records...', 'info');

        const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
        const formData = new FormData(form);

        const csrf = document.querySelector('input[name="_csrf_token"]')?.value ||
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (csrf) formData.set('_csrf_token', csrf);

        try {
          const response = await fetch(`${basePath}/registerStudent`, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.error || 'Failed to complete admission.');
          }

          // Clear local draft upon successful admission
          try {
            localStorage.removeItem('lecole_enrollment_draft');
            const admittedName = fullNameInput ? fullNameInput.value.trim() : '';
            let sDrafts = getSavedDrafts('student').filter(d => d.title !== admittedName && d.id !== 'legacy_enrollment_draft');
            localStorage.setItem('lecole_saved_drafts_student', JSON.stringify(sDrafts));
            updateSavedDraftsUI('student');
          } catch(e) {}

          const gradeVal = form.querySelector('input[name="grade"]')?.value || 'Grade 6';
          const classVal = form.querySelector('input[name="classSection"]')?.value || '6-A';
          const gradeIdMap = { 'Grade 6': 'g6', 'Grade 7': 'g7', 'Grade 8': 'g8', 'Grade 9': 'g9', 'Grade 10': 'g10', 'Grade 11': 'g11' };
          const gradeId = gradeIdMap[gradeVal] || 'g6';

          const studentIndex = result.indexNo || result.index || result.studentIndex || 'STU-NEW';

          const newStudentData = {
            id: studentIndex,
            name: fullNameInput.value.trim(),
            firstName: firstNameInput.value.trim(),
            lastName: lastNameInput.value.trim(),
            grade: gradeVal,
            gradeId: gradeId,
            className: classVal,
            email: form.querySelector('input[name="guardian[email]"]')?.value.trim() || result.parentEmail || 'Not recorded',
            parentEmail: form.querySelector('input[name="guardian[email]"]')?.value.trim() || result.parentEmail || 'Not recorded',
            parentName: result.parentName || '',
            parentPhone: form.querySelector('input[name="guardian[mobile]"]')?.value || form.querySelector('input[name="guardian[mobileNumber]"]')?.value || '',
            avatar: 'bg-sky-subtle text-sky',
            initials: (firstNameInput.value.trim()[0] || 'S') + (lastNameInput.value.trim()[0] || '')
          };

          // 1. Immediately Close the Add Person Page
          closeAddPersonForm();

          // 2. Switch to Students tab without isolating view to that single class
          switchRoleTab('Students');
          activeGradeId = 'all';
          activeClassName = 'all';
          try {
            sessionStorage.setItem('lecole_people_grade', 'all');
            sessionStorage.setItem('lecole_people_class', 'all');
          } catch (e) {}
          if (typeof window.setDropdownValue === 'function') {
            window.setDropdownValue('j-select-grade', 'all');
          }
          renderClassChips('all');
          updateContextCard('all');

          // 3. Prepend newly admitted row to the table
          const newRow = insertNewStudentRow(newStudentData);

          // 4. Re-apply filters so row and all students are visible
          applyFilters();

          // 5. Scroll smoothly to new row with highlight pulse
          if (newRow) {
            setTimeout(() => {
              newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
          }

          // 6. Reset form
          form.reset();

          // 7. Show persistent toast banner
          showToast(`Student ${newStudentData.name} (${studentIndex}) enrolled successfully!`, 'success');

        } catch (err) {
          refreshAdmissionKey(form);
          const targetField = findFieldFromErrorMessage(form, err.message);
          showFormNotice(form, err.message, 'error', targetField);
        } finally {
          form.dataset.saving = 'false';
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
              <svg class="c-icon" width="16" height="16"><use href="#icon-checkCircle"/></svg>
              <span id="j-enrollment-submit-label">Enroll student</span>
            `;
          }
        }
        return;
      }

      if (form.id === 'j-add-teacher-form') {
        if (form.dataset.saving === 'true') return;
        clearAllFieldErrors(form);

        const validation = validatePersonForm(form, 'teacher');
        if (!validation.isValid) {
          showFormNotice(form, validation.error, 'error', validation.targetInput);
          return;
        }

        form.dataset.saving = 'true';
        const submitBtn = form.querySelector('#j-teacher-submit');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = `
            <svg class="c-icon c-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path></svg>
            <span>Registering teacher...</span>
          `;
        }
        showFormNotice(form, 'Registering teacher and provisioning accounts...', 'info');

        const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
        const formData = new FormData(form);
        const csrf = document.querySelector('input[name="_csrf_token"]')?.value ||
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (csrf) formData.set('_csrf_token', csrf);

        try {
          const response = await fetch(`${basePath}/registerTeacher`, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.error || 'Failed to complete teacher registration.');
          }

          // Clear local draft upon successful registration
          try {
            const registeredName = result.teacher?.name || form.querySelector('input[name="fullName"]')?.value.trim() || '';
            let tDrafts = getSavedDrafts('teacher').filter(d => d.title !== registeredName);
            localStorage.setItem('lecole_saved_drafts_teacher', JSON.stringify(tDrafts));
            updateSavedDraftsUI('teacher');
          } catch(e) {}

          closeAddPersonForm();
          switchRoleTab('Teachers');
          const newRow = insertNewTeacherRow(result.teacher);
          applyFilters();

          if (newRow) {
            setTimeout(() => {
              newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
          }

          form.reset();
          showToast(`Teacher ${result.teacher.name} (${result.teacher.id}) registered successfully!`, 'success');
        } catch (err) {
          const targetField = findFieldFromErrorMessage(form, err.message);
          showFormNotice(form, err.message, 'error', targetField);
        } finally {
          form.dataset.saving = 'false';
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            `;
          }
        }
        return;
      }

      if (form.id === 'j-add-management-form') {
        if (form.dataset.saving === 'true') return;
        clearAllFieldErrors(form);

        const validation = validatePersonForm(form, 'management');
        if (!validation.isValid) {
          showFormNotice(form, validation.error, 'error', validation.targetInput);
          return;
        }

        form.dataset.saving = 'true';
        const submitBtn = form.querySelector('#j-mgmt-submit');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = `
            <svg class="c-icon c-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path></svg>
            <span>Registering staff member...</span>
          `;
        }
        showFormNotice(form, 'Registering management staff and provisioning accounts...', 'info');

        const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
        const formData = new FormData(form);
        const csrf = document.querySelector('input[name="_csrf_token"]')?.value ||
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (csrf) formData.set('_csrf_token', csrf);

        try {
          const response = await fetch(`${basePath}/registerManagement`, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          });
          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.error || 'Failed to complete management registration.');
          }

          // Clear local draft upon successful registration
          try {
            const registeredName = result.management?.name || form.querySelector('input[name="fullName"]')?.value.trim() || '';
            let mDrafts = getSavedDrafts('management').filter(d => d.title !== registeredName);
            localStorage.setItem('lecole_saved_drafts_management', JSON.stringify(mDrafts));
            updateSavedDraftsUI('management');
          } catch(e) {}

          closeAddPersonForm();
          switchRoleTab('Management Panel');
          const newRow = insertNewManagementRow(result.management);
          applyFilters();

          if (newRow) {
            setTimeout(() => {
              newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
          }

          form.reset();
          showToast(`Staff member ${result.management.name} (${result.management.id}) registered successfully!`, 'success');
        } catch (err) {
          const targetField = findFieldFromErrorMessage(form, err.message);
          showFormNotice(form, err.message, 'error', targetField);
        } finally {
          form.dataset.saving = 'false';
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
              <svg class="c-icon" width="16" height="16"><use href="#icon-save"/></svg>
              <span>Save Account</span>
            `;
          }
        }
        return;
      }
    });
  }

  function openAddPersonForm(role) {
    if (role === 'parent') {
      // Intercept: Parent accounts are enrolled with their student
      openAddPersonForm('student');
      const studentForm = document.getElementById('j-enrollment-form');
      if (studentForm) {
        showFormNotice(
          studentForm,
          'Parent and guardian accounts are registered alongside their child during student admission. Please complete the student and guardian details below.',
          'info'
        );
        setTimeout(() => {
          const noticeEl = studentForm.querySelector('.j-form-notice');
          if (noticeEl) {
            noticeEl.style.transition = 'opacity 0.4s ease';
            noticeEl.style.opacity = '0';
            setTimeout(() => {
              noticeEl.style.display = 'none';
              noticeEl.style.opacity = '1';
            }, 400);
          }
        }, 3000);
      }
      return;
    }

    if (panelEl) panelEl.style.display = 'none';
    const pageHeader = document.querySelector('.c-page-header');
    if (pageHeader) pageHeader.style.display = 'none';

    document.querySelectorAll('.j-page-add-person').forEach(p => p.style.display = 'none');
    const targetPage = document.getElementById('j-page-add-' + role);
    if (targetPage) {
      targetPage.style.display = 'block';
      if (role === 'student') {
        initInlineParentPicker();
        checkAndRestoreEnrollmentDraft(document.getElementById('j-enrollment-form'));
      }
      updateSavedDraftsUI(role);
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

  window.openAddPersonForm = openAddPersonForm;
  window.closeAddPersonForm = closeAddPersonForm;

  // Real-time Phone Syncing & Digit-Only Filtering for all country-code groups
  function syncPhoneGroup(group) {
    if (!group) return;
    const hiddenCode = group.querySelector('input[type="hidden"][name*="CountryCode"], input[type="hidden"][name*="countryCode"]') ||
                       group.querySelector('.c-select input[type="hidden"]');
    const numInput = group.querySelector('input[type="tel"]');
    const fullHidden = group.querySelector('input[type="hidden"]:not([name*="CountryCode"]):not([name*="countryCode"]):not(.c-select input)');
    if (!numInput) return;
    const code = hiddenCode?.value || '+94';
    let clean = numInput.value.replace(/\D/g, '');
    if (code === '+94' && clean.startsWith('0')) {
      clean = clean.replace(/^0+/, '');
    }
    if (code === '+94' && clean.length > 9) {
      clean = clean.slice(0, 9);
    } else if (clean.length > 12) {
      clean = clean.slice(0, 12);
    }
    if (numInput.value !== clean) {
      numInput.value = clean;
    }
    if (fullHidden) {
      fullHidden.value = clean ? `${code}${clean}` : '';
    }
  }

  function syncAllPhoneGroups(scope = document) {
    if (!scope) return;
    scope.querySelectorAll('.c-phone-input-group').forEach(syncPhoneGroup);
  }

  function clearAllFieldErrors(form) {
    if (!form) return;
    form.querySelectorAll('.c-field-inline-error').forEach(el => el.remove());
    form.querySelectorAll('.c-input-invalid').forEach(el => el.classList.remove('c-input-invalid'));
  }

  function refreshAdmissionKey(form) {
    if (!form) return;
    const keyInput = form.querySelector('input[name="admissionKey"]');
    if (keyInput) {
      const bytes = new Uint8Array(16);
      if (window.crypto && window.crypto.getRandomValues) {
        window.crypto.getRandomValues(bytes);
        keyInput.value = Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
      } else {
        keyInput.value = Math.random().toString(36).substring(2) + Math.random().toString(36).substring(2);
      }
    }
  }

  function findFieldFromErrorMessage(form, msg) {
    if (!form || !msg) return null;
    const m = msg.toLowerCase();
    if (m.includes('birth certificate') || m.includes('birth cert')) {
      return form.querySelector('[name="birthCertificateNumber"]');
    }
    if (m.includes('emergency contact') || m.includes('emergency')) {
      return form.querySelector('[name="guardian[emergencyContact]"]') || form.querySelector('[name="emergencyPhone"]') || form.querySelector('[name="guardian[emergencyName]"]') || form.querySelector('[name="emergencyName"]');
    }
    if (m.includes('nic') || m.includes('national id') || m.includes('passport')) {
      return form.querySelector('[name="guardian[nic]"]') || form.querySelector('[name="nic"]') || form.querySelector('[name="guardian[passport]"]');
    }
    if (m.includes('mobile') || m.includes('phone') || m.includes('contact number')) {
      return form.querySelector('[name="guardian[mobileNumber]"]') || form.querySelector('[name="guardian[mobile]"]') || form.querySelector('[name="phone"]');
    }
    if (m.includes('date of birth') || m.includes('dob') || m.includes('age')) {
      return form.querySelector('[name="dateOfBirth"]') || form.querySelector('[name="guardian[dateOfBirth]"]');
    }
    if (m.includes('first name')) {
      return form.querySelector('[name="firstName"]');
    }
    if (m.includes('last name')) {
      return form.querySelector('[name="lastName"]');
    }
    if (m.includes('full name') || m.includes('name')) {
      return form.querySelector('[name="fullName"]') || form.querySelector('[name="guardian[fullName]"]');
    }
    if (m.includes('address') || m.includes('residential')) {
      return form.querySelector('[name="homeAddress"]') || form.querySelector('[name="address"]') || form.querySelector('[name="guardian[address]"]');
    }
    if (m.includes('email')) {
      return form.querySelector('[name="personalEmail"]') || form.querySelector('[name="guardian[email]"]') || form.querySelector('[name="email"]');
    }
    return null;
  }

  function showFormNotice(form, message, type, targetInput = null) {
    if (!form) return;
    const noticeEl = form.querySelector('.j-form-notice');
    const footerNoticeEl = form.querySelector('#j-enrollment-footer-notice, .j-form-footer-notice');

    const updateEl = (el) => {
      if (!el) return;
      el.textContent = message;
      el.style.display = 'flex';
      el.style.opacity = '1';
      if (type === 'success') {
        el.style.background = 'rgba(75, 91, 52, 0.12)';
        el.style.color = 'var(--moss, #4B5B34)';
        el.style.border = '1px solid rgba(75, 91, 52, 0.25)';
      } else if (type === 'info') {
        el.style.background = 'rgba(32, 124, 130, 0.12)';
        el.style.color = 'var(--sky, #207C82)';
        el.style.border = '1px solid rgba(32, 124, 130, 0.25)';
      } else {
        el.style.background = 'rgba(175, 80, 49, 0.12)';
        el.style.color = '#c53030';
        el.style.border = '1px solid rgba(175, 80, 49, 0.25)';
      }
    };

    updateEl(noticeEl);
    updateEl(footerNoticeEl);

    if (type !== 'error') {
      clearAllFieldErrors(form);
    }

    if (targetInput) {
      const formGroup = targetInput.closest('.c-form-group') || targetInput.parentElement;
      if (formGroup) {
        const oldInline = formGroup.querySelector('.c-field-inline-error');
        if (oldInline) oldInline.remove();
      }

      if (type === 'error' && formGroup) {
        const badge = document.createElement('div');
        badge.className = 'c-field-inline-error';
        badge.innerHTML = `
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <span>${escapeHtml(message)}</span>
        `;
        const insertRef = targetInput.closest('.c-input-wrap') || targetInput.closest('.c-select') || targetInput;
        if (insertRef && insertRef.parentNode) {
          insertRef.parentNode.insertBefore(badge, insertRef.nextSibling);
        } else {
          formGroup.appendChild(badge);
        }

        const clearHandler = () => {
          badge.remove();
          targetInput.classList.remove('c-input-invalid');
          targetInput.removeEventListener('input', clearHandler);
          targetInput.removeEventListener('change', clearHandler);
        };
        targetInput.addEventListener('input', clearHandler);
        targetInput.addEventListener('change', clearHandler);
      }

      targetInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
      targetInput.classList.add('c-input-invalid');
      targetInput.focus();
    } else if (type === 'error' && noticeEl) {
      noticeEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function validatePersonForm(form, role) {
    if (!form) return { isValid: false, error: 'Form element not found.' };
    clearAllFieldErrors(form);
    syncAllPhoneGroups(form);

    const emailRegex = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/;

    function checkName(input, label) {
      if (!input) return null;
      const val = input.value.trim();
      if (!val) return null;
      if (/[0-9]/.test(val)) {
        return { isValid: false, error: `${label} cannot contain numbers. Only letters, spaces, hyphens, and dots are permitted.`, targetInput: input };
      }
      return null;
    }

    function checkEmail(input, label) {
      if (!input) return null;
      const val = input.value.trim();
      if (!val) return null;
      if (!val.includes('@')) {
        return { isValid: false, error: `${label} must contain an '@' character.`, targetInput: input };
      }
      if (!emailRegex.test(val)) {
        return { isValid: false, error: `Please enter a valid ${label} (e.g. name@example.com).`, targetInput: input };
      }
      return null;
    }

    function checkPhone(input, label) {
      if (!input) return null;
      const val = input.value.trim();
      if (!val) return null;
      if (/[a-zA-Z]/.test(val)) {
        return { isValid: false, error: `${label} cannot contain letters. Only numbers are allowed.`, targetInput: input };
      }
      const group = input.closest('.c-phone-input-group');
      const hiddenCode = group ? (group.querySelector('input[type="hidden"][name*="CountryCode"], input[type="hidden"][name*="countryCode"]') ||
                         group.querySelector('.c-select input[type="hidden"]')) : null;
      const code = hiddenCode?.value || '+94';
      const clean = val.replace(/\D/g, '');
      if (code === '+94') {
        if (clean.length !== 9) {
          return { isValid: false, error: `${label} must be exactly 9 digits for Sri Lanka (+94), excluding the leading 0 (e.g. 77 123 4567).`, targetInput: input };
        }
      } else {
        if (clean.length < 7 || clean.length > 12) {
          return { isValid: false, error: `${label} must be between 7 and 12 digits.`, targetInput: input };
        }
      }
      return null;
    }

    if (role === 'student' || form.id === 'j-enrollment-form') {
      const fullNameInput = form.querySelector('input[name="fullName"]');
      const firstNameInput = form.querySelector('input[name="firstName"]');
      const lastNameInput = form.querySelector('input[name="lastName"]');
      const studentDobInput = form.querySelector('input[name="dateOfBirth"]');
      const birthCertInput = form.querySelector('input[name="birthCertificateNumber"]');
      const addressInput = form.querySelector('textarea[name="homeAddress"]');

      if (!fullNameInput || !fullNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the student\'s Full Name (with initials).', targetInput: fullNameInput };
      }
      const studentNameErr = checkName(fullNameInput, 'Student Full Name') ||
                             checkName(firstNameInput, 'Student First Name') ||
                             checkName(lastNameInput, 'Student Last Name');
      if (studentNameErr) return studentNameErr;

      if (!firstNameInput || !firstNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the student\'s First Name.', targetInput: firstNameInput };
      }
      if (!lastNameInput || !lastNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the student\'s Last Name.', targetInput: lastNameInput };
      }
      if (!studentDobInput || !studentDobInput.value.trim()) {
        return { isValid: false, error: 'Please select the student\'s Date of Birth.', targetInput: studentDobInput };
      }

      // Student Age Verification (3–19 years)
      const studentDobVal = studentDobInput.value;
      const studentDobRoot = document.getElementById('j-student-dob');
      const isStudentUnlocked = studentDobRoot?.getAttribute('data-unlocked') === 'true';
      if (studentDobVal) {
        const birthDate = new Date(studentDobVal + 'T00:00:00');
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
          age--;
        }
        if ((age < 3 || age > 19) && !isStudentUnlocked) {
          return { isValid: false, error: `Student age (${age} years) is outside standard enrollment range (3–19 years). Click "Unlock range" above if this is an approved exception.`, targetInput: studentDobInput };
        }
      }

      if (!birthCertInput || !birthCertInput.value.trim()) {
        return { isValid: false, error: 'Please enter the student\'s Birth Certificate Number.', targetInput: birthCertInput };
      }
      if (!addressInput || !addressInput.value.trim()) {
        return { isValid: false, error: 'Please enter the student\'s Residential Address.', targetInput: addressInput };
      }

      // Guardian Validation
      const guardianMode = form.querySelector('input[name="guardianMode"]:checked')?.value || 'existing';
      if (guardianMode === 'existing') {
        const parentIdVal = document.getElementById('existing-parent-id')?.value;
        if (!parentIdVal) {
          const searchBox = document.getElementById('j-inline-parent-search');
          return { isValid: false, error: 'Please search and select an existing parent, or switch to "Create New Guardian".', targetInput: searchBox };
        }
      } else {
        // Guardian Age Verification: (18–80 years)
        const guardianDobInput = form.querySelector('input[name="guardian[dateOfBirth]"]');
        const guardianDobVal = guardianDobInput?.value;
        const guardianDobRoot = document.getElementById('j-guardian-dob');
        const isGuardianUnlocked = guardianDobRoot?.getAttribute('data-unlocked') === 'true';
        if (guardianDobVal) {
          const birthDate = new Date(guardianDobVal + 'T00:00:00');
          const today = new Date();
          let gAge = today.getFullYear() - birthDate.getFullYear();
          const m = today.getMonth() - birthDate.getMonth();
          if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            gAge--;
          }
          if ((gAge < 18 || gAge > 80) && !isGuardianUnlocked) {
            return { isValid: false, error: `Guardian age (${gAge} years) is outside standard adult range (18–80 years). Click "Unlock range" above if this is an approved exception.`, targetInput: guardianDobInput };
          }
        }

        const gFullNameInput = form.querySelector('[name="guardian[fullName]"]');
        const gFirstNameInput = form.querySelector('[name="guardian[firstName]"]');
        const gLastNameInput = form.querySelector('[name="guardian[lastName]"]');
        const gEmNameInput = form.querySelector('[name="guardian[emergencyName]"]');
        const gEmailInput = form.querySelector('[name="guardian[email]"]');
        const gMobileInput = form.querySelector('[name="guardian[mobileNumber]"]') || document.getElementById('guardian-mobile');
        const gEmPhoneInput = form.querySelector('[name="guardian[emergencyNumber]"]') || form.querySelector('[name="guardian[emergencyContact]"]');

        const requiredNew = [
          { name: 'guardian[fullName]', label: 'Guardian Full Name' },
          { name: 'guardian[firstName]', label: 'Guardian First Name' },
          { name: 'guardian[lastName]', label: 'Guardian Last Name' },
          { name: 'guardian[dateOfBirth]', label: 'Guardian Date of Birth' },
          { name: 'guardian[occupation]', label: 'Guardian Occupation' },
          { name: 'guardian[mobileNumber]', label: 'Guardian Mobile Number' },
          { name: 'guardian[email]', label: 'Guardian Email' },
          { name: 'guardian[emergencyName]', label: 'Secondary Emergency Contact Name' }
        ];

        for (const item of requiredNew) {
          const el = form.querySelector(`[name="${item.name}"]`);
          if (!el || !el.value.trim()) {
            return { isValid: false, error: `Please fill out required guardian field: ${item.label} (*).`, targetInput: el };
          }
        }

        if (!gEmPhoneInput || !gEmPhoneInput.value.trim()) {
          return { isValid: false, error: 'Please enter Secondary Emergency Contact Phone (*).', targetInput: gEmPhoneInput };
        }

        // Check Names (No numbers)
        const gNameErr = checkName(gFullNameInput, 'Guardian Full Name') ||
                         checkName(gFirstNameInput, 'Guardian First Name') ||
                         checkName(gLastNameInput, 'Guardian Last Name') ||
                         checkName(gEmNameInput, 'Secondary Emergency Contact Name');
        if (gNameErr) return gNameErr;

        // Check Email
        const gEmailErr = checkEmail(gEmailInput, 'Guardian Personal Email');
        if (gEmailErr) return gEmailErr;

        // Check Phones (Digits only, 9 digits for +94)
        const gPhoneErr = checkPhone(gMobileInput, 'Guardian Mobile Number');
        if (gPhoneErr) return gPhoneErr;

        const gEmPhoneErr = checkPhone(gEmPhoneInput, 'Secondary Emergency Contact Phone');
        if (gEmPhoneErr) return gEmPhoneErr;

        // Secondary emergency contact cannot be the parent/guardian
        const pName = gFullNameInput?.value.trim().toLowerCase();
        const emName = gEmNameInput?.value.trim().toLowerCase();
        const pPhone = (gMobileInput?.value || '').replace(/\D/g, '').slice(-7);
        const emPhone = (gEmPhoneInput?.value || '').replace(/\D/g, '').slice(-7);

        if (emName && pName && emName === pName) {
          return { isValid: false, error: 'Secondary emergency contact cannot have the same name as the parent/guardian.', targetInput: gEmNameInput };
        }
        if (emPhone && pPhone && emPhone === pPhone) {
          return { isValid: false, error: 'Secondary emergency contact phone cannot be the same as the parent\'s contact number.', targetInput: gEmPhoneInput };
        }

        // Check NIC or Passport
        const nicInput = form.querySelector('input[name="guardian[nic]"]');
        const passportInput = form.querySelector('input[name="guardian[passport]"]');
        if ((!nicInput || !nicInput.value.trim()) && (!passportInput || !passportInput.value.trim())) {
          return { isValid: false, error: 'Please provide either a National ID (NIC) or Passport number for the guardian.', targetInput: nicInput || passportInput };
        }

        if (nicInput && nicInput.value.trim()) {
          const nv = nicInput.value.trim();
          const is12 = /^[0-9]{12}$/.test(nv);
          const is9v = /^[0-9]{9}[vVxX]$/.test(nv);
          if (!is12 && !is9v) {
            return { isValid: false, error: `Entered NIC has ${nv.length} characters. A modern Sri Lankan NIC must have exactly 12 digits (e.g. 198012345678) or 9 digits followed by V/X (e.g. 801234567V).`, targetInput: nicInput };
          }
        }
      }

      return { isValid: true };
    }

    if (role === 'teacher' || form.id === 'j-add-teacher-form') {
      const fullNameInput = form.querySelector('input[name="fullName"]');
      const firstNameInput = form.querySelector('input[name="firstName"]');
      const lastNameInput = form.querySelector('input[name="lastName"]');
      const nicInput = form.querySelector('input[name="nic"]');
      const dobInput = form.querySelector('input[name="dateOfBirth"]');
      const phoneInput = form.querySelector('input[name="phoneNumber"]') || form.querySelector('input[name="phone"]');
      const emailInput = form.querySelector('input[name="personalEmail"]');
      const subjectsInput = form.querySelector('input[name="subjects"]');
      const expInput = form.querySelector('input[name="experience"]');
      const joinDateInput = form.querySelector('input[name="joinDate"]');
      const emNameInput = form.querySelector('input[name="emergencyName"]');
      const emPhoneInput = form.querySelector('input[name="emergencyPhoneNumber"]') || form.querySelector('input[name="emergencyPhone"]');

      if (!fullNameInput || !fullNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the teacher\'s Full Name.', targetInput: fullNameInput };
      }
      if (!firstNameInput || !firstNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the teacher\'s First Name.', targetInput: firstNameInput };
      }
      if (!lastNameInput || !lastNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the teacher\'s Last Name.', targetInput: lastNameInput };
      }

      // Check Names (No numbers)
      const tNameErr = checkName(fullNameInput, 'Teacher Full Name') ||
                       checkName(firstNameInput, 'Teacher First Name') ||
                       checkName(lastNameInput, 'Teacher Last Name') ||
                       checkName(emNameInput, 'Emergency Contact Name');
      if (tNameErr) return tNameErr;

      if (!nicInput || !nicInput.value.trim()) {
        return { isValid: false, error: 'Please enter the teacher\'s National Identity Card (NIC) number.', targetInput: nicInput };
      }

      const nicVal = nicInput.value.trim();
      const is12 = /^[0-9]{12}$/.test(nicVal);
      const is9v = /^[0-9]{9}[vVxX]$/.test(nicVal);
      if (!is12 && !is9v) {
        return { isValid: false, error: 'Please enter a valid Sri Lankan NIC (12 digits modern, or 9 digits followed by V/X).', targetInput: nicInput };
      }

      if (!dobInput || !dobInput.value.trim()) {
        return { isValid: false, error: 'Please select the teacher\'s Date of Birth.', targetInput: dobInput };
      }

      // Age bounds verification (21–65 years)
      const birthDate = new Date(dobInput.value.trim() + 'T00:00:00');
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      if (age < 21 || age > 65) {
        return { isValid: false, error: `Teacher age (${age} years) is outside acceptable faculty employment range (21–65 years).`, targetInput: dobInput };
      }

      if (!phoneInput || !phoneInput.value.trim()) {
        return { isValid: false, error: 'Please enter the teacher\'s Mobile Number.', targetInput: phoneInput };
      }
      const tPhoneErr = checkPhone(phoneInput, 'Teacher Mobile Number');
      if (tPhoneErr) return tPhoneErr;

      if (!emailInput || !emailInput.value.trim()) {
        return { isValid: false, error: 'Please enter the teacher\'s Personal Email.', targetInput: emailInput };
      }
      const tEmailErr = checkEmail(emailInput, 'Teacher Personal Email');
      if (tEmailErr) return tEmailErr;

      if (!subjectsInput || !subjectsInput.value.trim()) {
        return { isValid: false, error: 'Please enter the subjects qualified to teach.', targetInput: subjectsInput };
      }
      if (!expInput || expInput.value === '') {
        return { isValid: false, error: 'Please enter years of experience.', targetInput: expInput };
      }
      if (!joinDateInput || !joinDateInput.value.trim()) {
        return { isValid: false, error: 'Please select the Join Date.', targetInput: joinDateInput };
      }
      if (!emNameInput || !emNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter an Emergency Contact Name.', targetInput: emNameInput };
      }
      if (!emPhoneInput || !emPhoneInput.value.trim()) {
        return { isValid: false, error: 'Please enter an Emergency Contact Number.', targetInput: emPhoneInput };
      }
      const tEmPhoneErr = checkPhone(emPhoneInput, 'Emergency Contact Number');
      if (tEmPhoneErr) return tEmPhoneErr;

      // Anti-self-reference validation
      const tName = fullNameInput.value.trim().toLowerCase();
      const emName = emNameInput.value.trim().toLowerCase();
      const tDigits = phoneInput.value.replace(/\D/g, '').slice(-7);
      const emDigits = emPhoneInput.value.replace(/\D/g, '').slice(-7);
      if (emName && tName && emName === tName) {
        return { isValid: false, error: 'Emergency contact person cannot be the same as the staff member.', targetInput: emNameInput };
      }
      if (emDigits && tDigits && emDigits === tDigits) {
        return { isValid: false, error: 'Emergency contact phone number cannot be the same as the staff member\'s mobile number.', targetInput: emPhoneInput };
      }

      return { isValid: true };
    }

    if (role === 'management' || form.id === 'j-add-management-form') {
      const fullNameInput = form.querySelector('input[name="fullName"]');
      const firstNameInput = form.querySelector('input[name="firstName"]');
      const lastNameInput = form.querySelector('input[name="lastName"]');
      const nicInput = form.querySelector('input[name="nic"]');
      const phoneInput = form.querySelector('input[name="phoneNumber"]') || form.querySelector('input[name="phone"]');
      const emailInput = form.querySelector('input[name="personalEmail"]');
      const joinDateInput = form.querySelector('input[name="joinDate"]');
      const emNameInput = form.querySelector('input[name="emergencyName"]');
      const emPhoneInput = form.querySelector('input[name="emergencyPhoneNumber"]') || form.querySelector('input[name="emergencyPhone"]');

      if (!fullNameInput || !fullNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the staff member\'s Full Name.', targetInput: fullNameInput };
      }
      if (!firstNameInput || !firstNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the staff member\'s First Name.', targetInput: firstNameInput };
      }
      if (!lastNameInput || !lastNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the staff member\'s Last Name.', targetInput: lastNameInput };
      }

      // Check Names (No numbers)
      const mNameErr = checkName(fullNameInput, 'Staff Member Full Name') ||
                       checkName(firstNameInput, 'Staff Member First Name') ||
                       checkName(lastNameInput, 'Staff Member Last Name') ||
                       checkName(emNameInput, 'Emergency Contact Name');
      if (mNameErr) return mNameErr;

      if (!nicInput || !nicInput.value.trim()) {
        return { isValid: false, error: 'Please enter the National Identity Card (NIC) number.', targetInput: nicInput };
      }

      const nicVal = nicInput.value.trim();
      const is12 = /^[0-9]{12}$/.test(nicVal);
      const is9v = /^[0-9]{9}[vVxX]$/.test(nicVal);
      if (!is12 && !is9v) {
        return { isValid: false, error: 'Please enter a valid Sri Lankan NIC (12 digits modern, or 9 digits followed by V/X).', targetInput: nicInput };
      }

      if (!phoneInput || !phoneInput.value.trim()) {
        return { isValid: false, error: 'Please enter the Contact Number.', targetInput: phoneInput };
      }
      const mPhoneErr = checkPhone(phoneInput, 'Contact Number');
      if (mPhoneErr) return mPhoneErr;

      if (!emailInput || !emailInput.value.trim()) {
        return { isValid: false, error: 'Please enter the Personal Email.', targetInput: emailInput };
      }
      const mEmailErr = checkEmail(emailInput, 'Personal Email');
      if (mEmailErr) return mEmailErr;

      if (!joinDateInput || !joinDateInput.value.trim()) {
        return { isValid: false, error: 'Please select the Join Date.', targetInput: joinDateInput };
      }
      if (!emNameInput || !emNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter an Emergency Contact Name.', targetInput: emNameInput };
      }
      if (!emPhoneInput || !emPhoneInput.value.trim()) {
        return { isValid: false, error: 'Please enter an Emergency Contact Number.', targetInput: emPhoneInput };
      }
      const mEmPhoneErr = checkPhone(emPhoneInput, 'Emergency Contact Number');
      if (mEmPhoneErr) return mEmPhoneErr;

      // Anti-self-reference validation
      const mName = fullNameInput.value.trim().toLowerCase();
      const emName = emNameInput.value.trim().toLowerCase();
      const mDigits = phoneInput.value.replace(/\D/g, '').slice(-7);
      const emDigits = emPhoneInput.value.replace(/\D/g, '').slice(-7);
      if (emName && mName && emName === mName) {
        return { isValid: false, error: 'Emergency contact person cannot be the same as the staff member.', targetInput: emNameInput };
      }
      if (emDigits && mDigits && emDigits === mDigits) {
        return { isValid: false, error: 'Emergency contact phone number cannot be the same as the staff member\'s contact number.', targetInput: emPhoneInput };
      }

      return { isValid: true };
    }

    if (role === 'parent' || form.id === 'j-add-parent-form') {
      const fullNameInput = form.querySelector('input[name="fullName"]');
      if (fullNameInput && !fullNameInput.value.trim()) {
        return { isValid: false, error: 'Please enter the parent\'s Full Name.', targetInput: fullNameInput };
      }
      return { isValid: true };
    }

    return { isValid: true };
  }

  function escapeDraftHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getSavedDrafts(role) {
    if (!role) role = 'student';
    let drafts = [];
    try {
      const raw = localStorage.getItem('lecole_saved_drafts_' + role);
      if (raw) drafts = JSON.parse(raw);
    } catch (e) {
      drafts = [];
    }

    // Auto-migrate legacy single student draft if exists and not present in array
    if (role === 'student') {
      try {
        const legacyRaw = localStorage.getItem('lecole_enrollment_draft');
        if (legacyRaw) {
          const legacyObj = JSON.parse(legacyRaw);
          if (legacyObj && legacyObj.fields) {
            const hasLegacy = drafts.some(d => d.id === 'legacy_enrollment_draft');
            if (!hasLegacy && Object.keys(legacyObj.fields).length > 0) {
              const f = legacyObj.fields;
              const name = f.fullName || (f.firstName ? f.firstName + ' ' + (f.lastName || '') : 'Presaved Student Draft');
              drafts.push({
                id: 'legacy_enrollment_draft',
                role: 'student',
                title: name,
                subtitle: f.grade ? `${f.grade} • ${f.classSection || ''}` : 'Restored draft',
                dateStr: 'Recent',
                timeStr: 'Previous session',
                timestamp: Date.now() - 3600000,
                fields: f,
                customState: {
                  existingParentId: f._existingParentId || '',
                  gender: f.gender || '',
                  grade: f.grade || '',
                  classSection: f.classSection || '',
                  religion: f.religion || ''
                }
              });
            }
          }
        }
      } catch (e) {}
    }

    return Array.isArray(drafts) ? drafts : [];
  }

  function updateSavedDraftsUI(role) {
    if (!role) return;
    const wraps = document.querySelectorAll(`.j-saved-drafts-wrap[data-role="${role}"]`);
    const drafts = getSavedDrafts(role);
    const count = drafts.length;

    wraps.forEach(wrap => {
      const badge = wrap.querySelector('.j-drafts-count');
      if (badge) {
        badge.textContent = count;
      }
      if (count > 0) {
        wrap.classList.add('has-drafts');
      } else {
        wrap.classList.remove('has-drafts');
      }

      const countLabel = wrap.querySelector('.j-drafts-count-label');
      if (countLabel) {
        countLabel.textContent = `${count} ${count === 1 ? 'draft' : 'drafts'}`;
      }

      const listEl = wrap.querySelector('.j-saved-drafts-list');
      if (listEl) {
        if (count === 0) {
          listEl.innerHTML = `
            <div class="c-saved-drafts-empty">
              <svg class="c-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.35; margin: 0 auto 0.5rem auto; display: block;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
              <p style="font-weight: 600; font-size: 0.75rem; color: var(--midnight, #0F414A);">No presaved drafts yet</p>
              <p style="font-size: 0.6875rem; color: #64748B; margin-top: 0.25rem;">Complete all required fields and click "Save Draft" to store one.</p>
            </div>
          `;
        } else {
          listEl.innerHTML = drafts.map(draft => {
            const safeTitle = escapeDraftHtml(draft.title || 'Untitled Draft');
            const safeSubtitle = escapeDraftHtml(draft.subtitle || '');
            const timeLabel = draft.timeStr ? `${draft.dateStr ? draft.dateStr + ', ' : ''}${draft.timeStr}` : '';
            return `
              <div class="c-saved-draft-item j-saved-draft-item" data-id="${draft.id}" data-role="${role}" role="button" tabindex="0" title="Click to load draft into form">
                <div class="c-saved-draft-item__icon">
                  <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </div>
                <div class="c-saved-draft-item__content">
                  <div class="c-saved-draft-item__title">${safeTitle}</div>
                  <div class="c-saved-draft-item__meta">${safeSubtitle ? safeSubtitle + ' • ' : ''}${timeLabel}</div>
                </div>
                <button type="button" class="c-saved-draft-item__delete j-btn-delete-draft" data-id="${draft.id}" data-role="${role}" title="Delete this draft" aria-label="Delete draft">
                  <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-trash"/></svg>
                </button>
              </div>
            `;
          }).join('');
        }
      }
    });
  }

  function savePersonDraft(form, role) {
    if (!form) return false;
    if (!role) {
      if (form.id === 'j-enrollment-form') role = 'student';
      else if (form.id === 'j-add-teacher-form') role = 'teacher';
      else if (form.id === 'j-add-management-form') role = 'management';
      else if (form.id === 'j-add-parent-form') role = 'parent';
      else role = 'student';
    }

    // MANDATORY REQUIREMENT: All validations must be met before saving a draft!
    const validation = validatePersonForm(form, role);
    if (!validation.isValid) {
      showFormNotice(form, 'Cannot save draft: ' + validation.error, 'error', validation.targetInput);
      if (typeof showFeedbackBanner === 'function') {
        showFeedbackBanner('Draft not saved: ' + validation.error, 'error', 4000);
      } else {
        showToast('Draft not saved: ' + validation.error, 'error');
      }
      return false;
    }

    // Gather form field data
    const formData = new FormData(form);
    const fields = {};
    for (const [k, v] of formData.entries()) {
      if (k !== '_csrf_token' && k !== 'admissionKey' && !k.endsWith('[]')) {
        fields[k] = v;
      }
    }

    // Custom state
    const customState = {};
    if (role === 'student') {
      customState.existingParentId = document.getElementById('existing-parent-id')?.value || '';
      customState.gender = form.querySelector('input[name="gender"]')?.value || '';
      customState.grade = form.querySelector('input[name="grade"]')?.value || '';
      customState.classSection = form.querySelector('input[name="classSection"]')?.value || '';
      customState.religion = form.querySelector('input[name="religion"]')?.value || '';
    } else if (role === 'teacher') {
      const qualRows = form.querySelectorAll('#j-teacher-qual-fields .j-qual-row');
      const quals = [];
      qualRows.forEach(row => {
        const title = row.querySelector('input[name="qualTitle[]"]')?.value || '';
        const inst = row.querySelector('input[name="qualInstitution[]"]')?.value || '';
        const yr = row.querySelector('input[name="qualYear[]"]')?.value || '';
        if (title || inst || yr) {
          quals.push({ title, inst, yr });
        }
      });
      customState.qualifications = quals;
    }

    const fullName = form.querySelector('input[name="fullName"]')?.value.trim() || '';
    const firstName = form.querySelector('input[name="firstName"]')?.value.trim() || '';
    const lastName = form.querySelector('input[name="lastName"]')?.value.trim() || '';
    const title = fullName || (firstName + ' ' + lastName).trim() || (role.charAt(0).toUpperCase() + role.slice(1) + ' Draft');

    let subtitle = '';
    if (role === 'student') {
      const g = customState.grade || form.querySelector('input[name="grade"]')?.value || '';
      const c = customState.classSection || form.querySelector('input[name="classSection"]')?.value || '';
      subtitle = (g && c) ? `${g} • ${c}` : (g || 'Student record');
    } else if (role === 'teacher') {
      const subj = form.querySelector('input[name="subjects"]')?.value.trim() || '';
      subtitle = subj ? `Subjects: ${subj}` : 'Teacher account';
    } else if (role === 'management') {
      const nic = form.querySelector('input[name="nic"]')?.value.trim() || '';
      subtitle = nic ? `NIC: ${nic}` : 'Staff account';
    } else {
      subtitle = 'Draft record';
    }

    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const dateStr = now.toLocaleDateString([], { month: 'short', day: 'numeric' });

    const newDraft = {
      id: 'draft_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
      role: role,
      title: title,
      subtitle: subtitle,
      dateStr: dateStr,
      timeStr: timeStr,
      timestamp: Date.now(),
      fields: fields,
      customState: customState
    };

    const drafts = getSavedDrafts(role);
    drafts.unshift(newDraft);
    if (drafts.length > 15) drafts.pop();

    try {
      localStorage.setItem('lecole_saved_drafts_' + role, JSON.stringify(drafts));

      if (role === 'student') {
        localStorage.setItem('lecole_enrollment_draft', JSON.stringify({
          savedAt: new Date().toISOString(),
          fields: Object.assign({}, fields, { _existingParentId: customState.existingParentId })
        }));
      }

      // Visual feedback on save buttons
      const draftBtns = form.querySelectorAll('.j-btn-save-draft');
      draftBtns.forEach(btn => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = `
          <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <span style="font-weight:700;">Draft Saved!</span>
        `;
        setTimeout(() => {
          btn.innerHTML = originalHTML;
        }, 2200);
      });

      updateSavedDraftsUI(role);

      const draftPill = form.querySelector('.j-form-draft-pill') || document.getElementById('j-enrollment-draft-pill');
      if (draftPill) {
        draftPill.textContent = `Draft: ${title} (${timeStr})`;
      }

      if (typeof showFeedbackBanner === 'function') {
        showFeedbackBanner(`Draft for "${title}" successfully saved!`, 'success', 3000);
      } else {
        showToast(`Draft for "${title}" successfully saved!`, 'success');
      }

      showFormNotice(form, `Draft for "${title}" saved locally at ${timeStr}. All validations were met. You can load it anytime from Saved Drafts.`, 'success');
      return true;
    } catch (err) {
      if (typeof showFeedbackBanner === 'function') {
        showFeedbackBanner('Could not save draft: ' + err.message, 'error', 4000);
      } else {
        showToast('Could not save draft: ' + err.message, 'error');
      }
      showFormNotice(form, 'Could not save draft: ' + err.message, 'error');
      return false;
    }
  }

  function saveEnrollmentDraft(form) {
    return savePersonDraft(form, 'student');
  }

  function loadPersonDraft(form, draftId, role) {
    if (!form) return;
    const drafts = getSavedDrafts(role);
    const draft = drafts.find(d => d.id === draftId);
    if (!draft) {
      showToast('Draft not found.', 'error');
      return;
    }

    const fields = draft.fields || {};

    // 1. Populate text, email, tel, number, textarea, and select fields
    for (const [key, val] of Object.entries(fields)) {
      if (!key.startsWith('_')) {
        const input = form.querySelector(`[name="${key}"]`);
        if (input && input.type !== 'file' && input.type !== 'radio') {
          input.value = val;
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }
    }

    // 2. Populate radio buttons
    const radios = form.querySelectorAll('input[type="radio"]');
    radios.forEach(radio => {
      const fieldVal = fields[radio.name];
      if (fieldVal !== undefined && radio.value === fieldVal) {
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    // 3. Custom dropdowns & Role specifics
    if (role === 'student') {
      const cs = draft.customState || {};
      const gender = cs.gender || fields.gender;
      const grade = cs.grade || fields.grade;
      const classSection = cs.classSection || fields.classSection;
      const religion = cs.religion || fields.religion;

      if (gender && typeof window.setDropdownValue === 'function') {
        window.setDropdownValue('j-student-gender', gender);
      }
      if (grade && typeof window.setDropdownValue === 'function') {
        window.setDropdownValue('j-student-grade', grade);
        updateStudentFormClasses(grade);
      }
      if (classSection && typeof window.setDropdownValue === 'function') {
        window.setDropdownValue('j-student-class', classSection);
      }
      if (religion && typeof window.setDropdownValue === 'function') {
        window.setDropdownValue('j-student-religion', religion);
      }

      const guardianMode = fields.guardianMode || (cs.existingParentId ? 'existing' : 'new');
      const gRadio = form.querySelector(`input[name="guardianMode"][value="${guardianMode}"]`);
      if (gRadio) {
        gRadio.checked = true;
        gRadio.dispatchEvent(new Event('change', { bubbles: true }));
      }

      const existingParentId = cs.existingParentId || fields._existingParentId;
      if (existingParentId) {
        const parentIdInput = document.getElementById('existing-parent-id');
        if (parentIdInput) parentIdInput.value = existingParentId;
      }
    } else if (role === 'teacher') {
      const cs = draft.customState || {};
      if (Array.isArray(cs.qualifications) && cs.qualifications.length > 0) {
        const container = form.querySelector('#j-teacher-qual-fields');
        if (container) {
          const existingRows = container.querySelectorAll('.j-qual-row');
          existingRows.forEach(r => r.remove());

          const addBtn = container.querySelector('.j-qual-add');
          cs.qualifications.forEach(q => {
            const div = document.createElement('div');
            div.className = 'j-qual-row';
            div.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr 100px auto; gap: 0.75rem; align-items: end;';
            div.innerHTML = `
              <div class="c-form-field">
                <label class="c-form-field-label">Title / Degree</label>
                <input type="text" class="c-form-input j-qual-input" name="qualTitle[]" value="${escapeDraftHtml(q.title || '')}" />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Institution</label>
                <input type="text" class="c-form-input j-qual-input" name="qualInstitution[]" value="${escapeDraftHtml(q.inst || '')}" />
              </div>
              <div class="c-form-field">
                <label class="c-form-field-label">Year</label>
                <input type="text" class="c-form-input j-qual-input" name="qualYear[]" value="${escapeDraftHtml(q.yr || '')}" />
              </div>
              <button type="button" class="c-btn-solid-tone c-tone-maroon j-qual-remove" style="margin-bottom: 0.25rem; padding: 0.625rem 0.875rem;">Remove</button>
            `;
            if (addBtn) container.insertBefore(div, addBtn);
            else container.appendChild(div);
          });
        }
      }
    }

    // 4. Restore country codes and resync phone groups
    ['guardianCountryCode', 'guardianEmergencyCountryCode', 'teacherCountryCode', 'teacherEmergencyCountryCode', 'mgmtCountryCode', 'mgmtEmergencyCountryCode'].forEach(ccKey => {
      if (fields[ccKey] && typeof window.setDropdownValue === 'function') {
        const dd = form.querySelector(`input[name="${ccKey}"]`)?.closest('.c-select');
        if (dd && dd.id) {
          window.setDropdownValue(dd.id, fields[ccKey]);
        }
      }
    });
    syncAllPhoneGroups(form);

    // Close open draft dropdown
    const wrap = form.querySelector('.j-saved-drafts-wrap') || document.querySelector(`.j-saved-drafts-wrap[data-role="${role}"]`);
    if (wrap) {
      wrap.classList.remove('is-open');
      const dd = wrap.querySelector('.j-saved-drafts-dropdown');
      if (dd) dd.style.display = 'none';
      const toggle = wrap.querySelector('.j-saved-drafts-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    const draftPill = form.querySelector('.j-form-draft-pill') || document.getElementById('j-enrollment-draft-pill');
    if (draftPill) {
      draftPill.textContent = `Draft: ${draft.title}`;
    }

    showToast(`Presaved draft for "${draft.title}" restored!`, 'success');
    showFormNotice(form, `Presaved draft for "${draft.title}" restored successfully. All field data has been loaded.`, 'info');
  }

  function deletePersonDraft(draftId, role) {
    let drafts = getSavedDrafts(role);
    drafts = drafts.filter(d => d.id !== draftId);
    try {
      localStorage.setItem('lecole_saved_drafts_' + role, JSON.stringify(drafts));
      if (role === 'student' && draftId === 'legacy_enrollment_draft') {
        localStorage.removeItem('lecole_enrollment_draft');
      }
      updateSavedDraftsUI(role);
      showToast('Draft removed successfully.', 'info');
    } catch (e) {
      showToast('Could not delete draft: ' + e.message, 'error');
    }
  }

  function checkAndRestoreEnrollmentDraft(form) {
    if (!form) return;
    const raw = localStorage.getItem('lecole_enrollment_draft');
    if (!raw) return;
    try {
      const draftObj = JSON.parse(raw);
      const fields = draftObj.fields;
      if (!fields || Object.keys(fields).length === 0) return;

      const currentFullName = form.querySelector('[name="fullName"]')?.value;
      if (!currentFullName) {
        for (const [key, val] of Object.entries(fields)) {
          if (!key.startsWith('_')) {
            const input = form.querySelector(`[name="${key}"]`);
            if (input && input.type !== 'file' && input.type !== 'radio') {
              input.value = val;
            }
          }
        }

        if (fields.gender && typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-student-gender', fields.gender);
        }
        if (fields.grade && typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-student-grade', fields.grade);
          updateStudentFormClasses(fields.grade);
        }
        if (fields.classSection && typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-student-class', fields.classSection);
        }
        if (fields.religion && typeof window.setDropdownValue === 'function') {
          window.setDropdownValue('j-student-religion', fields.religion);
        }

        const draftPill = document.getElementById('j-enrollment-draft-pill');
        if (draftPill) {
          draftPill.textContent = 'Draft restored';
        }
        showFormNotice(form, 'A previously saved enrollment draft was automatically restored.', 'info');
      }
    } catch (e) {}
  }

  function insertNewStudentRow(studentData) {
    const tbody = document.querySelector('#j-table-student tbody');
    if (!tbody) return null;

    const emptyRow = tbody.querySelector('.c-empty-row');
    if (emptyRow) emptyRow.style.display = 'none';

    const isTeacherView = Boolean(document.querySelector('#j-table-student th')?.textContent.includes('STUDENT NAME'));
    const safeId = String(studentData.id).replace(/[^a-zA-Z0-9_-]/g, '').toLowerCase();

    const tr = document.createElement('tr');
    tr.className = 'c-row-hover-sky j-person-row c-row-newly-added';
    tr.dataset.role = 'student';
    tr.dataset.id = studentData.id;
    tr.dataset.grade = studentData.gradeId || 'g6';
    tr.dataset.class = studentData.className || '6-A';
    tr.dataset.status = 'Active';
    tr.dataset.activities = '';

    const viewBtn = `
      <td class="c-align-right" style="${isTeacherView ? 'text-align:center;' : ''}">
        <div class="c-row-actions" style="justify-content:${isTeacherView ? 'center' : 'flex-end'};">
          <button type="button" class="c-row-action-btn j-open-profile" data-role="student" data-id="${escapeHtml(studentData.id)}" title="View student profile">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
            <span class="c-row-action-label">View</span>
          </button>
        </div>
      </td>`;

    const middleCols = isTeacherView ? `
      <td style="font-size:0.8125rem; font-weight:500; color:var(--midnight, #0F414A);">${escapeHtml(studentData.parentName || 'Parent')}</td>
      <td style="font-size:0.8125rem; font-weight:500; color:rgba(15,65,74,0.8);">${escapeHtml(studentData.parentPhone || 'Not recorded')}</td>
      ${viewBtn}` : `
      <td>
        <div style="min-width: 8.5rem;">
          <div class="c-dropdown c-select c-dropdown--status c-dropdown--status-active j-dropdown" id="j-status-${safeId}">
            <button type="button" class="c-dropdown__trigger c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
              <span class="c-dropdown__trigger-text">Active</span>
              <svg class="c-icon c-dropdown__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="c-dropdown__menu c-select__menu" role="listbox">
              <button type="button" class="c-dropdown__option c-select__option is-selected" role="option" data-value="Active" aria-selected="true">Active</button>
              <button type="button" class="c-dropdown__option c-select__option" role="option" data-value="Deactivated" aria-selected="false">Deactivated</button>
            </div>
          </div>
        </div>
      </td>
      ${viewBtn}`;

    tr.innerHTML = `
      <td>
        <div class="c-person-cell">
          <div class="c-avatar c-avatar-sm ${studentData.avatar}">${escapeHtml(studentData.initials)}</div>
          <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);">${escapeHtml(studentData.name)}</span>
        </div>
      </td>
      <td style="font-size:0.75rem;font-weight:500;color:rgba(15,65,74,0.7);">${escapeHtml(studentData.id)}</td>
      <td><span class="c-tag-muted" style="font-size:11px;font-style:italic;color:rgba(15,65,74,0.4);">None</span></td>
      <td style="font-size:0.75rem;color:rgba(15,65,74,0.8);">
        <span class="c-mail-inline">
          <svg class="c-icon c-icon-muted" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
          <span>${escapeHtml(studentData.email || 'Not recorded')}</span>
        </span>
      </td>
      ${middleCols}
    `;

    tbody.insertBefore(tr, tbody.firstChild);

    if (window.__PEOPLE_DATA__?.students) {
      window.__PEOPLE_DATA__.students.unshift({
        id: studentData.id,
        index: studentData.id,
        name: studentData.name,
        firstName: studentData.firstName || '',
        lastName: studentData.lastName || '',
        email: studentData.email,
        gradeId: studentData.gradeId,
        className: studentData.className,
        status: 'Active',
        activities: []
      });
    }

    const studentRows = tbody.querySelectorAll('.j-person-row');
    if (countNumberEl && activeTabName === 'Students') countNumberEl.textContent = studentRows.length;
    const studentTabBadge = document.querySelector('.j-directory-tab[data-tab="Students"] .c-badge-pill');
    if (studentTabBadge) studentTabBadge.textContent = studentRows.length;

    return tr;
  }

  function insertNewTeacherRow(teacherData) {
    const tbody = document.querySelector('#j-table-teacher tbody');
    if (!tbody) return null;

    const emptyRow = tbody.querySelector('.c-empty-row');
    if (emptyRow) emptyRow.style.display = 'none';

    const safeId = String(teacherData.id).replace(/[^a-zA-Z0-9_-]/g, '').toLowerCase();

    const tr = document.createElement('tr');
    tr.className = 'c-row-hover-sunshine j-person-row c-row-newly-added';
    tr.dataset.role = 'teacher';
    tr.dataset.id = teacherData.id;
    tr.dataset.status = 'Active';
    tr.dataset.subject = teacherData.subject || teacherData.subjects || 'General';
    tr.dataset.classes = (teacherData.classes || []).join(',');
    tr.dataset.tic = teacherData.tic || '';

    tr.innerHTML = `
      <td>
        <div class="c-person-cell">
          <div class="c-avatar c-avatar-md ${teacherData.avatar || 'bg-sunshine text-white'}">${escapeHtml(teacherData.initials || 'TR')}</div>
          <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);">${escapeHtml(teacherData.name || teacherData.fullName)}</span>
        </div>
      </td>
      <td style="font-size:0.75rem;font-weight:700;color:var(--midnight, #0F414A);">${escapeHtml(teacherData.id)}</td>
      <td>
        <p style="font-size:0.75rem;font-weight:600;color:var(--midnight, #0F414A);margin:0;">${escapeHtml(teacherData.subject || teacherData.subjects || 'General')}</p>
      </td>
      <td>
        <p style="font-size:0.75rem;font-weight:600;color:rgba(15,65,74,0.8);margin:0;display:flex;align-items:center;gap:6px;line-height:1.2;">
          <span>${escapeHtml(teacherData.role || 'Teacher')}</span>
        </p>
      </td>
      <td class="c-stack-tight">
        <span class="c-contact-line">
          <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
          <span>${escapeHtml(teacherData.email || teacherData.institutionalEmail || 'Not recorded')}</span>
        </span>
        <span class="c-contact-line" style="margin-top:0.25rem;">
          <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-phone"/></svg>
          <span>${escapeHtml(teacherData.phone || 'Not recorded')}</span>
        </span>
      </td>
      <td>
        <div style="min-width: 8.5rem;">
          <div class="c-dropdown c-select c-dropdown--status c-dropdown--status-active j-dropdown" id="j-status-${safeId}">
            <button type="button" class="c-dropdown__trigger c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
              <span class="c-dropdown__trigger-text">Active</span>
              <svg class="c-icon c-dropdown__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="c-dropdown__menu c-select__menu" role="listbox">
              <button type="button" class="c-dropdown__option c-select__option is-selected" role="option" data-value="Active" aria-selected="true">Active</button>
              <button type="button" class="c-dropdown__option c-select__option" role="option" data-value="Deactivated" aria-selected="false">Deactivated</button>
            </div>
          </div>
        </div>
      </td>
      <td class="c-align-right">
        <div class="c-row-actions" style="justify-content: flex-end;">
          <button type="button" class="c-row-action-btn j-open-profile" data-role="teacher" data-id="${escapeHtml(teacherData.id)}" title="View teacher details">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
            <span class="c-row-action-label">View</span>
          </button>
          <button type="button" class="c-row-action-btn j-edit-profile" data-role="teacher" data-id="${escapeHtml(teacherData.id)}" title="Edit teacher details">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
            <span class="c-row-action-label">Edit</span>
          </button>
        </div>
      </td>
    `;

    tbody.insertBefore(tr, tbody.firstChild);

    if (window.__PEOPLE_DATA__?.teachers) {
      window.__PEOPLE_DATA__.teachers.unshift(teacherData);
    }

    const teacherRows = tbody.querySelectorAll('.j-person-row');
    if (countNumberEl && activeTabName === 'Teachers') countNumberEl.textContent = teacherRows.length;
    const teacherTabBadge = document.querySelector('.j-directory-tab[data-tab="Teachers"] .c-badge-pill');
    if (teacherTabBadge) teacherTabBadge.textContent = teacherRows.length;

    return tr;
  }

  function insertNewManagementRow(mgmtData) {
    const tbody = document.querySelector('#j-table-management tbody');
    if (!tbody) return null;

    const emptyRow = tbody.querySelector('.c-empty-row');
    if (emptyRow) emptyRow.style.display = 'none';

    const safeId = String(mgmtData.id).replace(/[^a-zA-Z0-9_-]/g, '').toLowerCase();

    const tr = document.createElement('tr');
    tr.className = 'c-row-hover-maroon j-person-row c-row-newly-added';
    tr.dataset.role = 'management';
    tr.dataset.id = mgmtData.id;
    tr.dataset.status = 'Active';

    tr.innerHTML = `
      <td>
        <div class="c-person-cell">
          <div class="c-avatar c-avatar-md ${mgmtData.avatar || 'bg-maroon text-white'}">${escapeHtml(mgmtData.initials || 'MG')}</div>
          <div>
            <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);display:block;">${escapeHtml(mgmtData.name || mgmtData.fullName)}</span>
            ${mgmtData.jobTitle || mgmtData.title ? `<p class="c-subtext" style="margin-top:0.125rem;margin-bottom:0;font-size:11px;font-weight:500;color:rgba(15,65,74,0.5);">${escapeHtml(mgmtData.jobTitle || mgmtData.title)}</p>` : ''}
          </div>
        </div>
      </td>
      <td style="font-size:0.75rem;font-weight:700;color:var(--midnight, #0F414A);">${escapeHtml(mgmtData.id)}</td>
      <td class="c-stack-tight">
        <span class="c-contact-line">
          <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
          <span>${escapeHtml(mgmtData.email || mgmtData.institutionalEmail || 'Not recorded')}</span>
        </span>
        <span class="c-contact-line" style="margin-top:0.25rem;">
          <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-phone"/></svg>
          <span>${escapeHtml(mgmtData.phone || 'Not recorded')}</span>
        </span>
      </td>
      <td>
        <div style="min-width: 8.5rem;">
          <div class="c-dropdown c-select c-dropdown--status c-dropdown--status-active j-dropdown" id="j-status-${safeId}">
            <button type="button" class="c-dropdown__trigger c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
              <span class="c-dropdown__trigger-text">Active</span>
              <svg class="c-icon c-dropdown__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="c-dropdown__menu c-select__menu" role="listbox">
              <button type="button" class="c-dropdown__option c-select__option is-selected" role="option" data-value="Active" aria-selected="true">Active</button>
              <button type="button" class="c-dropdown__option c-select__option" role="option" data-value="Deactivated" aria-selected="false">Deactivated</button>
            </div>
          </div>
        </div>
      </td>
      <td class="c-align-right">
        <div class="c-row-actions" style="justify-content: flex-end;">
          <button type="button" class="c-row-action-btn j-open-profile" data-role="management" data-id="${escapeHtml(mgmtData.id)}" title="View staff profile">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
            <span class="c-row-action-label">View</span>
          </button>
          <button type="button" class="c-row-action-btn j-edit-profile" data-role="management" data-id="${escapeHtml(mgmtData.id)}" title="Edit staff profile">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
            <span class="c-row-action-label">Edit</span>
          </button>
        </div>
      </td>
    `;

    tbody.insertBefore(tr, tbody.firstChild);

    if (window.__PEOPLE_DATA__?.management) {
      window.__PEOPLE_DATA__.management.unshift(mgmtData);
    }

    const mgmtRows = tbody.querySelectorAll('.j-person-row');
    if (countNumberEl && activeTabName === 'Management Panel') countNumberEl.textContent = mgmtRows.length;
    const mgmtTabBadge = document.querySelector('.j-directory-tab[data-tab="Management Panel"] .c-badge-pill');
    if (mgmtTabBadge) mgmtTabBadge.textContent = mgmtRows.length;

    return tr;
  }

  function syncPhone() {
    syncAllPhoneGroups();
  }

  function showToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
      return window.showToast(message, type);
    }
    if (typeof window.showFeedbackBanner === 'function') {
      return window.showFeedbackBanner(message, type);
    }
  }

  function initInlineParentPicker() {
    if (typeof window.initInlineParentPicker === 'function') {
      window.initInlineParentPicker();
    }
  }

  function updateStudentFormClasses(gradeVal) {
    const gradeNum = gradeVal.replace(/[^0-9]/g, '') || '6';
    const classDropdown = document.getElementById('j-student-class');
    if (!classDropdown) return;
    const menuEl = classDropdown.querySelector('.c-select__menu, .c-dropdown__menu');
    const labelEl = classDropdown.querySelector('.j-select-value, .c-dropdown__value');
    const inputEl = classDropdown.querySelector('input[name="classSection"]');
    const helperEl = document.getElementById('j-student-class-helper');

    if (helperEl) {
      helperEl.textContent = `Choose a Grade ${gradeNum} class`;
    }

    const sections = (gradeNum === '7' || gradeNum === '9' || gradeNum === '11')
      ? ['A', 'B', 'C']
      : ['A', 'B', 'C', 'D'];

    const newOptions = sections.map(sec => ({
      value: `${gradeNum}-${sec}`,
      label: `${gradeNum}-${sec}`
    }));

    if (menuEl) {
      menuEl.innerHTML = newOptions.map((opt, idx) => `
        <div class="c-select__option c-dropdown__option ${idx === 0 ? 'c-is-selected' : ''}" 
             data-value="${opt.value}" 
             role="option" 
             ${idx === 0 ? 'aria-selected="true"' : ''}>
          <span>${opt.label}</span>
        </div>
      `).join('');
    }

    const firstOpt = newOptions[0];
    if (firstOpt) {
      if (labelEl) {
        labelEl.textContent = firstOpt.label;
        labelEl.classList.remove('c-dropdown__placeholder');
      }
      if (inputEl) inputEl.value = firstOpt.value;
      const trigger = classDropdown.querySelector('.c-select__trigger, .c-dropdown__trigger');
      if (trigger) {
        trigger.classList.add('has-value');
        trigger.classList.remove('is-placeholder');
      }
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

    // Reset status filter to 'All'
    selectedStatus = 'all';
    document.querySelectorAll('.c-status-tab-group').forEach(grp => {
      grp.querySelectorAll('.c-status-tab-btn').forEach(btn => {
        const filter = (btn.getAttribute('data-status-filter') || btn.getAttribute('data-status') || '').toLowerCase();
        btn.classList.toggle('is-active', filter === 'all');
      });
    });

    applyFilters();
  }

  function renderClassChips(gradeId) {
    if (!classChipsWrapEl) return;

    // When 'All Grades' is selected, do not show class chips on the right
    if (gradeId === 'all' || !gradeId) {
      classChipsWrapEl.style.display = 'none';
      classChipsWrapEl.innerHTML = '';
      activeClassName = 'all';
      updateContextCard('all');
      return;
    }

    classChipsWrapEl.style.display = 'flex';

    const gradesData = window.__PEOPLE_DATA__?.grades || [
      { id: 'g6', name: 'Grade 6', classes: ['6-A', '6-B', '6-C', '6-D'] },
      { id: 'g7', name: 'Grade 7', classes: ['7-A', '7-B', '7-C'] },
      { id: 'g8', name: 'Grade 8', classes: ['8-A', '8-B', '8-C', '8-D'] },
      { id: 'g9', name: 'Grade 9', classes: ['9-A', '9-B', '9-C'] },
      { id: 'g10', name: 'Grade 10', classes: ['10-A', '10-B', '10-C', '10-D'] },
      { id: 'g11', name: 'Grade 11', classes: ['11-A', '11-B', '11-C'] }
    ];

    const targetGrade = gradesData.find(g => g.id === gradeId) || gradesData[0];
    let classes = targetGrade?.classes ? [...targetGrade.classes] : [];

    // Natural sort: numeric grade first (6, 7, 8, 9, 10, 11), then section letter (A, B, C...)
    classes.sort((a, b) => {
      const numA = parseInt(String(a).replace(/\D/g, ''), 10) || 0;
      const numB = parseInt(String(b).replace(/\D/g, ''), 10) || 0;
      if (numA !== numB) return numA - numB;
      return String(a).localeCompare(String(b), undefined, { numeric: true, sensitivity: 'base' });
    });

    if (activeClassName !== 'all' && !classes.includes(activeClassName) && activeClassName !== 'Unassigned') {
      activeClassName = 'all';
    }

    const unassignedCount = Array.from(document.querySelectorAll('#j-table-student .j-person-row')).filter(r => {
      const c = r.getAttribute('data-class');
      return !c || c === 'Unassigned';
    }).length;

    let chipsHtml = `
      <button type="button" class="c-class-chip j-class-chip ${activeClassName === 'all' ? 'is-active-chip' : ''}" data-class="all">
        All
      </button>
    `;

    chipsHtml += classes.map(cls => `
      <button type="button" class="c-class-chip j-class-chip ${cls === activeClassName ? 'is-active-chip' : ''}" data-class="${escapeHtml(cls)}">
        ${escapeHtml(cls)}
      </button>
    `).join('');

    if (unassignedCount > 0) {
      chipsHtml += `
        <button type="button" class="c-class-chip c-class-chip--unassigned j-class-chip ${activeClassName === 'Unassigned' ? 'is-active-chip' : ''}" data-class="Unassigned" style="margin-left: 0.5rem; border-color: rgba(127, 3, 3, 0.3); color: var(--maroon, #7F0303);">
          Unassigned <span class="c-badge-pill j-unassigned-badge" style="margin-left: 0.25rem; background: var(--maroon, #7F0303); color: #fff; padding: 1px 6px; border-radius: 10px; font-size: 10px; font-weight: 700;">${unassignedCount}</span>
        </button>
      `;
    }

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

    if (className === 'all') {
      if (contextTitleEl) contextTitleEl.textContent = activeGradeId === 'all' ? 'All Grades' : 'All Classes';
      if (contextEnrollmentEl) contextEnrollmentEl.textContent = 'All enrolled';
      if (contextTeacherEl) contextTeacherEl.textContent = 'Multiple homeroom teachers';
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
          if (activeGradeId && activeGradeId !== 'all' && rowGrade !== activeGradeId) {
            isVisible = false;
          }
          if (isVisible && activeClassName && activeClassName !== 'all' && rowClass !== activeClassName) {
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

      // 3. Status filter
      if (isVisible && selectedStatus !== 'all') {
        const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
        if (selectedStatus === 'active') {
          if (rowStatus !== 'active') isVisible = false;
        } else if (selectedStatus === 'deactivated') {
          if (rowStatus !== 'deactivated' && rowStatus !== 'inactive') isVisible = false;
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
