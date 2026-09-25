<?php
/**
 * =========================================================================
 * L'ÉCOLE — UNASSIGNED CANDIDATE ROW COMPONENT
 * =========================================================================
 * Renders an unassigned student/candidate row box with avatar, name, grade,
 * age group badge, and a team assignment selector + Assign button.
 *
 * Expects:
 *   - $student : array { id, name, grade, avatar, ageGroup }
 *   - $teams   : array of teams in current extracurricular activity
 *   - $canEdit : bool (default true)
 * =========================================================================
 */

$uc_student  = $student ?? [];
$uc_teams    = $teams   ?? [];
$uc_canEdit  = $canEdit ?? true;

$uc_id       = htmlspecialchars($uc_student['id'] ?? uniqid('u_'));
$uc_name     = htmlspecialchars($uc_student['name'] ?? 'Candidate');
$uc_grade    = htmlspecialchars($uc_student['grade'] ?? 'Student');
$uc_age      = htmlspecialchars($uc_student['ageGroup'] ?? '');
$uc_avatar   = htmlspecialchars($uc_student['avatar'] ?? '');
if (empty($uc_avatar)) {
    $uc_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($uc_student['name'] ?? 'U') . '&size=64&background=EFE8DF&color=0F414A';
}
?>
<div class="c-unassigned-item" id="j-unassigned-item-<?= $uc_id ?>" data-student-id="<?= $uc_id ?>" data-age-group="<?= $uc_age ?>">
  <div class="c-unassigned-item__left">
    <img src="<?= $uc_avatar ?>" alt="<?= $uc_name ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($uc_student['name'] ?? 'U') ?>&size=64&background=EFE8DF&color=0F414A';" />
    <div>
      <p class="c-unassigned-item__name"><?= $uc_name ?></p>
      <p class="c-unassigned-item__grade">
        <?= $uc_grade ?>
        <?php if (!empty($uc_age)): ?>
          <span class="c-unassigned-item__badge"><?= $uc_age ?></span>
        <?php endif; ?>
      </p>
    </div>
  </div>
  <?php if ($uc_canEdit): ?>
    <div class="c-unassigned-item__right">
      <div class="c-unassigned-dropdown j-unassigned-dropdown" data-student-id="<?= $uc_id ?>">
        <button type="button" class="c-btn-assign j-assign-trigger-btn" aria-haspopup="true" aria-expanded="false" <?= empty($uc_teams) ? 'disabled' : '' ?>>
          <span>Assign</span>
          <svg class="c-icon" width="13" height="13"><use href="#icon-chevronDown"/></svg>
        </button>
        <div class="c-unassigned-menu j-unassigned-menu">
          <?php foreach ($uc_teams as $tIdx => $t): ?>
            <button type="button" class="c-unassigned-choice j-assign-team-choice" data-team-index="<?= (int)$tIdx ?>" data-student-id="<?= $uc_id ?>">
              <span><?= htmlspecialchars($t['name'] ?? ('Team ' . ($tIdx + 1))) ?></span>
              <svg class="c-icon" width="12" height="12"><use href="#icon-chevronRight"/></svg>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
