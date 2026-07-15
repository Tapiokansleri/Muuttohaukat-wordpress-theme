<?php
/**
 * Sitemap page template (page-sivukartta).
 *
 * Lists all published pages excluding those specified in the
 * ACF 'sitemap_excludes' field.
 *
 * @package Muuttohaukat
 * @see https://wphierarchy.com
 */
namespace Muuttohaukat;

get_header(); ?>

<div class="mh-root mh-root--single-post sitemap mh-scheme--base-default">
  <?php

  while (have_posts()) { the_post();
    gutenbergContent();

    $excludes = get_field('sitemap_excludes') ?? [];
    $excludes = join(',', array_merge([get_the_ID()], $excludes));

    echo "<div class='prose mx-auto'>";
    echo "<h1>" . esc_html__('Sivukartta', 'muuttohaukat') . "</h1>";
    wp_list_pages("sort_column=menu_order&exclude=$excludes");
    echo "</div>";
  } ?>
</div>

<?php get_footer();
