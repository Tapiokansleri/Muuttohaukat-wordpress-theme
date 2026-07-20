<?php
/**
 * Singular template for posts, pages, and custom post types.
 *
 * @package Muuttohaukat
 * @see https://wphierarchy.com
 */
namespace Muuttohaukat;

get_header();

$post = get_queried_object();
$is_landing = $post instanceof \WP_Post && post_has_landing_blocks($post);

if ($is_landing) {
  echo '<main id="content" class="mh-landing" role="main">';
} else {
  echo '<div class="mh-root mh-root--single-post mh-scheme--base-default">';
}

while (have_posts()) {
  the_post();
  gutenbergContent();
}

echo $is_landing ? '</main>' : '</div>';

get_footer();
