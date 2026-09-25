<?php
/**
 * =========================================================================
 * L'ÉCOLE — SHARED PROFILE PAGE COMPONENT
 * =========================================================================
 * Renders the full profile page body for all 5 roles.
 *
 * Expects $profileData array:
 *   'role'         => 'admin'|'teacher'|'management'|'parent'|'student'
 *   'name'         => string
 *   'id'           => string (e.g. 'ADM-001')
 *   'status'       => 'Active'|'Inactive'
 *   'avatar'       => string (asset path)
 *   'eyebrow'      => string (e.g. 'System Administrator')
 *   'sub'          => string (e.g. 'Admin Portal · L\'École School Management')
 *   'editable'     => bool (false for student)
 *   'showPassword' => bool (false for student)
 *   'tintClass'    => string CSS class for tinted section (e.g. 'c-profile-tinted--sky')
 *   'contact'      => ['email','phone','phone2'(opt)]
 *   'personal'     => array of ['label','icon','value'] info cards
 *   'roleSection'  => ['title','tintClass','items' => [['label','icon','value']]]
 *   'account'      => array of ['label','icon','value','readonly'(opt:bool)]
 * =========================================================================
 */

$pd          = $profileData ?? [];
$role        = $pd['role']        ?? 'admin';
$name        = $pd['name']        ?? 'User';
$userId      = $pd['id']          ?? '';
$status      = $pd['status']      ?? 'Active';
$avatar      = $pd['avatar']      ?? '/assets/images/admin.jpg';
$eyebrow     = $pd['eyebrow']     ?? '';
$sub         = $pd['sub']         ?? '';
$editable    = $pd['editable']    ?? true;
$showPwd     = $pd['showPassword'] ?? true;
$contact     = $pd['contact']     ?? [];
$personal    = $pd['personal']    ?? [];
$roleSection   = $pd['roleSection']   ?? null;
$extraSections = $pd['extraSections'] ?? [];
$account       = $pd['account']       ?? [];

// Role → colour map for icon section
$roleIconMap = [
  'admin'      => ['icon' => 'icon-shieldCheck',   'tint' => '--midnight, #0F414A'],
  'student'    => ['icon' => 'icon-graduationCap',  'tint' => '--skyblue, #7FC7CC'],
  'parent'     => ['icon' => 'icon-heartHandshake', 'tint' => '--terracotta, #AF5031'],
  'management' => ['icon' => 'icon-building2',      'tint' => '--maroon, #7F0303'],
  'teacher'    => ['icon' => 'icon-bookOpen',       'tint' => '--sunshine, #EA8913'],
];
$ri = $roleIconMap[$role] ?? $roleIconMap['admin'];
?>

<div class="c-profile-page <?= $editable ? '' : 'c-profile-page--readonly' ?>" id="j-profile-page">

  <!-- ======================================================================
       HERO BANNER
       ====================================================================== -->
  <?php
  $heroRole      = $role;
  $heroName      = $name;
  $heroEyebrow   = $eyebrow;
  $heroSub       = $sub;
  $heroId        = $userId;
  $heroStatus    = $status;
  $heroAvatar    = $avatar;
  $heroEditable  = $editable;
  $heroEditBtnId = 'j-profile-edit-btn';
  $heroEditLabel = 'Edit Profile';
  require __DIR__ . '/_profile_hero.php';
  ?>

  <!-- ======================================================================
       CONTACT INFORMATION
       ====================================================================== -->
  <?php if (!empty($contact)): ?>
  <div class="c-profile-section">
    <div class="c-profile-section__header">
      <div class="c-profile-section__title-row">
        <span class="c-profile-section__icon">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-phone"/>
          </svg>
        </span>
        <h2 class="c-profile-section__title">Contact Information</h2>
      </div>
    </div>
    <div class="c-profile-grid">
      <?php foreach ($contact as $field): ?>
        <?php
          $readonly = $field['readonly'] ?? false;
          $isEmail  = $field['type'] ?? '';
        ?>
        <div class="c-info-card <?= $readonly ? 'c-info-card--readonly' : '' ?>">
          <p class="c-info-card-label">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#<?= htmlspecialchars($field['icon'] ?? 'icon-info') ?>"/>
            </svg>
            <?= htmlspecialchars($field['label']) ?>
          </p>
          <p class="c-info-card-value"><?= htmlspecialchars($field['value'] ?? '—') ?></p>
          <?php if (!$readonly): ?>
            <input class="c-info-card-input"
                   type="<?= $isEmail === 'email' ? 'email' : 'text' ?>"
                   id="j-field-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $field['label']))) ?>"
                   value="<?= htmlspecialchars($field['value'] ?? '') ?>" />
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ======================================================================
       PERSONAL INFORMATION
       ====================================================================== -->
  <?php if (!empty($personal)): ?>
  <div class="c-profile-section">
    <div class="c-profile-section__header">
      <div class="c-profile-section__title-row">
        <span class="c-profile-section__icon">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-user"/>
          </svg>
        </span>
        <h2 class="c-profile-section__title">Personal Details</h2>
      </div>
    </div>
    <div class="c-profile-grid c-cols-3">
      <?php foreach ($personal as $field): ?>
        <?php 
          $readonly  = $field['readonly'] ?? true; 
          $fullWidth = $field['fullWidth'] ?? false;
        ?>
        <div class="c-info-card <?= $readonly ? 'c-info-card--readonly' : '' ?>" style="<?= $fullWidth ? 'grid-column: 1 / -1;' : '' ?>">
          <p class="c-info-card-label">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#<?= htmlspecialchars($field['icon'] ?? 'icon-info') ?>"/>
            </svg>
            <?= htmlspecialchars($field['label']) ?>
          </p>
          <p class="c-info-card-value"><?= htmlspecialchars($field['value'] ?? '—') ?></p>
          <?php if (!$readonly): ?>
            <input class="c-info-card-input"
                   type="text"
                   id="j-field-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $field['label']))) ?>"
                   value="<?= htmlspecialchars($field['value'] ?? '') ?>" />
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ======================================================================
       ROLE-SPECIFIC SECTION (e.g. teaching assignments, linked child, etc.)
       ====================================================================== -->
  <?php if (!empty($roleSection)): ?>
  <div class="c-profile-section">
    <div class="c-profile-section__header">
      <div class="c-profile-section__title-row">
        <span class="c-profile-section__icon">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#<?= htmlspecialchars($ri['icon']) ?>"/>
          </svg>
        </span>
        <h2 class="c-profile-section__title"><?= htmlspecialchars($roleSection['title']) ?></h2>
      </div>
    </div>
    <div class="c-profile-tinted <?= htmlspecialchars($roleSection['tintClass'] ?? 'c-profile-tinted--sky') ?>">
      <div class="c-profile-grid c-cols-3">
        <?php foreach ($roleSection['items'] as $field): ?>
          <?php $fullWidth = $field['fullWidth'] ?? false; ?>
          <div class="c-info-card c-info-card--readonly" style="<?= $fullWidth ? 'grid-column: 1 / -1;' : '' ?>">
            <p class="c-info-card-label">
              <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <use href="#<?= htmlspecialchars($field['icon'] ?? 'icon-info') ?>"/>
              </svg>
              <?= htmlspecialchars($field['label']) ?>
            </p>
            <p class="c-info-card-value"><?= htmlspecialchars($field['value'] ?? '—') ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ======================================================================
       ADDITIONAL SECTIONS (e.g. Residential, Emergency, Medical, etc.)
       ====================================================================== -->
  <?php if (!empty($extraSections)): ?>
    <?php foreach ($extraSections as $sec): ?>
      <div class="c-profile-section">
        <div class="c-profile-section__header">
          <div class="c-profile-section__title-row">
            <span class="c-profile-section__icon">
              <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <use href="#<?= htmlspecialchars($sec['icon'] ?? 'icon-info') ?>"/>
              </svg>
            </span>
            <h2 class="c-profile-section__title"><?= htmlspecialchars($sec['title'] ?? '') ?></h2>
          </div>
        </div>
        <div class="c-profile-grid <?= htmlspecialchars($sec['cols'] ?? 'c-cols-3') ?>">
          <?php foreach ($sec['items'] as $field): ?>
            <?php
              $readonly  = $field['readonly'] ?? true;
              $fullWidth = $field['fullWidth'] ?? false;
            ?>
            <div class="c-info-card <?= $readonly ? 'c-info-card--readonly' : '' ?>" style="<?= $fullWidth ? 'grid-column: 1 / -1;' : '' ?>">
              <p class="c-info-card-label">
                <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#<?= htmlspecialchars($field['icon'] ?? 'icon-info') ?>"/>
                </svg>
                <?= htmlspecialchars($field['label']) ?>
              </p>
              <p class="c-info-card-value"><?= htmlspecialchars($field['value'] ?? '—') ?></p>
              <?php if (!$readonly): ?>
                <input class="c-info-card-input"
                       type="text"
                       id="j-field-<?= htmlspecialchars(strtolower(str_replace([' ', '/', '.'], '-', $field['label']))) ?>"
                       value="<?= htmlspecialchars($field['value'] ?? '') ?>" />
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- ======================================================================
       ACCOUNT INFORMATION
       ====================================================================== -->
  <?php if (!empty($account)): ?>
  <div class="c-profile-section">
    <div class="c-profile-section__header">
      <div class="c-profile-section__title-row">
        <span class="c-profile-section__icon">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-lockKeyhole"/>
          </svg>
        </span>
        <h2 class="c-profile-section__title">Account Information</h2>
      </div>
      <?php if ($showPwd): ?>
        <button type="button" class="c-profile-hero__edit-btn" id="j-profile-pwd-toggle"
                style="background: var(--alabaster, #EFE8DF); color: var(--midnight, #0F414A); border-color: rgba(15,65,74,0.15);">
          Change Password
        </button>
      <?php endif; ?>
    </div>

    <div class="c-profile-grid c-cols-4">
      <?php foreach ($account as $field): ?>
        <?php $readonly = $field['readonly'] ?? true; ?>
        <div class="c-info-card <?= $readonly ? 'c-info-card--readonly' : '' ?>">
          <p class="c-info-card-label">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#<?= htmlspecialchars($field['icon'] ?? 'icon-info') ?>"/>
            </svg>
            <?= htmlspecialchars($field['label']) ?>
          </p>
          <p class="c-info-card-value"><?= htmlspecialchars($field['value'] ?? '—') ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Change Password Sub-Section -->
    <?php if ($showPwd): ?>
    <div class="c-profile-pwd" id="j-profile-pwd-section">
      <div class="c-profile-grid">
        <div class="c-profile-pwd__field">
          <label class="c-profile-pwd__label" for="j-pwd-current">Current Password</label>
          <input class="c-profile-pwd__input" type="password" id="j-pwd-current" placeholder="••••••••" autocomplete="current-password" />
        </div>
        <div class="c-profile-pwd__field">
          <label class="c-profile-pwd__label" for="j-pwd-new">New Password</label>
          <input class="c-profile-pwd__input" type="password" id="j-pwd-new" placeholder="••••••••" autocomplete="new-password" />
        </div>
        <div class="c-profile-pwd__field">
          <label class="c-profile-pwd__label" for="j-pwd-confirm">Confirm New Password</label>
          <input class="c-profile-pwd__input" type="password" id="j-pwd-confirm" placeholder="••••••••" autocomplete="new-password" />
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:0.25rem;">
        <button type="button" id="j-profile-pwd-save" class="c-btn c-btn--approve" style="font-size:0.75rem;padding:0.5rem 1.25rem;">
          Update Password
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ======================================================================
       SAVE / CANCEL ACTION ROW (only shown when editing)
       ====================================================================== -->
  <?php if ($editable): ?>
  <div class="c-profile-actions" id="j-profile-actions">
    <button type="button" class="c-btn c-btn--ghost" id="j-profile-cancel-btn">Cancel</button>
    <button type="button" class="c-btn c-btn--approve" id="j-profile-save-btn">
      <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-check"/>
      </svg>
      Save Changes
    </button>
  </div>
  <?php endif; ?>

</div>

<!-- Save Toast Notification -->
<div class="c-profile-toast" id="j-profile-toast" role="status" aria-live="polite">
  <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <use href="#icon-checkCircle2"/>
  </svg>
  <span class="j-toast-text">Changes saved.</span>
</div>
