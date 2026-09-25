<?php
// MVC/app/Views/components/_metric_card.php
// Reusable Metric Card Partial
// Expects: $card array OR individual variables ($color, $icon, $value, $label, $delay, $valueId)

$cardColor   = $card['color'] ?? $color ?? 'sand';
$cardIcon    = $card['icon'] ?? $icon ?? 'icon-graduationCap';
$cardValue   = $card['value'] ?? $value ?? '0';
$cardLabel   = $card['label'] ?? $label ?? '';
$cardDelay   = $card['delay'] ?? $delay ?? 0;
$cardValueId = $card['valueId'] ?? $valueId ?? '';

if (!str_starts_with($cardIcon, 'icon-')) {
    $cardIcon = 'icon-' . $cardIcon;
}
?>
<div class="c-metric-card c-metric-card--<?= htmlspecialchars($cardColor) ?>" style="animation-delay: <?= (int)$cardDelay ?>ms;">
  <div class="c-metric-card__top">
    <span class="c-metric-card__icon" aria-hidden="true">
      <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <use href="#<?= htmlspecialchars($cardIcon) ?>"/>
      </svg>
    </span>
  </div>
  <p class="c-metric-card__value c-font-display"<?= !empty($cardValueId) ? ' id="' . htmlspecialchars($cardValueId) . '"' : '' ?>>
    <?= htmlspecialchars($cardValue) ?>
  </p>
  <p class="c-metric-card__label"><?= htmlspecialchars($cardLabel) ?></p>
</div>
