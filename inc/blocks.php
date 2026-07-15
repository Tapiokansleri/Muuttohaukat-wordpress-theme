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
 */
function getFlyingEagle() {
  static $runningCount = 0;

  $eagles = [87, 88, 89, 90, 91];

  if ($runningCount > (count($eagles) - 1)) {
    $runningCount = 0;
  }

  return \Muuttohaukat\Media\image($eagles[$runningCount++], ['className' => ['eagle-img']]);
}
