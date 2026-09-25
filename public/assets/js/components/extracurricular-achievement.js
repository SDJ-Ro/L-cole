// MVC/public/assets/js/components/extracurricular-achievement.js
// Dedicated Component Controller for _extracurricular_card_achievement_page.php
// Unified controller handling View, Inline Edit, and Creation of Achievements.

document.addEventListener('DOMContentLoaded', () => {

  const clubDetailView = document.getElementById('j-view-club-detail');
  const viewAchPage = document.getElementById('j-achievement-page-view');

  let currentActiveClub = null;
  let currentActiveAch = null;
  let currentActiveAchIndex = -1;

  // Active Achievement state (used for both Edit and Create flows)
  let activeCoverImage = '';
  let activeGalleryImages = [];
  let activeParticipantsList = [];
  let activeSelectedAgeGroup = 'Under 19';
  let activeSelectedTeam = '1st XI Team';
  let activeSelectedIndividual = '';

  // Parse active club data
  const getActiveClub = () => {
    if (window.currentExtracurricularClub) return window.currentExtracurricularClub;
    if (clubDetailView && clubDetailView.dataset.clubs) {
      try {
        const clubs = JSON.parse(clubDetailView.dataset.clubs);
        const heroId = document.getElementById('j-club-detail-hero')?.dataset.clubId;
        if (heroId) return clubs.find(c => String(c.id) === String(heroId)) || clubs[0];
        return clubs[0];
      } catch (e) {}
    }
    return null;
  };

  const escapeHtml = window.escapeHtml || ((text) => {
    if (!text && text !== 0) return '';
    return String(text).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  });

  const readImageFile = (file) => {
    return new Promise((resolve, reject) => {
      if (!file || !file.type.startsWith('image/')) {
        resolve(null);
        return;
      }
      const reader = new FileReader();
      reader.onload = (e) => resolve(e.target.result);
      reader.onerror = (e) => reject(e);
      reader.readAsDataURL(file);
    });
  };

  const getClubTeamNames = (club) => {
    if (club && club.teams && club.teams.length) {
      return club.teams.map((t) => (typeof t === 'string' ? t : t.name));
    }
    if (club && (club.type === 'Sports' || club.category === 'Athletics')) {
      return ['1st XI Team', 'Under 19 Team', 'Under 17 Squad'];
    }
    const name = club ? club.name : 'School';
    return [`${name} Senior Team`, `${name} Junior Team`];
  };

  const getClubAllStudents = (club) => {
    const names = new Set();
    if (club) {
      if (club.teams) {
        club.teams.forEach((t) => {
          if (t.roster) {
            t.roster.forEach((r) => {
              const n = typeof r === 'string' ? r : r.name;
              if (n) names.add(n);
            });
          }
        });
      }
      if (club.unassignedStudents) {
        club.unassignedStudents.forEach((s) => {
          const n = typeof s === 'string' ? s : s.name;
          if (n) names.add(n);
        });
      }
    }
    if (names.size === 0) {
      ['Kavindu Perera', 'Dilshan Senanayake', 'Nuwan Pradeep', 'Ashan Priyanjan', 'Tariq Mansoor', 'Pathum Nissanka', 'Maheesh Theekshana', 'Charith Asalanka'].forEach((n) => names.add(n));
    }
    return Array.from(names);
  };

  const getStudentsForTeam = (club, teamName) => {
    if (club && club.teams) {
      const found = club.teams.find((t) => (typeof t === 'string' ? t : t.name) === teamName);
      if (found && found.roster && found.roster.length) {
        return found.roster.map((r) => (typeof r === 'string' ? r : r.name));
      }
    }
    if (teamName && (teamName.includes('Under 17') || teamName.includes('Junior'))) {
      return ['Janith Liyanage (Captain)', 'Dhananjaya de Silva', 'Wanindu Hasaranga', 'Sahan Arachchige', 'Dunith Wellalage'];
    }
    return [
      'Kavindu Perera (Captain)',
      'Dilshan Senanayake (Vice Captain)',
      'Nuwan Pradeep',
      'Ashan Priyanjan',
      'Tariq Mansoor',
      'Pathum Nissanka',
      'Maheesh Theekshana'
    ];
  };

  // Re-render achievement gallery cards in club detail view
  window.renderAchievementGrid = (club) => {
    const achvGrid = document.getElementById('j-achv-grid');
    const achvEmpty = document.getElementById('j-achv-empty');
    if (!achvGrid) return;

    achvGrid.innerHTML = '';
    const awards = club && Array.isArray(club.awards) ? club.awards : [];

    if (awards.length > 0) {
      if (achvEmpty) achvEmpty.style.display = 'none';
      achvGrid.style.display = 'grid';

      awards.forEach((ach, i) => {
        const achId = ach.id || `ach-${i}`;
        const title = ach.title || 'Achievement';
        const year = ach.year || '';
        const level = ach.level || 'Provincial';
        const kind = ach.kind || 'Team';
        const img = ach.image || '';

        const cardEl = document.createElement('article');
        cardEl.className = 'c-achv-card j-achievement-card';
        cardEl.id = `j-achv-card-${achId}`;
        cardEl.dataset.achId = achId;
        cardEl.dataset.achIndex = String(i);
        cardEl.dataset.clubId = String(club.id);
        cardEl.setAttribute('role', 'button');
        cardEl.setAttribute('tabindex', '0');
        cardEl.setAttribute('aria-label', `View details for ${title}`);

        cardEl.innerHTML = `
          ${img ? `
            <div class="c-achv-card__media">
              <img class="c-achv-card__img" src="${escapeHtml(img)}" alt="${escapeHtml(title)}" loading="lazy" />
            </div>
          ` : `
            <div class="c-achv-card__media-empty" aria-hidden="true">
              <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><use href="#icon-trophy"/></svg>
            </div>
          `}
          <div class="c-achv-card__body">
            <div class="c-achv-card__row">
              <h3 class="c-achv-card__title">${escapeHtml(title)}</h3>
              <span class="c-achv-card__year">${escapeHtml(year)}</span>
            </div>
            <div class="c-achv-card__tags">
              <span class="c-achv-card__level">${escapeHtml(level)}</span>
              <span class="c-achv-card__dot" aria-hidden="true">•</span>
              <span class="c-achv-card__kind">${escapeHtml(kind)}</span>
            </div>
          </div>
        `;

        achvGrid.appendChild(cardEl);
      });
    } else {
      achvGrid.style.display = 'none';
      if (achvEmpty) achvEmpty.style.display = 'block';
    }
  };

  /* ===========================================================================
     SHARED UI COMPONENT HELPERS (DEDUPLICATED)
     =========================================================================== */

  // 1. Participant List Renderer
  const renderParticipantList = (listEl, countEl, participants, isEditable, onRemove) => {
    if (countEl) countEl.textContent = String(participants.length);
    if (!listEl) return;

    if (participants.length === 0) {
      listEl.innerHTML = isEditable ? '' : '<li class="c-participants-empty">Participant details have not been recorded for this achievement.</li>';
      return;
    }

    listEl.innerHTML = participants.map((p, idx) => `
      <li class="c-participant j-ex-125">
        <span style="display: flex; align-items: center; gap: 0.5rem;">
          <span class="c-participant__dot"></span>
          <span>${escapeHtml(p)}</span>
        </span>
        ${isEditable ? `
          <button type="button" class="j-remove-participant j-ex-126" data-idx="${idx}" title="Remove">
            <svg class="c-icon" width="14" height="14"><use href="#icon-close"/></svg>
          </button>
        ` : ''}
      </li>
    `).join('');

    if (isEditable && typeof onRemove === 'function') {
      listEl.querySelectorAll('.j-remove-participant').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          onRemove(Number(btn.dataset.idx));
        });
      });
    }
  };

  // 2. Gallery Grid Renderer
  const renderGalleryGrid = (gridEl, images, isEditable, options = {}) => {
    if (!gridEl) return;

    let itemsHtml = images.map((src, i) => `
      <div class="c-gallery-item j-ex-105">
        <img class="j-ex-106" src="${escapeHtml(src)}" alt="Gallery photo ${i + 1}" />
        ${isEditable ? `
          <button type="button" class="j-remove-gallery-img j-ex-107" data-idx="${i}" title="Delete photo">
            <svg class="c-icon" width="14" height="14"><use href="#icon-close"/></svg>
          </button>
        ` : ''}
      </div>
    `).join('');

    if (isEditable) {
      itemsHtml += `
        <button type="button" class="c-gallery-add-btn" id="${escapeHtml(options.uploadBtnId || 'j-open-gallery-upload-btn')}">
          <svg class="c-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-imagePlus"/></svg>
          <span>Add photos</span>
        </button>
      `;
    }

    gridEl.innerHTML = itemsHtml;

    if (isEditable) {
      if (typeof options.onRemove === 'function') {
        gridEl.querySelectorAll('.j-remove-gallery-img').forEach((btn) => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            options.onRemove(Number(btn.dataset.idx));
          });
        });
      }

      const openBtn = document.getElementById(options.uploadBtnId || 'j-open-gallery-upload-btn');
      const inputEl = document.getElementById(options.inputId);
      if (openBtn && inputEl) {
        openBtn.addEventListener('click', (e) => {
          e.preventDefault();
          inputEl.click();
        });
      }
    }
  };

  const AGE_GROUP_OPTIONS = ['Under 13', 'Under 15', 'Under 17', 'Under 19', 'Open'];

  // Helper: Setup custom .c-select dropdown box
  const setupCustomSelect = (rootEl, options, selectedVal, placeholder, onSelect) => {
    if (!rootEl) return;
    const trigger = rootEl.querySelector('.c-select__trigger');
    const valEl = rootEl.querySelector('.j-select-value');
    const menuEl = rootEl.querySelector('.c-select__menu');

    if (valEl) {
      valEl.textContent = selectedVal || placeholder || '-- Select --';
    }

    if (menuEl && Array.isArray(options)) {
      menuEl.innerHTML = options.map((opt) => {
        const val = typeof opt === 'string' ? opt : opt.value;
        const label = typeof opt === 'string' ? opt : opt.label;
        const isSel = val && selectedVal && String(val) === String(selectedVal);
        return `<div class="c-select__option ${isSel ? 'c-is-selected' : ''}" data-value="${escapeHtml(val)}">${escapeHtml(label)}</div>`;
      }).join('');

      menuEl.querySelectorAll('.c-select__option').forEach((optBtn) => {
        optBtn.onclick = (e) => {
          e.preventDefault();
          e.stopPropagation();
          const val = optBtn.dataset.value;
          if (valEl) valEl.textContent = val || placeholder || '-- Select --';
          menuEl.querySelectorAll('.c-select__option').forEach((b) => b.classList.toggle('c-is-selected', b === optBtn));
          rootEl.classList.remove('c-is-open');
          if (typeof onSelect === 'function') onSelect(val);
        };
      });
    }

    if (trigger) {
      trigger.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        document.querySelectorAll('.c-select.c-is-open').forEach((s) => {
          if (s !== rootEl) s.classList.remove('c-is-open');
        });
        rootEl.classList.toggle('c-is-open');
      };
    }
  };

  // 3. Team Dropdown & Individual Student Selector Binder
  const setupTeamSelectAndDropdown = (opts) => {
    const {
      teamSelectRoot,
      teamOptions,
      selectedTeam,
      indWrapEl,
      indSelectRoot,
      selectedIndividual,
      onTeamSelect,
      onIndividualSelect
    } = opts;

    if (teamSelectRoot) {
      const allTeamChoices = [
        ...teamOptions.map((t) => ({ value: t, label: t })),
        { value: 'Individual', label: 'Individual' }
      ];
      setupCustomSelect(teamSelectRoot, allTeamChoices, selectedTeam, '-- Choose Team / Individual --', (team) => {
        if (indWrapEl) indWrapEl.style.display = team === 'Individual' ? 'block' : 'none';
        if (typeof onTeamSelect === 'function') onTeamSelect(team);
      });
    }

    if (indWrapEl) {
      indWrapEl.style.display = selectedTeam === 'Individual' ? 'block' : 'none';
    }

    if (indSelectRoot) {
      const allStudents = getClubAllStudents(currentActiveClub);
      const studentOpts = [
        { value: '', label: '-- Choose Student --' },
        ...allStudents.map((s) => ({ value: s, label: s }))
      ];
      setupCustomSelect(indSelectRoot, studentOpts, selectedIndividual, '-- Choose Student --', (chosen) => {
        if (typeof onIndividualSelect === 'function') onIndividualSelect(chosen);
      });
    }
  };

  // 4. Form Data Extractor Helper
  const extractAchievementData = (prefix, fallbackAch, state) => {
    const getText = (id) => document.getElementById(id)?.textContent.trim() || '';
    const fb = fallbackAch || {};

    const title = getText(prefix + '-title') || fb.title || 'New Achievement';
    const place = getText(prefix + '-place') || fb.place || 'First Place';
    const level = getText(prefix + '-level') || fb.level || 'Provincial';
    const year = getText(prefix + '-year') || fb.year || new Date().getFullYear().toString();
    const date = getText(prefix + '-header-date') || fb.date || 'Nov 18, 2024';
    const venue = getText(prefix + '-header-venue') || fb.venue || 'Campus Grounds';
    const bio = getText(prefix + '-bio') || fb.bio || '';
    const details = getText(prefix + '-details') || fb.details || '';
    const tournament = getText('j-fact-tournament') || fb.tournament || title;
    const scope = getText('j-fact-scope') || fb.scope || level;
    const organisedBy = getText('j-fact-organisedBy') || fb.organisedBy || 'Schools Sports Association';
    const colours = getText('j-fact-colours') || fb.colours || place;

    return {
      title, place, level, year, date, venue, bio, details, tournament, scope,
      ageGroup: state.ageGroup,
      organisedBy, colours,
      kind: state.team === 'Individual' ? 'Individual' : 'Team',
      teamName: state.team === 'Individual' ? (state.individual || 'Individual') : state.team,
      recipient: state.team === 'Individual' ? (state.individual || 'Individual') : '',
      image: state.coverImage || fb.image || 'https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=800&h=400&fit=crop',
      participants: state.participants.slice(),
      gallery: state.gallery.slice()
    };
  };

  // Wire Cover Photo uploads via ImageUploader component
  if (window.ImageUploader) {
    window.ImageUploader.setupSinglePicker({
      input: 'j-ach-cover-input',
      preview: 'j-ach-cover-preview',
      emptyText: 'No cover photo selected',
      onLoaded: (dataUrl) => {
        activeCoverImage = dataUrl;
      }
    });
  } else {
    document.getElementById('j-ach-cover-input')?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      readImageFile(file).then((imgData) => {
        if (imgData) {
          activeCoverImage = imgData;
          const previewEl = document.getElementById('j-ach-cover-preview');
          if (previewEl) previewEl.innerHTML = `<img class="j-ex-124" src="${imgData}" alt="Cover preview" />`;
        }
      });
    });
  }

  // Wire Participant Input
  const addParticipantFromInput = () => {
    const input = document.getElementById('j-ach-part-input');
    const val = input ? input.value.trim() : '';
    if (val) {
      activeParticipantsList.push(val);
      input.value = '';
      const listEl = document.getElementById('j-ach-part-list');
      const countEl = document.getElementById('j-ach-part-count');
      renderParticipantList(listEl, countEl, activeParticipantsList, true, (idx) => {
        activeParticipantsList.splice(idx, 1);
        renderParticipantList(listEl, countEl, activeParticipantsList, true);
      });
    }
  };

  document.getElementById('j-ach-part-add')?.addEventListener('click', (e) => {
    e.preventDefault();
    addParticipantFromInput();
  });

  document.getElementById('j-ach-part-input')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      addParticipantFromInput();
    }
  });

  // ===========================================================================
  // UNIFIED ACHIEVEMENT VIEW / EDIT / CREATE (renderAchievementView)
  // ===========================================================================
  const openAchievementView = (achievement, index = -1, isEditing = false) => {
    currentActiveClub = getActiveClub();
    if (!viewAchPage) return;

    currentActiveAch = achievement;
    currentActiveAchIndex = index;

    const canEdit = viewAchPage.dataset.canEdit === 'true';
    const isCreateMode = (index === -1 || !achievement);
    const activeEdit = canEdit && (isEditing || isCreateMode);

    const clubTeams = getClubTeamNames(currentActiveClub);

    if (isCreateMode) {
      // Empty / placeholder state for creation
      activeCoverImage = '';
      activeGalleryImages = [];
      activeParticipantsList = [];
      activeSelectedAgeGroup = 'Under 19';
      activeSelectedTeam = clubTeams[0] || '1st XI Team';
      activeSelectedIndividual = '';
    } else {
      activeCoverImage = achievement.image || '';
      activeGalleryImages = Array.isArray(achievement.gallery) ? achievement.gallery.slice() : [];
      activeParticipantsList = Array.isArray(achievement.participants) ? achievement.participants.slice() : [];
      activeSelectedAgeGroup = achievement.ageGroup || 'Under 19';
      activeSelectedTeam = achievement.teamName || (achievement.kind === 'Individual' ? 'Individual' : (clubTeams[0] || '1st XI Team'));
      activeSelectedIndividual = (achievement.kind === 'Individual' ? achievement.recipient : '') || '';
    }

    // Populate data
    const setText = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val || '';
    };

    if (isCreateMode) {
      setText('j-ach-edit-place', 'First Place');
      setText('j-ach-edit-level', 'Provincial');
      setText('j-ach-edit-year', new Date().getFullYear().toString());
      setText('j-ach-edit-title', 'New Achievement Title');
      setText('j-ach-edit-header-date', 'Nov 18, 2024');
      setText('j-ach-edit-header-venue', 'Campus Grounds');
      setText('j-ach-edit-bio', 'Summary of achievement and team performance.');
      setText('j-ach-edit-details', 'Additional records and notes.');
      setText('j-fact-tournament', 'Tournament / Event Name');
      setText('j-fact-date', 'Nov 18, 2024');
      setText('j-fact-venue', 'Campus Grounds');
      setText('j-fact-scope', 'Provincial');
      setText('j-fact-ageGroup', activeSelectedAgeGroup);
      setText('j-fact-representing', activeSelectedTeam);
      setText('j-fact-organisedBy', 'Schools Sports Association');
      setText('j-fact-place', 'First Place');
      setText('j-fact-colours', 'Gold Medal');
    } else {
      setText('j-ach-edit-place', achievement.place || 'Achievement');
      setText('j-ach-edit-level', achievement.level || 'Provincial');
      setText('j-ach-edit-year', achievement.year || '2024');
      setText('j-ach-edit-title', achievement.title || 'Achievement Title');
      setText('j-ach-edit-header-date', achievement.date || 'Date not specified');
      setText('j-ach-edit-header-venue', achievement.venue || 'Campus Grounds');
      setText('j-ach-edit-bio', achievement.bio || `${achievement.title} was recognised during the ${achievement.year} season.`);
      setText('j-ach-edit-details', achievement.details || '');
      setText('j-fact-tournament', achievement.tournament || achievement.title);
      setText('j-fact-date', achievement.date || 'Not specified');
      setText('j-fact-venue', achievement.venue || 'Campus Grounds');
      setText('j-fact-scope', achievement.scope || achievement.level);
      setText('j-fact-ageGroup', activeSelectedAgeGroup);
      setText('j-fact-representing', achievement.kind === 'Individual' ? (activeSelectedIndividual || 'Individual') : activeSelectedTeam);
      setText('j-fact-organisedBy', achievement.organisedBy || 'Schools Sports Association');
      setText('j-fact-place', achievement.place || 'Achievement');
      setText('j-fact-colours', achievement.colours || achievement.place);
    }

    const dangerClub = document.getElementById('j-ach-danger-club-name');
    if (dangerClub && currentActiveClub) dangerClub.textContent = currentActiveClub.name;

    // Save button label
    const saveTextEl = document.getElementById('j-edit-ach-save-text');
    if (saveTextEl) {
      saveTextEl.textContent = isCreateMode ? 'Create Achievement' : 'Save Changes';
    }

    // Toggle Edit vs View Mode Controls
    const openEditBtn = document.getElementById('j-open-edit-achievement');
    const editControls = document.getElementById('j-ach-edit-controls');
    const coverSection = document.getElementById('j-ach-cover-section');
    const factAge = document.getElementById('j-fact-ageGroup');
    const ageSelect = document.getElementById('j-ach-age-select');
    const factRep = document.getElementById('j-fact-representing');
    const teamSelect = document.getElementById('j-ach-team-select');
    const indWrap = document.getElementById('j-ach-individual-wrap');
    const partAddRow = document.getElementById('j-ach-participant-add-row');
    const dangerZone = document.getElementById('j-ach-danger-zone');
    const canDelete = viewAchPage.dataset.canDelete === 'true';

    const editableIds = [
      'j-ach-edit-place', 'j-ach-edit-level', 'j-ach-edit-year',
      'j-ach-edit-title', 'j-ach-edit-header-date', 'j-ach-edit-header-venue',
      'j-ach-edit-bio', 'j-ach-edit-details',
      'j-fact-tournament', 'j-fact-date', 'j-fact-venue', 'j-fact-scope',
      'j-fact-organisedBy', 'j-fact-place', 'j-fact-colours'
    ];

    // Toggle edit vs view display
    if (openEditBtn) openEditBtn.style.display = (!activeEdit && canEdit) ? 'inline-flex' : 'none';
    if (editControls) editControls.style.display = activeEdit ? 'inline-flex' : 'none';
    if (coverSection) {
      coverSection.style.display = activeEdit ? 'block' : 'none';
      const cp = document.getElementById('j-ach-cover-preview');
      if (cp) cp.innerHTML = activeCoverImage ? `<img class="j-ex-124" src="${escapeHtml(activeCoverImage)}" alt="Cover preview" />` : '<span class="j-ex-119">No cover photo selected</span>';
      const ci = document.getElementById('j-ach-cover-input');
      if (ci && !activeCoverImage) ci.value = '';
    }

    if (factAge) factAge.style.display = activeEdit ? 'none' : '';
    if (ageSelect) {
      ageSelect.style.display = activeEdit ? 'block' : 'none';
      if (activeEdit) {
        setupCustomSelect(ageSelect, AGE_GROUP_OPTIONS, activeSelectedAgeGroup, '-- Choose Age Group --', (val) => {
          activeSelectedAgeGroup = val;
        });
      }
    }

    if (factRep) factRep.style.display = activeEdit ? 'none' : '';
    if (teamSelect) {
      teamSelect.style.display = activeEdit ? 'block' : 'none';
      if (activeEdit) {
        setupTeamSelectAndDropdown({
          teamSelectRoot: teamSelect,
          teamOptions: clubTeams,
          selectedTeam: activeSelectedTeam,
          indWrapEl: indWrap,
          indSelectRoot: document.getElementById('j-ach-individual-select'),
          selectedIndividual: activeSelectedIndividual,
          onTeamSelect: (team) => {
            activeSelectedTeam = team;
            activeParticipantsList = team === 'Individual' ? (activeSelectedIndividual ? [activeSelectedIndividual] : []) : getStudentsForTeam(currentActiveClub, team);
            renderActiveParticipants(true);
          },
          onIndividualSelect: (chosen) => {
            activeSelectedIndividual = chosen;
            if (activeSelectedTeam === 'Individual') {
              activeParticipantsList = chosen ? [chosen] : [];
              renderActiveParticipants(true);
            }
          }
        });
      }
    }
    if (!activeEdit && indWrap) indWrap.style.display = 'none';

    if (partAddRow) partAddRow.style.display = activeEdit ? 'flex' : 'none';
    // Only show Danger Zone on existing saved achievements (not in new creation mode)
    if (dangerZone) dangerZone.style.display = (!isCreateMode && canEdit && canDelete) ? 'flex' : 'none';

    editableIds.forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.contentEditable = activeEdit ? 'true' : 'false';
      el.style.borderBottom = activeEdit ? '1px dashed var(--sky-blue, #7FC7CC)' : '';
      el.style.outline = '';
      el.style.background = activeEdit ? 'rgba(127,199,204,0.06)' : '';
      el.style.borderRadius = activeEdit ? '4px' : '';
      el.style.padding = activeEdit ? '2px 6px' : '';
    });

    const renderActiveParticipants = (isEdit) => {
      const countEl = document.getElementById('j-ach-part-count');
      const listEl = document.getElementById('j-ach-part-list');
      renderParticipantList(listEl, countEl, activeParticipantsList, isEdit, (idx) => {
        activeParticipantsList.splice(idx, 1);
        renderActiveParticipants(isEdit);
      });
    };

    const renderActiveGallery = (isEdit) => {
      const galleryGrid = document.getElementById('j-achievement-gallery');
      renderGalleryGrid(galleryGrid, activeGalleryImages, isEdit, {
        uploadBtnId: 'j-open-gallery-upload-btn',
        inputId: 'j-gallery-upload',
        onRemove: (idx) => {
          activeGalleryImages.splice(idx, 1);
          renderActiveGallery(isEdit);
        }
      });

      if (isEdit) {
        const input = document.getElementById('j-gallery-upload');
        if (input && !input.dataset.bound) {
          input.dataset.bound = 'true';
          input.addEventListener('change', (e) => {
            const files = e.target.files;
            if (!files || !files.length) return;
            const reader = window.ImageUploader ? window.ImageUploader.readMultipleFiles(files) : Promise.all(Array.from(files).map(readImageFile));
            reader.then((imgs) => {
              const valid = imgs.filter(Boolean);
              if (valid.length) {
                activeGalleryImages = activeGalleryImages.concat(valid);
                renderActiveGallery(true);
              }
              input.value = '';
            });
          });
        }
      }
    };

    renderActiveParticipants(activeEdit);
    renderActiveGallery(activeEdit);

    if (clubDetailView) clubDetailView.style.display = 'none';
    viewAchPage.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Wire Click on Add Achievement Button
  document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('.j-open-add-achievement');
    if (addBtn) {
      e.preventDefault();
      openAchievementView(null, -1, true);
    }
  });

  // Wire Click on Achievement Card in Grid
  document.addEventListener('click', (e) => {
    const achCard = e.target.closest('.j-achievement-card');
    if (!achCard) return;

    e.preventDefault();
    currentActiveClub = getActiveClub();
    const achIndex = parseInt(achCard.dataset.achIndex, 10);
    if (currentActiveClub && currentActiveClub.awards && currentActiveClub.awards[achIndex]) {
      openAchievementView(currentActiveClub.awards[achIndex], achIndex, false);
    }
  });

  // Back to Club Detail
  document.getElementById('j-back-to-club-detail')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (viewAchPage) viewAchPage.style.display = 'none';
    if (clubDetailView) clubDetailView.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Edit Button
  document.getElementById('j-open-edit-achievement')?.addEventListener('click', (e) => {
    e.preventDefault();
    openAchievementView(currentActiveAch, currentActiveAchIndex, true);
  });

  // Cancel Edit / Creation Button
  document.getElementById('j-edit-ach-cancel')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (currentActiveAchIndex === -1 || !currentActiveAch) {
      // Return to club detail view
      if (viewAchPage) viewAchPage.style.display = 'none';
      if (clubDetailView) clubDetailView.style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      openAchievementView(currentActiveAch, currentActiveAchIndex, false);
    }
  });

  // Save Edit / Create Button
  document.getElementById('j-edit-ach-save')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!currentActiveClub) {
      currentActiveClub = getActiveClub();
    }
    if (!currentActiveClub) return;

    const data = extractAchievementData('j-ach-edit', currentActiveAch, {
      ageGroup: activeSelectedAgeGroup,
      team: activeSelectedTeam,
      individual: activeSelectedIndividual,
      coverImage: activeCoverImage,
      participants: activeParticipantsList,
      gallery: activeGalleryImages
    });

    if (currentActiveAchIndex === -1 || !currentActiveAch) {
      // Creation mode: Add new achievement to club
      const newAward = Object.assign({ id: 'ach-' + Date.now() }, data);
      if (!Array.isArray(currentActiveClub.awards)) {
        currentActiveClub.awards = [];
      }
      currentActiveClub.awards.unshift(newAward);
      window.renderAchievementGrid(currentActiveClub);
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Achievement added successfully.', 'success');
      }
      openAchievementView(newAward, 0, false);
    } else {
      // Edit mode: Update existing achievement
      Object.assign(currentActiveClub.awards[currentActiveAchIndex], data);
      window.renderAchievementGrid(currentActiveClub);
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Achievement updated successfully.', 'success');
      }
      openAchievementView(currentActiveClub.awards[currentActiveAchIndex], currentActiveAchIndex, false);
    }
  });

  // Danger Zone Delete
  document.getElementById('j-delete-achievement-btn')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!currentActiveClub || currentActiveAchIndex < 0) return;
    const achTitle = currentActiveAch?.title || 'this achievement';

    const performDelete = () => {
      if (Array.isArray(currentActiveClub.awards)) {
        currentActiveClub.awards.splice(currentActiveAchIndex, 1);
      }
      window.renderAchievementGrid(currentActiveClub);
      if (viewAchPage) viewAchPage.style.display = 'none';
      if (clubDetailView) clubDetailView.style.display = 'block';
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Achievement deleted successfully.', 'success');
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    if (typeof window.openUniversalDeleteModal === 'function') {
      window.openUniversalDeleteModal({
        title: 'Delete achievement?',
        description: `This will permanently remove "${achTitle}" from the records of ${escapeHtml(currentActiveClub.name || 'this club')}.`,
        buttonText: 'Delete achievement',
        onConfirm: performDelete
      });
    } else if (confirm(`Permanently delete "${achTitle}"?`)) {
      performDelete();
    }
  });

  // Close open dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.c-select')) {
      document.querySelectorAll('.c-select.c-is-open').forEach((s) => s.classList.remove('c-is-open'));
    }
  });

});
