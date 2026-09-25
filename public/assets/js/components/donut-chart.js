/**
 * L'École — Shared Donut / Pie Chart Hover Tooltip
 */
(function () {
  'use strict';

  function initDonutCharts() {
    let tooltip = document.getElementById('j-chart-tooltip');
    if (!tooltip) {
      tooltip = document.createElement('div');
      tooltip.id = 'j-chart-tooltip';
      tooltip.className = 'c-chart-tooltip';
      document.body.appendChild(tooltip);
    }

    document.querySelectorAll('.j-donut-slice, .j-donut-legend-item').forEach(el => {
      const name = el.dataset.name || '';
      const value = Number(el.dataset.value || 0).toLocaleString();

      el.addEventListener('mousemove', e => {
        tooltip.innerHTML = `
          <div class="c-chart-tooltip__eyebrow">${name}</div>
          <div class="c-chart-tooltip__value"><strong>${value}</strong> students</div>
        `;
        tooltip.classList.add('c-is-visible');

        const pad = 14;
        let left = e.clientX + pad;
        let top = e.clientY - tooltip.offsetHeight - pad;

        if (left + tooltip.offsetWidth > window.innerWidth - pad) {
          left = e.clientX - tooltip.offsetWidth - pad;
        }
        if (top < pad) {
          top = e.clientY + pad;
        }

        tooltip.style.left = `${Math.max(pad, left)}px`;
        tooltip.style.top = `${Math.max(pad, top)}px`;
      });

      el.addEventListener('mouseleave', () => {
        tooltip.classList.remove('c-is-visible');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', initDonutCharts);
})();
