<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Achievements — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/metric-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/achievements.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <?php
    $title    = 'Achievements';
    $subtitle = "A complete record of academic, sporting, and co-curricular honours earned during your time at L'École.";
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <!-- Top 4 Metric Cards Reusing _metric_card.php -->
    <div class="c-metrics-grid c-metrics-grid--four">
      <?php foreach ($metrics as $card): ?>
        <?php require __DIR__ . '/../components/_metric_card.php'; ?>
      <?php endforeach; ?>
    </div>

    <!-- Timeline Panel -->
    <div class="ach-panel">
      <div class="ach-panel-header">
        <h3 class="ach-panel-title">Full Timeline</h3>
      </div>

      <!-- Timeline Component (Clean 1-line call) -->
      <?php require __DIR__ . '/../components/_achievements_timeline.php'; ?>
    </div>
  </main>
</div>

<!-- Component Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/achievements.js?v=<?= time() ?>"></script>
</body>
</html>
