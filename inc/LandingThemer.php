<?php
/**
 * Posts built with the landing blocks render through singular.php, not a
 * Beaver Builder Themer layout.
 *
 * The `muutto` and `muuttosiivous` post types have Themer singular layouts
 * (5978 and 7870). Once a post's content holds the landing blocks, those
 * layouts would wrap it in the old template again, with its 110 link list.
 * Header, footer and part layouts are left alone.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

add_filter('fl_theme_builder_current_page_layouts', function ($layouts) {
  if (!is_array($layouts) || empty($layouts['singular']) || !is_singular()) {
    return $layouts;
  }

  $post = get_queried_object();
  if ($post instanceof \WP_Post && post_has_landing_blocks($post)) {
    unset($layouts['singular']);
  }

  return $layouts;
});
