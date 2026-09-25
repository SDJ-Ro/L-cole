<?php
/**
 * =========================================================================
 * L'ÉCOLE — EXTRACURRICULAR CARD ACHIEVEMENT DETAIL & EDITOR PAGE
 * =========================================================================
 * Unified full-page view and editor form for extracurricular achievements.
 * Ported directly from the proven prototype (renderAchievementView & renderAddAchievementView).
 * 
 * Features:
 *   - Back navigation to club detail view
 *   - Live Inline Edit Mode & View Mode toggle
 *   - Place, Level, and Year status chips
 *   - Title, Date, and Venue headers
 *   - Summary description and narrative bio
 *   - 2-Column Event Facts grid:
 *       * Tournament / Event, Date, Venue, Scope / Level
 *       * Segmented Age Group tabs (Under 13, Under 15, Under 17, Under 19, Open)
 *       * Representing tabs (Club Teams + Individual) with Student dropdown
 *       * Organised by, Position / Result, Award / Medal
 *   - Participants roster panel with live count and student badges
 *   - Event Photo Gallery with upload & per-photo delete controls
 *   - Danger Zone panel: Permanently delete achievement card (Admin & Teacher only)
 * 
 * Expects:
 *   - $club (array, optional) Parent club data
 *   - $canEdit (bool, optional) Whether user has editing/deletion privileges (default: true)
 * =========================================================================
 */

$c          = $club ?? [];
$clubName   = $c['name'] ?? 'Extracurricular Club';
$canEdit    = $canEdit ?? true;
$canDelete  = $canDelete ?? true;
?>

<div class="c-achievement-page" id="j-achievement-page-view" style="display: none;" data-can-edit="<?= $canEdit ? 'true' : 'false' ?>" data-can-delete="<?= $canDelete ? 'true' : 'false' ?>">

  <!-- Navigation & Action Header -->
  <div class="j-ex-95">
    <button type="button" class="c-back-link-btn j-ex-39" id="j-back-to-club-detail">
      <svg class="c-icon" width="16" height="16" aria-hidden="true"><use href="#icon-arrowLeft"/></svg>
      <span>Back</span>
    </button>

    <?php if ($canEdit): ?>
      <div class="j-ach-actions-slot">
        <!-- View Mode Action -->
        <button type="button" class="c-btn c-btn--sky c-btn--sm j-ex-35" id="j-open-edit-achievement">
          <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <use href="#icon-edit"/>
          </svg>
          <span>Edit Achievement</span>
        </button>

        <!-- Edit Mode Actions (Hidden in View Mode) -->
        <div class="j-ex-96" id="j-ach-edit-controls" style="display: none;">
          <button type="button" class="c-btn c-btn--ghost c-btn--sm" id="j-edit-ach-cancel">Cancel</button>
          <button type="button" class="c-btn c-btn--solid c-btn--sm j-ex-97" id="j-edit-ach-save">
            <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <use href="#icon-check"/>
            </svg>
            <span id="j-edit-ach-save-text">Save Changes</span>
          </button>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Main Achievement Article -->
  <article class="c-achv-article" id="j-achv-article">
    
    <!-- Top Hero Banner with Chips & Meta -->
    <header class="c-achv-hero">
      <div class="c-achv-hero__chips">
        <span class="c-chip c-chip--sunshine" id="j-ach-edit-place">Runners Up</span>
        <span class="c-chip c-chip--sky" id="j-ach-edit-level">Provincial</span>
        <span class="c-chip c-chip--sand" id="j-ach-edit-year">2024</span>
      </div>

      <h1 class="c-achv-hero__title c-font-display" id="j-ach-edit-title">Achievement Title</h1>

      <p class="c-achv-hero__meta">
        <svg class="c-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <use href="#icon-calendarDays"/>
        </svg>
        <span id="j-ach-edit-header-date">Nov 18, 2024</span>
        <span aria-hidden="true" id="j-ach-venue-sep">·</span>
        <svg class="c-icon" width="16" height="16" id="j-ach-venue-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <use href="#icon-mapPin"/>
        </svg>
        <span id="j-ach-edit-header-venue">Colombo</span>
      </p>
    </header>

    <!-- Cover Image Upload (Only shown in Edit / Add mode for authorized roles) -->
    <?php if ($canEdit): ?>
      <!-- Cover Image Upload Component (Only shown in Edit mode) -->
      <div id="j-ach-cover-section" style="display: none; padding: 0 1.75rem;">
        <?php
        $pickerId = 'j-ach-cover';
        $pickerLabel = 'Cover photo';
        $pickerHint = 'Select an image file to display on the achievement card.';
        $buttonText = 'Choose Cover Photo';
        require __DIR__ . '/_image_picker.php';
        ?>
      </div>
    <?php endif; ?>

    <!-- Body Information -->
    <div class="c-achv-body">
      
      <!-- Summary & Bio -->
      <section class="c-achv-summary">
        <p class="j-ex-114" id="j-ach-bio-label" style="display: none;">Achievement Summary / Bio</p>
        <p id="j-ach-edit-bio">Achievement performance bio description...</p>
        <p class="j-ex-116" id="j-ach-details-label" style="display: none;">Additional Details</p>
        <p id="j-ach-edit-details" style="display: none;">Additional tournament records...</p>
      </section>

      <!-- Event Information 2-Column Facts Grid -->
      <section>
        <div class="c-section-heading">
          <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <use href="#icon-trophy"/>
          </svg>
          <h2>Event information</h2>
        </div>

        <div class="c-fact-grid">
          <ul>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Tournament / event</p>
                <span class="c-fact-item__value" id="j-fact-tournament">Tournament Name</span>
              </div>
            </li>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Date</p>
                <span class="c-fact-item__value" id="j-fact-date">Nov 18, 2024</span>
              </div>
            </li>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Venue</p>
                <span class="c-fact-item__value" id="j-fact-venue">Main Grounds, Colombo</span>
              </div>
            </li>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Scope / level</p>
                <span class="c-fact-item__value" id="j-fact-scope">Provincial</span>
              </div>
            </li>

            <!-- Age Group Row -->
            <li class="c-fact-item j-ex-98" id="j-ach-age-row">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div class="j-ex-99">
                <p class="c-fact-item__label j-ex-100">Age Group</p>
                <!-- View Mode Display -->
                <span class="c-fact-item__value" id="j-fact-ageGroup">Under 19</span>
                <!-- Edit Mode Dropdown -->
                <div class="c-select" id="j-ach-age-select" style="display: none; width: 100%; max-width: 280px; margin-top: 0.35rem;">
                  <button type="button" class="c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="c-select__value j-select-value">Under 19</span>
                    <svg class="c-icon c-select__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <use href="#icon-chevronDown"/>
                    </svg>
                  </button>
                  <div class="c-select__menu" role="listbox">
                    <div class="c-select__option" data-value="Under 13">Under 13</div>
                    <div class="c-select__option" data-value="Under 15">Under 15</div>
                    <div class="c-select__option" data-value="Under 17">Under 17</div>
                    <div class="c-select__option" data-value="Under 19">Under 19</div>
                    <div class="c-select__option" data-value="Open">Open</div>
                  </div>
                </div>
              </div>
            </li>

            <!-- Representing Row -->
            <li class="c-fact-item j-ex-98" id="j-ach-team-row">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div class="j-ex-99">
                <p class="c-fact-item__label j-ex-100">Representing (Team / Individual)</p>
                <!-- View Mode Display -->
                <span class="c-fact-item__value" id="j-fact-representing">Senior Team</span>
                <!-- Edit Mode Dropdown -->
                <div class="c-select" id="j-ach-team-select" style="display: none; width: 100%; max-width: 280px; margin-top: 0.35rem;">
                  <button type="button" class="c-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="c-select__value j-select-value">Senior Team</span>
                    <svg class="c-icon c-select__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <use href="#icon-chevronDown"/>
                    </svg>
                  </button>
                  <div class="c-select__menu" role="listbox"></div>
                </div>
                <!-- Individual Student Selector -->
                <div id="j-ach-individual-wrap" style="display: none; margin-top: 0.75rem; width: 100%; max-width: 280px;">
                  <label class="j-ex-103" for="j-ach-individual-select">Select Individual Recipient</label>
                  <div class="c-select j-ex-104" id="j-ach-individual-select">
                    <button type="button" class="c-select__trigger j-ex-99" aria-haspopup="listbox" aria-expanded="false">
                      <span class="c-select__value j-select-value">-- Choose Student --</span>
                      <svg class="c-icon c-select__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <use href="#icon-chevronDown"/>
                      </svg>
                    </button>
                    <div class="c-select__menu" role="listbox"></div>
                  </div>
                </div>
              </div>
            </li>
          </ul>

          <ul>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Organised by</p>
                <span class="c-fact-item__value" id="j-fact-organisedBy">Schools Association</span>
              </div>
            </li>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Position / result</p>
                <span class="c-fact-item__value" id="j-fact-place">Runners Up</span>
              </div>
            </li>
            <li class="c-fact-item">
              <span class="c-fact-item__dot" aria-hidden="true"></span>
              <div>
                <p class="c-fact-item__label">Award / medal</p>
                <span class="c-fact-item__value" id="j-fact-colours">Silver Medal</span>
              </div>
            </li>
          </ul>
        </div>
      </section>

      <!-- Participants Roster Panel -->
      <section class="c-participants-panel" id="j-ach-participants-section">
        <div class="c-participants-panel__top">
          <h2 class="c-participants-panel__heading">
            <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <use href="#icon-users"/>
            </svg>
            <span>Participants</span>
          </h2>
          <span class="c-participants-panel__count" id="j-ach-part-count">0</span>
        </div>

        <?php if ($canEdit): ?>
          <div id="j-ach-participant-add-row" class="j-ex-122" style="display: none;">
            <input class="c-field-input j-ex-123" id="j-ach-part-input" placeholder="Type participant name and press Enter or Add" />
            <button type="button" class="c-btn c-btn--sky c-btn--sm" id="j-ach-part-add">Add</button>
          </div>
        <?php endif; ?>

        <div id="j-ach-participants-container">
          <ul class="c-participants-grid" id="j-ach-part-list"></ul>
        </div>
      </section>

      <!-- Event Photo Gallery -->
      <section class="j-ex-94" id="j-ach-gallery-section">
        <div class="c-section-heading">
          <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <use href="#icon-imagePlus"/>
          </svg>
          <h2>Event gallery</h2>
        </div>
        <div class="c-gallery-grid" id="j-achievement-gallery"></div>
        <?php if ($canEdit): ?>
          <input class="c-visually-hidden" id="j-gallery-upload" type="file" accept="image/*" multiple />
        <?php endif; ?>
      </section>

      <!-- Danger Zone Panel (Admin & Teacher Only) -->
      <?php if ($canDelete): ?>
        <section class="c-panel j-ex-28" id="j-ach-danger-zone">
          <div>
            <h3 class="j-ex-29">
              <svg class="c-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <use href="#icon-trash"/>
              </svg>
              <span>Danger Zone</span>
            </h3>
            <p class="j-ex-30">
              Permanently delete this achievement card from the records of <span id="j-ach-danger-club-name"><?= htmlspecialchars($clubName) ?></span>.
            </p>
          </div>
          <button type="button" class="c-btn j-delete-achievement-page-btn j-ex-31" id="j-delete-achievement-btn">
            Delete Achievement
          </button>
        </section>
      <?php endif; ?>

    </div>

  </article>

</div>
