<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Approvals — L'École Parent Portal</title>
  <meta name="description" content="Review and respond to consent requests from the school — approve or decline field trips, activity enrolments, and programme invitations for your child." />
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/approval-card.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/admin-verify.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/parent-approvals.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/reject-modal.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="c-main" id="j-main">
    <div class="c-main-inner">
      <div class="c-main-container">
        <div class="c-page-stack c-parent-approvals">

          <!-- Page Header -->
          <?php
          $pageTitle    = 'Approvals';
          $pageSubtitle = 'Review consent requests from the school — approve or decline field trips, club enrolments, and programme invitations for Nethmi Perera.';
          require __DIR__ . '/../components/_page_header.php';
          ?>

          <!-- ================================================================
               3 STATUS METRIC CARDS — Pending / Approved / Rejected
               Exact c-tab-card pattern from admin Approvals & Verifications.
               Icon sources: icon-clock (pending), icon-checkCircle2 (approved),
               icon-x (declined). All present in _icon_logos.php sprite.
               ================================================================ -->
          <div class="c-tab-grid j-tab-grid" role="group" aria-label="Filter by approval status">

            <!-- PENDING — Sunshine Orange -->
            <button type="button"
                    class="c-tab-card c-tab-card--sunshine c-is-active j-parent-tab-card"
                    data-status-name="Pending"
                    aria-pressed="true">
              <span class="c-tab-card__icon-wrap" aria-hidden="true">
                <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-clock"/>
                </svg>
              </span>
              <p class="c-tab-card__value j-pa-count"><?= (int)($statusCounts['Pending'] ?? 0) ?></p>
              <p class="c-tab-card__label">Awaiting Your Decision</p>
            </button>

            <!-- APPROVED — Moss Green -->
            <button type="button"
                    class="c-tab-card c-tab-card--moss j-parent-tab-card"
                    data-status-name="Approved"
                    aria-pressed="false">
              <span class="c-tab-card__icon-wrap" aria-hidden="true">
                <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-checkCircle2"/>
                </svg>
              </span>
              <p class="c-tab-card__value j-pa-count"><?= (int)($statusCounts['Approved'] ?? 0) ?></p>
              <p class="c-tab-card__label">Approved by You</p>
            </button>

            <!-- REJECTED / DECLINED — Maroon -->
            <button type="button"
                    class="c-tab-card c-tab-card--maroon j-parent-tab-card"
                    data-status-name="Rejected"
                    aria-pressed="false">
              <span class="c-tab-card__icon-wrap" aria-hidden="true">
                <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-x"/>
                </svg>
              </span>
              <p class="c-tab-card__value j-pa-count"><?= (int)($statusCounts['Rejected'] ?? 0) ?></p>
              <p class="c-tab-card__label">Declined by You</p>
            </button>

          </div>

          <!-- Filter Summary Label -->
          <div class="c-filter-row">
            <p class="c-parent-approvals__summary" id="j-parent-summary-label">
              <?= (int)($statusCounts['Pending'] ?? 0) ?> pending request<?= ($statusCounts['Pending'] ?? 0) !== 1 ? 's' : '' ?>
            </p>
          </div>

          <!-- ================================================================
               APPROVALS GRID
               Pending cards → full Approve + Decline action buttons (like admin).
               Approved / Declined cards → read-only with status badge.
               ================================================================ -->
          <div class="c-approval-grid" id="j-parent-approval-grid">
            <?php foreach ($items as $item):
              $id          = $item['id'] ?? '';
              $type        = $item['type'] ?? 'Extracurriculars';
              $title       = $item['title'] ?? '';
              $tag         = $item['tag'] ?? 'Request';
              $categoryTag = $item['categoryTag'] ?? '';
              $color       = $item['color'] ?? 'terracotta';
              $meta        = $item['meta'] ?? '';
              $description = $item['description'] ?? '';
              $author      = $item['author'] ?? '';
              $date        = $item['date'] ?? '';
              $status      = $item['status'] ?? 'Pending';
              $feedback    = $item['feedback'] ?? '';
            ?>
            <article class="c-approval-card c-approval-card--<?= htmlspecialchars($color) ?> j-parent-approval-card"
                     data-item-type="<?= htmlspecialchars($type) ?>"
                     data-item-id="<?= htmlspecialchars((string)$id) ?>"
                     data-item-status="<?= htmlspecialchars($status) ?>"
                     data-item-title="<?= htmlspecialchars($title) ?>"
                     <?= $status !== 'Pending' ? 'hidden' : '' ?>>

              <!-- Heading & Status Badge -->
              <div class="c-approval-card__top">
                <div class="c-approval-card__heading">
                  <?php if (!empty($categoryTag)): ?>
                    <div class="c-approval-card__badges">
                      <span class="c-approval-card__tag c-approval-card__tag--<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($tag) ?></span>
                      <span class="c-approval-card__tag c-approval-card__tag--neutral"><?= htmlspecialchars($categoryTag) ?></span>
                    </div>
                  <?php else: ?>
                    <span class="c-approval-card__tag c-approval-card__tag--<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($tag) ?></span>
                  <?php endif; ?>
                  <h3 class="c-approval-card__title <?= !empty($categoryTag) ? 'c-approval-card__title--tight' : '' ?> c-font-display">
                    <?= htmlspecialchars($title) ?>
                  </h3>
                  <?php if (!empty($meta)): ?>
                    <p class="c-approval-card__meta"><?= htmlspecialchars($meta) ?></p>
                  <?php endif; ?>
                </div>
                <span class="c-status-badge c-status-badge--<?= strtolower(htmlspecialchars($status)) ?> j-status-badge">
                  <?= htmlspecialchars($status) ?>
                </span>
              </div>

              <!-- Description Snippet -->
              <?php if (!empty($description)): ?>
                <p class="c-approval-card__description c-approval-card__description--clamp-3">
                  <?= htmlspecialchars($description) ?>
                </p>
              <?php endif; ?>

              <!-- Sent By & Date -->
              <div class="c-approval-card__info">
                <div class="c-approval-card__info-row">
                  <p class="c-approval-card__info-text">
                    Request from: <span class="c-approval-card__info-strong"><?= htmlspecialchars($author) ?></span>
                  </p>
                  <p class="c-approval-card__info-date"><?= htmlspecialchars($date) ?></p>
                </div>
              </div>

              <!-- Decline Reason Note (shown only after parent declines) -->
              <div class="c-feedback-note j-pa-decline-note" <?= ($status !== 'Rejected' || empty($feedback)) ? 'hidden' : '' ?>>
                <svg class="c-icon c-feedback-note__icon" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="#icon-message"/>
                </svg>
                <div>
                  <p class="c-feedback-note__author">Your reason for declining</p>
                  <p class="c-feedback-note__text j-pa-decline-text"><?= htmlspecialchars($feedback) ?></p>
                </div>
              </div>

              <!-- ============================================================
                   ACTION BUTTONS: Exactly as in admin — Decline (left) / Approve (right)
                   Only shown on Pending cards; hidden once a decision is made.
                   ============================================================ -->
              <div class="c-approval-card__actions j-parent-approval-actions" <?= $status !== 'Pending' ? 'hidden' : '' ?>>
                <button type="button" class="c-btn c-btn--reject j-pa-decline-btn">
                  <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#icon-x"/>
                  </svg>
                  Decline
                </button>
                <button type="button" class="c-btn c-btn--approve j-pa-approve-btn">
                  <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#icon-check"/>
                  </svg>
                  Approve
                </button>
              </div>

            </article>
            <?php endforeach; ?>
          </div>

          <!-- Empty State -->
          <div class="c-empty-state" id="j-parent-empty-state" hidden>
            <p class="c-empty-state__text">No requests to show for this status.</p>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

<!-- =========================================================================
     DECLINE CONFIRMATION MODAL
     Uses the same .c-modal-layer / .c-modal-backdrop / .c-modal structure
     as the admin _reject_modal.php — works with reject-modal.css for free.
     ========================================================================= -->
<div class="c-modal-layer" id="j-pa-decline-modal" role="presentation">
  <button type="button" class="c-modal-backdrop j-pa-modal-close" aria-label="Cancel"></button>
  <section class="c-modal c-modal--reject" role="dialog" aria-modal="true" aria-labelledby="j-pa-modal-title">

    <div class="c-reject-modal__intro">
      <div class="c-modal__icon-badge c-modal__icon-badge--maroon" aria-hidden="true">
        <svg class="c-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-x"/>
        </svg>
      </div>
      <h3 class="c-reject-modal__title c-font-display" id="j-pa-modal-title">Decline Request</h3>
      <p class="c-reject-modal__description">
        You are declining: <span class="c-reject-modal__item-name j-pa-modal-item-name"></span>.
        Optionally let the school know why.
      </p>
    </div>

    <div class="c-reject-modal__body">
      <label class="c-field-label" for="j-pa-decline-reason">
        Reason for declining
        <span style="font-weight:400;text-transform:none;letter-spacing:normal;font-size:11px;color:rgba(15,65,74,0.5);"> (optional)</span>
      </label>
      <textarea class="c-field-input c-field-input--textarea"
                id="j-pa-decline-reason"
                rows="3"
                placeholder="Let the school know why you're declining..."></textarea>
    </div>

    <footer class="c-reject-modal__footer">
      <button type="button" class="c-btn c-btn--ghost j-pa-modal-close j-modal-close">Cancel</button>
      <button type="button" class="c-btn c-btn--solid-maroon" id="j-pa-decline-confirm-btn">
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-x"/>
        </svg>
        Confirm Decline
      </button>
    </footer>

  </section>
</div>


<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/parent-approvals.js?v=<?= time() ?>"></script>

</body>
</html>
