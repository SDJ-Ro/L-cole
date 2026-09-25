<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT EXTRACURRICULAR ROSTER PANEL COMPONENT
 * =========================================================================
 * Renders the Team & Roster section for the student extracurricular detail view.
 * Features squad title, player count pill, and scrollable player list.
 * Reuses _student_extracurricular_roster_row.php per row item.
 *
 * Expects:
 *   - $club        : array (current club data with 'team' or 'teams')
 *   - $currentUser : string (default 'Jason Perera')
 * =========================================================================
 */

$clubData    = $club ?? [];
$userName    = $currentUser ?? 'Jason Perera';
$clubName    = $clubData['name'] ?? 'Squad';
$squadTitle  = $clubData['squadTitle'] ?? ($clubName . ' Squad');

// Extract team members
$teamMembers = $clubData['team'] ?? [];
if (empty($teamMembers) && !empty($clubData['teams'][0]['roster'])) {
    $teamMembers = array_map(function($r) {
        return [
            'name'  => $r['name'] ?? 'Player',
            'grade' => $r['grade'] ?? '',
            'role'  => $r['position'] ?? 'Player',
        ];
    }, $clubData['teams'][0]['roster']);
}
$playerCount = count($teamMembers);
?>

<section class="c-panel sc-roster-panel" id="j-roster-panel" style="background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06)); display: flex; flex-direction: column; box-sizing: border-box; height: 424px;">
  <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; flex-shrink: 0;">
    <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--midnight, #0F414A);">
      <use href="#icon-users"/>
    </svg>
    <h2 class="c-font-display" style="font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0;">Team &amp; Roster</h2>
  </div>

  <div class="sc-roster-card" style="margin: 0; display: flex; flex-direction: column; flex: 1; min-height: 0;">
    <div class="sc-roster-card-header" style="flex-shrink: 0;">
      <span class="sc-roster-card-title" id="j-roster-card-title"><?= htmlspecialchars($squadTitle) ?></span>
      <span class="sc-roster-card-count" id="j-roster-card-count"><?= $playerCount ?> Players</span>
    </div>

    <div class="sc-roster-list" id="j-roster-list" style="flex: 1; min-height: 0; overflow-y: auto;">
      <?php if (!empty($teamMembers)): ?>
        <?php foreach ($teamMembers as $member): ?>
          <?php require __DIR__ . '/_student_extracurricular_roster_row.php'; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="padding: 2.5rem 1rem; text-align: center; color: rgba(15, 65, 74, 0.5); font-size: 0.875rem;">
          No roster members registered yet.
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
/**
 * Dynamic roster renderer for student extracurricular detail view.
 * Refreshes the squad title, player count, and player rows when switching clubs.
 */
window.renderRosterForClub = function(club) {
  const titleEl = document.getElementById('j-roster-card-title');
  const countEl = document.getElementById('j-roster-card-count');
  const listEl = document.getElementById('j-roster-list');
  if (!listEl) return;

  const squadTitle = club.squadTitle || ((club.name || 'Squad') + ' Squad');
  if (titleEl) titleEl.textContent = squadTitle;

  let members = club.team || [];
  if ((!members || !members.length) && club.teams && club.teams[0] && club.teams[0].roster) {
    members = club.teams[0].roster.map(r => ({
      name: r.name,
      grade: r.grade || '',
      role: r.position || 'Player'
    }));
  }

  if (countEl) countEl.textContent = `${members ? members.length : 0} Players`;

  if (!members || !members.length) {
    listEl.innerHTML = '<div style="padding: 2.5rem 1rem; text-align: center; color: rgba(15, 65, 74, 0.5); font-size: 0.875rem;">No roster members registered yet.</div>';
    matchRosterToCalendarHeight();
    return;
  }

  const currentUser = 'Jason Perera';
  listEl.innerHTML = members.map(m => {
    const isYou = (m.name || '').trim().toLowerCase() === currentUser.toLowerCase();
    const parts = (m.name || '').trim().split(/\s+/);
    const initials = parts.length >= 2
      ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
      : (m.name || '').substring(0, 2).toUpperCase();
    const role = m.role || m.position || 'Player';

    return `
      <div class="sc-roster-item${isYou ? ' sc-roster-item-you' : ''}">
        <span class="sc-roster-avatar">${initials}</span>
        <div class="sc-roster-info">
          <div class="sc-roster-name-row">
            <span class="sc-roster-name">${m.name}</span>
            ${isYou ? '<span class="sc-roster-you-tag">YOU</span>' : ''}
          </div>
          ${m.grade ? `<div class="sc-roster-grade">${m.grade}</div>` : ''}
        </div>
        <div class="sc-roster-role">${role}</div>
      </div>
    `;
  }).join('');

  matchRosterToCalendarHeight();
};

/**
 * Sets the exact same height for the Team & Roster section as the
 * Schedule & Events calendar section on desktop layouts.
 */
function matchRosterToCalendarHeight() {
  const schedPanel = document.getElementById('j-schedule-panel');
  const rosterPanel = document.getElementById('j-roster-panel');
  if (!schedPanel || !rosterPanel) return;

  if (window.innerWidth >= 960) {
    const targetHeight = schedPanel.offsetHeight;
    if (targetHeight > 100) {
      rosterPanel.style.height = targetHeight + 'px';
    }
  } else {
    rosterPanel.style.height = 'auto';
  }
}

window.addEventListener('resize', matchRosterToCalendarHeight);
</script>
