<?php
require_once dirname(__FILE__) . '/../../../config/brand.php';
/**
 * =========================================================================
 * L'ÉCOLE — SHARED BAR CHART COMPONENT
 * =========================================================================
 * Reused identically across all 5 roles (Admin, Management, Student, Teacher, Parent).
 * Exact port of the standalone Student portal column chart engine
 * from admin_final_trying_to reduce js/Student.
 * 
 * - Fixed height 390px for clean Y-axis spacing (matches Student script.js).
 * - Uniform group width (95px), bar width (21px), bar gap (6px) across ALL roles.
 * - Perfectly straight X and Y axis arrowheads (direct SVG polygons).
 * - Rotated Y-axis title and centered X-axis title.
 * - White floating tooltip per column with dual-series data.
 * - Optional role-specific header slot for Term Dropdowns.
 * 
 * @var array $chartConfig
 */

// 1. CONFIGURATION & DEFAULTS
$chartId     = $chartConfig['id'] ?? 'j-bar-chart';
$chartTitle  = $chartConfig['title'] ?? 'Overview';
$yAxisTitle  = $chartConfig['yAxisTitle'] ?? 'VALUE';
$xAxisTitle  = $chartConfig['xAxisTitle'] ?? '';
$maxVal      = $chartConfig['maxVal'] ?? 100;
$ticks       = $chartConfig['ticks'] ?? [0, 25, 50, 75, 100];
$series      = $chartConfig['series'] ?? [
    ['key' => 'series1', 'label' => 'Series 1', 'color' => BRAND_SKYBLUE],
    ['key' => 'series2', 'label' => 'Series 2', 'color' => BRAND_MAROON],
];
$dataItems   = $chartConfig['data'] ?? [];
$termsData   = $chartConfig['termsData'] ?? null;
$unit        = ($maxVal <= 100 && stripos($yAxisTitle, 'SCORE') !== false) ? '%' : '';

// 2. UNIFORM DIMENSIONS (Proportioned to fill card cleanly without excessive white space)
$H          = 380;
$padLeft    = 50;
$padRight   = 14;
$padTop     = 14;
$padBottom  = 22; // Clean spacing below category labels
$axisColor  = '#7b9698';

$groupCount = max(count($dataItems), 1);
$barW       = 24;
$barGap     = 6;
$perGroupW  = ($groupCount <= 7) ? 126 : 95;

$plotW      = $groupCount * $perGroupW;
$plotH      = $H - $padTop - $padBottom;
$W          = $padLeft + $plotW + $padRight;
?>

<section class="c-panel c-primary-grid__chart chart-panel" 
         id="<?= htmlspecialchars($chartId) ?>-panel"
         data-chart-id="<?= htmlspecialchars($chartId) ?>"
         data-maxval="<?= (int)$maxVal ?>"
         data-ploth="<?= (int)$plotH ?>"
         data-padtop="<?= (int)$padTop ?>"
         data-unit="<?= htmlspecialchars($unit) ?>"
         <?php if (!empty($termsData)): ?>
           data-terms='<?= htmlspecialchars(json_encode($termsData), ENT_QUOTES, 'UTF-8') ?>'
         <?php endif; ?>>
  
  <!-- =====================================================================
       CARD HEADER (Shared title + Optional Role-Specific Controls Slot)
       ===================================================================== -->
  <div class="c-bar-chart-card__head panel-header">
    <h2 class="c-panel__title"><?= htmlspecialchars($chartTitle) ?></h2>

    <?php 
    // ---------------------------------------------------------------------
    // ROLE-SPECIFIC HEADER FEATURE SLOT
    // (Used by Student/Teacher/Parent for "Term 1/2/3" dropdown selector)
    // ---------------------------------------------------------------------
    if (!empty($chartConfig['headerExtra'])): 
    ?>
      <div class="c-bar-chart-card__extra">
        <?= $chartConfig['headerExtra'] ?>
      </div>
    <?php endif; ?>

  </div>

  <!-- =====================================================================
       SCROLLABLE CHART CANVAS (Exact Student Portal Engine: Fixed W, H=390)
       ===================================================================== -->
  <div class="c-bar-chart__area chart-area" id="<?= htmlspecialchars($chartId) ?>-area">
    <svg viewBox="0 0 <?= (int)$W ?> <?= (int)$H ?>" 
         width="<?= (int)$W ?>" 
         height="<?= (int)$H ?>" 
         style="width: <?= (int)$W ?>px; min-width: <?= (int)$W ?>px; height: <?= (int)$H ?>px; display: block;"
         id="<?= htmlspecialchars($chartId) ?>-svg">

      <!-- Y-Axis Solid Line -->
      <line x1="<?= $padLeft ?>" y1="<?= $padTop ?>" x2="<?= $padLeft ?>" y2="<?= $padTop + $plotH ?>" 
            stroke="<?= $axisColor ?>" stroke-width="1.5" class="c-bar-chart__axis-line" />

      <!-- X-Axis Solid Line -->
      <line x1="<?= $padLeft ?>" y1="<?= $padTop + $plotH ?>" x2="<?= $padLeft + $plotW ?>" y2="<?= $padTop + $plotH ?>" 
            stroke="<?= $axisColor ?>" stroke-width="1.5" class="c-bar-chart__axis-line" />

      <!-- Y-Axis Arrowhead (Direct Upward Polygon — Perfectly Straight) -->
      <polygon points="<?= $padLeft ?>,<?= $padTop - 6 ?> <?= $padLeft - 4.5 ?>,<?= $padTop + 2 ?> <?= $padLeft + 4.5 ?>,<?= $padTop + 2 ?>" 
               fill="<?= $axisColor ?>" />

      <!-- X-Axis Arrowhead (Direct Rightward Polygon — Perfectly Straight) -->
      <polygon points="<?= $padLeft + $plotW + 6 ?>,<?= $padTop + $plotH ?> <?= $padLeft + $plotW - 2 ?>,<?= $padTop + $plotH - 4.5 ?> <?= $padLeft + $plotW - 2 ?>,<?= $padTop + $plotH + 4.5 ?>" 
               fill="<?= $axisColor ?>" />

      <!-- Y-Axis Vertical Title (Rotated, moss green) -->
      <?php $yCenter = $padTop + ($plotH / 2); ?>
      <text x="14" y="<?= $yCenter ?>" class="c-bar-chart__axis-title" text-anchor="middle" 
            font-size="11.5" font-weight="600" letter-spacing="0.05em" fill="var(--moss, #4B5B34)" font-family="'Poppins', sans-serif"
            transform="rotate(-90 14 <?= $yCenter ?>)">
        <?= htmlspecialchars($yAxisTitle) ?>
      </text>

      <!-- Y-Axis Tick Labels (Student engine: clean labels, no gridlines) -->
      <?php foreach ($ticks as $tick): 
        $tickY = $padTop + $plotH - (($tick / $maxVal) * $plotH);
      ?>
        <text x="<?= $padLeft - 10 ?>" y="<?= $tickY + 4 ?>" text-anchor="end" 
              font-size="11" fill="#93a1a2" font-family="'Poppins', sans-serif" class="c-bar-chart__tick-label">
          <?= htmlspecialchars((string)$tick) ?>
        </text>
      <?php endforeach; ?>

      <!-- Data Columns (Category Groups) -->
      <?php foreach ($dataItems as $i => $item): 
        $groupX    = $padLeft + ($i * $perGroupW);
        $centerX   = $groupX + ($perGroupW / 2);
        $catLabel  = $item['label'] ?? '';

        // Two bars per column
        $val1   = $item[$series[0]['key'] ?? 'series1'] ?? 0;
        $val2   = $item[$series[1]['key'] ?? 'series2'] ?? 0;

        $h1     = ($val1 / $maxVal) * $plotH;
        $h2     = ($val2 / $maxVal) * $plotH;

        $y1     = $padTop + $plotH - $h1;
        $y2     = $padTop + $plotH - $h2;

        $bar1X  = $centerX - $barGap / 2 - $barW;
        $bar2X  = $centerX + $barGap / 2;
      ?>
        <!-- Group: <?= htmlspecialchars($catLabel) ?> -->
        <g class="j-chart-group" 
           data-category="<?= htmlspecialchars($catLabel) ?>"
           data-series1="<?= htmlspecialchars($series[0]['label'] ?? '') ?>"
           data-val1="<?= htmlspecialchars((string)$val1) ?>"
           data-color1="<?= htmlspecialchars($series[0]['color'] ?? BRAND_SKYBLUE) ?>"
           data-series2="<?= htmlspecialchars($series[1]['label'] ?? '') ?>"
           data-val2="<?= htmlspecialchars((string)$val2) ?>"
           data-color2="<?= htmlspecialchars($series[1]['color'] ?? BRAND_MAROON) ?>"
           data-unit="<?= htmlspecialchars($unit) ?>">

          <!-- Bar 1 -->
          <rect class="c-bar-chart__bar j-chart-bar chart-bar" 
                data-bar-type="series1"
                data-subject="<?= htmlspecialchars($catLabel) ?>"
                data-type="<?= htmlspecialchars($series[0]['label'] ?? '') ?>"
                data-value="<?= htmlspecialchars((string)$val1) ?>"
                x="<?= $bar1X ?>" y="<?= $y1 ?>" width="<?= $barW ?>" height="<?= max($h1, 2) ?>" rx="4"
                fill="<?= htmlspecialchars($series[0]['color'] ?? BRAND_SKYBLUE) ?>" />

          <!-- Bar 2 -->
          <rect class="c-bar-chart__bar j-chart-bar chart-bar" 
                data-bar-type="series2"
                data-subject="<?= htmlspecialchars($catLabel) ?>"
                data-type="<?= htmlspecialchars($series[1]['label'] ?? '') ?>"
                data-value="<?= htmlspecialchars((string)$val2) ?>"
                x="<?= $bar2X ?>" y="<?= $y2 ?>" width="<?= $barW ?>" height="<?= max($h2, 2) ?>" rx="4"
                fill="<?= htmlspecialchars($series[1]['color'] ?? BRAND_MAROON) ?>" />

          <!-- Category Label (Subject / Grade) -->
          <text x="<?= $centerX ?>" y="<?= $padTop + $plotH + 16 ?>" text-anchor="middle" 
                font-size="12" font-weight="400" fill="#5c6b6c" font-family="'Poppins', sans-serif" 
                class="c-bar-chart__cat-label">
            <?= htmlspecialchars($catLabel) ?>
          </text>
        </g>
      <?php endforeach; ?>

    </svg>
  </div>

  <!-- X-Axis Title (e.g. SUBJECTS or GRADE LEVEL) -->
  <?php if (!empty($xAxisTitle)): ?>
    <div class="c-bar-chart__axis-x-title chart-axis-x-title"><?= htmlspecialchars($xAxisTitle) ?></div>
  <?php endif; ?>

  <!-- =====================================================================
       CHART LEGEND (Bottom indicators matching Student portal palette)
       ===================================================================== -->
  <div class="c-bar-chart__legend chart-legend">
    <?php foreach ($series as $s): ?>
      <span class="c-bar-chart__legend-item legend-item">
        <span class="c-bar-chart__legend-dot legend-dot" style="background: <?= htmlspecialchars($s['color']) ?>;"></span>
        <?= htmlspecialchars($s['label']) ?>
      </span>
    <?php endforeach; ?>
  </div>

</section>
