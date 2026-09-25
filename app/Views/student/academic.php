<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Academic Records — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/export-pdf-button.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/student-academic.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <?php
    $title    = 'Academic Records';
    $subtitle = 'Detailed marks and subject-wise performance will appear here.';
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <div class="c-academic-layout">
      <div class="academic-top-grid">
        
        <!-- Left Side: Digital Record Book Component -->
        <div class="academic-record-book-col">
          <?php 
            // Reuse exact Digital Record Book component
            $studentGrade = 'Grade ' . ($selectedGrade ?? 6);
            $recordData = $recordData ?? [
                'selectedGrade' => 6,
                'selectedTerm'  => 'Term 1',
                'marks'         => $academicData[6]['terms']['Term 1'] ?? [],
                'feedback'      => $academicData[6]['feedback']['Term 1']['text'] ?? '',
                'feedbackDate'  => $academicData[6]['feedback']['Term 1']['date'] ?? '',
                'feedbackTeacher' => $academicData[6]['feedback']['Term 1']['name'] ?? ''
            ];
            $isEditable = false;
            require __DIR__ . '/../components/_digital_record_book.php'; 
          ?>
        </div>

        <!-- Right Side: Scaled Metric Card + Performance Trend Carousel -->
        <div class="academic-side-cards">
          
          <!-- Top Card: Total Marks (Term 1) -->
          <div class="panel total-marks-card">
            <div class="total-marks-icon">
              <svg class="c-icon" width="22" height="22" aria-hidden="true"><use href="#icon-trendingUp"/></svg>
            </div>
            <div class="total-marks-label" id="total-marks-label">TOTAL MARKS (TERM 1)</div>
            <div class="total-marks-value">
              <span id="total-marks-scored"><?= $initialScored ?? '522' ?></span>
              <span class="total-marks-out-of">/<?= $initialOutOf ?? '600' ?></span>
            </div>
            <div class="total-marks-average">Average: <span id="total-marks-average"><?= $initialAverage ?? '87.0%' ?></span></div>
            <div class="total-marks-position">Class Position: <span id="total-marks-position"><?= $initialPosition ?? '4th' ?></span></div>
          </div>

          <!-- Bottom Card: Multi-Grade Performance Trend Carousel -->
          <div class="panel trend-panel trend-panel-compact">
            <div class="trend-panel-header">
              <h3>Performance Trend</h3>
            </div>
            <p class="trend-sub">Comparative progress across academic terms, Grade 6 – Grade 11</p>
            
            <div class="trend-carousel">
              <button class="trend-nav-btn trend-nav-prev" id="trend-prev-btn" aria-label="Previous grade" type="button">
                <svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-chevronLeft"/></svg>
              </button>
              
              <div class="trend-grade-grid" id="trend-grade-grid"></div>
              
              <button class="trend-nav-btn trend-nav-next" id="trend-next-btn" aria-label="Next grade" type="button">
                <svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
              </button>
            </div>
            
            <div class="trend-dots" id="trend-dots"></div>
            <div class="trend-tooltip" id="trend-tooltip" hidden></div>
            
            <div class="chart-legend">
              <span class="legend-item"><span class="legend-dot dot-teal"></span>My Average</span>
              <span class="legend-item"><span class="legend-dot dot-pink"></span>Grade Average</span>
            </div>
          </div>

        </div>

      </div>
    </div>
  </main>
</div>

<!-- Embedded Academic Dataset for Dynamic Interactivity -->
<script id="j-academic-data" type="application/json"><?= json_encode($academicData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/export-pdf.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/student-academic.js?v=<?= time() ?>"></script>
</body>
</html>
