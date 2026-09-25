<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Approvals &amp; Verifications — L'École Admin</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/approval-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/admin-verify.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/reject-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
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
      $title    = 'Approvals & Verifications';
      $subtitle = 'Review and verify accounts, extracurriculars, and notices before they go live.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- Tab / Metric Cards -->
      <div class="c-tab-grid j-tab-grid">
        <!-- Tab 1: Teachers -->
        <button type="button" class="c-tab-card c-tab-card--sky c-is-active j-tab-card" data-tab-name="Teachers" aria-pressed="true">
          <span class="c-tab-card__icon-wrap" aria-hidden="true">
            <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-userPlus"/>
            </svg>
          </span>
          <p class="c-tab-card__value j-tab-count" data-count-for="Teachers"><?= (int)($pendingCounts['Teachers'] ?? 0) ?></p>
          <p class="c-tab-card__label">Teacher Accounts</p>
        </button>

        <!-- Tab 2: Extracurriculars -->
        <button type="button" class="c-tab-card c-tab-card--sunshine j-tab-card" data-tab-name="Extracurriculars" aria-pressed="false">
          <span class="c-tab-card__icon-wrap" aria-hidden="true">
            <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-extracurricular"/>
            </svg>
          </span>
          <p class="c-tab-card__value j-tab-count" data-count-for="Extracurriculars"><?= (int)($pendingCounts['Extracurriculars'] ?? 0) ?></p>
          <p class="c-tab-card__label">Extracurricular Cards</p>
        </button>

        <!-- Tab 3: Notices -->
        <button type="button" class="c-tab-card c-tab-card--terracotta j-tab-card" data-tab-name="Notices" aria-pressed="false">
          <span class="c-tab-card__icon-wrap" aria-hidden="true">
            <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-mail"/>
            </svg>
          </span>
          <p class="c-tab-card__value j-tab-count" data-count-for="Notices"><?= (int)($pendingCounts['Notices'] ?? 0) ?></p>
          <p class="c-tab-card__label">Notices</p>
        </button>
      </div>

      <!-- Status Filter Row -->
      <div class="c-filter-row">
        <p class="c-filter-row__label j-filter-label">Pending submissions</p>
        <div class="c-filter-select-wrap">
          <?php
          $dropdownId    = 'j-select-status-filter';
          $dropdownLabel = 'Filter submissions by status';
          $placeholder   = 'Pending';
          $options       = $statusOptions ?? ['All', 'Pending', 'Approved', 'Rejected'];
          $selectedValue = 'Pending';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>
      </div>

      <!-- Tab Panel 1: Teachers -->
      <section class="c-tab-panel c-is-active j-tab-panel" data-tab-panel="Teachers">
        <div class="c-approval-grid j-approval-grid">
          <?php if (!empty($items['Teachers'])): ?>
            <?php foreach ($items['Teachers'] as $item): ?>
              <?php require __DIR__ . '/../components/_approval_card.php'; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="c-empty-state j-empty-state" data-empty-noun="teacher accounts" hidden>
          <p class="c-empty-state__text j-empty-state-text">No teacher accounts to show.</p>
        </div>
      </section>

      <!-- Tab Panel 2: Extracurriculars -->
      <section class="c-tab-panel j-tab-panel" data-tab-panel="Extracurriculars" hidden>
        <div class="c-approval-grid j-approval-grid">
          <?php if (!empty($items['Extracurriculars'])): ?>
            <?php foreach ($items['Extracurriculars'] as $item): ?>
              <?php require __DIR__ . '/../components/_approval_card.php'; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="c-empty-state j-empty-state" data-empty-noun="extracurricular cards" hidden>
          <p class="c-empty-state__text j-empty-state-text">No extracurricular cards to show.</p>
        </div>
      </section>

      <!-- Tab Panel 3: Notices -->
      <section class="c-tab-panel j-tab-panel" data-tab-panel="Notices" hidden>
        <div class="c-approval-grid j-approval-grid">
          <?php if (!empty($items['Notices'])): ?>
            <?php foreach ($items['Notices'] as $item): ?>
              <?php require __DIR__ . '/../components/_approval_card.php'; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="c-empty-state j-empty-state" data-empty-noun="notices" hidden>
          <p class="c-empty-state__text j-empty-state-text">No notices to show.</p>
        </div>
      </section>

    </div>

    <!-- Rejection Confirmation Modal -->
    <?php require __DIR__ . '/../components/_reject_modal.php'; ?>

  </main>
</div>

<!-- Component Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/admin-verify.js?v=<?= time() ?>"></script>

</body>
</html>
