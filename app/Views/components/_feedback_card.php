<?php
/**
 * =========================================================================
 * L'ÉCOLE — UNIVERSAL FEEDBACK CARD & DETAIL MODAL COMPONENT
 * =========================================================================
 * Master feedback card & exact popup detail view modal for Parent & Teacher:
 *   - Solid Highlight Badges (Original Moss, Sunshine, Maroon)
 *   - Faded tinted footer strip with "Read Full Feedback" CTA & chevron
 *   - NO top horizontal colored border line
 *   - When clicked, opens cleanly in a centered popup overlay modal
 * =========================================================================
 */

$item = $card ?? $feedbackItem ?? [];
$id          = $item['id'] ?? uniqid();
$type        = $item['type'] ?? 'Positive';
$subject     = $item['subject'] ?? '';
$teacher     = $item['teacher'] ?? '';
$teacherRole = $item['teacherRole'] ?? '';
$student     = $item['student'] ?? '';
$studentId   = $item['studentId'] ?? '';
$parent      = $item['parent'] ?? '';
$date        = $item['date'] ?? '';
$preview     = $item['preview'] ?? '';
$fullText    = $item['fullText'] ?? $preview;

$typeNormalized = strtolower(trim($type));
if (!in_array($typeNormalized, ['positive', 'constructive', 'negative'])) {
    $typeNormalized = 'positive';
}
?>

<article class="c-feedback-card c-feedback-card--<?= htmlspecialchars($typeNormalized) ?> j-feedback-card"
         data-feedback-id="<?= htmlspecialchars($id) ?>"
         data-type="<?= htmlspecialchars($type) ?>"
         data-subject="<?= htmlspecialchars($subject) ?>"
         data-teacher="<?= htmlspecialchars($teacher) ?>"
         data-teacher-role="<?= htmlspecialchars($teacherRole) ?>"
         data-student="<?= htmlspecialchars($student) ?>"
         data-student-id="<?= htmlspecialchars($studentId) ?>"
         data-parent="<?= htmlspecialchars($parent) ?>"
         data-date="<?= htmlspecialchars($date) ?>"
         data-full-text="<?= htmlspecialchars($fullText) ?>">

  <div class="c-feedback-card__body">
    <div class="c-feedback-card__top">
      <!-- Original Solid Highlight Tab Badge -->
      <span class="c-type-badge c-type-badge--<?= htmlspecialchars($typeNormalized) ?>">
        <?php if ($typeNormalized === 'positive'): ?>
          <svg class="c-type-badge__icon" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><use href="#icon-star"/></svg>
        <?php elseif ($typeNormalized === 'constructive'): ?>
          <svg class="c-type-badge__icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-trendingUp"/></svg>
        <?php else: ?>
          <svg class="c-type-badge__icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <use href="#icon-alertCircle" />
          </svg>
        <?php endif; ?>
        <span><?= htmlspecialchars(ucfirst($type)) ?></span>
      </span>
      <span class="c-feedback-card__date"><?= htmlspecialchars($date) ?></span>
    </div>

    <?php if (!empty($parent)): ?>
      <p class="c-feedback-card__parent-line">
        PARENT: <?= htmlspecialchars($parent) ?><?php if (!empty($student)): ?> | PARENT OF <?= htmlspecialchars($student) ?><?php endif; ?>
      </p>
    <?php endif; ?>

    <div class="c-feedback-card__header-info">
      <h3 class="c-feedback-card__subject c-font-display"><?= htmlspecialchars($subject) ?></h3>
      <?php if (!empty($teacher)): ?>
        <p class="c-feedback-card__teacher-meta">
          <span class="c-feedback-card__teacher-name"><?= htmlspecialchars($teacher) ?></span>
          <?php if (!empty($teacherRole)): ?>
            <span class="c-feedback-card__teacher-sep">•</span>
            <span class="c-feedback-card__teacher-role"><?= htmlspecialchars($teacherRole) ?></span>
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>

    <p class="c-feedback-card__preview">“<?= htmlspecialchars($preview) ?>”</p>
  </div>

  <!-- Faded tinted footer matching respective category color -->
  <div class="c-feedback-card__footer c-feedback-card__footer--<?= htmlspecialchars($typeNormalized) ?>">
    <div class="c-read-more">
      <span>Read Full Feedback</span>
      <svg class="c-icon c-read-more__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <use href="#icon-chevronRight" />
      </svg>
    </div>
  </div>
</article>
