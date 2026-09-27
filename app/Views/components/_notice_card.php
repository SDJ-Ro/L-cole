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
$nAuthorRole = strtolower($n['author_role'] ?? 'admin');
$nDate     = $n['date'] ?? 'TODAY';
$nPinned   = !empty($n['pinned']);
$targetClub = $n['target_club'] ?? null;
$targetClass = $n['target_class'] ?? null;
$nPublishAt = $n['publish_at'] ?? '';
$nExpiresAt = $n['expires_at'] ?? '';
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
         data-author-role="<?= htmlspecialchars($nAuthorRole) ?>"
         data-target-club="<?= htmlspecialchars($targetClub ?? '') ?>"
         data-target-class="<?= htmlspecialchars($targetClass ?? '') ?>"
         data-publish-at="<?= htmlspecialchars((string)$nPublishAt) ?>"
         data-expires-at="<?= htmlspecialchars((string)$nExpiresAt) ?>"
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
    
    <!-- Context Scope Badge -->
    <?php if (!empty($targetClub)): ?>
      <span class="c-tag c-tag--scope" data-scope="club">
        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $targetClub))) ?>
      </span>
    <?php elseif (!empty($targetClass)): ?>
      <span class="c-tag c-tag--scope" data-scope="class">
        <?= htmlspecialchars($targetClass) ?>
      </span>
    <?php else: ?>
      <span class="c-tag c-tag--scope" data-scope="school">
        School-Wide
      </span>
    <?php endif; ?>

    <?php if (!empty($nExpiresAt)): ?>
      <span class="c-tag c-tag--expiry" style="background: rgba(175, 80, 49, 0.12); color: #AF5031;">
        Expires <?= date('d M', strtotime($nExpiresAt)) ?>
      </span>
    <?php endif; ?>

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

  <!-- Optional File Attachment Pill -->
  <?php if (!empty($n['attachment_path'])): ?>
    <div class="c-notice-card__attachment" style="margin: 0.5rem 0 0.75rem;">
      <a href="<?= htmlspecialchars($n['attachment_path']) ?>" download="<?= htmlspecialchars($n['attachment_name'] ?? 'attachment') ?>" class="c-tag c-tag--attachment" target="_blank" style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; text-decoration: none; padding: 0.3rem 0.75rem; border-radius: 9999px; background: rgba(127, 199, 204, 0.2); color: #0F414A; font-weight: 600; border: 1px solid rgba(127, 199, 204, 0.4); transition: background-color 150ms ease;">
        <svg class="c-icon" width="13" height="13" style="color: #207C82;"><use href="#icon-paperclip"/></svg>
        <span><?= htmlspecialchars($n['attachment_name'] ?? 'Download Attachment') ?></span>
      </a>
    </div>
  <?php endif; ?>

  <!-- Card Footer -->
  <footer class="c-notice-card__footer">
    <div class="c-notice-card__author">
      <span class="c-avatar"><?= htmlspecialchars($initials) ?></span>
      <div>
        <p class="c-notice-card__author-name"><?= htmlspecialchars($nAuthor) ?></p>
        <p class="c-notice-card__date"><?= htmlspecialchars(strtoupper($nDate)) ?></p>
      </div>
    </div>

    <!-- Action Buttons (Admin / Management / Teacher authority matrix) -->
    <?php
    $userRole = strtolower($currentRole ?? ($_SESSION['user']['role'] ?? 'admin'));
    $userId   = (int)($_SESSION['user']['id'] ?? 1);
    $nAuthorAccountId = !empty($n['author_account_id']) ? (int)$n['author_account_id'] : null;

    $canTogglePin = ($userRole === 'admin')
        || ($userRole === 'management' && (!($nPinned && $nAuthorRole === 'admin')))
        || ($userRole === 'teacher' && $nAuthorAccountId === $userId);

    $canEditOrDelete = ($userRole === 'admin')
        || ($userRole === 'management' && $nAuthorRole !== 'admin')
        || ($userRole === 'teacher' && $nAuthorAccountId === $userId);
    ?>

    <?php if ($actions && ($canTogglePin || $canEditOrDelete)): ?>
      <div class="c-notice-card__actions">
        <?php if ($canTogglePin): ?>
          <button type="button" class="c-icon-btn j-notice-pin" data-notice-id="<?= htmlspecialchars((string)$nId) ?>" aria-label="<?= $nPinned ? 'Unpin notice' : 'Pin notice' ?>">
            <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="<?= $nPinned ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#<?= $nPinned ? 'icon-pinFilled' : 'icon-pin' ?>"/>
            </svg>
          </button>
        <?php endif; ?>

        <?php if ($canEditOrDelete): ?>
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
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </footer>

</article>
