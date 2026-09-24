<?php
/**
 * =========================================================================
 * L'ÉCOLE — DROPDOWN COMPONENT
 * =========================================================================
 * Exact 1:1 port from Admin Notice custom dropdown.
 * 
 * Expects:
 *   - $dropdownId     : string (required) Unique element ID
 *   - $options        : array of strings or associative arrays:
 *                       ['Academic', 'Administrative', 'General'] OR
 *                       [['value' => 'All', 'label' => 'All Users'], ...]
 *   - $selectedValue  : string (optional) Currently active value
 *   - $placeholder    : string (optional, defaults to 'Select option')
 *   - $dropdownLabel  : string (optional, for aria-label)
 *   - $name           : string (optional, hidden input name for forms)
 * =========================================================================
 */

$dId      = $dropdownId ?? ($selectId ?? ('j-dropdown-' . uniqid()));
$dLabel   = $dropdownLabel ?? ($selectLabel ?? 'Dropdown');
$dHolder  = $placeholder ?? 'Select option';
$dList    = $options ?? [];
$dVal     = $selectedValue ?? null;
$dName    = $name ?? '';
$dClass   = $dropdownClass ?? ($extraClass ?? '');
$isMulti  = !empty($isMultiSelect);
$prefix   = $labelPrefix ?? '';

// Selected values for multi-select
$selectedValues = [];
if ($isMulti) {
    if (is_array($dVal)) {
        $selectedValues = $dVal;
    } elseif ($dVal !== null && $dVal !== '') {
        $selectedValues = [$dVal];
    }
}

// Format options uniformly and deduplicate by value
$formattedOptions = [];
$seenValues = [];
foreach ($dList as $opt) {
    if (is_array($opt)) {
        $val = $opt['value'] ?? ($opt['label'] ?? '');
        $lbl = $opt['label'] ?? $val;
    } else {
        $val = (string)$opt;
        $lbl = (string)$opt;
    }
    if (!isset($seenValues[$val])) {
        $seenValues[$val] = true;
        $formattedOptions[] = ['value' => $val, 'label' => $lbl];
    }
}

// Find current display label (for single-select)
$displayLabel = $dHolder;
$hasSelectedValue = false;
if (!$isMulti && $dVal !== null && $dVal !== '') {
    foreach ($formattedOptions as $opt) {
        if ((string)$opt['value'] === (string)$dVal) {
            $displayLabel = $opt['label'];
            $hasSelectedValue = true;
            break;
        }
    }
}
?>

<div class="c-select c-dropdown <?= $isMulti ? 'c-dropdown--multi' : '' ?> <?= htmlspecialchars($dClass) ?>" 
     id="<?= htmlspecialchars($dId) ?>" 
     <?= $isMulti ? 'data-multi="true"' : '' ?>
     <?= !empty($dName) ? 'data-name="' . htmlspecialchars($dName) . '"' : '' ?>
     aria-label="<?= htmlspecialchars($dLabel) ?>">

  <?php if ($isMulti): ?>
    <div class="c-dropdown__hidden-inputs j-dropdown-hidden-inputs">
      <?php foreach ($selectedValues as $sv): ?>
        <input type="hidden" name="<?= htmlspecialchars($dName) ?>" value="<?= htmlspecialchars((string)$sv) ?>" />
      <?php endforeach; ?>
    </div>
  <?php elseif (!empty($dName)): ?>
    <input type="hidden" name="<?= htmlspecialchars($dName) ?>" value="<?= htmlspecialchars((string)($dVal ?? '')) ?>" />
  <?php endif; ?>

  <?php if ($isMulti): ?>
    <div role="button" tabindex="0" class="c-select__trigger c-dropdown__trigger <?= !empty($selectedValues) ? 'has-value' : 'is-placeholder' ?>" aria-haspopup="listbox" aria-expanded="false">
      <div class="c-dropdown__chips j-dropdown-chips j-tag-chips">
        <?php foreach ($selectedValues as $sv): ?>
          <span class="c-chip">
            <span><?= htmlspecialchars((string)$sv) ?></span>
            <span role="button" tabindex="0" class="c-chip__remove j-chip-remove" data-val="<?= htmlspecialchars((string)$sv) ?>" aria-label="Remove <?= htmlspecialchars((string)$sv) ?>">
              <svg class="c-icon" width="10" height="10" aria-hidden="true"><use href="#icon-close"/></svg>
            </span>
          </span>
        <?php endforeach; ?>
      </div>
      <span class="c-dropdown__placeholder j-dropdown-placeholder" style="<?= !empty($selectedValues) ? 'display:none;' : '' ?>"><?= htmlspecialchars($dHolder) ?></span>
      <svg class="c-icon c-select__chevron c-dropdown__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
    </div>
  <?php else: ?>
    <button type="button" class="c-select__trigger c-dropdown__trigger <?= $hasSelectedValue ? 'has-value' : 'is-placeholder' ?>" aria-haspopup="listbox" aria-expanded="false">
      <span class="c-select-trigger-text" style="display:inline-flex;align-items:center;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
        <?php if (!empty($prefix)): ?>
          <span class="c-filter-trigger-label" style="color:rgba(15,65,74,0.5);font-weight:600;margin-right:0.25rem;"><?= htmlspecialchars($prefix) ?>:</span>
        <?php endif; ?>
        <span class="c-select__value c-dropdown__value j-select-value <?= !$hasSelectedValue ? 'c-dropdown__placeholder' : '' ?>"><?= htmlspecialchars($displayLabel) ?></span>
      </span>
      <svg class="c-icon c-select__chevron c-dropdown__chevron" width="16" height="16" aria-hidden="true"><use href="#icon-chevronDown"/></svg>
    </button>
  <?php endif; ?>

  <div class="c-select__menu c-dropdown__menu" role="listbox" aria-label="<?= htmlspecialchars($dLabel) ?>">
    <?php foreach ($formattedOptions as $opt): 
      $isSelected = $isMulti 
          ? in_array($opt['value'], $selectedValues, true) 
          : ($dVal !== null && $opt['value'] === $dVal);
    ?>
      <div class="c-select__option c-dropdown__option <?= $isSelected ? 'c-is-selected' : '' ?>" 
           data-value="<?= htmlspecialchars($opt['value']) ?>" 
           role="option"
           <?= $isSelected ? 'aria-selected="true"' : '' ?>>
        <span><?= htmlspecialchars($opt['label']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php unset($dropdownId, $selectId, $dropdownLabel, $selectLabel, $placeholder, $options, $selectedValue, $name, $dropdownClass, $extraClass, $isMultiSelect, $labelPrefix); ?>
