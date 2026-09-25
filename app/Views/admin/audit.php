<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Audit Logs — L'École Admin</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/audit.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-page-stack">

      <!-- Page Header -->
      <?php
      $title    = 'Audit Logs';
      $subtitle = 'System-wide activity tracking and security monitoring.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- Toolbar: Search + Activity/Actor Filters -->
      <div class="c-toolbar">
        <!-- Search Field -->
        <div class="c-search-field">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-search"/>
          </svg>
          <input type="search" class="c-search-field__input j-search-input" id="j-search-input" placeholder="Search by actor or details..." autocomplete="off" />
        </div>

        <!-- Filter Dropdowns -->
        <div class="c-toolbar__filters">
          <!-- Activity Filter Dropdown -->
          <?php
          $dropdownId    = 'j-select-activity';
          $dropdownLabel = 'Filter by activity type';
          $placeholder   = 'All activities';
          $options       = $activityOptions ?? ['All activities'];
          $selectedValue = 'All activities';
          require __DIR__ . '/../components/_dropdown.php';
          ?>

          <!-- Actor Filter Dropdown -->
          <?php
          $dropdownId    = 'j-select-actor';
          $dropdownLabel = 'Filter by actor role';
          $placeholder   = 'All actors';
          $options       = $actorOptions ?? ['All actors'];
          $selectedValue = 'All actors';
          require __DIR__ . '/../components/_dropdown.php';
          ?>

          <!-- Sliders Icon Decoration -->
          <div class="c-toolbar__sliders" aria-hidden="true">
            <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-sliders"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Audit Log List -->
      <div class="c-log-list" id="j-log-list" aria-live="polite">
        <?php if (!empty($logs)): ?>
          <?php foreach ($logs as $log): ?>
            <?php require __DIR__ . '/../components/_audit_card.php'; ?>
          <?php endforeach; ?>
        <?php endif; ?>

        <div class="c-log-empty" id="j-log-empty" <?= !empty($logs) ? 'hidden' : '' ?>>
          No audit events match those filters.
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Component Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/audit-card.js?v=<?= time() ?>"></script>

</body>
</html>
