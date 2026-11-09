<?php
/**
 * Interactive section: the price calculator (Tapio 26.10.2026: "hintataulukko jossa
 * voi itse tsekata miten hinta muuttuu eri m2 tai muita palveluita ostaessa").
 * A "Me vs muut" comparison was built too and removed on 30.10.2026, since it
 * was not going to be used.
 *
 *   [hintalaskuri]    price estimate by size, package and extra services.
 *
 * The shortcode prints the two columns of a `.mh-text-sec` (head on the left,
 * body on the right), so a page wraps it like the other text sections:
 *
 *   <!-- wp:muuttohaukat/landing-why {"background":"white","anchor":"hintalaskuri"} -->
 *   <!-- wp:group {"className":"mh-landing__inner mh-text-sec"} -->
 *   <div class="wp-block-group mh-landing__inner mh-text-sec">
 *     <!-- wp:shortcode -->[hintalaskuri]<!-- /wp:shortcode -->
 *   </div>
 *   <!-- /wp:group --><!-- /wp:muuttohaukat/landing-why -->
 *
 * Every number comes from the theme's own tables: price_rows() (price page),
 * box_rental_products() and box_delivery() (box hub), so a price change in the
 * theme updates the calculator too. The full service multiplier is the 2.15
 * the price table uses. The table's last row (over 100 m²) is shown in steps,
 * see INTERACTIVE_LARGE_HOME_STEPS.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/** Full service = this multiple of the base price, rounded to 5 € (tayden-palvelun-muutto page). */
const INTERACTIVE_FULL_SERVICE_MULTIPLIER = 2.15;

/** The largest size the calculator takes (the end of the size slider), in m². */
const INTERACTIVE_SIZE_MAX = 150;

/**
 * "Muutan toiseen kaupunkiin" (Tapio 9.11.2026): when the two cities are more than 50 km apart, the estimate gets
 * 0.80 € for every kilometre between them (the whole distance, not only the part over 50 km), counted both ways because
 * the trucks drive there and back ("Laskuriin km tuplana koska autot menee edestakaisin", same day). The distance is
 * estimated, not routed: straight line between the municipalities' centre points (assets/data/kunnat.json, the 308
 * municipalities of Finland from Wikidata) times 1.15, which is what real road distances between Finnish cities
 * average (1.05–1.19 for Helsinki–Tampere, Turku, Oulu, Rovaniemi and others).
 */
const INTERACTIVE_CITY_MIN_KM = 50;
const INTERACTIVE_CITY_EUROS_PER_KM = 0.8;
const INTERACTIVE_ROAD_FACTOR = 1.15;
/** The distance is charged for the way there and back. */
const INTERACTIVE_CITY_TRIPS = 2;

/** [name, latitude, longitude] of every Finnish municipality, for the city fields. */
function interactive_municipalities(): array {
  $file = get_template_directory() . '/assets/data/kunnat.json';
  $rows = is_readable($file) ? json_decode((string) file_get_contents($file), true) : null;
  return is_array($rows) ? $rows : [];
}

/**
 * The price table's last row ("yli 100 m²") is one wide range, which the size slider would show unchanged from 101 to
 * 150 m² (Tapio 3.11.2026: "Hintalaskuriin 100m2+ joku tarkennus"; he approved these steps the same day). A step is
 * [from m², to m², where its low end sits, where its high end sits], the last two as shares of the row's range, so the
 * outer ends stay the published ones. With the row 1000–2000 € this gives 101–115 m² 1000–1400 €, 116–130 m² 1200–1700 €
 * and 131–150 m² 1400–2000 €. The price with boxes keeps the row's own difference to the basic price (+100 € at the low
 * end, +200 € at the high end). The price page and the city pages are not touched: they say "1 000–2 000 € tai enemmän".
 */
const INTERACTIVE_LARGE_HOME_STEPS = [
  [101, 115, 0.0, 0.4],
  [116, 130, 0.2, 0.7],
  [131, INTERACTIVE_SIZE_MAX, 0.4, 1.0],
];

/** "300-500 €" -> [300, 500]; null when the text holds no range. */
function interactive_parse_range($text): ?array {
  if (!is_string($text) || !preg_match('/(\d+)\s*[-–]\s*(\d+)/u', $text, $m)) {
    return null;
  }
  return [(int) $m[1], (int) $m[2]];
}

/** "0,19 €" -> 0.19; null when no number. */
function interactive_parse_euros($text): ?float {
  if (!is_string($text) || !preg_match('/\d+(?:[.,]\d+)?/', $text, $m)) {
    return null;
  }
  return (float) str_replace(',', '.', $m[0]);
}

/**
 * The steps of the price table's open-ended last row, from its two ranges (INTERACTIVE_LARGE_HOME_STEPS), rounded to
 * 50 € like the table's own prices.
 *
 * @param int[] $basic [low, high] without boxes.
 * @param int[] $boxes [low, high] with boxes.
 */
function interactive_large_home_steps(array $basic, array $boxes): array {
  $span = $basic[1] - $basic[0];
  $extra = [$boxes[0] - $basic[0], $boxes[1] - $basic[1]];
  $at = fn(float $share): int => (int) (round(($basic[0] + $share * $span) / 50) * 50);
  $steps = [];
  foreach (INTERACTIVE_LARGE_HOME_STEPS as [$from, $to, $low, $high]) {
    $perus = [$at($low), $at($high)];
    $steps[] = ['min' => $from, 'max' => $to, 'perus' => $perus, 'laatikot' => [$perus[0] + $extra[0], $perus[1] + $extra[1]]];
  }
  return $steps;
}

/**
 * Price classes for the calculator, from price_rows(). Returns [] when any row
 * lacks a size or a price, in which case the calculator is not printed.
 */
function interactive_price_classes(): array {
  $classes = [];
  foreach (price_rows() as $row) {
    $size = (string) ($row['size'] ?? '');
    if (preg_match('/^(\d+)\s*[-–]\s*(\d+)/u', $size, $m)) {
      $min = (int) $m[1];
      $max = (int) $m[2];
    } elseif (stripos($size, 'yli') === 0 && preg_match('/(\d+)/', $size, $m)) {
      $min = (int) $m[1] + 1;
      $max = null;
    } else {
      return [];
    }
    $basic = interactive_parse_range($row['basic'] ?? null);
    $boxes = interactive_parse_range($row['boxes'] ?? null);
    if (!$basic || !$boxes) {
      return [];
    }
    if ($max === null && $min === INTERACTIVE_LARGE_HOME_STEPS[0][0]) {
      // The open-ended last row ("yli 100 m²") is shown in steps. A row that starts elsewhere stays one class.
      $classes = array_merge($classes, interactive_large_home_steps($basic, $boxes));
      continue;
    }
    $classes[] = ['min' => $min, 'max' => $max, 'perus' => $basic, 'laatikot' => $boxes];
  }
  return $classes;
}

/**
 * Internal pages the calculator links to: key => [label, address]. A page that does not exist (or is not published)
 * is left out, so a link is never broken.
 */
function interactive_page_links(): array {
  $pages = [
    'kotimuutto' => ['Kotimuutto', 'kotimuutto'],
    'hinnasto' => ['Muuttopalvelun hinta', 'kotimuutto/muuttopalvelun-hinta'],
    'taysi' => ['Täyden palvelun muutto', 'kotimuutto/tayden-palvelun-muutto'],
    'laatikot' => ['Muuttolaatikot ja muuttotarvikkeet', 'muuttolaatikot-ja-muuttotarvikkeet'],
    'siivous' => ['Muuttosiivous', 'muuttosiivous'],
    'piano' => ['Piano- ja kassakaappikuljetus', 'kassakaappikuljetus'],
    'kaluste' => ['Kalusteasennus', 'yritysmuutto/kalusteasennus'],
    'varastointi' => ['Varastointi (Pelican Self Storage)', 'varastointi'],
  ];
  $links = [];
  foreach ($pages as $key => [$label, $path]) {
    $page = get_page_by_path($path, OBJECT, 'page');
    if ($page && $page->post_status === 'publish') {
      $links[$key] = [$label, get_permalink($page)];
    }
  }
  if (isset($links['hinnasto'])) {
    // The price page's "Hinnan ehdot" section: VAT, insurance, cancellation and the household deduction.
    $links['ehdot'] = ['Hinnan ehdot', $links['hinnasto'][1] . '#hinnan-ehdot'];
  }
  return apply_filters('mh_interactive_page_links', $links);
}

/** Everything the calculator script needs, as one object. */
function interactive_calculator_data(): array {
  $classes = interactive_price_classes();
  if (!$classes) {
    return [];
  }

  $rent = interactive_parse_euros(box_rental_products()[0]['price'] ?? null) ?? 0.19;

  $trips = [];
  foreach (box_delivery()['prices'] ?? [] as $row) {
    $label = mb_strtolower((string) ($row['label'] ?? ''));
    $euros = interactive_parse_euros($row['price'] ?? null);
    if ($euros === null) {
      $trips[] = ['id' => sanitize_title($label), 'nimi' => $label . ' (tuntiveloitus, pyydä tarjous)', 'laskematon' => true];
    } else {
      $trips[] = ['id' => sanitize_title($label), 'nimi' => $label, 'suuntaEuroa' => $euros];
    }
  }
  $trips[] = ['id' => 'nouto', 'nimi' => 'Noudan itse toimipisteestä', 'suuntaEuroa' => 0];

  $data = [
    'linkit' => [
      'tarjous' => home_url('/tarjouspyynto/kotimuutto/'),
      'tilaaHeti' => 'https://tilaamuutto.fi/',
      'sivut' => array_map(fn($l) => $l[1], interactive_page_links()),
    ],
    'koko' => ['min' => 20, 'max' => INTERACTIVE_SIZE_MAX, 'oletus' => 50],
    // The site says "alle 80 m²" for ordering directly at tilaamuutto.fi.
    'tilaaHetiEnintaan' => 79,
    'taydenPalvelunKerroin' => INTERACTIVE_FULL_SERVICE_MULTIPLIER,
    'pyoristysEuroa' => 5,
    'hintaluokat' => $classes,
    'kaupunkimatka' => [
      'rajaKm' => INTERACTIVE_CITY_MIN_KM,
      'euroaKm' => INTERACTIVE_CITY_EUROS_PER_KM,
      'suuntia' => INTERACTIVE_CITY_TRIPS,
      'tieKerroin' => INTERACTIVE_ROAD_FACTOR,
      'kaupungit' => interactive_municipalities(),
      // The bigger cities come first in the suggestions (and are what an empty field offers).
      'suositut' => ['Helsinki', 'Espoo', 'Tampere', 'Vantaa', 'Oulu', 'Turku', 'Jyväskylä', 'Lahti', 'Kuopio', 'Pori', 'Kouvola', 'Joensuu', 'Lappeenranta', 'Hämeenlinna', 'Vaasa', 'Seinäjoki', 'Rovaniemi', 'Mikkeli', 'Kotka', 'Salo', 'Porvoo', 'Kokkola', 'Hyvinkää', 'Lohja', 'Järvenpää', 'Rauma', 'Kajaani', 'Kerava', 'Savonlinna', 'Nokia', 'Kirkkonummi', 'Tuusula', 'Kemi', 'Imatra', 'Riihimäki', 'Raasepori', 'Kangasala', 'Ylöjärvi', 'Nurmijärvi', 'Sipoo', 'Vihti', 'Raisio', 'Naantali', 'Kaarina', 'Hollola', 'Iisalmi', 'Tornio'],
    ],
    'paketit' => [
      ['id' => 'perus', 'nimi' => 'Muuttopalvelu', 'kuvaus' => 'Perusmuutto ilman laatikoita', 'laatikot' => false, 'taysi' => false],
      ['id' => 'laatikot', 'nimi' => 'Muutto ja laatikot', 'kuvaus' => 'Perusmuutto ja muuttolaatikot', 'laatikot' => true, 'taysi' => false],
      ['id' => 'taysi', 'nimi' => 'Täyden palvelun muutto', 'kuvaus' => 'Pakkaus, kuljetus ja purku', 'laatikot' => false, 'taysi' => true],
      ['id' => 'taysi-laatikot', 'nimi' => 'Täyden palvelun muutto ja laatikot', 'kuvaus' => 'Pakkaus, kuljetus ja purku sekä laatikot', 'laatikot' => true, 'taysi' => true],
    ],
    // Only "keskimäärin 200–400 € yksiössä tai kaksiossa" is published for cleaning.
    // Kotitalousvähennys 2026–27: 40 %, deductible 150 €, at most 2 100 € per person.
    'siivous' => [
      'hintaAlle' => 59,
      'hinta' => [200, 400],
      'vahennysProsentti' => 40,
      'omavastuu' => 150,
      'vahennysKatto' => 2100,
      'iso' => 'Hinta nousee koon mukaan, pyydä tarjous',
      'isoSelite' => "Keskimäärin 200–400\u{00A0}€ yksiössä tai kaksiossa. Isot asunnot ja omakotitalot voivat olla yli 1\u{00A0}000\u{00A0}€.",
    ],
    'piano' => ['teksti' => 'Hinta sovitaan aina erikseen'],
    'kaluste' => ['teksti' => 'Sovitaan tarjouksessa'],
    'varastointi' => ['teksti' => 'Varastointi onnistuu yhteistyökumppanimme Pelican Self Storagen kautta; Pelicanin asiakkaat saavat muutosta jopa 20 % alennuksen'],
    // Box rental is not asked from the visitor, it is calculated from the size: the number of boxes is the one the
    // Tilaamuutto.fi order flow recommends (its table up to 80 m², 0.7 boxes per m² above), the rental time is the
    // 14 days that flow picks (a week before and a week after the move), and delivery and pick-up cost 2 x 25 € or
    // 2 x 40 € (box_delivery()), shown as a range.
    'laatikkovuokra' => [
      'paivaHintaEuroa' => $rent,
      'oletusPaivat' => 14,
      'suuntia' => 2,
      'matkat' => $trips,
      'suositus' => [
        ['maxM2' => 29, 'laatikoita' => 20],
        ['maxM2' => 39, 'laatikoita' => 25],
        ['maxM2' => 49, 'laatikoita' => 30],
        ['maxM2' => 59, 'laatikoita' => 40],
        ['maxM2' => 69, 'laatikoita' => 50],
        ['maxM2' => 80, 'laatikoita' => 60],
      ],
      'suositusKerroin' => 0.7,
    ],
  ];

  return apply_filters('mh_interactive_data', $data);
}

/** Loads the stylesheet and script on pages that use the calculator. */
add_action('wp_enqueue_scripts', function () {
  $post = is_singular() ? get_queried_object() : null;
  if (!$post instanceof \WP_Post) {
    return;
  }
  if (!has_shortcode($post->post_content, 'hintalaskuri')) {
    return;
  }
  $uri = get_stylesheet_directory_uri();
  $version = wp_get_theme()->get('Version');
  wp_enqueue_style('muuttohaukat-interactive', $uri . '/assets/css/interactive.css', ['muuttohaukat-content'], $version);
  wp_enqueue_script('muuttohaukat-interactive', $uri . '/assets/js/interactive.js', [], $version, true);
}, 20);

/** One checkbox or radio with its label. */
function interactive_choice(string $type, string $name, string $id, string $value, string $label, string $desc = '', bool $checked = false): string {
  $text = '<span class="mhi-choice__name">' . esc_html($label) . '</span>'
    . ($desc !== '' ? '<span class="mhi-choice__desc">' . esc_html($desc) . '</span>' : '');
  return '<div class="mhi-choice">'
    . '<input type="' . esc_attr($type) . '" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . ($checked ? ' checked' : '') . '>'
    . '<label for="' . esc_attr($id) . '">' . $text . '</label></div>';
}

add_shortcode('hintalaskuri', function ($atts) {
  $data = interactive_calculator_data();
  if (!$data) {
    // A price row without a number: print nothing for visitors, a note for editors.
    return current_user_can('edit_posts')
      ? '<div class="mh-text-sec__head mhi"><p>Hintalaskuri ei näy: hinnaston (price_rows) jokaisella rivillä tulee olla koko ja hintahaarukka sekä ilman laatikoita että laatikoiden kanssa.</p></div>'
      : '';
  }

  $a = shortcode_atts([
    'kicker' => 'Hinta-arvio',
    'otsikko' => 'Laske muuton hinta-arvio',
    'johdanto' => 'Katso, miten hinta muuttuu asunnon koon ja palvelujen mukaan.',
  ], $atts, 'hintalaskuri');

  $k = $data['koko'];
  $packages = '';
  foreach ($data['paketit'] as $i => $p) {
    $packages .= interactive_choice('radio', 'mhi-paketti', 'mhi-paketti-' . $p['id'], $p['id'], $p['nimi'], $p['kuvaus'], $i === 0);
  }
  $extras = '';
  foreach (['siivous' => 'Muuttosiivous', 'piano' => 'Piano tai kassakaappi', 'kaluste' => 'Kalusteasennus', 'varastointi' => 'Varastointi'] as $value => $label) {
    $extras .= interactive_choice('checkbox', 'mhi-lisa', 'mhi-lisa-' . $value, $value, $label);
  }
  $json = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

  $head = '<div class="mh-text-sec__head mhi mhi-static" data-mhi="laskuri">'
    . '<p class="mh-landing-kicker">' . esc_html($a['kicker']) . '</p>'
    . '<h2 class="wp-block-heading">' . esc_html($a['otsikko']) . '</h2>'
    . '<p class="mhi-lead">' . esc_html($a['johdanto']) . '</p>'
    . '<div class="mhi-fields" role="group" aria-label="Hinta-arvion tiedot">'

    . '<div class="mhi-group"><label class="mhi-group__title" for="mhi-koko">Asunnon koko neliöinä</label>'
    . '<div class="mhi-size">'
    . '<div class="mhi-size__num"><input type="number" id="mhi-koko" inputmode="numeric" min="' . (int) $k['min'] . '" max="' . (int) $k['max'] . '" step="1" value="' . (int) $k['oletus'] . '" aria-describedby="mhi-koko-vihje"><span class="mhi-size__unit" aria-hidden="true">m²</span></div>'
    . '<div class="mhi-size__slider"><label class="mhi-vh" for="mhi-koko-liuku">Asunnon koko liukusäätimellä</label>'
    . '<input type="range" id="mhi-koko-liuku" min="' . (int) $k['min'] . '" max="' . (int) $k['max'] . '" step="1" value="' . (int) $k['oletus'] . '">'
    . '<div class="mhi-size__limits" aria-hidden="true"><span>' . (int) $k['min'] . "\u{00A0}m²</span><span>" . (int) $k['max'] . "\u{00A0}m²</span></div></div>"
    . '</div>'
    // The end of the slider (and a typed size above it): homes bigger than the calculator takes get a quote, not an estimate (interactive.js).
    . '<p class="mhi-hint mhi-hint--limit" id="mhi-koko-raja" role="status" hidden>Yli ' . (int) $k['max'] . "\u{00A0}m² asunnoista " . '<a href="' . esc_url($data['linkit']['tarjous']) . '">pyydä tarjous</a>.</p>'
    . '<p class="mhi-hint" id="mhi-koko-vihje" role="status" hidden>Syötä asunnon koko väliltä ' . (int) $k['min'] . '–' . (int) $k['max'] . "\u{00A0}m².</p></div>"

    // "Muutan toiseen kaupunkiin", right under the size (Tapio 9.11.2026: "Lisää täppä ylemmäs"): two city fields that
    // fill the distance fee in (interactive.js, INTERACTIVE_CITY_MIN_KM).
    . '<div class="mhi-group" data-mhi-city-group>'
    . interactive_choice('checkbox', 'mhi-kaupunki', 'mhi-kaupunki', 'kaupunki', 'Muutan toiseen kaupunkiin')
    . '<div class="mhi-city" data-mhi-city-fields hidden>'
    . '<div class="mhi-city__field"><label for="mhi-lahto">Lähtökaupunki</label>'
    . '<input type="text" id="mhi-lahto" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="mhi-lahto-lista" autocomplete="off" autocapitalize="words" spellcheck="false" placeholder="Esim. Helsinki">'
    . '<ul class="mhi-suggest" id="mhi-lahto-lista" role="listbox" aria-label="Kaupunkiehdotukset" hidden></ul></div>'
    . '<div class="mhi-city__field"><label for="mhi-kohde">Kohdekaupunki</label>'
    . '<input type="text" id="mhi-kohde" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="mhi-kohde-lista" autocomplete="off" autocapitalize="words" spellcheck="false" placeholder="Esim. Tampere">'
    . '<ul class="mhi-suggest" id="mhi-kohde-lista" role="listbox" aria-label="Kaupunkiehdotukset" hidden></ul></div>'
    . '<p class="mhi-hint mhi-city__note" data-mhi-city-note role="status"></p>'
    . '</div></div>'

    . '<fieldset class="mhi-group"><legend class="mhi-group__title">Paketti</legend>' . $packages . '</fieldset>'
    . '<fieldset class="mhi-group"><legend class="mhi-group__title">Lisäpalvelut</legend>' . $extras . '</fieldset>'

    . '<div class="mhi-group" data-mhi-rent-group>'
    . interactive_choice('checkbox', 'mhi-vuokra', 'mhi-vuokra', 'vuokra', 'Vuokraan laatikot erikseen')
    . '<p class="mhi-hint mhi-hint--rent" data-mhi-rent-note></p>'
    . '</div>'

    . '</div>'
    . '<script type="application/json" class="mhi-data">' . $json . '</script>'
    . '</div>';

  $body = '<div class="mh-text-sec__body mhi" data-mhi-result>'
    . '<div class="mhi-panel" data-mhi-panel aria-live="polite" aria-atomic="false"><p>Laskuri tarvitsee JavaScriptin toimiakseen.</p></div>'
    . '<div class="mhi-below">'
    . '<div class="mhi-buttons">'
    . '<a class="mh-painike mh-painike--black" data-mhi-quote href="' . esc_url($data['linkit']['tarjous']) . '">Pyydä tarjous</a>'
    . '<a class="mh-painike mh-painike--yellow" data-mhi-order href="' . esc_url($data['linkit']['tilaaHeti']) . '" target="_blank" rel="noopener noreferrer">Tilaa heti</a>'
    . '</div>'
    . '<p class="mhi-small">Esimerkkihinnat. Tarkka hinta pyydettäessä.</p>'
    . '</div>'
    // Floating price on phones and tablets: shown while the controls are in view and the result is not (interactive.js).
    // It repeats what the result panel says, so screen readers and the keyboard skip it.
    . '<div class="mhi-bar" data-mhi-bar aria-hidden="true" hidden>'
    . '<div class="mhi-bar__sum"><span class="mhi-bar__label" data-mhi-bar-label></span><span class="mhi-bar__price" data-mhi-bar-price></span></div>'
    . '<a class="mh-painike mh-painike--black mhi-bar__btn" data-mhi-bar-quote tabindex="-1" href="' . esc_url($data['linkit']['tarjous']) . '">Pyydä tarjous</a>'
    . '</div>'
    . '</div>';

  return $head . $body;
});
