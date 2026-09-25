<?php
/**
 * =========================================================================
 * L'ÉCOLE — TEACHER EXTRACURRICULAR JOIN REQUEST CARD COMPONENT
 * =========================================================================
 * 1:1 match with prototype and screenshot:
 * - Sand pill badge: JOIN REQUEST
 * - Club Name: e.g. Cricket Club
 * - Subtitle: Grade · Age group : U15
 * - Inner student card with Name, Submission Date, INDEX NO., and PARENT: APPROVED badge
 * - Side-by-side action buttons: Reject and Approve & Enroll
 *
 * Expects:
 *   - $request   : array  { id, name, grade, ageGroup, indexNo, dateSubmitted, parentStatus, avatar }
 *   - $clubName  : string (e.g. 'Cricket Club')
 *   - $cardIndex : int    (Optional index)
 * =========================================================================
 */

$jr_req          = $request ?? [];
$jr_id           = htmlspecialchars($jr_req['id'] ?? uniqid('req_'));
$jr_name         = htmlspecialchars($jr_req['name'] ?? ($jr_req['studentName'] ?? 'Student Candidate'));
$jr_grade        = htmlspecialchars($jr_req['grade'] ?? 'Grade 9');
$jr_age          = htmlspecialchars($jr_req['ageGroup'] ?? 'U15');
$jr_indexNo      = htmlspecialchars($jr_req['indexNo'] ?? '2022/0301');
$jr_date         = htmlspecialchars($jr_req['dateSubmitted'] ?? ($jr_req['requestedDate'] ?? '2024-06-12'));
$jr_parentStatus = htmlspecialchars($jr_req['parentStatus'] ?? 'PARENT: APPROVED');
$jr_clubName     = htmlspecialchars($clubName ?? 'Cricket Club');
$jr_avatar       = $jr_req['avatar'] ?? '';
?>

<article class="c-teacher-join-card" id="j-join-request-card-<?= $jr_id ?>"
         data-request-id="<?= $jr_id ?>"
         data-student-name="<?= $jr_name ?>"
         data-student-grade="<?= $jr_grade ?>"
         data-age-group="<?= $jr_age ?>">

  <!-- Card Top: Pill Badge, Club Name & Grade/Age Subtitle -->
  <div class="c-teacher-join-card__header">
    <span class="c-teacher-join-card__badge">JOIN REQUEST</span>
    <h3 class="c-teacher-join-card__title c-font-display"><?= $jr_clubName ?></h3>
    <p class="c-teacher-join-card__meta"><?= $jr_grade ?> &middot; Age group : <?= $jr_age ?></p>
  </div>

  <!-- Inner Student Info Box -->
  <div class="c-teacher-join-card__student-box">
    <div class="c-teacher-join-card__student-col">
      <p class="c-teacher-join-card__student-name"><?= $jr_name ?></p>
      <p class="c-teacher-join-card__submit-date">Submitted <?= $jr_date ?></p>
    </div>
    <div class="c-teacher-join-card__index-col">
      <span class="c-teacher-join-card__index-label">INDEX NO.</span>
      <p class="c-teacher-join-card__index-val"><?= $jr_indexNo ?></p>
    </div>
    <div class="c-teacher-join-card__parent-col">
      <span class="c-teacher-join-card__parent-badge"><?= $jr_parentStatus ?></span>
    </div>
  </div>

  <!-- Bottom Actions: Identical to Extracurricular Card Reject / Accept Buttons -->
  <div class="c-teacher-join-card__actions">
    <button type="button"
            class="c-btn c-btn--maroon c-btn--card-action j-reject-request"
            data-request-id="<?= $jr_id ?>"
            data-student-name="<?= $jr_name ?>"
            data-student-grade="<?= $jr_grade ?>"
            data-club-name="<?= $jr_clubName ?>">
      <svg class="c-icon" width="13" height="13"><use href="#icon-close"/></svg>
      <span>Reject</span>
    </button>
    <button type="button"
            class="c-btn c-btn--moss c-btn--card-action j-approve-request"
            data-request-id="<?= $jr_id ?>"
            data-student-name="<?= $jr_name ?>"
            data-student-grade="<?= $jr_grade ?>"
            data-student-avatar="<?= htmlspecialchars($jr_avatar) ?>"
            data-age-group="<?= $jr_age ?>"
            data-club-name="<?= $jr_clubName ?>">
      <svg class="c-icon" width="13" height="13"><use href="#icon-check"/></svg>
      <span>Accept</span>
    </button>
  </div>

</article>
