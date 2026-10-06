<?php
/**
 * Template Name: Artikkeli: lohkot ja palstat
 * Template Post Type: post
 *
 * The content is laid out like a page: Gutenberg blocks, columns and the
 * landing blocks get the full width. The article header is left out when
 * the content brings its own main heading (a landing hero or an H1), so the
 * page keeps one H1. Beaver Builder articles use this template automatically
 * (inc/Articles.php). Shared parts are in inc/Articles.php.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

get_header();

while (have_posts()) {
  the_post();
  $post    = get_post();
  $landing = post_has_landing_blocks($post);
  ?>
  <article class="mh-article mh-article--blocks<?php echo $landing ? ' mh-landing' : ''; ?>">
    <?php
    if (!article_content_has_h1($post)) {
      echo article_header_html($post);
      $featured = article_featured_html($post);
      if ($featured) {
        echo '<div class="mh-article-wrap mh-article__featured-wrap">' . $featured . '</div>';
      }
    }

    if ($landing) {
      gutenbergContent();
    } else {
      echo '<div class="mh-root mh-root--single-post mh-scheme--base-default">';
      gutenbergContent();
      echo '</div>';
    }

    echo article_footer_html($post);
    ?>
  </article>
  <?php
}

get_footer();
