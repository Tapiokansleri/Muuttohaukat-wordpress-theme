<?php
/**
 * "Varaa muuttopäivä heti kun tiedät sen" section.
 *
 * City pages place it as the muuttohaukat/landing-calendar block right after
 * the hero. City pages without the block (the /muutto/ Themer pages and
 * Espoo) get the same section printed before the footer. No other page
 * shows it.
 *
 * The calendar is rendered without the `link` attribute on purpose: with a
 * link every day becomes a crawlable ?date= address, which is exactly what
 * the SEO audit asked to remove from the front page.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * The place in the case the heading needs ("Lahdessa", "Hyvinkäällä"), from
 * the _mh_iness meta every city post carries. Empty on other pages.
 */
function calendar_section_place(): string {
  $post_id = is_admin() ? get_the_ID() : get_queried_object_id();

  return $post_id ? trim((string) get_post_meta($post_id, '_mh_iness', true)) : '';
}

/**
 * Section copy. Filter mh_calendar_section_texts to change the wording.
 */
function calendar_section_texts(): array {
  $place = calendar_section_place();

  return apply_filters('mh_calendar_section_texts', [
    'heading' => $place !== ''
      ? sprintf('Varaa muuttopäivä %s heti kun tiedät sen', $place)
      : 'Varaa muuttopäivä heti kun tiedät sen',
    'paragraphs' => [
      'Kuukauden vaihteet ja viikonloput ovat suosituimpia muuttopäiviä, koska vuokrasopimukset päättyvät silloin. Ne myös varataan meiltä ensimmäisenä.',
      'Jos toivomasi päivä on jo varattu, ehdotamme lähintä vapaata aamua ja kerromme, mitkä päivät sen ympärillä ovat auki. Yhteydenotto ei sido sinua mihinkään.',
    ],
    'highlight' => 'Saat tarjouksen alle 60m2 asuntosi muutosta välittömästi! Suuremmat asunnot viimeistään seuraavana arkipäivänä. Voit myös tilata alle 80m2 kokoisen asunnon muuton suoraan osoitteessa: <a href="https://tilaamuutto.fi">tilaamuutto.fi</a>',
  ]);
}

/**
 * The text column and the calendar, without the section wrapper.
 *
 * @param bool $highlight Whether to print the bold offer promise. City pages
 *                        turn it off, because the quote block above repeats it.
 */
function calendar_section_inner_html(bool $highlight = true): string {
  $texts = calendar_section_texts();

  ob_start();
  ?>
  <div class="mh-calendar-section__inner">
    <div class="mh-calendar-section__text">
      <h2 class="mh-calendar-section__title"><?php echo esc_html($texts['heading']); ?></h2>
      <?php foreach ($texts['paragraphs'] as $paragraph) : ?>
        <p><?php echo esc_html($paragraph); ?></p>
      <?php endforeach; ?>
      <?php if ($highlight && !empty($texts['highlight'])) : ?>
        <p class="mh-calendar-section__highlight"><?php echo wp_kses_post($texts['highlight']); ?></p>
      <?php endif; ?>
    </div>
    <?php echo do_shortcode('[muuttopaivat]'); ?>
  </div>
  <?php

  return (string) ob_get_clean();
}

/**
 * The stand alone section used outside the block editor.
 */
function calendar_section_html(): string {
  return '<section class="mh-calendar-section" aria-label="' . esc_attr(calendar_section_texts()['heading']) . '">'
    . calendar_section_inner_html(true)
    . '</section>';
}

/**
 * Whether this view should get the automatic section before the footer.
 * Only moving landing pages get it, meaning the city posts that carry
 * _mh_city (the /muutto/ Themer pages and Espoo); most city pages already
 * hold the calendar as a block. Other pages must not show it.
 */
function calendar_section_applies(): bool {
  if (!is_singular()) {
    return false;
  }

  $post = get_queried_object();
  if (!$post instanceof \WP_Post || get_post_meta($post->ID, '_mh_city', true) === '') {
    return false;
  }

  // A page that already shows the calendar does not need a second one.
  $content = (string) $post->post_content;
  if (strpos($content, 'muuttopaivat') !== false || strpos($content, 'muuttohaukat/landing-calendar') !== false) {
    return false;
  }
  foreach (['_fl_builder_data', '_fl_builder_draft'] as $meta_key) {
    $data = get_post_meta($post->ID, $meta_key, true);
    if (is_string($data) && strpos($data, 'muuttopaivat') !== false) {
      return false;
    }
  }

  return (bool) apply_filters('mh_show_calendar_section', true, $post);
}

add_action('get_footer', function () {
  if (!calendar_section_applies()) {
    return;
  }

  echo calendar_section_html();
}, 5);

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'muuttohaukat-calendar-section',
    get_stylesheet_directory_uri() . '/assets/css/calendar-section.css',
    ['muuttohaukat-content'],
    wp_get_theme()->get('Version')
  );
}, 20);

/**
 * The same copy for the block editor preview.
 */
add_action('enqueue_block_editor_assets', function () {
  $texts = calendar_section_texts();
  $texts['highlightText'] = trim(wp_strip_all_tags($texts['highlight'] ?? ''));

  wp_enqueue_style(
    'muuttohaukat-calendar-section',
    get_stylesheet_directory_uri() . '/assets/css/calendar-section.css',
    [],
    wp_get_theme()->get('Version')
  );
  wp_add_inline_script('wp-blocks', 'window.mhCalendarTexts = ' . wp_json_encode($texts) . ';', 'before');
}, 5);
