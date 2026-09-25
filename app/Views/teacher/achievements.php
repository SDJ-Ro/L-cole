<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Achievements — L'École Teacher Portal</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/datepicker.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/achievements.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/teacher-achievement-card.css?v=<?= time() ?>" />
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

      <!-- ===================================================================
           VIEW 1: ACHIEVEMENTS STUDENT GRID & FILTERS (Default View)
           =================================================================== -->
      <section id="j-achievements-main-section">
      <?php
      $title    = 'Achievements';
      $subtitle = 'Track student and school honors, awards, and recognitions.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

        <!-- Filter Bar: Search Input on Left, Category Dropdown on Right -->
        <section class="c-achievements-filter-bar" aria-label="Filter achievements">
          <!-- Quick Search -->
          <div class="c-search-field" style="min-width: 18rem; position: relative;">
            <svg class="c-icon c-search-field__icon" width="16" height="16" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(15,65,74,0.45);"><use href="#icon-search"/></svg>
            <input type="search" id="j-search-student-achievements" class="c-search-field__input" placeholder="Search student name..." autocomplete="off" style="padding-left: 2.5rem; padding-right: 1rem; height: 2.625rem; border-radius: var(--radius-lg, 0.5rem); border: 1px solid var(--color-border, #EFE8DF); background: #ffffff; width: 100%; font-size: 0.875rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.04)); box-sizing: border-box;" />
          </div>

          <!-- Category Dropdown Filter -->
          <div style="min-width: 11rem;">
            <?php
            $dropdownId    = 'j-select-category-filter';
            $options       = [
                ['value' => 'all',             'label' => 'All Categories'],
                ['value' => 'Class',           'label' => 'Class'],
                ['value' => 'Extracurricular', 'label' => 'Extracurricular']
            ];
            $selectedValue = 'all';
            $placeholder   = 'All Categories';
            $labelPrefix   = 'Filter';
            $dropdownLabel = 'Filter achievements category';
            require __DIR__ . '/../components/_dropdown.php';
            ?>
          </div>
        </section>

        <!-- Student Cards Grid (Option A: Component Reused Loop) -->
        <div class="c-student-grid" id="j-view-student-grid">
          <?php foreach ($students as $student): ?>
            <?php require __DIR__ . '/../components/_teacher_achievement_card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div id="j-achievements-empty-state" style="display: none; padding: 48px 16px; text-align: center; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); margin-top: 1.5rem;">
          <svg width="40" height="40" style="color: rgba(15,65,74,0.35); margin-bottom: 0.75rem;"><use href="#icon-search"/></svg>
          <p style="font-size: 1.0625rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0 0 4px 0;">No students found</p>
          <p style="font-size: 0.875rem; color: rgba(15,65,74,0.6); margin: 0;">Try adjusting your search query or selecting a different category filter.</p>
        </div>
      </section>

      <!-- ===================================================================
           VIEW 2: STUDENT TIMELINE VIEW (Shown when clicking 'View')
           =================================================================== -->
      <section class="c-timeline-view" id="j-view-student-timeline" style="display: none;">
        
        <!-- Standard Back Navigation Link (No redundant page title header above it) -->
        <div style="margin-bottom: 1.25rem;">
          <button type="button" class="c-back-link j-back-to-grid" id="j-back-to-grid" style="display: inline-flex; align-items: center; gap: 0.5rem; background: none; border: none; padding: 0; color: var(--midnight, #0F414A); font-weight: 700; font-size: 0.9375rem; cursor: pointer; text-decoration: none;">
            <svg class="c-icon" width="18" height="18"><use href="#icon-chevronLeft"/></svg>
            <span>Back</span>
          </button>
        </div>

        <!-- Connected Timeline Card Container (Header + Timeline Body Unified) -->
        <div class="c-timeline-card-container">
          <!-- Student Banner Header -->
          <div class="c-timeline-banner">
            <div class="c-timeline-banner__student">
              <div class="c-timeline-avatar-wrap" id="j-timeline-avatar-wrap">
                <div class="c-timeline-avatar-initials" id="j-timeline-initials">AS</div>
                <img class="c-timeline-avatar-img" id="j-timeline-avatar-img" src="" alt="" style="display: none; width: 3rem; height: 3rem; border-radius: 50%; object-fit: cover;" />
              </div>
              <div>
                <h2 class="c-timeline-banner__name">
                  <span id="j-timeline-student-name">Amara Silva</span>
                  <span class="c-tag" style="background: #D5EBE8; color: #2E7588; font-weight: 700; font-size: 0.6875rem; letter-spacing: 0.08em; padding: 0.2rem 0.5rem; border-radius: 4px;">FINALISED</span>
                </h2>
                <p class="c-timeline-banner__index" id="j-timeline-student-index">2021/0203</p>
              </div>
            </div>

            <!-- View Issue Button: Dark Maroon #8A1515 (Conditionally displayed when student has issue) -->
            <div id="j-timeline-issue-action" style="display: <?= !empty($students[0]['hasIssue']) ? 'block' : 'none' ?>;">
              <button type="button" class="c-btn c-btn--issue j-view-issue" id="j-btn-view-issue" title="Review student reported issue">
                <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-alertTriangle"/></svg>
                <span>View Issue</span>
              </button>
            </div>
          </div>

          <!-- Timeline Body Directly Connected Below -->
          <div class="c-timeline-card-body">
            <h3 style="font-size: 1.125rem; font-weight: 800; color: #0F414A; margin: 0 0 0.5rem 0;">Full Timeline</h3>
            <?php
              $showFilters = true;
              require __DIR__ . '/../components/_achievements_timeline.php';
            ?>
          </div>
        </div>

      </section>

    </div>
  </main>
</div>

<!-- =======================================================================
     REUSABLE RECORD ACHIEVEMENT & REVIEW ISSUE MODAL COMPONENT
     ======================================================================= -->
<?php require_once __DIR__ . '/../components/_record_achievement_modal.php'; ?>

<!-- Component Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/datepicker.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/achievements.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/teacher-achievements.js?v=<?= time() ?>"></script>
</body>
</html>
