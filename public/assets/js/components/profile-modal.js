/**
 * =========================================================================
 * L'ÉCOLE — MASTER PERSON PROFILE MODAL CONTROLLER (VIEW & EDIT)
 * =========================================================================
 * Client-side controller for the unified Person Profile Modal dialog.
 * Directly copied & adapted from original Admin/people/app.js lines 1349-1950
 * and synced with Student and Parent portal profiles.
 *
 * Supports:
 *   - Students (Information [5 full sections], Academics [2 top dropdowns], Extracurriculars, Achievements)
 *   - Teachers (Contact, Class Responsibility, Employment, Emergency, Assignments, Qualifications)
 *   - Parents (Contact, Personal, Enrollment, Linked Students with instant jump-to-profile)
 *   - Management Staff (Contact, Employment & Personal, Administrative Scope)
 *   - Seamless Account Linking (Parent <-> Student bidirectional jumps)
 *   - View mode vs Edit mode toggling with live validation and DOM update
 * =========================================================================
 */

(function () {
  'use strict';

  // Role metadata and theme configurations matching Admin/people/app.js lines 1338-1343
  const ROLE_THEMES = {
    student: {
      label: 'Student profile',
      headerClass: 'c-header-sky',
      pillBg: 'rgba(127, 199, 204, 0.2)',
      pillColor: '#0F414A',
      softBg: 'rgba(127, 199, 204, 0.12)',
      tone: 'sky'
    },
    teacher: {
      label: 'Teaching staff profile',
      headerClass: 'c-header-sunshine',
      pillBg: 'rgba(234, 137, 19, 0.2)',
      pillColor: '#0F414A',
      softBg: 'rgba(234, 137, 19, 0.1)',
      tone: 'sunshine'
    },
    parent: {
      label: 'Parent / guardian profile',
      headerClass: 'c-header-terracotta',
      pillBg: 'rgba(175, 80, 49, 0.15)',
      pillColor: '#AF5031',
      softBg: 'rgba(175, 80, 49, 0.1)',
      tone: 'terracotta'
    },
    management: {
      label: 'Management profile',
      headerClass: 'c-header-maroon',
      pillBg: 'rgba(127, 3, 3, 0.1)',
      pillColor: '#7F0303',
      softBg: 'rgba(127, 3, 3, 0.08)',
      tone: 'maroon'
    },
    admin: {
      label: 'Administrator profile',
      headerClass: 'c-header-maroon',
      pillBg: 'rgba(15, 65, 74, 0.2)',
      pillColor: '#0F414A',
      softBg: 'rgba(15, 65, 74, 0.1)',
      tone: 'maroon'
    }
  };

  // Activity card tints matching brand-assigned extracurricular colors
  const ACTIVITY_THEMES = {
    'debating': { bg: '#f4ebe1', border: 'rgba(15, 65, 74, 0.25)', text: '#0F414A' },
    'choir': { bg: 'rgba(127, 199, 204, 0.25)', border: 'rgba(127, 199, 204, 0.55)', text: '#092F33' },
    'music': { bg: 'rgba(127, 199, 204, 0.25)', border: 'rgba(127, 199, 204, 0.55)', text: '#092F33' },
    'robotics': { bg: 'rgba(234, 137, 19, 0.18)', border: 'rgba(234, 137, 19, 0.45)', text: '#7A4305' },
    'tech': { bg: 'rgba(234, 137, 19, 0.18)', border: 'rgba(234, 137, 19, 0.45)', text: '#7A4305' },
    'swimming': { bg: 'rgba(150, 192, 206, 0.28)', border: 'rgba(150, 192, 206, 0.55)', text: '#0F414A' },
    'science': { bg: 'rgba(75, 91, 52, 0.18)', border: 'rgba(75, 91, 52, 0.45)', text: '#4B5B34' },
    'green': { bg: 'rgba(75, 91, 52, 0.18)', border: 'rgba(75, 91, 52, 0.45)', text: '#4B5B34' },
    'nature': { bg: 'rgba(75, 91, 52, 0.18)', border: 'rgba(75, 91, 52, 0.45)', text: '#4B5B34' },
    'football': { bg: 'rgba(175, 80, 49, 0.15)', border: 'rgba(175, 80, 49, 0.45)', text: '#AF5031' },
    'athletics': { bg: 'rgba(175, 80, 49, 0.15)', border: 'rgba(175, 80, 49, 0.45)', text: '#AF5031' },
    'cricket': { bg: 'rgba(127, 3, 3, 0.12)', border: 'rgba(127, 3, 3, 0.4)', text: '#7F0303' },
    'chess': { bg: 'rgba(234, 137, 19, 0.18)', border: 'rgba(234, 137, 19, 0.45)', text: '#EA8913' },
    'art': { bg: 'rgba(106, 63, 150, 0.12)', border: 'rgba(106, 63, 150, 0.35)', text: '#6A3F96' },
    'drama': { bg: 'rgba(106, 63, 150, 0.12)', border: 'rgba(106, 63, 150, 0.35)', text: '#6A3F96' },
    'default': { bg: 'rgba(127, 199, 204, 0.15)', border: 'rgba(127, 199, 204, 0.4)', text: '#0F414A' }
  };

  // State
  let modalEl = null;
  let activeRole = 'student';
  let activeId = '';
  let activeMode = 'view'; // 'view' | 'edit'
  let activeSubtab = 'information';
  let currentPerson = null;
  let editedDraft = null;

  // Digital Record Book state
  let recordBookSelectedGrade = 6;
  let recordBookSelectedTerm = 'Term 2';

  /**
   * Initialize and bind event listeners
   */
  function init() {
    modalEl = document.getElementById('j-profile-modal');
    if (!modalEl) return;

    bindGlobalTriggers();
    bindModalInternalEvents();
  }

  /**
   * Bind triggers across table rows and linked elements
   */
  function bindGlobalTriggers() {
    document.addEventListener('click', function (e) {
      // 1. View button on row or clicking a person row directly
      const viewBtn = e.target.closest('.j-open-profile');
      const rowClick = !e.target.closest('button, a, input, select, .c-dropdown, .c-select') ? e.target.closest('.j-person-row') : null;
      const viewTarget = viewBtn || rowClick;
      if (viewTarget) {
        e.preventDefault();
        const role = viewTarget.getAttribute('data-role') || 'student';
        const id = viewTarget.getAttribute('data-id') || '';
        openProfileModal(role, id, 'view');
        return;
      }

      // 2. Edit button on row
      const editBtn = e.target.closest('.j-edit-profile');
      if (editBtn) {
        e.preventDefault();
        const role = editBtn.getAttribute('data-role') || 'student';
        const id = editBtn.getAttribute('data-id') || '';
        openProfileModal(role, id, 'edit');
        return;
      }

      // 3. Linked Student buttons in parent rows or within Parent Profile Modal
      const linkedChildBtn = e.target.closest('.j-open-linked-student, .j-open-linked-child');
      if (linkedChildBtn) {
        e.preventDefault();
        const childLink = linkedChildBtn.getAttribute('data-link') || linkedChildBtn.textContent.trim();
        const childId = linkedChildBtn.getAttribute('data-id');
        openLinkedStudent(childId, childLink);
        return;
      }

      // 4. "View Parent Profile" / "Open parent account" button inside Student Profile Modal
      const jumpParentBtn = e.target.closest('.j-jump-parent-profile');
      if (jumpParentBtn) {
        e.preventDefault();
        const parentId = jumpParentBtn.getAttribute('data-parent-id');
        if (parentId) {
          openProfileModal('parent', parentId, 'view');
        }
        return;
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalEl && modalEl.style.display !== 'none') {
        // If an open dropdown menu exists, let the dropdown close first
        if (document.querySelector('.c-select.c-is-open, .c-dropdown.c-is-open')) return;
        closeProfileModal();
      }
    });
  }

  /**
   * Bind event handlers inside the master modal
   */
  function bindModalInternalEvents() {
    // Close buttons (X, Scrim, Cancel)
    modalEl.querySelectorAll('.j-modal-close').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        closeProfileModal();
      });
    });

    // Student Sub-tabs switching
    modalEl.querySelectorAll('.j-subtab-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const tab = btn.getAttribute('data-subtab');
        switchStudentSubtab(tab);
      });
    });

    // Record Book: Top Dropdowns change listener (both CustomEvent & native change)
    document.addEventListener('dropdown:change', function (e) {
      const root = e.target;
      if (!root) return;
      if (root.id === 'j-record-grade-select' || root.closest('#j-record-grade-select')) {
        const gradeNum = parseInt(e.detail.value, 10);
        if (!isNaN(gradeNum)) {
          switchRecordBookGrade(gradeNum);
        }
      } else if (root.id === 'j-record-term-select' || root.closest('#j-record-term-select')) {
        if (e.detail.value) {
          switchRecordBookTerm(e.detail.value);
        }
      }
    });

    document.addEventListener('change', function (e) {
      const target = e.target;
      if (!target) return;
      if (target.name === 'j-record-grade-select' || target.closest('#j-record-grade-select')) {
        const gradeNum = parseInt(target.value, 10);
        if (!isNaN(gradeNum)) {
          switchRecordBookGrade(gradeNum);
        }
      } else if (target.name === 'j-record-term-select' || target.closest('#j-record-term-select')) {
        if (target.value) {
          switchRecordBookTerm(target.value);
        }
      }
    });

    // Modal click delegation for dynamic lists & items
    modalEl.addEventListener('click', function (e) {
      // 1. Record Book: Add Mark row (Edit mode)
      const addMarkBtn = e.target.closest('.j-add-mark-btn');
      if (addMarkBtn) {
        e.preventDefault();
        addRecordBookMarkRow();
        return;
      }

      // Record Book: Remove Mark row
      const removeMarkBtn = e.target.closest('.j-remove-mark');
      if (removeMarkBtn) {
        e.preventDefault();
        const row = removeMarkBtn.closest('.c-marks-row');
        if (row) row.remove();
        return;
      }

      // 2. Extracurricular: Add Activity (Edit mode)
      const addExtraBtn = e.target.closest('.j-add-extra-btn');
      if (addExtraBtn) {
        e.preventDefault();
        addExtracurricularCard();
        return;
      }

      // Extracurricular: Remove Activity
      const removeExtraBtn = e.target.closest('.j-remove-extracurricular');
      if (removeExtraBtn) {
        e.preventDefault();
        const card = removeExtraBtn.closest('.c-extra-card');
        if (card) card.remove();
        return;
      }

      // 3. Achievement: Add Achievement (Edit mode)
      const addAchBtn = e.target.closest('.j-add-achievement-btn');
      if (addAchBtn) {
        e.preventDefault();
        addAchievementCard();
        return;
      }

      // Achievement: Remove Achievement
      const removeAchBtn = e.target.closest('.j-remove-achievement');
      if (removeAchBtn) {
        e.preventDefault();
        const card = removeAchBtn.closest('.c-achievement-card');
        if (card) card.remove();
        return;
      }

      // 4. Teacher: Add Assignment
      const addTeacherAssignBtn = e.target.closest('.j-add-teacher-assignment-btn');
      if (addTeacherAssignBtn) {
        e.preventDefault();
        addTeacherAssignmentRow('Mathematics', 'Class 6-A');
        return;
      }

      // Teacher: Remove Assignment
      const removeTeacherAssignBtn = e.target.closest('.j-remove-teacher-assignment');
      if (removeTeacherAssignBtn) {
        e.preventDefault();
        const row = removeTeacherAssignBtn.closest('.c-assignment-edit-row');
        if (row) row.remove();
        return;
      }

      // 5. Teacher: Add Qualification
      const addTeacherQualBtn = e.target.closest('.j-add-teacher-qual-btn');
      if (addTeacherQualBtn) {
        e.preventDefault();
        addTeacherQualRow('B.Sc. in Education', 'University of Colombo', '2016');
        return;
      }

      // Teacher: Remove Qualification
      const removeTeacherQualBtn = e.target.closest('.j-remove-teacher-qual');
      if (removeTeacherQualBtn) {
        e.preventDefault();
        const row = removeTeacherQualBtn.closest('.c-qual-edit-row');
        if (row) row.remove();
        return;
      }

      // 6. Parent: Add Linked Student
      const addParentLinkedBtn = e.target.closest('.j-add-parent-linked-btn');
      if (addParentLinkedBtn) {
        e.preventDefault();
        addParentLinkedRow('Nethmi Perera', 'Class 6-A');
        return;
      }

      // Parent: Remove Linked Student
      const removeParentLinkedBtn = e.target.closest('.j-remove-parent-linked');
      if (removeParentLinkedBtn) {
        e.preventDefault();
        const row = removeParentLinkedBtn.closest('.c-linked-edit-row');
        if (row) row.remove();
        return;
      }
    });

    // Edit profile button inside modal footer
    const editFooterBtn = document.getElementById('j-btn-edit-profile');
    if (editFooterBtn) {
      editFooterBtn.addEventListener('click', function (e) {
        e.preventDefault();
        toggleModalMode('edit');
      });
    }

    // Cancel edit button inside modal footer
    const cancelBtn = document.getElementById('j-btn-cancel-profile');
    if (cancelBtn) {
      cancelBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (currentPerson) {
          renderRoleSection(activeRole, currentPerson, 'view');
        }
        toggleModalMode('view');
      });
    }

    // Save changes button
    const saveBtn = document.getElementById('j-btn-save-profile');
    if (saveBtn) {
      saveBtn.addEventListener('click', function (e) {
        e.preventDefault();
        saveProfileChanges();
      });
    }
  }

  /**
   * Find person record from client store or DOM row
   */
  function findPersonRecord(role, id) {
    let data = window.__PEOPLE_DATA__;
    if (!data) {
      const jsonEl = document.getElementById('j-people-data');
      if (jsonEl) {
        try { data = JSON.parse(jsonEl.textContent); } catch (e) {}
      }
    }
    data = data || {};
    let list = [];
    if (role === 'student') list = data.students || [];
    else if (role === 'teacher') list = data.teachers || [];
    else if (role === 'parent') list = data.parents || [];
    else if (role === 'management') list = data.management || [];
    else if (role === 'admin') list = data.admins || data.admin || [];

    // Match by id or index
    let found = list.find(function (p) {
      return (p.id && p.id === id) || (p.index && p.index === id);
    });

    if (!found) {
      // Look up in DOM table row
      const row = document.querySelector(`.j-person-row[data-role="${role}"][data-id="${id}"]`);
      if (row) {
        const nameEl = row.querySelector('.c-person-name');
        const name = nameEl ? nameEl.textContent.trim() : 'User Profile';
        found = {
          id: id,
          index: id,
          name: name,
          firstName: name.split(' ')[0] || '',
          lastName: name.split(' ').slice(1).join(' ') || '',
          initials: name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase(),
          status: 'Active',
          role: role
        };
      }
    }

    // Default fallback if still missing
    if (!found) {
      found = {
        id: id || 'USR-001',
        index: id || 'USR-001',
        name: 'Record Profile',
        firstName: 'Record',
        lastName: 'Profile',
        initials: 'RP',
        status: 'Active',
        role: role
      };
    }

    return Object.assign({}, found);
  }

  /**
   * Open the master profile modal
   */
  function openProfileModal(role, id, mode) {
    if (!modalEl) return;
    activeRole = role || 'student';
    activeId = id || '';
    activeMode = mode || 'view';
    activeSubtab = 'information';

    currentPerson = findPersonRecord(activeRole, activeId);
    editedDraft = JSON.parse(JSON.stringify(currentPerson));

    // 1. Set Header & Theme
    applyModalHeaderTheme(activeRole, currentPerson);

    // 2. Populate View / Edit elements per role
    renderRoleSection(activeRole, currentPerson, activeMode);

    // 3. Toggle View / Edit display mode
    toggleModalMode(activeMode);

    // 4. Show modal
    modalEl.style.display = 'flex';
    modalEl.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  /**
   * Close the master profile modal
   */
  function closeProfileModal() {
    if (!modalEl) return;
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  /**
   * Apply header styling and metadata
   */
  function applyModalHeaderTheme(role, person) {
    const theme = ROLE_THEMES[role] || ROLE_THEMES.student;
    const headerEl = document.getElementById('j-modal-header');
    if (headerEl) {
      headerEl.className = 'c-modal-header ' + theme.headerClass;
    }

    // Avatar initials and tone
    const avatarEl = document.getElementById('j-modal-avatar');
    if (avatarEl) {
      avatarEl.textContent = person.initials || (person.name ? person.name.substring(0, 2).toUpperCase() : 'U');
      avatarEl.className = 'c-modal-avatar ' + (person.avatar || person.tone || 'bg-sand text-midnight');
    }

    // Eyebrow
    const eyebrowEl = document.getElementById('j-modal-eyebrow');
    if (eyebrowEl) {
      eyebrowEl.textContent = activeMode === 'edit' ? ('Edit ' + theme.label.toLowerCase()) : theme.label;
    }

    // Close button aria-label
    const closeBtn = document.getElementById('j-modal-close-btn');
    if (closeBtn) {
      closeBtn.setAttribute('aria-label', activeMode === 'edit' ? 'Cancel profile editing' : 'Close profile');
    }

    // Name view & input
    const nameViewEl = document.getElementById('j-modal-name-view');
    const nameInputEl = document.getElementById('j-modal-name-input');
    if (nameViewEl) nameViewEl.textContent = person.name || 'User Profile';
    if (nameInputEl) nameInputEl.value = person.name || '';

    // Subtitle
    const subEl = document.getElementById('j-modal-subtitle');
    if (subEl) {
      if (role === 'student') {
        subEl.textContent = (person.grade || 'Grade 6') + ' · ' + (person.className ? 'Class ' + person.className : 'Class 6-A');
      } else if (role === 'teacher') {
        subEl.textContent = (person.subject || 'Faculty') + ' Teacher · ' + (person.role || 'Class Teacher');
      } else if (role === 'parent') {
        subEl.textContent = (person.relation || 'Parent') + (person.children && person.children.length ? ' of ' + person.children.join(', ') : '');
      } else {
        subEl.textContent = person.jobTitle || 'Principal · Administrative Scope';
      }
    }

    // ID Pill
    const idPillEl = document.getElementById('j-modal-id-pill');
    if (idPillEl) {
      idPillEl.textContent = person.id || person.index || 'ID-000';
      idPillEl.style.background = theme.pillBg;
      idPillEl.style.color = theme.pillColor;
    }

    // Status Pill
    const statusPillEl = document.getElementById('j-modal-status-pill');
    if (statusPillEl) {
      const st = person.status || 'Active';
      statusPillEl.textContent = st;
      if (st === 'Active') {
        statusPillEl.className = 'c-status-pill c-status-active';
      } else if (st === 'On Leave') {
        statusPillEl.className = 'c-status-pill';
      } else {
        statusPillEl.className = 'c-status-pill c-status-inactive';
      }
    }

    // Unknown directory record pill & alert banner
    const unknownPill = document.getElementById('j-modal-unknown-pill');
    const recordAlert = document.getElementById('j-modal-record-alert');
    const isUnknown = (person.isKnown === false);
    if (unknownPill) unknownPill.style.display = isUnknown ? 'inline-block' : 'none';
    if (recordAlert) recordAlert.style.display = isUnknown ? 'block' : 'none';
  }

  /**
   * Render role section content
   */
  function renderRoleSection(role, person, mode) {
    // Hide all role sections
    modalEl.querySelectorAll('.j-profile-role-section').forEach(function (sec) {
      sec.style.display = 'none';
    });

    if (role === 'student') {
      const sec = document.getElementById('j-section-student');
      if (sec) sec.style.display = 'block';
      renderStudentDetails(person, mode);
    } else if (role === 'teacher') {
      const sec = document.getElementById('j-section-teacher');
      if (sec) sec.style.display = 'block';
      renderTeacherDetails(person, mode);
    } else if (role === 'parent') {
      const sec = document.getElementById('j-section-parent');
      if (sec) sec.style.display = 'block';
      renderParentDetails(person, mode);
    } else if (role === 'management') {
      const sec = document.getElementById('j-section-management');
      if (sec) sec.style.display = 'block';
      renderManagementDetails(person, mode);
    } else if (role === 'admin') {
      const sec = document.getElementById('j-section-admin');
      if (sec) sec.style.display = 'block';
      renderAdminDetails(person, mode);
    }
  }

  /**
   * Render Student Section (Information, Academics, Extracurriculars, Achievements)
   */
  function renderStudentDetails(person, mode) {
    switchStudentSubtab('information');

    // 1. Account
    setCardVal('j-card-student-index', person.id || person.index || 'S2021-091');
    setFieldVal('student-email', person.email || 'n.perera@lecole.com');

    // 2. Student information
    setFieldVal('student-grade', person.grade || 'Grade 6');
    setFieldVal('student-class', person.className ? 'Class ' + person.className : 'Class 6-A');
    setFieldVal('student-bc', person.birthCertificate || '2014/COL/00123');
    setFieldVal('student-dob', person.dateOfBirth || '2012-05-14');
    setFieldVal('student-gender', person.gender || 'Female');
    setFieldVal('student-admission', person.admissionDate || '2020-01-10');
    setFieldVal('student-blood', person.bloodGroup || 'O+');
    setFieldVal('student-nationality', person.nationality || 'Sri Lankan');
    setFieldVal('student-religion', person.religion || 'Buddhism');
    setFieldVal('student-prevschool', person.prevSchool || 'Royal College Primary');

    // 3. Residential details
    setFieldVal('student-address', person.address || '45 Galle Road, Wellawatte, Colombo 06');
    setFieldVal('student-zone', person.educationalZone || 'Colombo Zone 3');
    setFieldVal('student-district', person.district || 'Colombo');
    setFieldVal('student-province', person.province || 'Western');

    // 4. Medical Notes
    setFieldVal('student-medical', person.medicalNotes || 'No known chronic allergies. Regular medical clearance provided.');

    // 5. Connected Guardian / Parent Info & Linking
    setupStudentGuardianCard(person, mode === 'edit');

    // Populate Extracurriculars
    renderExtracurriculars(person.activities || ['Debating', 'Choir'], mode);

    // Populate Achievements
    renderAchievements(person.achievements || [
      { title: 'All Island Junior Debater 2024', category: 'Cultural', level: 'National', year: '2024' },
      { title: 'Inter-School Science Olympiad Bronze', category: 'Academic', level: 'Inter-school', year: '2023' }
    ], mode);

    // Record Book initial setup
    const gMatch = (person.grade || 'Grade 6').match(/\d+/);
    recordBookSelectedGrade = gMatch ? parseInt(gMatch[0], 10) : 6;
    switchRecordBookGrade(recordBookSelectedGrade);
  }

  /**
   * Setup student's parent/guardian card with instant link to Parent Profile
   */
  function setupStudentGuardianCard(person, isEditing) {
    const wrapEl = document.getElementById('j-student-guardian-wrap');
    const emptyEl = document.getElementById('j-student-guardian-empty');
    const avatarEl = document.getElementById('j-guardian-avatar');
    const parentNameEl = document.getElementById('j-card-guardian-name');
    const parentRelEl = document.getElementById('j-card-guardian-rel');
    const parentRelEditEl = document.getElementById('j-card-guardian-rel-edit');
    const parentRelSelectEl = document.getElementById('j-card-guardian-rel-select');
    const parentEmailEl = document.getElementById('j-card-guardian-email');
    const parentPhoneEl = document.getElementById('j-card-guardian-phone');
    const jumpBtn = document.getElementById('j-btn-view-parent');
    const unavailPill = document.getElementById('j-guardian-unavailable-pill');

    // Try to find connected parent in store
    const parents = (window.__PEOPLE_DATA__ && window.__PEOPLE_DATA__.parents) || [];
    let linkedParent = null;

    if (person.parentId) {
      linkedParent = parents.find(p => (p.id === person.parentId || p.parent_id === person.parentId));
    }
    if (!linkedParent && person.guardian && person.guardian.isAvailable !== false) {
      linkedParent = person.guardian;
    }
    if (!linkedParent && person.student && person.student.guardian && person.student.guardian.isAvailable !== false) {
      linkedParent = person.student.guardian;
    }
    if (!linkedParent) {
      linkedParent = parents.find(function (p) {
        if (!p.children && !p.linkedStudents) return false;
        if (p.linkedStudents && p.linkedStudents.some(s => s.id === person.id || s.name === person.name)) return true;
        if (p.children) {
          return p.children.some(c => c.indexOf(person.name) !== -1 || (person.id && c.indexOf(person.id) !== -1));
        }
        return false;
      });
    }

    if (!linkedParent || linkedParent.isAvailable === false) {
      if (wrapEl) wrapEl.style.display = 'none';
      if (emptyEl) emptyEl.style.display = 'block';
      return;
    }

    if (wrapEl) wrapEl.style.display = 'flex';
    if (emptyEl) emptyEl.style.display = 'none';

    if (avatarEl) {
      avatarEl.textContent = linkedParent.initials || 'PG';
      const tone = linkedParent.tone || linkedParent.avatarTone || 'bg-terracotta text-white';
      avatarEl.className = 'c-avatar c-avatar-sm ' + tone;
    }

    if (parentNameEl) parentNameEl.textContent = linkedParent.name || 'Suresh Perera';

    const rel = linkedParent.relation || linkedParent.relationship || 'Father';
    const access = (linkedParent.status === 'Active' || linkedParent.accountAccess)
      ? (linkedParent.accountAccess || 'Account connected')
      : 'Account connected';

    if (isEditing) {
      if (parentRelEl) parentRelEl.style.display = 'none';
      if (parentRelEditEl) parentRelEditEl.style.display = 'block';
      if (parentRelSelectEl) parentRelSelectEl.value = rel;
    } else {
      if (parentRelEl) {
        parentRelEl.style.display = 'block';
        parentRelEl.textContent = rel + ' · ' + access;
      }
      if (parentRelEditEl) parentRelEditEl.style.display = 'none';
    }

    if (parentEmailEl) parentEmailEl.textContent = linkedParent.email || 'Email not recorded';
    if (parentPhoneEl) parentPhoneEl.textContent = linkedParent.phone || 'Phone not recorded';

    if (jumpBtn) {
      if (linkedParent.id) {
        jumpBtn.style.display = 'inline-flex';
        jumpBtn.setAttribute('data-parent-id', linkedParent.id);
        if (unavailPill) unavailPill.style.display = 'none';
      } else {
        jumpBtn.style.display = 'none';
        if (unavailPill) unavailPill.style.display = 'inline-block';
      }
    }
  }

  /**
   * Render Extracurriculars List
   */
  function renderExtracurriculars(activities, mode) {
    const listEl = document.getElementById('j-extra-list');
    if (!listEl) return;
    listEl.innerHTML = '';

    if (!activities || !activities.length) {
      listEl.innerHTML = '<p class="c-subtext" style="grid-column:1/-1;text-align:center;padding:1rem;">No extracurricular activities recorded.</p>';
      return;
    }

    activities.forEach(function (act) {
      const actName = typeof act === 'string' ? act : (act.name || 'Activity');
      const actRole = typeof act === 'object' ? (act.role || 'Member') : 'Member';
      const actStatus = typeof act === 'object' ? (act.status || 'Active') : 'Active';

      const key = actName.toLowerCase();
      let theme = ACTIVITY_THEMES.default;
      for (const t in ACTIVITY_THEMES) {
        if (key.indexOf(t) !== -1) {
          theme = ACTIVITY_THEMES[t];
          break;
        }
      }

      const card = document.createElement('article');
      card.className = 'c-extra-card';
      card.style.background = theme.bg;
      card.style.borderColor = theme.border;

      if (mode === 'edit') {
        card.innerHTML =
          '<div style="display:flex;flex-direction:column;gap:0.5rem;width:100%;">' +
            '<input type="text" class="c-info-card-input j-extra-name-input" value="' + escapeHtml(actName) + '" placeholder="Activity title" />' +
            '<div style="display:grid;grid-template-columns:1fr 1fr auto;gap:0.5rem;">' +
              '<input type="text" class="c-info-card-input j-extra-role-input" value="' + escapeHtml(actRole) + '" placeholder="Role" />' +
              '<input type="text" class="c-info-card-input j-extra-status-input" value="' + escapeHtml(actStatus) + '" placeholder="Status" />' +
              '<button type="button" class="c-marks-remove j-remove-extracurricular" title="Remove activity">' +
                '<svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-trash"/></svg>' +
              '</button>' +
            '</div>' +
          '</div>';
      } else {
        card.innerHTML =
          '<p class="c-extra-title">' + escapeHtml(actName) + '</p>' +
          '<p class="c-extra-meta">' + escapeHtml(actRole) + ' · ' + escapeHtml(actStatus) + '</p>';
      }

      listEl.appendChild(card);
    });
  }

  /**
   * Add new extracurricular card
   */
  function addExtracurricularCard() {
    const listEl = document.getElementById('j-extra-list');
    if (!listEl) return;
    const card = document.createElement('article');
    card.className = 'c-extra-card';
    card.style.background = 'rgba(127, 199, 204, 0.15)';
    card.style.borderColor = 'rgba(127, 199, 204, 0.4)';
    card.innerHTML =
      '<div style="display:flex;flex-direction:column;gap:0.5rem;width:100%;">' +
        '<input type="text" class="c-info-card-input j-extra-name-input" value="Drama Club" placeholder="Activity title" />' +
        '<div style="display:grid;grid-template-columns:1fr 1fr auto;gap:0.5rem;">' +
          '<input type="text" class="c-info-card-input j-extra-role-input" value="Performer" placeholder="Role" />' +
          '<input type="text" class="c-info-card-input j-extra-status-input" value="Active" placeholder="Status" />' +
          '<button type="button" class="c-marks-remove j-remove-extracurricular" title="Remove activity">' +
            '<svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-trash"/></svg>' +
          '</button>' +
        '</div>' +
      '</div>';
    listEl.appendChild(card);
  }

  /**
   * Render Achievements List
   */
  function renderAchievements(achievements, mode) {
    const listEl = document.getElementById('j-achievement-list');
    if (!listEl) return;
    listEl.innerHTML = '';

    if (!achievements || !achievements.length) {
      listEl.innerHTML = '<p class="c-subtext" style="grid-column:1/-1;text-align:center;padding:1rem;">No achievements recorded.</p>';
      return;
    }

    achievements.forEach(function (ach) {
      const card = document.createElement('article');
      card.className = 'c-achievement-card';

      if (mode === 'edit') {
        card.innerHTML =
          '<div class="c-achievement-body">' +
            '<div class="c-achievement-icon">' +
              '<svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-medal"/></svg>' +
            '</div>' +
            '<div style="min-width:0;flex:1;display:flex;flex-direction:column;gap:0.5rem;">' +
              '<input type="text" class="c-info-card-input j-achievement-title-input" value="' + escapeHtml(ach.title) + '" placeholder="Achievement title" />' +
              '<div style="display:grid;grid-template-columns:1fr 1fr 80px auto;gap:0.5rem;">' +
                '<input type="text" class="c-info-card-input j-achievement-cat-input" value="' + escapeHtml(ach.category || 'Academic') + '" placeholder="Category" />' +
                '<input type="text" class="c-info-card-input j-achievement-level-input" value="' + escapeHtml(ach.level || 'School') + '" placeholder="Level" />' +
                '<input type="text" class="c-info-card-input j-achievement-year-input" value="' + escapeHtml(ach.year || '2024') + '" placeholder="Year" />' +
                '<button type="button" class="c-marks-remove j-remove-achievement" title="Remove achievement">' +
                  '<svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-trash"/></svg>' +
                '</button>' +
              '</div>' +
            '</div>' +
          '</div>';
      } else {
        card.innerHTML =
          '<div class="c-achievement-body">' +
            '<div class="c-achievement-icon">' +
              '<svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-medal"/></svg>' +
            '</div>' +
            '<div>' +
              '<p class="c-achievement-title">' + escapeHtml(ach.title) + '</p>' +
              '<p class="c-achievement-meta">' + escapeHtml([ach.category, ach.level, ach.year].filter(Boolean).join(' · ')) + '</p>' +
            '</div>' +
          '</div>';
      }

      listEl.appendChild(card);
    });
  }

  /**
   * Add new achievement card
   */
  function addAchievementCard() {
    const listEl = document.getElementById('j-achievement-list');
    if (!listEl) return;
    const card = document.createElement('article');
    card.className = 'c-achievement-card';
    card.innerHTML =
      '<div class="c-achievement-body">' +
        '<div class="c-achievement-icon">' +
          '<svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-medal"/></svg>' +
        '</div>' +
        '<div style="min-width:0;flex:1;display:flex;flex-direction:column;gap:0.5rem;">' +
          '<input type="text" class="c-info-card-input j-achievement-title-input" value="Excellence in Mathematics" placeholder="Achievement title" />' +
          '<div style="display:grid;grid-template-columns:1fr 1fr 80px auto;gap:0.5rem;">' +
            '<input type="text" class="c-info-card-input j-achievement-cat-input" value="Academic" placeholder="Category" />' +
            '<input type="text" class="c-info-card-input j-achievement-level-input" value="Provincial" placeholder="Level" />' +
            '<input type="text" class="c-info-card-input j-achievement-year-input" value="2024" placeholder="Year" />' +
            '<button type="button" class="c-marks-remove j-remove-achievement" title="Remove achievement">' +
              '<svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-trash"/></svg>' +
            '</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    listEl.appendChild(card);
  }

  /**
   * Switch Student Sub-tabs (Information, Academics, Extracurriculars, Achievements)
   */
  function switchStudentSubtab(tabName) {
    activeSubtab = tabName || 'information';

    modalEl.querySelectorAll('.j-subtab-btn').forEach(function (btn) {
      const isMatch = btn.getAttribute('data-subtab') === activeSubtab;
      btn.classList.toggle('is-active-subtab', isMatch);
      btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
    });

    modalEl.querySelectorAll('.j-subtab-panel').forEach(function (panel) {
      panel.style.display = 'none';
    });

    const targetPanel = document.getElementById('j-panel-' + activeSubtab);
    if (targetPanel) {
      targetPanel.style.display = 'block';
    }

    if (activeSubtab === 'academics') {
      if (window.StudentAcademic?.renderAcademicPage) {
        window.StudentAcademic.renderAcademicPage();
      }
    }
  }

  /**
   * Digital Record Book: Switch Grade Level from Top Dropdown
   */
  function switchRecordBookGrade(gradeNumber) {
    recordBookSelectedGrade = gradeNumber;

    modalEl.querySelectorAll('.j-record-grade-label, #record-grade-label').forEach(function (el) {
      el.textContent = 'Grade ' + gradeNumber;
    });

    const fbLabel = document.getElementById('feedback-grade-label');
    if (fbLabel) fbLabel.textContent = 'Grade ' + gradeNumber;

    // Sync dropdown UI
    const gradeSelect = document.getElementById('j-record-grade-select');
    if (gradeSelect) {
      const valLabel = gradeSelect.querySelector('.j-select-value, .c-dropdown__value');
      if (valLabel) valLabel.textContent = 'Grade ' + gradeNumber;
      const hidden = gradeSelect.querySelector('input[type="hidden"]');
      if (hidden) hidden.value = String(gradeNumber);
    }

    renderRecordBookMarks();
    if (window.StudentAcademic?.selectGrade) {
      window.StudentAcademic.selectGrade(gradeNumber);
    }
  }

  /**
   * Digital Record Book: Switch Term from Top Dropdown
   */
  function switchRecordBookTerm(termName) {
    recordBookSelectedTerm = termName;

    const fbTerm = document.getElementById('feedback-term-label');
    if (fbTerm) fbTerm.textContent = termName;

    // Sync dropdown UI
    const termSelect = document.getElementById('j-record-term-select');
    if (termSelect) {
      const valLabel = termSelect.querySelector('.j-select-value, .c-dropdown__value');
      if (valLabel) valLabel.textContent = termName;
      const hidden = termSelect.querySelector('input[type="hidden"]');
      if (hidden) hidden.value = termName;
    }

    renderRecordBookMarks();
    if (window.StudentAcademic?.selectTerm) {
      window.StudentAcademic.selectTerm(termName);
    }
  }

  /**
   * Re-render Record Book Marks table dynamically based on Grade & Term
   */
  function renderRecordBookMarks() {
    const marksTbody = document.getElementById('marks-table-body');
    if (!marksTbody) return;

    const baseMarks = [
      { subject: 'English Language', mark: 88, highest: 94 },
      { subject: 'Mathematics', mark: 92, highest: 98 },
      { subject: 'Science', mark: 85, highest: 91 },
      { subject: 'History', mark: 78, highest: 89 },
      { subject: 'Geography', mark: 84, highest: 90 },
      { subject: 'ICT', mark: 95, highest: 97 }
    ];

    const offset = ((recordBookSelectedGrade - 6) * 3 + (recordBookSelectedTerm === 'Term 1' ? 0 : (recordBookSelectedTerm === 'Term 2' ? 2 : 4))) % 7;

    marksTbody.innerHTML = baseMarks.map(function (m, idx) {
      const markVal = Math.min(99, Math.max(55, m.mark + (idx % 2 === 0 ? offset : -offset)));
      const highestVal = Math.min(100, markVal + 4 + (idx % 3));
      return '<tr>' +
        '<td>' + escapeHtml(m.subject) + '</td>' +
        '<td>' + markVal + '</td>' +
        '<td>' + highestVal + '</td>' +
        '</tr>';
    }).join('');
  }

  /**
   * Add Record Book Mark Row in Edit mode
   */
  function addRecordBookMarkRow() {
    const listEl = document.getElementById('j-recordbook-edit-rows');
    if (!listEl) return;
    const row = document.createElement('div');
    row.className = 'c-marks-row c-marks-cols-edit';
    row.innerHTML =
      '<input type="text" class="c-info-card-input j-mark-subject" value="Mathematics" placeholder="Subject" />' +
      '<input type="number" min="0" max="100" class="c-marks-input j-mark-score" value="85" />' +
      '<button type="button" class="c-marks-remove j-remove-mark" title="Remove mark">' +
        '<svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-trash"/></svg>' +
      '</button>';
    listEl.appendChild(row);
  }

  /**
   * Helper: Add a teacher subject-class assignment row in edit mode
   */
  function addTeacherAssignmentRow(subject, cls) {
    const listEl = document.getElementById('j-teacher-assignment-edit-list');
    if (!listEl) return;
    const row = document.createElement('div');
    row.className = 'c-assignment-edit-row';
    row.innerHTML =
      '<input type="text" class="c-info-card-input j-assign-subject" value="' + escapeHtml(subject || 'Mathematics') + '" placeholder="Subject" />' +
      '<input type="text" class="c-info-card-input j-assign-class" value="' + escapeHtml(cls || 'Class 6-A') + '" placeholder="Class (e.g. 6-A)" />' +
      '<button type="button" class="c-btn-solid-tone c-tone-maroon j-remove-teacher-assignment" title="Remove assignment" style="padding:0.45rem 0.75rem;">' +
        'Remove' +
      '</button>';
    listEl.appendChild(row);
  }

  /**
   * Helper: Add a teacher qualification row in edit mode
   */
  function addTeacherQualRow(title, inst, year) {
    const listEl = document.getElementById('j-teacher-qual-edit-list');
    if (!listEl) return;
    const row = document.createElement('div');
    row.className = 'c-qual-edit-row';
    row.innerHTML =
      '<input type="text" class="c-info-card-input j-qual-title" value="' + escapeHtml(title || '') + '" placeholder="Title / Degree (e.g. B.Sc.)" />' +
      '<input type="text" class="c-info-card-input j-qual-institution" value="' + escapeHtml(inst || '') + '" placeholder="Institution" />' +
      '<input type="text" class="c-info-card-input j-qual-year" value="' + escapeHtml(year || '') + '" placeholder="Year" />' +
      '<button type="button" class="c-btn-solid-tone c-tone-maroon j-remove-teacher-qual" title="Remove qualification" style="padding:0.45rem 0.75rem;">' +
        'Remove' +
      '</button>';
    listEl.appendChild(row);
  }

  /**
   * Helper: Add a parent linked student row in edit mode
   */
  function addParentLinkedRow(name, cls) {
    const listEl = document.getElementById('j-parent-linked-edit-list');
    if (!listEl) return;
    const row = document.createElement('div');
    row.className = 'c-linked-edit-row';
    row.innerHTML =
      '<input type="text" class="c-info-card-input j-linked-name" value="' + escapeHtml(name || '') + '" placeholder="Student full name" />' +
      '<input type="text" class="c-info-card-input j-linked-class" value="' + escapeHtml(cls || 'Class 6-A') + '" placeholder="Class (e.g. 6-A)" />' +
      '<button type="button" class="c-btn-solid-tone c-tone-maroon j-remove-parent-linked" title="Remove student" style="padding:0.45rem 0.75rem;">' +
        'Remove' +
      '</button>';
    listEl.appendChild(row);
  }

  /**
   * Render Teacher Section
   */
  function renderTeacherDetails(person, mode) {
    setCardVal('j-card-teacher-id', person.id || person.index || 'T-001');
    setFieldVal('teacher-email', person.email || 'teacher@lecole.edu');
    setFieldVal('teacher-pemail', person.personalEmail || 'teacher.personal@gmail.com');
    setFieldVal('teacher-phone', person.phone || '+94 77 998 8776');

    // Class teacher of & subject (view+edit dropdowns exist in PHP)
    const classInCharge = person.classTeacherOf ? (person.classTeacherOf.startsWith('Class ') ? person.classTeacherOf : ('Class ' + person.classTeacherOf)) : 'Class 6-A';
    const viewRoleText = (classInCharge === 'None' || !person.classTeacherOf) ? 'Class Teacher (No class assigned)' : ('Class Teacher — In charge of ' + classInCharge);
    const cCard = document.getElementById('j-card-teacher-classincharge');
    if (cCard) cCard.textContent = viewRoleText;
    const cSelect = document.getElementById('j-select-teacher-classincharge');
    if (cSelect) {
      const valLabel = cSelect.querySelector('.j-select-value, .c-dropdown__value, .c-select__trigger span');
      if (valLabel) { valLabel.textContent = classInCharge; valLabel.classList.remove('c-dropdown__placeholder'); }
      const hidden = cSelect.querySelector('input[type="hidden"]');
      if (hidden) hidden.value = classInCharge;
    }

    const ticAssignment = person.tic || (Array.isArray(person.extracurriculars) && person.extracurriculars[0]) || 'None';
    setFieldVal('teacher-tic', ticAssignment);
    setFieldVal('teacher-tic-role', (ticAssignment === 'None' || !ticAssignment) ? 'None' : (person.ticRole || 'Teacher in Charge & Faculty Mentor'));
    setFieldVal('teacher-subject', person.subject || 'Mathematics');

    // Workload
    const workloadText = person.workload || 'Total weekly workload: 22 instructional periods.';
    const wlNote = document.getElementById('j-teacher-workload');
    const wlInput = document.getElementById('j-input-teacher-workload');
    if (wlNote) wlNote.textContent = workloadText;
    if (wlInput) wlInput.value = workloadText;

    // Personal & Employment
    setFieldVal('teacher-fullname', person.name || 'Mrs. Ishara Gunasekara');
    setFieldVal('teacher-nic', person.nic || '198574102938');
    setFieldVal('teacher-dob', person.dateOfBirth || person.dob || '1985-06-22');
    setFieldVal('teacher-pemail2', person.personalEmail || 'ishara.personal@gmail.com');
    setFieldVal('teacher-exp', person.experience || '8');
    setFieldVal('teacher-joindate', person.joinDate || person.joiningDate || '2018-01-15');

    // Emergency
    setFieldVal('teacher-emname', person.emergencyName || 'Nimal Gunasekara');
    setFieldVal('teacher-emphone', person.emergencyPhone || person.emergencyContact || '+94 77 456 7890');

    // Subject assignments
    let assignments = person.subjectAssignments;
    if (!assignments || !assignments.length) {
      if (person.classes && person.classes.length) {
        assignments = person.classes.map(c => ({ subject: person.subject || 'Mathematics', className: c }));
      } else {
        assignments = [
          { subject: person.subject || 'Mathematics', className: '6-A' },
          { subject: person.subject || 'Mathematics', className: '6-B' },
          { subject: person.subject || 'Mathematics', className: '7-A' }
        ];
      }
    }

    // Populate assignments view grid
    const assignEl = document.getElementById('j-teacher-assignments');
    if (assignEl) {
      assignEl.innerHTML = assignments.map(function (a) {
        const clsClean = (a.className || '').replace(/^Class\s*/i, '');
        return '<div class="c-assignment-row">' +
          '<span class="c-assignment-subject">' + escapeHtml(a.subject || person.subject || 'Mathematics') + '</span>' +
          '<span class="c-assignment-pill c-assignment-pill--sunshine">Class ' + escapeHtml(clsClean) + '</span>' +
        '</div>';
      }).join('');
    }

    // Populate assignments edit list
    const assignEditList = document.getElementById('j-teacher-assignment-edit-list');
    if (assignEditList) {
      assignEditList.innerHTML = '';
      assignments.forEach(function (a) {
        const clsStr = (a.className && a.className.startsWith('Class ')) ? a.className : ('Class ' + (a.className || '6-A'));
        addTeacherAssignmentRow(a.subject || person.subject || 'Mathematics', clsStr);
      });
    }

    // Qualifications
    let qualifications = person.qualifications;
    if (!qualifications || !qualifications.length) {
      qualifications = [
        { title: 'B.Sc. in Education & Natural Sciences', institution: 'University of Colombo', year: '2016' },
        { title: 'Postgraduate Diploma in Educational Leadership', institution: 'National Institute of Education', year: '2020' }
      ];
    }

    // Populate qualifications view list
    const qualEl = document.getElementById('j-teacher-qualifications');
    if (qualEl) {
      qualEl.innerHTML = qualifications.map(function (q) {
        return '<div style="background:var(--color-alabaster, #F8F9FA);padding:0.75rem 1rem;border-radius:var(--radius-lg, 0.5rem);border:1px solid rgba(15,65,74,0.1);">' +
          '<div style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);">' + escapeHtml(q.title || '') + '</div>' +
          '<div style="font-size:0.75rem;color:rgba(15,65,74,0.65);margin-top:0.25rem;">' + escapeHtml(q.institution || '') + (q.year ? ' • ' + escapeHtml(q.year) : '') + '</div>' +
        '</div>';
      }).join('');
    }

    // Populate qualifications edit list
    const qualEditList = document.getElementById('j-teacher-qual-edit-list');
    if (qualEditList) {
      qualEditList.innerHTML = '';
      qualifications.forEach(function (q) {
        addTeacherQualRow(q.title, q.institution, q.year);
      });
    }
  }

  /**
   * Render Parent Section with Linked Students accounts
   */
  function renderParentDetails(person, mode) {
    setCardVal('j-card-parent-id', person.id || person.index || 'P-045');
    setFieldVal('parent-email', person.email || 'parent@gmail.com');
    setFieldVal('parent-phone', person.phone || '+94 77 234 5678');
    setFieldVal('parent-sphone', person.secondaryContact || person.secondaryPhone || '011-2345678');

    // Personal
    setFieldVal('parent-fullname', person.name || 'Suresh Perera');
    setFieldVal('parent-relation', person.relation || person.relationship || 'Father');
    setFieldVal('parent-nic', person.nic || person.identityReference || '197829103948');
    setFieldVal('parent-passport', person.passport || 'N1298492');
    setFieldVal('parent-dob', person.dateOfBirth || person.dob || '1978-04-12');
    setFieldVal('parent-emname', person.emergencyName || 'Kumari Perera');
    setFieldVal('parent-emcontact', person.emergencyContact || '+94 77 998 8776');
    setFieldVal('parent-guardianstatus', person.guardianStatus || 'Living');

    // Enrollment
    setFieldVal('parent-occupation', person.occupation || 'Chartered Engineer');
    setFieldVal('parent-employer', person.employer || 'Civil Engineering Bureau');
    setFieldVal('parent-officephone', person.officePhone || '011-2334455');
    setFieldVal('parent-officeaddress', person.officeAddress || 'Level 4, World Trade Centre, Colombo 01');
    setFieldVal('parent-address', person.address || person.homeAddress || '45 Galle Road, Wellawatte, Colombo 06');

    // Linked Student Accounts
    let children = person.linkedStudents || person.children;
    if (!children || !children.length) {
      children = ['Nethmi Perera — 6-A'];
    }

    const normalizedChildren = children.map(function (c) {
      if (typeof c === 'string') {
        const parts = c.split('—').map(s => s.trim());
        return { name: parts[0] || c, className: (parts[1] || '6-A').replace(/^Class\s*/i, ''), id: '' };
      }
      return { name: c.name || '', className: (c.className || '6-A').replace(/^Class\s*/i, ''), id: c.id || '' };
    });

    // Populate Linked Students view grid
    const linkedWrap = document.getElementById('j-parent-linked-students');
    if (linkedWrap) {
      if (!normalizedChildren.length) {
        linkedWrap.innerHTML = '<p class="c-subtext">No linked student accounts recorded.</p>';
      } else {
        linkedWrap.innerHTML = normalizedChildren.map(function (child) {
          const childLink = child.name + ' — ' + child.className;
          return '<button type="button" class="c-linked-btn j-open-linked-child" data-link="' + escapeHtml(childLink) + '" data-name="' + escapeHtml(child.name) + '" ' + (child.id ? ('data-id="' + escapeHtml(child.id) + '"') : '') + '>' +
            '<span class="c-linked-left">' +
              '<svg class="c-icon" width="16" height="16"><use href="#icon-checkCircle"/></svg>' +
              '<span class="c-linked-name">' + escapeHtml(child.name) + '</span>' +
            '</span>' +
            '<span class="c-assignment-pill c-assignment-pill--terracotta">Class ' + escapeHtml(child.className) + '</span>' +
          '</button>';
        }).join('');
      }
    }

    // Populate Linked Students edit list
    const linkedEditList = document.getElementById('j-parent-linked-edit-list');
    if (linkedEditList) {
      linkedEditList.innerHTML = '';
      normalizedChildren.forEach(function (child) {
        addParentLinkedRow(child.name, 'Class ' + child.className);
      });
    }
  }

  /**
   * Render Management Section
   */
  function renderManagementDetails(person, mode) {
    setCardVal('j-card-mgmt-id', person.id || person.index || 'M-001');
    setFieldVal('mgmt-email', person.email || 'management@lecole.com');
    setFieldVal('mgmt-phone', person.phone || '+94 77 000 0001');

    // Employment
    setFieldVal('mgmt-title', person.jobTitle || person.title || 'Principal & Executive Director');
    setFieldVal('mgmt-joining', person.joiningDate || person.joinDate || '2015-01-01');
    setFieldVal('mgmt-office', person.officeLocation || person.officeAddress || 'Main Building · Admissions Desk');

    // Personal information
    setFieldVal('mgmt-fullname', person.name || 'Dr. Malik Samarasinghe');
    setFieldVal('mgmt-nic', person.nic || '197039201948');
    setFieldVal('mgmt-dob', person.dateOfBirth || person.dob || '1970-08-15');
    setFieldVal('mgmt-pemail', person.personalEmail || 'malik.personal@gmail.com');
    setFieldVal('mgmt-resaddress', person.personalAddress || person.address || '14 Palm Grove, Colombo 07');

    // Emergency account
    const emName = person.emergencyName || (person.emergencyContact || '').split(' · ')[0] || (person.emergencyContact || '').split(' - ')[0] || 'Emma Thompson';
    const emPhone = person.emergencyPhone || (person.emergencyContact || '').split(' · ')[1] || (person.emergencyContact || '').split(' - ')[1] || '+94 77 000 0091';
    setFieldVal('mgmt-emname', emName);
    setFieldVal('mgmt-emergency', emPhone);
  }

  /**
   * Render Admin Section (Contact, System Scope, Personal, Emergency)
   */
  function renderAdminDetails(person, mode) {
    setCardVal('j-card-admin-id', person.id || person.index || 'ADM-001');
    setFieldVal('admin-email', person.email || 'admin@lecole.edu');
    setFieldVal('admin-phone', person.phone || '+94 77 123 4567');
    setFieldVal('admin-noc', person.nocPhone || person.centralItPhone || '+94 11 234 5678');

    // System Administration & Scope
    setFieldVal('admin-title', person.jobTitle || person.title || 'System Administrator & IT Director');
    setFieldVal('admin-joindate', person.joiningDate || person.joinDate || '2021-01-01');
    setFieldVal('admin-office', person.officeLocation || person.officeAddress || 'Central Server Facility · Room A');

    // Personal information
    setFieldVal('admin-fullname', person.name || 'Alex Mendis');
    setFieldVal('admin-nic', person.nic || '198516503921');
    setFieldVal('admin-dob', person.dateOfBirth || person.dob || '1985-06-14');
    setFieldVal('admin-pemail', person.personalEmail || 'alex.personal@gmail.com');
    setFieldVal('admin-resaddress', person.personalAddress || person.address || '42 Alfred House Gardens, Colombo 03');

    // Emergency account
    const emName = person.emergencyName || (person.emergencyContact || '').split(' · ')[0] || (person.emergencyContact || '').split(' - ')[0] || 'P. Mendis';
    const emPhone = person.emergencyPhone || (person.emergencyContact || '').split(' · ')[1] || (person.emergencyContact || '').split(' - ')[1] || '+94 77 999 1122';
    setFieldVal('admin-emname', emName);
    setFieldVal('admin-emergency', emPhone);
  }

  /**
   * Toggle Modal between View and Edit modes
   */
  function toggleModalMode(mode) {
    const allowEdit = !modalEl || modalEl.dataset.allowEdit !== 'false';
    if (!allowEdit) {
      mode = 'view';
    }
    activeMode = mode;
    const isEdit = (mode === 'edit');

    // Title / input in header
    const nameViewEl = document.getElementById('j-modal-name-view');
    const nameEditWrap = document.getElementById('j-modal-name-edit-wrap');
    const nameInputEl = document.getElementById('j-modal-name-input');
    if (nameViewEl) nameViewEl.style.display = isEdit ? 'none' : 'block';
    if (nameEditWrap) nameEditWrap.style.display = isEdit ? 'block' : 'none';
    // Populate name input from current visible name (or person record)
    if (isEdit && nameInputEl) {
      const liveNameText = (nameViewEl && nameViewEl.textContent.trim()) || (currentPerson && currentPerson.name) || '';
      nameInputEl.value = liveNameText;
      // Focus at end of input so user can type immediately
      setTimeout(function () { nameInputEl.focus(); nameInputEl.setSelectionRange(liveNameText.length, liveNameText.length); }, 50);
    }

    // Status pill / dropdown in header
    const statusPill = document.getElementById('j-modal-status-pill');
    const statusSelect = document.getElementById('j-modal-status-select-wrap');
    if (statusPill) statusPill.style.display = isEdit ? 'none' : 'inline-block';
    if (statusSelect) statusSelect.style.display = isEdit ? 'inline-block' : 'none';

    // Toggle .j-view-only vs .j-edit-only across modal
    modalEl.querySelectorAll('.j-view-only').forEach(function (el) {
      el.style.display = isEdit ? 'none' : '';
    });
    modalEl.querySelectorAll('.j-edit-only').forEach(function (el) {
      el.style.display = isEdit ? '' : 'none';
    });

    // Record book containers toggle
    const rbView = document.getElementById('j-recordbook-view-mode');
    const rbEdit = document.getElementById('j-recordbook-edit-mode');
    if (rbView) rbView.style.display = isEdit ? 'none' : 'block';
    if (rbEdit) rbEdit.style.display = isEdit ? 'block' : 'none';

    // Footer with Save/Cancel/Edit Profile
    const footerEl = document.getElementById('j-modal-footer');
    const btnEdit = document.getElementById('j-btn-edit-profile');
    const btnCancel = document.getElementById('j-btn-cancel-profile');
    const btnSave = document.getElementById('j-btn-save-profile');

    if (footerEl) footerEl.style.display = allowEdit ? 'flex' : 'none';
    if (btnEdit) btnEdit.style.display = isEdit ? 'none' : 'inline-flex';
    if (btnCancel) btnCancel.style.display = isEdit ? 'inline-flex' : 'none';
    if (btnSave) {
      btnSave.style.display = isEdit ? 'inline-flex' : 'none';
      const theme = ROLE_THEMES[activeRole] || ROLE_THEMES.student;
      btnSave.className = 'c-btn-accent c-tone-' + theme.tone + ' j-save-profile-btn';
    }

    if (isEdit && typeof window.initAllDatePickers === 'function') {
      window.initAllDatePickers();
    }
  }

  /**
   * Jump from Linked Student button to Student Profile
   */
  function openLinkedStudent(childId, childLink) {
    const data = window.__PEOPLE_DATA__ || {};
    const students = data.students || [];

    let targetStudent = null;
    if (childId) {
      targetStudent = students.find(s => (s.id === childId || s.index === childId));
    }

    if (!targetStudent && childLink) {
      const namePart = childLink.split('—')[0].trim().toLowerCase();
      targetStudent = students.find(s => s.name && s.name.toLowerCase().indexOf(namePart) !== -1);
    }

    if (targetStudent) {
      openProfileModal('student', targetStudent.id || targetStudent.index, 'view');
    }
  }

  /**
   * Helper: get value from custom dropdown, datepicker, or standard input
   */
  function getFieldVal(fieldKey) {
    // 1. Check custom dropdown
    const selectEl = document.getElementById('j-select-' + fieldKey);
    if (selectEl) {
      const hidden = selectEl.querySelector('input[type="hidden"]');
      if (hidden && hidden.value) return hidden.value;
      const textVal = selectEl.querySelector('.c-dropdown__value, .j-select-value');
      if (textVal && !textVal.classList.contains('c-dropdown__placeholder')) return textVal.textContent.trim();
    }
    // 2. Check datepicker
    const dpEl = document.getElementById('j-dp-' + fieldKey);
    if (dpEl) {
      const dpInput = dpEl.querySelector('input');
      if (dpInput && dpInput.value) return dpInput.value;
    }
    // 3. Check regular text/email/tel input
    const inputEl = document.getElementById('j-input-' + fieldKey);
    if (inputEl) {
      return inputEl.value.trim();
    }
    return '';
  }

  /**
   * Asynchronously persist edited parent profile to the database
   */
  async function saveParentProfile() {
    const saveButton = document.getElementById('j-btn-save-profile');
    if (saveButton && saveButton.disabled) return;

    const nameInput = document.getElementById('j-modal-name-input');
    const newName = nameInput ? nameInput.value.trim() : (currentPerson.name || '');

    const savingParentId = activeId || currentPerson.id || currentPerson.parent_id;
    const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
    const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const data = new FormData();
    data.set('parentCode', savingParentId);
    data.set('version', currentPerson.profileVersion || '');
    if (csrfToken) data.set('_csrf_token', csrfToken);

    data.set('fullName', newName);
    data.set('firstName', newName ? newName.split(' ')[0] : (currentPerson.firstName || ''));
    data.set('lastName', newName ? (newName.split(' ').slice(1).join(' ') || '') : (currentPerson.lastName || ''));
    data.set('relationship', getFieldVal('parent-relation') || currentPerson.relation || 'Parent');

    const fields = {
      nic: 'parent-nic',
      passport: 'parent-passport',
      dateOfBirth: 'parent-dob',
      occupation: 'parent-occupation',
      employer: 'parent-employer',
      mobile: 'parent-phone',
      homePhone: 'parent-sphone',
      officePhone: 'parent-officephone',
      officeAddress: 'parent-officeaddress',
      homeAddress: 'parent-address',
      emergencyName: 'parent-emname',
      emergencyContact: 'parent-emcontact'
    };

    Object.entries(fields).forEach(function ([k, fieldId]) {
      data.set(k, getFieldVal(fieldId));
    });

    if (saveButton) {
      saveButton.disabled = true;
      saveButton.innerHTML = '<span>Saving changes...</span>';
    }

    try {
      const response = await fetch(`${basePath}/updateParent`, {
        method: 'POST',
        body: data,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.error || 'Failed to update parent details.');
      }

      // Update in memory and directory table
      const storedParent = (window.__PEOPLE_DATA__?.parents || []).find(function (p) {
        return (p.id === savingParentId || p.parent_id === savingParentId);
      });
      if (storedParent && result.parent) {
        Object.assign(storedParent, result.parent);
      }
      if (result.parent) {
        Object.assign(currentPerson, result.parent);
      } else {
        currentPerson.name = newName;
      }
      editedDraft = JSON.parse(JSON.stringify(currentPerson));

      // Sync DOM table row
      updateTableRowDOM('parent', savingParentId, currentPerson);

      // Render view mode
      renderRoleSection('parent', currentPerson, 'view');
      toggleModalMode('view');
      applyModalHeaderTheme('parent', currentPerson);

      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Parent profile updated successfully.', 'success');
      }
    } catch (err) {
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner(err.message || 'Unable to save parent changes.', 'error');
      } else {
        alert(err.message || 'Unable to save parent changes.');
      }
    } finally {
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.innerHTML = '<svg class="c-icon" width="15" height="15"><use href="#icon-check"/></svg><span>Save Changes</span>';
      }
    }
  }

  async function saveStudentProfile() {
    const saveButton = document.getElementById('j-btn-save-profile');
    if (saveButton && saveButton.disabled) return;

    const nameInput = document.getElementById('j-modal-name-input');
    const newName = nameInput ? nameInput.value.trim() : (currentPerson.name || '');
    if (!newName) {
      const nameError = modalEl.querySelector('.j-profile-name-error');
      if (nameError) {
        nameError.textContent = 'Full name is required.';
        nameError.style.display = 'block';
      }
      return;
    }

    const savingStudentId = activeId || currentPerson.id || currentPerson.index;
    const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
    const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Read updated status
    const statusSelect = document.getElementById('j-modal-status-dropdown');
    let selectedStatus = 'Active';
    if (statusSelect) {
      const hiddenStatus = statusSelect.querySelector('input[type="hidden"]');
      selectedStatus = (hiddenStatus && hiddenStatus.value) ? hiddenStatus.value : (statusSelect.value || 'Active');
    }

    const payload = {
      studentIndex: savingStudentId,
      fullName: newName,
      status: selectedStatus,
      grade: getFieldVal('student-grade') || currentPerson.grade,
      classSection: (getFieldVal('student-class') || currentPerson.className || '').replace(/^Class\s*/i, ''),
      dateOfBirth: getFieldVal('student-dob') || currentPerson.dateOfBirth,
      gender: getFieldVal('student-gender') || currentPerson.gender,
      nationalId: getFieldVal('student-bc') || currentPerson.nationalId,
      birthCertificateNumber: getFieldVal('student-bc') || currentPerson.birthCertificateNumber,
      religion: getFieldVal('student-religion') || currentPerson.religion,
      nationality: getFieldVal('student-nationality') || currentPerson.nationality,
      homeAddress: getFieldVal('student-address') || currentPerson.address,
      educationalZone: getFieldVal('student-zone') || currentPerson.educationalZone,
      district: getFieldVal('student-district') || currentPerson.district,
      province: getFieldVal('student-province') || currentPerson.province,
      previousSchool: getFieldVal('student-prevschool') || currentPerson.previousSchool,
      bloodGroup: getFieldVal('student-blood') || currentPerson.bloodGroup,
      medicalNotes: getFieldVal('student-medical') || currentPerson.medicalNotes,
      _csrf_token: csrfToken
    };

    if (saveButton) {
      saveButton.disabled = true;
      saveButton.innerHTML = '<span>Saving changes...</span>';
    }

    try {
      const response = await fetch(`${basePath}/updateStudentProfile`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(payload)
      });
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.error || 'Failed to update student details.');
      }

      // Update in memory
      if (result.student) {
        Object.assign(currentPerson, result.student);
      } else {
        currentPerson.name = newName;
        currentPerson.status = selectedStatus;
      }

      const storedStudent = (window.__PEOPLE_DATA__?.students || []).find(function (s) {
        return (s.id === savingStudentId || s.index === savingStudentId);
      });
      if (storedStudent && result.student) {
        Object.assign(storedStudent, result.student);
      }

      editedDraft = JSON.parse(JSON.stringify(currentPerson));

      // Sync DOM table row
      updateTableRowDOM('student', savingStudentId, currentPerson);

      // Render view mode
      renderRoleSection('student', currentPerson, 'view');
      toggleModalMode('view');
      applyModalHeaderTheme('student', currentPerson);

      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Student profile updated successfully.', 'success');
      }
    } catch (err) {
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner(err.message || 'Unable to save student changes.', 'error');
      } else {
        alert(err.message || 'Unable to save student changes.');
      }
    } finally {
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.innerHTML = '<svg class="c-icon" width="15" height="15"><use href="#icon-check"/></svg><span>Save Changes</span>';
      }
    }
  }

  async function saveTeacherProfile() {
    const saveButton = document.getElementById('j-btn-save-profile');
    if (saveButton && saveButton.disabled) return;

    const nameInput = document.getElementById('j-modal-name-input');
    const newName = nameInput ? nameInput.value.trim() : (currentPerson.name || '');
    if (!newName) {
      const nameError = modalEl.querySelector('.j-profile-name-error');
      if (nameError) {
        nameError.textContent = 'Full name is required.';
        nameError.style.display = 'block';
      }
      return;
    }

    const savingStaffId = activeId || currentPerson.id || currentPerson.staffId;
    const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
    const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Read updated status
    const statusSelect = document.getElementById('j-modal-status-dropdown');
    let selectedStatus = 'Active';
    if (statusSelect) {
      const hiddenStatus = statusSelect.querySelector('input[type="hidden"]');
      selectedStatus = (hiddenStatus && hiddenStatus.value) ? hiddenStatus.value : (statusSelect.value || 'Active');
    }

    // Read qualifications from edit list if present
    const qualRows = modalEl.querySelectorAll('#j-teacher-qual-edit-list .c-qual-edit-row');
    const newQuals = [];
    qualRows.forEach(function (row) {
      const title = row.querySelector('.j-qual-title') ? row.querySelector('.j-qual-title').value.trim() : '';
      const inst = row.querySelector('.j-qual-institution') ? row.querySelector('.j-qual-institution').value.trim() : '';
      const year = row.querySelector('.j-qual-year') ? row.querySelector('.j-qual-year').value.trim() : '';
      if (title || inst) {
        newQuals.push({ title: title, institution: inst, year: year });
      }
    });

    const payload = {
      staffId: savingStaffId,
      fullName: newName,
      status: selectedStatus,
      nic: getFieldVal('teacher-nic') || currentPerson.nic,
      dateOfBirth: getFieldVal('teacher-dob') || currentPerson.dateOfBirth,
      phone: getFieldVal('teacher-phone') || currentPerson.phone,
      personalEmail: getFieldVal('teacher-pemail') || getFieldVal('teacher-pemail2') || currentPerson.personalEmail,
      officeAddress: currentPerson.officeAddress || '',
      subjects: getFieldVal('teacher-subject') || currentPerson.subjects || currentPerson.subject,
      experience: getFieldVal('teacher-exp') || currentPerson.experience,
      joinDate: getFieldVal('teacher-joindate') || currentPerson.joinDate,
      emergencyName: getFieldVal('teacher-emname') || currentPerson.emergencyName,
      emergencyPhone: getFieldVal('teacher-emphone') || currentPerson.emergencyPhone,
      qualifications: newQuals.length ? newQuals : (currentPerson.qualifications || []),
      _csrf_token: csrfToken
    };

    if (saveButton) {
      saveButton.disabled = true;
      saveButton.innerHTML = '<span>Saving changes...</span>';
    }

    try {
      const response = await fetch(`${basePath}/updateTeacherProfile`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(payload)
      });
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.error || 'Failed to update teacher details.');
      }

      // Update in memory
      if (result.teacher) {
        Object.assign(currentPerson, result.teacher);
      } else {
        currentPerson.name = newName;
        currentPerson.status = selectedStatus;
      }

      const storedTeacher = (window.__PEOPLE_DATA__?.teachers || []).find(function (t) {
        return (t.id === savingStaffId || t.staffId === savingStaffId);
      });
      if (storedTeacher && result.teacher) {
        Object.assign(storedTeacher, result.teacher);
      }

      editedDraft = JSON.parse(JSON.stringify(currentPerson));

      // Sync DOM table row
      updateTableRowDOM('teacher', savingStaffId, currentPerson);

      // Render view mode
      renderRoleSection('teacher', currentPerson, 'view');
      toggleModalMode('view');
      applyModalHeaderTheme('teacher', currentPerson);

      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Teacher profile updated successfully.', 'success');
      }
    } catch (err) {
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner(err.message || 'Unable to save teacher changes.', 'error');
      } else {
        alert(err.message || 'Unable to save teacher changes.');
      }
    } finally {
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.innerHTML = '<svg class="c-icon" width="15" height="15"><use href="#icon-check"/></svg><span>Save Changes</span>';
      }
    }
  }

  async function saveManagementProfile() {
    const saveButton = document.getElementById('j-btn-save-profile');
    if (saveButton && saveButton.disabled) return;

    const nameInput = document.getElementById('j-modal-name-input');
    const newName = nameInput ? nameInput.value.trim() : (currentPerson.name || '');
    if (!newName) {
      const nameError = modalEl.querySelector('.j-profile-name-error');
      if (nameError) {
        nameError.textContent = 'Full name is required.';
        nameError.style.display = 'block';
      }
      return;
    }

    const savingStaffId = activeId || currentPerson.id || currentPerson.staffId;
    const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/management';
    const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value ||
                      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Read updated status
    const statusSelect = document.getElementById('j-modal-status-dropdown');
    let selectedStatus = 'Active';
    if (statusSelect) {
      const hiddenStatus = statusSelect.querySelector('input[type="hidden"]');
      selectedStatus = (hiddenStatus && hiddenStatus.value) ? hiddenStatus.value : (statusSelect.value || 'Active');
    }

    const payload = {
      staffId: savingStaffId,
      fullName: newName,
      status: selectedStatus,
      nic: getFieldVal('mgmt-nic') || currentPerson.nic,
      dob: getFieldVal('mgmt-dob') || currentPerson.dateOfBirth || currentPerson.dob,
      phone: getFieldVal('mgmt-phone') || currentPerson.phone,
      personalEmail: getFieldVal('mgmt-pemail') || currentPerson.personalEmail,
      title: getFieldVal('mgmt-title') || currentPerson.jobTitle || currentPerson.title,
      officeLocation: getFieldVal('mgmt-office') || currentPerson.officeLocation,
      joinDate: getFieldVal('mgmt-joining') || currentPerson.joinDate,
      emergencyName: getFieldVal('mgmt-emname') || currentPerson.emergencyName,
      emergencyPhone: getFieldVal('mgmt-emergency') || currentPerson.emergencyPhone,
      _csrf_token: csrfToken
    };

    if (saveButton) {
      saveButton.disabled = true;
      saveButton.innerHTML = '<span>Saving changes...</span>';
    }

    try {
      const response = await fetch(`${basePath}/updateManagementProfile`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(payload)
      });
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.error || 'Failed to update staff details.');
      }

      // Update in memory
      if (result.management) {
        Object.assign(currentPerson, result.management);
      } else {
        currentPerson.name = newName;
        currentPerson.status = selectedStatus;
      }

      const storedStaff = (window.__PEOPLE_DATA__?.management || []).find(function (m) {
        return (m.id === savingStaffId || m.staffId === savingStaffId);
      });
      if (storedStaff && result.management) {
        Object.assign(storedStaff, result.management);
      }

      editedDraft = JSON.parse(JSON.stringify(currentPerson));

      // Sync DOM table row
      updateTableRowDOM('management', savingStaffId, currentPerson);

      // Render view mode
      renderRoleSection('management', currentPerson, 'view');
      toggleModalMode('view');
      applyModalHeaderTheme('management', currentPerson);

      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Staff profile updated successfully.', 'success');
      }
    } catch (err) {
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner(err.message || 'Unable to save staff changes.', 'error');
      } else {
        alert(err.message || 'Unable to save staff changes.');
      }
    } finally {
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.innerHTML = '<svg class="c-icon" width="15" height="15"><use href="#icon-check"/></svg><span>Save Changes</span>';
      }
    }
  }

  /**
   * Save Profile Changes in place
   */
  function saveProfileChanges() {
    if (activeRole === 'parent' && (currentPerson.persisted || (currentPerson.id && currentPerson.id.startsWith('PAR-')))) {
      saveParentProfile();
      return;
    }
    if (activeRole === 'student' && (currentPerson.persisted || (currentPerson.id && currentPerson.id.startsWith('STU-')) || (currentPerson.index && currentPerson.index.startsWith('S20')))) {
      saveStudentProfile();
      return;
    }
    if (activeRole === 'teacher' && (currentPerson.persisted || (currentPerson.id && (currentPerson.id.startsWith('TEA-') || currentPerson.id.startsWith('T-'))))) {
      saveTeacherProfile();
      return;
    }
    if (activeRole === 'management' && (currentPerson.persisted || (currentPerson.id && (currentPerson.id.startsWith('MAN-') || currentPerson.id.startsWith('M-'))))) {
      saveManagementProfile();
      return;
    }

    const nameInput = document.getElementById('j-modal-name-input');
    const nameError = modalEl.querySelector('.j-profile-name-error');
    const newName = nameInput ? nameInput.value.trim() : '';

    if (!newName) {
      if (nameError) {
        nameError.textContent = 'Full name is required.';
        nameError.style.display = 'block';
      }
      return;
    }
    if (nameError) nameError.style.display = 'none';

    // Update object in memory
    currentPerson.name = newName;
    const nameViewEl = document.getElementById('j-modal-name-view');
    if (nameViewEl) nameViewEl.textContent = newName;

    // Read updated status
    const statusSelect = document.getElementById('j-modal-status-dropdown');
    if (statusSelect) {
      const hiddenStatus = statusSelect.querySelector('input[type="hidden"]');
      const selectedStatus = (hiddenStatus && hiddenStatus.value) ? hiddenStatus.value : (statusSelect.value || 'Active');
      currentPerson.status = selectedStatus;
      const statusPill = document.getElementById('j-modal-status-pill');
      if (statusPill) {
        statusPill.textContent = selectedStatus;
        statusPill.className = 'c-status-pill ' + (selectedStatus === 'Active' ? 'c-status-active' : 'c-status-inactive');
      }
    }

    if (activeRole === 'student') {
      const fields = [
        'student-email', 'student-grade', 'student-class', 'student-bc', 'student-dob',
        'student-gender', 'student-admission', 'student-blood', 'student-nationality',
        'student-religion', 'student-prevschool', 'student-address', 'student-zone',
        'student-district', 'student-province', 'student-medical'
      ];
      fields.forEach(function (f) {
        const val = getFieldVal(f);
        if (val !== '') {
          setFieldVal(f, val);
          if (f === 'student-email') currentPerson.email = val;
          if (f === 'student-grade') currentPerson.grade = val;
          if (f === 'student-class') currentPerson.className = val.replace('Class ', '');
          if (f === 'student-dob') currentPerson.dateOfBirth = val;
        }
      });
      const gRel = getFieldVal('guardian-rel');
      if (gRel) {
        const gRelEl = document.getElementById('j-card-guardian-rel');
        if (gRelEl) gRelEl.textContent = gRel + ' · Account connected';
      }
    } else if (activeRole === 'teacher') {
      const tFields = [
        'teacher-email', 'teacher-pemail', 'teacher-phone', 'teacher-classincharge',
        'teacher-subject', 'teacher-tic', 'teacher-tic-role', 'teacher-nic', 'teacher-dob',
        'teacher-pemail2', 'teacher-exp', 'teacher-joindate',
        'teacher-emname', 'teacher-emphone'
      ];
      tFields.forEach(function (f) {
        const val = getFieldVal(f);
        if (val !== '') {
          setFieldVal(f, val);
        }
      });
      currentPerson.email = getFieldVal('teacher-email') || currentPerson.email;
      currentPerson.phone = getFieldVal('teacher-phone') || currentPerson.phone;
      currentPerson.personalEmail = getFieldVal('teacher-pemail') || getFieldVal('teacher-pemail2') || currentPerson.personalEmail;
      currentPerson.classTeacherOf = (getFieldVal('teacher-classincharge') || '').replace(/^Class\s*/i, '') || currentPerson.classTeacherOf;
      currentPerson.tic = getFieldVal('teacher-tic') || currentPerson.tic;
      currentPerson.ticRole = getFieldVal('teacher-tic-role') || currentPerson.ticRole;
      currentPerson.subject = getFieldVal('teacher-subject') || currentPerson.subject;
      currentPerson.nic = getFieldVal('teacher-nic') || currentPerson.nic;
      currentPerson.dateOfBirth = getFieldVal('teacher-dob') || currentPerson.dateOfBirth;
      currentPerson.experience = getFieldVal('teacher-exp') || currentPerson.experience;
      currentPerson.joinDate = getFieldVal('teacher-joindate') || currentPerson.joinDate;
      currentPerson.emergencyName = getFieldVal('teacher-emname') || currentPerson.emergencyName;
      currentPerson.emergencyPhone = getFieldVal('teacher-emphone') || currentPerson.emergencyPhone;

      // Workload
      const wlInput = document.getElementById('j-input-teacher-workload');
      if (wlInput && wlInput.value) {
        currentPerson.workload = wlInput.value.trim();
        const wlNote = document.getElementById('j-teacher-workload');
        if (wlNote) wlNote.textContent = currentPerson.workload;
      }

      // Save assignments from edit list
      const assignRows = modalEl.querySelectorAll('#j-teacher-assignment-edit-list .c-assignment-edit-row');
      const newAssignments = [];
      assignRows.forEach(function (row) {
        const subj = row.querySelector('.j-assign-subject') ? row.querySelector('.j-assign-subject').value.trim() : '';
        const cls = row.querySelector('.j-assign-class') ? row.querySelector('.j-assign-class').value.trim() : '';
        if (subj || cls) {
          newAssignments.push({ subject: subj || currentPerson.subject || 'Subject', className: cls.replace(/^Class\s*/i, '') });
        }
      });
      if (newAssignments.length) {
        currentPerson.subjectAssignments = newAssignments;
        currentPerson.classes = newAssignments.map(a => a.className);
        const assignEl = document.getElementById('j-teacher-assignments');
        if (assignEl) {
          assignEl.innerHTML = newAssignments.map(function (a) {
            return '<div class="c-assignment-row">' +
              '<span class="c-assignment-subject">' + escapeHtml(a.subject) + '</span>' +
              '<span class="c-assignment-pill c-assignment-pill--sunshine">Class ' + escapeHtml(a.className) + '</span>' +
            '</div>';
          }).join('');
        }
      }

      // Save qualifications from edit list
      const qualRows = modalEl.querySelectorAll('#j-teacher-qual-edit-list .c-qual-edit-row');
      const newQuals = [];
      qualRows.forEach(function (row) {
        const title = row.querySelector('.j-qual-title') ? row.querySelector('.j-qual-title').value.trim() : '';
        const inst = row.querySelector('.j-qual-institution') ? row.querySelector('.j-qual-institution').value.trim() : '';
        const year = row.querySelector('.j-qual-year') ? row.querySelector('.j-qual-year').value.trim() : '';
        if (title || inst) {
          newQuals.push({ title: title, institution: inst, year: year });
        }
      });
      if (newQuals.length) {
        currentPerson.qualifications = newQuals;
        const qualEl = document.getElementById('j-teacher-qualifications');
        if (qualEl) {
          qualEl.innerHTML = newQuals.map(function (q) {
            return '<div style="background:var(--color-alabaster, #F8F9FA);padding:0.75rem 1rem;border-radius:var(--radius-lg, 0.5rem);border:1px solid rgba(15,65,74,0.1);">' +
              '<div style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);">' + escapeHtml(q.title) + '</div>' +
              '<div style="font-size:0.75rem;color:rgba(15,65,74,0.65);margin-top:0.25rem;">' + escapeHtml(q.institution) + (q.year ? ' • ' + escapeHtml(q.year) : '') + '</div>' +
            '</div>';
          }).join('');
        }
      }

      // Subtitle
      const subEl = document.getElementById('j-modal-subtitle');
      if (subEl) {
        subEl.textContent = (currentPerson.subject || 'Faculty') + ' Teacher · ' + (currentPerson.role || 'Class Teacher');
      }
    } else if (activeRole === 'parent') {
      const pFields = [
        'parent-email', 'parent-phone', 'parent-sphone', 'parent-fullname',
        'parent-relation', 'parent-nic', 'parent-passport', 'parent-dob',
        'parent-emname', 'parent-emcontact', 'parent-guardianstatus',
        'parent-occupation', 'parent-employer', 'parent-officephone',
        'parent-officeaddress', 'parent-address'
      ];
      pFields.forEach(function (f) {
        const val = getFieldVal(f);
        if (val !== '') {
          setFieldVal(f, val);
        }
      });
      currentPerson.email = getFieldVal('parent-email') || currentPerson.email;
      currentPerson.phone = getFieldVal('parent-phone') || currentPerson.phone;
      currentPerson.secondaryContact = getFieldVal('parent-sphone') || currentPerson.secondaryContact;
      currentPerson.relation = getFieldVal('parent-relation') || currentPerson.relation;
      currentPerson.relationship = currentPerson.relation;
      currentPerson.nic = getFieldVal('parent-nic') || currentPerson.nic;
      currentPerson.passport = getFieldVal('parent-passport') || currentPerson.passport;
      currentPerson.dateOfBirth = getFieldVal('parent-dob') || currentPerson.dateOfBirth;
      currentPerson.emergencyName = getFieldVal('parent-emname') || currentPerson.emergencyName;
      currentPerson.emergencyContact = getFieldVal('parent-emcontact') || currentPerson.emergencyContact;
      currentPerson.guardianStatus = getFieldVal('parent-guardianstatus') || currentPerson.guardianStatus;
      currentPerson.occupation = getFieldVal('parent-occupation') || currentPerson.occupation;
      currentPerson.employer = getFieldVal('parent-employer') || currentPerson.employer;
      currentPerson.officePhone = getFieldVal('parent-officephone') || currentPerson.officePhone;
      currentPerson.officeAddress = getFieldVal('parent-officeaddress') || currentPerson.officeAddress;
      currentPerson.address = getFieldVal('parent-address') || currentPerson.address;

      // Save linked children from edit list
      const linkedRows = modalEl.querySelectorAll('#j-parent-linked-edit-list .c-linked-edit-row');
      const newChildren = [];
      linkedRows.forEach(function (row) {
        const cName = row.querySelector('.j-linked-name') ? row.querySelector('.j-linked-name').value.trim() : '';
        const cCls = row.querySelector('.j-linked-class') ? row.querySelector('.j-linked-class').value.trim() : '';
        if (cName) {
          newChildren.push({ name: cName, className: cCls.replace(/^Class\s*/i, '') });
        }
      });
      if (newChildren.length) {
        currentPerson.linkedStudents = newChildren;
        currentPerson.children = newChildren.map(c => c.name + ' — ' + c.className);
        const linkedWrap = document.getElementById('j-parent-linked-students');
        if (linkedWrap) {
          linkedWrap.innerHTML = newChildren.map(function (child) {
            return '<button type="button" class="c-linked-btn j-open-linked-child" data-link="' + escapeHtml(child.name + ' — ' + child.className) + '" data-name="' + escapeHtml(child.name) + '">' +
              '<span class="c-linked-left">' +
                '<svg class="c-icon" width="16" height="16"><use href="#icon-checkCircle"/></svg>' +
                '<span class="c-linked-name">' + escapeHtml(child.name) + '</span>' +
              '</span>' +
              '<span class="c-assignment-pill c-assignment-pill--terracotta">Class ' + escapeHtml(child.className) + '</span>' +
            '</button>';
          }).join('');
        }
      }

      // Subtitle
      const subEl = document.getElementById('j-modal-subtitle');
      if (subEl) {
        subEl.textContent = (currentPerson.relation || 'Parent') + (currentPerson.children && currentPerson.children.length ? ' of ' + currentPerson.children.join(', ') : '');
      }
    } else if (activeRole === 'management') {
      const mFields = [
        'mgmt-email', 'mgmt-phone', 'mgmt-title', 'mgmt-joining',
        'mgmt-office', 'mgmt-fullname', 'mgmt-nic', 'mgmt-dob', 'mgmt-pemail',
        'mgmt-resaddress', 'mgmt-emname', 'mgmt-emergency'
      ];
      mFields.forEach(function (f) {
        const val = getFieldVal(f);
        if (val !== '') {
          setFieldVal(f, val);
        }
      });
      currentPerson.email = getFieldVal('mgmt-email') || currentPerson.email;
      currentPerson.phone = getFieldVal('mgmt-phone') || currentPerson.phone;
      currentPerson.jobTitle = getFieldVal('mgmt-title') || currentPerson.jobTitle;
      currentPerson.joiningDate = getFieldVal('mgmt-joining') || currentPerson.joiningDate;
      currentPerson.officeLocation = getFieldVal('mgmt-office') || currentPerson.officeLocation;
      currentPerson.officeAddress = currentPerson.officeLocation;
      currentPerson.nic = getFieldVal('mgmt-nic') || currentPerson.nic;
      currentPerson.dateOfBirth = getFieldVal('mgmt-dob') || currentPerson.dateOfBirth;
      currentPerson.personalEmail = getFieldVal('mgmt-pemail') || currentPerson.personalEmail;
      currentPerson.personalAddress = getFieldVal('mgmt-resaddress') || currentPerson.personalAddress;
      currentPerson.address = currentPerson.personalAddress;
      const mEmName = getFieldVal('mgmt-emname') || 'Emma Thompson';
      const mEmPhone = getFieldVal('mgmt-emergency') || '+94 77 000 0091';
      currentPerson.emergencyName = mEmName;
      currentPerson.emergencyPhone = mEmPhone;
      currentPerson.emergencyContact = mEmName + ' · ' + mEmPhone;

      // Subtitle
      const subEl = document.getElementById('j-modal-subtitle');
      if (subEl) {
        subEl.textContent = currentPerson.jobTitle || 'Principal · Administrative Scope';
      }
    } else if (activeRole === 'admin') {
      const aFields = [
        'admin-email', 'admin-phone', 'admin-noc', 'admin-title',
        'admin-joindate', 'admin-office', 'admin-fullname', 'admin-nic',
        'admin-dob', 'admin-pemail', 'admin-resaddress', 'admin-emname', 'admin-emergency'
      ];
      aFields.forEach(function (f) {
        const val = getFieldVal(f);
        if (val !== '') {
          setFieldVal(f, val);
        }
      });
      currentPerson.email = getFieldVal('admin-email') || currentPerson.email;
      currentPerson.phone = getFieldVal('admin-phone') || currentPerson.phone;
      currentPerson.nocPhone = getFieldVal('admin-noc') || currentPerson.nocPhone;
      currentPerson.jobTitle = getFieldVal('admin-title') || currentPerson.jobTitle;
      currentPerson.joinDate = getFieldVal('admin-joindate') || currentPerson.joinDate;
      currentPerson.officeLocation = getFieldVal('admin-office') || currentPerson.officeLocation;
      currentPerson.officeAddress = currentPerson.officeLocation;
      currentPerson.name = getFieldVal('admin-fullname') || currentPerson.name;
      currentPerson.nic = getFieldVal('admin-nic') || currentPerson.nic;
      currentPerson.dateOfBirth = getFieldVal('admin-dob') || currentPerson.dateOfBirth;
      currentPerson.personalEmail = getFieldVal('admin-pemail') || currentPerson.personalEmail;
      currentPerson.personalAddress = getFieldVal('admin-resaddress') || currentPerson.personalAddress;
      currentPerson.address = currentPerson.personalAddress;
      currentPerson.emergencyName = getFieldVal('admin-emname') || currentPerson.emergencyName;
      currentPerson.emergencyContact = getFieldVal('admin-emergency') || currentPerson.emergencyContact;

      // Subtitle
      const subEl = document.getElementById('j-modal-subtitle');
      if (subEl) {
        subEl.textContent = currentPerson.jobTitle || 'System Administrator · IT Director';
      }
    }

    // Update table row in DOM if present
    updateTableRowDOM(activeRole, activeId, currentPerson);

    // Switch back to view mode!
    toggleModalMode('view');
  }

  /**
   * Sync DOM row in directory table
   */
  function updateTableRowDOM(role, id, person) {
    const row = document.querySelector(`.j-person-row[data-role="${role}"][data-id="${id}"]`);
    if (!row) return;

    // Update Name
    const nameEl = row.querySelector('.c-person-name');
    if (nameEl) nameEl.textContent = person.name;

    // Update Email
    const mailSpan = row.querySelector('.c-mail-inline span, .c-contact-line span');
    if (mailSpan && person.email) mailSpan.textContent = person.email;

    // Update Status Pill / Dropdown
    const statusPill = row.querySelector('.c-status-pill');
    if (statusPill && person.status) {
      statusPill.textContent = person.status;
      statusPill.className = 'c-status-pill ' + (person.status === 'Active' ? 'c-status-active' : 'c-status-inactive');
    }
    const statusDropdown = row.querySelector('.c-dropdown--status');
    if (statusDropdown && person.status) {
      statusDropdown.classList.remove('c-dropdown--status-active', 'c-dropdown--status-deactivated');
      statusDropdown.classList.add(person.status === 'Active' ? 'c-dropdown--status-active' : 'c-dropdown--status-deactivated');
      const trigText = statusDropdown.querySelector('.c-dropdown__trigger-text');
      if (trigText) trigText.textContent = person.status;
    }

    // Role-specific updates: Teacher
    if (role === 'teacher') {
      const subj = person.subject || person.subjects;
      if (subj) {
        row.setAttribute('data-subject', subj);
        const subjP = row.querySelector('td:nth-child(3) p');
        if (subjP) subjP.textContent = subj;
      }
      const phoneSpan = row.querySelectorAll('.c-contact-line span')[1];
      if (phoneSpan && person.phone) phoneSpan.textContent = person.phone;
    }

    // Role-specific updates: Management
    if (role === 'management') {
      const jobTitle = person.jobTitle || person.title;
      if (jobTitle) {
        const titleP = row.querySelector('.c-subtext');
        if (titleP) titleP.textContent = jobTitle;
      }
      const phoneSpan = row.querySelectorAll('.c-contact-line span')[1];
      if (phoneSpan && person.phone) phoneSpan.textContent = person.phone;
    }

    // Role-specific updates: Admin
    if (role === 'admin') {
      const jobTitle = person.jobTitle || person.title;
      if (jobTitle) {
        const titleP = row.querySelector('.c-subtext');
        if (titleP) titleP.textContent = jobTitle;
      }
      const phoneSpan = row.querySelectorAll('.c-contact-line span')[1];
      if (phoneSpan && person.phone) phoneSpan.textContent = person.phone;
    }
  }

  /**
   * Helper: set text content of read-only card
   */
  function setCardVal(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val || 'Not recorded';
  }

  /**
   * Helper: set both view text and edit input value, plus dropdown/datepicker sync
   */
  function setFieldVal(fieldKey, val) {
    const viewEl = document.getElementById('j-card-' + fieldKey);
    const inputEl = document.getElementById('j-input-' + fieldKey);
    if (viewEl) {
      if (fieldKey === 'teacher-exp' && val && !String(val).includes('years')) {
        viewEl.textContent = val + ' years';
      } else {
        viewEl.textContent = val || 'Not recorded';
      }
    }
    if (inputEl) {
      if (fieldKey === 'teacher-exp' && val) {
        inputEl.value = parseInt(val, 10) || val;
      } else {
        inputEl.value = val || '';
      }
    }

    // Check custom dropdown
    const selectEl = document.getElementById('j-select-' + fieldKey);
    if (selectEl && val) {
      if (typeof window.setDropdownValue === 'function') {
        window.setDropdownValue(selectEl, val);
      } else {
        const valLabel = selectEl.querySelector('.j-select-value, .c-dropdown__value, .c-select__trigger span');
        if (valLabel) {
          valLabel.textContent = val;
          valLabel.classList.remove('c-dropdown__placeholder');
        }
        const hidden = selectEl.querySelector('input[type="hidden"]');
        if (hidden) hidden.value = val;
        const trigger = selectEl.querySelector('.c-dropdown__trigger');
        if (trigger) {
          trigger.classList.add('has-value');
          trigger.classList.remove('is-placeholder');
        }
      }
    }

    // Check datepicker
    const dpEl = document.getElementById('j-dp-' + fieldKey);
    if (dpEl && val) {
      if (typeof dpEl.setDateVal === 'function') {
        dpEl.setDateVal(val);
      } else {
        const dpInput = dpEl.querySelector('input');
        if (dpInput) dpInput.value = val;
        const dpLabel = dpEl.querySelector('.j-dp-label');
        if (dpLabel) {
          const d = new Date(val);
          if (!isNaN(d.getTime())) {
            dpLabel.textContent = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
          } else {
            dpLabel.textContent = val;
          }
          dpLabel.classList.remove('c-dp-placeholder');
        }
      }
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

  // Expose to window for global access
  window.openProfileModal = openProfileModal;
  window.closeProfileModal = closeProfileModal;

  // Initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
