<?php
/**
 * =========================================================================
 * L'ÉCOLE — NOTICE CARD COMPONENT
 * =========================================================================
 * Exact 1:1 port from Admin Notice card structure.
 * 
 * Expects:
 *   - $notice: array [
 *       'id'       => string|int (required)
 *       'title'    => string (required)
 *       'body'     => string (required)
 *       'category' => string ('Academic' | 'Extracurricular' | 'General' | 'Administrative' | 'Urgent')
 *       'audience' => array of strings (e.g. ['Students', 'Teachers'])
 *       'author'   => string (e.g. 'Dr. Robert Vance')
 *       'date'     => string (e.g. '18 SEP 2026')
 *       'pinned'   => bool (optional)
 *     ]
 *   - $showActions: bool (optional, defaults to true) Shows pin/edit/delete buttons
 *   - $cardIndex  : int (optional, for animation delay)
 * =========================================================================
 */

$n         = $notice ?? [];
$nId       = $n['id'] ?? '';
$nTitle    = $n['title'] ?? '';
$nBody     = $n['body'] ?? '';
$nCat      = $n['category'] ?? 'General';
$nAudience = (array)($n['audience'] ?? ['All users']);
$nAuthor   = $n['author'] ?? 'Admin Office';
$nDate     = $n['date'] ?? 'TODAY';
$nPinned   = !empty($n['pinned']);
$actions   = $showActions ?? true;
$delay     = isset($cardIndex) ? ($cardIndex * 40) : 0;

// Author initials
$parts    = explode(' ', trim($nAuthor));
$initials = '';
foreach ($parts as $p) {
    if (!empty($p)) $initials .= mb_substr($p, 0, 1);
}
$initials = mb_substr($initials, 0, 2);
?>

<article class="c-notice-card" 
         data-category="<?= htmlspecialchars($nCat) ?>" 
         data-audience="<?= htmlspecialchars(strtolower(implode(',', $nAudience))) ?>" 
         data-notice-id="<?= htmlspecialchars((string)$nId) ?>" 
         data-pinned="<?= $nPinned ? 'true' : 'false' ?>"
         style="animation-delay: <?= (int)$delay ?>ms" 
         tabindex="0">

  <!-- Pin Indicator Badge -->
  <?php if ($nPinned && empty($hidePinBadge)): ?>
    <span class="c-notice-card__pin" aria-label="Pinned notice">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-pinFilled"/>
      </svg>
    </span>
  <?php endif; ?>

  <!-- Tags Row -->
  <div class="c-notice-card__tags">
    <span class="c-tag c-tag--category"><?= htmlspecialchars($nCat) ?></span>
    <?php foreach ($nAudience as $aud): ?>
      <?php
      $audL = strtolower(trim($aud));
      $roleAttr = match(true) {
          str_contains($audL, 'student')    => 'students',
          str_contains($audL, 'teacher')    => 'teachers',
          str_contains($audL, 'parent')     => 'parents',
          str_contains($audL, 'management') => 'management',
          default => ''
      };
      ?>
      <span class="c-tag c-tag--audience" data-role="<?= $roleAttr ?>"><?= htmlspecialchars($aud) ?></span>
    <?php endforeach; ?>
  </div>

  <!-- Title & Body Snippet -->
  <h2 class="c-notice-card__title"><?= htmlspecialchars($nTitle) ?></h2>
  <p class="c-notice-card__body"><?= htmlspecialchars($nBody) ?></p>

  <!-- Card Footer -->
  <footer class="c-notice-card__footer">
    <div class="c-notice-card__author">
      <span class="c-avatar"><?= htmlspecialchars($initials) ?></span>
      <div>
        <p class="c-notice-card__author-name"><?= htmlspecialchars($nAuthor) ?></p>
        <p class="c-notice-card__date"><?= htmlspecialchars(strtoupper($nDate)) ?></p>
      </div>
    </div>

    <!-- Action Buttons (Admin / Teacher view) -->
    <?php if ($actions): ?>
      <div class="c-notice-card__actions">
        <button type="button" class="c-icon-btn j-notice-pin" data-notice-id="<?= htmlspecialchars((string)$nId) ?>" aria-label="<?= $nPinned ? 'Unpin notice' : 'Pin notice' ?>">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="<?= $nPinned ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#<?= $nPinned ? 'icon-pinFilled' : 'icon-pin' ?>"/>
          </svg>
        </button>
        <button type="button" class="c-icon-btn j-notice-edit" data-notice-id="<?= htmlspecialchars((string)$nId) ?>" aria-label="Edit notice">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-edit"/>
          </svg>
        </button>
        <button type="button" class="c-icon-btn c-icon-btn--danger j-notice-delete" data-notice-id="<?= htmlspecialchars((string)$nId) ?>" aria-label="Delete notice">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-trash"/>
          </svg>
        </button>
      </div>
    <?php endif; ?>
  </footer>

</article>
