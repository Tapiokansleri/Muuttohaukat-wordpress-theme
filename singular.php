<?php
/**
 * Singular template for posts, pages, and custom post types.
 *
 * @package Muuttohaukat
 * @see https://wphierarchy.com
 */
namespace Muuttohaukat;

get_header(); ?>

<div class="mh-root mh-root--single-post mh-scheme--base-default">
  <?php

  while (have_posts()) { the_post();
    gutenbergContent();
  } ?>
</div>

<?php get_footer();
