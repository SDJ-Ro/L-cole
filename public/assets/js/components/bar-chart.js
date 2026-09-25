// =========================================================================
// L'ÉCOLE — SHARED BAR CHART JAVASCRIPT
// Features:
// 1. Dual-series popup tooltip per column on hover (with smart viewport flipping).
// 2. Mouse wheel horizontal scrolling on the chart canvas.
// 3. Dynamic Term Switcher Dropdown (Term 1, Term 2, Term 3) with animated bar transitions.
// =========================================================================

/**
 * Read a CSS custom property value from :root (mirrors global.css brand tokens).
 * Falls back to the provided hex string if the variable is undefined.
 */
function brandToken(varName, hex) {
  var val = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
  return val || hex;
}

document.addEventListener('DOMContentLoaded', () => {
  initChartTooltips();
  initHorizontalWheelScroll();
  initTermDropdowns();
  fitChartWidth();
  initAcademicFilters();
});

window.addEventListener('resize', () => {
  fitChartWidth();
});

/**
 * -------------------------------------------------------------------------
 * 1. DUAL-SERIES POPUP TOOLTIP PER COLUMN
 * -------------------------------------------------------------------------
 */
function initChartTooltips() {
  const chartGroups = document.querySelectorAll('.j-chart-group');
  if (!chartGroups.length) return;

  // Single shared floating tooltip element in body
  let tooltip = document.getElementById('j-chart-tooltip');
  if (!tooltip) {
    tooltip = document.createElement('div');
    tooltip.id = 'j-chart-tooltip';
    tooltip.className = 'c-chart-tooltip';
    document.body.appendChild(tooltip);
  }

  chartGroups.forEach(group => {
    const show = (e) => {
      const cat = group.dataset.category || '';
      const s1Label = group.dataset.series1 || 'Series 1';
      const s1Val = group.dataset.val1 || '0';
      const s1Color = group.dataset.color1 || brandToken('--skyblue', '#7FC7CC');
      const s2Label = group.dataset.series2 || 'Series 2';
      const s2Val = group.dataset.val2 || '0';
      const s2Color = group.dataset.color2 || brandToken('--maroon', '#7F0303');
      const unit = group.dataset.unit || '';

      tooltip.innerHTML = `
        <div class="c-chart-tooltip__title">${escapeHtml(cat)}</div>
        <div class="c-chart-tooltip__row">
          <span class="c-chart-tooltip__color-dot" style="background:${s1Color};"></span>
          <span>${escapeHtml(s1Label)}: <strong>${escapeHtml(s1Val)}${unit}</strong></span>
        </div>
        <div class="c-chart-tooltip__row">
          <span class="c-chart-tooltip__color-dot" style="background:${s2Color};"></span>
          <span>${escapeHtml(s2Label)}: <strong>${escapeHtml(s2Val)}${unit}</strong></span>
        </div>
      `;
      tooltip.classList.add('c-is-visible');
      positionTooltip(e, tooltip);
    };

    const move = (e) => {
      positionTooltip(e, tooltip);
    };

    const hide = () => {
      tooltip.classList.remove('c-is-visible');
    };

    group.addEventListener('mouseenter', show);
    group.addEventListener('mousemove', move);
    group.addEventListener('mouseleave', hide);
  });
}
window.initChartTooltips = initChartTooltips;

function positionTooltip(e, tooltip) {
  if (!tooltip) return;
  const pad = 14;
  const rect = tooltip.getBoundingClientRect();
  let left = e.clientX + pad;
  let top = e.clientY - rect.height - pad;

  // Flip horizontally if overflow right
  if (left + rect.width > window.innerWidth - 12) {
    left = e.clientX - rect.width - pad;
  }
  // Flip vertically if overflow top
  if (top < 12) {
    top = e.clientY + pad;
  }

  tooltip.style.left = `${Math.max(12, left)}px`;
  tooltip.style.top = `${Math.max(12, top)}px`;
}

/**
 * -------------------------------------------------------------------------
 * 2. MOUSE WHEEL HORIZONTAL SCROLLING
 * Enables effortless horizontal scrolling with standard mouse wheels.
 * -------------------------------------------------------------------------
 */
function initHorizontalWheelScroll() {
  const chartAreas = document.querySelectorAll('.c-bar-chart__area');
  chartAreas.forEach(area => {
    area.addEventListener('wheel', (e) => {
      // Only intercept if there is actual horizontal scroll available and user is wheeling vertically
      if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && area.scrollWidth > area.clientWidth) {
        area.scrollLeft += e.deltaY;
        e.preventDefault();
      }
    }, { passive: false });
  });
}

/**
 * -------------------------------------------------------------------------
 * 3. DYNAMIC TERM SELECTION DROPDOWN (Role-Specific Feature)
 * -------------------------------------------------------------------------
 */
function initTermDropdowns() {
  const dropdowns = document.querySelectorAll('.j-term-dropdown, .term-dropdown');
  if (!dropdowns.length) return;

  dropdowns.forEach(dropdown => {
    const btn = dropdown.querySelector('.j-term-dropdown-btn, .c-select__trigger, .nb-dropdown-btn');
    const menu = dropdown.querySelector('.j-term-dropdown-menu, .c-select__menu, .nb-dropdown-menu');
    const label = dropdown.querySelector('.j-select-value, #term-dropdown-label');
    const opts = dropdown.querySelectorAll('.j-term-option, .c-select__option, .nb-dropdown-option');

    if (!btn || !menu) return;

    const toggle = (e) => {
      e.stopPropagation();
      const isOpen = dropdown.classList.contains('is-open') || dropdown.classList.contains('c-is-open');
      closeAllTermDropdowns();
      if (!isOpen) {
        dropdown.classList.add('is-open', 'c-is-open');
        menu.removeAttribute('hidden');
        menu.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        if (typeof window.adjustDropdownPosition === 'function') {
          window.adjustDropdownPosition(dropdown, btn);
        }
      }
    };

    btn.addEventListener('click', toggle);

    opts.forEach(opt => {
      opt.addEventListener('click', (e) => {
        e.stopPropagation();
        const selectedTerm = opt.getAttribute('data-value') || opt.textContent.trim();
        if (label) label.textContent = selectedTerm;

        opts.forEach(o => o.classList.remove('c-is-selected', 'is-selected'));
        opt.classList.add('c-is-selected', 'is-selected');

        closeAllTermDropdowns();

        // Find associated chart panel and apply data for this term
        const panel = dropdown.closest('.c-panel') || document.querySelector('section[data-terms]');
        if (panel && panel.dataset.terms) {
          try {
            const termsData = JSON.parse(panel.dataset.terms);
            const termRows = termsData[selectedTerm];
            if (termRows && Array.isArray(termRows)) {
              applyTermDataToChart(panel, termRows);
            }
          } catch (err) {
            console.warn("Could not parse chart termsData:", err);
          }
        }
      });
    });
  });

  document.addEventListener('click', () => {
    closeAllTermDropdowns();
  });
}

function closeAllTermDropdowns() {
  document.querySelectorAll('.j-term-dropdown, .term-dropdown').forEach(dropdown => {
    dropdown.classList.remove('is-open', 'c-is-open');
    const menu = dropdown.querySelector('.j-term-dropdown-menu, .c-select__menu, .nb-dropdown-menu');
    const btn = dropdown.querySelector('.j-term-dropdown-btn, .c-select__trigger, .nb-dropdown-btn');
    if (menu) {
      menu.setAttribute('hidden', '');
      menu.style.display = 'none';
      menu.style.left = '';
      menu.style.right = '';
      menu.style.top = '';
      menu.style.bottom = '';
    }
    if (btn) btn.setAttribute('aria-expanded', 'false');
  });
}

/**
 * Updates bar heights and data attributes smoothly when a term is selected.
 */
function applyTermDataToChart(panel, termRows) {
  const maxVal = parseFloat(panel.dataset.maxval) || 100;
  const plotH = parseFloat(panel.dataset.ploth) || 344;
  const padTop = parseFloat(panel.dataset.padtop) || 14;

  const groups = panel.querySelectorAll('.j-chart-group');
  groups.forEach(group => {
    const cat = group.dataset.category;
    const row = termRows.find(r => (r.label || r.subject) === cat);
    if (!row) return;

    // Keys might be mine/best or avg/high or student/average or series1/series2
    const keys = Object.keys(row).filter(k => k !== 'label' && k !== 'subject');
    const val1 = parseFloat(row[keys[0]] ?? row.mine ?? row.avg ?? row.student ?? row.total ?? 0);
    const val2 = parseFloat(row[keys[1]] ?? row.best ?? row.high ?? row.average ?? row.sports ?? 0);

    // Update group dataset
    group.dataset.val1 = String(val1);
    group.dataset.val2 = String(val2);

    // Calculate new bar geometry
    const h1 = (val1 / maxVal) * plotH;
    const h2 = (val2 / maxVal) * plotH;
    const y1 = padTop + plotH - h1;
    const y2 = padTop + plotH - h2;

    const bar1 = group.querySelector('.j-chart-bar[data-bar-type="series1"]');
    const bar2 = group.querySelector('.j-chart-bar[data-bar-type="series2"]');

    if (bar1) {
      bar1.setAttribute('y', y1);
      bar1.setAttribute('height', Math.max(h1, 2));
      bar1.dataset.value = String(val1);
    }
    if (bar2) {
      bar2.setAttribute('y', y2);
      bar2.setAttribute('height', Math.max(h2, 2));
      bar2.dataset.value = String(val2);
    }
  });
}

window.escapeHtml = window.escapeHtml || function (str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
};
var escapeHtml = window.escapeHtml;

/**
 * -------------------------------------------------------------------------
 * 4. DYNAMIC WIDTH BALANCING
 * Automatically extends the X-axis to the right border on small datasets (like Admin/Management),
 * perfectly balancing the left margin (card border -> Y-axis title) with the right margin (X-arrow -> card border).
 * -------------------------------------------------------------------------
 */
function fitChartWidth() {
  const panels = document.querySelectorAll('.chart-panel, .c-primary-grid__chart');
  panels.forEach(panel => {
    const area = panel.querySelector('.c-bar-chart__area, .chart-area');
    const svg = area ? area.querySelector('svg') : null;
    if (!area || !svg) return;

    const groups = svg.querySelectorAll('.j-chart-group');
    if (!groups.length) return;

    const groupCount = groups.length;
    const clientW = area.clientWidth;
    if (clientW <= 0) return;

    const padLeft = 50;
    const padRight = 14;

    // Only apply for datasets that fit within container without horizontal scrolling (<= 7 categories)
    if (groupCount <= 7) {
      const targetW = Math.max(clientW, 600);
      const plotW = targetW - padLeft - padRight;
      const groupW = plotW / groupCount;
      const barW = 24;
      const barGap = 6;
      const H = parseFloat(svg.getAttribute('height')) || 380;
      const padTop = parseFloat(panel.dataset.padtop) || 14;
      const plotH = parseFloat(panel.dataset.ploth) || (H - 14 - 22);

      // Update SVG width & viewBox
      svg.setAttribute('viewBox', `0 0 ${targetW} ${H}`);
      svg.setAttribute('width', targetW);
      svg.style.width = `${targetW}px`;
      svg.style.minWidth = `${targetW}px`;

      // Update X-axis line (horizontal line where y1 === y2)
      const lines = svg.querySelectorAll('.c-bar-chart__axis-line');
      lines.forEach(line => {
        if (line.getAttribute('y1') === line.getAttribute('y2')) {
          line.setAttribute('x2', padLeft + plotW);
        }
      });

      // Update X-axis arrowhead (polygon pointing right)
      const polygons = svg.querySelectorAll('polygon');
      polygons.forEach(poly => {
        const pts = poly.getAttribute('points');
        if (pts && !pts.startsWith(`${padLeft},`)) {
          const arrowTipX = padLeft + plotW + 6;
          const arrowBaseX = padLeft + plotW - 2;
          const y = padTop + plotH;
          poly.setAttribute('points', `${arrowTipX},${y} ${arrowBaseX},${y - 4.5} ${arrowBaseX},${y + 4.5}`);
        }
      });

      // Update group positions across the extended X-axis
      groups.forEach((group, i) => {
        const centerX = padLeft + (i * groupW) + (groupW / 2);
        const bar1X = centerX - (barGap / 2) - barW;
        const bar2X = centerX + (barGap / 2);

        const bar1 = group.querySelector('.j-chart-bar[data-bar-type="series1"]');
        const bar2 = group.querySelector('.j-chart-bar[data-bar-type="series2"]');
        const catLabel = group.querySelector('.c-bar-chart__cat-label');

        if (bar1) {
          bar1.setAttribute('x', bar1X);
          bar1.setAttribute('width', barW);
        }
        if (bar2) {
          bar2.setAttribute('x', bar2X);
          bar2.setAttribute('width', barW);
        }
        if (catLabel) {
          catLabel.setAttribute('x', centerX);
        }
      });
    }
  });
}

/**
 * -------------------------------------------------------------------------
 * 5. ACADEMIC PERFORMANCE CHART FILTERS (Grade, Subject, Term)
 * -------------------------------------------------------------------------
 */
function initAcademicFilters() {
  const container = document.querySelector('[data-academic-perf]');
  if (!container) return;

  let dataset = [];
  try {
    dataset = JSON.parse(container.dataset.academicPerf || '[]');
  } catch (e) {
    return;
  }

  const gradeDropdown   = document.getElementById('j-select-perf-grade');
  const subjectDropdown = document.getElementById('j-select-perf-subject');
  const termDropdown    = document.getElementById('j-select-perf-term');

  function updateChart() {
    const gradeVal   = (gradeDropdown ? gradeDropdown.querySelector('.j-select-value')?.textContent.trim() : 'Grade 6') || 'Grade 6';
    const subjectVal = (subjectDropdown ? subjectDropdown.querySelector('.j-select-value')?.textContent.trim() : 'Mathematics') || 'Mathematics';
    const termVal    = (termDropdown ? termDropdown.querySelector('.j-select-value')?.textContent.trim() : 'Term 1') || 'Term 1';

    const grade = dataset.find(g => g.name === gradeVal || g.id === gradeVal) || dataset[0];
    if (!grade) return;

    const scores = (grade.subjectScores && grade.subjectScores[subjectVal]) || [];
    const termOffset = (termVal === 'Term 2') ? 3 : ((termVal === 'Term 3') ? 6 : 0);
    const classAvg = scores.length ? Math.round(scores.reduce((a, b) => a + b, 0) / scores.length) : 0;

    const panel = document.getElementById('j-academic-perf-chart-panel');
    if (!panel) return;
    const maxVal = parseFloat(panel.dataset.maxval) || 100;
    const plotH  = parseFloat(panel.dataset.ploth) || 344;
    const padTop = parseFloat(panel.dataset.padtop) || 14;

    const groups = panel.querySelectorAll('.j-chart-group');
    grade.classes.forEach((sec, idx) => {
      let avg = scores[idx] != null ? scores[idx] : classAvg;
      avg = Math.min(100, avg + termOffset);
      const high = Math.min(100, avg + 13);

      const group = groups[idx];
      if (!group) return;

      group.dataset.category = sec;
      group.dataset.val1 = avg;
      group.dataset.val2 = high;

      const catLabel = group.querySelector('.c-bar-chart__cat-label');
      if (catLabel) catLabel.textContent = sec;

      const bar1 = group.querySelector('[data-bar-type="series1"]');
      if (bar1) {
        bar1.dataset.value = avg;
        const h1 = Math.max((avg / maxVal) * plotH, 2);
        const y1 = padTop + plotH - h1;
        bar1.setAttribute('y', y1.toFixed(1));
        bar1.setAttribute('height', h1.toFixed(1));
      }

      const bar2 = group.querySelector('[data-bar-type="series2"]');
      if (bar2) {
        bar2.dataset.value = high;
        const h2 = Math.max((high / maxVal) * plotH, 2);
        const y2 = padTop + plotH - h2;
        bar2.setAttribute('y', y2.toFixed(1));
        bar2.setAttribute('height', h2.toFixed(1));
      }
    });
  }

  // Listen to option changes on all 3 dropdowns
  [gradeDropdown, subjectDropdown, termDropdown].forEach(dd => {
    if (!dd) return;
    dd.addEventListener('dropdown:change', updateChart);
    dd.addEventListener('click', (e) => {
      if (e.target.closest('.c-select__option, .c-dropdown__option')) {
        setTimeout(updateChart, 30);
      }
    });
  });
}

