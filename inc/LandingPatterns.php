<?php
/**
 * Landing page default-content helper + full-page pattern.
 *
 * The 7 section blocks are registered as native blocks under
 * blocks/landing-<slug>/ (each appears in the inserter on its own).
 * This file exposes:
 *
 *  - landing_default_content() — full default markup used by the autoload hook
 *    in inc/LandingTemplate.php
 *  - A "full landing page" pattern that drops all 7 wrapper blocks at once
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Read the canonical default-content.html.
 */
function landing_default_content() {
  $path = get_template_directory() . '/template-parts/landing/default-content.html';
  if (!file_exists($path)) return '';
  return file_get_contents($path);
}

add_action('init', function () {
  if (!function_exists('register_block_pattern_category')) return;

  register_block_pattern_category('mh-landing', [
    'label' => __('Muuttohaukat — Landing page', 'muuttohaukat'),
  ]);

  $full = landing_default_content();
  if ($full === '') return;

  register_block_pattern('mh/landing-full', [
    'title'       => __('Landing: Koko Muuttopalvelu Paimio -sivu', 'muuttohaukat'),
    'categories'  => ['mh-landing'],
    'content'     => $full,
    'description' => __('Koko muuttopalvelu Paimio -laskeutumissivu valmiina.', 'muuttohaukat'),
  ]);
});

/**
 * Register the "Landing page" block category so all landing-* blocks group
 * together in the inserter instead of mixing into core "Design".
 * Each block.json under blocks/landing-<slug>/ must reference "landing-page"
 * as its `category` for this grouping to take effect.
 */
add_filter('block_categories_all', function ($categories) {
  array_unshift($categories, [
    'slug'  => 'landing-page',
    'title' => __('Landing page', 'muuttohaukat'),
    'icon'  => null,
  ]);
  return $categories;
});
