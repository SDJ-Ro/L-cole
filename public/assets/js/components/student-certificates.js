/**
 * L'École — Student Character Certificate Client Component
 * Handles A4 smart pagination, official certificate requests,
 * missing record submissions, and print/download.
 */

document.addEventListener('DOMContentLoaded', () => {
  /* ============================= CONSTANTS & STATE ============================= */
  let currentPage = 1;
  let totalPages = 1;
  let pagesData = [[]];

  /* DOM Elements */
  const certVisiblePage   = document.getElementById('certVisiblePage');
  const certMeasure       = document.getElementById('certMeasure');
  const certPaginationRow = document.getElementById('certPaginationRow');
  const certPageLabel     = document.getElementById('certPageLabel');
  const certPrevBtn       = document.getElementById('certPrevBtn');
  const certNextBtn       = document.getElementById('certNextBtn');
  const certPrintBtn      = document.getElementById('certPrintBtn');

  /* Modals Elements */
  const rccForm           = document.getElementById('rcc-form');
  const mrrForm           = document.getElementById('mrr-form');

  /* ============================= A4 SMART PAGINATION ============================= */
  function paginateContent() {
    return window.paginateA4Document ? window.paginateA4Document(certMeasure) : [[]];
  }

  function renderPage(pageNum) {
    if (!certVisiblePage || !pagesData.length) return;
    const pageBlocks = pagesData[pageNum - 1] || [];

    certVisiblePage.innerHTML = '';
    const inner = document.createElement('div');
    inner.className = 'cert-doc-inner';

    let curBody = null;
    let curSecMap = {};

    pageBlocks.forEach(b => {
      if (b.kind === 'header' || b.kind === 'footer' || b.kind === 'other') {
        inner.appendChild(b.node.cloneNode(true));
        return;
      }

      if (!curBody) {
        curBody = document.createElement('div');
        curBody.className = 'cert-doc-body';
        inner.appendChild(curBody);
      }

      const sIdx = b.sIdx;
      if (!curSecMap[sIdx]) {
        const secDiv = document.createElement('section');
        secDiv.className = 'cert-section-block';
        curBody.appendChild(secDiv);
        curSecMap[sIdx] = { container: secDiv, pointsList: null, grid: null };
      }

      const secInfo = curSecMap[sIdx];

      if (b.kind === 'heading') {
        secInfo.container.appendChild(b.node.cloneNode(true));
      } else if (b.kind === 'item') {
        if (b.listType === 'points') {
          if (!secInfo.pointsList) {
            secInfo.pointsList = document.createElement('ul');
            secInfo.pointsList.className = 'cert-points';
            secInfo.container.appendChild(secInfo.pointsList);
          }
          secInfo.pointsList.appendChild(b.node.cloneNode(true));
        } else if (b.listType === 'grid') {
          if (!secInfo.grid) {
            secInfo.grid = document.createElement('div');
            secInfo.grid.className = 'particulars-grid';
            secInfo.container.appendChild(secInfo.grid);
          }
          secInfo.grid.appendChild(b.node.cloneNode(true));
        }
      }
    });

    certVisiblePage.appendChild(inner);

    /* Page Overlay Counter */
    if (totalPages > 1) {
      const overlay = document.createElement('div');
      overlay.className = 'page-footer-overlay no-print';
      overlay.innerHTML = `<p>Page ${pageNum} of ${totalPages}</p>`;
      certVisiblePage.appendChild(overlay);
    }

    updatePaginationUI();
  }

  function updatePaginationUI() {
    if (!certPaginationRow) return;
    if (totalPages <= 1) {
      certPaginationRow.style.display = 'none';
      return;
    }

    certPaginationRow.style.display = 'flex';
    if (certPageLabel) {
      certPageLabel.textContent = `Page ${currentPage} of ${totalPages}`;
    }

    if (certPrevBtn) certPrevBtn.disabled = currentPage <= 1;
    if (certNextBtn) certNextBtn.disabled = currentPage >= totalPages;
  }

  function initPagination() {
    setTimeout(() => {
      pagesData = paginateContent();
      totalPages = Math.max(1, pagesData.length);
      currentPage = 1;
      renderPage(currentPage);
    }, 50);
  }

  /* Pagination Navigation Events */
  if (certPrevBtn) {
    certPrevBtn.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        renderPage(currentPage);
        certVisiblePage.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  if (certNextBtn) {
    certNextBtn.addEventListener('click', () => {
      if (currentPage < totalPages) {
        currentPage++;
        renderPage(currentPage);
        certVisiblePage.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  /* Print Handler */
  if (certPrintBtn) {
    certPrintBtn.addEventListener('click', () => {
      window.print();
    });
  }

  /* ============================= MODAL POPUPS ============================= */
  // Note: Modal closing, backdrop clicks, and [Escape] key are automatically
  // handled globally by common/dialogs-and-popups.js.
  document.addEventListener('click', (e) => {
    if (e.target.closest('#openRccModalBtn, #cert-request-btn, .cert-request-btn, [data-trigger="rcc-modal"]')) {
      e.preventDefault();
      openModal('rcc-overlay');
      return;
    }
    if (e.target.closest('#openMrrModalBtn, #cert-missing-btn, .cert-missing-btn, [data-trigger="mrr-modal"]')) {
      e.preventDefault();
      openModal('mrr-overlay');
      return;
    }
  });

  // Clear errors dynamically on input or dropdown select
  document.addEventListener('dropdown:change', (e) => {
    const root = e.target;
    if (root.id === 'rcc-purpose-dropdown') {
      const err = document.getElementById('rcc-purpose-error');
      if (err) err.style.display = 'none';
    } else if (root.id === 'rcc-delivery-dropdown') {
      const err = document.getElementById('rcc-delivery-error');
      if (err) err.style.display = 'none';
    } else if (root.id === 'mrr-type-dropdown') {
      const err = document.getElementById('mrr-type-error');
      if (err) err.style.display = 'none';
    }
  });

  document.addEventListener('input', (e) => {
    const id = e.target.id;
    if (id === 'rcc-copies') {
      const err = document.getElementById('rcc-copies-error');
      if (err) err.style.display = 'none';
    } else if (id === 'mrr-year') {
      const err = document.getElementById('mrr-year-error');
      if (err) err.style.display = 'none';
    } else if (id === 'mrr-item-title') {
      const err = document.getElementById('mrr-item-title-error');
      if (err) err.style.display = 'none';
    } else if (id === 'mrr-desc') {
      const err = document.getElementById('mrr-desc-error');
      if (err) err.style.display = 'none';
    }
  });

  if (rccForm) {
    rccForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const purposeInput = document.querySelector('#rcc-purpose-dropdown input[name="purpose"]');
      const deliveryInput = document.querySelector('#rcc-delivery-dropdown input[name="delivery"]');
      const purpose = purposeInput ? purposeInput.value : '';
      const delivery = deliveryInput ? deliveryInput.value : '';
      const purposeErr = document.getElementById('rcc-purpose-error');
      const deliveryErr = document.getElementById('rcc-delivery-error');

      let valid = true;
      if (!purpose) {
        if (purposeErr) purposeErr.style.display = 'block';
        valid = false;
      } else if (purposeErr) {
        purposeErr.style.display = 'none';
      }

      if (!delivery) {
        if (deliveryErr) deliveryErr.style.display = 'block';
        valid = false;
      } else if (deliveryErr) {
        deliveryErr.style.display = 'none';
      }

      if (!valid) return;

      closeModal('rcc-overlay');
      const randRef = 'CCR-2026-' + Math.floor(1000 + Math.random() * 9000);
      const refEl = document.getElementById('rcc-ref');
      if (refEl) refEl.textContent = 'Reference: ' + randRef;
      openModal('rcc-success-overlay');
      rccForm.reset();
      resetDropdown('rcc-purpose-dropdown', 'Select a purpose...');
      resetDropdown('rcc-delivery-dropdown', 'Select delivery method...');
      if (typeof showFeedbackBanner === 'function') {
        showFeedbackBanner('Certificate requested successfully! Ref: ' + randRef, 'success');
      }
    });
  }

  /* Missing Record Request Form Submission */
  if (mrrForm) {
    mrrForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const recTypeInput = document.querySelector('#mrr-type-dropdown input[name="type"]');
      const recType = recTypeInput ? recTypeInput.value : '';
      const year = document.getElementById('mrr-year') ? document.getElementById('mrr-year').value.trim() : '';
      const title = document.getElementById('mrr-item-title') ? document.getElementById('mrr-item-title').value.trim() : '';
      const desc = document.getElementById('mrr-desc') ? document.getElementById('mrr-desc').value.trim() : '';

      const typeErr = document.getElementById('mrr-type-error');
      const yearErr = document.getElementById('mrr-year-error');
      const titleErr = document.getElementById('mrr-item-title-error');
      const descErr = document.getElementById('mrr-desc-error');

      let valid = true;
      if (!recType) {
        if (typeErr) typeErr.style.display = 'block';
        valid = false;
      } else if (typeErr) typeErr.style.display = 'none';

      if (!year) {
        if (yearErr) yearErr.style.display = 'block';
        valid = false;
      } else if (yearErr) yearErr.style.display = 'none';

      if (!title) {
        if (titleErr) titleErr.style.display = 'block';
        valid = false;
      } else if (titleErr) titleErr.style.display = 'none';

      if (!desc) {
        if (descErr) descErr.style.display = 'block';
        valid = false;
      } else if (descErr) descErr.style.display = 'none';

      if (!valid) return;

      closeModal('mrr-overlay');
      const randRef = 'MRR-2026-' + Math.floor(1000 + Math.random() * 9000);
      const refEl = document.getElementById('mrr-ref');
      if (refEl) refEl.textContent = 'Reference: ' + randRef;
      openModal('mrr-success-overlay');
      mrrForm.reset();
      resetDropdown('mrr-type-dropdown', 'Select a record type...');
      if (typeof showFeedbackBanner === 'function') {
        showFeedbackBanner('Missing record request submitted! Ref: ' + randRef, 'success');
      }
    });
  }

  /* Kick off initial pagination */
  initPagination();
});
