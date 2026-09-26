<section class="c-form-section-head">
  <h2 class="c-form-section-title">Parent or guardian</h2>
  <p class="c-form-section-desc">Every student needs one guardian. Select an existing parent for a sibling, or enter a new parent below.</p>
</section>
<input type="hidden" name="admissionKey" value="<?= bin2hex(random_bytes(16)) ?>">
<?php

(function (array $parents) {
    $isMultiSelect = false;
    $labelPrefix = '';
    $dropdownClass = 'c-select-terracotta';
?>
<div class="c-form-field" style="margin:1rem 0">
  <label class="c-form-field-label">Guardian choice</label>
  <?php
    $dropdownId = 'guardian-mode';
    $name = 'guardianMode';
    $dropdownLabel = 'Guardian choice';
    $placeholder = 'Choose a guardian option';
    $selectedValue = 'existing';
    $options = [
        ['value'=>'existing', 'label'=>'Use an existing parent'],
        ['value'=>'new', 'label'=>'Create a new parent with this student']
    ];
    require __DIR__ . '/_dropdown.php';
  ?>
</div>
<fieldset id="existing-guardian-fields" style="border:0;padding:0;margin:1rem 0;min-width:0">
  <input type="hidden" id="existing-parent-id" name="existingParent" value="">
  <div id="selected-parent-card" hidden class="c-parent-selection" aria-live="polite">
    <strong id="selected-parent-name"></strong>
    <span id="selected-parent-code"></span>
    <span id="selected-parent-email"></span>
  </div>
  <button type="button" id="choose-existing-parent" class="c-btn-accent c-tone-terracotta">Choose Existing Parent</button>
</fieldset>
<fieldset id="new-guardian-fields" class="c-form-grid" hidden disabled style="display:none;border:0;padding:1rem 0">
  <div class="c-form-field">
    <label class="c-form-field-label">Relationship *</label>
    <?php
      $dropdownId = 'guardian-relationship';
      $name = 'guardian[relationship]';
      $dropdownLabel = 'Relationship to student';
      $placeholder = 'Select relationship';
      $selectedValue = 'Father';
      $options = ['Father','Mother','Guardian'];
      require __DIR__ . '/_dropdown.php';
    ?>
  </div>
<?php })( $parents ?? [] ); ?>
<div class="c-form-field"><label for="guardian-fullName">Full name *</label><input id="guardian-fullName" class="c-form-input" name="guardian[fullName]" type="text" maxlength="150" required></div>
<div class="c-form-field"><label for="guardian-firstName">First name *</label><input id="guardian-firstName" class="c-form-input" name="guardian[firstName]" type="text" maxlength="75" required></div>
<div class="c-form-field"><label for="guardian-lastName">Last name *</label><input id="guardian-lastName" class="c-form-input" name="guardian[lastName]" type="text" maxlength="75" required></div>
<div class="c-form-field"><label for="guardian-nic">NIC *</label><input id="guardian-nic" class="c-form-input" name="guardian[nic]" type="text" maxlength="30" required></div>
<div class="c-form-field"><label for="guardian-dateOfBirth">Date of birth *</label><input id="guardian-dateOfBirth" class="c-form-input" name="guardian[dateOfBirth]" type="date" maxlength="10" required></div>
<div class="c-form-field"><label for="guardian-occupation">Occupation *</label><input id="guardian-occupation" class="c-form-input" name="guardian[occupation]" type="text" maxlength="100" required></div>
<div class="c-form-field"><label for="guardian-mobile">Mobile phone *</label><input id="guardian-mobile" class="c-form-input" name="guardian[mobile]" type="tel" maxlength="30" required></div>
<div class="c-form-field"><label for="guardian-email">Email for account access *</label><input id="guardian-email" class="c-form-input" name="guardian[email]" type="email" maxlength="191" required></div>
<div class="c-form-field"><label for="guardian-passport">Passport</label><input id="guardian-passport" class="c-form-input" name="guardian[passport]" type="text" maxlength="50" ></div>
<div class="c-form-field"><label for="guardian-employer">Employer</label><input id="guardian-employer" class="c-form-input" name="guardian[employer]" type="text" maxlength="150" ></div>
<div class="c-form-field"><label for="guardian-homePhone">Home phone</label><input id="guardian-homePhone" class="c-form-input" name="guardian[homePhone]" type="tel" maxlength="30" ></div>
<div class="c-form-field"><label for="guardian-officePhone">Office phone</label><input id="guardian-officePhone" class="c-form-input" name="guardian[officePhone]" type="tel" maxlength="30" ></div>
<div class="c-form-field"><label for="guardian-officeAddress">Office address</label><input id="guardian-officeAddress" class="c-form-input" name="guardian[officeAddress]" type="text" maxlength="255" ></div>
<div class="c-form-field"><label for="guardian-emergencyName">Emergency contact name</label><input id="guardian-emergencyName" class="c-form-input" name="guardian[emergencyName]" type="text" maxlength="150" ></div>
<div class="c-form-field"><label for="guardian-emergencyContact">Emergency contact phone</label><input id="guardian-emergencyContact" class="c-form-input" name="guardian[emergencyContact]" type="tel" maxlength="30" ></div>
</fieldset>
