/**
 * =========================================================================
 * L'ÉCOLE — STUDENT ACADEMIC RECORDS CONTROLLER
 * =========================================================================
 * Connects:
 *   1. Digital Record Book (_digital_record_book.php)
 *   2. Total Marks Metric Card
 *   3. Multi-grade Performance Trend Carousel (Grades 6–11)
 * =========================================================================
 */

(function () {
  'use strict';

  const GRADES = [6, 7, 8, 9, 10, 11];
  const TERMS = ['Term 1', 'Term 2', 'Term 3'];

  // Load backend academic dataset
  let academicData = {};
  const dataScript = document.getElementById('j-academic-data');
  if (dataScript) {
    try {
      academicData = JSON.parse(dataScript.textContent);
    } catch (e) {
      console.error('[StudentAcademic] Failed to parse academic data JSON:', e);
    }
  }

  let currentGrade = 6;
  let currentTerm = 'Term 1';
  let trendViewGrade = 6;

  // DOM Elements
  const recordGradeLabel = document.getElementById('record-grade-label');
  const marksTableBody = document.getElementById('marks-table-body');
  const totalMarksLabel = document.getElementById('total-marks-label');
  const totalMarksScored = document.getElementById('total-marks-scored');
  const totalMarksOutOf = document.querySelector('.total-marks-out-of');
  const totalMarksAverage = document.getElementById('total-marks-average');
  const totalMarksPosition = document.getElementById('total-marks-position');

  const feedbackTermLabel = document.getElementById('feedback-term-label');
  const feedbackGradeLabel = document.getElementById('feedback-grade-label');
  const teacherFeedbackCard = document.getElementById('teacher-feedback-card');

  const trendGradeGridEl = document.getElementById('trend-grade-grid');
  const trendDotsEl = document.getElementById('trend-dots');
  const trendPrevBtn = document.getElementById('trend-prev-btn');
  const trendNextBtn = document.getElementById('trend-next-btn');
  const trendTooltipEl = document.getElementById('trend-tooltip');

  function ordinalSuffix(n) {
    const rem100 = n % 100;
    if (rem100 >= 11 && rem100 <= 13) return n + 'th';
    switch (n % 10) {
      case 1: return n + 'st';
      case 2: return n + 'nd';
      case 3: return n + 'rd';
      default: return n + 'th';
    }
  }

  const escapeHtml = window.escapeHtml || function (str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  };

  /**
   * 1. Render Digital Record Book Marks Table
   */
  function renderMarksTable() {
    if (!marksTableBody) return;
    const gradeObj = academicData[currentGrade];
    if (!gradeObj || !gradeObj.terms || !gradeObj.terms[currentTerm]) return;

    const rows = gradeObj.terms[currentTerm];
    marksTableBody.innerHTML = rows.map(function (r) {
      return '<tr>' +
        '<td>' + escapeHtml(r.subject) + '</td>' +
        '<td>' + escapeHtml(r.marks !== '' ? r.marks : '—') + '</td>' +
        '<td>' + escapeHtml(r.highest !== '' ? r.highest : '—') + '</td>' +
        '</tr>';
    }).join('');
  }

  /**
   * 2. Render Total Marks Card
   */
  function renderTotalMarksCard() {
    const gradeObj = academicData[currentGrade];
    if (!gradeObj || !gradeObj.terms || !gradeObj.terms[currentTerm]) return;

    const rows = gradeObj.terms[currentTerm];
    const scored = rows.reduce(function (sum, r) {
      return sum + (Number(r.marks) || 0);
    }, 0);
    const outOf = rows.length * 100;
    const avg = outOf > 0 ? ((scored / outOf) * 100).toFixed(1) : '0.0';
    const position = (gradeObj.positions && gradeObj.positions[currentTerm]) ? gradeObj.positions[currentTerm] : 1;

    if (totalMarksLabel) totalMarksLabel.textContent = 'TOTAL MARKS (' + currentTerm.toUpperCase() + ')';
    if (totalMarksScored) totalMarksScored.textContent = scored;
    if (totalMarksOutOf) totalMarksOutOf.textContent = '/' + outOf;
    if (totalMarksAverage) totalMarksAverage.textContent = avg + '%';
    if (totalMarksPosition) totalMarksPosition.textContent = ordinalSuffix(position);
  }

  /**
   * 3. Render Teacher's Feedback Card
   */
  function renderTeacherFeedback() {
    const gradeObj = academicData[currentGrade];
    if (!gradeObj) return;

    if (feedbackTermLabel) feedbackTermLabel.textContent = currentTerm;
    if (feedbackGradeLabel) feedbackGradeLabel.textContent = 'Grade ' + currentGrade;

    const feedback = (gradeObj.feedback && gradeObj.feedback[currentTerm]) ? gradeObj.feedback[currentTerm] : {
      name: 'Mrs. Ishara Gunasekara',
      date: 'Apr 17, 2024',
      text: 'A solid performance overall with consistent dedication across key subject areas.'
    };

    if (teacherFeedbackCard) {
      const nameEl = teacherFeedbackCard.querySelector('.teacher-feedback-name');
      const dateEl = teacherFeedbackCard.querySelector('.teacher-feedback-date');
      const textEl = teacherFeedbackCard.querySelector('.teacher-feedback-text');

      if (nameEl) nameEl.textContent = feedback.name;
      if (dateEl) dateEl.textContent = feedback.date;
      if (textEl) textEl.textContent = feedback.text;
    }
  }

  /**
   * 4. Build Clustered Bar SVG for Given Grade Trend
   */
  function buildGradeTrendSvg(grade) {
    const gradeObj = academicData[grade];
    const trend = (gradeObj && gradeObj.trend) ? gradeObj.trend : [
      { term: 'Term 1', mine: 85, grade: 75 },
      { term: 'Term 2', mine: 88, grade: 78 },
      { term: 'Term 3', mine: 90, grade: 80 }
    ];

    const W = 260, H = 130;
    const padLeft = 34, padRight = 8, padTop = 10, padBottom = 30;
    const plotW = W - padLeft - padRight;
    const plotH = H - padTop - padBottom;

    const groupCount = trend.length;
    const groupW = plotW / groupCount;
    const barW = groupW * 0.12;
    const barGap = groupW * 0.06;
    const maxVal = 100;

    let svg = '';

    // Y Axis Grid Marks
    [0, 50, 100].forEach(function (val) {
      const y = padTop + plotH - (val / maxVal) * plotH;
      svg += '<text x="' + (padLeft - 6) + '" y="' + (y + 3) + '" text-anchor="end" font-size="8.5" fill="#93a1a2" font-family="inherit">' + val + '</text>';
    });

    // Y Axis Line
    svg += '<line x1="' + padLeft + '" y1="' + padTop + '" x2="' + padLeft + '" y2="' + (padTop + plotH) + '" stroke="#c9c0aa" stroke-width="1.25"/>';
    // X Axis Line
    svg += '<line x1="' + padLeft + '" y1="' + (padTop + plotH) + '" x2="' + (W - padRight) + '" y2="' + (padTop + plotH) + '" stroke="#c9c0aa" stroke-width="1.25"/>';

    // Rotated Y Title
    const yTitleX = 9;
    const yTitleY = padTop + plotH / 2;
    svg += '<text x="' + yTitleX + '" y="' + yTitleY + '" text-anchor="middle" font-size="8.5" font-weight="600" letter-spacing="0.05em" fill="#4B5B34" font-family="inherit" transform="rotate(-90 ' + yTitleX + ' ' + yTitleY + ')">MARKS</text>';

    // X Title
    svg += '<text x="' + (padLeft + plotW / 2) + '" y="' + (H - 4) + '" text-anchor="middle" font-size="8.5" font-weight="600" letter-spacing="0.05em" fill="#4B5B34" font-family="inherit">ACADEMIC TERM</text>';

    // Bars
    trend.forEach(function (d, i) {
      const groupX = padLeft + i * groupW;
      const barMineX = groupX + groupW / 2 - barGap / 2 - barW;
      const barGradeX = groupX + groupW / 2 + barGap / 2;

      const mineH = (d.mine / maxVal) * plotH;
      const gradeH = (d.grade / maxVal) * plotH;
      const mineY = padTop + plotH - mineH;
      const gradeY = padTop + plotH - gradeH;

      svg += '<rect class="trend-bar" data-grade-num="' + grade + '" data-term="' + d.term + '" data-mine="' + d.mine + '" data-grade="' + d.grade + '" ' +
        'x="' + barMineX + '" y="' + mineY + '" width="' + barW + '" height="' + Math.max(mineH, 2) + '" rx="2" fill="#1f3a3d" style="cursor:pointer; transition: opacity 0.15s;"/>';
      svg += '<rect class="trend-bar" data-grade-num="' + grade + '" data-term="' + d.term + '" data-mine="' + d.mine + '" data-grade="' + d.grade + '" ' +
        'x="' + barGradeX + '" y="' + gradeY + '" width="' + barW + '" height="' + Math.max(gradeH, 2) + '" rx="2" fill="#e7b6b0" style="cursor:pointer; transition: opacity 0.15s;"/>';

      svg += '<text x="' + (groupX + groupW / 2) + '" y="' + (padTop + plotH + 13) + '" text-anchor="middle" font-size="9" font-weight="600" fill="#5c6b6c" font-family="inherit">T' + (i + 1) + '</text>';
    });

    return { svg: svg, W: W, H: H };
  }

  /**
   * 5. Render Carousel Performance Trend Card
   */
  function renderTrendChart() {
    if (!trendGradeGridEl) return;
    const grade = trendViewGrade;
    const chartData = buildGradeTrendSvg(grade);
    const gradeObj = academicData[grade];
    const trend = (gradeObj && gradeObj.trend) ? gradeObj.trend : [];
    const overallAvg = trend.length > 0 ? Math.round(trend.reduce(function (sum, d) { return sum + d.mine; }, 0) / trend.length) : 85;
    const isCurrent = (grade === currentGrade);

    trendGradeGridEl.innerHTML =
      '<div class="trend-grade-card' + (isCurrent ? ' current' : '') + '" data-grade-num="' + grade + '">' +
        '<div class="trend-grade-card-header">' +
          '<span class="trend-grade-card-title">Grade ' + grade + '</span>' +
          '<span class="trend-grade-card-avg">Avg ' + overallAvg + '%</span>' +
        '</div>' +
        '<svg class="trend-grade-card-chart" viewBox="0 0 ' + chartData.W + ' ' + chartData.H + '" preserveAspectRatio="none">' +
          chartData.svg +
        '</svg>' +
      '</div>';

    // Tooltip listeners on bars
    trendGradeGridEl.querySelectorAll('.trend-bar').forEach(function (bar) {
      bar.addEventListener('mouseenter', function (e) {
        showTrendTooltip(e.target);
      });
      bar.addEventListener('mousemove', function (e) {
        positionTrendTooltip(e);
      });
      bar.addEventListener('mouseleave', hideTrendTooltip);
    });

    // Clicking card syncs record book
    const card = trendGradeGridEl.querySelector('.trend-grade-card');
    if (card) {
      card.addEventListener('click', function () {
        selectGrade(Number(card.dataset.gradeNum));
      });
    }

    // Indicator Dots (Grades 6–11)
    if (trendDotsEl) {
      const gradeIndex = GRADES.indexOf(grade);
      trendDotsEl.innerHTML = GRADES.map(function (g, i) {
        return '<button class="trend-dot' + (i === gradeIndex ? ' active' : '') + '" data-grade-num="' + g + '" aria-label="Show Grade ' + g + '" type="button"></button>';
      }).join('');

      trendDotsEl.querySelectorAll('.trend-dot').forEach(function (dot) {
        dot.addEventListener('click', function () {
          trendViewGrade = Number(dot.dataset.gradeNum);
          renderTrendChart();
        });
      });
    }

    // Update Arrow Disabled States
    const gradeIdx = GRADES.indexOf(grade);
    if (trendPrevBtn) trendPrevBtn.disabled = (gradeIdx <= 0);
    if (trendNextBtn) trendNextBtn.disabled = (gradeIdx >= GRADES.length - 1);
  }

  function showTrendTooltip(bar) {
    if (!trendTooltipEl) return;
    const gradeNum = bar.dataset.gradeNum;
    const term = bar.dataset.term;
    const mine = bar.dataset.mine;
    const grade = bar.dataset.grade;

    trendTooltipEl.innerHTML =
      '<strong>Grade ' + gradeNum + ' — ' + escapeHtml(term) + '</strong><br>' +
      'My Average: <span class="tt-mine">' + mine + '%</span><br>' +
      'Grade Average: <span class="tt-grade">' + grade + '%</span>';
    trendTooltipEl.hidden = false;
  }

  function positionTrendTooltip(e) {
    if (!trendTooltipEl) return;
    trendTooltipEl.style.left = e.clientX + 'px';
    trendTooltipEl.style.top = (e.clientY - 14) + 'px';
  }

  function hideTrendTooltip() {
    if (trendTooltipEl) trendTooltipEl.hidden = true;
  }

  /**
   * 6. Global Switch Handlers
   */
  function selectGrade(newGrade) {
    currentGrade = Number(newGrade);
    trendViewGrade = currentGrade;

    if (recordGradeLabel) recordGradeLabel.textContent = 'Grade ' + currentGrade;
    const allGradeLabels = document.querySelectorAll('.j-record-grade-label');
    allGradeLabels.forEach(function (el) {
      el.textContent = 'Grade ' + currentGrade;
    });

    // Update grade dropdown trigger text if custom dropdown exists
    const gradeDropdown = document.getElementById('j-record-grade-select');
    if (gradeDropdown) {
      const valLabel = gradeDropdown.querySelector('.j-select-value, .c-dropdown__value');
      if (valLabel) valLabel.textContent = 'Grade ' + currentGrade;
      const hidden = gradeDropdown.querySelector('input[type="hidden"]');
      if (hidden) hidden.value = String(currentGrade);
    }

    renderAcademicPage();
  }

  function selectTerm(newTerm) {
    currentTerm = newTerm;

    // Update term dropdown trigger text if custom dropdown exists
    const termDropdown = document.getElementById('j-record-term-select');
    if (termDropdown) {
      const valLabel = termDropdown.querySelector('.j-select-value, .c-dropdown__value');
      if (valLabel) valLabel.textContent = currentTerm;
      const hidden = termDropdown.querySelector('input[type="hidden"]');
      if (hidden) hidden.value = currentTerm;
    }

    renderAcademicPage();
  }

  function renderAcademicPage() {
    renderMarksTable();
    renderTotalMarksCard();
    renderTeacherFeedback();
    renderTrendChart();
  }

  /**
   * 7. Initialize Listeners
   */
  document.addEventListener('DOMContentLoaded', function () {
    // Top Carousel Prev / Next
    if (trendPrevBtn) {
      trendPrevBtn.addEventListener('click', function () {
        const i = GRADES.indexOf(trendViewGrade);
        if (i > 0) {
          trendViewGrade = GRADES[i - 1];
          renderTrendChart();
        }
      });
    }

    if (trendNextBtn) {
      trendNextBtn.addEventListener('click', function () {
        const i = GRADES.indexOf(trendViewGrade);
        if (i < GRADES.length - 1) {
          trendViewGrade = GRADES[i + 1];
          renderTrendChart();
        }
      });
    }

    // Intercept dropdown clicks from _dropdown.php components
    document.addEventListener('click', function (e) {
      const opt = e.target.closest('.c-dropdown__option');
      if (!opt) return;
      const dropdown = opt.closest('.c-dropdown');
      if (!dropdown) return;

      const val = opt.getAttribute('data-value');
      if (dropdown.id === 'j-record-grade-select' || dropdown.closest('#j-record-grade-select')) {
        selectGrade(val);
      } else if (dropdown.id === 'j-record-term-select' || dropdown.closest('#j-record-term-select')) {
        selectTerm(val);
      }
    });

    // Native changes / hidden input changes
    document.addEventListener('change', function (e) {
      const target = e.target;
      if (target.id === 'j-record-grade-select' || target.name === 'j-record-grade-select') {
        selectGrade(target.value);
      } else if (target.id === 'j-record-term-select' || target.name === 'j-record-term-select') {
        selectTerm(target.value);
      }
    });

    // Initial render
    renderAcademicPage();
  });

})();
