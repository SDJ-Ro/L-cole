<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Character Certificate — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/character-certificate.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/student-certificates.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <?php
    $title    = 'Character Certificate';
    $subtitle = 'Review and verify student achievements and conduct records.';
    
    // Header action buttons matching original reference
    ob_start();
    ?>
    <div style="display: flex; align-items: center; gap: 12px;">
      <button class="btn btn-outline" id="certPrintBtn" type="button" style="display: inline-flex; align-items: center; gap: 8px;">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-printer"/>
        </svg>
        <span>Download / Print</span>
      </button>

      <button class="btn btn-maroon cert-request-btn" id="openRccModalBtn" data-trigger="rcc-modal" type="button" style="display: inline-flex; align-items: center; gap: 8px;">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-fileCheck"/>
        </svg>
        <span>Request Character Certificate</span>
      </button>
    </div>
    <?php
    $action = ob_get_clean();
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <!-- 2-Column Student Certificate Layout -->
    <div class="cert-layout">

      <!-- Left Column: A4 Document Canvas & Controls -->
      <div class="cert-pages">

        <!-- Hidden measurement container pre-rendering the pure A4 certificate -->
        <div id="certMeasure" style="position: absolute; left: -9999px; top: 0; width: 684px; visibility: hidden; pointer-events: none;">
          <?php
          $isEditing = false;
          require __DIR__ . '/../components/_character_certificate_a4.php';
          ?>
        </div>

        <!-- Visible A4 Page Container -->
        <article class="certificate-a4-page" id="certVisiblePage">
          <!-- Dynamically paginated by student-certificates.js -->
        </article>

        <!-- Pagination Controls (Dark green buttons matching design system) -->
        <div class="cert-pagination-row no-print" id="certPaginationRow" style="display: none;">
          <button type="button" class="btn btn-dark" id="certPrevBtn">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-chevronLeft"/>
            </svg>
            <span>Previous page</span>
          </button>
          <span id="certPageLabel">Page 1 of 1</span>
          <button type="button" class="btn btn-dark" id="certNextBtn">
            <span>Next page</span>
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-chevronRight"/>
            </svg>
          </button>
        </div>

        <!-- Preview Draft Disclaimer Banner -->
        <div class="cert-disclaimer no-print">
          <span class="cert-disclaimer-icon">
            <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-info"/>
            </svg>
          </span>
          <p>Final certificate will be available upon graduation verification. This document is a <strong>Preview Draft</strong> based on current records.</p>
        </div>

      </div>

      <!-- Right Column: Side Cards -->
      <div class="cert-side-cards no-print">

        <!-- "Something missing?" card -->
        <div class="cert-missing-card">
          <div class="cert-missing-title">Something missing?</div>
          <p class="cert-missing-text">If an achievement or position isn't appearing, please contact your Class Teacher.</p>
          <button class="cert-missing-btn" id="openMrrModalBtn" type="button">
            Submit Missing Record Request
          </button>
        </div>

      </div>

    </div>
  </main>
</div>

<!-- Student Certificate Modals Component (RCC & MRR) -->
<?php require_once __DIR__ . '/../components/_student_certificate_modals.php'; ?>

<!-- Component Scripts -->
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/export-pdf.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/student-certificates.js?v=<?= time() ?>"></script>
</body>
</html>
