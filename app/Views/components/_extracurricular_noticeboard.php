<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR CLUB NOTICE BOARD COMPONENT
 * =========================================================================
 * Reusable notice board panel for the club detail view.
 * Displays notices linked to this club/program using _notice_card.php.
 * 
 * Per specifications:
 *   - No Post Notice button
 *   - No pin / edit / delete buttons on cards ($showActions = false)
 *   - Clean, lightweight display of notice cards
 * 
 * Expects:
 *   - $club       : array  (Current club data; 'notices' key = array of notices)
 *   - $clubName   : string (Club name for empty state messaging)
 * =========================================================================
 */

$n_club     = $club ?? [];
$n_clubName = $clubName ?? ($n_club['name'] ?? 'this club');
$clubNotices = $n_club['notices'] ?? [];

// Fallback demo notices if none present
if (empty($clubNotices)) {
    $clubNotices = [
        [
            'id'       => 'cn-1',
            'title'    => 'New Training Kit Distribution',
            'body'     => 'Collect the new season kit from the sports office before Friday afternoon.',
            'category' => 'Extracurricular',
            'audience' => ['Students', 'Teachers'],
            'author'   => 'Club Coach',
            'date'     => '22 OCT 2024',
            'pinned'   => true,
        ],
        [
            'id'       => 'cn-2',
            'title'    => 'Fitness Assessment Week',
            'body'     => 'Mandatory fitness screening for all senior & junior players will take place next week.',
            'category' => 'Extracurricular',
            'audience' => ['Students'],
            'author'   => 'Teacher in Charge',
            'date'     => '18 OCT 2024',
            'pinned'   => false,
        ],
    ];
}

// Sort pinned notices to the top
usort($clubNotices, function($a, $b) {
    return (int)!empty($b['pinned']) - (int)!empty($a['pinned']);
});
?>

<section class="c-panel" id="j-club-noticeboard-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
  <!-- Panel Header (Strictly Display Only - No Post Notice button) -->
  <div class="c-panel__heading-row" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 1.25rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <span class="c-panel__heading-icon">
        <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-bell"/>
        </svg>
      </span>
      <h2 class="c-panel__title c-font-display" style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A);">Notice Board</h2>
    </div>
  </div>

  <!-- Notices Grid -->
  <div class="c-notice-grid" id="j-club-notices-grid">
    <?php foreach ($clubNotices as $idx => $noticeItem): ?>
      <?php
        $notice       = $noticeItem;
        $showActions  = false; // Display-only: no pin/edit/delete buttons
        $hidePinBadge = false; // Show pin badge if pinned
        $cardIndex    = $idx;
        require __DIR__ . '/_notice_card.php';
      ?>
    <?php endforeach; ?>
  </div>

  <!-- Empty state if no notices -->
  <div class="c-empty-box" id="j-club-notices-empty" style="display: <?= empty($clubNotices) ? 'block' : 'none' ?>; text-align: center; padding: 2rem;">
    <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.35; margin-bottom: 0.5rem;">
      <use href="#icon-bell"/>
    </svg>
    <p style="margin: 0; color: rgba(15, 65, 74, 0.7); font-size: 0.9375rem;">No recent notices posted for <?= htmlspecialchars($n_clubName) ?>.</p>
  </div>
</section>

<script>
/**
 * Dynamic notice board renderer for extracurricular detail view.
 * Re-renders notice cards when switching active club cards without page reload.
 */
window.renderNoticesForClub = function(club) {
  const grid = document.getElementById('j-club-notices-grid');
  const empty = document.getElementById('j-club-notices-empty');
  if (!grid) return;

  const notices = (club && club.notices && club.notices.length > 0) ? club.notices : [];

  if (notices.length === 0) {
    grid.innerHTML = '';
    if (empty) empty.style.display = 'block';
    return;
  }

  if (empty) empty.style.display = 'none';

  // Sort pinned first
  const sorted = notices.slice().sort((a, b) => (Number(b.pinned || 0) - Number(a.pinned || 0)));

  grid.innerHTML = sorted.map((n, idx) => {
    const isPinned = !!n.pinned;
    const cat = n.category || 'Extracurricular';
    const audience = Array.isArray(n.audience) ? n.audience : ['Students'];
    const title = n.title || 'Notice';
    const body = n.body || '';
    const author = n.author || (club.name ? club.name + ' Admin' : 'Faculty');
    const date = (n.date || 'RECENT').toUpperCase();

    const parts = author.trim().split(' ');
    let initials = '';
    parts.forEach(p => { if (p) initials += p[0]; });
    initials = initials.slice(0, 2).toUpperCase() || 'AD';

    return `
      <article class="c-notice-card" data-category="${cat.replace(/"/g, '&quot;')}" tabindex="0" style="animation-delay:${idx * 40}ms">
        <div class="c-notice-card__tags">
          <span class="c-tag c-tag--category">${cat}</span>
          ${audience.map(a => `<span class="c-tag c-tag--audience">${a}</span>`).join('')}
        </div>
        <h3 class="c-notice-card__title">${title}</h3>
        <p class="c-notice-card__body">${body}</p>
        <footer class="c-notice-card__footer">
          <div class="c-notice-card__author">
            <span class="c-avatar">${initials}</span>
            <div>
              <p class="c-notice-card__author-name">${author}</p>
              <p class="c-notice-card__date">${date}</p>
            </div>
          </div>
        </footer>
      </article>
    `;
  }).join('');
};
</script>
