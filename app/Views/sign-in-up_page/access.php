<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>" />
<title>Who's signing in? — L'École</title>
<link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
<link rel="stylesheet" href="/assets/css/auth-shared.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<a class="gw-back-btn" href="/landing"><svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>L'École home</a>

<div class="auth-shell">
  <aside class="gw-visual">
    <img data-role-image="" class="is-active" src="/assets/images/collage.jpg" alt="Collage of L'École life — students playing sport, a teacher in class, parents cheering, the management team at work, and the principal on campus" />
    <img data-role-image="student" src="/assets/images/students.jpg" alt="Student boy and girl in school uniform" />
    <img data-role-image="parent" src="/assets/images/parents.jpg" alt="Father and mother holding their baby" />
    <img data-role-image="teacher" src="/assets/images/teacher.jpg" alt="Welcoming teacher holding a book" />
    <img data-role-image="management" src="/assets/images/management.jpg" alt="School leadership team talking in a bright campus corridor" />
    <img data-role-image="admin" src="/assets/images/Schoolbackground.jpg" alt="L'École school building and front lawn" />
    <div class="gw-visual-scrim"></div>
    <div class="gw-visual-inner">
      <span class="gw-visual-badge"><svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
      <p class="gw-visual-eyebrow" id="gw-visual-eyebrow">One school, many roles</p>
      <h2 class="gw-visual-headline" id="gw-visual-headline">Who are you?</h2>
      <p class="gw-visual-copyright">&copy; 2026 L'École. All rights reserved.</p>
    </div>
  </aside>

  <section class="gw-form-section">
    <div class="gw-form-col">

      <!-- ===== role picker (default view) ===== -->
      <div id="gw-roles-view" class="is-active">
        <p class="auth-eyebrow">Access your account</p>
        <h1 class="auth-title">Who's signing in?</h1>
        <p class="auth-desc">Choose your role to continue to the right sign-in form.</p>

        <?php
          $notice = $_SESSION['flash']['notice'] ?? null;
          if ($notice) {
              unset($_SESSION['flash']['notice']);
          }
        ?>
        <?php if ($notice): ?>
          <div class="auth-notice" style="margin-top: 1rem; margin-bottom: 0.5rem; border-color: rgba(184, 80, 66, 0.3); background: rgba(184, 80, 66, 0.08);">
            <span class="auth-notice-icon" style="background:var(--maroon);color:#fff;"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-lockKeyhole"/></svg></span>
            <p class="auth-notice-text" style="color:var(--maroon);"><?= htmlspecialchars($notice) ?></p>
          </div>
        <?php endif; ?>

        <div class="gw-role-list">
          <button type="button" class="gw-role-btn j-gw-role-btn" data-role="student">
            <span class="gw-role-icon" style="background:var(--tan);color:var(--midnight);"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-graduationCap"/></svg></span>
            <span class="gw-role-copy"><span class="gw-role-name">Student</span><span class="gw-role-tagline">Timetable, grades, and report cards, plus the clubs, teams, and achievements you build along the way.</span></span>
            <svg class="icon gw-role-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
          </button>
          <button type="button" class="gw-role-btn j-gw-role-btn" data-role="parent">
            <span class="gw-role-icon" style="background:var(--lightblue);color:var(--midnight);"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-heartHandshake"/></svg></span>
            <span class="gw-role-copy"><span class="gw-role-name">Parent</span><span class="gw-role-tagline">Follow progress, activities, and achievements, approve school forms, and stay in touch with teachers.</span></span>
            <svg class="icon gw-role-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
          </button>
          <button type="button" class="gw-role-btn j-gw-role-btn" data-role="teacher">
            <span class="gw-role-icon" style="background:var(--maroon);color:var(--alabaster);"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-bookOpen"/></svg></span>
            <span class="gw-role-copy"><span class="gw-role-name">Teacher</span><span class="gw-role-tagline">Lessons, grades, and attendance beside the clubs, teams, and student development you guide.</span></span>
            <svg class="icon gw-role-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
          </button>
          <button type="button" class="gw-role-btn j-gw-role-btn" data-role="management">
            <span class="gw-role-icon" style="background:var(--terracotta);color:var(--cream);"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-building2"/></svg></span>
            <span class="gw-role-copy"><span class="gw-role-name">Management panel</span><span class="gw-role-tagline">Approvals, enrolment, staffing, curriculum, and school-wide reporting in one leadership workspace.</span></span>
            <svg class="icon gw-role-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
          </button>
          <button type="button" class="gw-role-btn j-gw-role-btn" data-role="admin">
            <span class="gw-role-icon" style="background:var(--midnight);color:#fff;"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-shield"/></svg></span>
            <span class="gw-role-copy"><span class="gw-role-name">Admin</span><span class="gw-role-tagline">Accounts, permissions, records, and the full audit trail behind the platform.</span></span>
            <svg class="icon gw-role-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-chevronRight"/></svg>
          </button>
        </div>
      </div>

      <!-- ===== student panel ===== -->
      <div class="gw-panel" data-role-panel="student">
        <button type="button" class="j-gw-back-to-roles"><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>Choose a different role</button>
        <p class="auth-eyebrow">Your learning journey</p>
        <h1 class="auth-title">Student sign in</h1>
        <p class="auth-desc">Pick up your academic day, activities, teams, and next opportunities.</p>
        <form class="auth-form" id="gw-form-student" data-submit-label="Sign in">
          <input type="hidden" name="role" value="student" />
          <p class="form-alert"></p>
          <p class="form-success"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg><span>Signed in successfully. Redirecting to your dashboard&hellip;</span></p>
          <div class="field-group">
            <div class="field"><span class="field-label">Student email or index number</span><input type="text" name="identifier" class="field-input" placeholder="e.g. STU-2026-0001" required autocomplete="username" /></div>
            <div class="field"><div class="field-label-row"><span class="field-label" style="margin-bottom:0;">Password</span><button type="button" class="field-forgot-btn j-forgot-trigger">Forgot password?</button></div><div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;"><input type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password" style="width:100%;padding-right:2.75rem;" /><button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button></div></div>
          </div>
          <div class="auth-form-footer">
            <a class="auth-need-access" href="/auth/studentSignup">Need access? Sign up</a>
            <button type="submit" class="auth-submit-btn">Sign in</button>
          </div>
        </form>
      </div>

      <!-- ===== parent panel ===== -->
      <div class="gw-panel" data-role-panel="parent">
        <button type="button" class="j-gw-back-to-roles"><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>Choose a different role</button>
        <p class="auth-eyebrow">Part of the team</p>
        <h1 class="auth-title">Parent sign in</h1>
        <p class="auth-desc">Stay connected to your child's academics, activities, moments, and progress.</p>
        <form class="auth-form" id="gw-form-parent" data-submit-label="Sign in">
          <input type="hidden" name="role" value="parent" />
          <p class="form-alert"></p>
          <p class="form-success"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg><span>Signed in successfully. Redirecting to your dashboard&hellip;</span></p>
          <div class="field-group">
            <div class="field"><span class="field-label">Email address</span><input type="email" name="identifier" class="field-input" placeholder="parent@email.com" required autocomplete="username" /></div>
            <div class="field"><div class="field-label-row"><span class="field-label" style="margin-bottom:0;">Password</span><button type="button" class="field-forgot-btn j-forgot-trigger">Forgot password?</button></div><div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;"><input type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password" style="width:100%;padding-right:2.75rem;" /><button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button></div></div>
          </div>
          <div class="auth-form-footer">
            <a class="auth-need-access" href="/auth/parentSignup">Need access? Sign up</a>
            <button type="submit" class="auth-submit-btn">Sign in</button>
          </div>
        </form>
      </div>

      <!-- ===== teacher panel ===== -->
      <div class="gw-panel" data-role-panel="teacher">
        <button type="button" class="j-gw-back-to-roles"><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>Choose a different role</button>
        <p class="auth-eyebrow">Guide the journey</p>
        <h1 class="auth-title">Teacher sign in</h1>
        <p class="auth-desc">Move between your classes, activities, and student progress with clarity.</p>
        <form class="auth-form" id="gw-form-teacher" data-submit-label="Sign in">
          <input type="hidden" name="role" value="teacher" />
          <p class="form-alert"></p>
          <p class="form-success"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg><span>Signed in successfully. Redirecting to your dashboard&hellip;</span></p>
          <div class="field-group">
            <div class="field"><span class="field-label">School email</span><input type="text" name="identifier" class="field-input" placeholder="name@lecole.edu" required autocomplete="username" /></div>
            <div class="field"><div class="field-label-row"><span class="field-label" style="margin-bottom:0;">Password</span><button type="button" class="field-forgot-btn j-forgot-trigger">Forgot password?</button></div><div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;"><input type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password" style="width:100%;padding-right:2.75rem;" /><button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button></div></div>
          </div>
          <div class="auth-form-footer">
            <a class="auth-need-access" href="/auth/teacherSignup">Need access? Sign up</a>
            <button type="submit" class="auth-submit-btn">Sign in</button>
          </div>
        </form>
      </div>

      <!-- ===== management panel (invite-only, no sign-up) ===== -->
      <div class="gw-panel" data-role-panel="management">
        <button type="button" class="j-gw-back-to-roles"><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>Choose a different role</button>
        <p class="auth-eyebrow">Restricted access</p>
        <h1 class="auth-title">Management sign in</h1>
        <p class="auth-desc">Approvals, enrolment, staffing, and school-wide performance in one leadership workspace.</p>
        <form class="auth-form" id="gw-form-management" data-submit-label="Sign in">
          <input type="hidden" name="role" value="management" />
          <div class="auth-notice"><span class="auth-notice-icon" style="background:var(--maroon);color:var(--alabaster);"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-lockKeyhole"/></svg></span><p class="auth-notice-text">You're entering the management panel. Accounts are issued by the school office.</p></div>
          <p class="form-alert"></p>
          <p class="form-success"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg><span>Signed in successfully. Redirecting to your dashboard&hellip;</span></p>
          <div class="field-group">
            <div class="field"><span class="field-label">Institutional email</span><input type="text" name="identifier" class="field-input" placeholder="name@staff.lecole.edu" required autocomplete="username" /></div>
            <div class="field"><div class="field-label-row"><span class="field-label" style="margin-bottom:0;">Password</span><button type="button" class="field-forgot-btn j-forgot-trigger">Forgot password?</button></div><div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;"><input type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password" style="width:100%;padding-right:2.75rem;" /><button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button></div></div>
          </div>
          <div class="auth-form-footer">
            <span class="auth-audited-tag"><svg class="icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-shieldCheck"/></svg>Access is audited</span>
            <button type="submit" class="auth-submit-btn">Sign in</button>
          </div>
        </form>
        <p class="auth-footnote">Need access or lost your credentials? Contact <strong>office@lecole.edu</strong></p>
      </div>

      <!-- ===== admin panel (invite-only, no sign-up) ===== -->
      <div class="gw-panel" data-role-panel="admin">
        <button type="button" class="j-gw-back-to-roles"><svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>Choose a different role</button>
        <p class="auth-eyebrow">Restricted access</p>
        <h1 class="auth-title">Admin sign in</h1>
        <p class="auth-desc">Manage accounts, permissions, records, and the full audit trail of the L'École platform.</p>
        <form class="auth-form" id="gw-form-admin" data-submit-label="Sign in">
          <input type="hidden" name="role" value="admin" />
          <div class="auth-notice"><span class="auth-notice-icon" style="background:var(--midnight);color:#fff;"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-lockKeyhole"/></svg></span><p class="auth-notice-text">You're entering the system admin panel. Every action here is logged.</p></div>
          <p class="form-alert"></p>
          <p class="form-success"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg><span>Signed in successfully. Redirecting to your dashboard&hellip;</span></p>
          <div class="field-group">
            <div class="field"><span class="field-label">Administrator email</span><input type="text" name="identifier" class="field-input" placeholder="name_admin@lecole.edu" required autocomplete="username" /></div>
            <div class="field"><div class="field-label-row"><span class="field-label" style="margin-bottom:0;">Password</span><button type="button" class="field-forgot-btn j-forgot-trigger">Forgot password?</button></div><div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;"><input type="password" name="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password" style="width:100%;padding-right:2.75rem;" /><button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" title="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button></div></div>
          </div>
          <div class="auth-form-footer">
            <span class="auth-audited-tag"><svg class="icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-shieldCheck"/></svg>Access is audited</span>
            <button type="submit" class="auth-submit-btn">Sign in</button>
          </div>
        </form>
        <p class="auth-footnote">Need access or lost your credentials? Contact <strong>itdesk@lecole.edu</strong></p>
      </div>

    </div>
  </section>
</div>

<div id="fp-modal-root"></div>
<script src="/assets/js/auth-shared.js?v=<?= time() ?>"></script>
<script>
  LECOLE_AUTH.injectForgotPasswordModal('');
  LECOLE_AUTH.wireAccessGateway();
  ['student','parent','teacher','management','admin'].forEach(function(r){ LECOLE_AUTH.wireAuthForm('gw-form-' + r); });
</script>
</body>
</html>
