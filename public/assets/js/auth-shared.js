/* =========================================================================
   L'ÉCOLE — AUTH SHARED JS
   Loaded by every page in sign-in/. Three jobs only:
   1. ROLES data (one canonical list — the original app had two overlapping
      role lists for the same 5 roles; merged into one here).
   2. Forgot-password modal — injected once, shared by every page that has
      a <div id="fp-modal-root"></div>.
   3. Basic form validation + fake-submit state for the sign-in/sign-up
      forms, and the role-switching behavior on access.html.
   Every page is otherwise static HTML — this file only does things a
   browser can't do without JS (validation, modal state, live switching).
   ========================================================================= */
(function () {
  'use strict';

  var ROLES = {
    student: {
      label: 'Student', selfServe: true,
      image: '/assets/images/students.jpg',
      imageAlt: 'Student boy and girl in school uniform',
      eyebrow: 'Your learning journey', tagline: 'Timetables, grades, clubs, and notices in one place.',
      chipBg: 'var(--tan)', chipColor: 'var(--midnight)', icon: 'graduationCap'
    },
    teacher: {
      label: 'Teacher', selfServe: true,
      image: '/assets/images/teacher.jpg',
      imageAlt: 'Welcoming teacher holding a book',
      eyebrow: 'Guide the journey', tagline: 'Plan lessons, record grades, run your clubs and teams.',
      chipBg: 'var(--maroon)', chipColor: 'var(--alabaster)', icon: 'bookOpen'
    },
    parent: {
      label: 'Parent', selfServe: true,
      image: '/assets/images/parents.jpg',
      imageAlt: 'Father and mother holding their baby',
      eyebrow: 'Part of the team', tagline: 'Follow academic progress, activities, and achievements.',
      chipBg: 'var(--lightblue)', chipColor: 'var(--midnight)', icon: 'heartHandshake'
    },
    management: {
      label: 'Management', selfServe: false, supportEmail: 'office@lecole.edu',
      image: '/assets/images/management.jpg',
      imageAlt: 'School leadership team talking in a bright campus corridor',
      eyebrow: 'Restricted access', tagline: 'Enrolment, staffing, curriculum, and school-wide notices.',
      chipBg: 'var(--terracotta)', chipColor: 'var(--cream)', icon: 'building2'
    },
    admin: {
      label: 'Admin', selfServe: false, supportEmail: 'admin@lecole.edu',
      image: '/assets/images/Schoolbackground.jpg',
      imageAlt: "L'École school building and front lawn",
      eyebrow: 'Restricted access', tagline: 'Platform configuration, accounts, and system access.',
      chipBg: 'var(--midnight)', chipColor: '#fff', icon: 'shield'
    }
  };
  window.LECOLE_ROLES = ROLES;

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
  function isValidEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    var input = document.querySelector('input[name="_csrf_token"]');
    return input ? input.value : '';
  }

  /* ---------------- forgot-password modal (shared by every page) ---------------- */
  function injectForgotPasswordModal(defaultEmail) {
    var root = document.getElementById('fp-modal-root');
    if (!root) return;
    root.innerHTML =
      '<div class="fp-overlay" id="fp-overlay" role="dialog" aria-modal="true" aria-labelledby="fp-title">' +
      '<div class="fp-dialog">' +
      '<div class="fp-head">' +
      '<div class="fp-head-left"><span class="fp-head-icon"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-keyRound"/></svg></span>' +
      '<div><h2 class="fp-title" id="fp-title">Reset your password</h2><p class="fp-subtitle" id="fp-subtitle">Enter your account email and we\'ll send a 6-digit code.</p></div></div>' +
      '<button type="button" class="fp-close-btn" id="fp-close" aria-label="Close"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-x"/></svg></button>' +
      '</div>' +
      '<div class="fp-body">' +
      '<div class="fp-step is-active" data-step="email">' +
      '<div class="field"><span class="field-label">Account email or index number</span><input type="text" class="field-input" id="fp-email" placeholder="name@lecole.edu or STU-2026-0001" value="' + (defaultEmail || '') + '" /></div>' +
      '<p class="form-alert" id="fp-email-alert"></p>' +
      '<button type="button" class="auth-submit-btn auth-submit-btn--block" id="fp-send-code">Send reset code<svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg></button>' +
      '</div>' +
      '<div class="fp-step" data-step="code">' +
      '<p class="fp-code-hint" id="fp-code-hint"></p>' +
      '<div class="field"><span class="field-label">6-digit verification code</span><input type="text" inputmode="numeric" maxlength="6" class="fp-otp-input" id="fp-code" placeholder="000000" autocomplete="one-time-code" /></div>' +
      '<div class="field" style="margin-top:0.75rem;">' +
      '<span class="field-label" id="fp-pw-label">Account password</span>' +
      '<div class="field-password-wrap" style="position:relative;display:flex;align-items:center;width:100%;">' +
      '<input type="password" class="field-input" id="fp-password" placeholder="Enter your current password" style="width:100%;padding-right:2.75rem;" autocomplete="current-password" />' +
      '<button type="button" class="field-password-toggle j-toggle-password" aria-label="Show password" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;outline:none;box-shadow:none;padding:0;margin:0;cursor:pointer;color:rgba(15,65,74,0.4);display:inline-flex;align-items:center;justify-content:center;z-index:5;"><svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg></button>' +
      '</div></div>' +
      '<div class="fp-toggle-wrap" style="margin:10px 0 6px 0;">' +
      '<label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;color:#0f414a;cursor:pointer;user-select:none;font-weight:500;">' +
      '<input type="checkbox" id="fp-change-password-toggle" style="accent-color:#0f414a;width:15px;height:15px;cursor:pointer;" />' +
      '<span>I forgot my password — set a new one</span>' +
      '</label>' +
      '</div>' +
      '<p class="form-alert" id="fp-code-alert"></p>' +
      '<div class="fp-otp-row"><button type="button" class="fp-link-btn" id="fp-resend"><svg class="icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-mailCheck"/></svg>Resend code</button>' +
      '<button type="button" class="auth-submit-btn" id="fp-verify-code">Verify & Sign in</button></div>' +
      '</div>' +
      '<div class="fp-step" data-step="success">' +
      '<div class="form-success is-visible"><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-checkCircle2"/></svg><span id="fp-success-msg">Verified successfully! Redirecting to your dashboard…</span></div>' +
      '<div style="margin-top:1rem;font-size:13px;color:rgba(15,65,74,0.7);text-align:center;">Redirecting to your dashboard&hellip;</div>' +
      '</div>' +
      '</div></div></div>';

    var overlay = qs('#fp-overlay');
    function showStep(name) {
      qsa('.fp-step', overlay).forEach(function (s) { s.classList.toggle('is-active', s.getAttribute('data-step') === name); });
    }
    var lastResolvedRole = '';
    var lastResolvedIdentifier = '';
    var lastSigninUrl = '';

    function open() { 
      overlay.classList.add('is-open'); 
      showStep('email'); 
      qs('#fp-email-alert').classList.remove('is-visible'); 
      var titleEl = qs('#fp-title');
      var subEl = qs('#fp-subtitle');
      if (titleEl) titleEl.textContent = 'Reset your password';
      if (subEl) subEl.textContent = "Enter your account email and we'll send a 6-digit code.";
    }
    function close() { 
      overlay.classList.remove('is-open'); 
      try {
        if (window.history && window.history.replaceState) {
          var url = new URL(window.location.href);
          if (url.searchParams.has('unlock') || url.searchParams.has('reset')) {
            url.searchParams.delete('unlock');
            url.searchParams.delete('reset');
            var cleanUrl = url.pathname + (url.search ? url.search : '');
            window.history.replaceState({}, document.title, cleanUrl);
          }
        }
      } catch (e) {}
    }
    qsa('.j-forgot-trigger').forEach(function (btn) { btn.addEventListener('click', open); });
    qs('#fp-close').addEventListener('click', close);
    overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(); });

    var pwToggle = qs('#fp-change-password-toggle');
    var verifyBtn = qs('#fp-verify-code');

    function updatePasswordToggleState() {
      if (!pwToggle) return;
      var pwLabel = qs('#fp-pw-label');
      var pwInput = qs('#fp-password');
      if (pwToggle.checked) {
        if (pwLabel) pwLabel.textContent = 'Create new password';
        if (pwInput) {
          pwInput.placeholder = 'Enter a new password (min. 8 chars)';
          pwInput.setAttribute('autocomplete', 'new-password');
        }
        if (verifyBtn) verifyBtn.textContent = 'Reset & Sign in';
      } else {
        if (pwLabel) pwLabel.textContent = 'Account password';
        if (pwInput) {
          pwInput.placeholder = 'Enter your current password';
          pwInput.setAttribute('autocomplete', 'current-password');
        }
        if (verifyBtn) verifyBtn.textContent = 'Verify & Sign in';
      }
    }
    if (pwToggle) {
      pwToggle.addEventListener('change', updatePasswordToggleState);
    }

    window.openUnlockModal = function (identifier) {
      open();
      var emailInput = qs('#fp-email');
      if (emailInput && identifier) emailInput.value = identifier;
      showStep('code');
      var titleEl = qs('#fp-title');
      var subEl = qs('#fp-subtitle');
      if (titleEl) titleEl.textContent = 'Unlock account & sign in';
      if (subEl) subEl.textContent = 'Enter your 6-digit verification code and password.';
      if (pwToggle) {
        pwToggle.checked = false;
        updatePasswordToggleState();
      }
      var hintEl = qs('#fp-code-hint');
      if (hintEl) {
        hintEl.textContent = 'Account temporarily locked. Enter the 6-digit code from your email and your password to regain access.';
      }
      wirePasswordToggles(overlay);
      var codeInput = qs('#fp-code');
      if (codeInput) {
        codeInput.value = '';
        setTimeout(function () { codeInput.focus(); }, 100);
      }
    };

    // Auto-open modal when arriving via Unlock & Reset link
    try {
      var urlParams = new URLSearchParams(window.location.search);
      var isUnlock = urlParams.get('unlock') === '1' || urlParams.get('reset') === '1';
      var paramIdent = urlParams.get('identifier');
      if (isUnlock && paramIdent) {
        setTimeout(function () {
          window.openUnlockModal(paramIdent);
        }, 80);
      }
    } catch (e) {}

    qs('#fp-send-code').addEventListener('click', function () {
      var email = qs('#fp-email').value.trim();
      var alertEl = qs('#fp-email-alert');
      var btn = qs('#fp-send-code');
      if (!email) { alertEl.textContent = 'Please enter your account email or index number.'; alertEl.classList.add('is-visible'); return; }
      alertEl.classList.remove('is-visible');
      btn.disabled = true;
      btn.textContent = 'Sending code…';

      fetch('/auth/handleForgotSend', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({ email: email, _csrf_token: getCsrfToken() })
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        btn.disabled = false;
        btn.innerHTML = 'Send reset code<svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>';
        if (data.success) {
          var hintText = 'We sent a 6-digit verification code to ' + (data.recipient || email) + '. Please check your inbox.';
          qs('#fp-code-hint').textContent = hintText;
          if (pwToggle) {
            pwToggle.checked = true;
            updatePasswordToggleState();
          }
          showStep('code');
          wirePasswordToggles(overlay);
        } else {
          alertEl.textContent = data.error || 'Failed to send reset code.';
          alertEl.classList.add('is-visible');
        }
      })
      .catch(function () {
        btn.disabled = false;
        btn.innerHTML = 'Send reset code<svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-arrowRight"/></svg>';
        alertEl.textContent = 'Network error. Please try again.';
        alertEl.classList.add('is-visible');
      });
    });

    qs('#fp-verify-code').addEventListener('click', function () {
      var email = (qs('#fp-email') ? qs('#fp-email').value.trim() : '') || (new URLSearchParams(window.location.search).get('identifier') || '').trim();
      var code = qs('#fp-code') ? qs('#fp-code').value.trim() : '';
      var isChangePw = pwToggle && pwToggle.checked;
      var passInput = qs('#fp-password');
      var password = passInput ? passInput.value.trim() : '';
      var alertEl = qs('#fp-code-alert');
      var btn = qs('#fp-verify-code');

      if (!code || code.length !== 6 || !/^\d{6}$/.test(code)) {
        alertEl.textContent = 'Enter the 6-digit numeric verification code.';
        alertEl.classList.add('is-visible');
        return;
      }
      if (!password) {
        alertEl.textContent = isChangePw ? 'Please enter a new password.' : 'Please enter your password to sign in.';
        alertEl.classList.add('is-visible');
        return;
      }
      if (isChangePw && password.length < 8) {
        alertEl.textContent = 'New password must be at least 8 characters long.';
        alertEl.classList.add('is-visible');
        return;
      }

      alertEl.classList.remove('is-visible');
      btn.disabled = true;
      btn.textContent = isChangePw ? 'Resetting & signing in…' : 'Verifying & signing in…';

      fetch('/auth/handleForgotReset', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({ 
          email: email, 
          code: code, 
          password: password, 
          is_reset: isChangePw,
          _csrf_token: getCsrfToken() 
        })
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.success) {
          var successMsgEl = qs('#fp-success-msg');
          if (successMsgEl && data.message) {
            successMsgEl.textContent = data.message;
          }
          showStep('success');
          if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
          }
          setTimeout(function () {
            window.location.href = data.redirect || '/landing';
          }, 700);
        } else {
          btn.disabled = false;
          updatePasswordToggleState();
          alertEl.textContent = data.error || 'Failed to process request.';
          alertEl.classList.add('is-visible');
        }
      })
      .catch(function () {
        btn.disabled = false;
        updatePasswordToggleState();
        alertEl.textContent = 'Network error. Please try again.';
        alertEl.classList.add('is-visible');
      });
    });

    var resendBtn = qs('#fp-resend');
    var resendTimer = null;

    if (resendBtn) {
      resendBtn.addEventListener('click', function () {
        var email = (qs('#fp-email') ? qs('#fp-email').value.trim() : '') || (new URLSearchParams(window.location.search).get('identifier') || '').trim();
        var alertEl = qs('#fp-code-alert');
        var hintEl = qs('#fp-code-hint');

        if (!email) {
          if (alertEl) {
            alertEl.textContent = 'Account email or index is missing. Please refresh and try again.';
            alertEl.classList.add('is-visible');
          }
          return;
        }

        if (alertEl) alertEl.classList.remove('is-visible');
        resendBtn.disabled = true;
        var originalHtml = resendBtn.innerHTML;
        resendBtn.textContent = 'Sending code…';

        fetch('/auth/handleForgotSend', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
          },
          body: JSON.stringify({ email: email, _csrf_token: getCsrfToken() })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data && data.success) {
            var dest = data.recipient || email;
            if (hintEl) {
              hintEl.textContent = 'A new 6-digit verification code has been dispatched to ' + dest + '. Please check your inbox.';
            }
            var codeInput = qs('#fp-code');
            if (codeInput) {
              codeInput.value = '';
              codeInput.focus();
            }

            var countdown = 30;
            resendBtn.textContent = 'Resend code (' + countdown + 's)';
            if (resendTimer) clearInterval(resendTimer);
            resendTimer = setInterval(function () {
              countdown--;
              if (countdown > 0) {
                resendBtn.textContent = 'Resend code (' + countdown + 's)';
              } else {
                clearInterval(resendTimer);
                resendTimer = null;
                resendBtn.disabled = false;
                resendBtn.innerHTML = originalHtml;
              }
            }, 1000);
          } else {
            resendBtn.disabled = false;
            resendBtn.innerHTML = originalHtml;
            if (alertEl) {
              alertEl.textContent = (data && data.error) ? data.error : 'Failed to resend code. Please try again.';
              alertEl.classList.add('is-visible');
            }
          }
        })
        .catch(function () {
          resendBtn.disabled = false;
          resendBtn.innerHTML = originalHtml;
          if (alertEl) {
            alertEl.textContent = 'Network error. Please try again.';
            alertEl.classList.add('is-visible');
          }
        });
      });
    }

    var doneBtn = qs('#fp-done');
    if (doneBtn) {
      doneBtn.addEventListener('click', function () {
        close();
        window.location.reload();
      });
    }
  }

  /* ---------------- sign-in / sign-up form: validate + redirect to admin portal ---------------- */
  function wireAuthForm(formId) {
    var form = document.getElementById(formId);
    if (!form) return;
    var alertEl = qs('.form-alert', form);
    var successEl = qs('.form-success', form);
    var submitBtn = qs('.auth-submit-btn', form);
    var originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var inputs = qsa('.field-input', form);
      var missing = inputs.some(function (i) { return i.hasAttribute('required') && !i.value.trim(); });
      if (missing) { showAlert('Please fill in every field.'); return; }

      var roleInput = qs('input[name="role"]', form);
      var roleVal = (roleInput ? roleInput.value : '').toLowerCase();
      var emailInput = qs('input[type="email"]', form);

      // Only enforce strict email formatting if not student
      if (emailInput && roleVal !== 'student' && !isValidEmail(emailInput.value.trim())) {
        showAlert('Enter a valid email address.');
        return;
      }
      hideAlert();

      var isSignUp = formId.indexOf('sign-up') !== -1;
      var endpoint = isSignUp ? '/auth/handleSignup' : '/auth/handleSignin';

      // Gather form inputs
      var formData = new FormData(form);
      var payload = {};
      formData.forEach(function (value, key) { payload[key] = value; });

      // Fallback role detection if not explicitly in form
      if (!payload.role) {
        var pathname = window.location.pathname.toLowerCase();
        ['student', 'teacher', 'parent', 'management', 'admin'].forEach(function (r) {
          if (pathname.indexOf(r) !== -1 || formId.indexOf(r) !== -1) {
            payload.role = r;
          }
        });
      }

      submitBtn.disabled = true;
      submitBtn.textContent = isSignUp ? 'Activating account…' : 'Signing in…';

      if (!payload._csrf_token) {
        payload._csrf_token = getCsrfToken();
      }

      fetch(endpoint, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify(payload)
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.success) {
          if (successEl) {
            var msgSpan = qs('span', successEl);
            if (msgSpan && data.message) msgSpan.textContent = data.message;
            successEl.classList.add('is-visible');
          }
          setTimeout(function () {
            window.location.href = data.redirect || '/landing';
          }, 600);
        } else if (data.needs_activation && data.redirect) {
          showAlert(data.error || 'Account pending activation. Redirecting…');
          setTimeout(function () {
            window.location.href = data.redirect;
          }, 1200);
        } else {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
          if (data.code === 'ACCOUNT_LOCKED_SOFT') {
            var targetId = data.identifier || payload.identifier || payload.email || '';
            showAlert((data.error || 'Account temporarily locked.') + 
              '<div style="margin-top:0.5rem;"><button type="button" class="j-quick-unlock-btn" style="background:none;border:none;padding:0;color:inherit;text-decoration:underline;font-weight:700;cursor:pointer;font-family:inherit;font-size:0.8125rem;">Enter 6-digit unlock code &rarr;</button></div>');
            var quickBtn = qs('.j-quick-unlock-btn', alertEl);
            if (quickBtn && window.openUnlockModal) {
              quickBtn.addEventListener('click', function () {
                window.openUnlockModal(targetId);
              });
            }
          } else {
            showAlert(data.error || 'Authentication failed. Please check your credentials.');
          }
        }
      })
      .catch(function (err) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
        showAlert('Network or server error. Please try again.');
      });
    });

    function showAlert(msg) { if (alertEl) { alertEl.innerHTML = msg; alertEl.classList.add('is-visible'); } }
    function hideAlert() { if (alertEl) alertEl.classList.remove('is-visible'); }
  }

  /* ---------------- access.html: role list -> inline panel switching ---------------- */
  function wireAccessGateway() {
    var roleButtons = qsa('.j-gw-role-btn');
    if (!roleButtons.length) return;
    var rolesView = document.getElementById('gw-roles-view');
    var eyebrowEl = document.getElementById('gw-visual-eyebrow');
    var headlineEl = document.getElementById('gw-visual-headline');
    var defaultEyebrow = eyebrowEl ? eyebrowEl.textContent : '';
    var badgeEl = qs('.gw-visual-badge');

    function activate(role, label, tagline) {
      qsa('.gw-panel').forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-role-panel') === role); });
      if (rolesView) rolesView.classList.remove('is-active');
      qsa('.gw-visual img').forEach(function (img) { img.classList.toggle('is-active', img.getAttribute('data-role-image') === role); });
      if (eyebrowEl) eyebrowEl.textContent = "L'École for " + label;
      if (headlineEl) headlineEl.textContent = tagline;
      var roleData = ROLES[role];
      if (badgeEl && roleData && roleData.icon) {
        badgeEl.innerHTML = '<svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-' + roleData.icon + '"/></svg>';
      }
    }
    function resetVisual() {
      qsa('.gw-visual img').forEach(function (img) { img.classList.toggle('is-active', img.getAttribute('data-role-image') === ''); });
      if (eyebrowEl) eyebrowEl.textContent = defaultEyebrow;
      if (headlineEl) headlineEl.textContent = defaultHeadline;
      if (badgeEl) {
        badgeEl.innerHTML = '<svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#icon-graduationCap"/></svg>';
      }
    }
    roleButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var label = qs('.gw-role-name', btn) ? qs('.gw-role-name', btn).textContent : '';
        var tagline = qs('.gw-role-tagline', btn) ? qs('.gw-role-tagline', btn).textContent : '';
        activate(btn.getAttribute('data-role'), label, tagline);
      });
    });
    qsa('.j-gw-back-to-roles').forEach(function (btn) {
      btn.addEventListener('click', function () {
        qsa('.gw-panel').forEach(function (p) { p.classList.remove('is-active'); });
        if (rolesView) rolesView.classList.add('is-active');
        resetVisual();
      });
    });

    var paramRole = new URLSearchParams(window.location.search).get('role');
    if (paramRole) {
      var targetBtn = qs('.j-gw-role-btn[data-role="' + paramRole.toLowerCase() + '"]');
      if (targetBtn) {
        targetBtn.click();
      }
    }
  }

  /* ---------------- password visibility toggle ---------------- */
  function wirePasswordToggles(root) {
    var btns = qsa('.j-toggle-password', root);
    btns.forEach(function (btn) {
      if (btn._wired) return;
      btn._wired = true;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var wrap = btn.closest('.field-password-wrap');
        if (!wrap) return;
        var input = qs('input', wrap);
        if (!input) return;
        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        btn.title = isPassword ? 'Hide password' : 'Show password';
        btn.innerHTML = isPassword
          ? '<svg class="icon icon-eye-off" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eyeOff"/></svg>'
          : '<svg class="icon icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="stroke:currentColor;fill:none;display:block;width:16px;height:16px;"><use href="#icon-eye"/></svg>';
      });
    });
  }

  // Auto-init password toggles when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { wirePasswordToggles(); });
  } else {
    wirePasswordToggles();
  }

  window.LECOLE_AUTH = { 
    injectForgotPasswordModal: injectForgotPasswordModal, 
    wireAuthForm: wireAuthForm, 
    wireAccessGateway: wireAccessGateway,
    wirePasswordToggles: wirePasswordToggles
  };
})();
