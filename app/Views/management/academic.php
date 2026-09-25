<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Academic Overview — L'École <?= ucfirst($currentRole ?? 'management') ?></title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/bar-chart.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/calendar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/academic.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/grade-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/curriculum-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/delete-modal.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-main-inner">
      <div class="c-main-container">
        <div class="c-page-stack c-page-stack--academic">

          <?php
          $title    = 'Academic Overview';
          $subtitle = 'Manage curriculum structure, assessment progress, and term schedules.';
          require __DIR__ . '/../components/_page_header.php';
          ?>

          <!-- ---------------------------------------------------------
               SECTION 1: CLASS SECTION PERFORMANCE + TERM 2 EXAM CALENDAR
               --------------------------------------------------------- -->
          <div class="c-primary-grid" data-academic-perf='<?= htmlspecialchars(json_encode($academicDataset ?? []), ENT_QUOTES, "UTF-8") ?>'>
            <!-- Column Chart -->
            <?php if (!empty($chartConfig)): 
              ob_start();
              ?>
              <div class="c-performance-card__filters">
                <?php
                  $dropdownClass = 'c-select--cream';
                  $dropdownId    = 'j-select-perf-grade';
                  $dropdownLabel = 'Grade';
                  $placeholder   = 'Grade 6';
                  $selectedValue = 'Grade 6';
                  $options       = array_column($grades ?? [], 'name');
                  require __DIR__ . '/../components/_dropdown.php';

                  $dropdownClass = 'c-select--cream';
                  $dropdownId    = 'j-select-perf-subject';
                  $dropdownLabel = 'Subject';
                  $placeholder   = 'Mathematics';
                  $selectedValue = 'Mathematics';
                  $options       = $subjects ?? [];
                  require __DIR__ . '/../components/_dropdown.php';

                  $dropdownClass = 'c-select--cream';
                  $dropdownId    = 'j-select-perf-term';
                  $dropdownLabel = 'Term';
                  $placeholder   = 'Term 1';
                  $selectedValue = 'Term 1';
                  $options       = $terms ?? [];
                  require __DIR__ . '/../components/_dropdown.php';
                ?>
              </div>
              <?php
              $chartConfig['headerExtra'] = ob_get_clean();
              require __DIR__ . '/../components/_bar_chart.php'; 
            endif; ?>

            <!-- Calendar -->
            <?php if (!empty($calendarConfig)): ?>
              <?php require __DIR__ . '/../components/_calendar.php'; ?>
            <?php endif; ?>
          </div>

          <!-- ---------------------------------------------------------
               SECTION 2: GRADE & CLASS STRUCTURE
               --------------------------------------------------------- -->
          <section class="c-structure-section">
            <div class="c-structure-section__head">
              <div class="c-structure-section__head-left">
                <div class="c-structure-section__icon-badge" aria-hidden="true">
                  <svg class="c-icon" width="19" height="19"><use href="#icon-graduationCap"/></svg>
                </div>
                <div>
                  <h2 class="c-structure-section__title">Grade & class structure</h2>
                </div>
              </div>

              <button type="button" class="c-btn-add c-btn-add--small j-open-add-grade" id="j-open-add-grade">
                <svg class="c-icon" width="13" height="13"><use href="#icon-plus"/></svg>
                Add grade
              </button>
            </div>

            <div class="c-grade-grid j-grade-grid" id="j-grade-grid">
              <?php if (!empty($grades)): ?>
                <?php foreach ($grades as $gradeIndex => $grade): ?>
                  <?php require __DIR__ . '/../components/_grade_card.php'; ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </section>

          <!-- ---------------------------------------------------------
               SECTION 3: CURRICULUM SUBJECTS
               --------------------------------------------------------- -->
          <section class="c-curriculum-section">
            <div class="c-curriculum-section__head">
              <div class="c-curriculum-section__head-left">
                <div class="c-curriculum-section__icon-badge" aria-hidden="true">
                  <svg class="c-icon" width="18" height="18"><use href="#icon-bookOpen"/></svg>
                </div>
                <div>
                  <h2 class="c-curriculum-section__title">Curriculum subjects</h2>
                </div>
              </div>

              <button type="button" class="c-btn-add c-btn-add--small j-add-curriculum-btn" id="j-add-curriculum-btn">
                <svg class="c-icon" width="13" height="13"><use href="#icon-plus"/></svg>
                Add curriculum
              </button>
            </div>

            <div class="c-curriculum-grid j-curriculum-grid" id="j-curriculum-grid">
              <?php if (!empty($curriculumGroups)): ?>
                <?php foreach ($curriculumGroups as $stageIndex => $stage): ?>
                  <?php require __DIR__ . '/../components/_curriculum_card.php'; ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </section>

        </div>
      </div>
    </div>
  </main>
</div>

<!-- Universal Delete Confirmation Modal -->
<?php require_once __DIR__ . '/../components/_delete_modal.php'; ?>

<!-- Add Grade Modal -->
<?php require_once __DIR__ . '/../components/_add_grade_modal.php'; ?>

<script>
  window.LECOLE_CURRENT_ROLE = <?= json_encode($currentRole ?? 'management') ?>;
  window.LECOLE_STAFF_DIRECTORY = <?= json_encode($staffAssignments ?? []) ?>;
  window.LECOLE_CSRF_TOKEN = <?= json_encode($csrf_token ?? '') ?>;
</script>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/bar-chart.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/calendar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/teacher-hover.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/grade-card.js?v=<?= time() ?>"></script>
</body>
</html>
