<?php
/**
 * =========================================================================
 * L'ÉCOLE — SHARED COMPLAINTS & INQUIRIES CARD COMPONENT
 * =========================================================================
 * Unified card representing an inquiry/complaint for Parent & Management.
 * 
 * Variables:
 * - $complaint (array)
 * - $canResolve (bool, default: false)
 * - $showParentInfo (bool, default: false)
 * =========================================================================
 */

$canResolve     = $canResolve ?? false;
$showParentInfo = $showParentInfo ?? false;

$id             = $complaint['id'] ?? 0;
$subject        = $complaint['subject'] ?? $complaint['title'] ?? 'Inquiry';
$category       = $complaint['category'] ?? 'General';
$message        = $complaint['message'] ?? $complaint['description'] ?? '';
$status         = $complaint['status'] ?? 'In Progress';
$isResolved     = ($status === 'Resolved');
$date           = !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : ($complaint['date'] ?? '');
$studentName    = $complaint['student_name'] ?? $complaint['student'] ?? '';
$studentGrade   = $complaint['student_grade'] ?? $complaint['childClass'] ?? '';
$parentName     = $complaint['parent_name'] ?? $complaint['parentName'] ?? '';

$resolutionNote = $complaint['resolution_note'] ?? $complaint['resolutionNote'] ?? '';
if (empty($resolutionNote) && !empty($complaint['responses']) && is_array($complaint['responses'])) {
    $latestResponse = end($complaint['responses']);
    $resolutionNote = $latestResponse['message'] ?? '';
}

$catKey      = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $category));
$statusClass = $isResolved ? 'c-complaint-card--resolved' : 'c-complaint-card--inprogress';
$searchData  = strtolower(trim("{$subject} {$message} {$category} {$status} {$studentName} {$parentName} {$date}"));
?>

<article class="c-complaint-card <?= $statusClass ?> c-complaint-card--cat-<?= htmlspecialchars($catKey) ?> j-complaint-card" 
         data-id="<?= (int)$id ?>"
         data-status="<?= htmlspecialchars($status) ?>"
         data-category="<?= htmlspecialchars($category) ?>"
         data-search="<?= htmlspecialchars($searchData) ?>">
  
  <div class="c-complaint-card__body">
    <div class="c-complaint-card__main">
      
      <!-- Meta Tags Row -->
      <div class="c-complaint-card__meta">
        <span class="c-status-badge <?= $isResolved ? 'c-status-badge--resolved' : 'c-status-badge--inprogress' ?>">
          <svg class="c-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <?php if ($isResolved): ?>
              <use href="#icon-checkCircle2"/>
            <?php else: ?>
              <use href="#icon-clock"/>
            <?php endif; ?>
          </svg>
          <span class="j-status-text"><?= htmlspecialchars($status) ?></span>
        </span>

        <span class="c-category-badge"><?= htmlspecialchars($category) ?></span>
        <span class="c-dot-sep">•</span>
        <span class="c-complaint-card__date"><?= htmlspecialchars($date) ?></span>
      </div>

      <!-- Subject / Title -->
      <h3 class="c-complaint-card__subject"><?= htmlspecialchars($subject) ?></h3>

      <!-- Message Content -->
      <p class="c-complaint-card__message"><?= nl2br(htmlspecialchars($message)) ?></p>

      <!-- Student & Parent Context -->
      <div class="c-complaint-card__context">
        <?php if (!empty($studentName)): ?>
          <span class="c-complaint-card__student">
            <strong>Student:</strong> <?= htmlspecialchars($studentName) ?><?= !empty($studentGrade) ? ' (' . htmlspecialchars($studentGrade) . ')' : '' ?>
          </span>
        <?php endif; ?>

        <?php if ($showParentInfo && !empty($parentName)): ?>
          <span class="c-dot-sep">•</span>
          <span class="c-complaint-card__parent">
            <strong>Parent:</strong> <?= htmlspecialchars($parentName) ?>
          </span>
        <?php endif; ?>
      </div>

    </div>

    <!-- Management Action: Mark as Resolved -->
    <?php if ($canResolve && !$isResolved): ?>
      <button type="button" class="c-complaint-card__resolve-btn j-start-resolve-btn" data-id="<?= (int)$id ?>">
        Mark as Resolved
      </button>
    <?php endif; ?>
  </div>

  <!-- Resolution Footer (When already resolved or updated) -->
  <div class="c-complaint-card__resolution-footer j-resolution-footer" style="<?= $isResolved && !empty($resolutionNote) ? '' : 'display: none;' ?>">
    <p class="c-complaint-card__resolution-label"><?= $canResolve ? 'Message to Parent:' : 'School Response:' ?></p>
    <p class="c-complaint-card__resolution-text j-resolution-note"><?= nl2br(htmlspecialchars($resolutionNote)) ?></p>
  </div>

  <!-- Inline Resolution Bar (Management only, toggled dynamically) -->
  <?php if ($canResolve): ?>
    <div class="c-complaint-card__resolve-bar j-resolve-bar" style="display: none;" data-id="<?= (int)$id ?>">
      <input type="text" 
             class="c-complaint-card__resolve-input j-resolve-input" 
             placeholder="Write a message to the parent explaining the resolution..." 
             autocomplete="off" />
      <button type="button" class="c-complaint-card__icon-btn j-cancel-resolve-btn" data-id="<?= (int)$id ?>" aria-label="Cancel resolution" title="Cancel">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-x"/>
        </svg>
      </button>
      <button type="button" class="c-complaint-card__send-btn j-send-resolve-btn" data-id="<?= (int)$id ?>" disabled>
        <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-send"/>
        </svg>
        <span>Resolve</span>
      </button>
    </div>
  <?php endif; ?>

</article>
