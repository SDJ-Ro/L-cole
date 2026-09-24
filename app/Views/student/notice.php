<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notice Board — L'École Student Portal</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/notice-board.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/notice-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    
    <div class="c-page-stack" id="j-view-board">
      
      <!-- Page Header (Read-only for Students) -->
      <?php
      $pageTitle    = 'Notice Board';
      $pageSubtitle = 'Stay up to date with official announcements, schedules, and club activities.';
      $actionButton = '';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- Search & Category Filter Toolbar -->
      <section class="c-filter-bar" aria-label="Filter notices">
        <label class="c-search-field">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-search"/>
          </svg>
          <input type="text" class="c-search-field__input j-search-input" placeholder="Search notices or programmes..." autocomplete="off" />
        </label>

        <div class="c-filter-bar__selects">
          <!-- Category Filter Dropdown -->
          <?php
          $dropdownId    = 'j-select-category-filter';
          $dropdownLabel = 'Filter by category';
          $placeholder   = 'All Categories';
          $options       = array_merge(
              [['value' => 'All', 'label' => 'All Categories']],
              array_map(fn($c) => ['value' => $c, 'label' => $c], array_values(array_diff($categories ?? [], ['All', 'All Categories'])))
          );
          $selectedValue = 'All';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>
      </section>

      <!-- Notice Cards Grid (Read-only: Zero action buttons) -->
      <div class="c-notice-grid" id="j-notice-grid">
        <?php if (!empty($notices)): ?>
          <?php foreach ($notices as $idx => $notice): ?>
            <?php 
              $showActions = false;
              $cardIndex = $idx;
              require __DIR__ . '/../components/_notice_card.php'; 
            ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Empty State -->
      <div class="c-empty-state" id="j-empty-state" hidden>
        <p class="c-empty-state__message">No announcements found matching your search or filter.</p>
        <button type="button" class="c-btn--link j-clear-filters">Clear filters</button>
      </div>

    </div>

  </main>
</div>

<!-- Component Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/notice-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/notice-filter.js?v=<?= time() ?>"></script>

</body>
</html>
