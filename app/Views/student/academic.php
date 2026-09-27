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

    <?php 
      $recordData = $recordData ?? [
          'selectedGrade'   => (int)($selectedGrade ?? 6),
          'selectedTerm'    => 'Term 1',
          'marks'           => $academicData[6]['terms']['Term 1'] ?? [],
          'feedback'        => $academicData[6]['feedback']['Term 1']['text'] ?? '',
          'feedbackDate'    => $academicData[6]['feedback']['Term 1']['date'] ?? '',
          'feedbackTeacher' => $academicData[6]['feedback']['Term 1']['name'] ?? ''
      ];
      $isEditable = false;
      require __DIR__ . '/../components/_academic_section.php'; 
    ?>
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
