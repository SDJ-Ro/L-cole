<?php
/**
 * =========================================================================
 * L'ÉCOLE — CHARACTER CERTIFICATE MODALS COMPONENT
 * =========================================================================
 * Contains the Student Requests Modal drawer and the Evidence Preview Lightbox.
 * Loaded in character certificate views.
 * =========================================================================
 */
?>

<!-- Student Requests Modal Drawer -->
<div class="modal-overlay" id="requestsModalOverlay" style="display: none;" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="requestsModalTitle">
  <div class="modal-backdrop" id="requestsModalBackdrop"></div>
  <div class="modal-panel">
    <header class="modal-header">
      <h2 id="requestsModalTitle">Student Requests</h2>
      <button type="button" class="modal-close" id="closeRequestsModalBtn" aria-label="Close modal">
        <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-x"/>
        </svg>
      </button>
    </header>
    <div class="modal-body" id="requestsModalBody">
      <!-- Dynamically populated by character-certificate.js -->
    </div>
  </div>
</div>

<!-- Evidence Preview Modal Lightbox -->
<div class="modal-overlay" id="previewModalOverlay" style="display: none;" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="previewModalTitle">
  <div class="modal-backdrop dark" id="previewModalBackdrop"></div>
  <div class="modal-panel preview">
    <header class="preview-header">
      <div class="left">
        <div class="icon-box">
          <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-fileCheck"/>
          </svg>
        </div>
        <div>
          <h2 id="previewModalTitle">Evidence Preview</h2>
          <p class="fsize" id="previewModalFileSize"></p>
        </div>
      </div>
      <div class="actions">
        <button type="button" class="btn btn-white-outline" id="previewDownloadBtn">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-download"/>
          </svg>
          <span>Download</span>
        </button>
        <button type="button" class="modal-close" id="closePreviewBtn" aria-label="Close preview">
          <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-x"/>
          </svg>
        </button>
      </div>
    </header>
    <div class="preview-body" id="previewModalBody">
      <!-- Dynamically populated by character-certificate.js -->
    </div>
  </div>
</div>
