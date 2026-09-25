<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER HANDOVER & INACTIVATION MODAL COMPONENT
 * =========================================================================
 * Reusable modal for gracefully deactivating a faculty member while safely
 * reassigning or releasing their active responsibilities:
 *   - Homeroom Class Teacher role
 *   - Subject Teaching assignments
 *   - Extracurricular / Club advisor roles
 *
 * Prevents orphaned classes, preserves database foreign keys, and adheres
 * to the zero-hard-delete school governance policy.
 * =========================================================================
 */
?>
<div class="c-modal-layer" id="j-modal-teacher-handover" role="presentation" style="display: none;">
  <button type="button" class="c-modal-backdrop j-modal-close" aria-label="Close handover dialog"></button>
  
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-teacher-handover-title" style="max-width: 34rem; width: 100%;">
    <header class="c-modal__header c-modal__header--grade" style="background: rgba(175,80,49,0.06); border-bottom: 1px solid rgba(175,80,49,0.15);">
      <div class="c-modal__heading-group">
        <div class="c-modal__icon-badge" style="background: rgba(175,80,49,0.12); color: var(--terracotta, #AF5031);" aria-hidden="true">
          <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-usersRound"/>
          </svg>
        </div>
        <div>
          <p class="c-modal__eyebrow" style="color: var(--terracotta, #AF5031);">Staff Lifecycle & Offboarding</p>
          <h2 class="c-modal__title" id="j-teacher-handover-title" style="color: var(--midnight, #0F414A);">Inactivate Faculty Member</h2>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close">
        <svg class="c-icon" width="18" height="18"><use href="#icon-x"/></svg>
      </button>
    </header>

    <form class="c-grade-form" id="j-teacher-handover-form" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
      <!-- Hidden Fields -->
      <input type="hidden" id="j-handover-teacher-id" value="" />
      <input type="hidden" id="j-handover-teacher-name" value="" />

      <!-- Teacher Info Banner -->
      <div style="display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem 1rem; border-radius: var(--radius-lg, 0.75rem); background: rgba(15,65,74,0.04); border: 1px solid rgba(15,65,74,0.08);">
        <div class="c-avatar c-avatar-md" id="j-handover-avatar" style="background: var(--sand, #E4CBA9); color: var(--midnight, #0F414A); font-weight: 700;">
          JW
        </div>
        <div>
          <h4 id="j-handover-display-name" style="margin: 0; font-size: 0.9375rem; font-weight: 700; color: var(--midnight, #0F414A);">James Wilson</h4>
          <p id="j-handover-display-meta" style="margin: 0.125rem 0 0; font-size: 0.75rem; color: rgba(15,65,74,0.6);">Science & Chemistry • Faculty</p>
        </div>
      </div>

      <!-- Advisory Guidance Box -->
      <div style="display: flex; gap: 0.625rem; padding: 0.75rem 0.875rem; border-radius: var(--radius-md, 0.5rem); background: rgba(234,137,19,0.08); border-left: 3px solid var(--sunshine, #EA8913);">
        <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--sunshine, #EA8913); flex-shrink: 0; margin-top: 0.125rem;"><use href="#icon-alertTriangle"/></svg>
        <p style="margin: 0; font-size: 0.75rem; line-height: 1.4; color: var(--midnight, #0F414A);">
          This teacher currently holds active academic responsibilities. Reassign their roles to replacement colleagues below, or choose <strong>Leave Unassigned</strong> to flag them on the Academic Dashboard.
        </p>
      </div>

      <!-- Dynamic Responsibility Cards Container -->
      <div id="j-handover-roles-list" style="display: flex; flex-direction: column; gap: 0.875rem;">
        
        <!-- 1. Homeroom Class Card -->
        <div class="c-handover-role-card" id="j-role-homeroom" style="padding: 0.875rem 1rem; border-radius: var(--radius-lg, 0.625rem); border: 1px solid rgba(15,65,74,0.1); background: #ffffff;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--skyblue, #207C82);"></span>
              <span style="font-size: 0.8125rem; font-weight: 700; color: var(--midnight, #0F414A);">Class Teacher (Homeroom)</span>
            </div>
            <span class="c-tag" id="j-handover-class-label" style="background: rgba(127,199,204,0.25); color: var(--midnight, #0F414A); font-size: 11px; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">6-A</span>
          </div>
          <label style="display: block; font-size: 0.75rem; font-weight: 500; color: rgba(15,65,74,0.7); margin-bottom: 0.375rem;">Replacement Class Teacher</label>
          <select class="c-grade-form__input j-handover-replacement-select" id="j-replacement-homeroom" style="height: 2.25rem; font-size: 0.8125rem;">
            <option value="">— Leave Unassigned (Mark pending) —</option>
          </select>
        </div>

        <!-- 2. Subject Teaching Workload Card -->
        <div class="c-handover-role-card" id="j-role-subjects" style="padding: 0.875rem 1rem; border-radius: var(--radius-lg, 0.625rem); border: 1px solid rgba(15,65,74,0.1); background: #ffffff;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--terracotta, #AF5031);"></span>
              <span style="font-size: 0.8125rem; font-weight: 700; color: var(--midnight, #0F414A);">Subject Teaching</span>
            </div>
            <span class="c-tag" id="j-handover-subject-count-badge" style="background: rgba(175,80,49,0.15); color: var(--terracotta, #AF5031); font-size: 11px; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">Science (3 Classes)</span>
          </div>
          <p id="j-handover-subject-detail" style="margin: 0 0 0.5rem; font-size: 0.75rem; color: rgba(15,65,74,0.65);">Classes: 6-A, 7-B, 8-C</p>
          <label style="display: block; font-size: 0.75rem; font-weight: 500; color: rgba(15,65,74,0.7); margin-bottom: 0.375rem;">Transfer Subjects To</label>
          <select class="c-grade-form__input j-handover-replacement-select" id="j-replacement-subjects" style="height: 2.25rem; font-size: 0.8125rem;">
            <option value="">— Leave Unassigned (Mark pending) —</option>
          </select>
        </div>

        <!-- 3. Extracurricular Activity Card -->
        <div class="c-handover-role-card" id="j-role-extras" style="padding: 0.875rem 1rem; border-radius: var(--radius-lg, 0.625rem); border: 1px solid rgba(15,65,74,0.1); background: #ffffff;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--moss, #4B5B34);"></span>
              <span style="font-size: 0.8125rem; font-weight: 700; color: var(--midnight, #0F414A);">Extracurricular Activity</span>
            </div>
            <span class="c-tag" id="j-handover-extra-label" style="background: rgba(164,171,152,0.3); color: var(--moss, #4B5B34); font-size: 11px; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">Science Society</span>
          </div>
          <label style="display: block; font-size: 0.75rem; font-weight: 500; color: rgba(15,65,74,0.7); margin-bottom: 0.375rem;">Replacement Club Advisor</label>
          <select class="c-grade-form__input j-handover-replacement-select" id="j-replacement-extra" style="height: 2.25rem; font-size: 0.8125rem;">
            <option value="">— Leave Vacant (Mark pending) —</option>
          </select>
        </div>

      </div>

      <!-- Action Buttons Footer -->
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.75rem; flex-wrap: wrap;">
        <button type="button" class="c-btn-plain j-modal-close" style="font-size: 0.8125rem;">Cancel</button>
        <div style="display: flex; gap: 0.625rem; align-items: center;">
          <button type="button" class="c-btn-secondary" id="j-btn-inactivate-unassigned" style="padding: 0.5rem 0.875rem; font-size: 0.8125rem; font-weight: 600; border-radius: var(--radius-md, 0.5rem); border: 1px solid rgba(15,65,74,0.2); background: transparent; color: var(--midnight, #0F414A); cursor: pointer;">
            Clear Roles & Inactivate
          </button>
          <button type="submit" class="c-btn-create-grade" id="j-btn-inactivate-reassign" style="background: var(--terracotta, #AF5031); color: #ffffff; padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 700; border-radius: var(--radius-md, 0.5rem); border: none; cursor: pointer;">
            Reassign & Inactivate
          </button>
        </div>
      </div>
    </form>
  </section>
</div>
