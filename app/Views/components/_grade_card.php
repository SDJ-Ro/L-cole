<?php
/**
 * =========================================================================
 * L'ÉCOLE — GRADE CARD COMPONENT
 * =========================================================================
 * Reusable card representing a single grade (e.g. Grade 6, Grade 7).
 * Expects:
 *   - $grade            : array ['id' => 'g6', 'name' => 'Grade 6', 'classes' => ['6-A', '6-B']]
 *   - $gradeIndex       : int (for alternating preview placement)
 *   - $classEnrollments : array mapping className => count
 *   - $classTeachers    : array mapping className => teacherName
 *   - $subjectTeachers  : array mapping className => [subject => teacherName]
 *   - $curriculumGroups : array of curriculum stages
 * =========================================================================
 */

$gradeId          = $grade['id'] ?? 'g6';
$gradeName        = $grade['name'] ?? 'Grade 6';
$classes          = $grade['classes'] ?? [];
$idx              = $gradeIndex ?? 0;
$previewPlacement = 'auto';

// Calculate total enrollment for this grade
$totalStudents = 0;
foreach ($classes as $cName) {
    $totalStudents += ($classEnrollments[$cName] ?? 30);
}

// Find appropriate subjects dynamically from curriculum groups
$gradeNum = (int)preg_replace('/\D/', '', $gradeName);
$subjects = [];
if (!empty($curriculumGroups)) {
    foreach ($curriculumGroups as $cg) {
        if (preg_match_all('/\d+/', $cg['range'], $m) && !empty($m[0])) {
            $min = (int)$m[0][0];
            $max = isset($m[0][1]) ? (int)$m[0][1] : $min;
            if ($gradeNum >= $min && $gradeNum <= $max) {
                $subjects = $cg['subjects'] ?? [];
                break;
            }
        }
    }
}
?>

<article class="c-grade-card" data-grade-id="<?= htmlspecialchars($gradeId) ?>" data-grade-name="<?= htmlspecialchars($gradeName) ?>">
  <div class="c-grade-card__head">
    <div>
      <h3 class="c-grade-card__name"><?= htmlspecialchars($gradeName) ?></h3>
      <p class="c-grade-card__meta">
        <svg class="c-icon" width="14" height="14"><use href="#icon-usersRound"/></svg>
        <?= number_format($totalStudents) ?> students · <?= count($classes) ?> classes
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <button type="button" class="c-btn-add c-btn-add--small j-add-class-btn" data-grade-id="<?= htmlspecialchars($gradeId) ?>">
        <svg class="c-icon" width="13" height="13"><use href="#icon-plus"/></svg>
        Add class
      </button>

      <button type="button" class="c-btn-delete-subtle j-delete-grade-btn" data-grade-id="<?= htmlspecialchars($gradeId) ?>" aria-label="Delete <?= htmlspecialchars($gradeName) ?>">
        <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-trash"/>
        </svg>
        Delete
      </button>
    </div>
  </div>

  <div class="c-class-list j-class-list">
    <?php foreach ($classes as $className): 
      $classTeacher = $classTeachers[$className] ?? 'Assignment pending';
      $studentCount = $classEnrollments[$className] ?? 30;
      $assignedSubjs = $subjectTeachers[$className] ?? [];
      require __DIR__ . '/_grade_class_row.php';
    endforeach; ?>
  </div>
</article>

<?php if (!defined('LECOLE_GRADE_TEMPLATES_RENDERED')): define('LECOLE_GRADE_TEMPLATES_RENDERED', true); ?>
<!-- Client-side HTML5 Templates sourced from modular PHP components -->
<template id="tmpl-grade-class-editor">
  <?php 
    $mode = 'add';
    $gradeId = '';
    $className = '';
    $studentCount = 30;
    $teacherName = '';
    $previewPlacement = 'right';
    require __DIR__ . '/_grade_class_editor.php'; 
  ?>
</template>

<template id="tmpl-grade-teacher-hover">
  <?php 
    $teacher = [
      'id'               => '',
      'name'             => '',
      'qualification'    => '',
      'subject'          => '',
      'classTeacher'     => '',
      'subjects'         => [],
      'extracurriculars' => []
    ];
    $placement = 'right';
    require __DIR__ . '/_grade_teacher_hover.php'; 
  ?>
</template>
<?php endif; ?>
