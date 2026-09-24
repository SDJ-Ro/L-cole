<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Character Certificates — L'École</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/character-certificate.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Management Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <!-- Universal Page Header Component (No "Overview & Certificates" text) -->
    <?php
    $title    = 'Character Certificates';
    $subtitle = 'Review, refine, and issue official character certificates for students who are leaving or graduating.';
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <div class="c-certificate-page">
      <!-- MASTER LIST VIEW -->
      <div class="c-cert-master-view" id="j-cert-master-view">
        <!-- ROW 1: Search Bar on Left, First Row of Tabs (Queue Tabs) on Right -->
        <section class="c-cert-row-primary" aria-label="Search and certificate status tabs">
          <!-- Search Input (Height, pill border & styling strictly matches the tabs) -->
          <div class="c-cert-search-box">
            <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-search"/>
            </svg>
            <input 
              type="search" 
              id="j-certificate-search" 
              placeholder="Search name, ID, or cohort..." 
              autocomplete="off" 
            />
          </div>

          <!-- First Row of Tabs: Queue / Status Tabs -->
          <nav class="c-cert-queue-tabs" role="tablist" aria-label="Primary certificate tabs">
            <button type="button" 
                    class="c-cert-tab j-cert-status-tab is-active" 
                    data-status-filter="all" 
                    role="tab" 
                    aria-selected="true">
              <span>All Certificates</span>
              <span class="c-cert-tab__count"><?= (int)($counts['all'] ?? count($certificates)) ?></span>
            </button>

            <button type="button" 
                    class="c-cert-tab j-cert-status-tab" 
                    data-status-filter="pending" 
                    role="tab" 
                    aria-selected="false">
              <span>Pending Review</span>
              <span class="c-cert-tab__count"><?= (int)($counts['pending'] ?? 0) ?></span>
            </button>

            <button type="button" 
                    class="c-cert-tab j-cert-status-tab" 
                    data-status-filter="issued" 
                    role="tab" 
                    aria-selected="false">
              <span>Issued</span>
              <span class="c-cert-tab__count"><?= (int)($counts['issued'] ?? 0) ?></span>
            </button>
          </nav>
        </section>

        <!-- ROW 2: Subtopics Row (All, Leaving, Graduating right-aligned under Row 1 tabs) -->
        <section class="c-cert-row-subtopics" aria-label="Student pathway subtopics">
          <div class="c-cert-pathway-tabs" role="group" aria-label="Student pathway subtopics filter">
            <button type="button" 
                    class="c-cert-tab c-cert-tab--subtopic j-cert-pathway-tab is-active" 
                    data-pathway-filter="all">
              <span>All</span>
            </button>

            <button type="button" 
                    class="c-cert-tab c-cert-tab--subtopic j-cert-pathway-tab" 
                    data-pathway-filter="leaving">
              <span>Leaving</span>
            </button>

            <button type="button" 
                    class="c-cert-tab c-cert-tab--subtopic j-cert-pathway-tab" 
                    data-pathway-filter="graduating">
              <span>Graduating</span>
            </button>
          </div>
        </section>

        <!-- Certificates Group Sections -->
        <?php
        $needingReview = array_filter($certificates, fn($cert) => ($cert['status'] ?? '') === 'Pending review');
        $issuedCerts   = array_filter($certificates, fn($cert) => ($cert['status'] ?? '') === 'Issued');
        ?>

        <div class="c-cert-sections-stack">
          <!-- Section: Certificates Needing Review -->
          <?php if (!empty($needingReview)): ?>
            <section class="cert-group-section j-cert-group" data-group="pending">
              <div class="cert-group-header">
                <h3 class="cert-group-title">
                  <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#icon-alertCircle"/>
                  </svg>
                  <span>Certificates needing review</span>
                </h3>
                <span class="cert-group-badge j-cert-badge-pending"><?= count($needingReview) ?> Pending Review</span>
              </div>

              <div class="cert-grid">
                <?php foreach ($needingReview as $certificate): ?>
                  <?php require __DIR__ . '/../components/_certificate_student_card.php'; ?>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <!-- Section: Verified Certificates -->
          <?php if (!empty($issuedCerts)): ?>
            <section class="cert-group-section j-cert-group" data-group="issued">
              <div class="cert-group-header">
                <h3 class="cert-group-title cert-group-title--verified">
                  <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#icon-shieldCheck"/>
                  </svg>
                  <span>Verified Certificates</span>
                </h3>
                <span class="cert-group-badge cert-group-badge--verified j-cert-badge-issued"><?= count($issuedCerts) ?> Verified</span>
              </div>

              <div class="cert-grid">
                <?php foreach ($issuedCerts as $certificate): ?>
                  <?php require __DIR__ . '/../components/_certificate_student_card.php'; ?>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <!-- Empty State -->
          <div class="empty-cert" id="j-cert-empty">
            No certificates match these filters.
          </div>
        </div>
      </div>

      <!-- DETAIL VIEW & A4 CERTIFICATE DOCUMENT WORKSPACE -->
      <?php require __DIR__ . '/../components/_certificate_workspace.php'; ?>
    </div>
  </main>
</div>

<!-- Certificate Modals Component (Requests Drawer + Evidence Lightbox) -->
<?php require_once __DIR__ . '/../components/_certificate_modals.php'; ?>

<!-- Seed Data Payload for Client-side Reactivity -->
<script>
  window.CHARACTER_CERTIFICATES = <?= json_encode(array_values($certificates), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/export-pdf.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/character-certificate.js?v=<?= time() ?>"></script>
</body>
</html>
