/**
 * L'École — Achievements Timeline Interactive Component
 */
(function () {
  'use strict';

  function initAchievements() {
    const container = document.getElementById('j-ach-timeline-container');
    if (!container) return;

    const filterBtns = container.querySelectorAll('.ach-filter');
    const items = container.querySelectorAll('.ach-timeline-item');
    const yearGroups = container.querySelectorAll('.ach-year-group');
    const emptyEl = document.getElementById('ach-empty');

    if (!filterBtns.length || !items.length) return;

    filterBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        const cat = this.getAttribute('data-cat') || 'all';

        // Toggle active button
        filterBtns.forEach(b => b.classList.remove('is-active'));
        this.classList.add('is-active');

        let totalVisible = 0;

        yearGroups.forEach(group => {
          const groupItems = group.querySelectorAll('.ach-timeline-item');
          let visibleInGroup = 0;
          let lastVisibleItem = null;

          groupItems.forEach(item => {
            const itemCat = item.getAttribute('data-category');
            const isMatch = (cat === 'all' || itemCat === cat);

            if (isMatch) {
              item.classList.remove('is-hidden');
              visibleInGroup++;
              totalVisible++;
              lastVisibleItem = item;
              // Reset line visibility by default
              const line = item.querySelector('.ach-timeline-line');
              if (line) line.style.display = '';
            } else {
              item.classList.add('is-hidden');
            }
          });

          // Show or hide year group
          if (visibleInGroup > 0) {
            group.style.display = '';
            // Hide connecting line on the last visible item in group
            if (lastVisibleItem) {
              const lastLine = lastVisibleItem.querySelector('.ach-timeline-line');
              if (lastLine) lastLine.style.display = 'none';
            }
          } else {
            group.style.display = 'none';
          }
        });

        // Toggle empty message
        if (emptyEl) {
          emptyEl.hidden = (totalVisible > 0);
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAchievements);
  } else {
    initAchievements();
  }
})();
