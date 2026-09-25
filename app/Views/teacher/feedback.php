<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feedback Channel — L'École Teacher</title>
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

          <!-- Page Header Component Reused with "New Feedback" action button -->
          <?php
          $pageTitle    = 'Feedback Channel';
          $pageSubtitle = 'View parent feedback, manage student progress, and post updates.';
          $actionButton = '
            <button type="button" class="c-btn c-btn--sky j-open-new-feedback-btn">
              <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <use href="#icon-plus"/>
              </svg>
              <span>New Feedback</span>
            </button>
          ';
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

<!-- =======================================================================
     TEACHER "NEW FEEDBACK" MODAL
     100% REUSES MASTER _form_card.php DIRECTLY (NO REDUNDANT COMPONENT FILE)
     ======================================================================= -->
<div class="c-modal-layer j-teacher-feedback-modal-layer" id="j-teacher-feedback-modal" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-teacher-modal-backdrop" aria-label="Close modal"></button>
  
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-teacher-fb-title" style="max-width: 580px; width: 100%;">
    <?php
    ob_start();
    ?>
    <button type="button" class="c-modal__close-btn j-teacher-modal-close" aria-label="Close modal">
      <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <use href="#icon-x" />
      </svg>
    </button>
    <?php
    $headerRightSlot = ob_get_clean();

    ob_start();
    ?>
      <!-- Student Dropdown -->
      <div class="c-form-field">
        <label class="c-form-field-label">Select Student <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
        <?php
        $dropdownId    = 'j-fb-student-select';
        $dropdownLabel = 'Select Student';
        $placeholder   = 'Choose a student...';
        $options       = [
            ['value' => 'Nethmi Perera (2021/0456)', 'label' => 'Nethmi Perera (2021/0456) - Grade 10-A'],
            ['value' => 'Amara Silva (2021/0203)',   'label' => 'Amara Silva (2021/0203) - Grade 10-B'],
            ['value' => 'Maya Kapoor (2022/0118)',   'label' => 'Maya Kapoor (2022/0118) - Grade 9-A'],
            ['value' => 'John Doe (2021/0892)',      'label' => 'John Doe (2021/0892) - Grade 10-A'],
            ['value' => 'Dilan Jayasuriya (2021/0501)', 'label' => 'Dilan Jayasuriya (2021/0501) - Grade 11-C']
        ];
        $selectedValue = 'Nethmi Perera (2021/0456)';
        $name          = 'student';
        require __DIR__ . '/../components/_dropdown.php';
        ?>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <!-- Category Dropdown -->
        <div class="c-form-field">
          <label class="c-form-field-label">Feedback Category <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
          <?php
          $dropdownId    = 'j-fb-category-select';
          $dropdownLabel = 'Feedback Category';
          $placeholder   = 'Select Category';
          $options       = [
              ['value' => 'Positive',     'label' => 'Positive'],
              ['value' => 'Constructive', 'label' => 'Constructive'],
              ['value' => 'Negative',     'label' => 'Negative']
          ];
          $selectedValue = 'Positive';
          $name          = 'category';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>

        <!-- Subject Dropdown -->
        <div class="c-form-field">
          <label class="c-form-field-label">Subject <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
          <?php
          $dropdownId    = 'j-fb-subject-select';
          $dropdownLabel = 'Subject';
          $placeholder   = 'Select Subject';
          $options       = [
              'Mathematics',
              'Science',
              'English Literature',
              'History',
              'Art & Design',
              'Physical Education',
              'General Conduct'
          ];
          $selectedValue = 'Mathematics';
          $name          = 'subject';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>
      </div>

      <!-- Subject / Title Input -->
      <div class="c-form-field">
        <label class="c-form-field-label" for="j-fb-title-input">Feedback Title / Topic <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
        <input type="text" class="c-form-input" id="j-fb-title-input" placeholder="e.g. Algebra Assessment Performance Review" required />
      </div>

      <!-- Content Textarea -->
      <div class="c-form-field">
        <label class="c-form-field-label" for="j-fb-content-input">Feedback Content <span class="c-required-mark" style="color: var(--terracotta, #AF5031);">*</span></label>
        <textarea class="c-form-textarea" id="j-fb-content-input" rows="4" placeholder="Write comprehensive feedback note for the parent and student..." required style="min-height: 110px;"></textarea>
      </div>
    <?php
    $formBodySlot = ob_get_clean();

    ob_start();
    ?>
      <!-- Actions -->
      <div class="c-form-footer-actions" style="margin-left: auto;">
        <button type="button" class="c-btn c-btn--ghost j-teacher-modal-close" style="padding: 0.625rem 1.25rem; font-weight: 600;">Cancel</button>
        <button type="button" class="c-btn c-btn--terracotta j-submit-teacher-feedback" style="background: var(--terracotta, #AF5031); color: #fff; border-radius: 9999px; padding: 0.625rem 1.5rem; font-weight: 700; border: none; cursor: pointer;">
          Submit Feedback
        </button>
      </div>
    <?php
    $formFooterSlot = ob_get_clean();

    // Call _form_card.php directly with cream header
    $formId          = 'j-new-teacher-feedback-form';
    $headerTheme     = 'cream';
    $isModal         = true;
    $cardClass       = 'c-form-card--modal';
    $headerEyebrow   = 'FEEDBACK CHANNEL';
    $formTitle       = 'Create New Feedback';
    $formSubtitle    = 'Send positive, constructive, or disciplinary feedback to parents';
    $headerRightSlot = $headerRightSlot;
    $formBodySlot    = $formBodySlot;
    $formFooterSlot  = $formFooterSlot;
    
    require __DIR__ . '/../components/_form_card.php';
    ?>
  </section>
</div>

<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback.js?v=<?= time() ?>"></script>
</body>
</html>
