<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Extracurricular — L'École Management</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/form-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/calendar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-detail.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/reject-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/delete-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/notice-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/team-card.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-page-stack">

      <!-- Page Header -->
      <?php
      $title    = 'Extracurricular';
      $subtitle = 'Oversee all school athletic teams, registered societies, and review pending club proposals.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- View 1: Extracurricular Activities Overview -->
      <div id="j-view-overview">
        <!-- Search & Filter Toolbar -->
        <section class="c-extracurricular-toolbar" aria-label="Filter activities">
          <div class="c-search-field">
            <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-search"/>
            </svg>
            <input type="search" class="c-search-field__input" id="j-club-search" placeholder="Search activities (e.g. Football, Digital Arts, Cricket...)" autocomplete="off" />
          </div>

          <div class="c-segmented-tabs" role="tablist" aria-label="Activity Categories">
            <button type="button" class="c-segmented-tab is-active" role="tab" data-value="All">All</button>
            <button type="button" class="c-segmented-tab" role="tab" data-value="Sports">Sports</button>
            <button type="button" class="c-segmented-tab" role="tab" data-value="Clubs and Societies">Clubs</button>
          </div>
        </section>

        <!-- Extracurricular Cards Grid -->
        <div class="c-club-grid" id="j-club-grid">
          <?php if (!empty($canCreate)): ?>
            <button type="button" class="c-club-card--create j-open-create-club" id="j-open-create-club" aria-label="Create New Extracurricular">
              <span class="c-club-card--create__icon" aria-hidden="true">
                <svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-plus"/>
                </svg>
              </span>
              <span class="c-club-card--create__title c-font-display">Create New Extracurricular</span>
              <span class="c-club-card--create__desc">Set up a new sport, society, club, or arts program.</span>
            </button>
          <?php endif; ?>

          <?php if (!empty($clubs)): ?>
            <?php foreach ($clubs as $club): ?>
              <?php require __DIR__ . '/../components/_extracurricular_card.php'; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: rgba(15,65,74,0.6); grid-column: 1 / -1; text-align: center; padding: 3rem 0;">No activities found.</p>
          <?php endif; ?>
        </div>
      </div>


      <!-- View 3: Inside Extracurricular Card (Detail View) -->
      <div id="j-view-club-detail" style="display: none;" data-clubs="<?= htmlspecialchars(json_encode($clubs ?? []), ENT_QUOTES, 'UTF-8') ?>">
        <!-- Common Header Component -->
        <?php
        $club    = !empty($clubs) ? $clubs[0] : [];
        $canEdit = true;
        require __DIR__ . '/../components/_extracurricular_card_header.php';
        ?>

        <!-- Achievements & Gallery Section -->
        <?php
        $canCreate = true;
        require __DIR__ . '/../components/_extracurricular_achievements_panel.php';
        ?>

        <!-- Schedule & Events Section -->
        <?php
        $calendarConfig = [
            'canAddEvent'  => true,
            'initialDate'  => '2026-06-17',
            'viewDate'     => '2026-06-01',
            'events'       => [
                [
                    'id'       => 'ev-1',
                    'title'    => 'Regular Team Training',
                    'date'     => '2026-06-17',
                    'time'     => '15:30–17:30',
                    'details'  => 'Weekly training on main pitch',
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

        <!-- Teams & Roster Section (Management: can add/edit, cannot delete) -->
        <?php
        $canEdit   = true;
        $canDelete = false;
        require __DIR__ . '/../components/_extracurricular_teams_panel.php';
        ?>
      </div>

      <!-- View 4: Achievement Detail & Editor Page Component (Management: can edit, cannot delete) -->
      <?php
      $canEdit   = true;
      $canDelete = false;
      require __DIR__ . '/../components/_extracurricular_card_achievement_page.php';
      ?>


    </div>
  </main>
</div>

<!-- Reusable Reject Feedback Modal -->
<?php require_once __DIR__ . '/../components/_reject_modal.php'; ?>

<!-- Universal Delete Confirmation Modal -->
<?php require_once __DIR__ . '/../components/_delete_modal.php'; ?>

<!-- Create Extracurricular Modal (Original Admin Design) -->
<?php if (!empty($canCreate)): ?>
  <?php require_once __DIR__ . '/../components/_add_extracurricular_modal.php'; ?>
<?php endif; ?>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/calendar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/form-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/image-uploader.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-achievement.js?v=<?= time() ?>"></script>
</body>
</html>
