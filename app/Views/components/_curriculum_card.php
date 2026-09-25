<?php
/**
 * =========================================================================
 * L'ÉCOLE — CURRICULUM CARD COMPONENT
 * =========================================================================
 * Renders a curriculum stage grouping card (e.g. Years 6–9, Years 10–11).
 * Expects:
 *   - $stage       : array ['range' => 'Years 6–9', 'description' => '...', 'subjects' => [...]]
 *   - $stageIndex  : int
 * =========================================================================
 */

$range    = $stage['range'] ?? 'Years 6–9';
$desc     = $stage['description'] ?? '';
$subjects = $stage['subjects'] ?? [];
$idx      = $stageIndex ?? 0;
?>

<article class="c-curriculum-card j-curriculum-card" data-range="<?= htmlspecialchars($range) ?>">
  <div class="c-curriculum-card__top">
    <div>
      <h3 class="c-curriculum-card__range"><?= htmlspecialchars($range) ?></h3>
      <?php if (!empty($desc)): ?>
        <p class="c-curriculum-card__desc"><?= htmlspecialchars($desc) ?></p>
      <?php endif; ?>
    </div>

    <div class="c-curriculum-card__badges">
      <span class="c-curriculum-card__count"><?= count($subjects) ?> Subjects</span>
      <button type="button" 
              class="c-curriculum-card__edit-btn j-edit-curriculum-btn" 
              data-range="<?= htmlspecialchars($range) ?>" 
              aria-label="Edit <?= htmlspecialchars($range) ?> subjects">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-edit"/>
        </svg>
      </button>
      <button type="button" 
              class="c-curriculum-card__edit-btn j-delete-curriculum-btn" 
              data-range="<?= htmlspecialchars($range) ?>" 
              aria-label="Delete <?= htmlspecialchars($range) ?>">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-trash"/>
        </svg>
      </button>
    </div>
  </div>

  <div class="c-curriculum-card__subjects j-curriculum-subjects">
    <?php foreach ($subjects as $sIndex => $subj): 
      $toneClass = 'c-subject-tone-' . ($sIndex % 5);
    ?>
      <span class="c-subject-chip <?= $toneClass ?>">
        <?= htmlspecialchars($subj) ?>
      </span>
    <?php endforeach; ?>
  </div>
</article>
