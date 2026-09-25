<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/metric-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/bar-chart.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/calendar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/upcoming-events.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/unread-notices.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <!-- Universal Page Header Component -->
    <?php
    $title    = 'Dashboard';
    $subtitle = 'Welcome back, Jason!';
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <!-- Metric Cards -->
    <?php if (!empty($metrics)): ?>
      <div class="c-metrics-grid c-metrics-grid--four" style="margin-bottom: 2rem;">
        <?php foreach ($metrics as $card): ?>
          <?php require __DIR__ . '/../components/_metric_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Main Content Grid (Chart + Calendar) -->
    <div class="c-primary-grid">
      <!-- Column Chart -->
      <?php if (!empty($chartConfig)): ?>
        <?php require __DIR__ . '/../components/_bar_chart.php'; ?>
      <?php endif; ?>

      <!-- Calendar -->
      <?php if (!empty($calendarConfig)): ?>
        <?php require __DIR__ . '/../components/_calendar.php'; ?>
      <?php endif; ?>
    </div>

    <!-- Bottom Grid (Upcoming Events + Unread Notices) -->
    <?php if (!empty($upcomingEvents) || !empty($notices)): ?>
      <section class="c-bottom-grid" aria-label="Upcoming events and notices">
        <?php 
          require __DIR__ . '/../components/_upcoming_events.php';
          require __DIR__ . '/../components/_unread_notices.php';
        ?>
      </section>
    <?php endif; ?>

  </main>
</div>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/bar-chart.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/calendar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
</body>
</html>
