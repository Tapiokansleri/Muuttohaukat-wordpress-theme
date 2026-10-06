<?php
/**
 * Article, default template: a text article in a readable column.
 *
 * For blocks, columns or landing blocks choose the template
 * "Artikkeli: lohkot ja palstat" (template-article-blocks.php).
 * Shared parts are in inc/Articles.php.
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
  $post = get_post();
  ?>
  <article class="mh-article mh-article--text">
    <?php echo article_header_html($post); ?>

    <div class="mh-article-wrap mh-article__body">
      <?php echo article_featured_html($post); ?>
      <div class="mh-gutenberg prose prose-lg mh-article__prose">
        <?php the_content(); ?>
      </div>
    </div>

    <?php echo article_footer_html($post); ?>
  </article>
  <?php
}

get_footer();
