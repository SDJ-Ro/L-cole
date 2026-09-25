<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR TEAM CARD COMPONENT
 * =========================================================================
 * Renders a single team card with roster list.
 * Includes a self-contained Edit Team modal (c-modal-layer) with:
 *   - Age Group custom dropdown (_dropdown.php)
 *   - Dynamic roster editor (add/remove members)
 *   - Cover image uploader
 *   - Delete Team action wired to window.openUniversalDeleteModal
 *
 * Expects:
 *   - $team      : array  { name, coverImage, ageGroup, roster: [{name,grade,position,avatar}] }
 *   - $teamIndex : int    (Position in parent teams array, used as data-index)
 *   - $canEdit   : bool   (Show Edit button; default true)
 *   - $memberWord: string (e.g. 'Members', 'Players'; default 'Members')
 *   - $teamWord  : string (e.g. 'Team', 'Squad'; default 'Team')
 *   - $ageGroups : array  (Optional list of age groups for dropdown)
 * =========================================================================
 */

$tc_team      = $team      ?? [];
$tc_index     = (int)($teamIndex ?? 0);
$tc_canEdit   = $canEdit   ?? true;
$tc_memberW   = $memberWord ?? 'Members';
$tc_singularM = rtrim($tc_memberW, 's');
$tc_teamW     = $teamWord  ?? 'Team';
$tc_ageGroups = $ageGroups ?? ['Under 13', 'Under 15', 'Under 17', 'Under 19', 'Open'];

$tc_name   = $tc_team['name']       ?? 'Unnamed Team';
$tc_cover  = $tc_team['coverImage'] ?? '';
$tc_age    = $tc_team['ageGroup']   ?? '';
$tc_roster = $tc_team['roster']     ?? [];
$tc_uid    = 'tc-' . $tc_index;
?>

<article class="c-team-card" id="j-team-card-<?= $tc_index ?>" style="animation-delay: <?= $tc_index * 40 ?>ms"
         data-team-index="<?= $tc_index ?>">

  <!-- Cover image -->
  <div class="c-team-card__cover" id="j-team-card-cover-wrap-<?= $tc_index ?>">
    <?php if (!empty($tc_cover)): ?>
      <img src="<?= htmlspecialchars($tc_cover) ?>" alt="<?= htmlspecialchars($tc_name) ?>" id="j-team-card-cover-img-<?= $tc_index ?>" onerror="this.style.display='none'; if(this.parentElement.querySelector('.c-team-card__cover-empty')) this.parentElement.querySelector('.c-team-card__cover-empty').style.display='flex';" />
      <div class="c-team-card__cover-empty" id="j-team-card-cover-empty-<?= $tc_index ?>" style="display:none;">
        <svg class="c-icon" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <use href="#icon-imagePlus"/>
        </svg>
      </div>
    <?php else: ?>
      <div class="c-team-card__cover-empty" id="j-team-card-cover-empty-<?= $tc_index ?>">
        <svg class="c-icon" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <use href="#icon-imagePlus"/>
        </svg>
      </div>
    <?php endif; ?>
    <div class="c-team-card__cover-tint"></div>

    <?php if ($tc_canEdit): ?>
      <button type="button"
              class="c-team-card__edit-btn j-edit-team-btn-<?= $tc_index ?>"
              data-team-index="<?= $tc_index ?>"
              aria-label="Edit <?= htmlspecialchars($tc_name) ?>">
        <svg class="c-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <use href="#icon-edit"/>
        </svg>
        Edit
      </button>
    <?php endif; ?>
  </div>

  <!-- Card body -->
  <div class="c-team-card__body">
    <div class="c-team-card__top">
      <h3 class="c-team-card__name c-font-display" id="j-team-card-name-<?= $tc_index ?>"><?= htmlspecialchars($tc_name) ?></h3>
      <span class="c-team-card__count" id="j-team-card-count-<?= $tc_index ?>">
        <?= count($tc_roster) ?> <?= htmlspecialchars($tc_memberW) ?>
      </span>
    </div>

    <?php if (!empty($tc_roster)): ?>
      <ul class="c-team-card__roster" id="j-team-card-roster-<?= $tc_index ?>">
        <?php foreach ($tc_roster as $mIdx => $member): ?>
          <?php
            $mName   = htmlspecialchars($member['name'] ?? '');
            $mGrade  = htmlspecialchars($member['grade'] ?? '');
            $mPos    = htmlspecialchars($member['position'] ?? '');
            $mAvatar = htmlspecialchars($member['avatar'] ?? '');
          ?>
          <li class="c-team-card__member" data-member-index="<?= (int)$mIdx ?>">
            <div class="c-team-card__member-left">
              <img src="<?= $mAvatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($member['name'] ?? 'M') . '&size=64&background=EFE8DF&color=0F414A' ?>"
                   alt="<?= $mName ?>" />
              <div>
                <p class="c-team-card__member-name"><?= $mName ?></p>
                <p class="c-team-card__member-grade"><?= $mGrade ?></p>
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.35rem;">
              <?php if ($mPos): ?>
                <span class="c-team-card__member-position"><?= $mPos ?></span>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="c-team-card__no-roster" id="j-team-card-no-roster-<?= $tc_index ?>">
        No <?= htmlspecialchars(strtolower($tc_memberW)) ?> added yet.
        <?php if ($tc_canEdit): ?>Use Edit to build this roster.<?php endif; ?>
      </p>
    <?php endif; ?>
  </div>
</article>


