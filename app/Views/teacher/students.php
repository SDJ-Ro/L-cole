<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Details — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/people-directory.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <?php
    ob_start();
    ?>
    <div class="c-search-field" style="min-width: 16rem;">
      <svg class="c-icon c-search-field__icon" width="16" height="16"><use href="#icon-search"/></svg>
      <input type="search" id="j-teacher-student-search" class="c-search-field__input" placeholder="Search students..." autocomplete="off" />
    </div>
    <?php
    $action   = ob_get_clean();
    $title    = 'Student Details';
    $subtitle = 'Find students and parents by the details that matter.';
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <!-- Main People Directory Panel (Option A: 100% Reused Component with Teacher Context) -->
    <?php
    $context = 'teacher';
    require __DIR__ . '/../components/_people_directory_panel.php';
    ?>
  </main>
</div>

<!-- Reused Person Profile Modal (Teacher View-Only: Editing Disabled) -->
<?php 
$allowEdit = false;
require __DIR__ . '/../components/_person_profile_modal.php'; 
?>

<!-- Embedded Datasets for Interactive Filtering and Modal View -->
<script id="j-teacher-students-data" type="application/json"><?= json_encode([
    'grades'        => $grades,
    'classContext'  => $classContext,
    'enrollments'   => $enrollments,
    'activeGradeId' => $activeGradeId,
    'activeClass'   => $activeClass,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>

<script id="j-people-data" type="application/json"><?= json_encode([
    'students'   => $students,
    'teachers'   => [],
    'parents'    => [],
    'management' => [],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>

<script>
  window.__PEOPLE_DATA__ = {
    grades: <?= json_encode($grades ?? []) ?>,
    context: <?= json_encode($classContext ?? []) ?>,
    enrollments: <?= json_encode($enrollments ?? []) ?>,
    students: <?= json_encode($students ?? []) ?>,
    teachers: [],
    parents: [],
    management: []
  };
</script>

<!-- Component Scripts -->
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/profile-modal.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/teacher-students.js?v=<?= time() ?>"></script>
</body>
</html>
