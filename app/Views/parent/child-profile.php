<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Child Profile — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/export-pdf-button.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/student-academic.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/reject-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/extracurricular-detail.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/calendar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/metric-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/achievements.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-page.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/parent-child-profile.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <div class="c-child-profile-page">

      <!-- Page Header -->
      <?php
      $title    = 'Child Profile';
      $subtitle = 'Welcome back, ' . htmlspecialchars($parentName ?? 'Mrs. Perera') . '.';
      require __DIR__ . '/../components/_page_header.php';
      ?>

      <!-- ================================================================
           CHILD IDENTITY CARD
           Reuses the exact same header markup + CSS as _person_profile_modal.php
           (.c-modal-header, .c-header-sky, .c-modal-identity, .c-id-pill etc.)
           All styles already present in profile-modal.css which is linked above.
           ================================================================ -->
      <?php
        $_sName       = $student['name']      ?? 'Nethmi Perera';
        $_sInitials   = $student['initials']  ?? 'NP';
        $_sAvatar     = $student['avatar']    ?? null;
        $_sId         = $student['index']     ?? 'S2021-091';
        $_sGrade      = $student['grade']     ?? 'Grade 6';
        $_sClass      = $student['class']     ?? '6A';
        $_sStatus     = $student['status']    ?? 'Active';
        $_sActivities = $student['activities'] ?? [];
        $_sTone       = $student['tone']      ?? 'bg-skyblue';
        $_sStatusClass = (strtolower($_sStatus) === 'active') ? 'c-status-active' : 'c-status-inactive';
      ?>
      <header class="c-modal-header c-header-sky c-child-profile-header">
        <div class="c-modal-header-row">
          <div class="c-modal-identity">
            <!-- Avatar -->
            <?php if (!empty($_sAvatar)): ?>
              <img class="c-modal-avatar" src="<?= htmlspecialchars($_sAvatar) ?>" alt="<?= htmlspecialchars($_sName) ?>" style="object-fit:cover;" />
            <?php else: ?>
              <div class="c-modal-avatar <?= htmlspecialchars($_sTone) ?>">
                <?= htmlspecialchars($_sInitials) ?>
              </div>
            <?php endif; ?>

            <!-- Name + context -->
            <div style="min-width:0;">
              <p class="c-modal-eyebrow">Student Profile</p>
              <h1 class="c-modal-name c-font-display"><?= htmlspecialchars($_sName) ?></h1>
              <p class="c-modal-subtitle"><?= htmlspecialchars($_sGrade) ?> &bull; Class <?= htmlspecialchars($_sClass) ?></p>
            </div>
          </div>
        </div>

        <!-- ID + Status + Activity pills -->
        <div class="c-modal-meta-row">
          <span class="c-id-pill" style="background:rgba(127,199,204,0.2);color:var(--midnight,#0F414A);"><?= htmlspecialchars($_sId) ?></span>
          <span class="c-status-pill <?= htmlspecialchars($_sStatusClass) ?>"><?= htmlspecialchars(strtoupper($_sStatus)) ?></span>
          <?php foreach ($_sActivities as $_act): ?>
            <span class="c-id-pill" style="background:rgba(127,199,204,0.18);color:var(--midnight,#0F414A);"><?= htmlspecialchars($_act) ?></span>
          <?php endforeach; ?>
        </div>
      </header>

      <!-- Navigation Tabs -->
      <div class="profile-tabs" role="tablist">
        <button class="tab-btn active" data-tab="information" type="button" role="tab">Information</button>
        <button class="tab-btn" data-tab="academics" type="button" role="tab">Academics</button>
        <button class="tab-btn" data-tab="sports" type="button" role="tab">Sports &amp; Clubs</button>
        <button class="tab-btn" data-tab="achievements" type="button" role="tab">Achievements</button>
      </div>

      <!-- ===================================================================
           TAB 1: INFORMATION
           Reuses _profile_information_tab.php component directly
           =================================================================== -->
      <div class="tab-panel active" id="tab-information" role="tabpanel">
        <?php 
          $renderInformationSection = 'student';
          $isEditable = false;
          $showGuardian = false;
          require __DIR__ . '/../components/_profile_information_tab.php'; 
        ?>
      </div>

      <!-- ===================================================================
           TAB 2: ACADEMICS
           Reuses _digital_record_book.php + Total Marks & Performance Trend cards
           =================================================================== -->
      <div class="tab-panel" id="tab-academics" role="tabpanel">
        <?php 
          $studentGrade = 'Grade ' . ($selectedGrade ?? 6);
          $isEditable = false;
          require __DIR__ . '/../components/_academic_section.php'; 
        ?>
      </div>

      <!-- ===================================================================
           TAB 3: SPORTS & CLUBS
           Reuses _extracurricular_card.php for each activity & opens detail page
           =================================================================== -->
      <div class="tab-panel" id="tab-sports" role="tabpanel">
        
        <!-- View A: Overview Cards Grid -->
        <div id="j-view-overview">
          <div class="sc-header">
            <h2>Sports &amp; Clubs</h2>
            <p>Browse extracurricular activities offered at school, and view active enrollments.</p>
          </div>

          <div class="sc-toolbar">
            <div class="sc-search-wrap">
              <svg class="sc-search-icon" viewBox="0 0 24 24"><use href="#icon-search"/></svg>
              <input type="text" id="sc-search-input" class="sc-search-input" placeholder="Search activities (e.g. Football, Debating...)">
            </div>
            <div class="sc-tabs" id="sc-tabs">
              <button class="sc-tab active" data-type="all" type="button">All</button>
              <button class="sc-tab" data-type="sport" type="button">Sports</button>
              <button class="sc-tab" data-type="club" type="button">Clubs</button>
            </div>
          </div>

          <div class="sc-grid" id="sc-grid">
            <?php if (!empty($childClubs)): ?>
              <?php foreach ($childClubs as $club): ?>
                <?php 
                  $currentRole = 'parent';
                  $canModerate = true;
                  require __DIR__ . '/../components/_extracurricular_card.php'; 
                ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <p class="sc-empty" id="sc-empty" hidden>No activities match your search or filters.</p>
        </div>

        <!-- View B: Inside Extracurricular Card (Detail View) -->
        <div id="j-view-club-detail" style="display: none;" data-clubs="<?= htmlspecialchars(json_encode($childClubs ?? []), ENT_QUOTES, 'UTF-8') ?>">
          <?php
          $club = null;
          if (!empty($childClubs)) {
              foreach ($childClubs as $c) {
                  if (($c['id'] ?? 0) === 2 || !empty($c['enrolled'])) {
                      $club = $c;
                      break;
                  }
              }
              if (!$club) $club = $childClubs[0];
          } else {
              $club = [];
          }
          $canEdit = false;
          require __DIR__ . '/../components/_extracurricular_card_header.php';
          ?>

          <!-- Achievements & Gallery Section -->
          <section class="c-panel" id="j-achievements-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--terracotta, #AF5031);">
                  <use href="#icon-trophy"/>
                </svg>
                <h2 class="c-font-display" style="font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0;">Achievements &amp; Gallery</h2>
              </div>
            </div>

            <!-- Achievements Grid -->
            <div class="c-achv-grid" id="j-achv-grid"></div>
            <div class="c-achv-empty-box" id="j-achv-empty" style="display: none;">No achievements recorded yet.</div>
          </section>

          <!-- Staff Section (Teacher in Charge & Coach) -->
          <?php require __DIR__ . '/../components/_student_extracurricular_staff_panel.php'; ?>

          <!-- Team & Roster (Left) + Schedule Calendar (Right) Grid -->
          <div class="sc-roster-calendar-grid">
            <div>
              <?php require __DIR__ . '/../components/_student_extracurricular_roster_panel.php'; ?>
            </div>
            <div>
              <section class="c-panel" id="j-schedule-panel" style="background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                  <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--midnight, #0F414A);">
                    <use href="#icon-calendarDays"/>
                  </svg>
                  <h2 class="c-font-display" style="font-size: 1.125rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0;">Schedule &amp; Events</h2>
                </div>
                <?php
                $calendarConfig = [
                    'canAddEvent'  => false,
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
                require __DIR__ . '/../components/_calendar.php';
                ?>
              </section>
            </div>
          </div>

          <!-- Club Notice Board Section -->
          <?php
          $canEdit  = false;
          $clubName = $club['name'] ?? 'this club';
          require __DIR__ . '/../components/_extracurricular_noticeboard.php';
          ?>

          <!-- Details & Status Section -->
          <?php require __DIR__ . '/../components/_student_extracurricular_status_panel.php'; ?>
        </div>

        <!-- Achievement Detail Modal Component -->
        <?php require __DIR__ . '/../components/_extracurricular_card_achievement_page.php'; ?>

      </div>

      <!-- ===================================================================
           TAB 4: ACHIEVEMENTS
           Reusing _metric_card.php and _achievements_timeline.php
           =================================================================== -->
      <div class="tab-panel" id="tab-achievements" role="tabpanel">
        <?php if (!empty($metrics)): ?>
          <div class="c-metrics-grid c-metrics-grid--four" style="margin-bottom: 1.5rem;">
            <?php foreach ($metrics as $card): ?>
              <?php require __DIR__ . '/../components/_metric_card.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="ach-panel" style="margin-top: 0;">
          <div class="ach-panel-header">
            <h3 class="ach-panel-title">Full Timeline</h3>
          </div>
          <?php require __DIR__ . '/../components/_achievements_timeline.php'; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Rejection Feedback Modal Component -->
<?php require __DIR__ . '/../components/_reject_modal.php'; ?>

<!-- Embedded Datasets for Dynamic Interactivity -->
<script id="j-student-data" type="application/json"><?= json_encode($student ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<script id="j-academic-data" type="application/json"><?= json_encode($academicData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/calendar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/export-pdf.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/student-academic.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-card.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/extracurricular-achievement.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/achievements.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/parent-child-profile.js?v=<?= time() ?>"></script>
</body>
</html>
