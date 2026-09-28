// MVC/public/assets/js/components/sidebar.js
// Handles sidebar collapse toggle and remembers state in localStorage

// Universal HTML Escaping Utility
window.escapeHtml = window.escapeHtml || function(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('j-sidebar');
  const toggleBtn = document.getElementById('j-sidebar-toggle');
  const appRoot = document.getElementById('j-app-root');

  if (!sidebar || !toggleBtn) return;

  // Restore saved collapse state per role
  const role = sidebar.dataset.role || 'default';
  const storageKey = `lecole_sidebar_collapsed_${role}`;
  const isCollapsed = localStorage.getItem(storageKey) === 'true';
  if (isCollapsed) {
    sidebar.classList.add('c-is-collapsed');
    if (appRoot) appRoot.classList.add('c-is-sidebar-collapsed');
  }

  // Toggle click
  toggleBtn.addEventListener('click', () => {
    const willCollapse = !sidebar.classList.contains('c-is-collapsed');
    sidebar.classList.toggle('c-is-collapsed', willCollapse);
    if (appRoot) appRoot.classList.toggle('c-is-sidebar-collapsed', willCollapse);
    localStorage.setItem(storageKey, willCollapse);
  });
});

// Prevent viewing cached authenticated pages after logout (BFCache back/forward)
window.addEventListener('pageshow', (event) => {
  if (event.persisted) {
    window.location.reload();
  }
});

// =========================================================================
// UNIVERSAL AUTHENTICATED PORTAL BACK-NAVIGATION GUARD & LOGOUT MODAL
// Traps browser back button navigation across all 5 authenticated portals,
// prompting the user with a styled confirmation modal before logging out.
// =========================================================================
(function initBackNavigationLogoutGuard() {
  function createLogoutGuardModal() {
    if (document.getElementById('j-logout-guard-modal')) return;

    const sidebar = document.getElementById('j-sidebar');
    const role = sidebar?.dataset.role || 'user';
    const roleTitle = role.charAt(0).toUpperCase() + role.slice(1);

    const modal = document.createElement('div');
    modal.className = 'c-modal-layer j-logout-guard-modal';
    modal.id = 'j-logout-guard-modal';
    modal.style.display = 'none';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'j-logout-guard-title');

    modal.innerHTML = `
      <div class="c-modal-backdrop j-logout-guard-backdrop" style="background: rgba(15, 65, 74, 0.45); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);"></div>
      <div class="c-modal c-modal--confirm" style="position:relative; z-index:2002; width:min(28rem, 92vw); max-width:28rem; background:#ffffff; border-radius:1.25rem; padding:2rem 1.75rem; text-align:center; box-shadow:0 24px 48px rgba(15,65,74,0.22); border:1px solid rgba(15,65,74,0.1); margin:auto; display:flex; flex-direction:column; align-items:center; box-sizing:border-box;">
        <div style="width:3.5rem; height:3.5rem; border-radius:50%; background:rgba(175, 80, 49, 0.12); color:#af5031; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; flex-shrink:0;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-logOut"/>
          </svg>
        </div>
        <h3 id="j-logout-guard-title" style="margin:0 0 0.5rem 0; font-size:1.25rem; font-weight:800; color:#0F414A; line-height:1.3; text-align:center;">
          Leave Your Workspace?
        </h3>
        <p id="j-logout-guard-desc" style="margin:0 0 1.5rem 0; font-size:0.875rem; line-height:1.55; color:rgba(15, 65, 74, 0.75); text-align:center; max-width:22rem;">
          You pressed the back button to leave your <strong>${window.escapeHtml(roleTitle)}</strong> account. Would you like to log out of your session, or stay in your workspace?
        </p>
        <div style="display:flex; align-items:center; justify-content:center; gap:0.75rem; width:100%;">
          <button type="button" class="c-btn c-btn--outline j-logout-guard-cancel" style="flex:1; padding:0.65rem 1rem; border:1px solid #EFE8DF; border-radius:0.5rem; background:#ffffff; color:#0F414A; font-weight:600; font-size:0.875rem; cursor:pointer; transition:all 140ms ease;">
            Stay Signed In
          </button>
          <a href="/logout" class="c-btn c-btn--solid j-logout-guard-confirm" style="flex:1; padding:0.65rem 1rem; border-radius:0.5rem; background:#af5031; border:1px solid #af5031; color:#ffffff; font-weight:700; font-size:0.875rem; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; cursor:pointer; transition:all 140ms ease;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-logOut"/></svg>
            Log Out &amp; Exit
          </a>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    // Close handlers
    const closeModal = () => {
      modal.classList.remove('c-is-open');
      modal.style.display = 'none';
      document.body.classList.remove('c-modal-open');
    };

    modal.querySelector('.j-logout-guard-backdrop')?.addEventListener('click', closeModal);
    modal.querySelector('.j-logout-guard-cancel')?.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('c-is-open')) {
        closeModal();
      }
    });

    return modal;
  }

  function openLogoutGuardModal() {
    const modal = document.getElementById('j-logout-guard-modal') || createLogoutGuardModal();
    if (!modal) return;
    modal.style.display = 'flex';
    modal.classList.add('c-is-open');
    document.body.classList.add('c-modal-open');
  }

  // Setup DOM elements when page is loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      createLogoutGuardModal();
      attachSidebarLogoutTrigger();
    });
  } else {
    createLogoutGuardModal();
    attachSidebarLogoutTrigger();
  }

  function attachSidebarLogoutTrigger() {
    const sidebarLogoutBtn = document.getElementById('j-logout-btn');
    if (sidebarLogoutBtn) {
      sidebarLogoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openLogoutGuardModal();
      });
    }
  }

  // Setup History API Back-Button Trap
  try {
    // Push guard state entry so pressing Back triggers a popstate event on the current page
    window.history.pushState({ lecolePortalGuard: true }, document.title, window.location.href);

    window.addEventListener('popstate', (e) => {
      // Re-push guard state immediately to lock navigation at the current page
      window.history.pushState({ lecolePortalGuard: true }, document.title, window.location.href);

      // Display the styled Logout confirmation popup
      openLogoutGuardModal();
    });
  } catch (err) {
    console.warn('[L\'École AuthGuard] Unable to initialize popstate trap:', err);
  }
})();

