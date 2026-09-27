<?php
/**
 * =========================================================================
 * L'ÉCOLE — SHARED ACADEMIC SECTION COMPONENT
 * =========================================================================
 * Master 2-column Academic Section interface:
 *   - Left column:  Digital Record Book (_digital_record_book.php)
 *   - Right column: Total Marks Metric Card + Multi-Grade Performance Trend Carousel
 *
 * Shared across:
 *   1. Student Profile Modal (_person_profile_modal.php -> Academics Tab)
 *   2. Student Academic Portal (student/academic.php)
 *   3. Parent Child Profile (parent/child-profile.php)
 * =========================================================================
 */

$selectedGrade   = $selectedGrade ?? 6;
$studentGrade    = $studentGrade ?? ('Grade ' . $selectedGrade);
$isEditable      = $isEditable ?? false;
$initialScored   = $initialScored ?? '522';
$initialOutOf    = $initialOutOf ?? '600';
$initialAverage  = $initialAverage ?? '87.0%';
$initialPosition = $initialPosition ?? '4th';

$recordData = $recordData ?? [
    'selectedGrade'   => (int)$selectedGrade,
    'selectedTerm'    => 'Term 1',
    'marks'           => [
        ['subject' => 'English Language', 'mark' => 88, 'highestMark' => 94],
        ['subject' => 'Mathematics',      'mark' => 92, 'highestMark' => 98],
        ['subject' => 'Science',          'mark' => 85, 'highestMark' => 91],
        ['subject' => 'History',          'mark' => 78, 'highestMark' => 89],
        ['subject' => 'Geography',        'mark' => 84, 'highestMark' => 90],
        ['subject' => 'ICT',              'mark' => 95, 'highestMark' => 97]
    ],
    'feedback'        => "A solid performance overall in Term 1, with strong analytical skills and consistent dedication. Continues to show exemplary progress across subjects.",
    'feedbackDate'    => 'Apr 17, 2024',
    'feedbackTeacher' => 'Mrs. Ishara Gunasekara'
];
?>

<div class="c-academic-layout">
  <div class="academic-top-grid">
    
    <!-- Left Side: Digital Record Book Component -->
    <div class="academic-record-book-col">
      <?php require __DIR__ . '/_digital_record_book.php'; ?>
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
          <span id="total-marks-scored"><?= htmlspecialchars((string)$initialScored) ?></span>
          <span class="total-marks-out-of">/<?= htmlspecialchars((string)$initialOutOf) ?></span>
        </div>
        <div class="total-marks-average">Average: <span id="total-marks-average"><?= htmlspecialchars((string)$initialAverage) ?></span></div>
        <div class="total-marks-position">Class Position: <span id="total-marks-position"><?= htmlspecialchars((string)$initialPosition) ?></span></div>
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
