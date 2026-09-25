<?php
// MVC/app/Views/components/_donut_chart.php
// Reusable SVG Donut / Pie Chart Component
// Expects: $donutConfig array:
//   - id: string
//   - title: string
//   - totalLabel: string
//   - centerLabel: string
//   - total: int|float (optional)
//   - slices: array of ['name' => ..., 'value' => ..., 'color' => ..., 'd' => ... (optional)]

$chartId     = $donutConfig['id'] ?? ('j-donut-' . uniqid());
$chartTitle  = $donutConfig['title'] ?? 'Overview';
$centerLabel = $donutConfig['centerLabel'] ?? 'Total';
$slices      = $donutConfig['slices'] ?? [];

// Calculate total if not explicit
$total = $donutConfig['total'] ?? 0;
if ($total <= 0) {
    foreach ($slices as $s) {
        $total += (float)($s['value'] ?? 0);
    }
}

// Compute SVG path for any slice lacking pre-computed 'd'
$cx = 100;
$cy = 100;
$R  = 70;
$r  = 50;
$currentAngle = -M_PI / 2;

foreach ($slices as &$slice) {
    if (!empty($slice['d'])) {
        continue;
    }
    $val = (float)($slice['value'] ?? 0);
    $fraction = ($total > 0) ? ($val / $total) : 0;
    $sliceAngle = $fraction * 2 * M_PI;
    $gap = (count($slices) > 1 && $fraction < 1) ? 0.035 : 0;
    
    $startA = $currentAngle + ($gap / 2);
    $endA   = $currentAngle + $sliceAngle - ($gap / 2);
    if ($endA <= $startA) {
        $endA = $startA + 0.001;
    }
    
    $x1 = $cx + $R * cos($startA);
    $y1 = $cy + $R * sin($startA);
    $x2 = $cx + $R * cos($endA);
    $y2 = $cy + $R * sin($endA);
    $x3 = $cx + $r * cos($endA);
    $y3 = $cy + $r * sin($endA);
    $x4 = $cx + $r * cos($startA);
    $y4 = $cy + $r * sin($startA);
    
    $largeArc = ($sliceAngle - $gap > M_PI) ? 1 : 0;
    
    $slice['d'] = sprintf(
        "M %.2f %.2f A %d %d 0 %d 1 %.2f %.2f L %.2f %.2f A %d %d 0 %d 0 %.2f %.2f Z",
        $x1, $y1, $R, $R, $largeArc, $x2, $y2,
        $x3, $y3, $r, $r, $largeArc, $x4, $y4
    );
    
    $currentAngle += $sliceAngle;
}
unset($slice);
?>
<section class="c-panel" id="<?= htmlspecialchars($chartId) ?>">
  <div class="c-donut-card__head">
    <h2 class="c-panel__title" style="font-size: 0.9375rem;"><?= htmlspecialchars($chartTitle) ?></h2>
  </div>

  <div class="c-donut-card__plot">
    <svg class="c-donut-card__svg" viewBox="0 0 200 200">
      <?php foreach ($slices as $slice): ?>
        <path class="c-donut-card__slice j-donut-slice" 
              data-name="<?= htmlspecialchars($slice['name']) ?>" 
              data-value="<?= htmlspecialchars((string)$slice['value']) ?>" 
              data-color="<?= htmlspecialchars($slice['color']) ?>" 
              fill="<?= htmlspecialchars($slice['color']) ?>" 
              d="<?= htmlspecialchars($slice['d']) ?>" />
      <?php endforeach; ?>
    </svg>
    <div class="c-donut-card__center" aria-hidden="true">
      <span class="c-donut-card__center-value c-font-display"><?= number_format($total) ?></span>
      <span class="c-donut-card__center-label"><?= htmlspecialchars($centerLabel) ?></span>
    </div>
  </div>

  <div class="c-donut-card__legend">
    <?php foreach ($slices as $slice): ?>
      <button type="button" class="c-donut-card__legend-item j-donut-legend-item" 
              data-name="<?= htmlspecialchars($slice['name']) ?>" 
              data-value="<?= htmlspecialchars((string)$slice['value']) ?>" 
              data-color="<?= htmlspecialchars($slice['color']) ?>" 
              aria-label="View <?= htmlspecialchars($slice['name']) ?> details">
        <span class="c-donut-card__legend-dot" style="background:<?= htmlspecialchars($slice['color']) ?>"></span>
        <?= htmlspecialchars($slice['name']) ?>
      </button>
    <?php endforeach; ?>
  </div>
</section>
