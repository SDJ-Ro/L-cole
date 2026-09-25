<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR CARD DETAIL HERO HEADER COMPONENT
 * =========================================================================
 * Shared header component displayed when viewing inside an extracurricular card.
 * 
 * Features:
 *   - Back navigation button returning to the activities overview grid
 *   - Atmospheric dark gradient hero banner with cover photo & tint overlay
 *   - Created Date badge with calendar icon
 *   - Role-conditional "Edit program" action button
 *   - Type (Sport/Club) and Category breadcrumb tags
 *   - Club Title and full Description
 * 
 * Expects:
 *   - $club (array) Activity details
 *   - $canEdit (bool, optional) Whether user can edit the program details (default: true)
 * =========================================================================
 */

$c          = $club ?? [];
$clubId     = (int)($c['id'] ?? 0);
$clubName   = $c['name'] ?? 'Extracurricular Program';
$clubType   = $c['type'] ?? 'Sports';
$category   = $c['category'] ?? 'General';
$desc       = $c['desc'] ?? '';
$createdAt  = $c['createdAt'] ?? '15 Jan 2024';
$image      = $c['image'] ?? null;
$canEdit      = $canEdit ?? true;
$showBackLink = $showBackLink ?? true;
?>

<!-- Back Link to Main Extracurricular Grid (Optional for views without a parent grid) -->
<?php if ($showBackLink): ?>
  <button type="button" class="c-back-link-btn j-back-to-activities-grid" id="j-back-to-activities-grid">
    <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <use href="#icon-chevronLeft"/>
    </svg>
    <span>Back</span>
  </button>
<?php endif; ?>

<!-- Hero Banner Header -->
<header class="c-detail-hero" id="j-club-detail-hero" data-club-id="<?= $clubId ?>">
  <div class="c-detail-hero__media">
    <?php if (!empty($image)): ?>
      <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($clubName) ?>" id="j-detail-hero-img" />
    <?php endif; ?>
    <div class="c-detail-hero__tint"></div>
  </div>

  <!-- Created Date Badge -->
  <span class="c-detail-hero__badge" id="j-detail-hero-badge">
    <svg class="c-icon" width="14" height="14" aria-hidden="true"><use href="#icon-calendar"/></svg>
    <span id="j-detail-hero-created-text">Created: <?= htmlspecialchars($createdAt) ?></span>
  </span>

  <!-- Edit Program Button (Role-conditioned) -->
  <?php if ($canEdit): ?>
    <button type="button" class="c-detail-hero__edit-btn j-open-program-edit" id="j-open-program-edit" data-club-id="<?= $clubId ?>">
      <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <use href="#icon-edit"/>
      </svg>
      <span>Edit program</span>
    </button>
  <?php endif; ?>

  <!-- Content & Metadata -->
  <div class="c-detail-hero__content">
    <div class="c-detail-hero__tags">
      <span class="c-detail-hero__type" id="j-detail-hero-type"><?= htmlspecialchars($clubType) ?></span>
      <span class="c-detail-hero__sep" aria-hidden="true">•</span>
      <span class="c-detail-hero__category" id="j-detail-hero-category"><?= htmlspecialchars($category) ?></span>
    </div>
    <h1 class="c-detail-hero__name c-font-display" id="j-detail-hero-name"><?= htmlspecialchars($clubName) ?></h1>
    <p class="c-detail-hero__desc" id="j-detail-hero-desc"><?= htmlspecialchars($desc) ?></p>
  </div>
</header>

<?php if ($canEdit): ?>
<!-- =========================================================================
     MODAL — EDIT PROGRAMME
     Reuses universal _form_card.php component with 'sand' header theme.
     ========================================================================= -->
<?php
ob_start();
?>
  <div class="c-form-row c-form-row--two-col">
    <div class="c-field-span-2" id="j-pi-name-field">
      <label class="c-field-label" for="j-pi-name">Programme Name <span class="c-field-required" style="color:var(--terracotta,#AF5031);">*</span></label>
      <input type="text" class="c-field-input" id="j-pi-name" name="name" value="<?= htmlspecialchars($clubName) ?>" placeholder="e.g. Senior Badminton Squad" required />
      <p class="c-field-error" id="j-pi-name-error" style="display:none; color:var(--terracotta,#AF5031); font-size:0.75rem; margin-top:0.25rem;"></p>
    </div>
  </div>

  <div class="c-form-row c-form-row--two-col" style="margin-top: 1.25rem;">
    <!-- Programme Type Dropdown -->
    <div>
      <label class="c-field-label">Programme Type <span class="c-field-required" style="color:var(--terracotta,#AF5031);">*</span></label>
      <?php
      $dropdownId    = 'j-pi-type-dropdown';
      $dropdownLabel = 'Type';
      $placeholder   = 'Select Type';
      $options       = ['Sports', 'Clubs and Societies'];
      $selectedValue = $clubType ?: 'Sports';
      $name          = 'type';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <!-- Category Dropdown -->
    <div>
      <label class="c-field-label">Category <span class="c-field-required" style="color:var(--terracotta,#AF5031);">*</span></label>
      <?php
      $dropdownId    = 'j-pi-category-dropdown';
      $dropdownLabel = 'Category';
      $placeholder   = 'Select category';
      $options       = ['Athletics', 'Creative Arts', 'Academic Club', 'STEM Club', 'Performing Arts', 'Team Sport', 'Aquatics', 'General'];
      $selectedValue = $category ?: 'Team Sport';
      $name          = 'category';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
  </div>

  <div class="c-form-row c-form-row--two-col" style="margin-top: 1.25rem;">
    <!-- Target Age Groups / Grades -->
    <div>
      <label class="c-field-label" for="j-pi-age-groups">Eligible Grades / Age Groups</label>
      <input type="text" class="c-field-input" id="j-pi-age-groups" name="age_groups" placeholder="e.g. Under 15, Under 19, Grade 9 – 12" />
    </div>

    <!-- Cover Photo Uploader -->
    <div>
      <label class="c-field-label">Cover Photo</label>
      <label class="c-cover-upload c-cover-upload--sand" for="j-pi-image-input" id="j-pi-image-preview" style="height: 105px; border-radius: var(--radius-lg, 0.75rem); border: 2px dashed rgba(15, 65, 74, 0.2); display: flex; align-items: center; justify-content: center; overflow: hidden; background: rgba(228, 203, 169, 0.12); cursor: pointer; transition: border-color 150ms ease;">
        <?php if (!empty($image)): ?>
          <img src="<?= htmlspecialchars($image) ?>" alt="Cover preview" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; if(this.parentElement.querySelector('.c-cover-upload__placeholder')) this.parentElement.querySelector('.c-cover-upload__placeholder').style.display='flex';" />
          <span class="c-cover-upload__placeholder" style="display: none; flex-direction: column; align-items: center; gap: 0.35rem; color: rgba(15, 65, 74, 0.6); font-size: 0.8125rem; font-weight: 600;">
            <svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <use href="#icon-imagePlus"/>
            </svg>
            Upload cover photo
          </span>
        <?php else: ?>
          <span class="c-cover-upload__placeholder" style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem; color: rgba(15, 65, 74, 0.6); font-size: 0.8125rem; font-weight: 600;">
            <svg class="c-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <use href="#icon-imagePlus"/>
            </svg>
            Upload cover photo
          </span>
        <?php endif; ?>
      </label>
      <input class="c-visually-hidden" id="j-pi-image-input" type="file" accept="image/*" />
    </div>
  </div>

  <!-- Coach / Instructor Section -->
  <section class="c-form-section" style="margin-top: 1.25rem;">
    <div class="c-form-section__head">
      <div>
        <h3 class="c-form-section__title">Coach / Instructor</h3>
        <p class="c-form-section__hint">Maintain the coordinator or coach details displayed on this programme.</p>
      </div>
    </div>
    <div class="c-form-row c-form-row--two-col" style="margin-top: 0.75rem;">
      <div>
        <label class="c-field-label" for="j-pi-coach-name">Name</label>
        <input type="text" class="c-field-input" id="j-pi-coach-name" name="coach_name" placeholder="e.g. Coach Dinesh" />
      </div>
      <div>
        <label class="c-field-label" for="j-pi-coach-specialty">Specialty</label>
        <input type="text" class="c-field-input" id="j-pi-coach-specialty" name="coach_specialty" placeholder="e.g. UEFA B Licensed" />
      </div>
    </div>
    <div class="c-form-row c-form-row--two-col" style="margin-top: 0.75rem;">
      <div>
        <label class="c-field-label" for="j-pi-coach-email">Email</label>
        <input type="email" class="c-field-input" id="j-pi-coach-email" name="coach_email" placeholder="e.g. coach@lecole.edu" />
      </div>
      <div>
        <label class="c-field-label" for="j-pi-coach-phone">Phone</label>
        <input type="tel" class="c-field-input" id="j-pi-coach-phone" name="coach_phone" placeholder="e.g. +94 77 123 4567" />
      </div>
    </div>
  </section>

  <!-- Description -->
  <div style="margin-top: 1.25rem;">
    <label class="c-field-label" for="j-pi-description">Description</label>
    <textarea class="c-field-input c-field-input--textarea" id="j-pi-description" name="description" rows="3" placeholder="Describe the program objectives, meeting highlights, and student activities..."><?= htmlspecialchars($desc) ?></textarea>
  </div>
<?php
$formBodySlot = ob_get_clean();

ob_start();
?>
  <div style="display: flex; justify-content: flex-end; gap: 0.75rem; width: 100%;">
    <button type="button" class="c-btn c-btn--ghost j-modal-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--solid c-btn--sky">Save programme</button>
  </div>
<?php
$formFooterSlot = ob_get_clean();

ob_start();
?>
  <button type="button" class="c-modal__close-btn j-modal-close" aria-label="Close">
    <svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-close"/></svg>
  </button>
<?php
$headerRightSlot = ob_get_clean();

$formId          = 'j-program-form';
$headerTheme     = 'sand';
$headerIcon      = 'icon-edit';
$headerEyebrow   = 'Extracurricular Programme';
$formTitle       = 'Edit Programme';
$formSubtitle    = 'Update the programme information, category, and cover image.';
$isModal         = true;
$cardClass       = 'c-form-card--modal';
?>

<div class="c-modal-layer" id="j-program-edit-modal" role="presentation">
  <button type="button" class="c-modal-backdrop j-modal-backdrop" aria-label="Close edit modal"></button>
  <section class="c-modal c-modal--program-edit" role="dialog" aria-modal="true" aria-labelledby="j-program-edit-title" style="width: min(44rem, 94vw); max-width: 44rem; max-height: 90vh; padding: 0; overflow: visible; border: none; background: transparent; display: flex; flex-direction: column;">
    <?php require __DIR__ . '/_form_card.php'; ?>
  </section>
</div>
<?php endif; ?>

