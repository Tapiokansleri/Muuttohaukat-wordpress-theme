<?php
/**
 * Query modifications.
 *
 * Ensures custom post types that share the default category and tag
 * taxonomies appear in taxonomy archive pages.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\QueryModifiers;

add_action('init', function () {
  $tag = get_taxonomy('post_tag');
  $cat = get_taxonomy('category');

  add_action('pre_get_posts', function ($query) use ($cat, $tag) {
    if (!$query->is_main_query()) {
      return;
    }

    if ($query->is_tag() && $tag) {
      $query->set('post_type', $tag->object_type);
    }

    if ($query->is_category() && $cat) {
      $query->set('post_type', $cat->object_type);
    }
  });
});
