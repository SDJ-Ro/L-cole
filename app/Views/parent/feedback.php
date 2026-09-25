<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher Feedback — L'École Parent Portal</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-main-inner">
      <div class="c-main-container">
        <div class="c-page-stack">

          <!-- Page Header Component Reused -->
          <?php
          $pageTitle    = 'Teacher Feedback';
          $pageSubtitle = 'Read updates and feedback from your child\'s teachers.';
          require __DIR__ . '/../components/_page_header.php';
          ?>

          <!-- Search & Filter Controls: Notice Board standard toolbar layout -->
          <section class="c-filter-bar c-filter-bar--feedback" aria-label="Filter feedback">
            <label class="c-search-field">
              <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <use href="#icon-search"/>
              </svg>
              <input 
                type="text" 
                class="c-search-field__input j-search-input" 
                id="j-feedback-search-input" 
                placeholder="Search feedback..." 
                autocomplete="off" 
              />
            </label>

            <div class="c-filter-bar__selects">
              <?php
              $dropdownId    = 'j-feedback-type-filter';
              $dropdownLabel = 'Filter Feedback Type';
              $placeholder   = 'All Types';
              $options       = $filterTypes ?? ['All Types', 'Positive', 'Constructive', 'Negative'];
              $selectedValue = 'All Types';
              $name          = 'feedback_type_filter';
              require __DIR__ . '/../components/_dropdown.php';
              ?>
            </div>
          </section>

          <!-- Feedback Grid Reusing single component: _feedback_card.php -->
          <div class="c-feedback-grid" id="j-feedback-grid">
            <?php if (!empty($feedbacks)): ?>
              <?php foreach ($feedbacks as $card): ?>
                <?php require __DIR__ . '/../components/_feedback_card.php'; ?>
              <?php endforeach; ?>
            <?php endif; ?>

            <!-- Empty State -->
            <div class="c-feedback-empty" id="j-feedback-empty-state" style="display: <?= empty($feedbacks) ? 'block' : 'none' ?>;">
              <div class="c-feedback-empty__icon">
                <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                  <use href="#icon-feedback" />
                </svg>
              </div>
              <h3 class="c-feedback-empty__title">No feedback entries found</h3>
              <p class="c-feedback-empty__text">Try adjusting your search query or filter selection.</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

<!-- =======================================================================
     FEEDBACK DETAIL MODAL: EXPANDED SAME CARD WITH UNIVERSAL BACK BUTTON
     ======================================================================= -->
<div class="c-modal-layer j-feedback-detail-layer" id="j-feedback-detail-modal" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-feedback-modal-backdrop" aria-label="Close feedback detail"></button>
  
  <div class="c-modal-card-wrapper" role="dialog" aria-modal="true" aria-labelledby="j-detail-subject" tabindex="-1">
    <!-- Universal Back Button (Solid Moss Green) -->
    <div>
      <button type="button" class="c-back-link-btn j-feedback-modal-close" aria-label="Back">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <use href="#icon-arrowLeft" />
        </svg>
        <span>Back</span>
      </button>
    </div>

    <!-- The Same Card Layout Expanded -->
    <article class="c-feedback-card c-feedback-detail-card" id="j-detail-card-container">
      <div class="c-feedback-card__body">
        <div class="c-feedback-card__top">
          <span class="c-type-badge" id="j-detail-badge"></span>
          <span class="c-feedback-card__date" id="j-detail-date"></span>
        </div>

        <p class="c-feedback-card__parent-line" id="j-detail-parent-line" style="display: none;"></p>

        <div class="c-feedback-card__header-info">
          <h3 class="c-feedback-card__subject c-font-display" id="j-detail-subject" style="font-size: 1.15rem;"></h3>
          <p class="c-feedback-card__teacher-meta">
            <span class="c-feedback-card__teacher-name" id="j-detail-teacher"></span>
            <span class="c-feedback-card__teacher-sep" id="j-detail-teacher-sep">•</span>
            <span class="c-feedback-card__teacher-role" id="j-detail-teacher-role"></span>
          </p>
        </div>

        <p class="c-feedback-card__preview" id="j-detail-fulltext"></p>
      </div>

      <!-- Faded Tinted Footer -->
      <div class="c-feedback-card__footer" id="j-detail-footer-tint">
        <span id="j-detail-footer-label">Official Feedback Record</span>
        <span class="c-feedback-card__date" id="j-detail-footer-date"></span>
      </div>
    </article>
  </div>
</div>

<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback.js?v=<?= time() ?>"></script>
</body>
</html>
