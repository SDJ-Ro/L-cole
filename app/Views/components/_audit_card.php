<?php
/**
 * =========================================================================
 * L'ÉCOLE — AUDIT LOG CARD COMPONENT
 * =========================================================================
 * Reusable card representing an individual audit event record.
 * 
 * Expects:
 *   - $log: array [
 *       'id'            => string|int (required)
 *       'role'          => string ('Management' | 'Teacher' | 'Parent' | 'System')
 *       'action'        => string ('Mark Edit', 'Notice Posted', etc.)
 *       'actor'         => string (e.g. 'Alex Thompson')
 *       'avatar'        => string (e.g. 'AT')
 *       'actorRole'     => string (e.g. 'Management account')
 *       'details'       => string (activity description)
 *       'date'          => string (e.g. '2024-06-15')
 *       'time'          => string (e.g. '09:42:11')
 *       'linkedStudent' => string|null (optional linked student string)
 *     ]
 * =========================================================================
 */

$l             = $log ?? ($item ?? []);
$id            = $l['id'] ?? '';
$role          = $l['role'] ?? 'System';
$action        = $l['action'] ?? '';
$actor         = $l['actor'] ?? '';
$avatar        = $l['avatar'] ?? (!empty($actor) ? strtoupper(substr($actor, 0, 2)) : 'SYS');
$actorRole     = $l['actorRole'] ?? ($role . ' account');
$details       = $l['details'] ?? '';
$date          = $l['date'] ?? '';
$time          = $l['time'] ?? '';
$ip            = $l['ip'] ?? '';
$linkedStudent = $l['linkedStudent'] ?? null;

$cleanRole = strtolower(trim((string)$role));
if (str_contains($cleanRole, 'admin')) {
    $themeClass  = 'c-theme-admin';
    $roleDisplay = 'Admin';
} elseif (str_contains($cleanRole, 'student') || str_contains($cleanRole, 'stu')) {
    $themeClass  = 'c-theme-student';
    $roleDisplay = 'Student';
} elseif (str_contains($cleanRole, 'manage') || str_contains($cleanRole, 'mgmt')) {
    $themeClass  = 'c-theme-management';
    $roleDisplay = 'Management';
} elseif (str_contains($cleanRole, 'teach') || str_contains($cleanRole, 'staff')) {
    $themeClass  = 'c-theme-teacher';
    $roleDisplay = 'Teacher';
} elseif (str_contains($cleanRole, 'parent') || str_contains($cleanRole, 'guard')) {
    $themeClass  = 'c-theme-parent';
    $roleDisplay = 'Parent';
} else {
    $themeClass  = 'c-theme-system';
    $roleDisplay = 'System';
}

$actorRole  = $l['actorRole'] ?? ($roleDisplay . ' account');
$searchData = strtolower(trim("{$actor} {$action} {$details} {$ip} {$roleDisplay} " . ($linkedStudent ?? '')));
?>

<article class="c-log-card <?= htmlspecialchars($themeClass) ?> j-log-card" 
         data-role="<?= htmlspecialchars($roleDisplay) ?>" 
         data-action="<?= htmlspecialchars($action) ?>"
         data-search="<?= htmlspecialchars($searchData) ?>">
  <div class="c-log-card__row">
    
    <!-- Actor Profile (Avatar, Name Pill, Role Subtitle) -->
    <div class="c-log-card__actor">
      <div class="c-log-card__avatar"><?= htmlspecialchars($avatar) ?></div>
      <div class="c-log-card__actor-text">
        <span class="c-tag c-log-card__actor-pill"><?= htmlspecialchars($actor) ?></span>
        <span class="c-log-card__actor-role"><?= htmlspecialchars($actorRole) ?></span>
      </div>
    </div>

    <!-- Activity Body (Action Pill, Details, Timestamp) -->
    <div class="c-log-card__body">
      <span class="c-tag c-tone-tan"><?= htmlspecialchars($action) ?></span>
      <p class="c-log-card__details"><?= htmlspecialchars($details) ?></p>
      <div class="c-log-card__time">
        <p class="c-log-card__date"><?= htmlspecialchars($date) ?></p>
        <p class="c-log-card__clock"><?= htmlspecialchars($time) ?></p>
      </div>
    </div>

  </div>
</article>
