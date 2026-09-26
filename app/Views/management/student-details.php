<!doctype html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e($csrf_token) ?>">
<title>Student details</title><link rel="stylesheet" href="/assets/css/global.css">
<link rel="stylesheet" href="/assets/css/components/parent-picker.css"></head>
<body><main style="max-width:800px;margin:3rem auto;padding:1.5rem;background:white;border-radius:12px">
<a href="/management/people">Back to directory</a>
<h1><?= e($student['name']) ?></h1>
<dl>
<?php foreach (['id'=>'Student index','className'=>'Class','dateOfBirth'=>'Date of birth','gender'=>'Gender','address'=>'Home address','admissionDate'=>'Admission date','parentName'=>'Guardian','parentId'=>'Guardian ID','email'=>'Guardian email','phone'=>'Guardian phone','status'=>'Account status'] as $field=>$label): ?>
<dt style="font-weight:bold;margin-top:1rem"><?= e($label) ?></dt><dd style="margin-left:0"><?= e($student[$field] ?: 'Not recorded') ?></dd>
<?php endforeach; ?>
</dl>
<?php if (($student['status'] ?? '') !== 'Deactivated'): ?>
<button type="button" id="open-guardian-change" class="c-btn-accent c-tone-terracotta">Change Guardian</button>
<?php endif; ?>
</main>
<dialog id="guardian-change-dialog" class="c-parent-picker" aria-labelledby="guardian-change-title">
  <h2 id="guardian-change-title">Change guardian for <?= e($student['name']) ?></h2>
  <p>This changes only <?= e($student['id']) ?>. Other children linked to the current parent are not affected.</p>
  <label for="guardian-search">Search by name, parent ID, or email</label>
  <input type="search" id="guardian-search" class="c-form-input" placeholder="Search parents" autocomplete="off">
  <p id="guardian-result-count" role="status"></p>
  <div id="guardian-results" class="c-parent-picker-results"></div>
  <section id="guardian-confirmation" class="c-parent-selection" hidden>
    <strong id="guardian-selected-name"></strong><span id="guardian-selected-code"></span>
    <p>Replace <?= e($student['parentName'] ?: 'the current guardian') ?> for this student?</p>
    <p id="guardian-change-error" role="alert" hidden></p>
    <div class="c-parent-picker-footer"><button type="button" id="back-to-guardian-results" class="c-btn-outline-sky">Back</button><button type="button" id="confirm-guardian-change" class="c-btn-accent c-tone-terracotta">Confirm Change</button></div>
  </section>
  <div class="c-parent-picker-footer" id="guardian-picker-footer"><button type="button" id="cancel-guardian-change" class="c-btn-outline-sky">Cancel</button></div>
</dialog>
<script>
window.__GUARDIAN_CHANGE__ = <?= json_encode(['studentIndex'=>$student['id'],'currentParentId'=>$student['parentId'],'parents'=>array_values(array_filter($parents ?? [], fn($parent) => ($parent['status'] ?? '') !== 'Deactivated'))], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="/assets/js/components/guardian-change.js"></script>
</body></html>
