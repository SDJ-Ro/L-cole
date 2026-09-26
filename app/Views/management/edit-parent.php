<!doctype html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Parent — L’École</title>
<link rel="stylesheet" href="/assets/css/global.css">
<link rel="stylesheet" href="/assets/css/components/form-card.css">
<link rel="stylesheet" href="/assets/css/components/dropdown.css">
<link rel="stylesheet" href="/assets/css/components/datepicker.css">
</head><body>
<?php require __DIR__ . '/../components/_icon_logos.php'; ?>
<main style="max-width:960px;margin:2rem auto;padding:1rem">
<a href="/management/people?tab=parents">Back to Parents</a>
<h1>Edit parent</h1>
<p><?= e($parent['parent_id']) ?> · <?= e($parent['personal_email']) ?></p>
<p>This form updates the shared parent profile. Login email, account access and student links are managed separately.</p>
<?php if ($error): ?><p role="alert" style="padding:1rem;background:#fbe9e7;color:#8b2419"><?= e($error) ?> <a href="/management/editParent?id=<?= urlencode($parent['parent_id']) ?>">Reload latest details</a></p><?php endif; ?>
<form method="post" action="/management/updateParent" class="c-form-card">
<?= $this->csrfField() ?>
<input type="hidden" name="parentCode" value="<?= e($parent['parent_id']) ?>">
<input type="hidden" name="version" value="<?= e($version) ?>">
<div class="c-form-body"><div class="c-form-grid">
<div class="c-form-field"><label class="c-form-field-label" for="edit-fullName">Full name *</label>
<input class="c-form-input" readonly id="edit-fullName" name="fullName" type="text" maxlength="150" value="<?= e($values['fullName']) ?>" required></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-firstName">First name *</label>
<input class="c-form-input" readonly id="edit-firstName" name="firstName" type="text" maxlength="75" value="<?= e($values['firstName']) ?>" required></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-lastName">Last name *</label>
<input class="c-form-input" readonly id="edit-lastName" name="lastName" type="text" maxlength="75" value="<?= e($values['lastName']) ?>" required></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-nic">NIC *</label>
<input class="c-form-input" id="edit-nic" name="nic" type="text" maxlength="30" value="<?= e($values['nic']) ?>" required></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-occupation">Occupation *</label>
<input class="c-form-input" id="edit-occupation" name="occupation" type="text" maxlength="100" value="<?= e($values['occupation']) ?>" required></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-mobile">Mobile phone *</label>
<input class="c-form-input" id="edit-mobile" name="mobile" type="tel" maxlength="30" value="<?= e($values['mobile']) ?>" required></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-passport">Passport</label>
<input class="c-form-input" id="edit-passport" name="passport" type="text" maxlength="50" value="<?= e($values['passport']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-employer">Employer</label>
<input class="c-form-input" id="edit-employer" name="employer" type="text" maxlength="150" value="<?= e($values['employer']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-homePhone">Home phone</label>
<input class="c-form-input" id="edit-homePhone" name="homePhone" type="tel" maxlength="30" value="<?= e($values['homePhone']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-officePhone">Office phone</label>
<input class="c-form-input" id="edit-officePhone" name="officePhone" type="tel" maxlength="30" value="<?= e($values['officePhone']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-officeAddress">Office address</label>
<input class="c-form-input" id="edit-officeAddress" name="officeAddress" type="text" maxlength="255" value="<?= e($values['officeAddress']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-emergencyName">Emergency contact name</label>
<input class="c-form-input" id="edit-emergencyName" name="emergencyName" type="text" maxlength="150" value="<?= e($values['emergencyName']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label" for="edit-emergencyContact">Emergency contact phone</label>
<input class="c-form-input" id="edit-emergencyContact" name="emergencyContact" type="tel" maxlength="30" value="<?= e($values['emergencyContact']) ?>" ></div>
<div class="c-form-field"><label class="c-form-field-label">Date of birth *</label>
<?php $datepickerId='edit-parent-dob'; $inputName='dateOfBirth'; $selectedValue=$values['dateOfBirth']; $placeholder='Select birth date'; $tone='terracotta'; $required=true; require __DIR__.'/../components/_datepicker.php'; ?>
</div>
<div class="c-form-field"><label class="c-form-field-label">Relationship *</label>
<p><?= e($parent['relationship']) ?></p>
</div>
<div class="c-form-field c-span-2"><label for="edit-homeAddress" class="c-form-field-label">Home address</label>
<textarea class="c-form-textarea" id="edit-homeAddress" name="homeAddress" maxlength="2000" rows="3"><?= e($values['homeAddress']) ?></textarea></div>
</div></div>
<footer class="c-form-footer"><a href="/management/people?tab=parents">Cancel</a><button type="submit" class="c-btn-accent c-tone-terracotta">Save changes</button></footer>
</form></main>
<script src="/assets/js/components/dropdown.js"></script><script src="/assets/js/components/datepicker.js"></script>
</body></html>
