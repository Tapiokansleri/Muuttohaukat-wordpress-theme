<?php
/**
 * Enqueue stylesheets and scripts.
 *
 * CSS lives in assets/css/ as readable files.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$app = app();

$localizeData = [
  'lang' => $app->translations->getLanguage(),
  'path' => get_stylesheet_directory_uri(),
  'wpurl' => get_site_url(),
  'translations' => $strings ?? [],
];

remove_action('wp_head', 'print_emoji_detection_script', 7);

/**
 * Check the current post's rendered content sources for the migrated pricing
 * table classes. Beaver Builder stores published and draft layouts as post
 * metadata rather than in post_content.
 */
function post_has_pricing_table($post) {
  if (!($post instanceof \WP_Post)) {
    return false;
  }

  $has_pricing_markup = static function ($value) {
    if (!is_string($value)) {
      $value = maybe_serialize($value);
    }

    return strpos($value, 'pricing-table') !== false ||
      strpos($value, 'pricing-container') !== false;
  };

  if ($has_pricing_markup($post->post_content)) {
    return true;
  }

  foreach (['_fl_builder_data', '_fl_builder_draft'] as $meta_key) {
    if ($has_pricing_markup(get_post_meta($post->ID, $meta_key, true))) {
      return true;
    }
  }

  return false;
}

add_action('wp_enqueue_scripts', function () use ($localizeData) {
  $themeUri = get_stylesheet_directory_uri();
  $version = wp_get_theme()->get('Version');

  \Muuttohaukat\FontAwesome\enqueue_font_awesome();

  // 1. Design tokens - shared custom properties for all theme styles
  wp_enqueue_style('muuttohaukat-tokens', $themeUri . '/assets/css/00-tokens.css', [], $version);
  // 2. Client CSS (normalize + fonts + legacy theme components)
  wp_enqueue_style('muuttohaukat-client', $themeUri . '/assets/css/client.css', ['muuttohaukat-tokens'], $version);
  // 3. Tailwind (Preflight reset + utilities + DaisyUI + prose)
  wp_enqueue_style('muuttohaukat-tailwind', $themeUri . '/assets/css/tailwind.css', ['muuttohaukat-client'], $version);
  // 4. Base element defaults - token-driven overrides after Tailwind
  wp_enqueue_style('muuttohaukat-base', $themeUri . '/assets/css/02-base.css', ['muuttohaukat-tailwind'], $version);
  // 5. Header styles
  wp_enqueue_style('muuttohaukat-header', $themeUri . '/assets/css/header.css', ['muuttohaukat-base'], $version);
  // 6. Footer styles
  wp_enqueue_style('muuttohaukat-footer', $themeUri . '/assets/css/footer.css', ['muuttohaukat-base'], $version);
  // 7. Content typography - final authority on content area styling
  wp_enqueue_style('muuttohaukat-content', $themeUri . '/assets/css/content.css', ['muuttohaukat-base'], $version);
  // Branded Muuttohaukat buttons (BB module, Gutenberg block, floating CTA)
  wp_enqueue_style(
    'muuttohaukat-painike',
    $themeUri . '/fl-builder/modules/muuttohaukat-painike/css/frontend.css',
    ['muuttohaukat-content'],
    $version
  );

  $queried_post = is_singular() ? get_queried_object() : null;
  $should_load_pricing = post_has_pricing_table($queried_post);
  $should_load_home_hero = is_front_page();

  if ($should_load_pricing) {
    wp_enqueue_style('muuttohaukat-pricing-table', $themeUri . '/assets/css/pricing-table.css', ['muuttohaukat-content'], $version);
  }

  if ($should_load_home_hero) {
    wp_enqueue_style('muuttohaukat-home-hero', $themeUri . '/assets/css/home-hero.css', ['muuttohaukat-content'], $version);
  }

  $should_load_landing = $queried_post instanceof \WP_Post && post_has_landing_blocks($queried_post);

  if ($should_load_landing) {
    wp_enqueue_style('muuttohaukat-landing', $themeUri . '/assets/css/landing.css', ['muuttohaukat-base'], $version);
  }

  // 8. Shared button hover chevron — load last so hover padding wins over landing/BB rules
  $chevron_deps = ['muuttohaukat-painike'];
  if ($should_load_landing) {
    $chevron_deps[] = 'muuttohaukat-landing';
  }
  if ($should_load_pricing) {
    $chevron_deps[] = 'muuttohaukat-pricing-table';
  }
  if ($should_load_home_hero) {
    $chevron_deps[] = 'muuttohaukat-home-hero';
  }
  wp_enqueue_style('muuttohaukat-button-chevron', $themeUri . '/assets/css/03-button-chevron.css', $chevron_deps, $version);

  wp_enqueue_script('muuttohaukat-client', $themeUri . '/assets/js/client.js', [], $version, true);
  wp_localize_script('muuttohaukat-client', 'wptheme', $localizeData);

  wp_enqueue_script('muuttohaukat-leadoo-blocks', $themeUri . '/assets/js/leadoo.js', [], $version, true);
  wp_enqueue_script('muuttohaukat-postlisting', $themeUri . '/assets/js/postlisting.js', ['muuttohaukat-client'], $version, true);
});

/**
 * Beaver Builder reads its cached layout CSS with file_get_contents() right
 * after checking file_exists(). If the cache file disappears between those two
 * calls — or PHP's per-request stat cache still reports a file that is already
 * gone — the read emits a warning and the page renders without its layout CSS.
 *
 * Re-stat the cache files before BB enqueues (priority 10) and drop BB's cache
 * entry for anything unreadable, so BB's own missing-file branch regenerates it.
 */
add_action('wp_enqueue_scripts', function () {
  if (is_admin() || !is_singular() || !class_exists('\FLBuilderModel')) {
    return;
  }

  $post_id = get_queried_object_id();
  if (!$post_id || !\FLBuilderModel::is_builder_enabled($post_id)) {
    return;
  }

  $info = \FLBuilderModel::get_asset_info();

  foreach (['css', 'js'] as $type) {
    $path = $info[$type] ?? '';
    if (!is_string($path) || $path === '') {
      continue;
    }

    clearstatcache(true, $path);

    if (!is_readable($path) || filesize($path) === 0) {
      \FLBuilderModel::delete_asset_cache($type);
    }
  }
}, 5);

// Font Awesome: inc/FontAwesome.php
