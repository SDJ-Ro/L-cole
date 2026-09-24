<?php
// MVC/app/Views/components/_sidebar.php
// Reusable sidebar component driven by config/roles.php
// Expects: $currentRole (string), $currentRoute (string)

if (!isset($roleConfig)) {
    $allRoles = require __DIR__ . '/../../../config/roles.php';
    $roleConfig = $allRoles[$currentRole ?? 'admin'] ?? [];
}
?>
<aside class="c-sidebar" id="j-sidebar" aria-label="Primary navigation" data-role="<?= htmlspecialchars($currentRole ?? 'admin') ?>">
  <div class="c-sidebar__brand">
    <a href="<?= htmlspecialchars($roleConfig['homeHref'] ?? '/') ?>" id="j-brand-home-link" class="c-sidebar__brand-link">
      <div class="c-sidebar__brand-mark" aria-hidden="true">
        <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#<?= htmlspecialchars($roleConfig['badgeIcon'] ?? 'icon-graduationCap') ?>"/>
        </svg>
      </div>
      <div class="c-sidebar__brand-text j-collapsible-text">
        <h1 class="c-sidebar__brand-title"><?= htmlspecialchars($roleConfig['title'] ?? "L'École") ?></h1>
        <p class="c-sidebar__brand-subtitle"><?= htmlspecialchars($roleConfig['subtitle'] ?? '') ?></p>
      </div>
    </a>
    <button type="button" id="j-sidebar-toggle" class="c-sidebar__collapse-btn" aria-label="Collapse navigation">
      <svg class="c-icon j-collapse-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-chevronLeft"/>
      </svg>
    </button>
  </div>

  <nav class="c-sidebar__nav" aria-label="Main menu">
    <p class="c-sidebar__nav-label j-collapsible-text">Main Menu</p>
    <ul class="c-sidebar__nav-list j-nav-list">
      <?php if (!empty($roleConfig['nav'])): ?>
        <?php foreach ($roleConfig['nav'] as $item): 
          $isSelected = (isset($currentRoute) && $currentRoute === $item['href']);
        ?>
          <li>
            <a href="<?= htmlspecialchars($item['href']) ?>" 
               class="c-nav-item j-nav-item <?= $isSelected ? 'c-is-selected' : '' ?>" 
               data-nav-name="<?= htmlspecialchars($item['dataNavName']) ?>" 
               <?= $isSelected ? 'aria-pressed="true"' : 'aria-pressed="false"' ?>>
              <span class="c-nav-item__pill" aria-hidden="true"></span>
              <span class="c-nav-item__icon" aria-hidden="true">
                <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#<?= htmlspecialchars($item['icon']) ?>"/>
                </svg>
              </span>
              <span class="c-nav-item__label j-collapsible-text"><?= htmlspecialchars($item['label']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </nav>

  <?php if (!empty($roleConfig['profile'])): 
    $profile = $roleConfig['profile'];
    $isProfileSelected = (isset($currentRoute) && $currentRoute === $profile['href']);
  ?>
    <div class="c-sidebar__profile-wrap">
      <a href="<?= htmlspecialchars($profile['href']) ?>" 
         class="c-profile-btn j-nav-item <?= $isProfileSelected ? 'c-is-selected' : '' ?>" 
         data-nav-name="Profile">
        <img class="c-profile-btn__avatar" alt="<?= htmlspecialchars($profile['name']) ?>" src="<?= htmlspecialchars($profile['avatar']) ?>" />
        <div class="c-profile-btn__meta j-collapsible-text">
          <p class="c-profile-btn__name"><?= htmlspecialchars($profile['name']) ?></p>
        </div>
        <span class="c-profile-btn__status-dot j-collapsible-text" aria-label="Account active"></span>
      </a>

      <a href="/auth" class="c-logout-btn logout-link j-logout-link" id="j-logout-btn" title="Logout" aria-label="Logout">
        <span class="c-logout-btn__icon" aria-hidden="true">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-logOut"/>
          </svg>
        </span>
        <span class="c-logout-btn__label j-collapsible-text">Logout</span>
      </a>
    </div>
  <?php endif; ?>
</aside>
