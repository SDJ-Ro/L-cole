<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER TERM MARKS PAGE
 * =========================================================================
 * Path: MVC/app/Views/teacher/term_marks.php
 * Controller: TeacherController@termMarks
 * 
 * Features:
 *   - Pinned top header & filters (stays put while only cards scroll)
 *   - Search & Term dropdown with reduced spacing
 *   - Term filter dropdown without small "Term:" prefix
 *   - Master-detail 60/40 workspace
 *   - Fixed non-scrolling right side panel
 * =========================================================================
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Term Marks | L'ÉCOLE Teacher</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/teacher-achievement-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/teacher-term-marks.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main c-main--term-marks" id="j-main">
    <div class="c-page-stack c-page-stack--term-marks">

      <!-- Pinned Header & Filter Bar Section (Stays Put) -->
      <div class="c-term-marks-header-section">
        <!-- Page Header -->
        <?php
        $title    = 'Term Marks';
        $subtitle = 'Manage and record student term marks and performance feedback.';
        require __DIR__ . '/../components/_page_header.php';
        ?>

        <!-- Page Filter Bar -->
        <section class="c-term-marks-filter-bar" aria-label="Filter term marks">
          <!-- Quick Search -->
          <div class="c-term-marks-filter-bar__left">
            <div class="c-search-field" style="position: relative; width: 100%;">
              <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(15,65,74,0.45);">
                <use href="#icon-search"/>
              </svg>
              <input type="search"
                     id="j-search-student-marks"
                     class="c-search-field__input j-search-input"
                     placeholder="Search student name or index..."
                     autocomplete="off"
                     style="padding-left: 2.5rem; padding-right: 1rem; height: 2.5rem; border-radius: var(--radius-lg, 0.5rem); border: 1px solid var(--color-border, #EFE8DF); background: #ffffff; width: 100%; font-size: 0.875rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.04)); box-sizing: border-box;" />
            </div>
          </div>

          <!-- Filter Selects: Term Selection (Clean dropdown without small lettered prefix) -->
          <div class="c-term-marks-filter-bar__right">
            <div style="min-width: 9.5rem;">
              <?php
              $dropdownId    = 'j-select-term-filter';
              $options       = [
                  ['value' => 'Term 1', 'label' => 'Term 1'],
                  ['value' => 'Term 2', 'label' => 'Term 2'],
                  ['value' => 'Term 3', 'label' => 'Term 3']
              ];
              $selectedValue = $selectedTerm ?? 'Term 1';
              $placeholder   = 'Select Term';
              $labelPrefix   = ''; // Removed small lettered Term
              $dropdownLabel = 'Filter by term';
              require __DIR__ . '/../components/_dropdown.php';
              ?>
            </div>
          </div>
        </section>
      </div>

      <!-- ===================================================================
           MASTER-DETAIL SPLIT WORKSPACE
           - Left: Student Grid (Only this section scrolls)
           - Right: Fixed non-scrolling side panel for editing marks
           =================================================================== -->
      <section class="c-term-marks-workspace j-term-marks-workspace" aria-label="Student Marks Workspace">
        <!-- Main Grid Area (Scrolls independently) -->
        <div class="c-term-marks-workspace__main">
          <div class="c-student-grid j-student-grid" id="j-view-student-grid">
            <?php 
            $cardType = 'term_marks';
            foreach ($students as $student):
                require __DIR__ . '/../components/_teacher_achievement_card.php';
            endforeach; 
            ?>
          </div>
        </div>

        <!-- Right-Docked Edit Marks Panel (Fixed in place, non-scrolling) -->
        <div class="c-term-marks-workspace__side j-term-marks-side" style="display: none;">
          <?php require __DIR__ . '/../components/_teacher_term_marks_panel.php'; ?>
        </div>
      </section>

    </div>
  </main>
</div>

<!-- Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/teacher-term-marks.js?v=<?= time() ?>"></script>
</body>
</html>
