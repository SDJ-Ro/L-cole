<?php
/**
 * MVC/app/Views/components/_certificate_workspace.php
 * Reusable Certificate Detail & Editing Workspace Component
 *
 * Contains:
 * - Back button & Print button toolbar
 * - Student info & management action header (Requests, Edit wording, Finalise)
 * - Measurement container pre-rendering the pure A4 certificate component (_character_certificate_a4.php)
 * - Visible A4 paginated sheet article
 * - Dark-green pagination controls (Previous / Next page)
 * - Lightweight certificate activity event trail
 */

$certificate = $certificate ?? ($certificates[0] ?? []);
$isEditing   = $isEditing ?? false;
?>

<!-- DETAIL VIEW & A4 CERTIFICATE DOCUMENT WORKSPACE -->
<div class="c-cert-detail-view" id="j-cert-detail-view" style="display: none;">
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
    <button type="button" class="c-back-link-btn no-print" id="backToListBtn">
      <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <use href="#icon-chevronLeft"/>
      </svg>
      <span>Back</span>
    </button>

    <button type="button" class="btn btn-outline no-print" id="printCertBtn">
      <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#icon-printer"/>
      </svg>
      <span>Print certificate</span>
    </button>
  </div>

  <section class="cert-detail-panel">
    <header class="cert-detail-header no-print" id="certDetailHeader">
      <div class="left">
        <span class="avatar-circle" id="certDetailAvatar">NP</span>
        <div>
          <div class="name-row">
            <h2 id="certDetailName">Student Name</h2>
            <span class="status-pill pending" id="certDetailStatus">Pending review</span>
          </div>
          <p class="sub-line" id="certDetailSubline">ID · Cohort</p>
        </div>
      </div>
      <div class="header-actions">
        <div class="btn-badge-wrap">
          <button type="button" class="btn btn-sand" id="openRequestsPanelBtn">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="#icon-message"/>
            </svg>
            <span>Student Requests</span>
          </button>
          <span class="badge-count" id="certRequestsCount" style="display: none;">0</span>
        </div>

        <button type="button" class="btn btn-sand" id="toggleEditBtn">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-pencil"/>
          </svg>
          <span id="toggleEditBtnLabel">Edit wording</span>
        </button>

        <button type="button" class="btn btn-maroon" id="finalizeBtn">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-send"/>
          </svg>
          <span>Finalise</span>
        </button>
      </div>
    </header>

    <div class="cert-doc-area">
      <!-- Hidden measurement container pre-rendered via reusable component -->
      <div id="certMeasure" style="position: absolute; left: -9999px; top: 0; width: 684px; visibility: hidden; pointer-events: none;">
        <?php
        require __DIR__ . '/_character_certificate_a4.php';
        ?>
      </div>

      <!-- Visible A4 page -->
      <article class="certificate-a4-page" id="certVisiblePage">
        <!-- Dynamically rendered page content -->
      </article>

      <!-- Pagination controls (both Previous and Next are dark green buttons) -->
      <div class="pagination-row no-print" id="certPaginationRow" style="display: none;">
        <button type="button" class="btn btn-dark" id="prevPageBtn">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-chevronLeft"/>
          </svg>
          <span>Previous page</span>
        </button>
        <span id="certPageLabel">Page 1 of 1</span>
        <button type="button" class="btn btn-dark" id="nextPageBtn">
          <span>Next page</span>
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-chevronRight"/>
          </svg>
        </button>
      </div>
    </div>
  </section>

  <!-- Certificate Activity Timeline -->
  <section class="timeline-section no-print" id="certTimelineSection" style="display: none;">
    <h3>Certificate Activity</h3>
    <div id="certTimelineList">
      <!-- Rendered dynamically -->
    </div>
  </section>
</div>
