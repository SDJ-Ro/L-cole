<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR TEAMS & ROSTER PANEL COMPONENT
 * =========================================================================
 * Renders the Teams & Roster section for Admin / Teacher / Management.
 * - Add Team button → c-modal-layer popup with Age Group dropdown (_dropdown.php)
 * - Renders each team card using _extracurricular_team_card.php (which contains its own Edit modal)
 * - Danger Zone → window.openUniversalDeleteModal (from _delete_modal.php)
 *
 * Expects:
 *   - $club      : array  (Current club/program data)
 *   - $canEdit   : bool   (Admin/Teacher: true; Management: false)
 *   - $canDelete : bool   (Admin only: true — shows Danger Zone)
 *   - $memberWord: string (e.g. 'Members'; optional)
 *   - $teamWord  : string (e.g. 'Teams'; optional)
 * =========================================================================
 */

$tp_club      = $club      ?? [];
$tp_canEdit   = $canEdit   ?? true;
$tp_canDel    = $canDelete ?? false;
$tp_teamW     = $teamWord  ?? 'Teams';
$tp_memberW   = $memberWord ?? 'Members';
$tp_singTeam  = rtrim($tp_teamW, 's') ?: 'Team';
$tp_singMbr   = rtrim($tp_memberW, 's') ?: 'Member';

$tp_teams     = $tp_club['teams']     ?? [];
$tp_ageGroups = $tp_club['ageGroups'] ?? ['Under 13', 'Under 15', 'Under 17', 'Under 19', 'Open'];
$tp_unassigned = $tp_club['unassignedStudents'] ?? [
    ['id' => 'u1', 'name' => 'Sahan Peiris',    'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&h=100&fit=crop', 'ageGroup' => 'Under 19'],
    ['id' => 'u2', 'name' => 'Kushan Silva',    'grade' => 'Grade 9',  'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop', 'ageGroup' => 'Under 15'],
    ['id' => 'u3', 'name' => 'Hasindu Bandara', 'grade' => 'Grade 11', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop', 'ageGroup' => 'Under 19'],
];

// Initial fallback demo teams if not provided
if (empty($tp_teams)) {
    $tp_teams = [
        [
            'name'        => 'Senior ' . $tp_singTeam,
            'coverImage'  => '',
            'ageGroup'    => 'Under 19',
            'roster'      => [
                ['name' => 'Kasun Perera',    'grade' => 'Grade 12', 'position' => 'Captain',     'avatar' => ''],
                ['name' => 'Dineth Rodrigo',  'grade' => 'Grade 11', 'position' => 'Vice Captain', 'avatar' => ''],
                ['name' => 'Ravindu Silva',   'grade' => 'Grade 12', 'position' => '',              'avatar' => ''],
            ],
        ],
        [
            'name'        => 'Junior ' . $tp_singTeam,
            'coverImage'  => '',
            'ageGroup'    => 'Under 15',
            'roster'      => [
                ['name' => 'Sachith Fernando', 'grade' => 'Grade 9', 'position' => 'Striker',     'avatar' => ''],
            ],
        ],
    ];
}
?>

<!-- =========================================================================
     TEAMS & ROSTER SECTION
     ========================================================================= -->
<section class="c-panel" id="j-teams-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
  <!-- Panel header -->
  <div class="c-panel__heading-row" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
    <div style="display:flex;align-items:center;gap:0.5rem;">
      <span class="c-panel__heading-icon">
        <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-users"/>
        </svg>
      </span>
      <h2 class="c-panel__title c-font-display" style="margin:0;font-size:1.125rem;font-weight:700;color:var(--midnight,#0F414A);">
        <?= htmlspecialchars($tp_teamW) ?> &amp; Roster
      </h2>
    </div>

    <?php if ($tp_canEdit): ?>
      <button type="button" class="c-btn c-btn--sky c-btn--sm" id="j-open-team-create"
              style="display:flex;align-items:center;gap:0.35rem;font-size:0.8125rem;">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-plus"/>
        </svg>
        <span>Add <?= htmlspecialchars($tp_singTeam) ?></span>
      </button>
    <?php endif; ?>
  </div>

  <!-- Age Group Filter Bar -->
  <div class="c-age-group-header" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:1.25rem 0;padding-bottom:1rem;border-bottom:1px solid var(--color-border,#EFE8DF);flex-wrap:wrap;">
    <span class="c-age-group-header__title" style="font-size:0.875rem;font-weight:700;color:var(--midnight,#0F414A);">Filter by Age Group</span>
    <div style="min-width: 180px;">
      <?php
      $dropdownId    = 'j-panel-age-group-filter';
      $dropdownLabel = 'Filter Age Group';
      $placeholder   = 'All Age Groups';
      $options       = array_merge(['All'], $tp_ageGroups);
      $selectedValue = 'All';
      $name          = 'filter_age_group';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
  </div>

  <!-- Unassigned Candidates Section (Renders each row via _unassigned_candidate_row.php) -->
  <div class="c-unassigned-section" id="j-unassigned-section" style="display: <?= empty($tp_unassigned) ? 'none' : 'block' ?>;">
    <div class="c-unassigned-header">
      <span class="c-unassigned-title">
        Unassigned Students (<span id="j-unassigned-count"><?= count($tp_unassigned) ?></span>)
      </span>
    </div>
    <div class="c-unassigned-list" id="j-unassigned-list">
      <?php foreach ($tp_unassigned as $sCand):
        $student = $sCand;
        $teams   = $tp_teams;
        $canEdit = $tp_canEdit;
        require __DIR__ . '/_unassigned_candidate_row.php';
      endforeach; ?>
    </div>
  </div>

  <!-- Teams Grid (renders each card via _extracurricular_team_card.php) -->
  <div class="c-team-grid" id="j-team-grid">
    <?php foreach ($tp_teams as $tIdx => $tData):
      $team       = $tData;
      $teamIndex  = $tIdx;
      $canEdit    = $tp_canEdit;
      $memberWord = $tp_memberW;
      $teamWord   = $tp_singTeam;
      $ageGroups  = $tp_ageGroups;
      require __DIR__ . '/_extracurricular_team_card.php';
    endforeach; ?>
  </div>

  <div class="c-empty-box" id="j-team-empty" style="display: <?= empty($tp_teams) ? 'block' : 'none' ?>;margin-top:1.5rem;text-align:center;padding:2rem;">
    <svg class="c-icon" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.35;">
      <use href="#icon-users"/>
    </svg>
    <p style="margin:0.5rem 0 0;color:rgba(15,65,74,0.7);font-size:0.9375rem;">No <?= htmlspecialchars(strtolower($tp_teamW)) ?> defined yet.</p>
    <?php if ($tp_canEdit): ?>
      <p style="font-size:0.8125rem;opacity:0.6;margin-top:0.25rem;">Click "Add <?= htmlspecialchars($tp_singTeam) ?>" above to create one.</p>
    <?php endif; ?>
  </div>
</section>

<!-- Danger Zone (Admin only) -->
<?php if ($tp_canDel): ?>
<section class="c-danger-zone" id="j-danger-zone">
  <div>
    <h3 class="c-danger-zone__heading">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-trash"/>
      </svg>
      Danger Zone
    </h3>
    <p class="c-danger-zone__desc">
      Permanently delete this extracurricular program card and all of its associated teams, notices, achievements, and schedules.
    </p>
  </div>
  <button type="button" class="c-btn--danger-zone j-delete-club-card-btn" id="j-delete-club-card-btn">
    Delete Card
  </button>
</section>
<?php endif; ?>

<?php if ($tp_canEdit): ?>
<!-- =========================================================================
     MODAL — ADD / EDIT TEAM (Universal Form Card)
     Unified single modal handling both team creation and team editing.
     ========================================================================= -->
<div class="c-modal-layer" id="j-team-modal" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close modal"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-team-modal-title" style="width: min(44rem, 94vw); max-width: 44rem; max-height: 90vh; padding: 0; overflow: visible; border: none; background: transparent; display: flex; flex-direction: column;">
    <form class="c-form-card" id="j-team-modal-form" novalidate style="margin: 0; display: flex; flex-direction: column; max-height: 90vh; background: #ffffff; border-radius: var(--radius-2xl, 1.25rem); overflow: hidden; box-shadow: 0 20px 45px rgba(15, 65, 74, 0.22); border: 1px solid var(--color-border, #EFE8DF);">
      <!-- Universal Form Header (Sand Theme) -->
      <header class="c-form-header c-form-header--sand" style="flex-shrink: 0;">
        <div class="c-form-header-row">
          <div>
            <h2 class="c-form-header-title c-font-display" id="j-team-modal-title">Add <?= htmlspecialchars($tp_singTeam) ?></h2>
            <p class="c-form-header-subtitle" id="j-team-modal-subtitle">Create a new <?= htmlspecialchars(strtolower($tp_singTeam)) ?> panel and assign initial members.</p>
          </div>
          <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close" style="color: var(--midnight, #0F414A); background: rgba(255,255,255,0.6); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 150ms ease;">
            <svg class="c-icon" width="18" height="18"><use href="#icon-close"/></svg>
          </button>
        </div>
      </header>

      <!-- Clean White Form Body Surface with Auto-Scroll -->
      <div class="c-form-body" style="padding: 1.5rem; overflow-y: auto; flex: 1 1 auto; max-height: calc(90vh - 140px); overscroll-behavior: contain;">
        <div class="c-form-grid c-form-grid--2col">
          <!-- Left Column: Name & Age Group -->
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="c-form-field">
              <label class="c-form-field__label" for="j-team-modal-name"><?= htmlspecialchars($tp_singTeam) ?> Name <span class="c-form-field__required">*</span></label>
              <input type="text" class="c-text-input" id="j-team-modal-name" placeholder="e.g. Senior <?= htmlspecialchars($tp_singTeam) ?>" required />
              <p class="c-field-error" id="j-team-modal-name-error" style="display:none; color:var(--terracotta,#AF5031); font-size:0.75rem; margin-top:0.25rem;"></p>
            </div>

            <!-- Age group custom dropdown -->
            <div class="c-form-field">
              <span class="c-form-field__label">Age Group</span>
              <?php
              $dropdownId    = 'j-team-modal-age-group';
              $dropdownLabel = 'Age Group';
              $placeholder   = 'Select age group';
              $options       = $tp_ageGroups;
              $selectedValue = '';
              $name          = 'age_group';
              require __DIR__ . '/_dropdown.php';
              ?>
            </div>
          </div>

          <!-- Right Column: Cover Image Upload Box -->
          <div class="c-form-field">
            <span class="c-form-field__label">Panel Cover</span>
            <label class="c-cover-upload c-cover-upload--sand" for="j-team-modal-cover-input" id="j-team-modal-cover-preview" style="height: 135px; border-radius: var(--radius-lg, 0.75rem); border: 2px dashed rgba(15, 65, 74, 0.2); display: flex; align-items: center; justify-content: center; overflow: hidden; background: rgba(228, 203, 169, 0.12); cursor: pointer; transition: border-color 150ms ease;">
              <span class="c-cover-upload__placeholder" style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem; color: rgba(15, 65, 74, 0.6); font-size: 0.8125rem; font-weight: 600;">
                <svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <use href="#icon-imagePlus"/>
                </svg>
                Upload cover photo
              </span>
            </label>
            <input class="c-visually-hidden" id="j-team-modal-cover-input" type="file" accept="image/*" style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;" />
          </div>
        </div>

        <!-- Roster Section -->
        <section class="c-form-section" style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border, #EFE8DF);">
          <div class="c-form-section__head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.875rem;">
            <div>
              <h3 class="c-form-section__title" style="margin: 0; font-size: 0.9375rem; font-weight: 700; color: var(--midnight, #0F414A);">Roster</h3>
              <p class="c-form-section__hint" style="margin: 0.15rem 0 0; font-size: 0.75rem; color: rgba(15, 65, 74, 0.65);">Add, update, or remove <?= htmlspecialchars(strtolower($tp_memberW)) ?>.</p>
            </div>
            <button type="button" class="c-btn c-btn--sky c-btn--sm" id="j-team-modal-add-member-btn"
                    style="display:flex;align-items:center;gap:0.35rem;font-size:0.8125rem;flex-shrink:0;">
              <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <use href="#icon-plus"/>
              </svg>
              Add <?= htmlspecialchars($tp_singMbr) ?>
            </button>
          </div>
          <p class="c-field-error" id="j-team-modal-roster-error" style="display:none; color:var(--terracotta,#AF5031); font-size:0.75rem; margin-bottom:0.5rem;"></p>
          <div id="j-team-modal-roster-wrap" style="display:flex;flex-direction:column;gap:0.75rem;"></div>
        </section>
      </div>

      <!-- Actions Footer -->
      <footer class="c-form-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; background: #FAF7F2; border-top: 1px solid var(--color-border, #EFE8DF); display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="c-btn c-btn--sm" id="j-team-modal-delete-btn"
                style="display:none; background:rgba(127,3,3,0.08);color:#7f0303;border:1px solid rgba(127,3,3,0.25);align-items:center;gap:0.35rem;">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <use href="#icon-trash"/>
          </svg>
          Delete <?= htmlspecialchars($tp_singTeam) ?>
        </button>
        <div style="display:flex;gap:0.75rem;margin-left:auto;">
          <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
          <button type="submit" class="c-btn c-btn--solid c-btn--sky" id="j-team-modal-submit-btn">Create <?= htmlspecialchars($tp_singTeam) ?></button>
        </div>
      </footer>
    </form>
  </section>
</div>
<?php endif; ?>

<script>
/**
 * Self-contained Teams & Roster panel controller:
 * - Age Group live filtering
 * - Unified Add & Edit Team modal controller
 * - Danger Zone permanently deletes club card via window.openUniversalDeleteModal
 */
(function() {
  function initTeamsPanel() {
    const panel = document.getElementById('j-teams-panel');
    if (!panel || panel.dataset.teamsInitialized === 'true') return;
    panel.dataset.teamsInitialized = 'true';

    // Initialize unassignedStudents if not already on window.currentExtracurricularClub
    if (window.currentExtracurricularClub && !window.currentExtracurricularClub.unassignedStudents) {
      window.currentExtracurricularClub.unassignedStudents = <?= json_encode($tp_unassigned) ?>;
    }

    // 1. Age Group Filter
    const filterDropdown = document.getElementById('j-panel-age-group-filter');
    if (filterDropdown) {
      filterDropdown.addEventListener('dropdown:change', (e) => {
        const val = (e.detail?.value || '').toLowerCase();
        const cards = panel.querySelectorAll('.c-team-card');
        let visibleTeams = 0;
        cards.forEach((card) => {
          const cardIdx = card.dataset.teamIndex;
          const club = window.currentExtracurricularClub;
          const teamData = club && club.teams && club.teams[cardIdx] ? club.teams[cardIdx] : null;
          const age = (teamData?.ageGroup || '').toLowerCase();

          if (!val || val === 'all' || age.includes(val) || val.includes(age)) {
            card.style.display = '';
            visibleTeams++;
          } else {
            card.style.display = 'none';
          }
        });

        const emptyBox = document.getElementById('j-team-empty');
        if (emptyBox) {
          emptyBox.style.display = visibleTeams === 0 ? 'block' : 'none';
        }

        // Filter unassigned candidates by age group
        const candidateItems = panel.querySelectorAll('.c-unassigned-item');
        let visibleCandidates = 0;
        candidateItems.forEach((item) => {
          const age = (item.dataset.ageGroup || '').toLowerCase();
          if (!val || val === 'all' || !age || age.includes(val) || val.includes(age)) {
            item.style.display = '';
            visibleCandidates++;
          } else {
            item.style.display = 'none';
          }
        });

        const unassignedSec = document.getElementById('j-unassigned-section');
        if (unassignedSec) {
          unassignedSec.style.display = visibleCandidates === 0 ? 'none' : 'block';
        }
      });
    }

    // 2. Unified Team Modal Controller (Add & Edit)
    const modal = document.getElementById('j-team-modal');
    const form = document.getElementById('j-team-modal-form');
    const titleEl = document.getElementById('j-team-modal-title');
    const subEl = document.getElementById('j-team-modal-subtitle');
    const nameInp = document.getElementById('j-team-modal-name');
    const nameErr = document.getElementById('j-team-modal-name-error');
    const rostErr = document.getElementById('j-team-modal-roster-error');
    const rosterWrap = document.getElementById('j-team-modal-roster-wrap');
    const addMemberBtn = document.getElementById('j-team-modal-add-member-btn');
    const coverInput = document.getElementById('j-team-modal-cover-input');
    const coverPreview = document.getElementById('j-team-modal-cover-preview');
    const deleteBtn = document.getElementById('j-team-modal-delete-btn');
    const submitBtn = document.getElementById('j-team-modal-submit-btn');

    let currentEditingTeamIndex = null;
    let rosterState = [];
    let coverState = '';
    let removedFromRoster = [];

    const singTeamName = <?= json_encode($tp_singTeam) ?>;
    const singMbrName = <?= json_encode($tp_singMbr) ?>;

    function renderRoster() {
      if (!rosterWrap) return;
      if (rosterState.length === 0) {
        rosterWrap.innerHTML = `<div class="c-empty-box" style="padding:1.25rem;text-align:center;font-size:0.8125rem;color:rgba(15,65,74,0.6);border:1px dashed var(--color-border,#EFE8DF);border-radius:var(--radius-xl,0.875rem);">No ${singMbrName.toLowerCase()}s yet. This ${singTeamName.toLowerCase()} can be saved empty.</div>`;
        return;
      }
      rosterWrap.innerHTML = rosterState.map((m, idx) => `
        <div class="c-roster-row" data-row-index="${idx}" style="display:grid;grid-template-columns:1fr 100px 1fr 36px;gap:0.5rem;align-items:center;">
          <input class="c-text-input" data-field="name" placeholder="Student name" value="${(m.name || '').replace(/"/g, '&quot;')}" />
          <input class="c-text-input" data-field="grade" placeholder="Grade" value="${(m.grade || '').replace(/"/g, '&quot;')}" />
          <input class="c-text-input" data-field="position" placeholder="Role / position" value="${(m.position || '').replace(/"/g, '&quot;')}" />
          <button type="button" class="c-roster-row__remove-btn j-remove-roster-row" data-index="${idx}" title="Remove entry" style="border:none;background:rgba(127,3,3,0.08);color:#7F0303;border-radius:var(--radius-sm,0.375rem);width:36px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
            <svg class="c-icon" width="16" height="16"><use href="#icon-close"/></svg>
          </button>
        </div>
      `).join('');
    }

    function setCoverPreview(src) {
      if (!coverPreview) return;
      if (src) {
        coverPreview.innerHTML = `<img src="${src}" alt="Cover preview" style="width:100%;height:100%;object-fit:cover;" />`;
      } else {
        coverPreview.innerHTML = `
          <span class="c-cover-upload__placeholder" style="display:flex;flex-direction:column;align-items:center;gap:0.35rem;color:rgba(15,65,74,0.6);font-size:0.8125rem;font-weight:600;">
            <svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <use href="#icon-imagePlus"/>
            </svg>
            Upload cover photo
          </span>`;
      }
    }

    function openModal(mode, teamIdx = null) {
      if (!modal) return;
      currentEditingTeamIndex = (mode === 'edit') ? teamIdx : null;
      removedFromRoster = [];
      if (nameErr) { nameErr.textContent = ''; nameErr.style.display = 'none'; }
      if (rostErr) { rostErr.textContent = ''; rostErr.style.display = 'none'; }

      const ageDropdown = document.getElementById('j-team-modal-age-group');
      const ageLabel = ageDropdown?.querySelector('.c-dropdown__selected');
      const ageInput = ageDropdown?.querySelector('input[name="age_group"]');

      if (mode === 'add') {
        if (titleEl) titleEl.textContent = `Add ${singTeamName}`;
        if (subEl) subEl.textContent = `Create a new ${singTeamName.toLowerCase()} panel and assign initial members.`;
        if (submitBtn) submitBtn.textContent = `Create ${singTeamName}`;
        if (deleteBtn) deleteBtn.style.display = 'none';
        if (nameInp) nameInp.value = '';
        if (ageLabel) ageLabel.textContent = 'Select age group';
        if (ageInput) ageInput.value = '';
        coverState = '';
        rosterState = [];
        setCoverPreview('');
      } else {
        const club = window.currentExtracurricularClub;
        const team = club && club.teams ? club.teams[teamIdx] : null;
        if (!team) return;

        if (titleEl) titleEl.textContent = `Edit ${team.name || singTeamName}`;
        if (subEl) subEl.textContent = `Update this ${singTeamName.toLowerCase()}, its roster, and cover image.`;
        if (submitBtn) submitBtn.textContent = 'Save changes';
        if (deleteBtn) deleteBtn.style.display = 'inline-flex';
        if (nameInp) nameInp.value = team.name || '';
        const ageVal = team.ageGroup || '';
        if (ageLabel) ageLabel.textContent = ageVal || 'Select age group';
        if (ageInput) ageInput.value = ageVal;
        coverState = team.coverImage || '';
        setCoverPreview(coverState);
        rosterState = Array.isArray(team.roster) ? team.roster.map(m => ({ ...m })) : [];
      }

      renderRoster();
      document.body.style.overflow = 'hidden';
      modal.classList.add('c-is-open');
      requestAnimationFrame(() => {
        nameInp?.focus();
        nameInp?.select && nameInp.select();
      });
    }

    function closeModal() {
      if (!modal) return;
      document.body.style.overflow = '';
      modal.classList.remove('c-is-open');
      if (nameErr) { nameErr.textContent = ''; nameErr.style.display = 'none'; }
      if (rostErr) { rostErr.textContent = ''; rostErr.style.display = 'none'; }
    }

    if (modal) {
      document.getElementById('j-open-team-create')?.addEventListener('click', () => openModal('add'));
      modal.querySelector('.j-modal-backdrop')?.addEventListener('click', closeModal);
      modal.querySelectorAll('.j-modal-close').forEach(b => b.addEventListener('click', closeModal));
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('c-is-open')) closeModal();
      });

      coverInput?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
          coverState = ev.target.result;
          setCoverPreview(coverState);
        };
        reader.readAsDataURL(file);
      });

      addMemberBtn?.addEventListener('click', () => {
        rosterState.push({ name: '', grade: '', position: '', avatar: '' });
        renderRoster();
      });

      rosterWrap?.addEventListener('input', (e) => {
        const row = e.target.closest('[data-row-index]');
        if (!row) return;
        const idx = Number(row.dataset.rowIndex);
        const field = e.target.dataset.field;
        if (field && rosterState[idx]) {
          rosterState[idx][field] = e.target.value;
        }
      });

      rosterWrap?.addEventListener('click', (e) => {
        const btn = e.target.closest('.j-remove-roster-row');
        if (!btn) return;
        const idx = Number(btn.dataset.index);
        const removed = rosterState.splice(idx, 1)[0];
        if (currentEditingTeamIndex !== null && removed && removed.name) {
          removedFromRoster.push(removed);
        }
        renderRoster();
      });

      form?.addEventListener('submit', (e) => {
        e.preventDefault();
        const nameVal = nameInp ? nameInp.value.trim() : '';
        if (!nameVal) {
          if (nameErr) { nameErr.textContent = `Please enter a ${singTeamName.toLowerCase()} name.`; nameErr.style.display = 'block'; }
          nameInp?.focus();
          return;
        }
        if (nameErr) { nameErr.textContent = ''; nameErr.style.display = 'none'; }

        const hasEmpty = rosterState.some(m => !m.name.trim() || !m.grade.trim());
        if (hasEmpty) {
          if (rostErr) { rostErr.textContent = 'Please complete or remove incomplete roster entries.'; rostErr.style.display = 'block'; }
          return;
        }
        if (rostErr) { rostErr.textContent = ''; rostErr.style.display = 'none'; }

        const ageDropdown = document.getElementById('j-team-modal-age-group');
        const ageVal = ageDropdown?.querySelector('input[name="age_group"]')?.value || '';

        const teamData = {
          name: nameVal,
          ageGroup: ageVal,
          coverImage: coverState,
          roster: rosterState.map(m => ({
            id: m.id || ('r_' + Date.now() + Math.random().toString(36).substr(2, 4)),
            name: m.name.trim(),
            grade: m.grade.trim(),
            position: (m.position || '').trim(),
            avatar: m.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&size=64&background=EFE8DF&color=0F414A`
          }))
        };

        const club = window.currentExtracurricularClub;
        if (club) {
          if (!club.teams) club.teams = [];

          if (currentEditingTeamIndex === null) {
            club.teams.push(teamData);
          } else {
            if (removedFromRoster.length > 0) {
              if (!club.unassignedStudents) club.unassignedStudents = [];
              removedFromRoster.forEach(m => {
                if (m.name) {
                  club.unassignedStudents.push({
                    id: m.id || ('u_' + Date.now() + Math.random().toString(36).substr(2, 4)),
                    name: m.name,
                    grade: m.grade || '',
                    avatar: m.avatar || '',
                    ageGroup: ageVal || ''
                  });
                }
              });
              removedFromRoster = [];
            }
            club.teams[currentEditingTeamIndex] = teamData;
          }

          window.renderTeamsForClub(club);
        }

        closeModal();
      });

      deleteBtn?.addEventListener('click', () => {
        if (currentEditingTeamIndex === null || !window.currentExtracurricularClub) return;
        const teamName = nameInp?.value || `this ${singTeamName.toLowerCase()}`;

        const doDeleteTeam = () => {
          const deleted = window.currentExtracurricularClub.teams.splice(currentEditingTeamIndex, 1)[0];
          if (deleted && Array.isArray(deleted.roster)) {
            if (!window.currentExtracurricularClub.unassignedStudents) {
              window.currentExtracurricularClub.unassignedStudents = [];
            }
            deleted.roster.forEach(m => {
              if (m && m.name) {
                window.currentExtracurricularClub.unassignedStudents.push({
                  id: m.id || ('u_' + Date.now() + Math.random().toString(36).substr(2, 4)),
                  name: m.name,
                  grade: m.grade || '',
                  avatar: m.avatar || '',
                  ageGroup: deleted.ageGroup || ''
                });
              }
            });
          }
          window.renderTeamsForClub(window.currentExtracurricularClub);
          closeModal();
        };

        if (typeof window.openUniversalDeleteModal === 'function') {
          window.openUniversalDeleteModal({
            title: `Delete ${teamName}?`,
            description: `Are you sure you want to delete this ${singTeamName.toLowerCase()}? All associated roster members will be moved back to the Unassigned pool.`,
            buttonText: `Delete ${singTeamName}`,
            onConfirm: doDeleteTeam
          });
        } else if (confirm(`Delete ${teamName}? All members will be sent to the unassigned pool.`)) {
          doDeleteTeam();
        }
      });
    }

    // Delegated click handler on document for Edit Team and Assign Dropdown
    document.addEventListener('click', (e) => {
      // 1. Edit Team Button (Opens modal to manage/edit roster)
      const editBtn = e.target.closest('.c-team-card__edit-btn, [class*="j-edit-team-btn"]');
      if (editBtn) {
        const tIdx = Number(editBtn.dataset.teamIndex);
        if (!isNaN(tIdx)) {
          openModal('edit', tIdx);
        }
        return;
      }

      // 2. Click "Assign" trigger button -> toggles choices dropdown
      const assignTrigger = e.target.closest('.j-assign-trigger-btn');
      if (assignTrigger) {
        e.stopPropagation();
        const dropdown = assignTrigger.closest('.j-unassigned-dropdown');
        const itemRow = assignTrigger.closest('.c-unassigned-item');
        const wasOpen = dropdown ? dropdown.classList.contains('c-is-open') : false;
        
        document.querySelectorAll('.c-unassigned-item.c-has-open-dropdown').forEach(it => it.classList.remove('c-has-open-dropdown'));
        document.querySelectorAll('.j-unassigned-dropdown.c-is-open').forEach(d => {
          d.classList.remove('c-is-open');
          d.querySelector('.j-assign-trigger-btn')?.setAttribute('aria-expanded', 'false');
        });
        if (!wasOpen && dropdown) {
          dropdown.classList.add('c-is-open');
          assignTrigger.setAttribute('aria-expanded', 'true');
          itemRow?.classList.add('c-has-open-dropdown');
        }
        return;
      }

      // 3. Click choice in Assign dropdown -> assigns candidate to chosen team
      const choiceBtn = e.target.closest('.j-assign-team-choice');
      if (choiceBtn && window.currentExtracurricularClub) {
        const studentId = choiceBtn.dataset.studentId;
        const targetTeamIdx = Number(choiceBtn.dataset.teamIndex);
        document.querySelectorAll('.c-unassigned-item.c-has-open-dropdown').forEach(it => it.classList.remove('c-has-open-dropdown'));
        document.querySelectorAll('.j-unassigned-dropdown.c-is-open').forEach(d => d.classList.remove('c-is-open'));

        if (!window.currentExtracurricularClub.unassignedStudents) {
          window.currentExtracurricularClub.unassignedStudents = [];
        }
        const sIdx = window.currentExtracurricularClub.unassignedStudents.findIndex(s => String(s.id) === String(studentId));
        if (sIdx !== -1) {
          const student = window.currentExtracurricularClub.unassignedStudents.splice(sIdx, 1)[0];
          const targetTeam = window.currentExtracurricularClub.teams[targetTeamIdx];
          if (targetTeam) {
            if (!targetTeam.roster) targetTeam.roster = [];
            targetTeam.roster.push({
              id: student.id || ('r_' + Date.now()),
              name: student.name,
              grade: student.grade || '',
              avatar: student.avatar || '',
              position: ''
            });
          }
          window.renderTeamsForClub(window.currentExtracurricularClub);
        }
        return;
      }

      // Click outside closes any open dropdown
      if (!e.target.closest('.j-unassigned-dropdown')) {
        document.querySelectorAll('.c-unassigned-item.c-has-open-dropdown').forEach(it => it.classList.remove('c-has-open-dropdown'));
        document.querySelectorAll('.j-unassigned-dropdown.c-is-open').forEach(d => {
          d.classList.remove('c-is-open');
          d.querySelector('.j-assign-trigger-btn')?.setAttribute('aria-expanded', 'false');
        });
      }
    });

    // 4. Danger Zone — Delete Program Card
    const deleteCardBtn = document.getElementById('j-delete-club-card-btn');
    if (deleteCardBtn) {
      deleteCardBtn.addEventListener('click', () => {
        const club = window.currentExtracurricularClub || {};
        const clubName = club.name || 'this extracurricular program';
        const clubId = club.id;

        if (typeof window.openUniversalDeleteModal === 'function') {
          window.openUniversalDeleteModal({
            title: `Delete "${clubName}"?`,
            description: `Are you sure you want to permanently delete "${clubName}"? All of its associated teams, notices, achievements, and schedules will be permanently erased. This action cannot be undone.`,
            buttonText: 'Delete Card',
            onConfirm: () => {
              if (clubId) {
                const cardOnGrid = document.querySelector(`.j-club-card[data-club-id="${clubId}"]`);
                if (cardOnGrid) cardOnGrid.remove();
                if (window.allClubsData) {
                  const cIdx = window.allClubsData.findIndex(c => Number(c.id) === Number(clubId));
                  if (cIdx !== -1) window.allClubsData.splice(cIdx, 1);
                }
              }
              const backBtn = document.getElementById('j-back-to-activities-grid');
              if (backBtn) {
                backBtn.click();
              } else {
                const detailView = document.getElementById('j-view-club-detail');
                const overviewView = document.getElementById('j-view-overview');
                if (detailView) detailView.style.display = 'none';
                if (overviewView) overviewView.style.display = 'block';
              }
            }
          });
        } else if (confirm(`Are you sure you want to permanently delete "${clubName}"?`)) {
          const backBtn = document.getElementById('j-back-to-activities-grid');
          if (backBtn) backBtn.click();
        }
      });
    }

    // 5. Dynamic Renderer for Teams & Unassigned Candidates
    window.renderTeamsForClub = function(club) {
      const grid = document.getElementById('j-team-grid');
      const emptyBox = document.getElementById('j-team-empty');
      const unassignedSec = document.getElementById('j-unassigned-section');
      const unassignedList = document.getElementById('j-unassigned-list');
      const unassignedCountEl = document.getElementById('j-unassigned-count');
      if (!grid) return;

      const teams = (club && Array.isArray(club.teams)) ? club.teams : [];
      const unassigned = (club && Array.isArray(club.unassignedStudents)) ? club.unassignedStudents : [];
      const teamWord = (club && club.type === 'Clubs and Societies') ? 'Group' : 'Team';
      const teamsWord = (club && club.type === 'Clubs and Societies') ? 'Groups' : 'Teams';
      const memberWord = (club && club.type === 'Clubs and Societies') ? 'Members' : 'Players';

      // A. Update Unassigned Candidates Section
      if (unassignedSec) {
        unassignedSec.style.display = unassigned.length > 0 ? 'block' : 'none';
      }
      if (unassignedCountEl) {
        unassignedCountEl.textContent = unassigned.length;
      }
      if (unassignedList) {
        unassignedList.innerHTML = unassigned.map(s => {
          const sName = s.name || 'Candidate';
          const sGrade = s.grade || 'Student';
          const sAge = s.ageGroup || '';
          const sAvatar = s.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(sName)}&size=64&background=EFE8DF&color=0F414A`;
          const sId = s.id || ('u_' + Date.now());

          return `
            <div class="c-unassigned-item" id="j-unassigned-item-${sId}" data-student-id="${sId}" data-age-group="${sAge}">
              <div class="c-unassigned-item__left">
                <img src="${sAvatar}" alt="${sName.replace(/"/g, '&quot;')}" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(sName)}&size=64&background=EFE8DF&color=0F414A';" />
                <div>
                  <p class="c-unassigned-item__name">${sName}</p>
                  <p class="c-unassigned-item__grade">
                    ${sGrade}
                    ${sAge ? `<span class="c-unassigned-item__badge">${sAge}</span>` : ''}
                  </p>
                </div>
              </div>
              <div class="c-unassigned-item__right">
                <div class="c-unassigned-dropdown j-unassigned-dropdown" data-student-id="${sId}">
                  <button type="button" class="c-btn-assign j-assign-trigger-btn" aria-haspopup="true" aria-expanded="false" ${teams.length === 0 ? 'disabled' : ''}>
                    <span>Assign</span>
                    <svg class="c-icon" width="13" height="13"><use href="#icon-chevronDown"/></svg>
                  </button>
                  <div class="c-unassigned-menu j-unassigned-menu">
                    ${teams.map((t, idx) => `
                      <button type="button" class="c-unassigned-choice j-assign-team-choice" data-team-index="${idx}" data-student-id="${sId}">
                        <span>${(t.name || (teamWord + ' ' + (idx + 1))).replace(/"/g, '&quot;')}</span>
                        <svg class="c-icon" width="12" height="12"><use href="#icon-chevronRight"/></svg>
                      </button>
                    `).join('')}
                  </div>
                </div>
              </div>
            </div>
          `;
        }).join('');
      }

      // B. Update Teams Grid (NO cross marks on cards, editing happens via Edit button)
      if (teams.length === 0) {
        grid.innerHTML = '';
        if (emptyBox) {
          emptyBox.style.display = 'block';
          const p = emptyBox.querySelector('p');
          if (p) p.textContent = `No ${teamsWord.toLowerCase()} defined yet.`;
        }
        return;
      }

      if (emptyBox) emptyBox.style.display = 'none';

      grid.innerHTML = teams.map((team, idx) => {
        const name = team.name || `${teamWord} ${idx + 1}`;
        const cover = team.coverImage || '';
        const roster = Array.isArray(team.roster) ? team.roster : [];

        const coverHtml = cover 
          ? `<img src="${cover}" alt="${name.replace(/"/g, '&quot;')}" id="j-team-card-cover-img-${idx}" onerror="this.style.display='none'; if(this.parentElement.querySelector('.c-team-card__cover-empty')) this.parentElement.querySelector('.c-team-card__cover-empty').style.display='flex';" />
             <div class="c-team-card__cover-empty" id="j-team-card-cover-empty-${idx}" style="display:none;"><svg class="c-icon" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><use href="#icon-imagePlus"/></svg></div>`
          : `<div class="c-team-card__cover-empty" id="j-team-card-cover-empty-${idx}"><svg class="c-icon" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><use href="#icon-imagePlus"/></svg></div>`;

        const rosterHtml = roster.length > 0 
          ? `<ul class="c-team-card__roster" id="j-team-card-roster-${idx}">
              ${roster.map((m, mIdx) => `
                <li class="c-team-card__member" data-member-index="${mIdx}">
                  <div class="c-team-card__member-left">
                    <img src="${m.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(m.name || 'M') + '&size=64&background=EFE8DF&color=0F414A'}" alt="${(m.name || '').replace(/"/g, '&quot;')}" />
                    <div><p class="c-team-card__member-name">${m.name || ''}</p><p class="c-team-card__member-grade">${m.grade || ''}</p></div>
                  </div>
                  <div style="display:flex;align-items:center;gap:0.35rem;">
                    ${m.position ? `<span class="c-team-card__member-position">${m.position}</span>` : ''}
                  </div>
                </li>
              `).join('')}
            </ul>`
          : `<p class="c-team-card__no-roster" id="j-team-card-no-roster-${idx}">No ${memberWord.toLowerCase()} added yet.</p>`;

        return `
          <article class="c-team-card" id="j-team-card-${idx}" data-team-index="${idx}" style="animation-delay:${idx * 40}ms">
            <div class="c-team-card__cover" id="j-team-card-cover-wrap-${idx}">
              ${coverHtml}
              <div class="c-team-card__cover-tint"></div>
              <button type="button" class="c-team-card__edit-btn j-edit-team-btn-${idx}" data-team-index="${idx}" aria-label="Edit ${name.replace(/"/g, '&quot;')}">
                <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-edit"/></svg>
                Edit
              </button>
            </div>
            <div class="c-team-card__body">
              <div class="c-team-card__top">
                <h3 class="c-team-card__name c-font-display" id="j-team-card-name-${idx}">${name}</h3>
                <span class="c-team-card__count" id="j-team-card-count-${idx}">${roster.length} ${memberWord}</span>
              </div>
              ${rosterHtml}
            </div>
          </article>
        `;
      }).join('');
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTeamsPanel);
  } else {
    initTeamsPanel();
  }
})();
</script>
