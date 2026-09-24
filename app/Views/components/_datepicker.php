<?php
/**
 * =========================================================================
 * L'ÉCOLE — REUSABLE DATEPICKER ATOM COMPONENT
 * =========================================================================
 * Form widget with formatted trigger button and interactive calendar dropdown.
 *
 * Variables:
 *   - $datepickerId  (string, optional) Root element ID
 *   - $inputName     (string, required) Form POST input name
 *   - $selectedValue (string, optional) Current ISO date 'YYYY-MM-DD'
 *   - $placeholder   (string, optional) Placeholder text (default: 'Select date')
 *   - $tone          (string, optional) 'sky' | 'sunshine' | 'terracotta' | 'maroon' (default: 'sky')
 *   - $disabled      (bool, optional)   Whether disabled
 *   - $required      (bool, optional)   Whether required
 *   - $ariaLabel     (string, optional) Accessibility label
 * =========================================================================
 */

$dpId        = $datepickerId ?? ('j-dp-' . uniqid());
$name        = $inputName ?? 'date';
$val         = $selectedValue ?? '';
$placeholder = $placeholder ?? 'Select date';
$toneColor   = $tone ?? 'sky';
$isDisabled  = !empty($disabled);
$isRequired  = !empty($required);
$label       = $ariaLabel ?? $placeholder;
?>

<div class="c-datepicker" id="<?= htmlspecialchars($dpId) ?>" data-tone="<?= htmlspecialchars($toneColor) ?>">
  <input 
    type="hidden" 
    class="j-dp-input" 
    name="<?= htmlspecialchars($name) ?>" 
    value="<?= htmlspecialchars($val) ?>" 
    <?= $isRequired ? 'required' : '' ?>
  />

  <button 
    type="button" 
    class="c-datepicker-trigger c-dp-<?= htmlspecialchars($toneColor) ?> j-dp-trigger" 
    data-placeholder="<?= htmlspecialchars($placeholder) ?>"
    aria-label="<?= htmlspecialchars($label) ?>"
    <?= $isDisabled ? 'disabled' : '' ?>
  >
    <span class="j-dp-label <?= empty($val) ? 'c-dp-placeholder' : '' ?>">
      <?= empty($val) ? htmlspecialchars($placeholder) : htmlspecialchars(date('d M Y', strtotime($val))) ?>
    </span>
    <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <use href="#icon-calendar"/>
    </svg>
  </button>
</div>
