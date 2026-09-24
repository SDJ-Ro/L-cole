<?php
/**
 * =========================================================================
 * L'ÉCOLE — CANONICAL PROFILE HERO COMPONENT
 * =========================================================================
 * Shared hero banner component across all full profile views:
 *   - Admin, Teacher, Management, Parent, Student profile pages
 *   - Parent Child Profile page
 *
 * Expects:
 *   - $heroRole      : string ('admin' | 'student' | 'teacher' | 'parent' | 'management')
 *   - $heroName      : string
 *   - $heroEyebrow   : string (optional)
 *   - $heroSub       : string (optional)
 *   - $heroId        : string (optional, e.g. 'ADM-001' or 'S2021-091')
 *   - $heroStatus    : string (optional, default: 'ACTIVE')
 *   - $heroAvatar    : string (optional, image URL)
 *   - $heroInitials  : string (optional, fallback if no image avatar)
 *   - $heroEditable  : bool   (optional, default: false)
 *   - $heroEditBtnId : string (optional, default: 'j-profile-edit-btn')
 *   - $heroEditLabel : string (optional, default: 'Edit Profile')
 * =========================================================================
 */

$heroRole      = $heroRole      ?? 'student';
$heroName      = $heroName      ?? 'User';
$heroEyebrow   = $heroEyebrow   ?? '';
$heroSub       = $heroSub       ?? '';
$heroId        = $heroId        ?? '';
$heroStatus    = $heroStatus    ?? 'Active';
$heroAvatar    = $heroAvatar    ?? null;
$heroInitials  = $heroInitials  ?? '';
$heroEditable  = $heroEditable  ?? false;
$heroEditBtnId = $heroEditBtnId ?? 'j-profile-edit-btn';
$heroEditLabel = $heroEditLabel ?? 'Edit Profile';
?>

<div class="c-profile-hero" data-role="<?= htmlspecialchars($heroRole) ?>">
  <?php if (!empty($heroAvatar)): ?>
    <img class="c-profile-hero__avatar"
         src="<?= htmlspecialchars($heroAvatar) ?>"
         alt="<?= htmlspecialchars($heroName) ?>" />
  <?php else: ?>
    <div class="c-profile-hero__avatar c-profile-hero__avatar--initials" aria-hidden="true">
      <?= htmlspecialchars($heroInitials ?: strtoupper(substr($heroName, 0, 2))) ?>
    </div>
  <?php endif; ?>

  <div class="c-profile-hero__body">
    <?php if ($heroEyebrow): ?>
      <p class="c-profile-hero__eyebrow"><?= htmlspecialchars($heroEyebrow) ?></p>
    <?php endif; ?>
    <h1 class="c-profile-hero__name"><?= htmlspecialchars($heroName) ?></h1>
    <?php if ($heroSub): ?>
      <p class="c-profile-hero__sub"><?= htmlspecialchars($heroSub) ?></p>
    <?php endif; ?>
    <div class="c-profile-hero__badges">
      <?php if ($heroId): ?>
        <span class="c-profile-badge c-profile-badge--id"><?= htmlspecialchars($heroId) ?></span>
      <?php endif; ?>
      <?php if ($heroStatus): ?>
        <span class="c-profile-badge c-profile-badge--active"><?= htmlspecialchars(strtoupper($heroStatus)) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($heroEditable): ?>
    <button type="button" class="c-profile-hero__edit-btn" id="<?= htmlspecialchars($heroEditBtnId) ?>" data-editing="false">
      <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-edit"/>
      </svg>
      <?= htmlspecialchars($heroEditLabel) ?>
    </button>
  <?php endif; ?>
</div>
