<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR CARD ACHIEVEMENT CARD COMPONENT
 * =========================================================================
 * Reusable gallery card representing an individual award, trophy, or recognition.
 * 
 * Expects:
 *   - $achievement (array) Award record:
 *       ['id' => ..., 'title' => ..., 'year' => ..., 'level' => ..., 'kind' => ..., 'image' => ...]
 *   - $achievementIndex (int, optional) Zero-based index within the club awards list
 *   - $clubId (int, optional) Parent club identifier
 * =========================================================================
 */

$ach      = $achievement ?? [];
$achId    = $ach['id'] ?? ('ach-' . ($achievementIndex ?? 0));
$title    = $ach['title'] ?? 'Achievement';
$year     = $ach['year'] ?? date('Y');
$level    = $ach['level'] ?? 'Provincial';
$kind     = $ach['kind'] ?? 'Team';
$image    = $ach['image'] ?? null;
$place    = $ach['place'] ?? '';
$idx      = $achievementIndex ?? 0;
$cId      = $clubId ?? ($club['id'] ?? 0);
?>

<article class="c-achv-card j-achievement-card" 
         id="j-achv-card-<?= htmlspecialchars((string)$achId) ?>"
         data-ach-id="<?= htmlspecialchars((string)$achId) ?>"
         data-ach-index="<?= (int)$idx ?>"
         data-club-id="<?= (int)$cId ?>"
         role="button"
         tabindex="0"
         aria-label="View details for <?= htmlspecialchars($title) ?>">

  <!-- Cover Photo or Trophy Placeholder -->
  <?php if (!empty($image)): ?>
    <div class="c-achv-card__media">
      <img class="c-achv-card__img" src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy" />
    </div>
  <?php else: ?>
    <div class="c-achv-card__media-empty" aria-hidden="true">
      <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-trophy"/>
      </svg>
    </div>
  <?php endif; ?>

  <!-- Card Body -->
  <div class="c-achv-card__body">
    <div class="c-achv-card__row">
      <h3 class="c-achv-card__title"><?= htmlspecialchars($title) ?></h3>
      <span class="c-achv-card__year"><?= htmlspecialchars($year) ?></span>
    </div>

    <div class="c-achv-card__tags">
      <span class="c-achv-card__level"><?= htmlspecialchars($level) ?></span>
      <span class="c-achv-card__dot" aria-hidden="true">•</span>
      <span class="c-achv-card__kind"><?= htmlspecialchars($kind) ?></span>
    </div>
  </div>

</article>
