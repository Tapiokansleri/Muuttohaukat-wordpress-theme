<?php
/**
 * Example prices: worked examples of what a move costs, generated from the
 * price table (Muuttohaukat 12.10.2026: "Voidaan generoida hinta-haarukan
 * perusteella"). They are examples from the price ranges, not realized moves,
 * and they follow price_rows(), so a price change updates them too.
 *
 *   [esimerkkihinnat]
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * A price range scaled like the landing-prices block's multiplier, rounded to
 * the nearest 5 €, with an en dash: "450-600 €" × 2.15 -> "970–1290 €".
 */
function example_price(string $range, float $multiplier = 1.0): string {
  if (abs($multiplier - 1.0) >= 0.0001) {
    $range = preg_replace_callback('/\d+/', fn($m) => (string) (int) (round((int) $m[0] * $multiplier / 5) * 5), $range);
  }
  return str_replace('-', '–', $range);
}

function example_prices_html(): string {
  $rows = [];
  foreach (price_rows() as $row) {
    $rows[$row['type']] = $row;
  }

  // [title, price row type, multiplier, text]
  $examples = [
    ['Yksiö, 25 m²', 'yksiö', 1.0, 'Pieni koti, esimerkiksi opiskelija-asunto. Muuttoauto, muuttajat sekä tavaroiden kanto ja kuljetus.'],
    ['Kaksio, 50 m²', 'kaksio', 1.0, 'Tavallinen kerrostalokaksio. Huonekalut suojataan, ja tavarat kannetaan uudessa kodissa oikeille paikoille.'],
    ['Kolmio, 70 m²', 'kolmio', 1.0, 'Pariskunnan tai pienen perheen koti. Tavaraa on enemmän, joten mukana on isompi muuttoauto ja enemmän muuttajia.'],
    ['Perheasunto, 90 m²', 'perheasunto', 1.0, 'Iso perheasunto, jossa on paljon huonekaluja. Kotimuutto tehdään lähtökohtaisesti yhdellä kuormalla.'],
    ['Kaksio, 50 m², täysi palvelu', 'kaksio', 2.15, 'Pakkaamme, kuljetamme ja puramme tavarat puolestasi. Sinun ei tarvitse pakata eikä kantaa mitään.'],
  ];

  $cards = '';
  foreach ($examples as [$title, $type, $multiplier, $text]) {
    if (empty($rows[$type]['basic'])) {
      continue;
    }
    $row = $rows[$type];
    $cards .= '<div class="mh-example">'
      . '<h3 class="mh-example__title">' . esc_html($title) . '</h3>'
      . '<p class="mh-example__price">' . esc_html(example_price($row['basic'], $multiplier)) . '</p>'
      . (!empty($row['boxes']) ? '<p class="mh-example__boxes">' . esc_html('muuttolaatikoiden kanssa ' . example_price($row['boxes'], $multiplier)) . '</p>' : '')
      . '<p>' . esc_html($text) . '</p>'
      . '</div>';
  }

  // The price page's own figure for the biggest moves (it is not in the price table).
  $cards .= '<div class="mh-example">'
    . '<h3 class="mh-example__title">' . esc_html('Omakotitalo') . '</h3>'
    . '<p class="mh-example__price">' . esc_html('1000–2000 €') . '</p>'
    . '<p class="mh-example__boxes">' . esc_html('tai enemmän tavaramäärän mukaan') . '</p>'
    . '<p>' . esc_html('Suuremmissa omakotitalomuutoissa hinta lasketaan aina tarjouksessa tavaramäärän ja matkan mukaan.') . '</p>'
    . '</div>';

  return '<div class="mh-examples not-prose">' . $cards . '</div>';
}

add_shortcode('esimerkkihinnat', fn() => example_prices_html());
