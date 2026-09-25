<?php
/**
 * =========================================================================
 * L'ÉCOLE — CHARACTER CERTIFICATE VERTICAL CAPSULE PROFILE CARD
 * =========================================================================
 * Vertical capsule card with the 6 solid mineral colors from teacher term marks,
 * white typography, 112px circular avatar wrap, bold initials, and red pending dot.
 * =========================================================================
 */

$c = $certificate ?? [];
$id          = $c['id'] ?? '';
$name        = $c['name'] ?? 'Student Name';
$cohort      = $c['cohort'] ?? '';
$reason      = $c['reason'] ?? 'Graduating student';
$status      = $c['status'] ?? 'Pending review';
$avatar      = $c['avatar'] ?? null;
$requestedOn = $c['requestedOn'] ?? '';

// Check pending requests
$missingReqs = $c['missingRecordRequests'] ?? [];
$certReqs    = $c['certificateRequests'] ?? [];
$pendingMissingCount = count(array_filter($missingReqs, fn($r) => ($r['status'] ?? '') === 'Pending'));
$pendingCertCount    = count(array_filter($certReqs, fn($r) => ($r['status'] ?? '') === 'Pending'));
$totalPendingRequests = $pendingMissingCount + $pendingCertCount;

// Pending condition: has pending requests or status is 'Pending review'
$hasPending = ($totalPendingRequests > 0) || ($status === 'Pending review');

// Fallback Initials (e.g. NP, AF, SJ, EW)
$initials = '';
$nameParts = explode(' ', trim($name));
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
} else {
    $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 2));
}

// 6 Solid Mineral Colors (matching teacher term marks & achievement cards)
$mineralClasses = [
    'c-bg-card-slate',
    'c-bg-card-steel',
    'c-bg-card-taupe',
    'c-bg-card-sand',
    'c-bg-card-seafoam',
    'c-bg-card-sage'
];
$colorClass = $c['colorClass'] ?? $mineralClasses[abs(crc32($id . $name)) % count($mineralClasses)];

// Extract clean cohort (e.g. "Grade 13-A")
$cohortParts = explode('·', $cohort);
$cohortShort = trim($cohortParts[0] ?? $cohort);
?>

<button type="button" 
        class="cert-card <?= htmlspecialchars($colorClass) ?> j-cert-card" 
        data-open-cert="<?= htmlspecialchars($id) ?>" 
        data-id="<?= htmlspecialchars($id) ?>"
        data-name="<?= htmlspecialchars(strtolower($name)) ?>"
        data-index="<?= htmlspecialchars(strtolower($id)) ?>"
        data-cohort="<?= htmlspecialchars(strtolower($cohort)) ?>"
        data-status="<?= htmlspecialchars(strtolower($status)) ?>"
        data-reason="<?= htmlspecialchars(strtolower($reason)) ?>"
        data-has-pending="<?= $hasPending ? 'true' : 'false' ?>"
        aria-label="Open <?= htmlspecialchars($name) ?>'s character certificate">
  
  <!-- Outer Avatar Wrap (112px round with padding & pending tint) -->
  <span class="cert-avatar-wrap <?= $hasPending ? 'has-pending' : '' ?>">
    <?php if (!empty($avatar)): ?>
      <img src="<?= htmlspecialchars($avatar) ?>" alt="Portrait of <?= htmlspecialchars($name) ?>" />
    <?php else: ?>
      <span class="cert-avatar-initials"><?= htmlspecialchars($initials) ?></span>
    <?php endif; ?>

    <?php if ($hasPending): ?>
      <span class="cert-pending-dot" title="Pending student request"></span>
    <?php endif; ?>
  </span>

  <!-- Student Name, ID, Cohort with White Typography -->
  <h3><?= htmlspecialchars($name) ?></h3>
  <p><?= htmlspecialchars($id) ?></p>
  <p><?= htmlspecialchars($cohortShort) ?></p>
</button>
