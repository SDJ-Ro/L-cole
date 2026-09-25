/**
 * =========================================================================
 * L'ÉCOLE ADMIN — AUDIT LOGS COMPONENT CONTROLLER
 * =========================================================================
 * Handles live search filtering, activity & actor dropdown filters,
 * and empty state toggling. Ported directly from Admin/audit/script.js.
 * =========================================================================
 */

(function () {
  'use strict';

  const state = {
    searchQuery: '',
    activityFilter: 'All activities',
    actorFilter: 'All actors'
  };

  function initLogFilters() {
    const searchInput = document.getElementById('j-search-input');
    const logCards = Array.from(document.querySelectorAll('.j-log-card'));
    const logEmptyState = document.getElementById('j-log-empty');

    function applyLogFilters() {
      const query = state.searchQuery.toLowerCase().trim();
      const activity = state.activityFilter;
      const actor = state.actorFilter;

      let visibleCount = 0;

      logCards.forEach((card) => {
        const searchCorpus = (card.dataset.search || '').toLowerCase();
        const cardRole = card.dataset.role || '';
        const cardAction = card.dataset.action || '';

        const matchesQuery = query === '' || searchCorpus.includes(query);
        const matchesActivity = activity === 'All activities' || cardAction === activity;
        const matchesActor = actor === 'All actors' || cardRole.toLowerCase() === actor.toLowerCase();

        const isVisible = matchesQuery && matchesActivity && matchesActor;

        card.classList.toggle('c-is-hidden', !isVisible);
        if (isVisible) visibleCount += 1;
      });

      if (logEmptyState) {
        logEmptyState.hidden = visibleCount !== 0;
      }
    }

    // Search Input Event
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        state.searchQuery = this.value;
        applyLogFilters();
      });
    }

    // Listen for dropdown:change events emitted by the shared _dropdown component
    document.addEventListener('dropdown:change', function (e) {
      const activityDropdown = e.target.closest('#j-select-activity');
      if (activityDropdown && e.detail && e.detail.value) {
        state.activityFilter = e.detail.value;
        applyLogFilters();
        return;
      }

      const actorDropdown = e.target.closest('#j-select-actor');
      if (actorDropdown && e.detail && e.detail.value) {
        state.actorFilter = e.detail.value;
        applyLogFilters();
        return;
      }
    });

    applyLogFilters();
  }

  document.addEventListener('DOMContentLoaded', initLogFilters);
})();
