<?php
/**
 * Template Name: Contained page
 *
 * Same layout as the default page template, with content wrapped in a
 * centered max-width container.
 *
 * @package Muuttohaukat
 */

namespace Muuttohaukat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pages built from landing blocks lay out their own full-width sections in
// .mh-landing like every other page; the container is for plain content only.
$post = get_queried_object();
if ($post instanceof \WP_Post && post_has_landing_blocks($post)) {
  require locate_template('singular.php');
  return;
}

get_header(); ?>

<div class="mh-root mh-root--single-post mh-root--contained mh-scheme--base-default">
  <div class="container mx-auto px-4">
    <?php
    while (have_posts()) {
      the_post();
      gutenbergContent();
    }
    ?>
  </div>
</div>

<?php get_footer();
