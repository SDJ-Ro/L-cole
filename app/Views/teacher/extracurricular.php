<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Extracurricular — L'École Teacher Portal</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/calendar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-detail.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/reject-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/delete-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/notice-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/metric-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/teacher-join-request-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/team-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-page-stack">

      <!-- Inside Extracurricular Card (Teacher Direct Assigned Activity View) -->
      <div id="j-view-club-detail" style="display: block;" data-clubs="<?= htmlspecialchars(json_encode($clubs ?? []), ENT_QUOTES, 'UTF-8') ?>">
        <!-- Common Header Component (No back link for teacher) -->
        <?php
        $showBackLink = false;
        $canEdit      = true;
        require __DIR__ . '/../components/_extracurricular_card_header.php';
        ?>

        <!-- Achievements & Gallery Section -->
        <?php require __DIR__ . '/../components/_extracurricular_achievements_panel.php'; ?>

        <!-- =====================================================================
             ENROLLMENT PROCESSING SECTION (Teacher TIC / Head Coach Only)
             ===================================================================== -->
        <section class="c-panel" id="j-enrollment-processing-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
          <div class="c-panel__heading-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <span class="c-panel__heading-icon">
                <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--midnight, #0F414A);">
                  <use href="#icon-users"/>
                </svg>
              </span>
              <div>
                <h2 class="c-panel__title c-font-display" style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A);">
                  Enrollment Processing
                </h2>
                <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: rgba(15, 65, 74, 0.65); font-weight: 500;">
                  <?= htmlspecialchars($club['name'] ?? 'Cricket Club') ?> &middot; Students cannot be added without confirmed parental approval.
                </p>
              </div>
            </div>
            <span class="c-tag c-tag--category" style="font-size: 0.75rem; font-weight: 700;">
              Faculty Moderation
            </span>
          </div>

          <!-- 1. Metric Cards Component Grid -->
          <?php if (!empty($enrollmentMetrics)): ?>
            <div class="c-metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
              <?php foreach ($enrollmentMetrics as $card): ?>
                <?php require __DIR__ . '/../components/_metric_card.php'; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- 2. Horizontal Scrolling Carousel of Join Request Cards -->
          <div class="c-teacher-join-carousel" id="j-teacher-join-carousel">
            <?php if (!empty($joinRequests)): ?>
              <?php foreach ($joinRequests as $cardIndex => $request): ?>
                <?php
                  $clubName = $club['name'] ?? 'Cricket Club';
                  require __DIR__ . '/../components/_teacher_extracurricular_join_request_card.php';
                ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Empty state when all requests have been processed -->
          <div class="c-empty-box" id="j-teacher-join-empty" style="display: <?= empty($joinRequests) ? 'block' : 'none' ?>; text-align: center; padding: 2.5rem 1rem; border: 1px dashed var(--color-border, #EFE8DF); border-radius: var(--radius-xl, 0.875rem); background: rgba(247, 243, 236, 0.4); margin-top: 0.5rem;">
            <svg class="c-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.35; margin-bottom: 0.5rem; color: var(--midnight, #0F414A);">
              <use href="#icon-check"/>
            </svg>
            <p style="margin: 0; color: rgba(15, 65, 74, 0.7); font-size: 0.9375rem; font-weight: 600;">All enrollment requests processed.</p>
            <p style="margin: 0.25rem 0 0; color: rgba(15, 65, 74, 0.5); font-size: 0.8125rem;">New student applications to join this program will appear here.</p>
          </div>
        </section>

        <!-- Schedule & Events Section -->
        <?php
        $calendarConfig = [
            'canAddEvent'  => !empty($canCreate),
            'initialDate'  => '2026-06-17',
            'viewDate'     => '2026-06-01',
            'events'       => [
                [
                    'id'       => 'ev-1',
                    'title'    => 'Weekly Team Training',
                    'date'     => '2026-06-17',
                    'time'     => '15:30–17:30',
                    'details'  => 'Weekly training on main pitch',
                    'category' => 'Extracurricular'
                ],
                [
                    'id'       => 'ev-2',
                    'title'    => 'Upcoming Friendly Tournament',
                    'date'     => '2026-06-25',
                    'time'     => '14:30–17:00',
                    'details'  => 'Friendly warmup fixture',
                    'category' => 'Extracurricular'
                ]
            ],
        ];
        $canEdit = true;
        require __DIR__ . '/../components/_extracurricular_schedule_panel.php';
        ?>

        <!-- Club Notice Board Section -->
        <?php
        $canEdit  = true;
        $clubName = $club['name'] ?? 'this club';
        require __DIR__ . '/../components/_extracurricular_noticeboard.php';
        ?>

        <!-- Teams & Roster Section -->
        <?php
        $canEdit   = true;
        $canDelete = false; // Teachers edit teams but can't delete the program card
        require __DIR__ . '/../components/_extracurricular_teams_panel.php';
        ?>
      </div>

      <!-- View 4: Achievement Detail & Editor Page Component -->
      <?php require __DIR__ . '/../components/_extracurricular_card_achievement_page.php'; ?>


    </div>
  </main>
</div>

<!-- Reusable Reject Feedback Modal -->
<?php require_once __DIR__ . '/../components/_reject_modal.php'; ?>

<!-- Universal Delete Confirmation Modal -->
<?php require_once __DIR__ . '/../components/_delete_modal.php'; ?>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/calendar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/form-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/image-uploader.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-achievement.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/teacher-extracurricular.js?v=<?= time() ?>"></script>
</body>
</html>
