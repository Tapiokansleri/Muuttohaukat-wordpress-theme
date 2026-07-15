<?php
/**
 * Block type registration and helpers.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Restrict allowed block types to core essentials and registered ACF blocks.
 */
add_action('allowed_block_types_all', function () {
  $app = app();

  $core = [
    'core/block',
    'core/template',
    'core/paragraph',
    'core/heading',
    'core/list',
    'core/list-item',
    'core/nextpage',
    'core/separator',
    'core/shortcode',
    'core/embed',
    'core/columns',
    'core/column',
    'core/group',
    'core/image',
    'core/buttons',
    'core/button',
    'core/html',
    'core/spacer',
    'core/quote',
  ];

  $acfBlocks = [];

  foreach ($app->getBlocks() as $block) {
    $acfBlocks[] = 'acf/' . strtolower($block->getName());
  }

  $native = [
    'muuttohaukat/route-calculator',
    'muuttohaukat/icon-item',
    'muuttohaukat/buttons',
    'muuttohaukat/landing-hero',
    'muuttohaukat/landing-service',
    'muuttohaukat/landing-cases',
    'muuttohaukat/landing-kantoapu',
    'muuttohaukat/landing-distances',
    'muuttohaukat/landing-why',
    'muuttohaukat/landing-final-cta',
  ];

  return array_merge($core, $acfBlocks, $native);
}, PHP_INT_MAX);

/**
 * Register PostListing REST route.
 */
add_action('rest_api_init', function () {
  (new \Muuttohaukat\Routes\PostListing())->registerRoutes();
});

/**
 * Rotate through flying eagle images for posts without thumbnails.
 *
 * Uses bundled theme SVGs so cards do not depend on broken attachment srcset metadata.
 *
 * @return string
 */
function getFlyingEagle() {
  static $runningCount = 0;

  $eagles = [
    'muuttohaukka_01.b9243e77.svg',
    'muuttohaukka_02.c3b3b03f.svg',
    'muuttohaukka_03.538eb563.svg',
    'muuttohaukka_04.a40463b8.svg',
    'muuttohaukka_05.b0e94b25.svg',
  ];

  if ($runningCount > (count($eagles) - 1)) {
    $runningCount = 0;
  }

  $src = get_stylesheet_directory_uri() . '/assets/img/' . $eagles[$runningCount++];
  $class = \Muuttohaukat\className('eagle-img');

  return "<img src='" . esc_url($src) . "' {$class} alt='' width='1366' height='768' loading='lazy'>";
}
