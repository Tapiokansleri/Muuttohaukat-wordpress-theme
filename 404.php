<?php
/**
 * The 404 (not found) template.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

get_header();

$app = app();
?>

<div class="mh-root--404">
  <article class="mh-gutenberg">
    <div class="mh-container">
      <h1><?= esc_html($app->translations->getText('Title: 404')) ?></h1>
    </div>
  </article>
</div>

<?php get_footer();
