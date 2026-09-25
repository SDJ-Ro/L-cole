// MVC/public/assets/js/components/teacher-extracurricular.js
// Dedicated Component Controller for Teacher Enrollment Processing & Join Requests

document.addEventListener('DOMContentLoaded', () => {
  const panel = document.getElementById('j-enrollment-processing-panel');
  if (!panel) return;

  const rejectModal = document.getElementById('j-modal-reject');
  let currentRejectRequestCardEl = null;
  let currentRequestId = null;

  // Helper to update metric counters safely
  function updateCounters(deltaPending, deltaApproved, deltaRoster) {
    const pendingEl = document.getElementById('j-metric-pending-requests');
    const approvedEl = document.getElementById('j-metric-approved-requests');
    const rosterEl = document.getElementById('j-metric-active-roster');

    if (pendingEl && deltaPending !== 0) {
      const cur = Math.max(0, (parseInt(pendingEl.textContent, 10) || 0) + deltaPending);
      pendingEl.textContent = cur;
    }
    if (approvedEl && deltaApproved !== 0) {
      const cur = Math.max(0, (parseInt(approvedEl.textContent, 10) || 0) + deltaApproved);
      approvedEl.textContent = cur;
    }
    if (rosterEl && deltaRoster !== 0) {
      const cur = Math.max(0, (parseInt(rosterEl.textContent, 10) || 0) + deltaRoster);
      rosterEl.textContent = cur;
    }
  }

  function checkEmptyState() {
    const grid = document.getElementById('j-teacher-join-carousel') || document.getElementById('j-teacher-join-grid');
    const emptyBox = document.getElementById('j-teacher-join-empty');
    if (!grid || !emptyBox) return;

    const remainingCards = grid.querySelectorAll('.c-teacher-join-card');
    if (remainingCards.length === 0) {
      emptyBox.style.display = 'block';
    }
  }

  // ---------------------------------------------------------------------------
  // 1. APPROVE JOIN REQUEST
  // ---------------------------------------------------------------------------
  panel.addEventListener('click', (e) => {
    const approveBtn = e.target.closest('.j-approve-request');
    if (!approveBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = approveBtn.closest('.c-teacher-join-card');
    if (!card) return;

    const studentName   = approveBtn.dataset.studentName   || 'Student';
    const studentGrade  = approveBtn.dataset.studentGrade  || '';
    const studentAvatar = approveBtn.dataset.studentAvatar || '';
    const ageGroup      = approveBtn.dataset.ageGroup      || '';

    // Mark card as accepted with celebratory UI
    card.classList.add('c-is-accepted');
    const actionsWrap = card.querySelector('.c-teacher-join-card__actions');
    if (actionsWrap) {
      actionsWrap.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:center;gap:0.35rem;width:100%;color:var(--midnight,#0F414A);font-weight:700;font-size:0.8125rem;padding:0.45rem;background:rgba(127,199,204,0.25);border-radius:var(--radius-lg,0.625rem);">
          <svg class="c-icon" width="15" height="15"><use href="#icon-check"/></svg>
          <span>Enrolled &amp; Moved to Squad Pool</span>
        </div>
      `;
    }

    updateCounters(-1, +1, +1);

    // Add student directly to in-memory unassignedStudents pool so they show in Teams panel
    if (window.currentExtracurricularClub) {
      const club = window.currentExtracurricularClub;
      if (!club.unassignedStudents) club.unassignedStudents = [];
      club.unassignedStudents.push({
        id: 'u_' + Date.now(),
        name: studentName,
        grade: studentGrade,
        avatar: studentAvatar,
        ageGroup: ageGroup
      });
      if (typeof window.renderTeamsForClub === 'function') {
        window.renderTeamsForClub(club);
      }
    }

    // Apply centered decision overlay before removing card
    if (typeof applyCardDecisionOverlay === 'function') {
      applyCardDecisionOverlay(card, {
        status: 'approved',
        label: 'Enrolled in Squad',
        delay: 1100,
        onComplete: () => {
          card.style.transition = 'opacity 300ms ease, transform 300ms ease, max-height 300ms ease';
          card.style.opacity = '0';
          card.style.transform = 'scale(0.94)';
          setTimeout(() => {
            card.remove();
            checkEmptyState();
          }, 300);
        }
      });
    } else {
      setTimeout(() => {
        card.style.transition = 'opacity 300ms ease, transform 300ms ease, max-height 300ms ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.94)';
        setTimeout(() => {
          card.remove();
          checkEmptyState();
        }, 300);
      }, 800);
    }
  });

  // ---------------------------------------------------------------------------
  // 2. REJECT JOIN REQUEST (Wired to universal _reject_modal.php)
  // ---------------------------------------------------------------------------
  panel.addEventListener('click', (e) => {
    const rejectBtn = e.target.closest('.j-reject-request');
    if (!rejectBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = rejectBtn.closest('.c-teacher-join-card');
    const modal = rejectModal || document.getElementById('j-modal-reject');
    if (!card || !modal) return;

    currentRejectRequestCardEl = card;
    currentRequestId = rejectBtn.dataset.requestId;

    const studentName  = rejectBtn.dataset.studentName || 'Student';
    const studentGrade = rejectBtn.dataset.studentGrade || '';
    const displayLabel = studentGrade ? `"${studentName} (${studentGrade})"` : `"${studentName}"`;

    const typeLabel = modal.querySelector('.j-reject-modal-type');
    if (typeLabel) typeLabel.textContent = 'Join Request';

    const itemName = modal.querySelector('.j-reject-modal-item-name');
    if (itemName) itemName.textContent = displayLabel;

    const feedbackInput = modal.querySelector('#j-reject-feedback-input');
    const confirmBtn = modal.querySelector('#j-reject-confirm-btn');

    if (feedbackInput) {
      feedbackInput.value = '';
      setTimeout(() => feedbackInput.focus(), 100);
    }
    if (confirmBtn) confirmBtn.disabled = true;

    if (feedbackInput && confirmBtn) {
      feedbackInput.oninput = () => {
        confirmBtn.disabled = !feedbackInput.value.trim();
      };
    }

    if (typeof window.openModal === 'function') {
      window.openModal(modal);
    } else if (typeof openModal === 'function') {
      openModal(modal);
    } else {
      document.body.style.overflow = 'hidden';
      modal.classList.add('c-is-open');
    }

    // Override or assign confirm action for join requests
    confirmBtn.onclick = () => {
      if (!currentRejectRequestCardEl) return;
      const targetCard = currentRejectRequestCardEl;
      currentRejectRequestCardEl = null;

      if (typeof window.closeModal === 'function') {
        window.closeModal(modal);
      } else if (typeof closeModal === 'function') {
        closeModal(modal);
      } else {
        document.body.style.overflow = '';
        modal.classList.remove('c-is-open');
      }

      updateCounters(-1, 0, 0);

      if (typeof applyCardDecisionOverlay === 'function') {
        applyCardDecisionOverlay(targetCard, {
          status: 'declined',
          label: 'Request Declined',
          delay: 1100,
          onComplete: () => {
            targetCard.style.transition = 'opacity 350ms ease, transform 350ms ease';
            targetCard.style.opacity = '0';
            targetCard.style.transform = 'scale(0.92)';
            setTimeout(() => {
              targetCard.remove();
              checkEmptyState();
            }, 350);
          }
        });
      } else {
        targetCard.style.transition = 'opacity 350ms ease, transform 350ms ease';
        targetCard.style.opacity = '0';
        targetCard.style.transform = 'scale(0.92)';
        setTimeout(() => {
          targetCard.remove();
          checkEmptyState();
        }, 350);
      }
    };
  });

  const rejectModalEl = rejectModal || document.getElementById('j-modal-reject');
  if (rejectModalEl) {
    rejectModalEl.addEventListener('modal:closed', () => {
      currentRejectRequestCardEl = null;
    });
  }
});
