<?php
/**
 * The index/archive template. Last fallback in the WordPress template hierarchy.
 *
 * @see https://wphierarchy.com
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$app   = app();
$postlisting = $app->getBlock('PostListing');

// The heading of the archive: which category, author or month this is. The
// author archive used to come from a Beaver Themer layout (Tapio, 22.9.2026).
$news_label = $app->translations->getText('Title: Blog Heading');
$news_page  = get_page_by_path('ajankohtaista');
$kicker     = $news_label;
$title      = $news_label;
$lead       = '';
if (is_category() || is_tag() || is_tax()) {
  $title = single_term_title('', false);
  $lead  = term_description();
} elseif (is_author()) {
  $author = get_queried_object();
  $kicker = 'Kirjoittaja';
  $title  = $author instanceof \WP_User ? $author->display_name : get_the_author();
  $lead   = $author instanceof \WP_User ? wpautop(esc_html($author->description)) : '';
} elseif (is_year()) {
  $title = get_the_date('Y');
} elseif (is_month()) {
  $title = ucfirst(get_the_date('F Y'));
} elseif (is_day()) {
  $title = get_the_date('j.n.Y');
}

get_header(); ?>

<div class="mh-root mh-root--archive bg-white">
  <header class="mh-article-hero mh-archive-hero">
    <div class="mh-article-wrap">
      <div class="mh-article-hero__text">
        <p class="mh-article-kicker"><?php if ($news_page && $kicker === $news_label) : ?><a href="<?= esc_url(get_permalink($news_page)) ?>"><?= esc_html($kicker) ?></a><?php else : echo esc_html($kicker); endif; ?></p>
        <h1 class="mh-article-hero__title"><?= esc_html(wp_strip_all_tags($title)) ?></h1>
        <?php if (trim(wp_strip_all_tags((string) $lead)) !== '') : ?>
          <div class="mh-article-hero__lead"><?= wp_kses_post($lead) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="mh-article-wrap mh-archive-list">
    <?php
    echo withTransient(capture([$postlisting, 'render'], [
      'mode' => 'mainQuery',
      'paginated' => true,
      'trackStateInUrl' => true,
      'template' => 'Card',
    ]), [
      'key' => 'indexPostListing',
      'options' => [
        'type' => 'manual-block',
      ]
    ], $missReason);

    echo "\n<!-- Block " . esc_html($postlisting->getName()) . " cache: " . esc_html(transientResult($missReason)) . " -->";
    ?>
  </div>
</div>

<?php get_footer();
