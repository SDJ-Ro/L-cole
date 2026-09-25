/**
 * =========================================================================
 * L'ÉCOLE — NOTICE BOARD FILTER SCRIPT
 * =========================================================================
 * Real-time client-side searching and category/audience filtering.
 * =========================================================================
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.j-search-input');
    const noticeGrid = document.getElementById('j-notice-grid');
    const emptyState = document.getElementById('j-empty-state');
    const clearBtn = document.querySelector('.j-clear-filters');

    if (!noticeGrid) return;

    let selectedAudience = 'all';
    let selectedCategory = 'all';
    let searchQuery = '';

    function applyFilter() {
      const cards = noticeGrid.querySelectorAll('.c-notice-card');
      let visibleCount = 0;

      cards.forEach((card) => {
        const cat = (card.getAttribute('data-category') || '').toLowerCase();
        const audAttr = (card.getAttribute('data-audience') || '').toLowerCase();
        const audList = audAttr ? audAttr.split(',').map((s) => s.trim().toLowerCase()) : [];
        const domTags = Array.from(card.querySelectorAll('.c-tag--audience')).map((t) => t.textContent.trim().toLowerCase());
        const cardAudiences = Array.from(new Set([...audList, ...domTags]));

        const text = card.textContent.toLowerCase();

        const matchesSearch = !searchQuery || text.includes(searchQuery);
        const matchesCategory = (selectedCategory === 'all' || selectedCategory === 'all categories') || cat === selectedCategory;
        const isAllAudience = (!selectedAudience || selectedAudience === 'all' || selectedAudience === 'all users');
        const matchesAudience = isAllAudience || cardAudiences.includes(selectedAudience);

        if (matchesSearch && matchesCategory && matchesAudience) {
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
              valEl.textContent = 'All Users';
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
  });
})();
