<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER ACHIEVEMENT STUDENT PROFILE CARD COMPONENT
 * =========================================================================
 * Reusable student card for Achievements tracking across teacher/faculty views.
 *
 * Parameters expected:
 *   - $student: array [
 *         'name'       => string,
 *         'index'      => string,
 *         'avatar'     => string|null (image URL or null),
 *         'initials'   => string|null,
 *         'hasIssue'   => bool (optional, displays warning badge & red avatar ring),
 *         'itemType'   => string ('Class' | 'Extracurricular'),
 *         'colorClass' => string|null (e.g. 'c-bg-card-slate')
 *     ]
 * =========================================================================
 */

$s = $student ?? [];
$name     = $s['name'] ?? 'Student Name';
$index    = $s['index'] ?? 'S0000/0000';
$avatar   = $s['avatar'] ?? null;
$showRedRing = isset($hasIssue) ? !empty($hasIssue) : (!empty($s['hasIssue']) || !empty($s['showRedRing']));
$itemType    = $s['itemType'] ?? 'Class';

// Fallback initials
$initials = $s['initials'] ?? '';
if (empty($initials)) {
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0] ?? 'S', 0, 1) . substr($parts[1] ?? '', 0, 1));
}

// 6-Color Mineral / Archival Palette
$mineralTones = [
    'c-bg-card-slate',
    'c-bg-card-steel',
    'c-bg-card-taupe',
    'c-bg-card-sand',
    'c-bg-card-seafoam',
    'c-bg-card-sage'
];

$toneClass = $s['colorClass'] ?? $mineralTones[abs(crc32($index . $name)) % count($mineralTones)];
$cardType  = $cardType ?? ($s['cardType'] ?? 'achievement');
$marksJson = isset($s['marks']) ? json_encode($s['marks']) : '{}';
$feedback  = $s['feedback'] ?? '';
$classTxt  = $s['class'] ?? 'Class 6-A';
?>

<article class="c-student-card <?= htmlspecialchars($toneClass) ?> <?= $showRedRing ? 'c-student-card--highlighted' : '' ?> j-student-achievement-card"
         data-student-name="<?= htmlspecialchars($name) ?>"
         data-student-index="<?= htmlspecialchars($index) ?>"
         data-student-class="<?= htmlspecialchars($classTxt) ?>"
         data-student-avatar="<?= htmlspecialchars($avatar ?? '') ?>"
         data-student-initials="<?= htmlspecialchars($initials) ?>"
         data-student-tone="<?= htmlspecialchars($toneClass) ?>"
         data-student-marks='<?= htmlspecialchars($marksJson, ENT_QUOTES, 'UTF-8') ?>'
         data-student-feedback="<?= htmlspecialchars($feedback) ?>"
         data-item-type="<?= htmlspecialchars($itemType) ?>">

  <!-- Popped-out Avatar -->
  <div class="c-student-card__avatar-wrap">
    <?php if (!empty($avatar)): ?>
      <img class="c-student-card__avatar" src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($name) ?>" />
    <?php else: ?>
      <div class="c-student-card__avatar-initials"><?= htmlspecialchars($initials) ?></div>
    <?php endif; ?>
  </div>

  <!-- Student Name & Index -->
  <h3 class="c-student-card__name"><?= htmlspecialchars($name) ?></h3>
  <p class="c-student-card__index"><?= htmlspecialchars($index) ?></p>

  <!-- Action Buttons -->
  <div class="c-student-card__actions">
    <?php if ($cardType === 'term_marks'): ?>
      <!-- Term Marks: Edit Marks Button -->
      <button type="button" class="c-student-btn c-student-btn--edit j-edit-marks" data-name="<?= htmlspecialchars($name) ?>" data-index="<?= htmlspecialchars($index) ?>" title="Edit student marks">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-pencil"/>
        </svg>
        <span>Edit Marks</span>
      </button>
    <?php else: ?>
      <!-- View Button: Whiter Frosted Glass Style -->
      <button type="button" class="c-student-btn c-student-btn--view j-view-student" data-name="<?= htmlspecialchars($name) ?>" data-index="<?= htmlspecialchars($index) ?>" title="View student achievements">
        <svg class="c-icon" width="14" height="14"><use href="#icon-eye"/></svg>
        <span>View</span>
      </button>

      <!-- Record Button: Signature Sunshine Accent -->
      <button type="button" class="c-student-btn c-student-btn--record j-record-student" data-name="<?= htmlspecialchars($name) ?>" data-index="<?= htmlspecialchars($index) ?>" title="Record new achievement">
        <svg class="c-icon" width="14" height="14"><use href="#icon-edit"/></svg>
        <span>Record</span>
      </button>
    <?php endif; ?>
  </div>
</article>
