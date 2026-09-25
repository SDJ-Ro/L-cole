<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inquiries & Complaints Management — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/complaints.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
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
    $title    = 'Inquiries & Complaints';
    $subtitle = 'Review, investigate, and officially resolve parent questions, grievances, and service requests.';
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <div class="c-complaints-page">
      <!-- Search & Filter Controls Toolbar (Notice Board standard layout) -->
      <section class="c-filter-bar c-filter-bar--complaints" aria-label="Filter inquiries">
        <label class="c-search-field">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-search"/>
          </svg>
          <input 
            type="text" 
            class="c-search-field__input j-search-input" 
            id="j-complaints-search" 
            placeholder="Search by reference, parent, student, or issue..." 
            autocomplete="off" 
          />
        </label>

        <div class="c-filter-bar__selects">
          <!-- Category Filter -->
          <?php
          $dropdownId     = 'filter-category';
          $dropdownLabel  = 'Category';
          $placeholder    = 'All Categories';
          $selectedValue  = 'All';
          $options        = $categories ?? ['All', 'Academic', 'Facilities', 'Transport', 'Administration', 'Extracurricular'];
          $name           = 'filter-category-val';
          require __DIR__ . '/../components/_dropdown.php';
          ?>

          <!-- Status Filter -->
          <?php
          $dropdownId     = 'filter-status';
          $dropdownLabel  = 'Status';
          $placeholder    = 'All Statuses';
          $selectedValue  = 'All';
          $options        = ['All', 'In Progress', 'Resolved'];
          $name           = 'filter-status-val';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>
      </section>

      <!-- Complaints List Grid -->
      <div class="c-complaints-list" id="j-complaints-list">
        <?php if (!empty($complaints)): ?>
          <?php foreach ($complaints as $complaint): ?>
            <?php
            $canResolve     = true;
            $showParentInfo = true;
            require __DIR__ . '/../components/_complaint_card.php';
            ?>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty State Notice -->
        <div class="c-complaints-empty" id="j-complaints-empty" style="<?= empty($complaints) ? 'display: flex;' : 'display: none;' ?>">
          <svg class="c-complaints-empty__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-complaints"/>
          </svg>
          <h4 class="c-complaints-empty__title">No inquiries match your filter</h4>
          <p class="c-complaints-empty__desc">Try adjusting your search terms or filter selections.</p>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Scripts -->
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/complaints.js?v=<?= time() ?>"></script>

</body>
</html>
