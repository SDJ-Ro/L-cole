<?php
/**
 * =========================================================================
 * L'ÉCOLE — PEOPLE DIRECTORY ROW COMPONENT
 * =========================================================================
 * Renders a single <tr> table row adaptively for Students, Teachers, Parents,
 * or Management Panel staff. Direct 1:1 extraction from Admin/people/app.js.
 *
 * Expects:
 *   - $role   : 'student' | 'teacher' | 'parent' | 'management'
 *   - $person : array of user attributes
 * =========================================================================
 */

$userRole  = $role ?? 'student';
$p         = $person ?? [];
$isTeacher = ($context ?? '') === 'teacher';

$name     = $p['name'] ?? trim(($p['firstName'] ?? '') . ' ' . ($p['lastName'] ?? ''));
$initials = $p['initials'] ?? (substr($p['firstName'] ?? 'U', 0, 1) . substr($p['lastName'] ?? '', 0, 1));
$avatar   = !empty($p['avatar']) ? $p['avatar'] : (!empty($p['tone']) ? $p['tone'] : 'bg-sand text-midnight');
$status   = $p['status'] ?? 'Active';
$id       = $p['index'] ?? ($p['id'] ?? '');

// Helper for exact original activity badge styles
$getActStyle = function($act) {
    $n = strtolower(trim($act));
    $base = 'border-radius:var(--radius-lg, 0.5rem); padding:0.25rem 0.5rem; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; display:inline-block;';
    if (strpos($n, 'debating') !== false) return 'background:rgba(127,199,204,0.3); color:var(--midnight, #0F414A); ' . $base;
    if (strpos($n, 'choir') !== false) return 'background:rgba(234,137,19,0.2); color:var(--midnight, #0F414A); ' . $base;
    if (strpos($n, 'robotics') !== false) return 'background:rgba(228,203,169,0.5); color:var(--midnight, #0F414A); ' . $base;
    if (strpos($n, 'swimming') !== false) return 'background:rgba(150,192,206,0.3); color:var(--midnight, #0F414A); ' . $base;
    if (strpos($n, 'science') !== false) return 'background:rgba(164,171,152,0.3); color:var(--moss, #4B5B34); ' . $base;
    if (strpos($n, 'football') !== false) return 'background:rgba(175,80,49,0.15); color:var(--terracotta, #AF5031); ' . $base;
    return 'background:#f4ebe1; color:var(--midnight, #0F414A); ' . $base;
};
?>

<?php if ($userRole === 'student'): ?>
  <tr class="c-row-hover-sky j-person-row" data-role="student" data-id="<?= htmlspecialchars($id) ?>" data-grade="<?= htmlspecialchars($p['gradeId'] ?? '') ?>" data-class="<?= htmlspecialchars($p['className'] ?? '') ?>" data-activities="<?= htmlspecialchars(implode(',', $p['activities'] ?? [])) ?>">
    <td>
      <div class="c-person-cell">
        <div class="c-avatar c-avatar-sm <?= htmlspecialchars($avatar) ?>"><?= htmlspecialchars($initials) ?></div>
        <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);"><?= htmlspecialchars($name) ?></span>
      </div>
    </td>
    <td style="font-size:0.75rem;font-weight:500;color:rgba(15,65,74,0.7);"><?= htmlspecialchars($id) ?></td>
    <td>
      <?php if (!empty($p['activities'])): ?>
        <div class="c-tag-row">
          <?php foreach ($p['activities'] as $act): ?>
            <span class="c-tag" style="<?= $getActStyle($act) ?>"><?= htmlspecialchars($act) ?></span>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <span class="c-tag-muted" style="font-size:11px;font-style:italic;color:rgba(15,65,74,0.4);">None</span>
      <?php endif; ?>
    </td>
    <td style="font-size:0.75rem;color:rgba(15,65,74,0.8);">
      <span class="c-mail-inline">
        <svg class="c-icon c-icon-muted" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
        <span><?= htmlspecialchars($p['email'] ?? 'Not recorded') ?></span>
      </span>
    </td>

    <?php if ($isTeacher): ?>
      <!-- Teacher Context: Parent Name & Contact Phone -->
      <td style="font-size:0.8125rem; font-weight:500; color:var(--midnight, #0F414A);"><?= htmlspecialchars($p['parentName'] ?? 'Parent') ?></td>
      <td style="font-size:0.8125rem; font-weight:500; color:rgba(15,65,74,0.8);"><?= htmlspecialchars($p['phone'] ?? 'Not recorded') ?></td>
      <td class="c-align-right" style="text-align: center;">
        <div class="c-row-actions" style="justify-content: center;">
          <button type="button" class="c-row-action-btn j-open-profile" data-role="student" data-id="<?= htmlspecialchars($id) ?>" title="View student profile">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
            <span class="c-row-action-label">View</span>
          </button>
        </div>
      </td>
    <?php else: ?>
      <!-- Admin Context: Account Access Toggle & View/Edit -->
      <td>
        <div style="min-width: 8.5rem;">
          <?php
          $dropdownId    = 'j-status-' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $id));
          $options       = [
              ['value' => 'Active', 'label' => 'Active'],
              ['value' => 'Deactivated', 'label' => 'Deactivated']
          ];
          $selectedValue = $status;
          $dropdownClass = 'c-dropdown--status c-dropdown--status-' . strtolower($status);
          $dropdownLabel = 'Account status for ' . $name;
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>
      </td>
      <td class="c-align-right">
        <div class="c-row-actions" style="justify-content: flex-end;">
          <button type="button" class="c-row-action-btn j-open-profile" data-role="student" data-id="<?= htmlspecialchars($id) ?>" title="View student profile">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
            <span class="c-row-action-label">View</span>
          </button>
          <button type="button" class="c-row-action-btn j-edit-profile" data-role="student" data-id="<?= htmlspecialchars($id) ?>" title="Edit student record">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
            <span class="c-row-action-label">Edit</span>
          </button>
        </div>
      </td>
    <?php endif; ?>
  </tr>

<?php elseif ($userRole === 'teacher'): ?>
  <tr class="c-row-hover-sunshine j-person-row" data-role="teacher" data-id="<?= htmlspecialchars($id) ?>" data-subject="<?= htmlspecialchars($p['subject'] ?? '') ?>" data-classes="<?= htmlspecialchars(implode(',', $p['classes'] ?? [])) ?>" data-tic="<?= htmlspecialchars($p['tic'] ?? '') ?>">
    <td>
      <div class="c-person-cell">
        <div class="c-avatar c-avatar-md <?= htmlspecialchars($avatar) ?>"><?= htmlspecialchars($initials) ?></div>
        <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);"><?= htmlspecialchars($name) ?></span>
      </div>
    </td>
    <td style="font-size:0.75rem;font-weight:700;color:var(--midnight, #0F414A);"><?= htmlspecialchars($id) ?></td>
    <td>
      <p style="font-size:0.75rem;font-weight:600;color:var(--midnight, #0F414A);margin:0;"><?= htmlspecialchars($p['subject'] ?? '') ?></p>
      <?php if (!empty($p['classes'])): ?>
        <div class="c-tag-row" style="margin-top:0.25rem;flex-wrap:nowrap;">
          <?php foreach ($p['classes'] as $cls): ?>
            <span class="c-tag" style="background:rgba(234,137,19,0.2);color:var(--midnight, #0F414A);font-size:10px;font-weight:700;padding:0.25rem 0.5rem;border-radius:var(--radius-lg, 0.5rem);white-space:nowrap;"><?= htmlspecialchars($cls) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </td>
    <td>
      <p style="font-size:0.75rem;font-weight:600;color:rgba(15,65,74,0.8);margin:0;display:flex;align-items:center;gap:6px;line-height:1.2;">
        <span><?= htmlspecialchars($p['role'] === 'Class Teacher' ? 'Class' : ($p['role'] ?? 'Teacher')) ?></span>
        <?php if (!empty($p['classTeacherOf'])): ?>
          <span class="c-tag" style="background:rgba(234,137,19,0.2);color:var(--midnight, #0F414A);font-weight:700;font-size:11px;margin:0;vertical-align:middle;white-space:nowrap;padding:0.2rem 0.4rem;border-radius:var(--radius-lg, 0.5rem);"><?= htmlspecialchars($p['classTeacherOf']) ?></span>
        <?php endif; ?>
      </p>
      <?php if (!empty($p['tic'])): ?>
        <p style="margin-top:0.25rem;margin-bottom:0;font-size:11px;font-weight:500;color:rgba(15,65,74,0.5);white-space:nowrap;">TIC: <?= htmlspecialchars($p['tic']) ?></p>
      <?php endif; ?>
    </td>
    <td class="c-stack-tight">
      <span class="c-contact-line">
        <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
        <span><?= htmlspecialchars($p['email'] ?? 'Not recorded') ?></span>
      </span>
      <span class="c-contact-line" style="margin-top:0.25rem;">
        <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-phone"/></svg>
        <span><?= htmlspecialchars($p['phone'] ?? 'Not recorded') ?></span>
      </span>
    </td>
    <td>
      <div style="min-width: 8.5rem;">
        <?php
        $dropdownId    = 'j-status-' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $id));
        $options       = [
            ['value' => 'Active', 'label' => 'Active'],
            ['value' => 'Deactivated', 'label' => 'Deactivated']
        ];
        $selectedValue = $status;
        $dropdownClass = 'c-dropdown--status c-dropdown--status-' . strtolower($status);
        $dropdownLabel = 'Account status for ' . $name;
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </td>
    <td class="c-align-right">
      <div class="c-row-actions" style="justify-content: flex-end;">
        <button type="button" class="c-row-action-btn j-open-profile" data-role="teacher" data-id="<?= htmlspecialchars($id) ?>" title="View teacher details">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
          <span class="c-row-action-label">View</span>
        </button>
        <button type="button" class="c-row-action-btn j-edit-profile" data-role="teacher" data-id="<?= htmlspecialchars($id) ?>" title="Edit teacher details">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
          <span class="c-row-action-label">Edit</span>
        </button>
      </div>
    </td>
  </tr>

<?php elseif ($userRole === 'parent'): ?>
  <tr class="c-row-hover-terracotta j-person-row" data-role="parent" data-id="<?= htmlspecialchars($id) ?>" data-relation="<?= htmlspecialchars($p['relation'] ?? '') ?>">
    <td>
      <div class="c-person-cell">
        <div class="c-avatar c-avatar-md <?= htmlspecialchars($avatar) ?>"><?= htmlspecialchars($initials) ?></div>
        <div>
          <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);display:block;"><?= htmlspecialchars($name) ?></span>
          <p class="c-subtext" style="margin-top:0.125rem;margin-bottom:0;font-size:11px;font-weight:500;color:rgba(15,65,74,0.5);"><?= htmlspecialchars($p['relation'] ?? 'Parent') ?></p>
        </div>
      </div>
    </td>
    <td style="font-size:0.75rem;font-weight:700;color:var(--midnight, #0F414A);"><?= htmlspecialchars($id) ?></td>
    <td>
      <?php if (!empty($p['children'])): ?>
        <div style="display:flex;flex-direction:column;gap:0.375rem;">
          <?php foreach ($p['children'] as $child): ?>
            <?php
            preg_match('/—\s*(\d+)-/', $child, $m);
            $gradeStr = $m[1] ?? '';
            $style = 'background:rgba(175,80,49,0.15);color:var(--terracotta, #AF5031);';
            if ($gradeStr === '6') $style = 'background:rgba(234,137,19,0.2);color:var(--sunshine, #EA8913);';
            elseif ($gradeStr === '7') $style = 'background:rgba(127,199,204,0.3);color:var(--skyblue, #207C82);';
            elseif ($gradeStr === '8') $style = 'background:rgba(164,171,152,0.3);color:var(--moss, #4B5B34);';
            elseif ($gradeStr === '9') $style = 'background:rgba(15,65,74,0.15);color:var(--midnight, #0F414A);';
            elseif ($gradeStr === '10' || $gradeStr === '11') $style = 'background:rgba(127,3,3,0.1);color:var(--maroon, #7F0303);';
            ?>
            <button type="button" class="c-tag-chip j-open-linked-student" style="<?= $style ?> font-size:10px;font-weight:700;padding:0.25rem 0.5rem;border-radius:var(--radius-lg, 0.5rem);border:none;cursor:pointer;width:fit-content;text-align:left;" data-link="<?= htmlspecialchars($child) ?>">
              <?= htmlspecialchars($child) ?>
            </button>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <span class="c-tag-muted" style="font-size:11px;font-style:italic;color:rgba(15,65,74,0.4);">None linked</span>
      <?php endif; ?>
    </td>
    <td class="c-stack-tight">
      <span class="c-contact-line">
        <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
        <span><?= htmlspecialchars($p['email'] ?? 'Not recorded') ?></span>
      </span>
      <span class="c-contact-line" style="margin-top:0.25rem;">
        <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-phone"/></svg>
        <span><?= htmlspecialchars($p['phone'] ?? 'Not recorded') ?></span>
      </span>
    </td>
    <td>
      <div style="min-width: 8.5rem;">
        <?php
        $dropdownId    = 'j-status-' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $id));
        $options       = [
            ['value' => 'Active', 'label' => 'Active'],
            ['value' => 'Deactivated', 'label' => 'Deactivated']
        ];
        $selectedValue = $status;
        $dropdownClass = 'c-dropdown--status c-dropdown--status-' . strtolower($status);
        $dropdownLabel = 'Account status for ' . $name;
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </td>
    <td class="c-align-right">
      <div class="c-row-actions" style="justify-content: flex-end;">
        <button type="button" class="c-row-action-btn j-open-profile" data-role="parent" data-id="<?= htmlspecialchars($id) ?>" title="View parent profile">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
          <span class="c-row-action-label">View</span>
        </button>
        <button type="button" class="c-row-action-btn j-edit-profile" data-role="parent" data-id="<?= htmlspecialchars($id) ?>" title="Edit parent profile">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
          <span class="c-row-action-label">Edit</span>
        </button>
      </div>
    </td>
  </tr>

<?php elseif ($userRole === 'management'): ?>
  <tr class="c-row-hover-maroon j-person-row" data-role="management" data-id="<?= htmlspecialchars($id) ?>">
    <td>
      <div class="c-person-cell">
        <div class="c-avatar c-avatar-md <?= htmlspecialchars($avatar) ?>"><?= htmlspecialchars($initials) ?></div>
        <div>
          <span class="c-person-name" style="font-weight:600;font-size:0.875rem;color:var(--midnight, #0F414A);display:block;"><?= htmlspecialchars($name) ?></span>
          <?php if (!empty($p['jobTitle'])): ?>
            <p class="c-subtext" style="margin-top:0.125rem;margin-bottom:0;font-size:11px;font-weight:500;color:rgba(15,65,74,0.5);"><?= htmlspecialchars($p['jobTitle']) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </td>
    <td style="font-size:0.75rem;font-weight:700;color:var(--midnight, #0F414A);"><?= htmlspecialchars($id) ?></td>
    <td class="c-stack-tight">
      <span class="c-contact-line">
        <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-mail"/></svg>
        <span><?= htmlspecialchars($p['email'] ?? 'Not recorded') ?></span>
      </span>
      <span class="c-contact-line" style="margin-top:0.25rem;">
        <svg class="c-icon c-icon-muted" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(15,65,74,0.4);"><use href="#icon-phone"/></svg>
        <span><?= htmlspecialchars($p['phone'] ?? 'Not recorded') ?></span>
      </span>
    </td>
    <td>
      <div style="min-width: 8.5rem;">
        <?php
        $dropdownId    = 'j-status-' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $id));
        $options       = [
            ['value' => 'Active', 'label' => 'Active'],
            ['value' => 'Deactivated', 'label' => 'Deactivated']
        ];
        $selectedValue = $status;
        $dropdownClass = 'c-dropdown--status c-dropdown--status-' . strtolower($status);
        $dropdownLabel = 'Account status for ' . $name;
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </td>
    <td class="c-align-right">
      <div class="c-row-actions" style="justify-content: flex-end;">
        <button type="button" class="c-row-action-btn j-open-profile" data-role="management" data-id="<?= htmlspecialchars($id) ?>" title="View staff profile">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-eye"/></svg>
          <span class="c-row-action-label">View</span>
        </button>
        <button type="button" class="c-row-action-btn j-edit-profile" data-role="management" data-id="<?= htmlspecialchars($id) ?>" title="Edit staff profile">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-edit"/></svg>
          <span class="c-row-action-label">Edit</span>
        </button>
      </div>
    </td>
  </tr>
<?php endif; ?>
