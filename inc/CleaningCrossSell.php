<?php
/**
 * Muuttosiivous on the moving landing pages.
 *
 * The person who books a move also has an old home to hand over, so every
 * city page offers the cleaning right after its prices. The 13 cities that
 * kept their own cleaning page (audit 3.10) link to it; the other cities link
 * to /muuttosiivous/. The cleaning pages already link back from their hero.
 *
 * The section is added in render_block after the landing-prices block of any
 * post with _mh_city, so the 103 city pages need no content change.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * The cleaning page for a city: its own page when one is published, else the
 * cleaning hub.
 *
 * @return array{url: string, own: bool}
 */
function cleaning_page_for(string $city): array {
  static $pages = null;
  if ($pages === null) {
    $pages = [];
    foreach (get_posts(['post_type' => 'muuttosiivous', 'post_status' => 'publish', 'numberposts' => -1]) as $post) {
      $pages[$post->post_name] = get_permalink($post);
    }
  }

  $slug = 'muuttosiivous-' . sanitize_title($city);

  return isset($pages[$slug])
    ? ['url' => $pages[$slug], 'own' => true]
    : ['url' => home_url('/muuttosiivous/'), 'own' => false];
}

function cleaning_cross_sell_html(string $city, string $iness): string {
  $page  = cleaning_page_for($city);
  $place = $iness !== '' ? $iness : $city;

  $heading = $page['own']
    ? sprintf('Muuttosiivous %s samalla tarjouksella', $place)
    : 'Muuttosiivous samalla tarjouksella';
  $button = $page['own'] ? sprintf('Muuttosiivous %s', $place) : 'Lue muuttosiivouksesta';

  ob_start();
  ?>
  <section class="mh-landing-section mh-landing-section--bg-black mh-landing-cleaning" id="muuttosiivous" aria-labelledby="mh-landing-cleaning-title">
    <div class="mh-landing__inner mh-landing-cleaning__inner">
      <div class="mh-landing-cleaning__text">
        <p class="mh-landing-kicker">Muuttosiivous</p>
        <h2 id="mh-landing-cleaning-title"><?php echo esc_html($heading); ?></h2>
        <p>Kun tavarat ovat lähteneet, siivoamme vanhan kodin, ja luovutat sen puhtaana. Tilaa muuttosiivous samaan tarjoukseen muuton kanssa, niin muuttoauto, kantajat ja siivous tulevat samalta toimittajalta.</p>
        <ul class="mh-hero-checklist">
          <li>Kaikki asunnon pinnat lattiasta kattoon</li>
          <li>Nurkat ja kolot huolellisesti</li>
          <li>Jätteet hävitetään ja kierrätetään asianmukaisesti</li>
        </ul>
      </div>
      <div class="mh-landing-cleaning__offer">
        <p class="mh-landing-kicker">Hinta</p>
        <p class="mh-landing-cleaning__price">200-400 €</p>
        <p>on muuttosiivouksen keskimääräinen hinta yksiössä tai kaksiossa. Suuremmassa asunnossa hinta nousee koon mukaan, ja saat hinnan tarjouksesta ennen siivousta.</p>
        <div class="mh-painike-wrap">
          <a class="mh-painike mh-painike--yellow" href="<?php echo esc_url($page['url']); ?>"><?php echo esc_html($button); ?></a>
        </div>
      </div>
    </div>
  </section>
  <?php

  return (string) ob_get_clean();
}

add_filter('render_block', function ($html, $block) {
  if (($block['blockName'] ?? '') !== 'muuttohaukat/landing-prices' || !is_singular()) {
    return $html;
  }

  $post_id = get_the_ID() ?: get_queried_object_id();
  $city = trim((string) get_post_meta($post_id, '_mh_city', true));
  if ($city === '' || get_post_type($post_id) === 'muuttosiivous') {
    return $html;
  }

  return $html . cleaning_cross_sell_html($city, trim((string) get_post_meta($post_id, '_mh_iness', true)));
}, 10, 2);
