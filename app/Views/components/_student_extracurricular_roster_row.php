<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT EXTRACURRICULAR ROSTER ROW COMPONENT
 * =========================================================================
 * Renders an individual player row in the team roster card.
 * Displays player initials avatar, name, grade, uppercase role, and highlights
 * the currently signed-in student with a warm background and a "YOU" badge.
 *
 * Expects:
 *   - $member      : array { name, grade, role|position }
 *   - $currentUser : string (default 'Jason Perera')
 * =========================================================================
 */

$m           = $member ?? [];
$userName    = $currentUser ?? 'Jason Perera';
$name        = $m['name'] ?? 'Player';
$grade       = $m['grade'] ?? '';
$role        = $m['role'] ?? ($m['position'] ?? 'Player');
$isYou       = (strcasecmp(trim($name), trim($userName)) === 0);

// Initials (up to 2 characters)
$cleanName = trim($name);
$words = preg_split('/\s+/', $cleanName);
if (count($words) >= 2) {
    $initials = strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1));
} else {
    $initials = strtoupper(mb_substr($cleanName, 0, 2));
}
?>

<div class="sc-roster-item<?= $isYou ? ' sc-roster-item-you' : '' ?>">
  <span class="sc-roster-avatar"><?= htmlspecialchars($initials) ?></span>
  <div class="sc-roster-info">
    <div class="sc-roster-name-row">
      <span class="sc-roster-name"><?= htmlspecialchars($name) ?></span>
      <?php if ($isYou): ?>
        <span class="sc-roster-you-tag">YOU</span>
      <?php endif; ?>
    </div>
    <?php if (!empty($grade)): ?>
      <div class="sc-roster-grade"><?= htmlspecialchars($grade) ?></div>
    <?php endif; ?>
  </div>
  <div class="sc-roster-role"><?= htmlspecialchars($role) ?></div>
</div>
