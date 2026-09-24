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
