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

$themeClass = 'c-theme-' . strtolower($role);
$searchData = strtolower(trim("{$actor} {$action} {$details} {$ip} " . ($linkedStudent ?? '')));
?>

<article class="c-log-card <?= htmlspecialchars($themeClass) ?> j-log-card" 
         data-role="<?= htmlspecialchars($role) ?>" 
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
