<?php
/**
 * =========================================================================
 * L'ÉCOLE — PROFILE ACTIVITIES & ACHIEVEMENTS COMPONENT
 * =========================================================================
 * Modular subtabs component for:
 *   1. Extracurricular Activities (Clubs, Sports, Societies)
 *   2. Honours & Achievements (Awards, Competitions, Certifications)
 *
 * Reusable across:
 *   - Student Profile Modal (Extracurriculars & Achievements subtabs)
 *   - Student Portal Activities & Achievements pages
 *   - Parent Portal Child Profile Activities & Achievements tabs
 * =========================================================================
 */
?>

<!-- Student: Extracurriculars Sub-Tab Panel -->
<div class="c-subtab-panel j-subtab-panel" id="j-panel-extracurriculars" style="display: none;">
  <div class="c-section-title-row" style="justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-trophy"/></svg></span>
      <h3 class="c-section-title">Extracurricular Involvement</h3>
    </div>
    <button type="button" class="c-btn-accent c-tone-sunshine j-add-extra-btn j-edit-only" style="display: none; padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 700; border-radius: var(--radius-md, 0.5rem); border: none; cursor: pointer;">
      <svg class="c-icon" width="12" height="12" aria-hidden="true"><use href="#icon-plus"/></svg>
      <span>Add Activity</span>
    </button>
  </div>
  <div class="c-extra-grid" id="j-extra-list">
    <!-- Rendered dynamically by profile-modal.js / activities script -->
  </div>
</div>

<!-- Student: Achievements Sub-Tab Panel -->
<div class="c-subtab-panel j-subtab-panel" id="j-panel-achievements" style="display: none;">
  <div class="c-section-title-row" style="justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <span class="c-icon-accent"><svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-medal"/></svg></span>
      <h3 class="c-section-title">Honours &amp; Achievements</h3>
    </div>
    <button type="button" class="c-btn-accent c-tone-sunshine j-add-achievement-btn j-edit-only" style="display: none; padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 700; border-radius: var(--radius-md, 0.5rem); border: none; cursor: pointer;">
      <svg class="c-icon" width="12" height="12" aria-hidden="true"><use href="#icon-plus"/></svg>
      <span>Add Achievement</span>
    </button>
  </div>
  <div class="c-achievement-grid" id="j-achievement-list">
    <!-- Rendered dynamically by profile-modal.js / activities script -->
  </div>
</div>
