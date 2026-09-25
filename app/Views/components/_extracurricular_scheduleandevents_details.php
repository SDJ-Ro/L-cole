<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR SCHEDULE & EVENTS DETAILS COMPONENT
 * =========================================================================
 * Reusable details panel rendered beside the extracurricular calendar.
 * Features the signature cream/brown styled cards with staff credentials,
 * meeting times, physical venue, and registration timestamps.
 * 
 * Edit buttons open the shared c-modal-layer popup (same system as calendar).
 * 
 * Expects:
 *   - $club    : array (Current club/sport details)
 *   - $canEdit : bool  (Permission to show Edit actions; default true)
 * =========================================================================
 */

$clubData       = $club ?? [];
$isEditable     = $canEdit ?? true;
$showStaffCards = $showStaff ?? true;

$tic = $clubData['tic'] ?? [];
$ticName = $tic['name'] ?? 'Mr. Weerasinghe';
$ticSpecialty = $tic['subject'] ?? 'Teacher in Charge';
$ticAvatar = $tic['avatar'] ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces';
$ticEmail = $tic['email'] ?? 'weerasinghe@lecole.edu';
$ticPhone = $tic['phone'] ?? '+94 77 123 4567';

$coach = $clubData['coach'] ?? [];
$coachName = $coach['name'] ?? 'Coach Dinesh Fernando';
$coachSpecialty = $coach['specialty'] ?? 'Head Coach';
$coachAvatar = $coach['avatar'] ?? 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=100&h=100&fit=crop&crop=faces';
$coachEmail = $coach['email'] ?? 'dinesh.coach@lecole.edu';
$coachPhone = $coach['phone'] ?? '+94 71 987 6543';

$scheduleTime = $clubData['schedule'] ?? 'Tuesdays & Thursdays, 3:30 – 5:30 PM';
$venueLocation = $clubData['location'] ?? 'Main Sports Complex';
$createdAt = $clubData['createdAt'] ?? '15 Jan 2024';
?>

<section class="c-staff-sidebar j-ex-47" id="j-schedule-details-sidebar">
  <?php if ($showStaffCards): ?>
  <!-- Teacher in Charge Card -->
  <div class="j-ex-48 c-staff-card-box">
    <article class="j-ex-37">
      <div class="j-ex-38">
        <h3 class="c-staff-card__role j-ex-39">Teacher in Charge</h3>
        <?php if ($isEditable): ?>
          <button type="button" class="c-btn c-btn--ghost c-btn--sm j-ex-40" id="j-edit-tic-btn" title="Edit Teacher in Charge">
            <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24"><use href="#icon-edit"/></svg>
            <span>Edit</span>
          </button>
        <?php endif; ?>
      </div>
      <div class="c-staff-card__person j-ex-41">
        <img id="j-detail-tic-avatar" src="<?= htmlspecialchars($ticAvatar) ?>" alt="<?= htmlspecialchars($ticName) ?>" />
        <div class="j-ex-42">
          <p class="c-staff-card__name" id="j-detail-tic-name"><?= htmlspecialchars($ticName) ?></p>
          <p class="c-staff-card__specialty" id="j-detail-tic-specialty"><?= htmlspecialchars($ticSpecialty) ?></p>
        </div>
      </div>
      <div class="c-staff-card__contact j-ex-43">
        <p class="c-staff-card__contact-row j-ex-39">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-mail"/></svg>
          <span id="j-detail-tic-email"><?= htmlspecialchars($ticEmail) ?></span>
        </p>
        <p class="c-staff-card__contact-row j-ex-39">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
          <span id="j-detail-tic-phone"><?= htmlspecialchars($ticPhone) ?></span>
        </p>
      </div>
    </article>
  </div>

  <!-- Coach / Instructor Card -->
  <div class="j-ex-49 c-staff-card-box">
    <article class="j-ex-37">
      <div class="j-ex-38">
        <h3 class="c-staff-card__role j-ex-39">Coach / Instructor</h3>
        <?php if ($isEditable): ?>
          <button type="button" class="c-btn c-btn--ghost c-btn--sm j-ex-40" id="j-edit-coach-btn" title="Edit coach details">
            <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24"><use href="#icon-edit"/></svg>
            <span>Edit</span>
          </button>
        <?php endif; ?>
      </div>
      <div class="c-staff-card__person j-ex-41">
        <img id="j-detail-coach-avatar" src="<?= htmlspecialchars($coachAvatar) ?>" alt="<?= htmlspecialchars($coachName) ?>" />
        <div class="j-ex-42">
          <p class="c-staff-card__name" id="j-detail-coach-name"><?= htmlspecialchars($coachName) ?></p>
          <p class="c-staff-card__specialty" id="j-detail-coach-specialty"><?= htmlspecialchars($coachSpecialty) ?></p>
        </div>
      </div>
      <div class="c-staff-card__contact j-ex-43">
        <p class="c-staff-card__contact-row j-ex-39">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-mail"/></svg>
          <span id="j-detail-coach-email"><?= htmlspecialchars($coachEmail) ?></span>
        </p>
        <p class="c-staff-card__contact-row j-ex-39">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
          <span id="j-detail-coach-phone"><?= htmlspecialchars($coachPhone) ?></span>
        </p>
      </div>
    </article>
  </div>
  <?php endif; ?>

  <!-- Details & Created Date Subgrid -->
  <div class="j-ex-50" <?= !$showStaffCards ? 'style="display: flex; flex-direction: column; gap: 1rem; height: 100%;"' : '' ?>>
    <!-- Schedule & Venue Details -->
    <div class="j-ex-51 c-schedule-subcard">
      <div class="j-ex-52">
        <h4 class="j-ex-53">Details</h4>
        <?php if ($isEditable): ?>
          <button type="button" class="c-btn c-btn--ghost c-btn--sm j-ex-54" id="j-open-edit-details" title="Edit session details">
            <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24"><use href="#icon-edit"/></svg>
            <span>Edit</span>
          </button>
        <?php endif; ?>
      </div>

      <div class="j-ex-55">
        <span class="j-ex-56">
          <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#icon-clock"/></svg>
        </span>
        <div class="j-ex-42">
          <p class="j-ex-57">Schedule</p>
          <p class="j-ex-58" id="j-detail-schedule"><?= htmlspecialchars($scheduleTime) ?></p>
        </div>
      </div>

      <div class="j-ex-59">
        <span class="j-ex-56">
          <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#icon-mapPin"/></svg>
        </span>
        <div class="j-ex-42">
          <p class="j-ex-57">Location</p>
          <p class="j-ex-58" id="j-detail-location"><?= htmlspecialchars($venueLocation) ?></p>
        </div>
      </div>
    </div>

    <!-- Created Date -->
    <div class="j-ex-51 c-schedule-subcard">
      <h4 class="j-ex-60">Created Date</h4>
      <div class="j-ex-61">
        <div class="j-ex-42">
          <p class="j-ex-57">Date Added</p>
          <p class="j-ex-58" id="j-detail-created-at"><?= htmlspecialchars($createdAt) ?></p>
        </div>
        <span class="j-ex-56" style="margin-top: 0.5rem;">
          <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#icon-calendarDays"/></svg>
        </span>
      </div>
    </div>
  </div>
</section>

<?php if ($isEditable): ?>
<!-- =========================================================================
     SHARED EDIT MODAL — Schedule & Events Details (TIC / Coach / Details)
     Reuses the existing c-modal-layer + c-event-form system from calendar.
     ========================================================================= -->
<div class="c-modal-layer" id="j-staff-edit-modal" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close editor"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-staff-modal-title" style="width: min(38rem, 94vw); max-width: 38rem; max-height: 90vh; padding: 0; overflow: visible; border: none; background: transparent; display: flex; flex-direction: column;">
    <form class="c-form-card" id="j-staff-edit-form" novalidate style="margin: 0; display: flex; flex-direction: column; max-height: 90vh; background: #ffffff; border-radius: var(--radius-2xl, 1.25rem); overflow: hidden; box-shadow: 0 20px 45px rgba(15, 65, 74, 0.22); border: 1px solid var(--color-border, #EFE8DF);">
      <!-- Universal Form Header (Sand Theme) -->
      <header class="c-form-header c-form-header--sand" style="flex-shrink: 0;">
        <div class="c-form-header-row">
          <div>
            <h2 class="c-form-header-title c-font-display" id="j-staff-modal-title">Edit Details</h2>
            <p class="c-form-header-subtitle" id="j-staff-modal-desc">Update the details below and save.</p>
          </div>
          <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close" style="color: var(--midnight, #0F414A); background: rgba(255,255,255,0.6); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 150ms ease;">
            <svg class="c-icon" width="18" height="18"><use href="#icon-close"/></svg>
          </button>
        </div>
      </header>

      <!-- Clean White Form Body Surface with Auto-Scroll -->
      <div class="c-form-body" style="padding: 1.5rem; overflow-y: auto; flex: 1 1 auto; max-height: calc(90vh - 140px); overscroll-behavior: contain;">
        <div id="j-staff-modal-fields" class="c-form-grid c-form-grid--2col"></div>
      </div>

      <!-- Actions Footer -->
      <footer class="c-form-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; background: #FAF7F2; border-top: 1px solid var(--color-border, #EFE8DF); display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
        <button type="submit" class="c-btn c-btn--solid c-btn--sky">Save changes</button>
      </footer>
    </form>
  </section>
</div>

<script>
/**
 * Self-contained interactivity for Schedule & Events Details component.
 * Edit buttons open the shared c-modal-layer using the common c-form-card pattern.
 */
(function() {
  function initScheduleDetailsComponent() {
    const sidebar = document.getElementById('j-schedule-details-sidebar');
    if (!sidebar || sidebar.dataset.detailsInitialized === 'true') return;
    sidebar.dataset.detailsInitialized = 'true';

    const modal      = document.getElementById('j-staff-edit-modal');
    const form       = document.getElementById('j-staff-edit-form');
    const fieldsWrap = document.getElementById('j-staff-modal-fields');
    const title      = document.getElementById('j-staff-modal-title');
    const desc       = document.getElementById('j-staff-modal-desc');

    if (!modal || !form) return;

    // ---- Modal open/close (Standardized via dialogs-and-popups.js) ---------
    function openModal() {
      if (typeof window.openModal === 'function') {
        window.openModal(modal);
      } else {
        document.body.style.overflow = 'hidden';
        modal.style.display = 'flex';
        modal.classList.add('c-is-open');
      }
      requestAnimationFrame(() => {
        const first = form.querySelector('input, textarea, select');
        if (first) { first.focus(); first.select && first.select(); }
      });
    }
    function closeModal() {
      if (typeof window.closeModal === 'function') {
        window.closeModal(modal);
      } else {
        document.body.style.overflow = '';
        modal.classList.remove('c-is-open');
        modal.style.display = 'none';
      }
      if (fieldsWrap) fieldsWrap.innerHTML = '';
      form.onsubmit = null;
    }

    // Backdrop + close buttons
    modal.querySelector('.j-modal-backdrop')?.addEventListener('click', closeModal);
    modal.querySelectorAll('.j-modal-close').forEach(btn => btn.addEventListener('click', closeModal));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('c-is-open')) closeModal();
    });

    // ---- Field builder helpers (reusing standard c-form-field & c-text-input) ----
    function field(id, label, value, type = 'text', placeholder = '', span2 = false) {
      return `
        <div class="c-form-field ${span2 ? 'c-form-field--span-2' : ''}">
          <label class="c-form-field__label" for="${id}">${label}</label>
          <input class="c-text-input" id="${id}" type="${type}" value="${value.replace(/"/g, '&quot;')}" placeholder="${placeholder}" />
        </div>`;
    }

    // -----------------------------------------------------------------------
    // 0. Teacher in Charge (TIC)
    // -----------------------------------------------------------------------
    document.getElementById('j-edit-tic-btn')?.addEventListener('click', () => {
      const name  = document.getElementById('j-detail-tic-name')?.textContent.trim() || '';
      const spec  = document.getElementById('j-detail-tic-specialty')?.textContent.trim() || 'Teacher in Charge';
      const email = document.getElementById('j-detail-tic-email')?.textContent.trim() || '';
      const phone = document.getElementById('j-detail-tic-phone')?.textContent.trim() || '';

      // Faculty Directory Single Source of Truth from DB-backed window.LECOLE_STAFF_DIRECTORY
      const rawFaculty = Array.isArray(window.LECOLE_STAFF_DIRECTORY) && window.LECOLE_STAFF_DIRECTORY.length > 0
        ? window.LECOLE_STAFF_DIRECTORY.map(t => t.name)
        : [
            'James Wilson', 'Sarah Peiris', 'Rohan Dias', 'Madhavi Fernando',
            'Alex Benjamin', 'Priya De Silva', 'Sofia Fernando', 'Shanthi Silva',
            'Mr. Weerasinghe', 'Anura Wijesinghe', 'David Peris', 'Ruwan Silva'
          ];
      const facultyList = [...new Set(rawFaculty.filter(Boolean))];

      if (title) title.textContent = 'Edit Teacher in Charge';
      if (desc)  desc.textContent  = 'Assign or update the faculty coordinator for this programme.';
      fieldsWrap.innerHTML = `
        <div class="c-form-field c-form-field--span-2">
          <label class="c-form-field__label">Quick Select Faculty Member <span style="font-weight:400;color:rgba(15,65,74,0.55);">(Hover name for 3-level workload details)</span></label>
          <div class="c-tic-pills" style="display:flex;flex-wrap:wrap;gap:0.35rem;padding:0.45rem;background:var(--sand-light,#FAF7F2);border-radius:var(--radius-lg,0.625rem);border:1px solid var(--color-border,#EFE8DF);">
            ${facultyList.map(fn => `
              <button type="button" class="c-btn-plain j-tic-pill-btn" data-teacher="${fn}" style="font-size:0.75rem;padding:0.25rem 0.55rem;border-radius:999px;border:1px solid ${fn === name ? 'var(--deepsea,#0F414A)' : 'rgba(15,65,74,0.15)'};background:${fn === name ? 'var(--deepsea,#0F414A)' : '#fff'};color:${fn === name ? '#fff' : 'var(--deepsea,#0F414A)'};font-weight:600;cursor:pointer;">
                ${fn}
              </button>
            `).join('')}
          </div>
        </div>
        ${field('j-m-tic-name',  'Teacher in Charge Name', name,  'text',  'e.g. Mr. Weerasinghe or James Wilson', false)}
        ${field('j-m-tic-spec',  'Role / Department',      spec,  'text',  'e.g. Science / Faculty Mentor', false)}
        ${field('j-m-tic-email', 'Institutional Email',    email, 'email', 'e.g. faculty@lecole.edu', false)}
        ${field('j-m-tic-phone', 'Contact Number',         phone, 'tel',   'e.g. +94 77 123 4567', false)}
      `;

      // Wire hover preview and quick fill to each pill
      fieldsWrap.querySelectorAll('.j-tic-pill-btn').forEach(btn => {
        const tName = btn.dataset.teacher;
        if (typeof window.attachTeacherHoverPreview === 'function') {
          window.attachTeacherHoverPreview(btn, tName);
        }
        btn.addEventListener('click', () => {
          const inp = document.getElementById('j-m-tic-name');
          if (inp) inp.value = tName;
          const tData = (typeof window.findTeacherData === 'function') ? window.findTeacherData(tName) : null;
          if (tData) {
            const specInp = document.getElementById('j-m-tic-spec');
            if (specInp && (tData.qualification || tData.subject)) {
              specInp.value = tData.qualification || tData.subject;
            }
          }
          fieldsWrap.querySelectorAll('.j-tic-pill-btn').forEach(b => {
            const isMatch = b.dataset.teacher === tName;
            b.style.borderColor = isMatch ? 'var(--deepsea,#0F414A)' : 'rgba(15,65,74,0.15)';
            b.style.background = isMatch ? 'var(--deepsea,#0F414A)' : '#fff';
            b.style.color = isMatch ? '#fff' : 'var(--deepsea,#0F414A)';
          });
        });
      });

      form.onsubmit = (e) => {
        e.preventDefault();
        const newName  = document.getElementById('j-m-tic-name')?.value.trim()  || name;
        const newSpec  = document.getElementById('j-m-tic-spec')?.value.trim()  || spec;
        const newEmail = document.getElementById('j-m-tic-email')?.value.trim() || '';
        const newPhone = document.getElementById('j-m-tic-phone')?.value.trim() || '';

        const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        setTxt('j-detail-tic-name',      newName);
        setTxt('j-detail-tic-specialty', newSpec);
        setTxt('j-detail-tic-email',     newEmail);
        setTxt('j-detail-tic-phone',     newPhone);

        if (window.currentExtracurricularClub) {
          const t = window.currentExtracurricularClub;
          if (!t.tic) t.tic = {};
          t.tic.name    = newName;
          t.tic.subject = newSpec;
          t.tic.email   = newEmail;
          t.tic.phone   = newPhone;

          // Update corresponding card on grid if present
          const card = document.querySelector(`.j-club-card[data-club-id="${t.id}"]`);
          if (card) {
            const cardTicName = card.querySelector('.j-card-tic-name');
            if (cardTicName) cardTicName.textContent = newName;
            const btn = card.querySelector('.j-edit-card-tic-btn');
            if (btn) btn.dataset.currentTic = newName;
          }
        }
        closeModal();
      };
      openModal();
    });

    // -----------------------------------------------------------------------
    // 1. Coach / Instructor
    // -----------------------------------------------------------------------
    document.getElementById('j-edit-coach-btn')?.addEventListener('click', () => {
      const name  = document.getElementById('j-detail-coach-name')?.textContent.trim() || '';
      const spec  = document.getElementById('j-detail-coach-specialty')?.textContent.trim() || '';
      const email = document.getElementById('j-detail-coach-email')?.textContent.trim() || '';
      const phone = document.getElementById('j-detail-coach-phone')?.textContent.trim() || '';

      if (title) title.textContent = 'Edit Coach / Instructor';
      if (desc)  desc.textContent  = 'Update the coach details for this program.';
      fieldsWrap.innerHTML = `
        ${field('j-m-coach-name',  'Full Name',             name,  'text',  'e.g. Coach Dinesh Fernando', false)}
        ${field('j-m-coach-spec',  'Role / Specialty',      spec,  'text',  'e.g. Head Coach', false)}
        ${field('j-m-coach-email', 'Email Address',         email, 'email', 'e.g. coach@lecole.edu', false)}
        ${field('j-m-coach-phone', 'Contact Number',        phone, 'tel',   'e.g. +94 71 987 6543', false)}
      `;

      form.onsubmit = (e) => {
        e.preventDefault();
        const newName  = document.getElementById('j-m-coach-name')?.value.trim()  || name;
        const newSpec  = document.getElementById('j-m-coach-spec')?.value.trim()  || spec;
        const newEmail = document.getElementById('j-m-coach-email')?.value.trim() || '';
        const newPhone = document.getElementById('j-m-coach-phone')?.value.trim() || '';

        const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        setTxt('j-detail-coach-name',      newName);
        setTxt('j-detail-coach-specialty', newSpec);
        setTxt('j-detail-coach-email',     newEmail);
        setTxt('j-detail-coach-phone',     newPhone);

        if (window.currentExtracurricularClub) {
          const t = window.currentExtracurricularClub;
          if (!t.coach) t.coach = {};
          t.coach.name      = newName;
          t.coach.specialty = newSpec;
          t.coach.email     = newEmail;
          t.coach.phone     = newPhone;
        }
        closeModal();
      };
      openModal();
    });

    // -----------------------------------------------------------------------
    // 3. Session Details (Schedule & Venue)
    // -----------------------------------------------------------------------
    document.getElementById('j-open-edit-details')?.addEventListener('click', () => {
      const sched = document.getElementById('j-detail-schedule')?.textContent.trim() || '';
      const loc   = document.getElementById('j-detail-location')?.textContent.trim() || '';

      if (title) title.textContent = 'Edit Session Details';
      if (desc)  desc.textContent  = 'Update the session schedule times and physical venue.';
      fieldsWrap.innerHTML = `
        ${field('j-m-sched', 'Schedule / Session Times', sched, 'text', 'e.g. Tuesdays & Thursdays, 3:30 – 5:30 PM', true)}
        ${field('j-m-loc',   'Location / Venue',         loc,   'text', 'e.g. Main Sports Complex', true)}
      `;

      form.onsubmit = (e) => {
        e.preventDefault();
        const newSched = document.getElementById('j-m-sched')?.value.trim() || sched;
        const newLoc   = document.getElementById('j-m-loc')?.value.trim()   || loc;

        const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        setTxt('j-detail-schedule', newSched);
        setTxt('j-detail-location', newLoc);

        if (window.currentExtracurricularClub) {
          window.currentExtracurricularClub.schedule = newSched;
          window.currentExtracurricularClub.location = newLoc;
        }
        closeModal();
      };
      openModal();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScheduleDetailsComponent);
  } else {
    initScheduleDetailsComponent();
  }
})();
</script>
<?php endif; ?>
