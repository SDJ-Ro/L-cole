<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>People Directory - L'École Management</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/people-directory.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/datepicker.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/export-pdf-button.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/parent-picker.css?v=<?= time() ?>">
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
      $tabs = $allowedTabs ?? ['Students', 'Teachers', 'Parents'];
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

      <?php if ($notice = $this->getFlash('admission_success')): ?>
        <p role="status" style="padding:1rem;background:#e8f2ed;border-radius:8px"><?= e($notice) ?></p>
      <?php endif; ?>
      <!-- People Directory Organism Panel -->
      <?php require __DIR__ . '/../components/_people_directory_panel.php'; ?>

      <!-- Add Person Form Pages (Hidden initially, shown on "+ Add [Role]" button click) -->
      <?php
      $role = 'student';
      require __DIR__ . '/../components/_add_person_page.php';

      $role = 'teacher';
      require __DIR__ . '/../components/_add_person_page.php';

      ?>

      <!-- Master Person Profile Modal Component (View & Edit) -->
      <?php require __DIR__ . '/../components/_person_profile_modal.php'; ?>

    </div>
  </main>
</div>

<dialog id="parent-enrolment-dialog" aria-labelledby="parent-enrolment-title" aria-describedby="parent-enrolment-description" style="max-width:460px;width:calc(100% - 2rem);border:0;border-radius:12px;padding:1.5rem;color:#0f414a">
  <h2 id="parent-enrolment-title">Enrol a student to add a parent</h2>
  <p id="parent-enrolment-description">A new parent must be linked to a student. Continue to Student Enrolment, where you can enter the student and parent details together.</p>
  <p>For an existing student, open their record and use Change Guardian.</p>
  <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;flex-wrap:wrap">
    <button type="button" id="cancel-parent-enrolment" class="c-btn-outline-sky" autofocus>Cancel</button>
    <button type="button" id="continue-parent-enrolment" class="c-btn-accent c-tone-terracotta">Continue to Enrolment</button>
  </div>
</dialog>
<?php require __DIR__ . '/../components/_parent_picker.php'; ?>
<?php require __DIR__ . '/../components/_deactivate_parent_dialog.php'; ?>
<!-- Embedded context dataset for client-side interactions -->
<script>
  window.__PEOPLE_DATA__ = {
    grades: <?= json_encode($grades ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    context: <?= json_encode($classContext ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    enrollments: <?= json_encode($classEnrollments ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    students: <?= json_encode($students ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    teachers: <?= json_encode($teachers ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    parents: <?= json_encode($parents ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    management: <?= json_encode($management ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
  };
</script>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/datepicker.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/people-directory.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/profile-modal.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/export-pdf.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/parent-picker.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/parent-deactivation.js?v=<?= time() ?>"></script>
</body>
</html>
