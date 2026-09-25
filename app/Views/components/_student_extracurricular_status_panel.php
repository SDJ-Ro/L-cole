<?php
/**
 * =========================================================================
 * L'ÉCOLE — STUDENT EXTRACURRICULAR DETAILS & STATUS PANEL
 * =========================================================================
 * Displays the Details (Schedule & Location) and Status (Enrolled Since & Date)
 * cards side-by-side below the Notice Board in the student view.
 * Reuses the _student_extracurricular_staff_card.php component for both cards.
 *
 * Expects:
 *   - $club : array (current club data)
 * =========================================================================
 */

$clubData       = $club ?? [];
$schedule       = $clubData['schedule'] ?? 'Tuesdays & Thursdays, 3:30 – 5:30 PM';
$location       = $clubData['location'] ?? 'Main Cricket Ground';
$enrolledSince  = $clubData['enrolledSince'] ?? (!empty($clubData['enrolled']) ? 'Grade 9' : 'Not Enrolled');
$enrollmentDate = $clubData['enrollmentDate'] ?? ($clubData['enrolledDate'] ?? (!empty($clubData['enrolled']) ? 'September 4, 2023' : '—'));
?>

<section class="c-panel" id="j-student-details-status-panel" style="margin-top: 1.5rem; background: #ffffff; border-radius: var(--radius-xl, 0.875rem); border: 1px solid var(--color-border, #EFE8DF); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 2px rgba(15, 65, 74, 0.06));">
  <div class="c-staff-grid c-staff-grid--two">
    <!-- Details Card (reusing _student_extracurricular_staff_card.php) -->
    <?php
    $cardTitle = 'Details';
    $cardType  = 'info';
    $infoItems = [
        [
            'icon'  => 'icon-clock',
            'label' => 'Schedule',
            'value' => $schedule,
            'id'    => 'j-detail-schedule'
        ],
        [
            'icon'  => 'icon-mapPin',
            'label' => 'Location',
            'value' => $location,
            'id'    => 'j-detail-location'
        ],
    ];
    require __DIR__ . '/_student_extracurricular_staff_card.php';
    ?>

    <!-- Status Card (reusing _student_extracurricular_staff_card.php) -->
    <?php
    $cardTitle = 'Status';
    $cardType  = 'info';
    $infoItems = [
        [
            'icon'  => 'icon-check',
            'label' => 'Enrolled Since',
            'value' => $enrolledSince,
            'id'    => 'j-detail-enrolled-since'
        ],
        [
            'icon'  => 'icon-calendarDays',
            'label' => 'Enrollment Date',
            'value' => $enrollmentDate,
            'id'    => 'j-detail-enrolled-date'
        ],
    ];
    require __DIR__ . '/_student_extracurricular_staff_card.php';
    ?>
  </div>
</section>
