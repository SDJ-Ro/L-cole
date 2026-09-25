// MVC/public/assets/js/components/extracurricular-card.js
// Dedicated Component Controller for _extracurricular_card.php and Extracurricular Overview Grid

document.addEventListener('DOMContentLoaded', () => {

  const rejectModal = document.getElementById('j-modal-reject');
  let currentRejectCardEl = null;

  // ---------------------------------------------------------------------------
  // 1. APPROVE / ACCEPT PENDING CARD
  // ---------------------------------------------------------------------------
  document.addEventListener('click', (e) => {
    const approveBtn = e.target.closest('.j-approve-club');
    if (!approveBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = approveBtn.closest('.j-club-card');
    if (!card) return;

    const clubId = approveBtn.dataset.clubId;
    const clubType = card.dataset.clubType || 'Sports';

    card.classList.remove('c-club-card--pending');
    card.classList.add('c-club-card--clickable');
    card.dataset.status = 'Active';

    card.style.transition = 'filter 400ms ease, opacity 400ms ease, transform 300ms ease';
    card.style.transform = 'scale(1.02)';
    setTimeout(() => { card.style.transform = ''; }, 300);

    const pill = card.querySelector('.j-club-pill');
    if (pill) {
      pill.classList.remove('c-club-card__pill--pending');
      pill.classList.add(clubType === 'Sports' ? 'c-club-card__pill--sport' : 'c-club-card__pill--club');
      pill.textContent = clubType === 'Sports' ? 'SPORT' : 'CLUB';
    }

    const lockedContact = card.querySelector('.j-contact-locked');
    if (lockedContact) {
      lockedContact.className = 'c-club-card__contact c-club-card__contact--unlocked';
      lockedContact.innerHTML = `
        <span class="c-club-card__contact-icon" aria-hidden="true">
          <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
        </span>
        <span class="c-club-card__contact-text">+94 77 555 1234</span>
      `;
    }

    const actionsSlot = card.querySelector('.j-card-actions');
    if (actionsSlot) {
      const btnClass = clubType === 'Sports' ? 'c-club-card__btn--sport' : 'c-club-card__btn--club';
      actionsSlot.innerHTML = `
        <button type="button" class="c-club-card__btn ${btnClass} j-view-details" data-club-id="${clubId}">
          View Details &rarr;
        </button>
      `;
    }
  });

  // ---------------------------------------------------------------------------
  // 2. STUDENT "INTEREST" BUTTON ACTION
  // ---------------------------------------------------------------------------
  document.addEventListener('click', (e) => {
    const interestBtn = e.target.closest('.j-interest-club');
    if (!interestBtn) return;

    e.preventDefault();
    e.stopPropagation();

    interestBtn.classList.remove('c-club-card__btn--interest', 'j-interest-club');
    interestBtn.classList.add('c-club-card__btn--requested', 'j-requested-club');
    interestBtn.disabled = true;
    interestBtn.innerHTML = `
      <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-check"/></svg>
      <span>Requested</span>
    `;

    interestBtn.style.transform = 'scale(1.05)';
    setTimeout(() => { interestBtn.style.transform = ''; }, 200);
  });

  // ---------------------------------------------------------------------------
  // 3. REJECT PENDING CARD (Connects to shared _reject_modal.php)
  // ---------------------------------------------------------------------------
  document.addEventListener('click', (e) => {
    const rejectBtn = e.target.closest('.j-reject-club');
    if (!rejectBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = rejectBtn.closest('.j-club-card');
    const modal = rejectModal || document.getElementById('j-modal-reject');
    if (!card || !modal) return;

    currentRejectCardEl = card;
    const clubName = rejectBtn.dataset.clubName || 'Extracurricular';

    const typeLabel = modal.querySelector('.j-reject-modal-type');
    if (typeLabel) typeLabel.textContent = 'Extracurricular';

    const itemName = modal.querySelector('.j-reject-modal-item-name');
    if (itemName) itemName.textContent = `"${clubName}"`;

    const feedbackInput = modal.querySelector('#j-reject-feedback-input');
    const confirmBtn = modal.querySelector('#j-reject-confirm-btn');

    if (feedbackInput) {
      feedbackInput.value = '';
      setTimeout(() => feedbackInput.focus(), 100);
    }
    if (confirmBtn) confirmBtn.disabled = true;

    if (feedbackInput && confirmBtn) {
      feedbackInput.oninput = () => {
        confirmBtn.disabled = !feedbackInput.value.trim();
      };
    }

    if (typeof window.openModal === 'function') {
      window.openModal(modal);
    } else {
      document.body.style.overflow = 'hidden';
      modal.classList.add('c-is-open');
    }
  });

  const modalEl = rejectModal || document.getElementById('j-modal-reject');
  if (modalEl) {
    const confirmBtn = modalEl.querySelector('#j-reject-confirm-btn');

    if (confirmBtn) {
      confirmBtn.addEventListener('click', () => {
        if (!currentRejectCardEl) return;
        const targetCard = currentRejectCardEl;
        currentRejectCardEl = null;

        if (typeof window.closeModal === 'function') {
          window.closeModal(modalEl);
        } else {
          document.body.style.overflow = '';
          modalEl.classList.remove('c-is-open');
        }

        if (typeof window.applyCardDecisionOverlay === 'function') {
          window.applyCardDecisionOverlay(targetCard, {
            status: 'declined',
            label: 'Declined',
            delay: 1100,
            onComplete: () => {
              targetCard.style.transition = 'opacity 350ms ease, transform 350ms ease';
              targetCard.style.opacity = '0';
              targetCard.style.transform = 'scale(0.92)';
              setTimeout(() => {
                targetCard.remove();
              }, 350);
            }
          });
        } else {
          targetCard.style.transition = 'opacity 350ms ease, transform 350ms ease';
          targetCard.style.opacity = '0';
          targetCard.style.transform = 'scale(0.92)';
          setTimeout(() => {
            targetCard.remove();
          }, 350);
        }
      });
    }

    modalEl.addEventListener('modal:closed', () => {
      currentRejectCardEl = null;
    });
  }

  // ---------------------------------------------------------------------------
  // 4. OVERVIEW <-> CLUB DETAIL VIEW NAVIGATION
  // ---------------------------------------------------------------------------
  const clubDetailView = document.getElementById('j-view-club-detail');
  const viewAchPage = document.getElementById('j-achievement-page-view');

  let clubsData = [];
  if (clubDetailView && clubDetailView.dataset.clubs) {
    try {
      clubsData = JSON.parse(clubDetailView.dataset.clubs);
      if (clubDetailView.style.display !== 'none' && clubsData.length > 0) {
        window.currentExtracurricularClub = clubsData[0];
      }
    } catch (e) {}
  }
  window.allClubsData = clubsData;

  const openClubDetail = (clubId) => {
    const pool = window.allClubsData || clubsData;
    let club = pool.find((c) => Number(c.id) === Number(clubId));
    if (!club) return;

    window.currentExtracurricularClub = club;

    const heroEl = document.getElementById('j-club-detail-hero');
    if (heroEl) {
      heroEl.dataset.clubId = club.id;

      const mediaWrap = heroEl.querySelector('.c-detail-hero__media');
      if (mediaWrap) {
        let heroImg = document.getElementById('j-detail-hero-img');
        if (club.image) {
          if (!heroImg) {
            heroImg = document.createElement('img');
            heroImg.id = 'j-detail-hero-img';
            mediaWrap.insertBefore(heroImg, mediaWrap.firstChild);
          }
          heroImg.src = club.image;
          heroImg.alt = club.name;
          heroImg.style.display = '';
        } else if (heroImg) {
          heroImg.style.display = 'none';
        }
      }

      const createdText = document.getElementById('j-detail-hero-created-text');
      if (createdText) createdText.textContent = `Created: ${club.createdAt || '15 Jan 2024'}`;

      const typeEl = document.getElementById('j-detail-hero-type');
      if (typeEl) typeEl.textContent = club.type || 'Sports';

      const catEl = document.getElementById('j-detail-hero-category');
      if (catEl) catEl.textContent = club.category || 'General';

      const nameEl = document.getElementById('j-detail-hero-name');
      if (nameEl) nameEl.textContent = club.name;

      const descEl = document.getElementById('j-detail-hero-desc');
      if (descEl) descEl.textContent = club.desc || '';

      const editBtn = document.getElementById('j-open-program-edit');
      if (editBtn) editBtn.dataset.clubId = club.id;
    }

    if (typeof window.renderAchievementGrid === 'function') {
      window.renderAchievementGrid(club);
    }

    if (typeof window.renderNoticesForClub === 'function') {
      window.renderNoticesForClub(club);
    }

    if (typeof window.renderTeamsForClub === 'function') {
      window.renderTeamsForClub(club);
    }

    if (typeof window.renderRosterForClub === 'function') {
      window.renderRosterForClub(club);
    }

    // Update Schedule & Events Details Component
    const tic = club.tic || {};
    const ticNameEl = document.getElementById('j-detail-tic-name');
    const ticSpecEl = document.getElementById('j-detail-tic-specialty');
    const ticAvatarEl = document.getElementById('j-detail-tic-avatar');
    const ticEmailEl = document.getElementById('j-detail-tic-email');
    const ticPhoneEl = document.getElementById('j-detail-tic-phone');

    if (ticNameEl) ticNameEl.textContent = tic.name || 'Mr. Weerasinghe';
    if (ticSpecEl) ticSpecEl.textContent = tic.subject || 'Teacher in Charge';
    if (ticAvatarEl && tic.avatar) ticAvatarEl.src = tic.avatar;
    if (ticEmailEl) ticEmailEl.textContent = tic.email || 'weerasinghe@lecole.edu';
    if (ticPhoneEl) ticPhoneEl.textContent = tic.phone || '+94 77 123 4567';

    const coach = club.coach || {};
    const coachNameEl = document.getElementById('j-detail-coach-name');
    const coachSpecEl = document.getElementById('j-detail-coach-specialty');
    const coachAvatarEl = document.getElementById('j-detail-coach-avatar');
    const coachEmailEl = document.getElementById('j-detail-coach-email');
    const coachPhoneEl = document.getElementById('j-detail-coach-phone');

    if (coachNameEl) coachNameEl.textContent = coach.name || 'Coach details pending';
    if (coachSpecEl) coachSpecEl.textContent = coach.specialty || 'Head Coach';
    if (coachAvatarEl && coach.avatar) coachAvatarEl.src = coach.avatar;
    if (coachEmailEl) coachEmailEl.textContent = coach.email || 'coach@lecole.edu';
    if (coachPhoneEl) coachPhoneEl.textContent = coach.phone || '+94 71 987 6543';

    const schedEl = document.getElementById('j-detail-schedule');
    const locEl = document.getElementById('j-detail-location');
    const createdEl = document.getElementById('j-detail-created-at');
    const enrolledSinceEl = document.getElementById('j-detail-enrolled-since');
    const enrolledDateEl = document.getElementById('j-detail-enrolled-date');

    if (schedEl) schedEl.textContent = club.schedule || 'Tuesdays & Thursdays, 3:30 – 5:30 PM';
    if (locEl) locEl.textContent = club.location || 'Main Sports Ground';
    if (createdEl) createdEl.textContent = club.createdAt || '15 Jan 2024';
    if (enrolledSinceEl) enrolledSinceEl.textContent = club.enrolledSince || (club.enrolled ? 'Grade 9' : 'Not Enrolled');
    if (enrolledDateEl) enrolledDateEl.textContent = club.enrollmentDate || club.enrolledDate || (club.enrolled ? 'September 4, 2023' : '—');

    const overviewEl = document.getElementById('j-view-overview');
    const createEl = document.getElementById('j-view-create-club');

    if (overviewEl) overviewEl.style.display = 'none';
    if (createEl) createEl.style.display = 'none';
    if (viewAchPage) viewAchPage.style.display = 'none';
    if (clubDetailView) {
      clubDetailView.style.display = 'block';
      if (typeof matchRosterToCalendarHeight === 'function') {
        setTimeout(matchRosterToCalendarHeight, 10);
      }
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Back to Main Overview Grid
  document.getElementById('j-back-to-activities-grid')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (clubDetailView) clubDetailView.style.display = 'none';
    if (viewAchPage) viewAchPage.style.display = 'none';
    const overviewEl = document.getElementById('j-view-overview');
    if (overviewEl) overviewEl.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Card click & View Details click
  document.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.j-view-details');
    const cardClick = !e.target.closest('button') && !e.target.closest('a') ? e.target.closest('.c-club-card--clickable') : null;
    const target = viewBtn || cardClick;

    if (!target) return;

    const clubId = target.dataset.clubId || target.closest('.j-club-card')?.dataset.clubId;
    if (clubId) {
      openClubDetail(clubId);
    }
  });

  // ---------------------------------------------------------------------------
  // 5. LIVE SEARCH & CATEGORY FILTERING
  // ---------------------------------------------------------------------------
  const searchInput = document.getElementById('j-club-search');
  const tabs = document.querySelectorAll('.c-segmented-tab');

  let activeType = 'All';
  let searchQuery = '';

  const filterCards = () => {
    const cards = document.querySelectorAll('.j-club-card');
    cards.forEach((card) => {
      const cardType = card.dataset.clubType || '';
      const cardName = (card.dataset.clubName || '').toLowerCase();
      const cardCategory = (card.querySelector('.c-club-card__category')?.textContent || '').toLowerCase();

      const matchesType = (activeType === 'All')
        || (activeType === 'Sports' && cardType === 'Sports')
        || (activeType === 'Clubs and Societies' && cardType !== 'Sports');

      const matchesQuery = !searchQuery
        || cardName.includes(searchQuery)
        || cardCategory.includes(searchQuery);

      card.style.display = (matchesType && matchesQuery) ? '' : 'none';
    });
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      tabs.forEach((t) => t.classList.remove('is-active'));
      tab.classList.add('is-active');
      activeType = tab.dataset.value || 'All';
      filterCards();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.toLowerCase().trim();
      filterCards();
    });
  }

  // ---------------------------------------------------------------------------
  // 6. CREATE NEW EXTRACURRICULAR MODAL (Ported directly from original admin prototype)
  // Reuses common components: _dropdown.php, dropdown.js, dialogs-and-popups.js
  // ---------------------------------------------------------------------------
  const createModal = document.getElementById('j-modal-create-club');
  const openCreateBtn = document.getElementById('j-open-create-club');
  const createForm = document.getElementById('j-create-club-form');
  const positionsEl = document.getElementById('j-cc-positions');
  const addPosBtn = document.getElementById('j-cc-add-position');
  const errorBanner = document.getElementById('j-create-club-error');

  const escapeHtml = window.escapeHtml || ((str) => {
    if (!str && str !== 0) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  });

  let positions = [{ title: '', showOnCard: true }];

  const renderPositions = () => {
    if (!positionsEl) return;
    positionsEl.innerHTML = positions.map((p, i) => `
      <div class="c-position-row" data-position-index="${i}">
        <input class="c-field-input" type="text" placeholder="Position title" data-role="title" value="${escapeHtml(p.title)}" />
        <button type="button" class="c-position-row__star-btn ${p.showOnCard ? 'c-is-active' : ''}" data-role="star" aria-label="${p.showOnCard ? 'Hide on card' : 'Show on card'}" title="${p.showOnCard ? 'Shown on card' : 'Hidden on card'}">
          <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="${p.showOnCard ? 'currentColor' : 'none'}"><use href="#icon-star"/></svg>
        </button>
        <button type="button" class="c-position-row__remove-btn" data-role="remove" aria-label="Remove position" ${positions.length === 1 ? 'disabled' : ''}>
          <svg class="c-icon" width="16" height="16"><use href="#icon-trash"/></svg>
        </button>
      </div>
    `).join('');
  };

  const openCreateModal = () => {
    if (!createModal) return;
    positions = [{ title: '', showOnCard: true }];

    if (createForm) createForm.reset();
    if (errorBanner) {
      errorBanner.textContent = '';
      errorBanner.classList.remove('c-is-visible');
    }

    // Reset dropdown via universal dropdown.js component
    if (typeof window.resetDropdown === 'function') {
      window.resetDropdown('j-cc-type', 'Select type');
      window.resetDropdown('j-cc-tic', 'Assign Teacher in Charge');
    }

    renderPositions();

    if (typeof window.openModal === 'function') {
      window.openModal(createModal);
    } else {
      createModal.classList.add('c-is-open');
    }
    document.getElementById('j-cc-name')?.focus();
  };

  if (openCreateBtn) {
    openCreateBtn.addEventListener('click', (e) => {
      e.preventDefault();
      openCreateModal();
    });
  }

  // Position rows event handling
  if (addPosBtn) {
    addPosBtn.addEventListener('click', (e) => {
      e.preventDefault();
      positions.push({ title: '', showOnCard: false });
      renderPositions();
    });
  }

  if (positionsEl) {
    positionsEl.addEventListener('input', (e) => {
      const row = e.target.closest('[data-position-index]');
      if (row && e.target.dataset.role === 'title') {
        const idx = Number(row.dataset.positionIndex);
        if (positions[idx]) positions[idx].title = e.target.value;
      }
    });

    positionsEl.addEventListener('click', (e) => {
      const row = e.target.closest('[data-position-index]');
      if (!row) return;
      const idx = Number(row.dataset.positionIndex);

      if (e.target.closest('[data-role="star"]')) {
        e.preventDefault();
        if (positions[idx]) {
          positions[idx].showOnCard = !positions[idx].showOnCard;
          renderPositions();
        }
      } else if (e.target.closest('[data-role="remove"]') && positions.length > 1) {
        e.preventDefault();
        positions.splice(idx, 1);
        renderPositions();
      }
    });
  }

  // Form submission
  if (createForm) {
    createForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('j-cc-name')?.value.trim() || '';
      const selectedType = createForm.querySelector('input[name="type"]')?.value
        || (typeof window.getDropdownValue === 'function' ? window.getDropdownValue('j-cc-type') : '')
        || 'Sports';
      const selectedTic = createForm.querySelector('input[name="tic_name"]')?.value
        || (typeof window.getDropdownValue === 'function' ? window.getDropdownValue('j-cc-tic') : '')
        || '';
      const ageLimit = document.getElementById('j-cc-age-limit')?.value.trim() || '';
      const teamLimit = document.getElementById('j-cc-team-limit')?.value.trim() || '';
      const description = document.getElementById('j-cc-description')?.value.trim() || '';

      if (!name || !selectedType || !description) {
        if (errorBanner) {
          errorBanner.textContent = 'Add a programme name, type, and description.';
          errorBanner.classList.add('c-is-visible');
        }
        return;
      }

      if (errorBanner) errorBanner.classList.remove('c-is-visible');

      const newId = Date.now();
      const isSport = (selectedType === 'Sports');
      const pillClass = isSport ? 'c-club-card__pill--sport' : 'c-club-card__pill--club';
      const pillText  = isSport ? 'SPORT' : 'CLUB';
      const topTheme  = isSport ? 'c-club-card__top--main-sport' : 'c-club-card__top--club';
      const bgIcon    = isSport ? 'trophy' : 'usersRound';
      const leadPositions = positions.filter(p => p.title.trim() && p.showOnCard).map(p => p.title.trim()).join(' · ');

      const assignedTic = selectedTic || 'Faculty Coordinator';
      const emailPrefix = assignedTic.toLowerCase().replace(/[^a-z0-9]/g, '.');

      // Create rich in-memory club data object so "View Details" opens an active page
      const newClubObj = {
        id: newId,
        name: name,
        type: selectedType,
        category: selectedType,
        theme: isSport ? 'main-sport' : 'club',
        bgIcon: bgIcon,
        desc: description,
        status: 'Active',
        createdAt: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
        image: isSport ? 'https://images.unsplash.com/photo-1518605368461-1e1e38ce1548?w=800&h=400&fit=crop' : 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800&h=400&fit=crop',
        tic: {
          name: assignedTic,
          avatar: '',
          email: `${emailPrefix}@lecole.edu`,
          subject: 'Teacher in Charge',
          phone: '+94 77 123 4567'
        },
        coach: {
          name: 'Not Assigned',
          avatar: '',
          phone: '',
          specialty: 'Head Coach'
        },
        schedule: 'Tuesdays & Thursdays, 3:30 – 5:00 PM',
        location: isSport ? 'Main Sports Grounds' : 'Activity Hall',
        ageGroups: ageLimit ? [`Up to ${ageLimit} students`] : ['All Grades'],
        stats: { members: 0, teams: 0, trophies: 0 },
        unassignedStudents: [],
        team: [],
        teams: [],
        squad: [],
        notices: [],
        awards: []
      };

      if (!window.allClubsData) window.allClubsData = [];
      window.allClubsData.unshift(newClubObj);
      if (typeof clubsData !== 'undefined' && Array.isArray(clubsData)) {
        clubsData.unshift(newClubObj);
      }

      const grid = document.getElementById('j-club-grid');
      if (grid) {
        const cardArticle = document.createElement('article');
        cardArticle.className = 'c-club-card c-club-card--clickable j-club-card';
        cardArticle.id = `j-club-card-${newId}`;
        cardArticle.dataset.clubId = String(newId);
        cardArticle.dataset.clubName = name;
        cardArticle.dataset.clubType = selectedType;
        cardArticle.dataset.status = 'Active';

        const btnClass = isSport ? 'c-club-card__btn--sport' : 'c-club-card__btn--club';

        cardArticle.innerHTML = `
          <div class="c-club-card__top ${topTheme}">
            <div class="c-club-card__bg-icon" aria-hidden="true">
              <svg width="140" height="140" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <use href="#icon-${bgIcon}"/>
              </svg>
            </div>
            <div class="c-club-card__header-badges">
              <span class="c-club-card__pill ${pillClass} j-club-pill" data-type="${escapeHtml(selectedType)}">${pillText}</span>
            </div>
            <div class="c-club-card__titles">
              <p class="c-club-card__category">${escapeHtml(selectedType.toUpperCase())}</p>
              <h2 class="c-club-card__name c-font-display">${escapeHtml(name)}</h2>
            </div>
          </div>
          <div class="c-club-card__bottom">
            <div class="c-club-card__tic">
              <div class="c-club-card__tic-avatar-placeholder" aria-hidden="true">
                <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-usersRound"/></svg>
              </div>
              <div style="flex: 1; min-width: 0;">
                <div class="c-club-card__tic-label">${leadPositions ? escapeHtml(leadPositions.toUpperCase()) : 'TEACHER IN CHARGE'}</div>
                <div class="c-club-card__tic-name j-card-tic-name">${escapeHtml(assignedTic)}</div>
              </div>
              <button type="button" class="c-btn c-btn--ghost c-btn--sm j-edit-card-tic-btn" data-club-id="${newId}" data-club-name="${escapeHtml(name)}" data-current-tic="${escapeHtml(assignedTic)}" title="Change Teacher in Charge" style="padding: 0.2rem 0.5rem; font-size: 0.72rem; margin-left: auto; height: auto; border-radius: var(--radius-md);">
                <svg class="c-icon" width="11" height="11" viewBox="0 0 24 24"><use href="#icon-edit"/></svg>
                <span>Change</span>
              </button>
            </div>
            <div class="c-club-card__contact c-club-card__contact--unlocked">
              <span class="c-club-card__contact-icon" aria-hidden="true">
                <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
              </span>
              <span class="c-club-card__contact-text">+94 77 123 4567</span>
            </div>
            <div class="c-club-card__actions j-card-actions">
              <button type="button" class="c-club-card__btn ${btnClass} j-view-details" data-club-id="${newId}">
                View Details &rarr;
              </button>
            </div>
          </div>
        `;

        if (openCreateBtn) {
          openCreateBtn.insertAdjacentElement('afterend', cardArticle);
        } else {
          grid.prepend(cardArticle);
        }
      }

      // Close modal
      if (typeof window.closeModal === 'function') {
        window.closeModal(createModal);
      } else {
        createModal.classList.remove('c-is-open');
      }

      // Toast feedback banner
      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner(`"${name}" was created successfully with TIC ${assignedTic}.`, 'success');
      }
    });
  }

  // ---------------------------------------------------------------------------
  // 6A. TEACHER 3-LEVEL WORKLOAD HOVER PREVIEW COMPONENT
  // Delegates to unified window.attachTeacherHoverPreview & window.findTeacherData
  // ---------------------------------------------------------------------------
  const findTeacherData = (name) => {
    return (typeof window.findTeacherData === 'function')
      ? window.findTeacherData(name)
      : { name: name, qualification: 'Faculty Member', classTeacher: '', extras: [], subjects: [] };
  };

  const attachTeacherHoverPreview = (itemEl, teacherNameOrId, options = {}) => {
    if (typeof window.attachTeacherHoverPreview === 'function') {
      window.attachTeacherHoverPreview(itemEl, teacherNameOrId, options);
    }
  };

  function initCreateClubTicHover() {
    const ticDropdown = document.getElementById('j-cc-tic');
    if (!ticDropdown) return;
    ticDropdown.querySelectorAll('.c-select__option, .c-dropdown__option').forEach(opt => {
      const tName = opt.dataset.value || opt.textContent.trim();
      attachTeacherHoverPreview(opt, tName);
    });
  }

  // Dismiss any active preview on global click or scroll
  document.addEventListener('click', () => {
    document.querySelectorAll('.c-extracurricular-teacher-preview').forEach(p => p.remove());
  });
  window.addEventListener('scroll', () => {
    document.querySelectorAll('.c-extracurricular-teacher-preview').forEach(p => p.remove());
  }, { passive: true });

  // Universal hover preview delegator across all extracurricular UI contexts
  document.addEventListener('mouseover', (e) => {
    // 1. Dropdown options in Create Extracurricular Modal
    const dropdownOpt = e.target.closest('#j-cc-tic .c-dropdown__option, #j-cc-tic .c-select__option');
    if (dropdownOpt && !dropdownOpt.dataset.previewBound) {
      dropdownOpt.dataset.previewBound = 'true';
      const tName = dropdownOpt.dataset.value || (dropdownOpt.querySelector('span') ? dropdownOpt.querySelector('span').textContent.trim() : dropdownOpt.textContent.trim());
      attachTeacherHoverPreview(dropdownOpt, tName);
      dropdownOpt.dispatchEvent(new MouseEvent('mouseenter', { bubbles: false, cancelable: true }));
      return;
    }

    // 2. Buttons in Card TIC Popovers
    const popoverBtn = e.target.closest('.c-card-tic-popover button');
    if (popoverBtn && !popoverBtn.dataset.previewBound) {
      popoverBtn.dataset.previewBound = 'true';
      const tName = popoverBtn.dataset.teacherName || (popoverBtn.querySelector('span') ? popoverBtn.querySelector('span').textContent.trim() : popoverBtn.textContent.trim());
      attachTeacherHoverPreview(popoverBtn, tName);
      popoverBtn.dispatchEvent(new MouseEvent('mouseenter', { bubbles: false, cancelable: true }));
      return;
    }

    // 3. Teacher in Charge row / name directly inside extracurricular cards
    const cardTicRow = e.target.closest('.c-club-card__tic');
    if (cardTicRow && !e.target.closest('.j-edit-card-tic-btn') && !cardTicRow.dataset.previewBound) {
      const nameEl = cardTicRow.querySelector('.j-card-tic-name');
      const tName = nameEl ? nameEl.textContent.trim() : '';
      if (tName && tName !== 'Faculty Mentor') {
        cardTicRow.dataset.previewBound = 'true';
        cardTicRow.style.cursor = 'help';
        attachTeacherHoverPreview(cardTicRow, () => {
          const freshNameEl = cardTicRow.querySelector('.j-card-tic-name');
          return freshNameEl ? freshNameEl.textContent.trim() : tName;
        });
        cardTicRow.dispatchEvent(new MouseEvent('mouseenter', { bubbles: false, cancelable: true }));
      }
      return;
    }

    // 4. Teacher in Charge in detail view sidebar
    const detailTicCard = e.target.closest('.c-staff-card-box');
    if (detailTicCard && !e.target.closest('#j-edit-tic-btn') && !detailTicCard.dataset.previewBound) {
      const nameEl = document.getElementById('j-detail-tic-name');
      const tName = nameEl ? nameEl.textContent.trim() : '';
      if (tName && tName !== 'Pending') {
        detailTicCard.dataset.previewBound = 'true';
        detailTicCard.style.cursor = 'help';
        attachTeacherHoverPreview(detailTicCard, () => {
          const freshEl = document.getElementById('j-detail-tic-name');
          return freshEl ? freshEl.textContent.trim() : tName;
        });
        detailTicCard.dispatchEvent(new MouseEvent('mouseenter', { bubbles: false, cancelable: true }));
      }
      return;
    }
  });

  // Initialize hover on create modal dropdown
  initCreateClubTicHover();

  // ---------------------------------------------------------------------------
  // 6B. QUICK EDIT TEACHER-IN-CHARGE ON CARDS (Admin & Management)
  // ---------------------------------------------------------------------------
  document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.j-edit-card-tic-btn');
    if (!editBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const clubId = editBtn.dataset.clubId;
    const currentTic = editBtn.dataset.currentTic || '';
    const clubCard = editBtn.closest('.j-club-card');

    const baseTeachers = [
      'James Wilson',
      'Sarah Peiris',
      'Rohan Dias',
      'Madhavi Fernando',
      'Alex Benjamin',
      'Priya De Silva',
      'Sofia Fernando',
      'Shanthi Silva',
      'Anura Wijesinghe',
      'Mr. Weerasinghe',
      'Nethmi Perera',
      'Amara Silva',
      'Kavindi Jayasinghe',
      'David Peris',
      'Ruwan Silva'
    ];
    const dirTeachers = (window.LECOLE_STAFF_DIRECTORY && Array.isArray(window.LECOLE_STAFF_DIRECTORY))
      ? window.LECOLE_STAFF_DIRECTORY.map(s => s.name).filter(Boolean)
      : [];
    const teachersList = Array.from(new Set([...baseTeachers, ...dirTeachers]));

    // Remove any existing card tic popovers
    document.querySelectorAll('.c-card-tic-popover').forEach(p => p.remove());

    const popover = document.createElement('div');
    popover.className = 'c-card-tic-popover';
    popover.style.cssText = `
      position: absolute;
      bottom: calc(100% + 8px);
      left: 0;
      right: 0;
      max-height: 220px;
      overflow-y: auto;
      background: #ffffff;
      border: 1px solid var(--color-border, #EFE8DF);
      border-radius: var(--radius-xl, 0.875rem);
      box-shadow: 0 12px 30px rgba(15, 65, 74, 0.2);
      z-index: 100;
      padding: 0.5rem;
    `;

    const titleEl = document.createElement('div');
    titleEl.style.cssText = 'padding: 0.35rem 0.5rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted, #71717A); border-bottom: 1px solid var(--alabaster, #F4F1EA); margin-bottom: 0.35rem;';
    titleEl.textContent = 'Select Teacher in Charge';
    popover.appendChild(titleEl);

    teachersList.forEach(tName => {
      const itemBtn = document.createElement('button');
      itemBtn.type = 'button';
      itemBtn.className = 'c-btn-plain';
      itemBtn.dataset.teacherName = tName;
      const isSelected = (tName === currentTic);
      itemBtn.style.cssText = `
        width: 100%;
        text-align: left;
        padding: 0.45rem 0.65rem;
        font-size: 0.82rem;
        font-weight: ${isSelected ? '700' : '500'};
        color: ${isSelected ? 'var(--deepsea, #0F414A)' : 'inherit'};
        background: ${isSelected ? 'rgba(15, 65, 74, 0.08)' : 'transparent'};
        border-radius: var(--radius-md, 0.5rem);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background 150ms ease;
      `;
      itemBtn.innerHTML = `
        <span>${escapeHtml(tName)}</span>
        ${isSelected ? '<svg class="c-icon" width="13" height="13" style="stroke:var(--sky-blue,#38BDF8);"><use href="#icon-check"/></svg>' : ''}
      `;
      itemBtn.addEventListener('mouseenter', () => { if (!isSelected) itemBtn.style.background = 'var(--sand-light, #FAF7F2)'; });
      itemBtn.addEventListener('mouseleave', () => { if (!isSelected) itemBtn.style.background = 'transparent'; });

      // Attach 3-level workload hover preview to this option button inside the card popover
      attachTeacherHoverPreview(itemBtn, tName);

      itemBtn.onclick = (evt) => {
        evt.stopPropagation();
        document.querySelectorAll('.c-extracurricular-teacher-preview').forEach(p => p.remove());

        // Update Card UI
        const ticNameEl = clubCard?.querySelector('.j-card-tic-name');
        if (ticNameEl) ticNameEl.textContent = tName;
        editBtn.dataset.currentTic = tName;

        // Update In-memory Store
        const pool = window.allClubsData || clubsData;
        const targetClub = pool.find(c => Number(c.id) === Number(clubId));
        if (targetClub) {
          if (!targetClub.tic) targetClub.tic = {};
          targetClub.tic.name = tName;
        }

        // If detail hero is active for this club, sync
        if (window.currentExtracurricularClub && Number(window.currentExtracurricularClub.id) === Number(clubId)) {
          window.currentExtracurricularClub.tic.name = tName;
          const heroTic = document.getElementById('j-detail-tic-name');
          if (heroTic) heroTic.textContent = tName;
        }

        popover.remove();
        if (typeof window.showFeedbackBanner === 'function') {
          window.showFeedbackBanner(`Teacher in Charge updated to ${tName}.`, 'success');
        }
      };

      popover.appendChild(itemBtn);
    });

    const cardBottom = clubCard?.querySelector('.c-club-card__bottom') || editBtn.parentElement;
    cardBottom.style.position = 'relative';
    cardBottom.appendChild(popover);

    // Dismiss active preview on popover scroll
    popover.addEventListener('scroll', () => {
      document.querySelectorAll('.c-extracurricular-teacher-preview').forEach(p => p.remove());
    }, { passive: true });

    // Close on outside click
    const outsideCloser = (evt) => {
      if (!popover.contains(evt.target) && evt.target !== editBtn) {
        document.querySelectorAll('.c-extracurricular-teacher-preview').forEach(p => p.remove());
        popover.remove();
        document.removeEventListener('click', outsideCloser);
      }
    };
    setTimeout(() => { document.addEventListener('click', outsideCloser); }, 20);
  });

  // ---------------------------------------------------------------------------
  // 7. EDIT EXTRACURRICULAR PROGRAMME MODAL
  // Reuses universal components: _form_card.php, _dropdown.php, dialogs-and-popups.js
  // ---------------------------------------------------------------------------
  const editProgModal = document.getElementById('j-program-edit-modal');
  const progForm = document.getElementById('j-program-form');
  let progCoverImage = '';

  const openProgramEditModal = () => {
    if (!editProgModal) return;
    const club = window.currentExtracurricularClub || {};
    const currentName = document.getElementById('j-detail-hero-name')?.textContent.trim() || club.name || '';
    const currentType = document.getElementById('j-detail-hero-type')?.textContent.trim() || club.type || 'Sports';
    const currentCat  = document.getElementById('j-detail-hero-category')?.textContent.trim() || club.category || 'General';
    const currentDesc = document.getElementById('j-detail-hero-desc')?.textContent.trim() || club.desc || '';
    const currentImg  = document.getElementById('j-detail-hero-img')?.getAttribute('src') || club.image || '';

    const nameInput = document.getElementById('j-pi-name');
    const descInput = document.getElementById('j-pi-description');
    const ageInput  = document.getElementById('j-pi-age-groups');
    const titleEl   = progForm?.querySelector('.c-form-header-title');
    const previewEl = document.getElementById('j-pi-image-preview');

    if (nameInput) nameInput.value = currentName;
    if (descInput) descInput.value = currentDesc;
    if (titleEl)   titleEl.textContent = currentName ? `Edit ${currentName}` : 'Edit Programme';

    if (ageInput) {
      const ages = Array.isArray(club.ageGroups) ? club.ageGroups.join(', ') : (club.ageGroups || '');
      ageInput.value = ages;
    }

    // Dropdown values via dropdown.js
    if (typeof window.setDropdownValue === 'function') {
      window.setDropdownValue('j-pi-type-dropdown', currentType);
      window.setDropdownValue('j-pi-category-dropdown', currentCat);
    } else {
      const typeDrop = document.getElementById('j-pi-type-dropdown');
      if (typeDrop) {
        const valSpan = typeDrop.querySelector('.j-select-value, .c-dropdown__selected');
        if (valSpan) valSpan.textContent = currentType;
        const hiddenInp = typeDrop.querySelector('input[name="type"]');
        if (hiddenInp) hiddenInp.value = currentType;
      }
      const catDrop = document.getElementById('j-pi-category-dropdown');
      if (catDrop) {
        const valSpan = catDrop.querySelector('.j-select-value, .c-dropdown__selected');
        if (valSpan) valSpan.textContent = currentCat;
        const hiddenInp = catDrop.querySelector('input[name="category"]');
        if (hiddenInp) hiddenInp.value = currentCat;
      }
    }

    // Pre-fill TIC fields
    const tic = club.tic || {};
    const ticNameInput  = document.getElementById('j-pi-tic-name');
    const ticSpecInput  = document.getElementById('j-pi-tic-specialty');
    const ticEmailInput = document.getElementById('j-pi-tic-email');
    const ticPhoneInput = document.getElementById('j-pi-tic-phone');

    const curTicName  = document.getElementById('j-detail-tic-name')?.textContent.trim() || tic.name || '';
    const curTicSpec  = document.getElementById('j-detail-tic-specialty')?.textContent.trim() || tic.subject || '';
    const curTicEmail = document.getElementById('j-detail-tic-email')?.textContent.trim() || tic.email || '';
    const curTicPhone = document.getElementById('j-detail-tic-phone')?.textContent.trim() || tic.phone || '';

    if (ticNameInput)  ticNameInput.value  = curTicName;
    if (ticSpecInput)  ticSpecInput.value  = curTicSpec;
    if (ticEmailInput) ticEmailInput.value = curTicEmail;
    if (ticPhoneInput) ticPhoneInput.value = curTicPhone;

    // Pre-fill Coach fields
    const coach = club.coach || {};
    const coachNameInput  = document.getElementById('j-pi-coach-name');
    const coachSpecInput  = document.getElementById('j-pi-coach-specialty');
    const coachEmailInput = document.getElementById('j-pi-coach-email');
    const coachPhoneInput = document.getElementById('j-pi-coach-phone');

    const curCoachName  = document.getElementById('j-detail-coach-name')?.textContent.trim() || coach.name || '';
    const curCoachSpec  = document.getElementById('j-detail-coach-specialty')?.textContent.trim() || coach.specialty || '';
    const curCoachEmail = document.getElementById('j-detail-coach-email')?.textContent.trim() || coach.email || '';
    const curCoachPhone = document.getElementById('j-detail-coach-phone')?.textContent.trim() || coach.phone || '';

    if (coachNameInput)  coachNameInput.value  = curCoachName;
    if (coachSpecInput)  coachSpecInput.value  = curCoachSpec;
    if (coachEmailInput) coachEmailInput.value = curCoachEmail;
    if (coachPhoneInput) coachPhoneInput.value = curCoachPhone;

    progCoverImage = currentImg;
    if (previewEl) {
      if (progCoverImage) {
        previewEl.innerHTML = `<img src="${progCoverImage}" alt="Cover preview" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.style.display='none';" />`;
      } else {
        previewEl.innerHTML = `<span class="c-cover-upload__placeholder" style="display:flex;flex-direction:column;align-items:center;gap:0.35rem;color:rgba(15,65,74,0.6);font-size:0.8125rem;font-weight:600;"><svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><use href="#icon-imagePlus"/></svg>Upload cover photo</span>`;
      }
    }

    if (typeof window.openModal === 'function') {
      window.openModal(editProgModal);
    } else {
      editProgModal.classList.add('c-is-open');
    }
    requestAnimationFrame(() => {
      nameInput?.focus();
      nameInput?.select();
    });
  };

  // Wire trigger button via event delegation
  document.addEventListener('click', (e) => {
    if (e.target.closest('#j-open-program-edit, .j-open-program-edit')) {
      e.preventDefault();
      openProgramEditModal();
    }
  });

  const progFileInput = document.getElementById('j-pi-image-input');
  if (progFileInput) {
    progFileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        progCoverImage = ev.target.result;
        const previewEl = document.getElementById('j-pi-image-preview');
        if (previewEl) previewEl.innerHTML = `<img src="${progCoverImage}" alt="Cover preview" style="width:100%;height:100%;object-fit:cover;" />`;
      };
      reader.readAsDataURL(file);
    });
  }

  if (progForm) {
    progForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const nameInput = document.getElementById('j-pi-name');
      const nameVal = nameInput ? nameInput.value.trim() : '';
      const errEl = document.getElementById('j-pi-name-error');

      if (!nameVal) {
        if (errEl) {
          errEl.textContent = 'Please enter a programme name.';
          errEl.style.display = 'block';
        }
        nameInput?.focus();
        return;
      }
      if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }

      const typeVal = typeof window.getDropdownValue === 'function' ? (window.getDropdownValue('j-pi-type-dropdown') || 'Sports') : (document.getElementById('j-pi-type-dropdown')?.querySelector('input[name="type"]')?.value || 'Sports');
      const catVal  = typeof window.getDropdownValue === 'function' ? (window.getDropdownValue('j-pi-category-dropdown') || 'General') : (document.getElementById('j-pi-category-dropdown')?.querySelector('input[name="category"]')?.value || 'General');
      const descVal = document.getElementById('j-pi-description')?.value.trim() || '';
      const ageVal  = document.getElementById('j-pi-age-groups')?.value.trim() || '';

      const ticNameVal  = document.getElementById('j-pi-tic-name')?.value.trim() || '';
      const ticSpecVal  = document.getElementById('j-pi-tic-specialty')?.value.trim() || '';
      const ticEmailVal = document.getElementById('j-pi-tic-email')?.value.trim() || '';
      const ticPhoneVal = document.getElementById('j-pi-tic-phone')?.value.trim() || '';

      const coachNameVal  = document.getElementById('j-pi-coach-name')?.value.trim() || '';
      const coachSpecVal  = document.getElementById('j-pi-coach-specialty')?.value.trim() || '';
      const coachEmailVal = document.getElementById('j-pi-coach-email')?.value.trim() || '';
      const coachPhoneVal = document.getElementById('j-pi-coach-phone')?.value.trim() || '';

      // Update Hero Header DOM
      const nameEl = document.getElementById('j-detail-hero-name');
      const typeEl = document.getElementById('j-detail-hero-type');
      const catEl  = document.getElementById('j-detail-hero-category');
      const descEl = document.getElementById('j-detail-hero-desc');

      if (nameEl) nameEl.textContent = nameVal;
      if (typeEl) typeEl.textContent = typeVal;
      if (catEl)  catEl.textContent  = catVal;
      if (descEl) descEl.textContent = descVal;

      // Update TIC & Coach in Sidebar DOM
      const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
      if (ticNameVal)  setTxt('j-detail-tic-name', ticNameVal);
      if (ticSpecVal)  setTxt('j-detail-tic-specialty', ticSpecVal);
      if (ticEmailVal) setTxt('j-detail-tic-email', ticEmailVal);
      if (ticPhoneVal) setTxt('j-detail-tic-phone', ticPhoneVal);

      if (coachNameVal)  setTxt('j-detail-coach-name', coachNameVal);
      if (coachSpecVal)  setTxt('j-detail-coach-specialty', coachSpecVal);
      if (coachEmailVal) setTxt('j-detail-coach-email', coachEmailVal);
      if (coachPhoneVal) setTxt('j-detail-coach-phone', coachPhoneVal);

      // Update Cover Image DOM
      if (progCoverImage) {
        let heroImg = document.getElementById('j-detail-hero-img');
        const mediaWrap = document.querySelector('#j-club-detail-hero .c-detail-hero__media');
        if (!heroImg && mediaWrap) {
          heroImg = document.createElement('img');
          heroImg.id = 'j-detail-hero-img';
          mediaWrap.insertBefore(heroImg, mediaWrap.firstChild);
        }
        if (heroImg) {
          heroImg.src = progCoverImage;
          heroImg.alt = nameVal;
          heroImg.style.display = '';
        }
      }

      // Update in-memory club object
      if (window.currentExtracurricularClub) {
        const c = window.currentExtracurricularClub;
        c.name = nameVal;
        c.type = typeVal;
        c.category = catVal;
        c.desc = descVal;
        c.ageGroups = ageVal;
        if (progCoverImage) c.image = progCoverImage;

        if (!c.tic) c.tic = {};
        if (ticNameVal)  c.tic.name    = ticNameVal;
        if (ticSpecVal)  c.tic.subject = ticSpecVal;
        if (ticEmailVal) c.tic.email   = ticEmailVal;
        if (ticPhoneVal) c.tic.phone   = ticPhoneVal;

        if (!c.coach) c.coach = {};
        if (coachNameVal)  c.coach.name      = coachNameVal;
        if (coachSpecVal)  c.coach.specialty = coachSpecVal;
        if (coachEmailVal) c.coach.email     = coachEmailVal;
        if (coachPhoneVal) c.coach.phone     = coachPhoneVal;
      }

      // Update matching card on the main grid
      const clubId = window.currentExtracurricularClub?.id;
      if (clubId) {
        const gridCard = document.querySelector(`.j-club-card[data-club-id="${clubId}"]`);
        if (gridCard) {
          const cardName = gridCard.querySelector('.c-club-card__name');
          const cardCat  = gridCard.querySelector('.c-club-card__category');
          const cardTic  = gridCard.querySelector('.j-card-tic-name');
          const cardTicBtn = gridCard.querySelector('.j-edit-card-tic-btn');
          if (cardName) cardName.textContent = nameVal;
          if (cardCat)  cardCat.textContent  = catVal.toUpperCase();
          if (cardTic && ticNameVal) cardTic.textContent = ticNameVal;
          if (cardTicBtn && ticNameVal) cardTicBtn.dataset.currentTic = ticNameVal;
        }
      }

      // Close modal cleanly via window.closeModal
      if (typeof window.closeModal === 'function') {
        window.closeModal(editProgModal);
      } else {
        editProgModal.classList.remove('c-is-open');
      }

      if (typeof window.showFeedbackBanner === 'function') {
        window.showFeedbackBanner('Programme updated successfully.', 'success');
      }
    });
  }

});

