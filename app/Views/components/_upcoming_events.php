<?php
// MVC/app/Views/components/_upcoming_events.php
// Reusable Upcoming Events Card Component
// Expects:
//   - $upcomingEvents: array of ['day' => ..., 'month' => ..., 'name' => ..., 'tag' => ..., 'tagColor' => ...]
//   - $eventsTitle: string (optional, defaults to 'Upcoming Events')

$title = $eventsTitle ?? 'Upcoming Events';
$eventsList = $upcomingEvents ?? [];
$eventCount = count($eventsList);
?>
<section class="c-panel">
  <div class="c-events-card__head">
    <h2 class="c-events-card__title" style="font-size: 0.9375rem;"><?= htmlspecialchars($title) ?></h2>
    <?php if ($eventCount > 0): ?>
      <span class="c-events-card__pill"><?= $eventCount ?> <?= $eventCount === 1 ? 'EVENT' : 'EVENTS' ?></span>
    <?php endif; ?>
  </div>
  <div class="c-events-list">
    <?php if (!empty($eventsList)): ?>
      <?php foreach ($eventsList as $ev): ?>
        <div class="c-event-row">
          <div class="c-event-row__date">
            <div class="c-event-row__day"><?= htmlspecialchars((string)($ev['day'] ?? '')) ?></div>
            <div class="c-event-row__month"><?= htmlspecialchars($ev['month'] ?? 'JUN') ?></div>
          </div>
          <div>
            <h3 class="c-event-row__name" style="font-size: 0.8125rem;"><?= htmlspecialchars($ev['name'] ?? '') ?></h3>
            <span class="c-event-row__tag c-event-row__tag--<?= htmlspecialchars($ev['tagColor'] ?? 'sand') ?>">
              <?= htmlspecialchars($ev['tag'] ?? 'Academic') ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="color: rgba(15, 65, 74, 0.5); font-size: 0.875rem; margin: 0;">No upcoming events scheduled.</p>
    <?php endif; ?>
  </div>
</section>
