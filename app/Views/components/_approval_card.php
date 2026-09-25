<?php
/**
 * =========================================================================
 * L'ÉCOLE — APPROVAL & VERIFICATION CARD COMPONENT
 * =========================================================================
 * Reusable card for Teacher Accounts, Extracurriculars, and Notice queues.
 * 
 * Expects:
 *   - $item: array [
 *       'id'          => string|int (required)
 *       'type'        => string ('Teachers' | 'Extracurriculars' | 'Notices')
 *       'title'       => string (required)
 *       'tag'         => string (e.g. 'New Teacher', 'New Club', 'New Sport')
 *       'categoryTag' => string (optional, e.g. 'Academic', 'Administrative')
 *       'color'       => string ('sky' | 'sunshine' | 'terracotta')
 *       'meta'        => string (optional, e.g. subject, TIC, audience)
 *       'description' => string (optional snippet)
 *       'author'      => string (e.g. 'Management Panel', 'Mr. Weerasinghe')
 *       'date'        => string (e.g. 'Oct 24, 2024')
 *       'status'      => string ('Pending' | 'Approved' | 'Rejected')
 *       'feedback'    => string (optional feedback note)
 *     ]
 * =========================================================================
 */

$i           = $item ?? [];
$id          = $i['id'] ?? '';
$type        = $i['type'] ?? 'Teachers';
$title       = $i['title'] ?? '';
$tag         = $i['tag'] ?? 'Submission';
$categoryTag = $i['categoryTag'] ?? '';
$color       = $i['color'] ?? 'sky';
$meta        = $i['meta'] ?? '';
$description = $i['description'] ?? '';
$author      = $i['author'] ?? 'Management Panel';
$date        = $i['date'] ?? 'Today';
$status      = $i['status'] ?? 'Pending';
$feedback    = $i['feedback'] ?? '';
?>

<article class="c-approval-card c-approval-card--<?= htmlspecialchars($color) ?> j-approval-card" 
         data-item-type="<?= htmlspecialchars($type) ?>" 
         data-item-id="<?= htmlspecialchars((string)$id) ?>" 
         data-item-status="<?= htmlspecialchars($status) ?>" 
         data-item-title="<?= htmlspecialchars($title) ?>" 
         data-item-author="<?= htmlspecialchars($author) ?>">

  <!-- Card Heading & Status Badge -->
  <div class="c-approval-card__top">
    <div class="c-approval-card__heading">
      <?php if (!empty($categoryTag)): ?>
        <div class="c-approval-card__badges">
          <span class="c-approval-card__tag c-approval-card__tag--<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($tag) ?></span>
          <span class="c-approval-card__tag c-approval-card__tag--neutral"><?= htmlspecialchars($categoryTag) ?></span>
        </div>
      <?php else: ?>
        <span class="c-approval-card__tag c-approval-card__tag--<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($tag) ?></span>
      <?php endif; ?>

      <h3 class="c-approval-card__title <?= !empty($categoryTag) ? 'c-approval-card__title--tight' : '' ?> c-font-display"><?= htmlspecialchars($title) ?></h3>
      
      <?php if (!empty($meta) && $type !== 'Notices'): ?>
        <p class="c-approval-card__meta"><?= htmlspecialchars($meta) ?></p>
      <?php endif; ?>
    </div>
    <span class="c-status-badge c-status-badge--<?= strtolower(htmlspecialchars($status)) ?> j-status-badge"><?= htmlspecialchars($status) ?></span>
  </div>

  <!-- Optional Description Snippet -->
  <?php if (!empty($description)): ?>
    <p class="c-approval-card__description c-approval-card__description--clamp-3"><?= htmlspecialchars($description) ?></p>
  <?php endif; ?>

  <!-- Creator Info & Date -->
  <div class="c-approval-card__info">
    <div class="c-approval-card__info-row">
      <p class="c-approval-card__info-text"><?= ($type === 'Notices') ? 'Author:' : 'Created by:' ?> <span class="c-approval-card__info-strong"><?= htmlspecialchars($author) ?></span></p>
      <p class="c-approval-card__info-date"><?= htmlspecialchars($date) ?></p>
    </div>
    <?php if ($type === 'Notices' && !empty($meta)): ?>
      <p class="c-approval-card__audience"><?= htmlspecialchars($meta) ?></p>
    <?php endif; ?>
  </div>

  <!-- Rejection Feedback Note (Hidden by default, shown upon rejection) -->
  <div class="c-feedback-note j-feedback-note" <?= empty($feedback) ? 'hidden' : '' ?>>
    <svg class="c-icon c-feedback-note__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <use href="#icon-message"/>
    </svg>
    <div>
      <p class="c-feedback-note__author">Feedback sent to <span class="j-feedback-author"><?= htmlspecialchars($author) ?></span></p>
      <p class="c-feedback-note__text j-feedback-text"><?= htmlspecialchars($feedback) ?></p>
    </div>
  </div>

  <!-- Action Buttons (Reject / Approve) -->
  <div class="c-approval-card__actions j-approval-actions" <?= $status !== 'Pending' ? 'hidden' : '' ?>>
    <button type="button" class="c-btn c-btn--reject j-reject-btn">
      <svg class="c-icon" width="14" height="14"><use href="#icon-close"/></svg>
      Reject
    </button>
    <button type="button" class="c-btn c-btn--approve j-approve-btn">
      <svg class="c-icon" width="14" height="14"><use href="#icon-check"/></svg>
      Approve
    </button>
  </div>

</article>
