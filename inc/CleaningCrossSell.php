<?php
/**
 * Muuttosiivous on the moving landing pages.
 *
 * The person who books a move also has an old home to hand over, so a city
 * page offers the cleaning right after its prices, when the cleaning crews
 * reach the town: it has its own cleaning page, or it lies within
 * CLEANING_RADIUS_KM of a town that has one. Cities with their own page link
 * to it; the others link to /muuttosiivous/. The cleaning pages already link
 * back from their hero.
 *
 * The section is added in render_block after the landing-prices block of any
 * post with _mh_city, so the city pages need no content change. Distances use
 * the _mh_coords meta ("lat,lng") of the moving pages.
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

/**
 * How far the cleaning crews reach from a town with its own cleaning page, as
 * the crow flies (Muuttohaukat 12.10.2026: no cleaning in Pori, Savonlinna or
 * Lappeenranta, "Yltää about 50km säteellä").
 */
const CLEANING_RADIUS_KM = 50;

/**
 * A post's _mh_coords meta as [lat, lng], or null.
 */
function city_coords(int $post_id): ?array {
  $raw = (string) get_post_meta($post_id, '_mh_coords', true);
  if (!preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $raw, $m)) {
    return null;
  }
  return [(float) $m[1], (float) $m[2]];
}

/**
 * Great-circle distance in kilometres.
 */
function km_between(array $a, array $b): float {
  [$lat1, $lng1, $lat2, $lng2] = array_map('deg2rad', [$a[0], $a[1], $b[0], $b[1]]);
  $h = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lng2 - $lng1) / 2) ** 2;
  return 2 * 6371 * asin(min(1, sqrt($h)));
}

/**
 * Coordinates of the towns with a published cleaning page, from their moving
 * pages (the cleaning page muuttosiivous-<town> and the moving page share the
 * town's _mh_city).
 *
 * @return array<int, array{0: float, 1: float}>
 */
function cleaning_town_coords(): array {
  static $coords = null;
  if ($coords !== null) {
    return $coords;
  }

  $own = [];
  foreach (get_posts(['post_type' => 'muuttosiivous', 'post_status' => 'publish', 'numberposts' => -1]) as $post) {
    $own[preg_replace('/^muuttosiivous-/', '', $post->post_name)] = true;
  }

  $coords = [];
  foreach (get_posts(['post_type' => 'muutto', 'post_status' => 'publish', 'numberposts' => -1, 'meta_key' => '_mh_coords']) as $post) {
    $point = city_coords($post->ID);
    if ($point && isset($own[sanitize_title((string) get_post_meta($post->ID, '_mh_city', true))])) {
      $coords[] = $point;
    }
  }

  return $coords;
}

/**
 * Whether the cleaning crews reach the town of a city page.
 */
function cleaning_reaches(int $post_id, string $city): bool {
  if (cleaning_page_for($city)['own']) {
    return true;
  }

  $point = city_coords($post_id);
  if (!$point) {
    return false;
  }

  foreach (cleaning_town_coords() as $town) {
    if (km_between($point, $town) <= CLEANING_RADIUS_KM) {
      return true;
    }
  }

  return false;
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
  if ($city === '' || get_post_type($post_id) === 'muuttosiivous' || !cleaning_reaches((int) $post_id, $city)) {
    return $html;
  }

  return $html . cleaning_cross_sell_html($city, trim((string) get_post_meta($post_id, '_mh_iness', true)));
}, 10, 2);
