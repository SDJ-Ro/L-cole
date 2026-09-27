<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>" />
  <title><?= htmlspecialchars($title ?? "Sign up — L'École") ?></title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/auth-shared.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<a class="gw-back-btn" href="/auth"><svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>Who's signing in</a>

<div class="auth-shell">
  <aside class="auth-visual">
    <img src="<?= htmlspecialchars($image ?? '/assets/images/schoolyard.jpg') ?>" alt="<?= htmlspecialchars($imageAlt ?? "L'École Campus") ?>" />
    <div class="auth-visual-scrim"></div>
    <div class="auth-visual-inner">
      <a class="auth-back-link" href="/auth">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg> 
        Who's signing in
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
        <a class="auth-mobile-brand" href="/auth">
          <span class="auth-mobile-brand-badge" style="background: transparent !important; box-shadow: none !important;"><img src="/assets/images/logo.png" alt="L'École Crest" style="width: 100%; height: 100%; object-fit: contain;" /></span>
          L'École
        </a>
        <a class="auth-mobile-home" href="/auth">Roles</a>
      </div>

      <a href="/auth" class="auth-role-switch-btn">
        <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>
        Choose a different role
      </a>

      <div class="auth-head">
        <p class="auth-eyebrow"><?= htmlspecialchars($eyebrow ?? '') ?></p>
        <h1 class="auth-title"><?= htmlspecialchars($formTitle ?? (isset($title) ? explode(' — ', $title)[0] : 'Create account')) ?></h1>
        <p class="auth-desc"><?= htmlspecialchars($desc ?? '') ?></p>
      </div>

      <form class="auth-form" id="sign-up-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>" />
        <p class="form-alert"></p>
        <p class="form-success">
          <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg>
          <span>Request received. The school office will confirm your access by email.</span>
        </p>

        <input type="hidden" name="role" value="<?= htmlspecialchars($role ?? '') ?>" />

        <!-- STEP 1: Account Details & Password Setup -->
        <div id="j-signup-step-details">
          <div class="field-group">
            <div class="field">
              <span class="field-label">Full name <em style="font-size:0.75rem;font-weight:normal;color:rgba(15,65,74,0.6);">(as registered with school)</em></span>
              <input type="text" name="fullName" class="field-input" placeholder="e.g. Samantha Perera" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>" required />
            </div>
            <div class="field">
              <span class="field-label"><?= htmlspecialchars($inputLabel ?? 'Email address') ?></span>
              <input type="<?= htmlspecialchars($inputType ?? 'text') ?>" name="identifier" class="field-input" placeholder="<?= htmlspecialchars($inputPlaceholder ?? 'name@lecole.edu') ?>" value="<?= htmlspecialchars($_GET['identifier'] ?? '') ?>" required autocomplete="username" />
            </div>
            <div class="field">
              <span class="field-label">Create password</span>
              <div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;">
                <input type="password" name="password" class="field-input" placeholder="Choose a password (min 8 characters)" required autocomplete="new-password" style="width:100%;padding-right:2.75rem;" />
                <button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button>
              </div>
            </div>
          </div>

          <div class="auth-form-footer">
            <a class="auth-need-access" href="<?= htmlspecialchars($signinUrl ?? '/auth') ?>">Already have access? Sign in</a>
            <button type="submit" class="auth-submit-btn">
              Set password &amp; continue
              <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>
            </button>
          </div>
        </div>

        <!-- STEP 2: 6-Digit OTP Verification Screen -->
        <div id="j-signup-step-otp" style="display:none;">
          <div class="c-otp-notice" style="background:rgba(32,124,130,0.08);border:1px solid var(--sky, #207C82);border-radius:0.5rem;padding:0.875rem 1rem;margin-bottom:1.25rem;">
            <div style="font-weight:700;color:var(--midnight,#0F414A);font-size:0.875rem;margin-bottom:0.25rem;display:flex;align-items:center;gap:0.4rem;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--sky,#207C82)" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              Verification Code Sent
            </div>
            <p style="margin:0;font-size:0.8125rem;line-height:1.45;color:rgba(15,65,74,0.85);">
              A 6-digit verification code was dispatched to <strong id="j-signup-masked-target">your registered email</strong>. Please enter the code below to finalize your account activation.
            </p>
          </div>

          <div class="field">
            <span class="field-label">6-Digit Verification Code</span>
            <input type="text" name="otp" id="j-signup-otp-input" class="field-input" placeholder="&bull; &bull; &bull; &bull; &bull; &bull;" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" style="text-align:center;font-size:1.6rem;letter-spacing:0.5rem;font-weight:700;font-family:monospace;padding:0.65rem;" />
          </div>

          <div class="auth-form-footer" style="margin-top:1.25rem;">
            <button type="button" id="j-signup-back-btn" class="auth-need-access" style="background:none;border:none;cursor:pointer;padding:0;color:inherit;font-family:inherit;font-size:inherit;">&larr; Change details</button>
            <button type="button" id="j-signup-verify-btn" class="auth-submit-btn">
              Verify &amp; Activate
              <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>
            </button>
          </div>

          <div style="margin-top:1rem;text-align:center;font-size:0.8125rem;color:var(--text-subtle, #5C7679);">
            Didn't receive the email? 
            <button type="button" id="j-signup-resend-btn" style="background:none;border:none;padding:0;color:var(--sky,#207C82);font-weight:700;cursor:pointer;text-decoration:underline;font-family:inherit;">Resend code</button>
          </div>
        </div>
      </form>

      <p class="auth-alternate">Already have an account? <a href="<?= htmlspecialchars($signinUrl ?? '/auth') ?>">Sign in instead</a></p>
    </div>
  </section>
</div>

<script src="/assets/js/auth-shared.js?v=<?= time() ?>"></script>
<script>
  LECOLE_AUTH.wireAuthForm('sign-up-form');
</script>
</body>
</html>
