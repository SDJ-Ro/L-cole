<?php
// MVC/app/Views/components/_achievements_timeline.php
// Reusable Achievements Timeline Component
// Expects: $achievements (array, optional), $showFilters (bool, optional), $timelineTitle (string, optional)

use App\Models\AchievementModel;

if (!class_exists('App\Models\AchievementModel') && file_exists(__DIR__ . '/../Models/AchievementModel.php')) {
    require_once __DIR__ . '/../Models/AchievementModel.php';
}

$achievements  = $achievements ?? AchievementModel::getAll();
$showFilters   = $showFilters ?? true;
$timelineTitle = $timelineTitle ?? 'Full Timeline';

// Group items by year
$groupedByYear = [];
foreach ($achievements as $ach) {
    $year = $ach['year'] ?? (isset($ach['date']) ? (int)substr($ach['date'], -4) : date('Y'));
    $groupedByYear[$year][] = $ach;
}
krsort($groupedByYear);
?>

<div class="ach-timeline-container" id="j-ach-timeline-container">
  <?php if ($showFilters): ?>
    <div class="ach-filters-row">
      <div class="ach-filters" id="ach-filters">
        <button class="ach-filter is-active" data-cat="all" type="button">All</button>
        <button class="ach-filter" data-cat="academic" type="button">Academic</button>
        <button class="ach-filter" data-cat="sports" type="button">Sports</button>
        <button class="ach-filter" data-cat="clubs" type="button">Clubs</button>
        <button class="ach-filter" data-cat="leadership" type="button">Leadership</button>
      </div>
    </div>
  <?php endif; ?>

  <div class="ach-timeline" id="ach-timeline">
    <?php foreach ($groupedByYear as $year => $items): ?>
      <div class="ach-year-group" data-year="<?= htmlspecialchars($year) ?>">
        <div class="ach-year-label-wrap">
          <span class="ach-year-label"><?= htmlspecialchars($year) ?></span>
        </div>
        <div class="ach-year-items">
          <?php foreach ($items as $idx => $item): 
            $cat = strtolower($item['category'] ?? 'academic');
            $catLabel = strtoupper($item['categoryLabel'] ?? $item['category'] ?? 'Academic');
          ?>
            <div class="ach-timeline-item" data-category="<?= htmlspecialchars($cat) ?>">
              <div class="ach-timeline-marker">
                <div class="ach-timeline-dot ach-dot-<?= htmlspecialchars($cat) ?>"></div>
                <div class="ach-timeline-line"></div>
              </div>

              <div class="ach-timeline-card">
                <div class="ach-timeline-header">
                  <span class="ach-timeline-title"><?= htmlspecialchars($item['title'] ?? '') ?></span>
                  <span class="ach-badge ach-badge-<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($catLabel) ?></span>
                </div>

                <div class="ach-timeline-date"><?= htmlspecialchars($item['date'] ?? '') ?></div>

                <?php if (!empty($item['description'])): ?>
                  <p class="ach-timeline-desc"><?= htmlspecialchars($item['description']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <p class="ach-empty" id="ach-empty" hidden>No achievements found in this category.</p>
</div>
