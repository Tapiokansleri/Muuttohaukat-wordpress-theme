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

  $content = file_get_contents($path);
  if (!is_string($content)) return '';

  return str_replace(
    ['{{theme_uri}}', '{{home_url}}'],
    [esc_url(get_template_directory_uri()), esc_url(home_url())],
    $content
  );
}

add_action('init', function () {
  if (!function_exists('register_block_pattern_category')) return;

  register_block_pattern_category('mh-landing', [
    'label' => __('Muuttohaukat — Landing page', 'muuttohaukat'),
  ]);

  $full = landing_default_content();
  if ($full === '') return;

  register_block_pattern('mh/landing-full', [
    'title'       => __('Koko Muuttopalvelu Paimio -sivu', 'muuttohaukat'),
    'categories'  => ['mh-landing'],
    'content'     => $full,
    'description' => __('Koko muuttopalvelu Paimio -laskeutumissivu valmiina.', 'muuttohaukat'),
  ]);
});

/**
 * Register the Muuttohaukat block category so theme blocks group together
 * in the inserter. Each block.json under blocks/ must reference "muuttohaukat"
 * as its `category` for this grouping to take effect.
 */
add_filter('block_categories_all', function ($categories) {
  array_unshift($categories, [
    'slug'  => 'muuttohaukat',
    'title' => __('Muuttohaukat', 'muuttohaukat'),
    'icon'  => null,
  ]);
  return $categories;
});
