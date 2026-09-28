/**
 * =========================================================================
 * L'ÉCOLE — NOTICE BOARD FILTER SCRIPT
 * =========================================================================
 * Real-time client-side searching, tab mode switching (My Notices vs My Posts),
 * and dynamic category/audience filtering.
 * =========================================================================
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.j-search-input');
    const noticeGrid = document.getElementById('j-notice-grid');
    const emptyState = document.getElementById('j-empty-state');
    const clearBtn = document.querySelector('.j-clear-filters');
    const audienceWrapper = document.querySelector('.j-filter-audience-wrapper');

    if (!noticeGrid) return;

    const currentRole = (noticeGrid.getAttribute('data-current-role') || 'management').toLowerCase();

    // Default to active tab in DOM (or 'all' for roles without tabs: admin, student, parent)
    const activeTabBtn = document.querySelector('.j-notice-scope-tab.is-active-tab');
    let selectedTabMode = activeTabBtn ? (activeTabBtn.getAttribute('data-filter') || 'my-notices') : (['admin', 'student', 'parent'].includes(currentRole) ? 'all' : 'my-notices');
    let selectedAudience = 'all';
    let selectedCategory = 'all';
    let searchQuery = '';

    // Synchronize audience dropdown visibility based on active tab
    function syncAudienceVisibility() {
      if (!audienceWrapper) return;
      if (currentRole === 'admin' || currentRole === 'student' || currentRole === 'parent') {
        audienceWrapper.style.display = '';
        return;
      }
      if (selectedTabMode === 'my-notices') {
        audienceWrapper.style.display = 'none';
        selectedAudience = 'all';
      } else {
        audienceWrapper.style.display = '';
      }
    }

    function applyFilter() {
      const cards = noticeGrid.querySelectorAll('.c-notice-card');
      let visibleCount = 0;

      cards.forEach((card) => {
        const cat = (card.getAttribute('data-category') || '').toLowerCase();
        const audAttr = (card.getAttribute('data-audience') || '').toLowerCase();
        const audList = audAttr ? audAttr.split(',').map((s) => s.trim().toLowerCase()) : [];
        const domTags = Array.from(card.querySelectorAll('.c-tag--audience')).map((t) => t.textContent.trim().toLowerCase());
        const cardAudiences = Array.from(new Set([...audList, ...domTags]));
        const cardAuthorRole = (card.getAttribute('data-author-role') || '').toLowerCase();

        const text = card.textContent.toLowerCase();

        // 1. Search Query Match
        const matchesSearch = !searchQuery || text.includes(searchQuery);

        // 2. Category Match
        const matchesCategory = (selectedCategory === 'all' || selectedCategory === 'all categories') || cat === selectedCategory;

        // 3. Tab Mode Filter:
        let matchesTab = true;
        let matchesAudience = true;

        if (selectedTabMode === 'my-notices') {
          // "My Notices" (Inbox): Notices sent to current role or official directives
          if (currentRole === 'management') {
            const isToManagement = cardAudiences.some((a) => a.includes('management') || a === 'all' || a === 'all users');
            matchesTab = isToManagement || cardAuthorRole === 'admin';
          } else if (currentRole === 'teacher') {
            const isToTeachers = cardAudiences.some((a) => a.includes('teacher') || a === 'all' || a === 'all users');
            const isByAboveRole = (cardAuthorRole === 'admin' || cardAuthorRole === 'management');
            matchesTab = isToTeachers && isByAboveRole;
          } else if (currentRole === 'student') {
            matchesTab = cardAudiences.some((a) => a.includes('student') || a === 'all' || a === 'all users');
          } else if (currentRole === 'parent') {
            matchesTab = cardAudiences.some((a) => a.includes('parent') || a === 'all' || a === 'all users');
          } else if (currentRole === 'admin') {
            // Admin sees all incoming institutional directives / department notices
            matchesTab = true;
          }
        } else if (selectedTabMode === 'my-posts') {
          // "My Posts" (Outbox): Authored by this role
          matchesTab = (cardAuthorRole === currentRole);

          // Apply Audience dropdown filter when in "My Posts"
          const isAllAudience = (!selectedAudience || selectedAudience === 'all' || selectedAudience === 'all audiences' || selectedAudience === 'all users');
          matchesAudience = isAllAudience || cardAudiences.includes(selectedAudience);
        } else if (selectedTabMode === 'all') {
          // Full feed
          const isAllAudience = (!selectedAudience || selectedAudience === 'all' || selectedAudience === 'all audiences' || selectedAudience === 'all users');
          matchesAudience = isAllAudience || cardAudiences.includes(selectedAudience);
        }

        if (matchesSearch && matchesCategory && matchesTab && matchesAudience) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (emptyState) {
        emptyState.hidden = visibleCount > 0;
      }
    }

    // Role Tablist Click Listener (e.g. My Notices vs My Posts)
    document.addEventListener('click', function (e) {
      const tabBtn = e.target.closest('.j-notice-scope-tab');
      if (!tabBtn) return;
      e.preventDefault();

      selectedTabMode = tabBtn.getAttribute('data-filter') || 'my-notices';

      const tablist = tabBtn.closest('.c-tablist');
      if (tablist) {
        tablist.querySelectorAll('.j-notice-scope-tab').forEach((btn) => {
          btn.classList.remove('is-active-tab', 'c-tone-sky');
          btn.setAttribute('aria-selected', 'false');
        });
        tabBtn.classList.add('is-active-tab', 'c-tone-sky');
        tabBtn.setAttribute('aria-selected', 'true');
      }

      syncAudienceVisibility();
      applyFilter();
    });

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        searchQuery = this.value.trim().toLowerCase();
        applyFilter();
      });
    }

    // Dropdown change events
    document.addEventListener('dropdown:change', function (e) {
      const root = e.target.closest('.c-select, .c-dropdown') || e.target;
      if (!root) return;

      const val = (e.detail && e.detail.value ? e.detail.value : '').toLowerCase();
      const rootId = (root.id || '').toLowerCase();

      if (rootId.includes('audience')) {
        selectedAudience = val;
      } else if (rootId.includes('category')) {
        selectedCategory = val;
      }
      applyFilter();
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (searchInput) searchInput.value = '';
        searchQuery = '';
        selectedAudience = 'all';
        selectedCategory = 'all';

        // Reset dropdown labels
        document.querySelectorAll('.c-select, .c-dropdown').forEach((root) => {
          const valEl = root.querySelector('.j-select-value');
          if (valEl) {
            if (root.id.includes('audience')) {
              valEl.textContent = 'All Audiences';
            } else if (root.id.includes('category')) {
              valEl.textContent = 'All Categories';
            } else {
              valEl.textContent = 'All';
            }
          }
          root.querySelectorAll('.c-select__option, .c-dropdown__option').forEach((opt) => {
            const optVal = (opt.getAttribute('data-value') || '').toLowerCase();
            opt.classList.toggle('c-is-selected', optVal === 'all');
          });
        });

        applyFilter();
      });
    }

    // Initial run
    syncAudienceVisibility();
    applyFilter();
  });
})();
