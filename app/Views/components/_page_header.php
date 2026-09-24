<?php
/**
 * =========================================================================
 * L'ÉCOLE — PAGE HEADER COMPONENT
 * =========================================================================
 * Standard top header across all secondary pages in all 5 roles.
 * 
 * Expects:
 *   - $pageTitle / $title       : string (required) Primary page heading
 *   - $pageSubtitle / $subtitle : string (optional) Supporting description
 *   - $actionButton / $action   : string (optional HTML) Action button (e.g. "Post Notice")
 * =========================================================================
 */

$headerTitle    = $pageTitle ?? $title ?? '';
$headerSubtitle = $pageSubtitle ?? $subtitle ?? '';
$headerAction   = $actionButton ?? $action ?? $headerAction ?? '';
?>

<header class="c-page-header">
  <div class="c-page-header__meta">
    <h1 class="c-page-header__title c-font-display"><?= htmlspecialchars($headerTitle) ?></h1>
    <?php if (!empty($headerSubtitle)): ?>
      <p class="c-page-header__subtitle"><?= htmlspecialchars($headerSubtitle) ?></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($headerAction)): ?>
    <div class="c-page-header__actions">
      <?= $headerAction ?>
    </div>
  <?php endif; ?>
</header>
