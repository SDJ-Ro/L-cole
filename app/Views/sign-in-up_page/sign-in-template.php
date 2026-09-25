<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>" />
  <title><?= htmlspecialchars($title ?? "Sign in — L'École") ?></title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/auth-shared.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div class="auth-shell">
  <aside class="auth-visual">
    <img src="<?= htmlspecialchars($image ?? '/assets/images/schoolyard.jpg') ?>" alt="<?= htmlspecialchars($imageAlt ?? "L'École Campus") ?>" />
    <div class="auth-visual-scrim"></div>
    <div class="auth-visual-inner">
      <a class="auth-back-link" href="/landing">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg> 
        L'École home
      </a>
      <div>
        <span class="auth-visual-badge">
          <svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#<?= htmlspecialchars($badgeIcon ?? 'icon-graduationCap') ?>"/></svg>
        </span>
        <p class="auth-visual-eyebrow"><?= htmlspecialchars($eyebrow ?? 'Welcome') ?></p>
        <h2 class="auth-visual-headline"><?= htmlspecialchars($headline ?? '') ?></h2>
      </div>
      <p class="auth-visual-copyright">&copy; 2026 L'École. All rights reserved.</p>
    </div>
  </aside>

  <section class="auth-form-section">
    <div class="auth-form-col">
      <div class="auth-mobile-top">
        <a class="auth-mobile-brand" href="/landing">
          <span class="auth-mobile-brand-badge"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
          L'École
        </a>
        <a class="auth-mobile-home" href="/landing">Home</a>
      </div>

      <a href="/auth" class="auth-role-switch-btn">
        <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>
        Choose a different role
      </a>

      <div class="auth-head">
        <p class="auth-eyebrow"><?= htmlspecialchars($eyebrow ?? '') ?></p>
        <h1 class="auth-title"><?= htmlspecialchars($formTitle ?? (isset($title) ? explode(' — ', $title)[0] : 'Sign in')) ?></h1>
        <p class="auth-desc"><?= htmlspecialchars($desc ?? '') ?></p>
      </div>

      <form class="auth-form" id="sign-in-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>" />
        <?php if (!empty($noticeText)): ?>
          <div class="auth-notice">
            <span class="auth-notice-icon" style="<?= htmlspecialchars($noticeBgStyle ?? 'background:var(--midnight);color:#fff;') ?>">
              <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#<?= htmlspecialchars($noticeIcon ?? 'icon-lockKeyhole') ?>"/></svg>
            </span>
            <p class="auth-notice-text"><?= htmlspecialchars($noticeText) ?></p>
          </div>
        <?php endif; ?>

        <p class="form-alert"></p>
        <p class="form-success">
          <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg>
          <span>Signed in successfully. Redirecting to your dashboard&hellip;</span>
        </p>

        <input type="hidden" name="role" value="<?= htmlspecialchars($role ?? '') ?>" />
        <div class="field-group">
          <div class="field">
            <span class="field-label"><?= htmlspecialchars($inputLabel ?? 'Email address') ?></span>
            <input type="<?= htmlspecialchars($inputType ?? 'text') ?>" name="identifier" class="field-input" placeholder="<?= htmlspecialchars($inputPlaceholder ?? 'name@lecole.edu') ?>" required autocomplete="username" />
          </div>
          <div class="field">
            <div class="field-label-row">
              <span class="field-label" style="margin-bottom:0;">Password</span>
              <button type="button" class="field-forgot-btn j-forgot-trigger">Forgot password?</button>
            </div>
            <div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;">
              <input type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password" style="width:100%;padding-right:2.75rem;" />
              <button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button>
            </div>
          </div>
        </div>

        <div class="auth-form-footer">
          <?php if (!empty($signupUrl)): ?>
            <a class="auth-need-access" href="<?= htmlspecialchars($signupUrl) ?>"><?= htmlspecialchars($signupLabel ?? 'Need access? Sign up') ?></a>
          <?php elseif (!empty($isAudited)): ?>
            <span class="auth-audited-tag">
              <svg class="icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-shieldCheck"/></svg>
              Access is audited
            </span>
          <?php else: ?>
            <span></span>
          <?php endif; ?>

          <button type="submit" class="auth-submit-btn">
            <?= htmlspecialchars($submitLabel ?? 'Sign in') ?>
            <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>
          </button>
        </div>
      </form>

      <?php if (!empty($signupUrl) && !empty($alternateAction)): ?>
        <p class="auth-alternate"><?= htmlspecialchars($alternateText ?? "New to L'École?") ?> <a href="<?= htmlspecialchars($signupUrl) ?>"><?= htmlspecialchars($alternateAction) ?></a></p>
      <?php endif; ?>

      <?php if (!empty($footnote)): ?>
        <p class="auth-footnote"><?= $footnote ?></p>
      <?php endif; ?>
    </div>
  </section>
</div>

<div id="fp-modal-root"></div>
<script src="/assets/js/auth-shared.js?v=<?= time() ?>"></script>
<script>
  LECOLE_AUTH.injectForgotPasswordModal('');
  LECOLE_AUTH.wireAuthForm('sign-in-form');
</script>
</body>
</html>
