<?php
// =============================================================================
// MVC/config/brand.php
// L'École — PHP-side Brand Color Constants
//
// SINGLE SOURCE OF TRUTH for all brand hex values used in PHP (controllers,
// views). These must mirror the CSS custom properties in global.css:
//
//   --skyblue      #7FC7CC     --lightblue    #96C0CE
//   --sunshine     #EA8913     --terracotta   #AF5031
//   --maroon       #7F0303     --red-wine     #980204
//   --midnight     #0F414A     --deepsea      #092F33
//   --moss         #4B5B34     --alabaster    #EFE8DF
//   --cream        #F7F3EC     --sand         #E4CBA9
//
// PHP cannot use CSS variables at runtime, so we define constants here.
// If a brand colour changes, update it in BOTH this file AND global.css.
// =============================================================================

// ---- Core Palette -----------------------------------------------------------
define('BRAND_SKYBLUE',     '#7FC7CC');
define('BRAND_LIGHTBLUE',   '#96C0CE');
define('BRAND_SUNSHINE',    '#EA8913');
define('BRAND_TERRACOTTA',  '#AF5031');
define('BRAND_MAROON',      '#7F0303');
define('BRAND_RED_WINE',    '#980204');
define('BRAND_MIDNIGHT',    '#0F414A');
define('BRAND_DEEPSEA',     '#092F33');
define('BRAND_MOSS',        '#4B5B34');
define('BRAND_ALABASTER',   '#EFE8DF');
define('BRAND_CREAM',       '#F7F3EC');
define('BRAND_SAND',        '#E4CBA9');
define('BRAND_TAN',         '#D8BA98');
define('BRAND_CHERRY',      '#FDABA5');
