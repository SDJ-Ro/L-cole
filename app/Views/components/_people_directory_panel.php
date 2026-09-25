<?php
/**
 * =========================================================================
 * L'ÉCOLE — PEOPLE / USERS DIRECTORY PANEL COMPONENT
 * =========================================================================
 * Organism-level component rendering the primary Users Directory interface.
 * Structure matches original Admin & Management design 1:1:
 *   - Top Toolbar (tinted background per role):
 *       - Students: Grade dropdown + Class chips (left), Add Student (right)
 *       - Teachers: Search + Subject + Add Teacher
 *       - Parents : Search + Relation + Add Parent
 *       - Management: Search + Add Staff
 *   - White Context Box (in-line with Class 6-A for students):
 *       - Left: Class 6-A heading
 *       - Right: Search students input + Activity filter dropdown
 *       - Bottom: Enrollment & Class Teacher cards
 *   - Responsive scrollable table window with role-adaptive headers
 *       - Actions column header centered above the buttons
 *       - Parents table: NO relation column (relation is under parent name in col 1)
 *   - Row loop calling _people_directory_row.php
 * =========================================================================
 */

$tabs         = $allowedTabs ?? ['Students', 'Teachers', 'Parents', 'Management Panel'];
$currentTab   = $activeTab ?? ($tabs[0] ?? 'Students');
$allGrades    = $grades ?? [];
$studentList  = $students ?? [];
$teacherList  = $teachers ?? [];
$parentList   = $parents ?? [];
$mgmtList     = $management ?? [];
$contexts     = $classContext ?? [];
$enrollments  = $classEnrollments ?? [];

$activeGradeId = 'g6';
$activeClass   = '6-A';

// Initial counts
$counts = [
    'Students'         => count($studentList),
    'Teachers'         => count($teacherList),
    'Parents'          => count($parentList),
    'Management Panel' => count($mgmtList),
];

// Activity options for student filter
$activityOptions = [
    ['value' => 'all',       'label' => 'All Activities'],
    ['value' => 'Debating',  'label' => 'Debating'],
    ['value' => 'Choir',     'label' => 'Choir'],
    ['value' => 'Robotics',  'label' => 'Robotics'],
    ['value' => 'Swimming',  'label' => 'Swimming'],
    ['value' => 'Science',   'label' => 'Science'],
    ['value' => 'Football',  'label' => 'Football'],
];

// Teacher subject options
$teacherSubjects = array_values(array_unique(array_filter(array_map(fn($t) => $t['subject'] ?? '', $teacherList))));
$teacherSubjectOptions = array_merge([['value' => 'all', 'label' => 'All Subjects']], array_map(fn($s) => ['value' => $s, 'label' => $s], $teacherSubjects));

// Parent relation options
$parentRelations = array_values(array_unique(array_filter(array_map(fn($p) => $p['relation'] ?? '', $parentList))));
$parentRelationOptions = array_merge([['value' => 'all', 'label' => 'All Relations']], array_map(fn($r) => ['value' => $r, 'label' => $r], $parentRelations));

$dirContext    = $context ?? 'admin';
$isTeacherMode = ($dirContext === 'teacher');
?>

<!-- Main People Directory Frame -->
<section class="c-people-panel" id="j-people-panel">

<?php if (!$isTeacherMode): ?>
  <!-- =====================================================================
       1. TINTED TOOLBAR (Swaps controls & tint per role)
       ===================================================================== -->
  <div class="c-people-toolbar c-tint-sky" id="j-people-toolbar">

    <!-- A. Students Toolbar (Grade Dropdown + Class Chips on Left, Add Student on Right) -->
    <div class="j-role-toolbar j-role-toolbar--student" id="j-toolbar-student" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <!-- Grade Dropdown -->
        <div style="min-width: 8.5rem;">
          <?php
          $dropdownId    = 'j-select-grade';
          $options       = array_map(fn($g) => ['value' => $g['id'], 'label' => $g['name']], $allGrades);
          $selectedValue = $activeGradeId;
          $placeholder   = 'Select Grade';
          $labelPrefix   = 'Grade';
          $dropdownLabel = 'Grade Filter';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <!-- Class Chips (6-A in Sky Blue) -->
        <div class="c-class-chip-row" id="j-class-chips">
          <?php
          $initialClasses = $allGrades[0]['classes'] ?? ['6-A', '6-B', '6-C', '6-D'];
          foreach ($initialClasses as $cls):
          ?>
            <button type="button" class="c-class-chip j-class-chip <?= $cls === $activeClass ? 'is-active-chip' : '' ?>" data-class="<?= htmlspecialchars($cls) ?>">
              <?= htmlspecialchars($cls) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Add Student Button -->
      <button type="button" class="c-btn-accent c-tone-sky j-btn-add-account" data-role="student">
        <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-users"/>
        </svg>
        <span>Add Student</span>
      </button>
    </div>

    <!-- B. Teachers Toolbar (Search + Subject on Left, Add Teacher on Right) -->
    <div class="j-role-toolbar j-role-toolbar--teacher" id="j-toolbar-teacher" style="display: none; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <div class="c-search-field" style="min-width: 14rem;">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24"><use href="#icon-search"/></svg>
          <input type="search" class="c-search-field__input j-role-search-input" id="j-teacher-search" placeholder="Search teachers..." autocomplete="off" />
        </div>
        <div style="min-width: 10rem;">
          <?php
          $dropdownId    = 'j-select-subject';
          $options       = $teacherSubjectOptions;
          $selectedValue = 'all';
          $placeholder   = 'All Subjects';
          $labelPrefix   = 'Subject';
          $dropdownLabel = 'Subject Filter';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>
      </div>

      <button type="button" class="c-btn-accent c-tone-sunshine j-btn-add-account" data-role="teacher">
        <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#icon-users"/></svg>
        <span>Add Teacher</span>
      </button>
    </div>

    <!-- C. Parents Toolbar (Search + Relation on Left, Add Parent on Right) -->
    <div class="j-role-toolbar j-role-toolbar--parent" id="j-toolbar-parent" style="display: none; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <div class="c-search-field" style="min-width: 14rem;">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24"><use href="#icon-search"/></svg>
          <input type="search" class="c-search-field__input j-role-search-input" id="j-parent-search" placeholder="Search parents..." autocomplete="off" />
        </div>
        <div style="min-width: 10rem;">
          <?php
          $dropdownId    = 'j-select-relation';
          $options       = $parentRelationOptions;
          $selectedValue = 'all';
          $placeholder   = 'All Relations';
          $labelPrefix   = 'Relationship';
          $dropdownLabel = 'Relation Filter';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>
      </div>

      <button type="button" class="c-btn-accent c-tone-terracotta j-btn-add-account" data-role="parent">
        <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#icon-users"/></svg>
        <span>Add Parent</span>
      </button>
    </div>

    <!-- D. Management Toolbar (Search on Left, Add Staff on Right) -->
    <div class="j-role-toolbar j-role-toolbar--management" id="j-toolbar-management" style="display: none; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <div class="c-search-field" style="min-width: 14rem;">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24"><use href="#icon-search"/></svg>
          <input type="search" class="c-search-field__input j-role-search-input" id="j-management-search" placeholder="Search staff..." autocomplete="off" />
        </div>
      </div>

      <button type="button" class="c-btn-accent c-tone-maroon j-btn-add-account" data-role="management">
        <svg class="c-icon" width="15" height="15" viewBox="0 0 24 24"><use href="#icon-users"/></svg>
        <span>Add Staff</span>
      </button>
    </div>

  </div>

  <!-- =====================================================================
       2. WHITE SECTION (Search & All Activities In Line With Class 6-A Title)
       ===================================================================== -->
  <div class="c-people-context" id="j-student-context-bar">
    <div class="c-context-row" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
      <div>
        <h2 class="c-context-title c-font-display" id="j-context-class-title">Class 6-A</h2>
      </div>

      <!-- Search & All Activities Dropdown In-line with Class 6-A Title -->
      <div class="c-toolbar-right" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <div class="c-search-field" style="min-width: 14rem;">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24"><use href="#icon-search"/></svg>
          <input type="search" class="c-search-field__input j-role-search-input" id="j-student-search" placeholder="Search students..." autocomplete="off" />
        </div>

        <div style="min-width: 10.5rem;">
          <?php
          $dropdownId    = 'j-select-activity';
          $options       = $activityOptions;
          $selectedValue = 'all';
          $placeholder   = 'All Activities';
          $labelPrefix   = 'Activity';
          $dropdownLabel = 'Activity Filter';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>
      </div>
    </div>

    <!-- Context Cards -->
    <div class="c-context-cards">
      <div class="c-context-card">
        <p class="c-context-card-label">Enrollment</p>
        <p class="c-context-card-value" id="j-context-enrollment">30 students</p>
      </div>
      <div class="c-context-card">
        <p class="c-context-card-label">Class Teacher</p>
        <p class="c-context-card-value" id="j-context-teacher">James Wilson</p>
      </div>
    </div>
  </div>

  <!-- Result Count Summary -->
  <div class="c-result-summary" id="j-result-summary">
    <span id="j-count-number"><?= $counts['Students'] ?></span> users found
  </div>
<?php endif; // !$isTeacherMode ?>

  <!-- 1. Students Table -->
  <div class="c-table-scroll j-table-container j-table-container--student" id="j-table-container-student">
    <table class="c-table" id="j-table-student" style="width: 100%; border-collapse: collapse;">
      <thead>
        <?php if ($isTeacherMode): ?>
          <tr style="background: #E4F2F3; color: var(--midnight, #0F414A);">
            <th style="width: 22%; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">STUDENT NAME</th>
            <th style="width: 12%; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">REG. NUMBER</th>
            <th style="width: 20%; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">EXTRA-CURRICULAR ACTIVITIES</th>
            <th style="width: 18%; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;"> <?= ($currentRole ?? '') === 'management' ? 'PARENT / GUARDIAN' : 'STUDENT EMAIL' ?></th>
            <th style="width: 14%; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">PARENT'S NAME</th>
            <th style="width: 12%; padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">CONTACT NUMBER</th>
            <th class="c-align-right" style="width: 6%; padding: 14px 16px; text-align: center; font-size: 11px; font-weight: 700; letter-spacing: 0.05em;">VIEW</th>
          </tr>
        <?php else: ?>
          <tr style="background: rgba(127, 199, 204, 0.2);">
            <th style="width: 20%;">Student</th>
            <th style="width: 14%;">Reg. Number</th>
            <th style="width: 25%;">Extra-Curricular Activities</th>
            <th style="width: 20%;"> <?= ($currentRole ?? '') === 'management' ? 'Parent / Guardian' : 'Student Email' ?></th>
            <th style="width: 11%;">Account access</th>
            <th class="c-align-right" style="width: 10%;">Actions</th>
          </tr>
        <?php endif; ?>
      </thead>
      <tbody>
        <?php foreach ($studentList as $student): ?>
          <?php
          $role    = 'student';
          $person  = $student;
          $context = $dirContext;
          require __DIR__ . '/_people_directory_row.php';
          ?>
        <?php endforeach; ?>
        <tr class="c-empty-row" style="display: none;">
          <td colspan="<?= $isTeacherMode ? 7 : 6 ?>" style="padding: 32px 16px; text-align: center;">
            <p class="c-empty-title" style="font-size: 0.9375rem; font-weight: 700; color: var(--midnight, #0F414A); margin: 0 0 4px 0;">No students match these filters.</p>
            <p class="c-empty-desc" style="font-size: 0.8125rem; color: rgba(15,65,74,0.6); margin: 0;">Try a different search term or clear the filters.</p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

<?php if (!$isTeacherMode): ?>

  <!-- 2. Teachers Table -->
  <div class="c-table-scroll j-table-container j-table-container--teacher" id="j-table-container-teacher" style="display: none;">
    <table class="c-table" id="j-table-teacher">
      <thead>
        <tr style="background: rgba(234, 137, 19, 0.2);">
          <th style="width: 16%;">Teacher</th>
          <th style="width: 14%; min-width: 90px;">ID</th>
          <th style="width: 24%; min-width: 180px;">Subject &amp; Classes</th>
          <th style="width: 16%; min-width: 120px;">Roles</th>
          <th style="width: 15%;">Contact Info</th>
          <th style="width: 9%;">Account access</th>
          <th class="c-align-right" style="width: 6%;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($teacherList as $teacher): ?>
          <?php
          $role   = 'teacher';
          $person = $teacher;
          require __DIR__ . '/_people_directory_row.php';
          ?>
        <?php endforeach; ?>
        <tr class="c-empty-row" style="display: none;">
          <td colspan="7">
            <p class="c-empty-title">No teachers match these filters.</p>
            <p class="c-empty-desc">Try a different search term or clear the filters.</p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- 3. Parents Table -->
  <div class="c-table-scroll j-table-container j-table-container--parent" id="j-table-container-parent" style="display: none;">
    <table class="c-table" id="j-table-parent">
      <thead>
        <tr style="background: rgba(175, 80, 49, 0.15);">
          <th style="width: 24%;">Parent</th>
          <th style="width: 13%;">ID</th>
          <th style="width: 28%;">Linked Students</th>
          <th style="width: 17%;">Contact Info</th>
          <th style="width: 10%;">Account access</th>
          <th class="c-align-right" style="width: 8%;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($parentList as $parent): ?>
          <?php
          $role   = 'parent';
          $person = $parent;
          require __DIR__ . '/_people_directory_row.php';
          ?>
        <?php endforeach; ?>
        <tr class="c-empty-row" style="display: none;">
          <td colspan="6">
            <p class="c-empty-title">No parents match these filters.</p>
            <p class="c-empty-desc">Try a different search term or clear the filters.</p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- 4. Management Panel Table -->
  <?php if (in_array('Management Panel', $tabs)): ?>
    <div class="c-table-scroll j-table-container j-table-container--management" id="j-table-container-management" style="display: none;">
      <table class="c-table" id="j-table-management">
        <thead>
          <tr style="background: rgba(127, 3, 3, 0.1);">
            <th style="width: 28%;">Staff Member</th>
            <th style="width: 14%;">ID</th>
            <th style="width: 30%;">Contact Info</th>
            <th style="width: 16%;">Account access</th>
            <th class="c-align-right" style="width: 12%;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mgmtList as $member): ?>
            <?php
            $role   = 'management';
            $person = $member;
            require __DIR__ . '/_people_directory_row.php';
            ?>
          <?php endforeach; ?>
          <tr class="c-empty-row" style="display: none;">
            <td colspan="5">
              <p class="c-empty-title">No staff match these filters.</p>
              <p class="c-empty-desc">Try a different search term or clear the filters.</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; // !$isTeacherMode ?>

</section>
