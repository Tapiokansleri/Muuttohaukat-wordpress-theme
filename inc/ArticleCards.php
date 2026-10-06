<?php
/**
 * One article card for every article feed: the PostListing block's Card
 * template (the "Ajankohtaista Muuttohaukoilla" block and /ajankohtaista/)
 * and "Lue myös" under each article. Photo, date, title and a short excerpt;
 * no borders, backgrounds or shadows.
 *
 * Most articles have no featured image. They get one of the brand photos
 * below instead, handed out in date order so neighbouring cards differ.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Brand photos for articles without a featured image: trucks, movers at
 * work, boxes and packing.
 */
function article_fallback_images(): array {
  return apply_filters('mh_article_fallback_images', [1360, 3898, 1355, 1347, 1350, 1335, 1321, 1359, 3929, 7627, 1346, 1333]);
}

/**
 * The card photo: the featured image, else a brand photo.
 */
function article_card_image_id(\WP_Post $post): int {
  $id = (int) get_post_thumbnail_id($post);
  if ($id) {
    return $id;
  }

  static $position = null;
  if ($position === null) {
    $ids = get_posts([
      'post_type'   => 'post',
      'post_status' => 'publish',
      'numberposts' => -1,
      'fields'      => 'ids',
      'orderby'     => 'date',
      'order'       => 'DESC',
      'meta_query'  => [['key' => '_thumbnail_id', 'compare' => 'NOT EXISTS']],
    ]);
    $position = array_flip($ids);
  }
  $images = article_fallback_images();

  return (int) $images[($position[$post->ID] ?? $post->ID) % count($images)];
}

function article_card_html(\WP_Post $post, string $heading = 'h3'): string {
  $heading = in_array($heading, ['h2', 'h3', 'h4'], true) ? $heading : 'h3';
  $link    = get_permalink($post);
  $title   = get_the_title($post);
  $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt($post)), 22, '…');
  // The photo repeats the title link, so it is hidden from screen readers.
  $image   = wp_get_attachment_image(article_card_image_id($post), 'medium_large', false, [
    'class'   => 'mh-article-card__img',
    'alt'     => '',
    'loading' => 'lazy',
    'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 440px',
  ]);

  ob_start();
  ?>
  <article class="mh-article-card">
    <?php if ($image) : ?>
      <a class="mh-article-card__media" href="<?php echo esc_url($link); ?>" tabindex="-1" aria-hidden="true"><?php echo $image; ?></a>
    <?php endif; ?>
    <time class="mh-article-card__date" datetime="<?php echo esc_attr(get_the_date('Y-m-d', $post)); ?>"><?php echo esc_html(get_the_date('j.n.Y', $post)); ?></time>
    <<?php echo $heading; ?> class="mh-article-card__title"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></<?php echo $heading; ?>>
    <?php if ($excerpt !== '') : ?>
      <p class="mh-article-card__text"><?php echo esc_html($excerpt); ?></p>
    <?php endif; ?>
  </article>
  <?php

  return (string) ob_get_clean();
}

/**
 * [nostot sivut="1453:1355,777:1358:Piano- ja kassakaappikuljetus" sarakkeet="4" kuvat="ei"]
 * Highlights of other pages in the article card style: page ID, optional
 * image ID and optional title, comma separated. The text is the page's
 * search description (The SEO Framework), else its excerpt. Without an image
 * ID the featured image is used, else a brand photo; kuvat="ei" leaves
 * images out.
 */
function page_card_html(\WP_Post $post, int $image = 0, string $title = '', bool $with_image = true): string {
  $link = get_permalink($post);
  $title = $title !== '' ? $title : get_the_title($post);
  $text = trim((string) get_post_meta($post->ID, '_genesis_description', true));
  if ($text === '') {
    $text = wp_trim_words(wp_strip_all_tags(get_the_excerpt($post)), 24, '…');
  }
  $media = '';
  if ($with_image) {
    $image = $image ?: (int) get_post_thumbnail_id($post) ?: article_fallback_images()[$post->ID % count(article_fallback_images())];
    $img = wp_get_attachment_image($image, 'medium_large', false, [
      'class'   => 'mh-article-card__img',
      'alt'     => '',
      'loading' => 'lazy',
      'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 340px',
    ]);
    $media = $img ? '<a class="mh-article-card__media" href="' . esc_url($link) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>' : '';
  }

  return '<article class="mh-article-card mh-article-card--page">' . $media
    . '<h3 class="mh-article-card__title"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>'
    . ($text !== '' ? '<p class="mh-article-card__text">' . esc_html($text) . '</p>' : '')
    . '</article>';
}

add_shortcode('nostot', function ($atts) {
  $atts = shortcode_atts(['sivut' => '', 'sarakkeet' => '3', 'kuvat' => 'kylla'], $atts, 'nostot');
  $cards = '';
  foreach (array_filter(array_map('trim', explode(',', $atts['sivut']))) as $item) {
    $parts = array_map('trim', explode(':', $item, 3));
    $post = get_post((int) $parts[0]);
    if (!$post || $post->post_status !== 'publish') {
      continue;
    }
    $cards .= page_card_html($post, (int) ($parts[1] ?? 0), (string) ($parts[2] ?? ''), $atts['kuvat'] !== 'ei');
  }
  $cols = in_array($atts['sarakkeet'], ['2', '3', '4'], true) ? $atts['sarakkeet'] : '3';

  return $cards === '' ? '' : '<div class="mh-article-cards mh-article-cards--' . $cols . ' not-prose">' . $cards . '</div>';
});

/**
 * [artikkelit maara="6"]: the latest articles as article cards (the old
 * Beaver Builder post grids).
 */
add_shortcode('artikkelit', function ($atts) {
  $atts = shortcode_atts(['maara' => '3'], $atts, 'artikkelit');
  $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'numberposts' => max(1, min(12, (int) $atts['maara'])), 'post__not_in' => [get_queried_object_id()]]);
  if (!$posts) {
    return '';
  }
  return '<div class="mh-article-cards not-prose">' . implode('', array_map(fn($p) => article_card_html($p, 'h3'), $posts)) . '</div>';
});
