(function () {
  'use strict';
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
      // 1. View button on row
      const viewBtn = e.target.closest('.j-open-profile');
      if (viewBtn) {
        e.preventDefault();
        const role = viewBtn.getAttribute('data-role') || 'student';
        const id = viewBtn.getAttribute('data-id') || '';
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
        const parentId = jumpParentBtn.getAttribute('data-parent-id') || 'P-045';
        openProfileModal('parent', parentId, 'view');
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
    const oldError = document.getElementById('parent-edit-error');
    if (oldError) oldError.hidden = true;
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

    if (person.student && person.student.guardian && person.student.guardian.isAvailable !== false) {
      linkedParent = person.student.guardian;
    } else {
      linkedParent = parents.find(function (p) {
        if (!p.children) return false;
        return p.children.some(function (c) {
          return c.indexOf(person.name) !== -1 || (person.id && c.indexOf(person.id) !== -1);
        });
      });
    }

    if (!linkedParent) {
      if (person.name && person.name.indexOf('Perera') !== -1) {
        linkedParent = parents.find(p => p.id === 'P-045') || {
          id: 'P-045',
          name: 'Suresh Perera',
          relation: 'Father',
          email: 's.perera@gmail.com',
          phone: '+94 77 234 5678',
          initials: 'SP',
          status: 'Active',
          avatarTone: 'bg-terracotta text-white'
        };
      } else if (person.guardian) {
        linkedParent = person.guardian;
      }
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

    setFieldVal('teacher-tic', person.tic || 'Debating Society');
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
    setCardVal('j-card-parent-id', person.id || person.index || '');
    setFieldVal('parent-email', person.email || '');
    setFieldVal('parent-phone', person.phone || '');
    setFieldVal('parent-sphone', person.secondaryContact || person.secondaryPhone || '');

    // Personal
    setFieldVal('parent-fullname', person.name || '');
    setFieldVal('parent-relation', person.relation || person.relationship || '');
    setFieldVal('parent-nic', person.nic || person.identityReference || '');
    setFieldVal('parent-passport', person.passport || '');
    setFieldVal('parent-dob', person.dateOfBirth || person.dob || '');
    setFieldVal('parent-emname', person.emergencyName || '');
    setFieldVal('parent-emcontact', person.emergencyContact || '');
    setFieldVal('parent-guardianstatus', person.guardianStatus || '');

    // Enrollment
    setFieldVal('parent-occupation', person.occupation || '');
    setFieldVal('parent-employer', person.employer || '');
    setFieldVal('parent-officephone', person.officePhone || '');
    setFieldVal('parent-officeaddress', person.officeAddress || '');
    setFieldVal('parent-address', person.address || person.homeAddress || '');

    // Linked Student Accounts
    let children = person.linkedStudents || person.children;
    if (!children || !children.length) {
      children = [];
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
          return '<div class="c-linked-btn" style="cursor:default;pointer-events:none">' +
            '<span>' + escapeHtml(childLink) + '</span>' +
            (child.id ? '<span class="c-subtext">' + escapeHtml(child.id) + '</span>' : '') + '</div>';
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
    setFieldVal('mgmt-pemail', person.personalEmail || 'malik.personal@gmail.com');
    setFieldVal('mgmt-resaddress', person.personalAddress || person.address || '14 Palm Grove, Colombo 07');

    // Emergency account
    const emName = person.emergencyName || (person.emergencyContact || '').split(' · ')[0] || (person.emergencyContact || '').split(' - ')[0] || 'Emma Thompson';
    const emPhone = person.emergencyPhone || (person.emergencyContact || '').split(' · ')[1] || (person.emergencyContact || '').split(' - ')[1] || '+94 77 000 0091';
    setFieldVal('mgmt-emname', emName);
    setFieldVal('mgmt-emergency', emPhone);
  }

  /**
   * Toggle Modal between View and Edit modes
   */
  function toggleModalMode(mode) {
    const persistedParent = activeRole === 'parent' && (window.__PEOPLE_DATA__?.parents || []).some(p => p.persisted && p.id === activeId);
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
    if (isEdit && nameInputEl && !persistedParent) {
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

    if (persistedParent) {
      if (nameViewEl) nameViewEl.style.display = 'block';
      if (nameEditWrap) nameEditWrap.style.display = 'none';
      if (statusPill) statusPill.style.display = 'inline-block';
      if (statusSelect) statusSelect.style.display = 'none';

      ['parent-email', 'parent-fullname'].forEach(function (field) {
        const input = document.getElementById('j-input-' + field);
        const value = document.getElementById('j-card-' + field);
        if (input) input.style.display = 'none';
        if (value) value.style.display = '';
      });
      const relationship = document.getElementById('j-select-parent-relation');
      if (relationship) relationship.closest('.j-edit-only').style.display = 'none';
      document.getElementById('j-card-parent-relation').style.display = '';
      const guardianStatus = document.getElementById('j-select-parent-guardianstatus');
      if (guardianStatus) guardianStatus.closest('.c-info-card').style.display = 'none';
      document.getElementById('j-parent-linked-edit-wrap').style.display = 'none';
      document.getElementById('j-parent-linked-students').parentElement.style.display = '';
    }

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
    } else {
      openProfileModal('student', 'S2021-091', 'view');
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

  async function saveParentProfile() {
    const saveButton = document.getElementById('j-btn-save-profile');
    if (saveButton.disabled) return;
    let errorBox = document.getElementById('parent-edit-error');
    if (!errorBox) {
      errorBox = document.createElement('p');
      errorBox.id = 'parent-edit-error';
      errorBox.setAttribute('role', 'alert');
      errorBox.style.cssText = 'padding:1rem;background:#fbe9e7;color:#8b2419;border-radius:8px';
      document.getElementById('j-modal-body').prepend(errorBox);
    }
    errorBox.hidden = true;
    errorBox.style.background = '#fbe9e7';
    errorBox.style.color = '#8b2419';
    const data = new FormData();
    data.set('parentCode', activeId);
    data.set('version', currentPerson.profileVersion);
    data.set('_csrf_token', document.querySelector('input[name="_csrf_token"]').value);
    data.set('fullName', currentPerson.name);
    data.set('firstName', currentPerson.firstName);
    data.set('lastName', currentPerson.lastName);
    data.set('relationship', currentPerson.relation);
    const savingParentId = activeId;
    const fields = {
      nic:'parent-nic',
      dateOfBirth:'parent-dob', occupation:'parent-occupation',
      mobile:'parent-phone', passport:'parent-passport', employer:'parent-employer',
      homePhone:'parent-sphone', officePhone:'parent-officephone', officeAddress:'parent-officeaddress',
      homeAddress:'parent-address', emergencyName:'parent-emname', emergencyContact:'parent-emcontact'
    };
    Object.entries(fields).forEach(function ([name, field]) { data.set(name, getFieldVal(field)); });
    saveButton.disabled = true;
    try {
      const response = await fetch('/management/updateParent', {
        method:'POST', body:data,
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.error || 'Unable to save changes.');
      const storedParent = window.__PEOPLE_DATA__.parents.find(parent => parent.id === savingParentId);
      if (storedParent) Object.assign(storedParent, result.parent);

      const row = Array.from(document.querySelectorAll('.j-person-row[data-role="parent"]'))
        .find(row => row.dataset.id === savingParentId);
      if (row) {
        const contacts = row.querySelectorAll('.c-contact-line > span');
        if (contacts[0]) contacts[0].textContent = result.parent.email;
        if (contacts[1]) contacts[1].textContent = result.parent.phone;
      }
      if (activeRole === 'parent' && activeId === savingParentId) {
        currentPerson = Object.assign({}, result.parent);
        editedDraft = JSON.parse(JSON.stringify(currentPerson));
        renderRoleSection('parent', currentPerson, 'view');
        toggleModalMode('view');
        applyModalHeaderTheme('parent', currentPerson);
        document.getElementById('j-btn-edit-profile').focus();
        errorBox.textContent = 'Parent details saved.';
        errorBox.style.background = '#edf3e8';
        errorBox.style.color = '#344a26';
        errorBox.hidden = false;
      }
    } catch (error) {
      errorBox.textContent = error.message || 'Unable to save changes. Try again.';
      errorBox.hidden = false;
      errorBox.scrollIntoView({block:'nearest'});
    } finally {
      saveButton.disabled = false;
    }
  }

  function saveProfileChanges() {
    if (activeRole === 'parent' && currentPerson.persisted) {
      saveParentProfile();
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
        'teacher-subject', 'teacher-tic', 'teacher-nic', 'teacher-dob',
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
        'mgmt-office', 'mgmt-fullname', 'mgmt-nic', 'mgmt-pemail',
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

    // Check datepicker
    const dpEl = document.getElementById('j-dp-' + fieldKey);
    if (dpEl && val) {
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