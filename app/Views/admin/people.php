<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>People Directory — L'École Admin</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/people-directory.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/datepicker.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/export-pdf-button.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-page-stack">

      <!-- Page Header with Top-Right Role Tablist Capsule -->
      <?php
      $tabs = $allowedTabs ?? ['Students', 'Teachers', 'Parents', 'Management Panel'];
      $curr = $activeTab ?? 'Students';
      ob_start();
      ?>
      <div class="c-tablist" role="tablist" id="j-directory-tablist" aria-label="Directory user roles">
        <?php foreach ($tabs as $tab): ?>
          <?php
          $isActive = ($tab === $curr);
          $toneClass = match($tab) {
              'Students'         => 'c-tone-sky',
              'Teachers'         => 'c-tone-sunshine',
              'Parents'          => 'c-tone-terracotta',
              'Management Panel' => 'c-tone-maroon',
              default            => 'c-tone-sky',
          };
          ?>
          <button type="button" role="tab"
                  class="c-tab-btn j-directory-tab <?= $isActive ? 'is-active-tab ' . $toneClass : '' ?>"
                  data-tab="<?= htmlspecialchars($tab) ?>"
                  aria-selected="<?= $isActive ? 'true' : 'false' ?>">
            <span><?= htmlspecialchars($tab) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
      <?php
      $headerAction = ob_get_clean();
      $action       = $headerAction;
      $title        = 'Users Directory';
      $subtitle     = 'Find students, staff, and families by the details that matter.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- People Directory Organism Panel -->
      <?php require __DIR__ . '/../components/_people_directory_panel.php'; ?>

      <!-- Add Person Form Pages (Hidden initially, shown on "+ Add [Role]" button click) -->
      <?php
      $role = 'student';
      require __DIR__ . '/../components/_add_person_page.php';

      $role = 'teacher';
      require __DIR__ . '/../components/_add_person_page.php';

      $role = 'parent';
      require __DIR__ . '/../components/_add_person_page.php';

      $role = 'management';
      require __DIR__ . '/../components/_add_person_page.php';
      ?>

      <!-- Master Person Profile Modal Component (View & Edit) -->
      <?php require __DIR__ . '/../components/_person_profile_modal.php'; ?>

    </div>
  </main>
</div>

<!-- Embedded context dataset for client-side interactions -->
<script>
  window.__PEOPLE_DATA__ = {
    grades: <?= json_encode($grades ?? []) ?>,
    context: <?= json_encode($classContext ?? []) ?>,
    enrollments: <?= json_encode($classEnrollments ?? []) ?>,
    students: <?= json_encode($students ?? []) ?>,
    teachers: <?= json_encode($teachers ?? []) ?>,
    parents: <?= json_encode($parents ?? []) ?>,
    management: <?= json_encode($management ?? []) ?>
  };
</script>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/datepicker.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/people-directory.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/profile-modal.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/export-pdf.js?v=<?= time() ?>"></script>
</body>
</html>
