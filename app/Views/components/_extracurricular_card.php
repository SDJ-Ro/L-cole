<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR CARD COMPONENT
 * =========================================================================
 * Unified card component handling:
 * - Active / Regular cards with vibrant theme gradients and background art
 * - Pending cards with greyed-out filter, dashed border & Accept/Reject controls
 * - Teacher in charge (TIC) avatar, badge, and locked/unlocked contact row
 * - Seamless client-side transition when accepted (grey filter smoothly lifts)
 * 
 * Expects:
 *   - $club (array) Activity details
 *   - $canModerate (bool, optional) Whether user can accept/reject pending items (default: true for Admin/Management)
 * =========================================================================
 */

$clubId       = (int)($club['id'] ?? 0);
$clubName     = $club['name'] ?? 'Extracurricular';
$clubType     = $club['type'] ?? 'Sports';
$category     = $club['category'] ?? 'General';
$theme        = $club['theme'] ?? ($clubType === 'Sports' ? 'main-sport' : 'club');
$bgIcon       = $club['bgIcon'] ?? ($clubType === 'Sports' ? 'trophy' : 'usersRound');
$status       = $club['status'] ?? 'Active';
$isPending    = ($status === 'Pending');
$isEnrolled   = ($status === 'Enrolled') || !empty($club['enrolled']);
$isRequested  = !empty($club['requested']);
$image        = $club['image'] ?? null;
$tic          = $club['tic'] ?? [];
$coach        = $club['coach'] ?? [];
$canModerate  = $canModerate ?? true;
$currentRole  = $currentRole ?? 'admin';
$isStudent    = ($currentRole === 'student');

// Pill configuration
if ($isStudent) {
    if ($isEnrolled) {
        $pillClass = 'c-club-card__pill--enrolled';
        $pillText  = 'ENROLLED';
    } elseif ($clubType === 'Sports') {
        $pillClass = 'c-club-card__pill--sport';
        $pillText  = 'SPORT';
    } else {
        $pillClass = 'c-club-card__pill--club';
        $pillText  = 'CLUB';
    }
} else {
    if ($isPending) {
        $pillClass = 'c-club-card__pill--pending';
        $pillText  = 'PENDING APPROVAL';
    } elseif ($isEnrolled) {
        $pillClass = 'c-club-card__pill--enrolled';
        $pillText  = 'ENROLLED';
    } elseif ($clubType === 'Sports') {
        $pillClass = 'c-club-card__pill--sport';
        $pillText  = 'SPORT';
    } else {
        $pillClass = 'c-club-card__pill--club';
        $pillText  = 'CLUB';
    }
}

$contactPhone = !empty($coach['phone']) ? $coach['phone'] : (!empty($tic['phone']) ? $tic['phone'] : '+94 77 123 4567');
?>

<article class="c-club-card <?= ($isPending && !$isStudent) ? 'c-club-card--pending' : 'c-club-card--clickable' ?> j-club-card" 
         id="j-club-card-<?= $clubId ?>"
         data-club-id="<?= $clubId ?>" 
         data-club-name="<?= htmlspecialchars($clubName) ?>"
         data-club-type="<?= htmlspecialchars($clubType) ?>"
         data-status="<?= htmlspecialchars($status) ?>">

  <!-- Top Hero & Identity Banner -->
  <div class="c-club-card__top c-club-card__top--<?= htmlspecialchars($theme) ?>"
       <?php if (!empty($image)): ?>style="background-image: url('<?= htmlspecialchars($image) ?>');"<?php endif; ?>>
    
    <?php if (empty($image)): ?>
      <div class="c-club-card__bg-icon" aria-hidden="true">
        <svg width="140" height="140" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <use href="#icon-<?= htmlspecialchars($bgIcon) ?>"/>
        </svg>
      </div>
    <?php endif; ?>

    <div class="c-club-card__header-badges">
      <span class="c-club-card__pill <?= $pillClass ?> j-club-pill" data-type="<?= htmlspecialchars($clubType) ?>">
        <?php if ($isEnrolled): ?>
          <svg class="c-icon" width="10" height="10" viewBox="0 0 24 24"><use href="#icon-check"/></svg>
        <?php endif; ?>
        <?= htmlspecialchars($pillText) ?>
      </span>
    </div>

    <div class="c-club-card__titles">
      <p class="c-club-card__category"><?= htmlspecialchars(strtoupper($category)) ?></p>
      <h2 class="c-club-card__name c-font-display"><?= htmlspecialchars($clubName) ?></h2>
    </div>
  </div>

  <!-- Bottom Metadata & Controls -->
  <div class="c-club-card__bottom">
    
    <!-- Teacher in Charge (TIC) Row -->
    <div class="c-club-card__tic">
      <?php if (!empty($tic['avatar'])): ?>
        <img class="c-club-card__tic-avatar" src="<?= htmlspecialchars($tic['avatar']) ?>" alt="<?= htmlspecialchars($tic['name'] ?? 'Teacher') ?>" />
      <?php else: ?>
        <div class="c-club-card__tic-avatar-placeholder" aria-hidden="true">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24"><use href="#icon-usersRound"/></svg>
        </div>
      <?php endif; ?>
      <div style="flex: 1; min-width: 0;">
        <div class="c-club-card__tic-label">TEACHER IN CHARGE</div>
        <div class="c-club-card__tic-name j-card-tic-name"><?= htmlspecialchars($tic['name'] ?? 'Faculty Mentor') ?></div>
      </div>
      <?php if (!empty($canModerate) || !empty($canCreate)): ?>
        <button type="button" class="c-btn c-btn--ghost c-btn--sm j-edit-card-tic-btn" data-club-id="<?= $clubId ?>" data-club-name="<?= htmlspecialchars($clubName) ?>" data-current-tic="<?= htmlspecialchars($tic['name'] ?? '') ?>" title="Change Teacher in Charge" style="padding: 0.2rem 0.5rem; font-size: 0.72rem; margin-left: auto; height: auto; border-radius: var(--radius-md);">
          <svg class="c-icon" width="11" height="11" viewBox="0 0 24 24"><use href="#icon-edit"/></svg>
          <span>Change</span>
        </button>
      <?php endif; ?>
    </div>

    <!-- Contact Row -->
    <?php if ($isStudent): ?>
      <?php if ($isEnrolled): ?>
        <div class="c-club-card__contact c-club-card__contact--unlocked">
          <span class="c-club-card__contact-icon" aria-hidden="true">
            <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
          </span>
          <span class="c-club-card__contact-text"><?= htmlspecialchars($contactPhone) ?></span>
        </div>
      <?php else: ?>
        <div class="c-club-card__contact c-club-card__contact--locked j-contact-locked">
          <span class="c-club-card__contact-icon" aria-hidden="true">
            <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24"><use href="#icon-lock"/></svg>
          </span>
          <span class="c-club-card__contact-text">Coach contact hidden until enrollment</span>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <?php if (!$isPending): ?>
        <div class="c-club-card__contact c-club-card__contact--unlocked">
          <span class="c-club-card__contact-icon" aria-hidden="true">
            <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-phone"/></svg>
          </span>
          <span class="c-club-card__contact-text"><?= htmlspecialchars($contactPhone) ?></span>
        </div>
      <?php else: ?>
        <div class="c-club-card__contact c-club-card__contact--locked j-contact-locked">
          <span class="c-club-card__contact-icon" aria-hidden="true">
            <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24"><use href="#icon-lock"/></svg>
          </span>
          <span class="c-club-card__contact-text">Contact hidden until program approval</span>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Action Slot -->
    <div class="c-club-card__actions j-card-actions">
      <?php if ($isStudent): ?>
        <?php if ($isEnrolled): ?>
          <button type="button" class="c-club-card__btn <?= $clubType === 'Sports' ? 'c-club-card__btn--sport' : 'c-club-card__btn--club' ?> j-view-details" data-club-id="<?= $clubId ?>">
            View Details &rarr;
          </button>
        <?php elseif ($isRequested): ?>
          <button type="button" class="c-club-card__btn c-club-card__btn--requested j-requested-club" data-club-id="<?= $clubId ?>" disabled>
            <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-check"/></svg>
            Requested
          </button>
        <?php else: ?>
          <button type="button" class="c-club-card__btn c-club-card__btn--interest j-interest-club" data-club-id="<?= $clubId ?>" data-type="<?= htmlspecialchars($clubType) ?>">
            <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-heart"/></svg>
            Interest
          </button>
        <?php endif; ?>
      <?php elseif ($isPending && $canModerate): ?>
        <button type="button" class="c-btn c-btn--maroon c-btn--card-action j-reject-club" data-club-id="<?= $clubId ?>" data-club-name="<?= htmlspecialchars($clubName) ?>">
          <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-x"/></svg>
          Reject
        </button>
        <button type="button" class="c-btn c-btn--moss c-btn--card-action j-approve-club" data-club-id="<?= $clubId ?>" data-club-name="<?= htmlspecialchars($clubName) ?>">
          <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24"><use href="#icon-check"/></svg>
          Accept
        </button>
      <?php else: ?>
        <button type="button" class="c-club-card__btn <?= $clubType === 'Sports' ? 'c-club-card__btn--sport' : 'c-club-card__btn--club' ?> j-view-details" data-club-id="<?= $clubId ?>">
          View Details &rarr;
        </button>
      <?php endif; ?>
    </div>

  </div>
</article>
