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
