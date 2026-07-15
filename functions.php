<?php
/**
 * Muuttohaukat theme bootstrap.
 *
 * Loads files in dependency order: classes and app first,
 * then translations, includes, enqueue, blocks, forms, and API routes.
 *
 * @package Muuttohaukat
 */

/** 1. Theme setup: class autoloading, App singleton, core helpers. */
require_once __DIR__ . '/inc/setup.php';

/** 2. Include files (filters, menus, media, helpers, etc.). */
$excludeFromGlob = ['setup.php', 'enqueue.php', 'blocks.php', 'forms.php'];

foreach (glob(__DIR__ . '/inc/*.php') as $filename) {
  if (!in_array(basename($filename), $excludeFromGlob)) {
    require_once $filename;
  }
}

/** 3. Customizer settings. */
foreach (glob(__DIR__ . '/inc/customizer/*.php') as $filename) {
  require_once $filename;
}

/** 4. Enqueue assets (depends on $strings from translations.php). */
require_once __DIR__ . '/inc/enqueue.php';

/** 4. Block configuration and helpers. */
require_once __DIR__ . '/inc/blocks.php';

/** 5. Form handlers (LibreForm / WPLF). */
require_once __DIR__ . '/inc/forms.php';

/** 6. REST API endpoints. */
foreach (glob(__DIR__ . '/api/*.php') as $filename) {
  require_once $filename;
}

/** 7. Beaver Builder custom modules. */
add_filter( 'fl_builder_load_modules_paths', function( $paths ) {
  $modules_dir = get_stylesheet_directory() . '/fl-builder/modules';
  if ( is_dir( $modules_dir ) ) {
    $modules = glob( $modules_dir . '/*' );
    if ( is_array( $modules ) ) {
      $paths = array_merge( $paths, $modules );
    }
  }
  return $paths;
}, 10 );
