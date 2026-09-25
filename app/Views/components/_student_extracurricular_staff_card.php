<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT EXTRACURRICULAR CARD COMPONENT
 * =========================================================================
 * Reusable card template for student extracurricular activities.
 * Supports:
 *   1. Staff Card Mode (Teacher in Charge, Coach, Instructor)
 *      with avatar, name, specialty/subject, email, and phone.
 *   2. Info Card Mode (Details, Status)
 *      with title and list of icon + label + value items.
 *
 * Expects for Staff Mode:
 *   - $staffRole      : string
 *   - $staffName      : string
 *   - $staffSpecialty : string
 *   - $staffAvatar    : string
 *   - $staffEmail     : string
 *   - $staffPhone     : string
 *   - $staffIdPrefix  : string (optional: 'tic' or 'coach')
 *
 * Expects for Info Mode:
 *   - $cardType       : 'info'
 *   - $cardTitle      : string (e.g. 'Details', 'Status')
 *   - $infoItems      : array of [ 'icon' => string, 'label' => string, 'value' => string, 'id' => string ]
 * =========================================================================
 */

$isInfoMode = isset($cardType) && $cardType === 'info';

if ($isInfoMode):
    $title = $cardTitle ?? 'Info';
    $items = $infoItems ?? [];
?>
<div class="c-staff-card-box">
  <article class="c-staff-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
    <div class="c-staff-card__header">
      <h3 class="c-staff-card__role"><?= htmlspecialchars($title) ?></h3>
    </div>
    <div class="c-staff-card__info-items" style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 0.25rem;">
      <?php foreach ($items as $item): ?>
        <div style="display: flex; gap: 0.625rem; align-items: center;">
          <span class="j-ex-56" style="width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
            <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#<?= htmlspecialchars($item['icon']) ?>"/></svg>
          </span>
          <div class="j-ex-42">
            <p class="j-ex-57" style="margin: 0;"><?= htmlspecialchars($item['label']) ?></p>
            <p class="j-ex-58" <?= !empty($item['id']) ? 'id="' . htmlspecialchars($item['id']) . '"' : '' ?> style="margin: 0.15rem 0 0;"><?= htmlspecialchars($item['value']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</div>
<?php else:
    $roleTitle = $staffRole ?? 'Staff Member';
    $name      = $staffName ?? '';
    $specialty = $staffSpecialty ?? '';
    $avatar    = !empty($staffAvatar) ? $staffAvatar : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=faces';
    $email     = $staffEmail ?? 'staff@lecole.edu';
    $phone     = $staffPhone ?? '+94 77 123 4567';
    $prefix    = $staffIdPrefix ?? '';
?>
<div class="c-staff-card-box">
  <article class="c-staff-card">
    <div class="c-staff-card__header">
      <h3 class="c-staff-card__role"><?= htmlspecialchars($roleTitle) ?></h3>
    </div>
    <div class="c-staff-card__person">
      <img <?= $prefix ? 'id="j-detail-' . htmlspecialchars($prefix) . '-avatar"' : '' ?> src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($name) ?>" />
      <div>
        <p class="c-staff-card__name" <?= $prefix ? 'id="j-detail-' . htmlspecialchars($prefix) . '-name"' : '' ?>><?= htmlspecialchars($name) ?></p>
        <p class="c-staff-card__specialty" <?= $prefix ? 'id="j-detail-' . htmlspecialchars($prefix) . '-specialty"' : '' ?>><?= htmlspecialchars($specialty) ?></p>
      </div>
    </div>
    <div class="c-staff-card__contact">
      <p class="c-staff-card__contact-row">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-mail"/></svg>
        <span <?= $prefix ? 'id="j-detail-' . htmlspecialchars($prefix) . '-email"' : '' ?>><?= htmlspecialchars($email) ?></span>
      </p>
      <p class="c-staff-card__contact-row">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
        <span <?= $prefix ? 'id="j-detail-' . htmlspecialchars($prefix) . '-phone"' : '' ?>><?= htmlspecialchars($phone) ?></span>
      </p>
    </div>
  </article>
</div>
<?php endif; ?>
