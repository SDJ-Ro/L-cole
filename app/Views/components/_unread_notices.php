<?php
// MVC/app/Views/components/_unread_notices.php
// Reusable Unread Notices Card Component
// Expects:
//   - $notices: array of ['title' => ..., 'desc' => ...]
//   - $noticesTitle: string (optional, defaults to 'Unread Notices')
//   - $noticesPill: string (optional)

$title     = $noticesTitle ?? 'Unread Notices';
$list      = $notices ?? [];
$count     = count($list);
$pill      = $noticesPill ?? ($count . ' ' . ($count === 1 ? 'NOTICE' : 'NOTICES'));
$cardItems = array_slice($list, 0, 2);
?>
<section class="c-panel c-notices-card">
  <div class="c-notices-card__head">
    <h2 class="c-notices-card__title">
      <span class="c-notices-card__bell" aria-hidden="true">
        <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-bell"/>
        </svg>
      </span>
      <span><?= htmlspecialchars($title) ?></span>
    </h2>
    <div class="c-notices-card__actions">
      <span class="c-notices-card__pill"><?= htmlspecialchars($pill) ?></span>
      <button type="button" class="c-notices-card__view-all j-notices-view-all" data-modal-open="j-notices-modal" aria-label="View all notices">VIEW ALL</button>
    </div>
  </div>

  <div class="c-notices-card__list">
    <?php if (!empty($cardItems)): ?>
      <?php foreach ($cardItems as $notice): ?>
        <article class="c-notices-card__item">
          <h3 class="c-notices-card__item-title"><?= htmlspecialchars($notice['title'] ?? '') ?></h3>
          <p class="c-notices-card__item-text"><?= htmlspecialchars($notice['desc'] ?? '') ?></p>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($count <= 1): ?>
      <div class="c-notices-card__empty">
        <h3 class="c-notices-card__empty-title">No more Notices</h3>
        <p class="c-notices-card__empty-text">You have caught up with all unread announcements.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Unread Notices Modal -->
<div class="c-notices-modal-layer c-modal-layer" id="j-notices-modal" role="dialog" aria-modal="true" aria-labelledby="j-notices-modal-title" hidden>
  <div class="c-notices-modal-backdrop j-modal-close j-notices-modal-close" tabindex="-1"></div>
  <div class="c-notices-modal">
    <div class="c-notices-modal__header">
      <div class="c-notices-modal__title-group">
        <span class="c-notices-card__bell" aria-hidden="true">
          <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-bell"/>
          </svg>
        </span>
        <h3 class="c-notices-modal__title" id="j-notices-modal-title"><?= htmlspecialchars($title) ?></h3>
        <span class="c-notices-card__pill"><?= htmlspecialchars($pill) ?></span>
      </div>
      <button type="button" class="c-notices-modal__close-btn j-modal-close j-notices-modal-close" aria-label="Close notices modal">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-x"/>
        </svg>
      </button>
    </div>

    <div class="c-notices-modal__body">
      <div class="c-notices-card__list">
        <?php if (!empty($list)): ?>
          <?php foreach ($list as $notice): ?>
            <article class="c-notices-card__item">
              <h4 class="c-notices-card__item-title"><?= htmlspecialchars($notice['title'] ?? '') ?></h4>
              <p class="c-notices-card__item-text"><?= htmlspecialchars($notice['desc'] ?? '') ?></p>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align: center; color: rgba(15, 65, 74, 0.6); padding: 1.5rem 0;">No unread notices at this time.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
