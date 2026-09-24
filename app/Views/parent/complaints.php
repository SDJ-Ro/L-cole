<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inquiries & Complaints — L'École Parent Portal</title>
  <link rel="stylesheet" href="/assets/css/global.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/page-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/sidebar.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/dropdown.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/complaints.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/components/feedback-banner.css?v=<?= time() ?>" />
</head>
<body>

<?php require_once __DIR__ . '/../components/_icon_logos.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <!-- Shared Sidebar Component -->
  <?php require_once __DIR__ . '/../components/_sidebar.php'; ?>

  <!-- Main Content Area -->
  <main class="c-main" id="j-main">
    <!-- Universal Page Header Component -->
    <?php
    $title    = 'Inquiries & Complaints';
    $subtitle = 'Submit, track, and review communications with school administration regarding your child.';
    $action   = '
      <button type="button" class="c-btn c-btn--maroon" id="j-new-inquiry-btn">
        <span>+ New Inquiry</span>
      </button>
    ';
    require __DIR__ . '/../components/_page_header.php';
    ?>

    <div class="c-complaints-page">
      <!-- Search & Filter Toolbar (Notice Board standard layout) -->
      <section class="c-filter-bar c-filter-bar--complaints" aria-label="Filter inquiries">
        <label class="c-search-field">
          <svg class="c-icon c-search-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-search"/>
          </svg>
          <input 
            type="text" 
            class="c-search-field__input j-search-input" 
            id="j-complaints-search" 
            placeholder="Search by subject, category, or keywords..." 
            autocomplete="off" 
          />
        </label>

        <div class="c-filter-bar__selects">
          <!-- Category Filter -->
          <?php
          $dropdownId     = 'filter-category';
          $dropdownLabel  = 'Category';
          $placeholder    = 'All Categories';
          $selectedValue  = 'All';
          $options        = $categories ?? ['All', 'Academic', 'Facilities', 'Transport', 'Administration', 'Extracurricular'];
          $name           = 'filter-category-val';
          require __DIR__ . '/../components/_dropdown.php';
          ?>

          <!-- Status Filter -->
          <?php
          $dropdownId     = 'filter-status';
          $dropdownLabel  = 'Status';
          $placeholder    = 'All Statuses';
          $selectedValue  = 'All';
          $options        = ['All', 'In Progress', 'Resolved'];
          $name           = 'filter-status-val';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>
      </section>

      <!-- Complaints List Grid -->
      <div class="c-complaints-list" id="j-complaints-list">
        <?php if (!empty($complaints)): ?>
          <?php foreach ($complaints as $complaint): ?>
            <?php
            $canResolve     = false;
            $showParentInfo = false;
            require __DIR__ . '/../components/_complaint_card.php';
            ?>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty State Notice -->
        <div class="c-complaints-empty" id="j-complaints-empty" style="<?= empty($complaints) ? 'display: flex;' : 'display: none;' ?>">
          <svg class="c-complaints-empty__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-complaints"/>
          </svg>
          <h4 class="c-complaints-empty__title">No inquiries match your filter</h4>
          <p class="c-complaints-empty__desc">Try adjusting your search terms or filter selections.</p>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal: New Inquiry (Exact Notice Board & Calendar Modal Standard) -->
<div class="c-modal-layer" id="j-modal-new-inquiry" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close new inquiry"></button>
  <section class="c-modal" role="dialog" aria-modal="true" aria-labelledby="j-inquiry-modal-title">
    <header class="c-modal__header">
      <div class="c-modal__heading-group">
        <div class="c-modal__icon-badge c-modal__icon-badge--sky" aria-hidden="true">
          <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-complaints"/>
          </svg>
        </div>
        <div>
          <p class="c-modal__eyebrow">Communication</p>
          <h2 class="c-modal__title" id="j-inquiry-modal-title">New Inquiry or Complaint</h2>
          <p class="c-modal__description">Direct communication with school departments and administration.</p>
        </div>
      </div>
      <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close modal">
        <svg class="c-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <use href="#icon-x"/>
        </svg>
      </button>
    </header>

    <form class="c-inquiry-modal-form" id="j-new-inquiry-form" novalidate>
      <div>
        <label class="c-field-label" for="j-inquiry-subject">Subject</label>
        <input 
          class="c-field-input" 
          id="j-inquiry-subject" 
          name="subject" 
          placeholder="e.g. Clarification on Chemistry Revision Syllabus" 
          required 
          type="text" 
        />
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <span class="c-field-label">Category</span>
          <?php
          $dropdownId     = 'inquiry-form-category';
          $dropdownLabel  = 'Category';
          $placeholder    = 'Select Category';
          $selectedValue  = 'Academic';
          $options        = ['Academic', 'Facilities', 'Transport', 'Administration', 'Extracurricular'];
          $name           = 'category';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>

        <div>
          <span class="c-field-label">Associated Student</span>
          <?php
          $dropdownId     = 'inquiry-form-student';
          $dropdownLabel  = 'Student';
          $placeholder    = 'Select Student';
          $childrenOptions = array_map(function($ch) {
              return ['value' => $ch['name'], 'label' => $ch['name'] . ' (' . ($ch['grade'] ?? '') . ')'];
          }, $children ?? [['name' => 'Nethmi Perera', 'grade' => 'Grade 10-A']]);
          $options        = $childrenOptions;
          $selectedValue  = $childrenOptions[0]['value'] ?? 'Nethmi Perera';
          $name           = 'student';
          require __DIR__ . '/../components/_dropdown.php';
          ?>
        </div>
      </div>

      <div>
        <label class="c-field-label" for="j-inquiry-message">Message</label>
        <textarea 
          class="c-field-input c-field-input--textarea" 
          id="j-inquiry-message" 
          name="message" 
          rows="5" 
          placeholder="Describe your inquiry or concern in detail..." 
          required
        ></textarea>
      </div>

      <footer class="c-inquiry-modal-form__footer">
        <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
        <button type="submit" class="c-btn c-btn--solid">
          <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <use href="#icon-send"/>
          </svg>
          <span>Submit Inquiry</span>
        </button>
      </footer>
    </form>
  </section>
</div>

<!-- Scripts -->
<script src="/assets/js/components/dialogs-and-popups.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/feedback-banners.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/sidebar.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/dropdown.js?v=<?= time() ?>"></script>
<script src="/assets/js/components/complaints.js?v=<?= time() ?>"></script>

</body>
</html>
