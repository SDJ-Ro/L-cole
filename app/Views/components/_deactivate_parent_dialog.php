<!-- =========================================================================
     L'ÉCOLE — DEACTIVATE PARENT DIALOG
     Modal to deactivate parent account with Sibling-Guard validation notices.
     ========================================================================= -->
<dialog id="deactivate-parent-dialog" class="c-modal-dialog" aria-labelledby="deactivate-parent-title" aria-describedby="deactivate-parent-description">
  <div class="c-modal-content c-modal-deactivate">
    <div class="c-modal-header c-modal-header--danger">
      <div class="c-modal-header-icon c-modal-header-icon--danger">
        <svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <div>
        <h2 id="deactivate-parent-title" class="c-modal-title">Deactivate Parent Account</h2>
        <p class="c-modal-subtitle">Confirm deactivation of parent portal credentials.</p>
      </div>
    </div>

    <div class="c-modal-body">
      <div class="c-deactivate-parent-meta">
        <strong id="deactivate-parent-name" class="c-deactivate-name"></strong>
        <span id="deactivate-parent-code" class="c-badge-pill c-badge-terracotta"></span>
      </div>

      <div class="c-deactivate-guard-notice">
        <p id="deactivate-parent-description" class="c-deactivate-desc"></p>
      </div>

      <div id="deactivate-parent-error" class="c-alert-banner c-alert-danger" role="alert" style="display:none;"></div>
    </div>

    <div class="c-modal-footer">
      <button type="button" id="cancel-parent-deactivation" class="c-btn-ghost j-deactivate-parent-close">Cancel</button>
      <button type="button" id="confirm-parent-deactivation" class="c-btn-solid-danger">Deactivate Account</button>
    </div>
  </div>
</dialog>
