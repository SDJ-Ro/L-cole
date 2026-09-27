<!-- =========================================================================
     L'ÉCOLE — PARENT PICKER MODAL
     Interactive dialog for searching and linking existing parents to students.
     ========================================================================= -->
<dialog id="parent-picker" class="c-parent-picker-modal" aria-labelledby="parent-picker-title">
  <div class="c-parent-picker-wrapper">
    <div class="c-parent-picker-header">
      <div class="c-parent-picker-title-group">
        <div class="c-parent-picker-icon">
          <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <div>
          <h2 id="parent-picker-title" class="c-parent-picker-title">Select Registered Parent</h2>
          <p class="c-parent-picker-desc">Search by parent name, Parent ID code, email, NIC, or phone.</p>
        </div>
      </div>
      <button type="button" class="c-parent-picker-close j-parent-picker-close" aria-label="Close dialog">
        <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="6"></line></svg>
      </button>
    </div>

    <div class="c-parent-picker-body">
      <div class="c-parent-picker-search-bar">
        <svg class="c-icon c-parent-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="search" id="parent-picker-search" class="c-parent-search-input" placeholder="Search parents by name, code, email..." autocomplete="off">
        <span id="parent-picker-spinner" class="c-parent-picker-spinner" style="display:none;"></span>
      </div>

      <p id="parent-picker-count" class="c-parent-picker-count" role="status">Type at least 2 characters to search active parents.</p>

      <div id="parent-picker-results" class="c-parent-picker-results" role="region" aria-live="polite">
        <!-- Search results dynamically rendered here -->
      </div>
    </div>

    <div class="c-parent-picker-footer">
      <button type="button" id="parent-picker-cancel" class="c-btn-ghost j-parent-picker-close">Cancel</button>
    </div>
  </div>
</dialog>
