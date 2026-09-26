<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sports &amp; Clubs — L'École Student Portal</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/calendar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-detail.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
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
      $subtitle = 'Browse every sport and club offered at school, and manage your enrollments.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- View 1: Extracurricular Activities Overview -->
      <div id="j-view-overview" style="<?= !empty($_GET['id']) ? 'display: none;' : '' ?>">
        <!-- Search & Filter Toolbar -->
        <section class="c-extracurricular-toolbar" aria-label="Filter activities">
          <div class="c-search-field">
            <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-search"/>
            </svg>
            <input type="search" class="c-search-field__input" id="j-club-search" placeholder="Search activities (e.g. Cricket, Debating, Football...)" autocomplete="off" />
          </div>

          <div class="c-segmented-tabs" role="tablist" aria-label="Activity Categories">
            <button type="button" class="c-segmented-tab is-active" role="tab" data-value="All">All</button>
            <button type="button" class="c-segmented-tab" role="tab" data-value="Sports">Sports</button>
            <button type="button" class="c-segmented-tab" role="tab" data-value="Clubs and Societies">Clubs</button>
          </div>
        </section>

        <!-- Extracurricular Cards Grid -->
        <div class="c-club-grid" id="j-club-grid">
          <?php if (!empty($clubs)): ?>
            <?php foreach ($clubs as $club): ?>
              <?php require __DIR__ . '/../components/_extracurricular_card.php'; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: rgba(15,65,74,0.6); grid-column: 1 / -1; text-align: center; padding: 3rem 0;">No activities found.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- View 3: Inside Extracurricular Card (Detail View for Enrolled Activities) -->
      <div id="j-view-club-detail" style="<?= !empty($_GET['id']) ? 'display: block;' : 'display: none;' ?>" data-clubs="<?= htmlspecialchars(json_encode($clubs ?? []), ENT_QUOTES, 'UTF-8') ?>">
        <!-- Common Header Component (Read-only for Students) -->
        <?php
        $selectedId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
        $club = !empty($club) ? $club : null;
        if (empty($club) && !empty($clubs)) {
            if ($selectedId > 0) {
                foreach ($clubs as $c) {
                    if (($c['id'] ?? 0) === $selectedId) {
                        $club = $c;
                        break;
                    }
                }
            }
            if (!$club) {
                $club = $clubs[0];
            }
        } elseif (empty($club)) {
            $club = [];
        }
        $canEdit = false;
        require __DIR__ . '/../components/_extracurricular_card_header.php';
        ?>

        <!-- Achievements & Gallery Section -->
        <?php require __DIR__ . '/../components/_extracurricular_achievements_panel.php'; ?>

        <!-- View 2.5: Student Staff Section (Teacher in Charge & Coach / Instructor Cards) -->
        <?php require __DIR__ . '/../components/_student_extracurricular_staff_panel.php'; ?>

        <!-- View 3: Team & Roster (Left) + Schedule & Events Calendar (Right) Grid -->
        <div class="sc-roster-calendar-grid">
          <!-- Left Column: Team & Roster Component -->
          <div>
            <?php require __DIR__ . '/../components/_student_extracurricular_roster_panel.php'; ?>
          </div>

          <!-- Right Column: Schedule & Events (Calendar Component) -->
          <div>
            <section class="c-panel" id="j-schedule-panel" style="background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--midnight, #0F414A);">
                  <use href="#icon-calendarDays"/>
                </svg>
                <h2 class="c-font-display" style="font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0;">Schedule &amp; Events</h2>
              </div>

              <!-- Calendar Component (Read-only for Students) -->
              <?php
              $calendarConfig = [
                  'canAddEvent' => false,
                  'initialDate' => date('Y-m-d'),
                  'viewDate'    => date('Y-m-01'),
                  'events'      => ($scopeType ?? 'club') === 'sport'
                      ? CalendarEventModel::getEventsForSport((int)($club['id'] ?? 0))
                      : CalendarEventModel::getEventsForClub((int)($club['id'] ?? 0)),
              ];
              require __DIR__ . '/../components/_calendar.php';
              ?>
            </section>
          </div>
        </div>

        <!-- Club Notice Board Section (read-only for students) -->
        <?php
        $canEdit  = false;
        $clubName = $club['name'] ?? 'this club';
        require __DIR__ . '/../components/_extracurricular_noticeboard.php';
        ?>

        <!-- View 3.5: Details & Status Section (Schedule, Location, Enrolled Since, Date) -->
        <?php require __DIR__ . '/../components/_student_extracurricular_status_panel.php'; ?>
      </div>

      <!-- View 4: Achievement Detail Page Component -->
      <?php require __DIR__ . '/../components/_extracurricular_card_achievement_page.php'; ?>

    </div>
  </main>
</div>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/calendar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-achievement.js?v=<?= time() ?>"></script>
</body>
</html>
