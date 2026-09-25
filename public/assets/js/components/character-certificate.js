/**
 * =========================================================================
 * L'ÉCOLE — CHARACTER CERTIFICATE CONTROLLER & A4 DOCUMENT ENGINE
 * =========================================================================
 * Comprehensive controller handling:
 *   1. Two-tier master filtering (Status Queue tabs + Pathway subtopic tabs + Search)
 *   2. Master-Detail switching between student capsule cards and A4 sheet
 *   3. Smart A4 pagination engine with DOM block measurement & orphan protection
 *   4. Live interactive wording & student particulars editing
 *   5. Student requests modal (reviewing, approving, rejecting, and live merging into certificate)
 *   6. Evidence lightbox modal
 *   7. Certificate finalization and print engine
 * =========================================================================
 */

(function () {
  'use strict';

  /* ============================= SVG ICONS ============================= */
  const ICONS = {
    checkCircle: '<svg class="c-icon" width="16" height="16"><use href="#icon-checkCircle"/></svg>',
    fileCheck: '<svg class="c-icon" width="16" height="16"><use href="#icon-fileCheck"/></svg>',
    grad: '<svg class="c-icon" width="16" height="16"><use href="#icon-graduationCap"/></svg>',
    user: '<svg class="c-icon" width="16" height="16"><use href="#icon-user"/></svg>',
    message: '<svg class="c-icon" width="16" height="16"><use href="#icon-message"/></svg>',
    check: '<svg class="c-icon" width="14" height="14"><use href="#icon-check"/></svg>',
    xCircle: '<svg class="c-icon" width="14" height="14"><use href="#icon-xCircle"/></svg>',
    eye: '<svg class="c-icon" width="14" height="14"><use href="#icon-eye"/></svg>',
    download: '<svg class="c-icon" width="14" height="14"><use href="#icon-download"/></svg>',
    fileText: '<svg class="c-icon" width="48" height="48"><use href="#icon-fileText"/></svg>'
  };

  /* ============================= HELPERS ============================= */
  const escapeHtml = window.escapeHtml || function (str) {
    if (str == null) return '';
    return String(str).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  };

  function initialsFor(name) {
    if (!name) return '?';
    return name.split(' ').map((p) => p[0]).join('').slice(0, 2).toUpperCase();
  }

  function certificatePoints(value) {
    if (!value) return [];
    const parts = value.split(/\r?\n| · /).map((s) => s.trim()).filter(Boolean);
    if (parts.length) return parts;
    return [value.trim()].filter(Boolean);
  }

  function formatForEditing(value) {
    if (!value) return '';
    return certificatePoints(value).join('\n');
  }

  function nowTimestamp() {
    return new Date().toLocaleString('en-GB', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  /* ============================= DATA & STATE ============================= */
  let certificates = (window.CHARACTER_CERTIFICATES && window.CHARACTER_CERTIFICATES.length)
    ? JSON.parse(JSON.stringify(window.CHARACTER_CERTIFICATES))
    : [];

  const state = {
    query: '',
    statusFilter: 'all',     // 'all' | 'pending' | 'issued'
    pathwayFilter: 'all',    // 'all' | 'leaving' | 'graduating'
    selectedId: null,
    isEditing: false,
    isRequestsPanelOpen: false,
    certificatePage: 1,
    totalPages: 1,
    previewFile: null
  };

  function getSelected() {
    return certificates.find((c) => c.id === state.selectedId) || null;
  }

  /* ============================= DOM ELEMENTS ============================= */
  let masterView, detailView, searchInput, statusTabs, pathwayTabs, emptyState, groupSections;
  let backToListBtn, printCertBtn, toggleEditBtn, toggleEditBtnLabel, finalizeBtn, openRequestsPanelBtn;
  let certRequestsCount, certMeasure, certVisiblePage, certPaginationRow, prevPageBtn, nextPageBtn, certPageLabel;
  let certTimelineSection, certTimelineList;
  let requestsModalOverlay, requestsModalBackdrop, closeRequestsModalBtn, requestsModalBody;
  let previewModalOverlay, previewModalBackdrop, closePreviewBtn, previewModalBody, previewModalTitle, previewModalFileSize;

  /* ============================= MASTER FILTER ENGINE ============================= */
  function applyFilter() {
    if (!masterView) return;
    const cards = masterView.querySelectorAll('.j-cert-card');
    let visibleTotal   = 0;
    let pendingVisible = 0;
    let issuedVisible  = 0;

    cards.forEach((card) => {
      const name      = (card.getAttribute('data-name') || '').toLowerCase();
      const id        = (card.getAttribute('data-index') || '').toLowerCase();
      const cohort    = (card.getAttribute('data-cohort') || '').toLowerCase();
      const status    = (card.getAttribute('data-status') || '').toLowerCase();
      const reason    = (card.getAttribute('data-reason') || '').toLowerCase();

      const isPending = status === 'pending review';
      const isIssued  = status === 'issued';
      const isLeaving = reason.includes('leav');
      const isGrad    = reason.includes('graduat');

      // 1. Status Filter (Row 1)
      let matchesStatus = true;
      if (state.statusFilter === 'pending') {
        matchesStatus = isPending;
      } else if (state.statusFilter === 'issued') {
        matchesStatus = isIssued;
      }

      // 2. Pathway Subtopic Filter (Row 2)
      let matchesPathway = true;
      if (state.pathwayFilter === 'leaving') {
        matchesPathway = isLeaving;
      } else if (state.pathwayFilter === 'graduating') {
        matchesPathway = isGrad;
      }

      // 3. Search Query
      let matchesSearch = true;
      if (state.query.trim() !== '') {
        const q = state.query.trim().toLowerCase();
        matchesSearch = name.includes(q) || id.includes(q) || cohort.includes(q) || reason.includes(q);
      }

      const shouldShow = matchesStatus && matchesPathway && matchesSearch;
      if (shouldShow) {
        card.classList.remove('is-hidden');
        card.style.display = '';
        visibleTotal++;
        if (isPending) pendingVisible++;
        if (isIssued) issuedVisible++;
      } else {
        card.classList.add('is-hidden');
        card.style.display = 'none';
      }
    });

    // Toggle Section visibility
    groupSections.forEach((section) => {
      const sectionCards = section.querySelectorAll('.j-cert-card');
      const hasVisibleCards = Array.from(sectionCards).some((c) => !c.classList.contains('is-hidden'));
      section.style.display = hasVisibleCards ? '' : 'none';
    });

    // Update Section badges
    const pendingBadge = document.querySelector('.j-cert-badge-pending');
    if (pendingBadge) pendingBadge.textContent = `${pendingVisible} Pending Review`;
    const issuedBadge = document.querySelector('.j-cert-badge-issued');
    if (issuedBadge) issuedBadge.textContent = `${issuedVisible} Verified`;

    // Handle Empty State
    if (emptyState) {
      if (visibleTotal === 0) {
        emptyState.classList.add('is-visible');
        emptyState.style.display = 'block';
      } else {
        emptyState.classList.remove('is-visible');
        emptyState.style.display = 'none';
      }
    }
  }

  /* ============================= MASTER / DETAIL SWITCHING ============================= */
  function openCertificate(certId) {
    if ((!certificates || !certificates.length) && window.CHARACTER_CERTIFICATES) {
      certificates = JSON.parse(JSON.stringify(window.CHARACTER_CERTIFICATES));
    }
    const cert = certificates.find((c) => c.id === certId);
    if (!cert) return;

    state.selectedId = certId;
    state.isEditing = false;
    state.certificatePage = 1;
    state.totalPages = 1;

    if (masterView) masterView.style.display = 'none';
    if (detailView) detailView.style.display = 'flex';

    window.scrollTo({ top: 0, behavior: 'smooth' });
    renderDetailView();
  }

  function backToList() {
    // If we were editing, flush values
    flushVisiblePageValues();
    state.selectedId = null;
    state.isEditing = false;

    if (detailView) detailView.style.display = 'none';
    if (masterView) masterView.style.display = 'flex';

    applyFilter();
  }

  /* ============================= DETAIL VIEW RENDERING ============================= */
  function renderDetailView() {
    const selected = getSelected();
    if (!selected) return;

    // 1. Update Header Info
    const avatarEl = document.getElementById('certDetailAvatar');
    if (avatarEl) avatarEl.textContent = initialsFor(selected.name);

    const nameEl = document.getElementById('certDetailName');
    if (nameEl) nameEl.textContent = selected.name;

    const statusEl = document.getElementById('certDetailStatus');
    if (statusEl) {
      statusEl.textContent = selected.status;
      statusEl.className = `status-pill ${selected.status === 'Pending review' ? 'pending' : 'issued'}`;
    }

    const sublineEl = document.getElementById('certDetailSubline');
    if (sublineEl) sublineEl.textContent = `${selected.id} · ${selected.cohort}`;

    // 2. Pending Requests Count Badge
    const pendingReqCount =
      ((selected.certificateRequests || []).filter((r) => r.status === 'Pending').length) +
      ((selected.missingRecordRequests || []).filter((r) => r.status === 'Pending').length);

    if (certRequestsCount) {
      if (pendingReqCount > 0) {
        certRequestsCount.textContent = pendingReqCount;
        certRequestsCount.style.display = 'flex';
      } else {
        certRequestsCount.style.display = 'none';
      }
    }

    // 3. Action Buttons State
    if (toggleEditBtnLabel) {
      toggleEditBtnLabel.textContent = state.isEditing ? 'Done editing' : 'Edit wording';
    }

    if (finalizeBtn) {
      finalizeBtn.style.display = selected.status === 'Pending review' ? 'inline-flex' : 'none';
    }

    // 4. Render Measurement DOM and Paginate
    renderMeasurementDom(selected);
    setupPagination();

    // 5. Render Activity Timeline
    renderActivityTimeline(selected);
  }

  /* ============================= MEASUREMENT & PAGINATION ENGINE ============================= */
  function autoExpandTextarea(ta) {
    if (!ta) return;
    ta.style.height = 'auto';
    ta.style.height = Math.max(96, ta.scrollHeight) + 'px';
  }

  function renderFact(label, value, editable, field) {
    if (editable) {
      return `
        <label class="fact-row">
          <span class="fact-label">${escapeHtml(label)}</span>
          <input aria-label="${escapeHtml(label)}" data-particular="${field}" value="${escapeHtml(value)}" />
        </label>`;
    }
    return `
      <div class="fact-row">
        <span class="fact-label">${escapeHtml(label)}</span>
        <span class="fact-value">${escapeHtml(value)}</span>
      </div>`;
  }

  function renderCertSection(iconSvg, label, value, editable, field, pendingAnnotations) {
    pendingAnnotations = pendingAnnotations || [];
    let body;
    const val = value || '';
    if (editable) {
      const editVal = formatForEditing(val);
      body = `<textarea class="cert-textarea" aria-label="${escapeHtml(label)}" data-section="${field}">${escapeHtml(editVal)}</textarea>`;
    } else {
      const points = certificatePoints(val);
      const pointsHtml = points.length
        ? points.map((p) => `<li><span class="dot"></span>${escapeHtml(p)}</li>`).join('')
        : (val ? `<li><span class="dot"></span>${escapeHtml(val)}</li>` : '');
      const annotations = pendingAnnotations.map(
        (a) => `<li class="pending"><span class="dot"></span><span>${escapeHtml(a)}</span><span class="pending-badge">Pending Review</span></li>`
      ).join('');
      body = `<ul class="cert-points">${pointsHtml}${annotations}</ul>`;
    }

    return `
      <section class="cert-section-block">
        <div class="cert-section-heading">
          <h4>${iconSvg}<span>${escapeHtml(label)}</span></h4>
          ${editable ? '<span class="detail">Editable (Press Enter for new point)</span>' : ''}
        </div>
        ${body}
      </section>`;
  }

  function renderMeasurementDom(selected) {
    if (!certMeasure) return;

    const classLabel = (selected.cohort || '').split(' · ')[0] || selected.cohort;
    const studyPeriod = selected.reason === 'Graduating student'
      ? `${classLabel.replace(/-[A-Z]$/, '')} completion`
      : `${classLabel} record`;

    const pendingAcademic = (selected.missingRecordRequests || [])
      .filter((r) => r.status === 'Pending' && r.category === 'Academic')
      .map((r) => r.title);

    const pendingActivities = (selected.missingRecordRequests || [])
      .filter((r) => r.status === 'Pending' && (r.category === 'Sports' || r.category === 'Club' || r.category === 'Other'))
      .map((r) => r.title);

    const pendingConduct = (selected.missingRecordRequests || [])
      .filter((r) => r.status === 'Pending' && r.category === 'Attendance')
      .map((r) => r.title);

    const particularsHtml = `
      <section class="cert-section-block" aria-labelledby="student-particulars-heading">
        <div class="cert-section-heading">
          <h4 id="student-particulars-heading">Student particulars</h4>
          <span class="detail">${state.isEditing ? 'Editable' : 'Verified student record'}</span>
        </div>
        <div class="particulars-grid">
          ${renderFact('Full name', selected.name, state.isEditing, 'name')}
          ${renderFact('Name with initials', initialsFor(selected.name), false)}
          ${renderFact('Student index no.', selected.id, state.isEditing, 'id')}
          ${renderFact('Current class', selected.cohort, state.isEditing, 'cohort')}
          ${renderFact('Period of study', studyPeriod, false)}
        </div>
      </section>`;

    const academicSection = renderCertSection(ICONS.grad, 'Special recognition', selected.academic, state.isEditing, 'academic', pendingAcademic);
    const activitiesSection = renderCertSection(ICONS.fileCheck, 'Extracurricular achievements', selected.activities, state.isEditing, 'activities', pendingActivities);
    const conductSection = renderCertSection(ICONS.user, 'Conduct & character', selected.conduct, state.isEditing, 'conduct', pendingConduct);

    certMeasure.innerHTML = `
      <div id="certContentRef" class="cert-doc-inner">
        <header class="cert-doc-header">
          <p class="school-name">L’ÉCOLE</p>
          <p class="tagline">Institutional excellence since 1994</p>
          <div class="rule"></div>
          <h3>Character Certificate</h3>
          <p class="awarded">Awarded to <b>${escapeHtml(selected.name)}</b></p>
        </header>
        <div class="cert-doc-body">
          ${particularsHtml}
          ${academicSection}
          ${activitiesSection}
          ${conductSection}
        </div>
        <footer class="cert-doc-footer">
          <div>
            <p class="issued-bold">Issued on ${escapeHtml(selected.requestedOn)}</p>
            <p>Student pathway: ${escapeHtml(selected.reason)}</p>
          </div>
          <div class="signature-line">Principal signature</div>
        </footer>
      </div>
    `;

    certMeasure.querySelectorAll('.cert-textarea').forEach(autoExpandTextarea);
  }

  function paginateContent() {
    const contentRef = document.getElementById('certContentRef');
    const pages = window.paginateA4Document ? window.paginateA4Document(contentRef) : [[]];
    state.totalPages = Math.max(1, pages.length);
    if (state.certificatePage > state.totalPages) state.certificatePage = state.totalPages;
    return pages;
  }

  function renderCurrentPage() {
    const pages = paginateContent();
    if (!certVisiblePage) return;

    const pageIdx = state.certificatePage - 1;
    const pageBlocks = pages[pageIdx] || pages[0] || [];

    certVisiblePage.innerHTML = '';
    const inner = document.createElement('div');
    inner.className = 'cert-doc-inner';

    let openSection = null;
    let openList = null;
    let openSIdx = -1;

    function flushSection() {
      if (openSection) {
        inner.appendChild(openSection);
        openSection = null;
        openList = null;
        openSIdx = -1;
      }
    }

    for (const b of pageBlocks) {
      if (b.kind === 'header' || b.kind === 'footer' || b.kind === 'other') {
        flushSection();
        inner.appendChild(b.node.cloneNode(true));
      } else if (b.kind === 'heading') {
        flushSection();
        openSection = document.createElement('div');
        openSection.className = 'cert-section-block';
        openSIdx = b.sIdx;
        openSection.appendChild(b.node.cloneNode(true));
      } else if (b.kind === 'item') {
        if (!openSection || openSIdx !== b.sIdx) {
          flushSection();
          openSection = document.createElement('div');
          openSection.className = 'cert-section-block';
          openSIdx = b.sIdx;
        }
        if (!openList) {
          if (b.listType === 'grid') {
            openList = document.createElement('div');
            openList.className = 'particulars-grid';
          } else {
            openList = document.createElement('ul');
            openList.className = 'cert-points';
          }
          openSection.appendChild(openList);
        }
        openList.appendChild(b.node.cloneNode(true));
      } else if (b.kind === 'textarea') {
        if (!openSection || openSIdx !== b.sIdx) {
          flushSection();
          openSection = document.createElement('div');
          openSection.className = 'cert-section-block';
          openSIdx = b.sIdx;
        }
        openSection.appendChild(b.node.cloneNode(true));
      }
    }
    flushSection();

    // Page Number Overlay
    const overlay = document.createElement('div');
    overlay.className = 'page-footer-overlay no-print';
    overlay.innerHTML = `<p>Page ${state.certificatePage} of ${state.totalPages}</p>`;

    certVisiblePage.appendChild(inner);
    certVisiblePage.appendChild(overlay);

    certVisiblePage.querySelectorAll('.cert-textarea').forEach(autoExpandTextarea);

    // Update Pagination Controls
    if (certPaginationRow) {
      if (state.totalPages > 1) {
        certPaginationRow.style.display = 'flex';
        if (certPageLabel) certPageLabel.textContent = `Page ${state.certificatePage} of ${state.totalPages}`;
        if (prevPageBtn) {
          prevPageBtn.disabled = state.certificatePage === 1;
          prevPageBtn.onclick = () => {
            state.certificatePage = Math.max(1, state.certificatePage - 1);
            renderCurrentPage();
            wireVisiblePageEvents();
          };
        }
        if (nextPageBtn) {
          nextPageBtn.disabled = state.certificatePage === state.totalPages;
          nextPageBtn.onclick = () => {
            state.certificatePage = Math.min(state.totalPages, state.certificatePage + 1);
            renderCurrentPage();
            wireVisiblePageEvents();
          };
        }
      } else {
        certPaginationRow.style.display = 'none';
      }
    }
  }

  function flushVisiblePageValues() {
    if (!certVisiblePage) return;
    certVisiblePage.querySelectorAll('[data-particular]').forEach((input) => {
      updateCertificateInState(input.dataset.particular, input.value);
    });
    certVisiblePage.querySelectorAll('[data-section]').forEach((textarea) => {
      updateCertificateInState(textarea.dataset.section, textarea.value);
    });
  }

  function wireVisiblePageEvents() {
    if (!certVisiblePage) return;

    certVisiblePage.querySelectorAll('[data-particular]').forEach((input) => {
      input.oninput = (e) => {
        updateCertificateInState(input.dataset.particular, e.target.value);
      };
    });

    certVisiblePage.querySelectorAll('[data-section]').forEach((textarea) => {
      textarea.oninput = (e) => {
        autoExpandTextarea(e.target);
        updateCertificateInState(textarea.dataset.section, e.target.value);
        const hiddenTextarea = certMeasure ? certMeasure.querySelector(`[data-section="${textarea.dataset.section}"]`) : null;
        if (hiddenTextarea) {
          hiddenTextarea.value = e.target.value;
          autoExpandTextarea(hiddenTextarea);
        }
      };
    });
  }

  function setupPagination() {
    renderCurrentPage();
    wireVisiblePageEvents();
  }

  /* ============================= DATA MUTATIONS ============================= */
  function updateCertificateInState(field, value) {
    const selected = getSelected();
    if (!selected) return;

    certificates = certificates.map((c) => (c.id === selected.id ? { ...c, [field]: value } : c));
    if (field === 'id') state.selectedId = value;

    // Synchronize card in master list
    const masterCard = document.querySelector(`.j-cert-card[data-id="${selected.id}"]`);
    if (masterCard) {
      if (field === 'name') {
        const title = masterCard.querySelector('h3');
        if (title) title.textContent = value;
        masterCard.setAttribute('data-name', value);
      }
      if (field === 'cohort') {
        const cohortP = masterCard.querySelector('p:nth-of-type(1)');
        if (cohortP) cohortP.textContent = value;
        masterCard.setAttribute('data-cohort', value);
      }
    }
  }

  function updateMissingRecordRequest(certificateId, requestId, updates) {
    certificates = certificates.map((cert) => {
      if (cert.id !== certificateId) return cert;
      const req = (cert.missingRecordRequests || []).find((r) => r.id === requestId);
      const newActivity = {
        id: `ACT-${Date.now()}`,
        timestamp: nowTimestamp(),
        action: `Management updated request status to ${updates.status || updates.managementStatus || 'modified'}`,
        user: 'Management Panel'
      };

      let updatedCert = { ...cert };
      if (updates.status === 'Resolved' && updates.managementStatus === 'Approved' && req) {
        if (req.category === 'Academic') {
          updatedCert.academic = updatedCert.academic ? `${updatedCert.academic} · ${req.title}` : req.title;
        } else if (req.category === 'Sports' || req.category === 'Club' || req.category === 'Other') {
          updatedCert.activities = updatedCert.activities ? `${updatedCert.activities} · ${req.title}` : req.title;
        } else if (req.category === 'Attendance') {
          updatedCert.conduct = updatedCert.conduct ? `${updatedCert.conduct} · ${req.title}` : req.title;
        }
      }

      return {
        ...updatedCert,
        missingRecordRequests: (updatedCert.missingRecordRequests || []).map((r) =>
          r.id === requestId ? { ...r, ...updates, activityLog: [...(r.activityLog || []), newActivity] } : r
        ),
        activityLog: [...(updatedCert.activityLog || []), newActivity]
      };
    });

    renderDetailView();
    renderRequestsModalContent();
  }

  function updateCertificateRequest(certificateId, requestId, updates) {
    certificates = certificates.map((cert) => {
      if (cert.id !== certificateId) return cert;
      const newActivity = {
        id: `ACT-${Date.now()}`,
        timestamp: nowTimestamp(),
        action: `Management updated request status to ${updates.status || 'modified'}`,
        user: 'Management Panel'
      };

      return {
        ...cert,
        certificateRequests: (cert.certificateRequests || []).map((r) =>
          r.id === requestId ? { ...r, ...updates, activityLog: [...(r.activityLog || []), newActivity] } : r
        ),
        activityLog: [...(cert.activityLog || []), newActivity]
      };
    });

    renderDetailView();
    renderRequestsModalContent();
  }

  function finalizeCertificate() {
    const selected = getSelected();
    if (!selected) return;

    const newActivity = {
      id: `ACT-${Date.now()}`,
      timestamp: nowTimestamp(),
      action: 'Certificate officially finalized and verified by Management',
      user: 'Management Panel'
    };

    certificates = certificates.map((c) =>
      c.id === selected.id ? { ...c, status: 'Issued', activityLog: [...(c.activityLog || []), newActivity] } : c
    );

    // Update master card badge & status
    const masterCard = document.querySelector(`.j-cert-card[data-id="${selected.id}"]`);
    if (masterCard) {
      masterCard.setAttribute('data-status', 'Issued');
      const dot = masterCard.querySelector('.cert-pending-dot');
      if (dot) dot.remove();
      const avatarWrap = masterCard.querySelector('.cert-avatar-wrap');
      if (avatarWrap) avatarWrap.classList.remove('has-pending');
    }

    state.isEditing = false;
    renderDetailView();
  }

  /* ============================= TIMELINE RENDERING ============================= */
  function renderActivityTimeline(selected) {
    if (!certTimelineSection || !certTimelineList) return;
    const log = selected.activityLog || [];

    if (!log.length) {
      certTimelineSection.style.display = 'none';
      return;
    }

    certTimelineSection.style.display = 'block';
    certTimelineList.innerHTML = log
      .map((activity, i) => `
        <div class="timeline-item">
          <div class="timeline-dot-col">
            <div class="timeline-dot"></div>
            ${i !== log.length - 1 ? '<div class="timeline-line"></div>' : ''}
          </div>
          <div class="timeline-content">
            <p>${escapeHtml(activity.action)}</p>
            <p>${escapeHtml(activity.timestamp)} · ${escapeHtml(activity.user)}</p>
          </div>
        </div>`)
      .join('');
  }

  /* ============================= STUDENT REQUESTS MODAL ============================= */
  function renderReqActivityTimeline(activityLog) {
    if (!activityLog || !activityLog.length) return '';
    return `
      <div class="req-timeline">
        <p class="lbl">Timeline</p>
        ${activityLog
          .map((activity, i) => `
            <div class="timeline-item">
              <div class="timeline-dot-col">
                <div class="timeline-dot small"></div>
                ${i !== activityLog.length - 1 ? '<div class="timeline-line"></div>' : ''}
              </div>
              <div class="timeline-content small">
                <p>${escapeHtml(activity.action)}</p>
                <p>${escapeHtml(activity.timestamp)} · ${escapeHtml(activity.user)}</p>
              </div>
            </div>`)
          .join('')}
      </div>`;
  }

  function renderCertRequestCard(selected, request) {
    let actionsHtml = '';
    if (request.status === 'Pending') {
      actionsHtml = `
        <div class="req-actions-row">
          <button type="button" class="btn btn-moss flex1" data-approve-cert="${escapeHtml(request.id)}">
            ${ICONS.check} Approve
          </button>
          <button type="button" class="btn btn-maroon-ghost flex1" data-reject-cert="${escapeHtml(request.id)}">
            ${ICONS.xCircle} Reject
          </button>
        </div>`;
    }

    return `
      <div class="req-card">
        <div class="req-card-top">
          <span class="req-id">${escapeHtml(request.id)}</span>
          <span class="req-status-badge ${escapeHtml(request.status)}">${escapeHtml(request.status)}</span>
        </div>
        <div class="req-fields">
          <div class="req-field-row"><span class="k">Purpose:</span><span class="v">${escapeHtml(request.purpose)}</span></div>
          <div class="req-field-row"><span class="k">Copies:</span><span class="v">${escapeHtml(request.copies)}</span></div>
          <div class="req-field-row"><span class="k">Delivery:</span><span class="v">${escapeHtml(request.deliveryMethod || 'Pickup from Office')}</span></div>
          <div class="req-field-row"><span class="k">Submitted:</span><span class="v">${escapeHtml(request.submittedAt)}</span></div>
        </div>
        ${request.managementNotes ? `<div class="mgmt-note"><p>Management Note</p><p>${escapeHtml(request.managementNotes)}</p></div>` : ''}
        ${renderReqActivityTimeline(request.activityLog)}
        ${actionsHtml ? `<div class="req-actions">${actionsHtml}</div>` : ''}
      </div>`;
  }

  function renderMissingRecordCard(selected, request) {
    let actionsHtml = '';
    if (request.status === 'Pending') {
      actionsHtml = `
        <div class="req-actions-row">
          <button type="button" class="btn btn-moss flex1" data-approve-missing="${escapeHtml(request.id)}">
            ${ICONS.check} Approve
          </button>
          <button type="button" class="btn btn-maroon-ghost flex1" data-reject-missing="${escapeHtml(request.id)}">
            ${ICONS.xCircle} Reject
          </button>
        </div>`;
    }

    const evidenceHtml = (request.evidenceFiles && request.evidenceFiles.length)
      ? `
        <div class="evidence-list">
          <p class="evidence-label">Attached Evidence</p>
          ${request.evidenceFiles
            .map(
              (file, i) => `
            <div class="evidence-item">
              <div class="left">
                <div class="icon-box">${file.type === 'image' ? ICONS.eye : ICONS.fileCheck}</div>
                <div class="info">
                  <span class="fname">${escapeHtml(file.name)}</span>
                  ${file.size ? `<span class="fsize">${escapeHtml(file.size)}</span>` : ''}
                </div>
              </div>
              <div class="actions">
                <button type="button" data-preview-file="${escapeHtml(request.id)}::${i}" title="Preview">${ICONS.eye}</button>
                <button type="button" title="Download">${ICONS.download}</button>
              </div>
            </div>`
            )
            .join('')}
        </div>`
      : '';

    return `
      <div class="req-card">
        <div class="req-card-top">
          <span class="req-id">${escapeHtml(request.id)}</span>
          <span class="req-status-badge ${escapeHtml(request.status)}">${escapeHtml(request.status)}</span>
        </div>
        <h4 class="req-title">${escapeHtml(request.title)}</h4>
        <p class="req-desc">${escapeHtml(request.description)}</p>
        <div class="assigned-row">
          ${ICONS.user}
          <span class="txt">Assigned to: <b>${escapeHtml(request.assignedTeacher || 'Unassigned')}</b></span>
          ${request.teacherStatus ? `<span class="teacher-status ${escapeHtml(request.teacherStatus)}">${escapeHtml(request.teacherStatus)}</span>` : ''}
        </div>
        ${request.managementNotes ? `<div class="mgmt-note"><p>Management Note</p><p>${escapeHtml(request.managementNotes)}</p></div>` : ''}
        ${evidenceHtml}
        ${renderReqActivityTimeline(request.activityLog)}
        ${actionsHtml ? `<div class="req-actions">${actionsHtml}</div>` : ''}
      </div>`;
  }

  function renderRequestsModalContent() {
    const selected = getSelected();
    if (!selected || !requestsModalBody) return;

    const certReqs = selected.certificateRequests || [];
    const missingReqs = selected.missingRecordRequests || [];
    const empty = !certReqs.length && !missingReqs.length;

    requestsModalBody.innerHTML = `
      ${certReqs.length ? `
        <section class="req-section">
          <h3>Certificate Requests</h3>
          <div class="req-cards">${certReqs.map((r) => renderCertRequestCard(selected, r)).join('')}</div>
        </section>` : ''}
      ${missingReqs.length ? `
        <section class="req-section">
          <h3>Missing Records</h3>
          <div class="req-cards">${missingReqs.map((r) => renderMissingRecordCard(selected, r)).join('')}</div>
        </section>` : ''}
      ${empty ? `<div class="empty-requests">${ICONS.message}<p>No requests submitted.</p></div>` : ''}
    `;

    // Wire Approve / Reject Cert Request
    requestsModalBody.querySelectorAll('[data-approve-cert]').forEach((btn) => {
      btn.onclick = () => updateCertificateRequest(selected.id, btn.dataset.approveCert, { status: 'Resolved' });
    });
    requestsModalBody.querySelectorAll('[data-reject-cert]').forEach((btn) => {
      btn.onclick = () => updateCertificateRequest(selected.id, btn.dataset.rejectCert, { status: 'Rejected' });
    });

    // Wire Approve / Reject Missing Record
    requestsModalBody.querySelectorAll('[data-approve-missing]').forEach((btn) => {
      btn.onclick = () => updateMissingRecordRequest(selected.id, btn.dataset.approveMissing, { status: 'Resolved', managementStatus: 'Approved' });
    });
    requestsModalBody.querySelectorAll('[data-reject-missing]').forEach((btn) => {
      btn.onclick = () => updateMissingRecordRequest(selected.id, btn.dataset.rejectMissing, { status: 'Rejected', managementStatus: 'Rejected' });
    });

    // Wire Evidence Preview
    requestsModalBody.querySelectorAll('[data-preview-file]').forEach((btn) => {
      btn.onclick = () => {
        const [reqId, idx] = btn.dataset.previewFile.split('::');
        const req = (selected.missingRecordRequests || []).find((r) => r.id === reqId);
        if (req && req.evidenceFiles && req.evidenceFiles[Number(idx)]) {
          openEvidencePreview(req.evidenceFiles[Number(idx)]);
        }
      };
    });
  }

  function openRequestsModal() {
    renderRequestsModalContent();
    if (requestsModalOverlay) {
      requestsModalOverlay.style.display = 'flex';
      requestsModalOverlay.setAttribute('aria-hidden', 'false');
    }
  }

  function closeRequestsModal() {
    if (requestsModalOverlay) {
      requestsModalOverlay.style.display = 'none';
      requestsModalOverlay.setAttribute('aria-hidden', 'true');
    }
  }

  /* ============================= EVIDENCE PREVIEW LIGHTBOX ============================= */
  function openEvidencePreview(file) {
    if (!previewModalOverlay || !previewModalBody) return;

    if (previewModalTitle) previewModalTitle.textContent = file.name || 'Evidence Preview';
    if (previewModalFileSize) previewModalFileSize.textContent = file.size || '';

    if (file.type === 'image') {
      previewModalBody.innerHTML = `<img src="${escapeHtml(file.url)}" alt="${escapeHtml(file.name)}" />`;
    } else {
      previewModalBody.innerHTML = `
        <div class="preview-placeholder">
          ${ICONS.fileText}
          <p>PDF Preview</p>
          <p>In a production system, a full PDF document viewer would render here.</p>
        </div>`;
    }

    previewModalOverlay.style.display = 'flex';
    previewModalOverlay.setAttribute('aria-hidden', 'false');
  }

  function closeEvidencePreview() {
    if (previewModalOverlay) {
      previewModalOverlay.style.display = 'none';
      previewModalOverlay.setAttribute('aria-hidden', 'true');
    }
  }

  /* ============================= INITIALIZATION ============================= */
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Resolve DOM Elements
    masterView             = document.getElementById('j-cert-master-view');
    detailView             = document.getElementById('j-cert-detail-view');
    searchInput            = document.getElementById('j-certificate-search');
    statusTabs             = document.querySelectorAll('.j-cert-status-tab');
    pathwayTabs            = document.querySelectorAll('.j-cert-pathway-tab');
    emptyState             = document.getElementById('j-cert-empty');
    groupSections          = document.querySelectorAll('.j-cert-group');

    backToListBtn          = document.getElementById('backToListBtn');
    printCertBtn           = document.getElementById('printCertBtn');
    toggleEditBtn          = document.getElementById('toggleEditBtn');
    toggleEditBtnLabel     = document.getElementById('toggleEditBtnLabel');
    finalizeBtn            = document.getElementById('finalizeBtn');
    openRequestsPanelBtn   = document.getElementById('openRequestsPanelBtn');
    certRequestsCount      = document.getElementById('certRequestsCount');

    certMeasure            = document.getElementById('certMeasure');
    certVisiblePage        = document.getElementById('certVisiblePage');
    certPaginationRow      = document.getElementById('certPaginationRow');
    prevPageBtn            = document.getElementById('prevPageBtn');
    nextPageBtn            = document.getElementById('nextPageBtn');
    certPageLabel          = document.getElementById('certPageLabel');

    certTimelineSection    = document.getElementById('certTimelineSection');
    certTimelineList       = document.getElementById('certTimelineList');

    requestsModalOverlay   = document.getElementById('requestsModalOverlay');
    requestsModalBackdrop  = document.getElementById('requestsModalBackdrop');
    closeRequestsModalBtn  = document.getElementById('closeRequestsModalBtn');
    requestsModalBody      = document.getElementById('requestsModalBody');

    previewModalOverlay    = document.getElementById('previewModalOverlay');
    previewModalBackdrop   = document.getElementById('previewModalBackdrop');
    closePreviewBtn        = document.getElementById('closePreviewBtn');
    previewModalBody       = document.getElementById('previewModalBody');
    previewModalTitle      = document.getElementById('previewModalTitle');
    previewModalFileSize   = document.getElementById('previewModalFileSize');

    // 2. Bind Master Filters
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        state.query = e.target.value;
        applyFilter();
      });
    }

    statusTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        statusTabs.forEach((t) => t.classList.remove('is-active'));
        tab.classList.add('is-active');
        state.statusFilter = tab.getAttribute('data-status-filter') || 'all';
        applyFilter();
      });
    });

    pathwayTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        pathwayTabs.forEach((t) => t.classList.remove('is-active'));
        tab.classList.add('is-active');
        state.pathwayFilter = tab.getAttribute('data-pathway-filter') || 'all';
        applyFilter();
      });
    });

    // 3. Card Click Action
    document.querySelectorAll('.j-cert-card').forEach((card) => {
      card.addEventListener('click', (e) => {
        e.preventDefault();
        const certId = card.getAttribute('data-id') || card.getAttribute('data-open-cert');
        if (certId) {
          openCertificate(certId);
        }
      });
    });

    document.addEventListener('click', (e) => {
      const card = e.target.closest('.j-cert-card');
      if (!card) return;

      const certId = card.getAttribute('data-id') || card.getAttribute('data-open-cert');
      if (certId) {
        openCertificate(certId);
      }
    });

    // 4. Detail Navigation Actions
    if (backToListBtn) {
      backToListBtn.onclick = backToList;
    }

    if (printCertBtn) {
      printCertBtn.onclick = () => window.print();
    }

    if (toggleEditBtn) {
      toggleEditBtn.onclick = () => {
        flushVisiblePageValues();
        state.isEditing = !state.isEditing;
        renderDetailView();
      };
    }

    if (finalizeBtn) {
      finalizeBtn.onclick = finalizeCertificate;
    }

    // 5. Modals Wireup
    if (openRequestsPanelBtn) {
      openRequestsPanelBtn.onclick = openRequestsModal;
    }
    if (closeRequestsModalBtn) {
      closeRequestsModalBtn.onclick = closeRequestsModal;
    }
    if (requestsModalBackdrop) {
      requestsModalBackdrop.onclick = closeRequestsModal;
    }

    if (closePreviewBtn) {
      closePreviewBtn.onclick = closeEvidencePreview;
    }
    if (previewModalBackdrop) {
      previewModalBackdrop.onclick = closeEvidencePreview;
    }

    // Initial Master Filter Run
    applyFilter();
  });

  // Expose for external access or debugging
  window.__characterCertificateApp = {
    state,
    certificates,
    openCertificate,
    backToList,
    finalizeCertificate
  };
})();
