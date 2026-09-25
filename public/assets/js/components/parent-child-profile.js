/**
 * =========================================================================
 * L'ÉCOLE — PARENT CHILD PROFILE CONTROLLER
 * =========================================================================
 * Handles:
 *   1. Top Tab Switching (Information, Academics, Sports & Clubs, Achievements)
 *   2. Sports & Clubs Search & Type Filtering (All, Sports, Clubs)
 * =========================================================================
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    // -----------------------------------------------------------------------
    // 1. TOP TABS SWITCHING
    // -----------------------------------------------------------------------
    const tabButtons = document.querySelectorAll('.profile-tabs .tab-btn');
    const tabPanels = document.querySelectorAll('.c-child-profile-page .tab-panel');

    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const targetTab = btn.getAttribute('data-tab') || btn.getAttribute('data-subtab');
        if (!targetTab) return;

        // Toggle button active state
        tabButtons.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');

        // Toggle panel active state
        tabPanels.forEach(function (p) { p.classList.remove('active'); });
        let targetPanel = document.getElementById('tab-' + targetTab);
        if (!targetPanel && targetTab === 'extracurriculars') {
          targetPanel = document.getElementById('tab-sports');
        } else if (!targetPanel && targetTab === 'sports') {
          targetPanel = document.getElementById('tab-extracurriculars');
        }
        if (targetPanel) {
          targetPanel.classList.add('active');
        }
      });
    });

    // -----------------------------------------------------------------------
    // 2. SPORTS & CLUBS SEARCH & TYPE FILTERING
    // -----------------------------------------------------------------------
    const searchInput = document.getElementById('sc-search-input');
    const scTabs = document.querySelectorAll('.sc-tabs .sc-tab');
    const clubCards = document.querySelectorAll('.sc-grid .j-club-card');
    const emptyMsg = document.getElementById('sc-empty');

    let activeType = 'all';
    let searchQuery = '';

    function filterClubs() {
      let visibleCount = 0;
      clubCards.forEach(function (card) {
        const name = (card.getAttribute('data-club-name') || '').toLowerCase();
        const type = (card.getAttribute('data-club-type') || '').toLowerCase();

        const matchesSearch = !searchQuery || name.includes(searchQuery);
        const matchesType = (activeType === 'all') || (type === activeType);

        if (matchesSearch && matchesType) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (emptyMsg) {
        emptyMsg.hidden = (visibleCount > 0);
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', function (e) {
        searchQuery = (e.target.value || '').trim().toLowerCase();
        filterClubs();
      });
    }

    scTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        scTabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');

        const filterVal = tab.getAttribute('data-type');
        activeType = (filterVal === 'sport') ? 'sports' : ((filterVal === 'club') ? 'club' : 'all');
        filterClubs();
      });
    });
  });

})();
