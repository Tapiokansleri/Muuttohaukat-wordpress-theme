<?php
/**
 * Article (post) templates.
 *
 * Posts no longer use Beaver Builder Themer layout 8255. The theme renders
 * them with two templates that share the parts below:
 *
 *   single-post.php               default: a text article in a readable column
 *   template-article-blocks.php   "Artikkeli: lohkot ja palstat": the content is
 *                                 laid out like a page, so Gutenberg blocks,
 *                                 columns and the landing blocks get the full
 *                                 width. Beaver Builder articles use it
 *                                 automatically.
 *
 * Shared: the header (date, reading time, title, excerpt, featured image),
 * the author box (Beaver Builder template 8437, as in the old layout), a
 * quote call to action, "Lue myös" and Article markup.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

const ARTICLE_BLOCKS_TEMPLATE = 'template-article-blocks.php';
const ARTICLE_AUTHOR_PATTERN  = 'kirjoittaja-totte-vivolin'; // synced pattern (wp_block), edited under Patterns

/** Posts skip the Themer singular layout; the theme templates render them. */
add_filter('fl_theme_builder_current_page_layouts', function ($layouts) {
  if (is_array($layouts) && !empty($layouts['singular']) && is_singular('post')) {
    unset($layouts['singular']);
  }

  return $layouts;
}, 20);

/** Articles built with Beaver Builder need the full width of the blocks template. */
add_filter('template_include', function ($template) {
  $post = is_singular('post') ? get_queried_object() : null;
  if ($post instanceof \WP_Post && article_uses_blocks($post) && basename($template) !== ARTICLE_BLOCKS_TEMPLATE) {
    return locate_template(ARTICLE_BLOCKS_TEMPLATE) ?: $template;
  }

  return $template;
}, 20);

add_action('wp_enqueue_scripts', function () {
  // Articles, and the archive heading in index.php (category, author, month).
  if (!is_singular('post') && !(is_archive() || is_home()) || is_front_page()) {
    return;
  }
  $file = '/assets/css/article.css';
  $path = get_stylesheet_directory() . $file;
  wp_enqueue_style('muuttohaukat-article', get_stylesheet_directory_uri() . $file, ['muuttohaukat-base'], file_exists($path) ? (string) filemtime($path) : null);
}, 20);

function article_is_bb(\WP_Post $post): bool {
  return class_exists('FLBuilderModel') && \FLBuilderModel::is_builder_enabled($post->ID);
}

function article_uses_blocks(\WP_Post $post): bool {
  return get_page_template_slug($post) === ARTICLE_BLOCKS_TEMPLATE || article_is_bb($post);
}

/** Whether the content brings its own main heading (a landing hero or an H1). */
function article_content_has_h1(\WP_Post $post): bool {
  if (preg_match('#<h1[\s>]|wp:muuttohaukat/landing-hero#', $post->post_content)) {
    return true;
  }
  if (article_is_bb($post)) {
    foreach ((array) get_post_meta($post->ID, '_fl_builder_data', true) as $node) {
      $s = $node->settings ?? null;
      if (($s->type ?? '') === 'heading' && ($s->tag ?? '') === 'h1') {
        return true;
      }
      if (isset($s->text) && is_string($s->text) && preg_match('#<h1[\s>]#', $s->text)) {
        return true;
      }
    }
  }

  return false;
}

function article_reading_minutes(\WP_Post $post): int {
  $words = str_word_count(wp_strip_all_tags(strip_shortcodes($post->post_content)), 0, 'äöåÄÖÅéÉ');
  return max(1, (int) round($words / 200));
}

const ARTICLE_HERO_IMAGE = 8708; // the mascot of the front page hero

/** The featured image, shown at the top of the article column. */
function article_featured_html(\WP_Post $post): string {
  if (!has_post_thumbnail($post)) {
    return '';
  }
  $img = \Muuttohaukat\Media\image(get_post_thumbnail_id($post), ['size' => 'large', 'altFallback' => get_the_title($post), 'className' => ['mh-article-featured__img']]);

  return $img ? '<figure class="mh-article-featured">' . $img . '</figure>' : '';
}

function article_header_html(\WP_Post $post): string {
  $hawk = get_post(ARTICLE_HERO_IMAGE)
    ? \Muuttohaukat\Media\image(ARTICLE_HERO_IMAGE, ['size' => 'medium', 'responsive' => false, 'altFallback' => 'Muuttohaukkojen maskotti, keltalakkinen haukka peukku pystyssä', 'className' => ['mh-article-hero__img']])
    : '';
  $news = get_page_by_path('ajankohtaista');

  ob_start();
  ?>
  <header class="mh-article-hero">
    <div class="mh-article-wrap mh-article-hero__inner<?php echo $hawk ? ' has-media' : ''; ?>">
      <div class="mh-article-hero__text">
        <p class="mh-article-kicker">
          <?php if ($news) : ?><a href="<?php echo esc_url(get_permalink($news)); ?>">Ajankohtaista</a><?php else : ?>Ajankohtaista<?php endif; ?>
          <span aria-hidden="true"> · </span><time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $post)); ?>"><?php echo esc_html(get_the_date('j.n.Y', $post)); ?></time>
          <span aria-hidden="true"> · </span><?php echo esc_html(sprintf('%d min lukuaika', article_reading_minutes($post))); ?>
        </p>
        <h1 class="mh-article-hero__title"><?php echo esc_html(get_the_title($post)); ?></h1>
        <?php if (has_excerpt($post)) : ?>
          <p class="mh-article-hero__lead"><?php echo esc_html(get_the_excerpt($post)); ?></p>
        <?php endif; ?>
      </div>
      <?php if ($hawk) : ?>
        <figure class="mh-article-hero__media"><?php echo $hawk; ?></figure>
      <?php endif; ?>
    </div>
  </header>
  <?php

  return (string) ob_get_clean();
}

function article_related(\WP_Post $post, int $count = 3): array {
  return get_posts([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => $count,
    'post__not_in'   => [$post->ID],
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);
}

function article_footer_html(\WP_Post $post): string {
  $pattern = get_page_by_path(ARTICLE_AUTHOR_PATTERN, OBJECT, 'wp_block');
  $author  = $pattern ? do_blocks('<!-- wp:block {"ref":' . (int) $pattern->ID . '} /-->') : '';
  $related = article_related($post);
  $news    = get_page_by_path('ajankohtaista');

  ob_start();
  ?>
  <?php if (trim($author) !== '') : ?>
    <aside class="mh-article-author" aria-label="Kirjoittaja">
      <div class="mh-article-wrap"><?php echo $author; ?></div>
    </aside>
  <?php endif; ?>

  <section class="mh-article-cta" aria-labelledby="mh-article-cta-title">
    <div class="mh-article-wrap mh-article-cta__inner">
      <div class="mh-article-cta__text">
        <p class="mh-article-kicker">Muutto edessä?</p>
        <h2 id="mh-article-cta-title">Pyydä tarjous muutosta</h2>
        <p>Kerro asunnon koko ja muuttopäivä, niin saat hinnan. Alle 60 neliön asunnon muutosta tarjous tulee heti.</p>
      </div>
      <div class="mh-article-cta__actions">
        <a class="mh-painike mh-painike--yellow" href="<?php echo esc_url(home_url('/tarjouspyynto/kotimuutto/')); ?>">Pyydä tarjous</a>
        <a class="mh-article-cta__link" href="<?php echo esc_url(home_url('/kotimuutto/muuttopalvelun-hinta/')); ?>">Katso hinnat</a>
      </div>
    </div>
  </section>

  <?php if ($related) : ?>
    <section class="mh-article-related" aria-labelledby="mh-article-related-title">
      <div class="mh-article-wrap">
        <div class="mh-article-related__head">
          <p class="mh-article-kicker">Ajankohtaista</p>
          <h2 id="mh-article-related-title">Lue myös</h2>
        </div>
        <div class="mh-article-cards">
          <?php foreach ($related as $item) : ?>
            <?php echo article_card_html($item, 'h3'); ?>
          <?php endforeach; ?>
        </div>
        <?php if ($news) : ?>
          <p class="mh-article-related__all"><a href="<?php echo esc_url(get_permalink($news)); ?>">Kaikki artikkelit</a></p>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>
  <?php

  return (string) ob_get_clean();
}

/** Article markup; The SEO Framework gives only WebSite and WebPage. */
add_action('wp_head', function () {
  if (!is_singular('post')) {
    return;
  }
  $post = get_queried_object();
  if (!$post instanceof \WP_Post) {
    return;
  }

  $logo = get_theme_mod('custom_logo');
  $data = [
    '@context'         => 'https://schema.org',
    '@type'            => 'Article',
    'headline'         => wp_strip_all_tags(get_the_title($post)),
    'datePublished'    => get_the_date('c', $post),
    'dateModified'     => get_the_modified_date('c', $post),
    'mainEntityOfPage' => get_permalink($post),
    'author'           => ['@type' => 'Organization', 'name' => 'Muuttohaukat', 'url' => home_url('/')],
    'publisher'        => array_filter([
      '@type' => 'Organization',
      'name'  => 'Muuttohaukat',
      'url'   => home_url('/'),
      'logo'  => $logo ? wp_get_attachment_image_url((int) $logo, 'full') : null,
    ]),
  ];
  if (has_post_thumbnail($post)) {
    $data['image'] = wp_get_attachment_image_url(get_post_thumbnail_id($post), 'large');
  }

  print_json_ld($data);
}, 20);
