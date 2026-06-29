<?php
/**
 * The static front page template.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

get_header(); ?>

<div class="mh-root mh-root--front-page">
  <?php
  while (have_posts()) { the_post();
    gutenbergContent();
  } ?>
</div>

<?php get_footer();
