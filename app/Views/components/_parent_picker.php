<dialog id="parent-picker" class="c-parent-picker" aria-labelledby="parent-picker-title">
  <h2 id="parent-picker-title">Choose an existing parent</h2>
  <label for="parent-picker-search">Search by name, parent ID, or email</label>
  <input type="search" id="parent-picker-search" class="c-form-input" placeholder="Search parents" autocomplete="off" autofocus>
  <p id="parent-picker-count" role="status"></p>
  <div id="parent-picker-results" class="c-parent-picker-results"></div>
  <section id="parent-picker-details" hidden class="c-parent-selection" aria-label="Parent contact details">
    <strong id="parent-picker-detail-name"></strong>
    <span id="parent-picker-detail-id"></span>
    <span id="parent-picker-detail-email"></span>
    <span id="parent-picker-detail-phone"></span>
    <span id="parent-picker-detail-status"></span>
  </section>
  <div class="c-parent-picker-footer"><button type="button" id="parent-picker-cancel" class="c-btn-outline-sky">Cancel</button></div>
</dialog>
