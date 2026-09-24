<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile — L'École Admin</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/profile-page.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
</head>
<body>
<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>
<div id="j-app-root" class="c-app-shell">
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>
  <main class="c-main" id="j-main">
    <div class="c-page-stack">
      <?php
        $pageTitle    = 'My Profile';
        $pageSubtitle = 'Manage your account information and credentials.';
        require __DIR__ . '/../components/_page_header.php';
        require __DIR__ . '/../components/_profile_page.php';
      ?>
    </div>
  </main>
</div>
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/profile-page.js?v=<?= time() ?>"></script>
</body>
</html>
