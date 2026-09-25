<?php
/**
 * =========================================================================
 * L'ÉCOLE — DIGITAL RECORD BOOK COMPONENT
 * =========================================================================
 * Reusable Academic Record Book interface matching the new prototype design:
 *   - Record Book Header with Title & Export PDF button
 *   - 2 Dropdown Boxes on Top:
 *       1. Grade Selector (Grades 6 - 13)
 *       2. Term Selector (Term 1, Term 2, Term 3)
 *   - Maroon-Themed Marks Table (Subject, Marks, Highest Mark in Class)
 *   - Class Teacher's Feedback Card
 *   - Dynamic Edit Mode with Subject & Numeric Marks inputs
 *
 * Reused across:
 *   1. Student Profile Modal (Academics Tab)
 *   2. Teacher Student Details View & Grade Editing
 *   3. Parent Child Academics View
 *   4. Student Academics Portal
 * =========================================================================
 */

$gradeLevels = [6, 7, 8, 9, 10, 11, 12, 13];
$subjectsList = ['Sinhala', 'Tamil', 'English', 'English Language', 'Mathematics', 'Science', 'History', 'Geography', 'ICT', 'Information Tech', 'Arts', 'Music', 'Health & Physical Education', 'Religion'];

$rawGrade = $studentGrade ?? 'Grade 6';
preg_match('/\d+/', $rawGrade, $gm);
$currentGradeNum = !empty($gm[0]) ? (int)$gm[0] : 6;

$rb = $recordData ?? [];
$selectedGrade = (int)($rb['selectedGrade'] ?? $currentGradeNum);
$selectedTerm  = $rb['selectedTerm'] ?? 'Term 2';
$marks         = $rb['marks'] ?? [
    ['subject' => 'English Language', 'mark' => 88, 'highestMark' => 94],
    ['subject' => 'Mathematics',      'mark' => 92, 'highestMark' => 98],
    ['subject' => 'Science',          'mark' => 85, 'highestMark' => 91],
    ['subject' => 'History',          'mark' => 78, 'highestMark' => 89],
    ['subject' => 'Geography',        'mark' => 84, 'highestMark' => 90],
    ['subject' => 'ICT',              'mark' => 95, 'highestMark' => 97]
];
$teacherFeedback = $rb['feedback'] ?? "A solid performance overall in Term 1, with strong analytical skills and consistent dedication. Continues to show exemplary progress across subjects.";
$feedbackDate    = $rb['feedbackDate'] ?? 'Apr 17, 2024';
$feedbackTeacher = $rb['feedbackTeacher'] ?? 'Mrs. Ishara Gunasekara';
$editable        = !empty($isEditable);
?>

<div class="panel record-book-panel j-recordbook-container" data-editable="<?= $editable ? 'true' : 'false' ?>">
  
  <!-- Record Book Header -->
  <div class="record-book-header">
    <div class="record-book-title">
      <span class="record-book-icon">
        <svg viewBox="0 0 24 24"><use href="#icon-book"/></svg>
      </span>
      <h3 class="c-font-display">Digital Record Book — <span id="record-grade-label" class="j-record-grade-label">Grade <?= $selectedGrade ?></span></h3>
    </div>
    <?php
    $buttonId       = 'export-pdf-btn';
    $buttonLabel    = 'Export PDF';
    $targetSelector = '.j-recordbook-container';
    $documentTitle  = 'Official Student Record Book';
    $tone           = 'sky';
    require __DIR__ . '/_export_pdf_button.php';
    ?>
  </div>

  <!-- 2 Dropdown Boxes on Top: Grade & Term -->
  <div class="record-book-dropdowns" id="record-book-dropdowns">
    <div class="dropdown-group">
      <label class="dropdown-label">Grade</label>
      <div style="min-width: 140px;" class="j-rb-grade-dropdown-wrap">
        <?php
        $dropdownId    = 'j-record-grade-select';
        $options       = array_map(fn($g) => ['value' => (string)$g, 'label' => 'Grade ' . $g], $gradeLevels);
        $selectedValue = (string)$selectedGrade;
        $dropdownLabel = 'Grade';
        $dropdownClass = 'c-dropdown--compact';
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>
    <div class="dropdown-group">
      <label class="dropdown-label">Term</label>
      <div style="min-width: 140px;" class="j-rb-term-dropdown-wrap">
        <?php
        $dropdownId    = 'j-record-term-select';
        $options       = [
            ['value' => 'Term 1', 'label' => 'Term 1'],
            ['value' => 'Term 2', 'label' => 'Term 2'],
            ['value' => 'Term 3', 'label' => 'Term 3']
        ];
        $selectedValue = $selectedTerm;
        $dropdownLabel = 'Term';
        $dropdownClass = 'c-dropdown--compact';
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>
  </div>

  <!-- VIEW MODE: Maroon Marks Table -->
  <div class="j-recordbook-view-mode" id="j-recordbook-view-mode" <?= $editable ? 'style="display:none;"' : '' ?>>
    <table class="marks-table">
      <thead>
        <tr>
          <th>Subject</th>
          <th>Marks</th>
          <th>Highest Mark in Class</th>
        </tr>
      </thead>
      <tbody id="marks-table-body" class="j-marks-view-tbody">
        <?php foreach ($marks as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['subject']) ?></td>
            <td><?= htmlspecialchars($m['mark'] !== '' ? (string)$m['mark'] : '—') ?></td>
            <td><?= htmlspecialchars((string)($m['highestMark'] ?? '—')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- EDIT MODE: Dynamic Rows with Subject & Score Input -->
  <div class="c-marks-edit-container j-recordbook-edit-mode" id="j-recordbook-edit-mode" <?= !$editable ? 'style="display:none;"' : '' ?>>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; margin-top: 0.75rem;">
      <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(15, 65, 74, 0.55);">Subject &amp; Score (0-100)</span>
      <button type="button" class="c-add-mark-btn j-add-mark-btn">
        <svg class="c-icon" width="12" height="12" aria-hidden="true"><use href="#icon-plus"/></svg>
        <span>Add Mark</span>
      </button>
    </div>
    <div class="j-marks-edit-list" id="j-recordbook-edit-rows" style="display: flex; flex-direction: column; gap: 0.35rem;">
      <?php foreach ($marks as $idx => $m): ?>
        <div class="c-marks-row c-marks-cols-edit j-marks-edit-row" data-index="<?= $idx ?>">
          <input type="text" class="c-info-card-input j-mark-subject" value="<?= htmlspecialchars($m['subject']) ?>" placeholder="Subject" />
          <input type="number" min="0" max="100" class="c-marks-input j-mark-score" value="<?= htmlspecialchars((string)$m['mark']) ?>" placeholder="Score" />
          <button type="button" class="c-marks-remove j-remove-mark" title="Remove mark row">
            <svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-trash"/></svg>
          </button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Class Teacher Feedback Card -->
  <div class="teacher-feedback-section">
    <div class="teacher-feedback-header">
      <span class="teacher-feedback-icon">
        <svg class="c-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-feedback"/></svg>
      </span>
      <h4>Class Teacher's Feedback — <span id="feedback-term-label" class="j-feedback-term-label"><?= $selectedTerm ?></span>, <span id="feedback-grade-label">Grade <?= $selectedGrade ?></span></h4>
    </div>
    <div class="teacher-feedback-card" id="teacher-feedback-card">
      <div class="teacher-feedback-avatar">
        <svg class="c-icon" width="20" height="20" aria-hidden="true"><use href="#icon-user"/></svg>
      </div>
      <div class="teacher-feedback-body">
        <div class="teacher-feedback-meta" style="margin-bottom: 0.35rem;">
          <span class="teacher-feedback-name" style="font-weight: 700; color: var(--midnight, #0F414A);"><?= htmlspecialchars($feedbackTeacher) ?></span>
          <span class="teacher-feedback-date" style="font-weight: 500; color: rgba(15, 65, 74, 0.5); font-size: 0.8125rem; margin-left: 0.5rem;"><?= htmlspecialchars($feedbackDate) ?></span>
        </div>
        <p class="teacher-feedback-text j-feedback-text j-view-only" style="margin: 0; font-size: 0.875rem; line-height: 1.5; color: rgba(15, 65, 74, 0.8); <?= $editable ? 'display:none;' : '' ?>"><?= htmlspecialchars($teacherFeedback) ?></p>
        <textarea class="c-form-textarea j-feedback-textarea j-edit-only" style="<?= !$editable ? 'display:none;' : '' ?> margin-top:0.35rem;min-height:4rem;font-size:0.8125rem;" placeholder="Enter class teacher feedback..."><?= htmlspecialchars($teacherFeedback) ?></textarea>
      </div>
    </div>
  </div>

</div>
