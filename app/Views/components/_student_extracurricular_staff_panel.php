<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT EXTRACURRICULAR STAFF PANEL COMPONENT
 * =========================================================================
 * Renders the two staff cards (Teacher in Charge & Coach / Instructor)
 * side-by-side directly below the Achievements & Gallery section in the
 * student extracurricular view, matching the prototype design.
 *
 * Reuses the _student_extracurricular_staff_card template for each card.
 *
 * Expects:
 *   - $club : array (current club data with 'tic' and 'coach' keys)
 * =========================================================================
 */

$clubData = $club ?? [];
$tic      = $clubData['tic'] ?? [];
$coach    = $clubData['coach'] ?? [];
?>

<section class="c-panel" id="j-student-staff-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
  <div class="c-staff-grid c-staff-grid--two">
    <!-- Teacher in Charge Card -->
    <?php
    $staffRole      = 'Teacher in Charge';
    $staffName      = $tic['name'] ?? 'Mr. Weerasinghe';
    $staffSpecialty = $tic['subject'] ?? 'Teacher in Charge';
    $staffAvatar    = $tic['avatar'] ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces';
    $staffEmail     = $tic['email'] ?? 'weerasinghe@lecole.edu';
    $staffPhone     = $tic['phone'] ?? '+94 77 123 4567';
    $staffIdPrefix  = 'tic';
    require __DIR__ . '/_student_extracurricular_staff_card.php';
    ?>

    <!-- Coach / Instructor Card -->
    <?php
    $staffRole      = 'Coach / Instructor';
    $staffName      = $coach['name'] ?? 'Coach Dinesh Fernando';
    $staffSpecialty = $coach['specialty'] ?? 'Head Coach';
    $staffAvatar    = $coach['avatar'] ?? 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=100&h=100&fit=crop&crop=faces';
    $staffEmail     = $coach['email'] ?? 'dinesh.coach@lecole.edu';
    $staffPhone     = $coach['phone'] ?? '+94 71 987 6543';
    $staffIdPrefix  = 'coach';
    require __DIR__ . '/_student_extracurricular_staff_card.php';
    ?>
  </div>
</section>
