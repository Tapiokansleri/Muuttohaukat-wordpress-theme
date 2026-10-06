<?php
/**
 * The static front page template.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// A front page built from landing blocks gets the .mh-landing wrapper and
// its styles like every other landing page.
$post = get_queried_object();
if ($post instanceof \WP_Post && post_has_landing_blocks($post)) {
  require locate_template('singular.php');
  return;
}

get_header(); ?>

<div class="mh-root mh-root--front-page">
  <?php
  while (have_posts()) { the_post();
    gutenbergContent();
  } ?>
</div>

<?php get_footer();
