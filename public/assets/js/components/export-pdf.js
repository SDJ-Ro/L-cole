/**
 * =========================================================================
 * L'ÉCOLE — EXPORT PDF COMPONENT CONTROLLER
 * =========================================================================
 * Universal client-side PDF export engine.
 * Generates an official, print-perfect school transcript / report PDF from
 * any target container (such as the Digital Record Book, Student Profile,
 * or Progress Report) with zero dependencies.
 *
 * Uses isolated off-screen rendering iframe with @page A4 standard styles
 * so it never disrupts or prints background admin navigation, sidebars, or modals.
 * =========================================================================
 */

(function () {
  'use strict';

  /**
   * Main Export Engine
   */
  const LecolePdfExport = {
    /**
     * Export target element to official printable PDF
     * @param {Object} options
     *   - target: HTMLElement | string (CSS selector)
     *   - title: string (document title)
     *   - filename: string (suggested filename)
     *   - studentName: string (optional override)
     *   - studentId: string (optional override)
     *   - grade: string (optional override)
     *   - term: string (optional override)
     */
    export: function (options) {
      options = options || {};
      let targetEl = null;

      if (typeof options.target === 'string') {
        targetEl = document.querySelector(options.target);
      } else if (options.target instanceof HTMLElement) {
        targetEl = options.target;
      } else {
        targetEl = document.querySelector('.j-recordbook-container') || document.querySelector('#j-panel-academics');
      }

      if (!targetEl) {
        console.warn('[LecolePdfExport] Target element not found for PDF export.');
        return false;
      }

      // 1. Gather Metadata from active profile / page context
      const studentName = options.studentName || 
        (document.getElementById('j-modal-name-view') ? document.getElementById('j-modal-name-view').textContent.trim() : '') ||
        'Student Academic Record';

      const studentId = options.studentId || 
        (document.getElementById('j-modal-id-pill') ? document.getElementById('j-modal-id-pill').textContent.trim() : '') ||
        'STU-2026';

      const gradeLabel = options.grade || 
        (document.getElementById('record-grade-label') ? document.getElementById('record-grade-label').textContent.trim() : '') ||
        'Grade 6';

      const termLabel = options.term || 
        (document.getElementById('feedback-term-label') ? document.getElementById('feedback-term-label').textContent.trim() : '') ||
        'Term 1';

      const docTitle = options.title || `${studentName} — ${gradeLabel} (${termLabel}) Record Book`;

      // 2. Extract Marks Table
      const tableEl = targetEl.querySelector('.marks-table');
      let tableHtml = '';
      if (tableEl) {
        // Clone and sanitize marks table
        const cloneTable = tableEl.cloneNode(true);
        // Ensure standard headers and clean cell texts
        tableHtml = cloneTable.outerHTML;
      }

      // 3. Extract Teacher Feedback
      const feedbackCard = targetEl.querySelector('.teacher-feedback-card');
      let feedbackHtml = '';
      if (feedbackCard) {
        const teacherName = feedbackCard.querySelector('.teacher-feedback-name')?.textContent.trim() || 'Class Teacher';
        const feedbackDate = feedbackCard.querySelector('.teacher-feedback-date')?.textContent.trim() || new Date().toLocaleDateString();
        const feedbackText = feedbackCard.querySelector('.teacher-feedback-text')?.textContent.trim() || 
          feedbackCard.querySelector('textarea')?.value || 'Student has demonstrated consistent academic commitment.';

        feedbackHtml = `
          <div class="pdf-feedback-box">
            <div class="pdf-feedback-header">
              <span class="pdf-feedback-badge">Class Teacher's Assessment (${termLabel})</span>
              <span class="pdf-feedback-date">${feedbackDate}</span>
            </div>
            <p class="pdf-feedback-text">"${escapeHtml(feedbackText)}"</p>
            <div class="pdf-feedback-author">
              <strong>Evaluated by:</strong> ${escapeHtml(teacherName)}
            </div>
          </div>
        `;
      }

      // 4. Extract Total / Summary if present
      let totalMarksHtml = '';
      const totalScored = document.getElementById('total-marks-scored')?.textContent.trim();
      const totalOutOf = document.getElementById('total-marks-outof')?.textContent.trim();
      const totalAvg = document.getElementById('total-marks-average')?.textContent.trim();
      const totalPos = document.getElementById('total-marks-position')?.textContent.trim();

      if (totalScored && totalOutOf) {
        totalMarksHtml = `
          <div class="pdf-summary-grid">
            <div class="pdf-summary-card">
              <span class="pdf-summary-label">Total Marks</span>
              <span class="pdf-summary-val">${totalScored} ${totalOutOf}</span>
            </div>
            ${totalAvg ? `
            <div class="pdf-summary-card">
              <span class="pdf-summary-label">Average Score</span>
              <span class="pdf-summary-val">${totalAvg}</span>
            </div>` : ''}
            ${totalPos ? `
            <div class="pdf-summary-card">
              <span class="pdf-summary-label">Class Position</span>
              <span class="pdf-summary-val">${totalPos}</span>
            </div>` : ''}
          </div>
        `;
      }

      // 5. Construct Complete Official Printable HTML Document
      const printableDocument = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>${escapeHtml(docTitle)}</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

  @page {
    size: A4 portrait;
    margin: 14mm 12mm;
  }

  * {
    box-sizing: border-box;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  body {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #0F414A;
    background: #FFFFFF;
    margin: 0;
    padding: 0;
    font-size: 12px;
    line-height: 1.5;
  }

  /* Official Letterhead */
  .pdf-letterhead {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #7F0303;
    padding-bottom: 12px;
    margin-bottom: 16px;
  }

  .pdf-school-brand {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .pdf-crest {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #7F0303;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: 0.05em;
  }

  .pdf-school-title h1 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #0F414A;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .pdf-school-title p {
    margin: 2px 0 0;
    font-size: 10px;
    font-weight: 600;
    color: rgba(15, 65, 74, 0.65);
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }

  .pdf-doc-badge {
    text-align: right;
  }

  .pdf-badge-pill {
    display: inline-block;
    background: #7F0303;
    color: #FFFFFF;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 4px 10px;
    border-radius: 999px;
  }

  .pdf-issued-date {
    margin-top: 4px;
    font-size: 9px;
    font-weight: 500;
    color: rgba(15, 65, 74, 0.5);
  }

  /* Student Details Meta Banner */
  .pdf-student-banner {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 8px;
    background: #F7F3EC;
    border: 1px solid #EFE8DF;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 18px;
  }

  .pdf-meta-item {
    display: flex;
    flex-direction: column;
  }

  .pdf-meta-label {
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(15, 65, 74, 0.55);
  }

  .pdf-meta-val {
    font-size: 12px;
    font-weight: 700;
    color: #0F414A;
    margin-top: 2px;
  }

  /* Section Titles */
  .pdf-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #7F0303;
    margin: 16px 0 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Marks Table Styling */
  .marks-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
    font-size: 11.5px;
  }

  .marks-table th {
    background: #7F0303 !important;
    color: #FFFFFF !important;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 8px 12px;
    text-align: left;
    border: 1px solid #7F0303;
  }

  .marks-table th:nth-child(2),
  .marks-table th:nth-child(3) {
    text-align: center;
  }

  .marks-table td {
    padding: 8px 12px;
    border: 1px solid #EFE8DF;
    color: #0F414A;
    font-weight: 500;
  }

  .marks-table td:first-child {
    font-weight: 700;
  }

  .marks-table td:nth-child(2) {
    text-align: center;
    font-weight: 700;
    color: #7F0303;
  }

  .marks-table td:nth-child(3) {
    text-align: center;
    color: rgba(15, 65, 74, 0.7);
  }

  .marks-table tr:nth-child(even) td {
    background: rgba(247, 243, 236, 0.4);
  }

  /* Summary Grid */
  .pdf-summary-grid {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
  }

  .pdf-summary-card {
    flex: 1;
    background: #F7F3EC;
    border: 1px solid #EFE8DF;
    border-radius: 6px;
    padding: 8px 12px;
    text-align: center;
  }

  .pdf-summary-label {
    display: block;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(15, 65, 74, 0.55);
  }

  .pdf-summary-val {
    display: block;
    font-size: 14px;
    font-weight: 800;
    color: #0F414A;
    margin-top: 2px;
  }

  /* Teacher Feedback Box */
  .pdf-feedback-box {
    background: #F7F3EC;
    border: 1px solid #EFE8DF;
    border-radius: 8px;
    padding: 12px 14px;
    margin-top: 14px;
    margin-bottom: 24px;
  }

  .pdf-feedback-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
  }

  .pdf-feedback-badge {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #7F0303;
  }

  .pdf-feedback-date {
    font-size: 9px;
    color: rgba(15, 65, 74, 0.5);
    font-weight: 600;
  }

  .pdf-feedback-text {
    margin: 0;
    font-size: 11px;
    font-style: italic;
    color: #0F414A;
    line-height: 1.6;
  }

  .pdf-feedback-author {
    margin-top: 6px;
    font-size: 9.5px;
    color: rgba(15, 65, 74, 0.7);
  }

  /* Official Signatures Section */
  .pdf-signatures {
    display: flex;
    justify-content: space-between;
    margin-top: 36px;
    padding-top: 12px;
  }

  .pdf-sig-col {
    width: 200px;
    text-align: center;
  }

  .pdf-sig-line {
    border-top: 1px solid #0F414A;
    margin-bottom: 4px;
  }

  .pdf-sig-label {
    font-size: 9.5px;
    font-weight: 700;
    color: #0F414A;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .pdf-sig-sub {
    font-size: 8px;
    color: rgba(15, 65, 74, 0.5);
  }

  /* Verification Footer */
  .pdf-footer {
    margin-top: 24px;
    border-top: 1px solid #EFE8DF;
    padding-top: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 8.5px;
    color: rgba(15, 65, 74, 0.45);
  }
</style>
</head>
<body>

  <!-- Official School Letterhead -->
  <div class="pdf-letterhead">
    <div class="pdf-school-brand">
      <div class="pdf-crest">L'É</div>
      <div class="pdf-school-title">
        <h1>L'École International</h1>
        <p>Excellence in Global Secondary Education • Established 1994</p>
      </div>
    </div>
    <div class="pdf-doc-badge">
      <span class="pdf-badge-pill">Official Transcript</span>
      <div class="pdf-issued-date">Date: ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
    </div>
  </div>

  <!-- Student Details Banner -->
  <div class="pdf-student-banner">
    <div class="pdf-meta-item">
      <span class="pdf-meta-label">Student Full Name</span>
      <span class="pdf-meta-val">${escapeHtml(studentName)}</span>
    </div>
    <div class="pdf-meta-item">
      <span class="pdf-meta-label">Registration No.</span>
      <span class="pdf-meta-val">${escapeHtml(studentId)}</span>
    </div>
    <div class="pdf-meta-item">
      <span class="pdf-meta-label">Academic Level</span>
      <span class="pdf-meta-val">${escapeHtml(gradeLabel)}</span>
    </div>
    <div class="pdf-meta-item">
      <span class="pdf-meta-label">Evaluation Period</span>
      <span class="pdf-meta-val">${escapeHtml(termLabel)}</span>
    </div>
  </div>

  <!-- Academic Record Table -->
  <div class="pdf-section-title">
    Academic Marks &amp; Subject Performance
  </div>

  ${tableHtml}

  ${totalMarksHtml}

  <!-- Teacher Feedback -->
  ${feedbackHtml}

  <!-- Signatures -->
  <div class="pdf-signatures">
    <div class="pdf-sig-col">
      <div class="pdf-sig-line"></div>
      <div class="pdf-sig-label">Class Teacher</div>
      <div class="pdf-sig-sub">Academic In-Charge</div>
    </div>
    <div class="pdf-sig-col">
      <div class="pdf-sig-line"></div>
      <div class="pdf-sig-label">Head of School / Registrar</div>
      <div class="pdf-sig-sub">Official Institutional Seal</div>
    </div>
  </div>

  <!-- Verification Note -->
  <div class="pdf-footer">
    <span>Document Ref: LEC-${Math.random().toString(36).substring(2, 8).toUpperCase()}-2026</span>
    <span>This is an official computer-generated student record book issued by L'École Administration.</span>
  </div>

</body>
</html>
      `;

      // 6. Print via Isolated Off-Screen Iframe
      printIframe(printableDocument, docTitle);
      return true;
    }
  };

  /**
   * Helper: Print HTML using a hidden iframe
   */
  function printIframe(htmlContent, title) {
    // Remove any existing print iframe
    const oldIframe = document.getElementById('lecole-pdf-print-frame');
    if (oldIframe) oldIframe.remove();

    const iframe = document.createElement('iframe');
    iframe.id = 'lecole-pdf-print-frame';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    iframe.style.opacity = '0';
    iframe.style.zIndex = '-9999';

    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(htmlContent);
    doc.close();

    // Trigger printing once iframe resources are ready
    setTimeout(function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      } catch (err) {
        console.error('[LecolePdfExport] Printing error:', err);
      } finally {
        setTimeout(function () {
          iframe.remove();
        }, 3000);
      }
    }, 450);
  }

  const escapeHtml = window.escapeHtml || function (str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  };

  /**
   * Wire Event Listeners to all Export PDF Buttons
   */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.j-export-pdf-btn, [data-action="export-pdf"], #export-pdf-btn');
    if (!btn) return;

    e.preventDefault();

    // Visual button state: "Generating PDF..."
    const originalHtml = btn.innerHTML;
    const textEl = btn.querySelector('.export-btn__text') || btn;
    btn.disabled = true;
    btn.style.opacity = '0.75';

    if (textEl) textEl.textContent = 'Preparing PDF…';

    try {
      const targetSelector = btn.getAttribute('data-export-target') || '.j-recordbook-container';
      const docTitle = btn.getAttribute('data-export-title') || 'Academic Record Book';
      const filename = btn.getAttribute('data-export-filename') || 'Record_Book.pdf';

      LecolePdfExport.export({
        target: targetSelector,
        title: docTitle,
        filename: filename
      });
    } catch (err) {
      console.error('[LecolePdfExport] Error triggering export:', err);
    } finally {
      setTimeout(function () {
        btn.disabled = false;
        btn.style.opacity = '';
        btn.innerHTML = originalHtml;
      }, 1200);
    }
  });

  /**
   * =========================================================================
   * Universal A4 Smart Pagination Engine
   * =========================================================================
   * Measures content blocks within an offscreen or measuring container and
   * distributes them across printable A4 pages with strict orphan prevention.
   */
  function paginateA4Document(container, options) {
    if (!container) return [[]];
    const el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return [[]];

    const opts = Object.assign({
      pageInnerHeight: 915,
      sectionGap: 28,
      itemGap: 8,
      headingGap: 16,
      minHeadingItems: 3
    }, options);

    el.querySelectorAll('.cert-textarea').forEach(function (ta) {
      ta.style.height = 'auto';
      ta.style.height = Math.max(96, ta.scrollHeight) + 'px';
    });

    const blocks = [];
    const header = el.querySelector('.cert-doc-header');
    if (header) blocks.push({ kind: 'header', node: header, height: header.offsetHeight });

    const body = el.querySelector('.cert-doc-body');
    if (body) {
      Array.from(body.children).forEach(function (sec, sIdx) {
        if (!sec.classList.contains('cert-section-block')) {
          blocks.push({ kind: 'other', node: sec, height: sec.offsetHeight });
          return;
        }

        const heading = sec.querySelector('.cert-section-heading');
        if (heading) blocks.push({ kind: 'heading', node: heading, sIdx: sIdx, height: heading.offsetHeight });

        const textarea = sec.querySelector('.cert-textarea');
        if (textarea) {
          blocks.push({ kind: 'textarea', node: textarea, sIdx: sIdx, height: textarea.offsetHeight });
          return;
        }

        const pointsList = sec.querySelector('.cert-points');
        if (pointsList) {
          Array.from(pointsList.querySelectorAll(':scope > li')).forEach(function (li) {
            blocks.push({ kind: 'item', node: li, sIdx: sIdx, listType: 'points', height: li.offsetHeight });
          });
          return;
        }

        const grid = sec.querySelector('.particulars-grid');
        if (grid) {
          Array.from(grid.querySelectorAll(':scope > .fact-row')).forEach(function (row) {
            blocks.push({ kind: 'item', node: row, sIdx: sIdx, listType: 'grid', height: row.offsetHeight });
          });
        }
      });
    }

    const footer = el.querySelector('.cert-doc-footer');
    if (footer) blocks.push({ kind: 'footer', node: footer, height: footer.offsetHeight });

    if (!blocks.length) return [[]];

    const pages = [[]];
    let curH = 0;

    for (let i = 0; i < blocks.length; i++) {
      const b = blocks[i];

      if (b.kind === 'heading') {
        const sectionItems = [];
        for (let j = i + 1; j < blocks.length && blocks[j].sIdx === b.sIdx; j++) {
          sectionItems.push(blocks[j]);
        }

        const headingCost = b.height + opts.headingGap;
        let minItemsH = headingCost;
        for (let k = 0; k < Math.min(opts.minHeadingItems, sectionItems.length); k++) {
          minItemsH += sectionItems[k].height + opts.itemGap;
        }

        if (curH > 0 && curH + minItemsH + opts.sectionGap > opts.pageInnerHeight) {
          pages.push([]);
          curH = 0;
        }

        pages[pages.length - 1].push(b);
        curH += headingCost;
      } else if (b.kind === 'item' || b.kind === 'textarea') {
        const cost = b.height + opts.itemGap;
        if (curH > 0 && curH + cost > opts.pageInnerHeight) {
          pages.push([]);
          curH = 0;
        }
        pages[pages.length - 1].push(b);
        curH += cost;
      } else {
        const cost = b.height + opts.sectionGap;
        if (curH > 0 && curH + cost > opts.pageInnerHeight) {
          pages.push([]);
          curH = 0;
        }
        pages[pages.length - 1].push(b);
        curH += cost;
      }
    }

    return pages;
  }

  // Export globally for programmatic calls
  LecolePdfExport.paginate = paginateA4Document;
  window.LecolePdfExport = LecolePdfExport;
  window.paginateA4Document = paginateA4Document;

})();

