<?php
/**
 * =========================================================================
 * L'ÉCOLE — CHARACTER CERTIFICATE A4 DOCUMENT COMPONENT
 * =========================================================================
 * Reusable A4 Character Certificate template.
 * Can be used in Management Panel (preview / edit / measurement) and in the
 * Student Portal.
 *
 * Parameters:
 *   $certificate       : array of student certificate data
 *   $isEditing         : bool (whether textareas/inputs are shown instead of bullet points)
 *   $showPageOverlay   : bool (whether page footer number overlay is shown)
 *   $currentPage       : int (for multi-page display)
 *   $totalPages        : int
 * =========================================================================
 */

$certificate = $certificate ?? [];
$isEditing   = !empty($isEditing);

$name        = $certificate['name'] ?? 'Student Name';
$id          = $certificate['id'] ?? '—';
$cohort      = $certificate['cohort'] ?? '—';
$reason      = $certificate['reason'] ?? 'Graduating student';
$requestedOn = $certificate['requestedOn'] ?? date('d M Y');
$academic    = $certificate['academic'] ?? '';
$activities  = $certificate['activities'] ?? '';
$conduct     = $certificate['conduct'] ?? '';

// Helpers
$nameParts   = preg_split('/\s+/', trim($name));
$initials    = '';
foreach ($nameParts as $p) {
    if (!empty($p)) $initials .= mb_substr($p, 0, 1);
}
$initials = mb_strtoupper(mb_substr($initials, 0, 2));

$classLabel = explode(' · ', $cohort)[0] ?? $cohort;
$studyPeriod = ($reason === 'Graduating student')
    ? preg_replace('/-[A-Z]$/', '', $classLabel) . ' completion'
    : $classLabel . ' record';

// Convert string to points
$toPoints = function($val) {
    if (empty($val)) return [];
    $lines = preg_split('/\r?\n| · /', $val);
    return array_values(array_filter(array_map('trim', $lines)));
};

$academicPoints   = $toPoints($academic);
$activitiesPoints = $toPoints($activities);
$conductPoints    = $toPoints($conduct);

// Pending requests (missing record requests that are pending)
$pendingReqs = $certificate['missingRecordRequests'] ?? [];
$pendingAcademic   = [];
$pendingActivities = [];
$pendingConduct    = [];

foreach ($pendingReqs as $r) {
    if (($r['status'] ?? '') === 'Pending') {
        $cat = $r['category'] ?? '';
        if ($cat === 'Academic') {
            $pendingAcademic[] = $r['title'] ?? '';
        } elseif (in_array($cat, ['Sports', 'Club', 'Other'])) {
            $pendingActivities[] = $r['title'] ?? '';
        } elseif ($cat === 'Attendance') {
            $pendingConduct[] = $r['title'] ?? '';
        }
    }
}
?>

<div class="cert-doc-inner" id="certContentRef">
  <!-- Document Header -->
  <header class="cert-doc-header">
    <p class="school-name">L’ÉCOLE</p>
    <p class="tagline">Institutional excellence since 1994</p>
    <div class="rule"></div>
    <h3>Character Certificate</h3>
    <p class="awarded">Awarded to <b><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></b></p>
  </header>

  <!-- Document Body -->
  <div class="cert-doc-body">
    <!-- Student Particulars -->
    <section class="cert-section-block" aria-labelledby="student-particulars-heading">
      <div class="cert-section-heading">
        <h4 id="student-particulars-heading">Student particulars</h4>
        <span class="detail"><?= $isEditing ? 'Editable' : 'Verified student record' ?></span>
      </div>
      <div class="particulars-grid">
        <div class="fact-row">
          <span class="fact-label">Full name</span>
          <?php if ($isEditing): ?>
            <input aria-label="Full name" data-particular="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" />
          <?php else: ?>
            <span class="fact-value"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="fact-row">
          <span class="fact-label">Name with initials</span>
          <span class="fact-value"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="fact-row">
          <span class="fact-label">Student index no.</span>
          <?php if ($isEditing): ?>
            <input aria-label="Student index no." data-particular="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" />
          <?php else: ?>
            <span class="fact-value"><?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="fact-row">
          <span class="fact-label">Current class</span>
          <?php if ($isEditing): ?>
            <input aria-label="Current class" data-particular="cohort" value="<?= htmlspecialchars($cohort, ENT_QUOTES, 'UTF-8') ?>" />
          <?php else: ?>
            <span class="fact-value"><?= htmlspecialchars($cohort, ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="fact-row">
          <span class="fact-label">Period of study</span>
          <span class="fact-value"><?= htmlspecialchars($studyPeriod, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </div>
    </section>

    <!-- Special Recognition (Academic) -->
    <section class="cert-section-block">
      <div class="cert-section-heading">
        <h4>
          <svg class="c-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-graduationCap"/>
          </svg>
          <span>Special recognition</span>
        </h4>
        <?php if ($isEditing): ?>
          <span class="detail">Editable (Press Enter for new point)</span>
        <?php endif; ?>
      </div>
      <?php if ($isEditing): ?>
        <textarea class="cert-textarea" aria-label="Special recognition" data-section="academic"><?= htmlspecialchars(implode("\n", $academicPoints), ENT_QUOTES, 'UTF-8') ?></textarea>
      <?php else: ?>
        <ul class="cert-points">
          <?php foreach ($academicPoints as $pt): ?>
            <li><span class="dot"></span><?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
          <?php foreach ($pendingAcademic as $annot): ?>
            <li class="pending"><span class="dot"></span><span><?= htmlspecialchars($annot, ENT_QUOTES, 'UTF-8') ?></span><span class="pending-badge">Pending Review</span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- Extracurricular Achievements -->
    <section class="cert-section-block">
      <div class="cert-section-heading">
        <h4>
          <svg class="c-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-fileCheck"/>
          </svg>
          <span>Extracurricular achievements</span>
        </h4>
        <?php if ($isEditing): ?>
          <span class="detail">Editable (Press Enter for new point)</span>
        <?php endif; ?>
      </div>
      <?php if ($isEditing): ?>
        <textarea class="cert-textarea" aria-label="Extracurricular achievements" data-section="activities"><?= htmlspecialchars(implode("\n", $activitiesPoints), ENT_QUOTES, 'UTF-8') ?></textarea>
      <?php else: ?>
        <ul class="cert-points">
          <?php foreach ($activitiesPoints as $pt): ?>
            <li><span class="dot"></span><?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
          <?php foreach ($pendingActivities as $annot): ?>
            <li class="pending"><span class="dot"></span><span><?= htmlspecialchars($annot, ENT_QUOTES, 'UTF-8') ?></span><span class="pending-badge">Pending Review</span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- Conduct & Character -->
    <section class="cert-section-block">
      <div class="cert-section-heading">
        <h4>
          <svg class="c-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-user"/>
          </svg>
          <span>Conduct & character</span>
        </h4>
        <?php if ($isEditing): ?>
          <span class="detail">Editable (Press Enter for new point)</span>
        <?php endif; ?>
      </div>
      <?php if ($isEditing): ?>
        <textarea class="cert-textarea" aria-label="Conduct & character" data-section="conduct"><?= htmlspecialchars(implode("\n", $conductPoints), ENT_QUOTES, 'UTF-8') ?></textarea>
      <?php else: ?>
        <ul class="cert-points">
          <?php foreach ($conductPoints as $pt): ?>
            <li><span class="dot"></span><?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
          <?php foreach ($pendingConduct as $annot): ?>
            <li class="pending"><span class="dot"></span><span><?= htmlspecialchars($annot, ENT_QUOTES, 'UTF-8') ?></span><span class="pending-badge">Pending Review</span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>

  <!-- Document Footer -->
  <footer class="cert-doc-footer">
    <div>
      <p class="issued-bold">Issued on <?= htmlspecialchars($requestedOn, ENT_QUOTES, 'UTF-8') ?></p>
      <p>Student pathway: <?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="signature-line">Principal signature</div>
  </footer>
</div>
