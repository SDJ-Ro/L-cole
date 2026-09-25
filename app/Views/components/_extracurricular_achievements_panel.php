<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR ACHIEVEMENTS & GALLERY PANEL COMPONENT
 * =========================================================================
 * Shared panel rendered inside the extracurricular club detail view.
 * Eliminates ~40 lines of duplicated HTML across 4 role-specific views.
 *
 * Expects (from parent scope):
 *   - $canCreate : bool   (optional) — if truthy, shows the "+ Add Achievement" button
 *   - $club      : array  (optional) — if $club['awards'] is set, pre-renders server-side
 *                                      achievement cards for roles that need SSR (teacher).
 * =========================================================================
 */

$_awards    = $club['awards'] ?? [];
$_hasAwards = !empty($_awards);
?>

<!-- Achievements & Gallery Section -->
<section class="c-panel" id="j-achievements-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--terracotta, #AF5031);">
        <use href="#icon-trophy"/>
      </svg>
      <h2 class="c-font-display" style="font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0;">Achievements &amp; Gallery</h2>
    </div>

    <?php if (!empty($canCreate)): ?>
      <button type="button" class="c-btn c-btn--sky c-btn--sm j-open-add-achievement" id="j-open-add-achievement" style="display: inline-flex; align-items: center; gap: 0.35rem;">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-plus"/>
        </svg>
        <span>Add Achievement</span>
      </button>
    <?php endif; ?>
  </div>

  <!-- Achievements Grid -->
  <div class="c-achv-grid" id="j-achv-grid" style="<?= $_hasAwards ? 'display: grid;' : 'display: none;' ?>">
    <?php if ($_hasAwards): ?>
      <?php foreach ($_awards as $achievementIndex => $achievement): ?>
        <?php require __DIR__ . '/_extracurricular_card_achievement_card.php'; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Empty State -->
  <div class="c-achv-empty-box" id="j-achv-empty" style="<?= $_hasAwards ? 'display: none;' : 'display: block;' ?>">
    No achievements recorded yet.<?php if (!empty($canCreate)): ?> Click &ldquo;+ Add Achievement&rdquo; above to add one.<?php endif; ?>
  </div>
</section>
