<?php
/**
 * Interactive sections: the "Me vs muut" comparison and the price calculator
 * (Tapio 26.10.2026: "Luo luonnos interaktiivisesta osioista: 'Me vs muut' ja
 * hintataulukko jossa voi itse tsekata miten hinta muuttuu eri m2 tai muita
 * palveluita ostaessa").
 *
 *   [me_vs_muut]      filterable comparison: Muuttohaukat's facts and the
 *                     questions to ask any moving company.
 *   [hintalaskuri]    price estimate by size, package and extra services.
 *
 * Each shortcode prints the two columns of a `.mh-text-sec` (head on the left,
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
 * the price table uses. Facts in the comparison are quoted from the site's
 * pages (price page, delivery terms, environment page, front page).
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/** Full service = this multiple of the base price, rounded to 5 € (tayden-palvelun-muutto page). */
const INTERACTIVE_FULL_SERVICE_MULTIPLIER = 2.15;

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
    $classes[] = ['min' => $min, 'max' => $max, 'perus' => $basic, 'laatikot' => $boxes];
  }
  return $classes;
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
    'linkit' => ['tarjous' => home_url('/tarjouspyynto/kotimuutto/'), 'tilaaHeti' => 'https://tilaamuutto.fi/'],
    'koko' => ['min' => 20, 'max' => 150, 'oletus' => 50],
    // The site says "alle 80 m²" for ordering directly at tilaamuutto.fi.
    'tilaaHetiEnintaan' => 79,
    'taydenPalvelunKerroin' => INTERACTIVE_FULL_SERVICE_MULTIPLIER,
    'pyoristysEuroa' => 5,
    'hintaluokat' => $classes,
    'paketit' => [
      ['id' => 'perus', 'nimi' => 'Muuttopalvelu', 'kuvaus' => 'Perusmuutto ilman laatikoita', 'laatikot' => false, 'taysi' => false, 'erotusSana' => null],
      ['id' => 'laatikot', 'nimi' => 'Muutto ja laatikot', 'kuvaus' => 'Perusmuutto ja muuttolaatikot', 'laatikot' => true, 'taysi' => false, 'erotusSana' => 'laatikoilla'],
      ['id' => 'taysi', 'nimi' => 'Täyden palvelun muutto', 'kuvaus' => 'Pakkaus, kuljetus ja purku', 'laatikot' => false, 'taysi' => true, 'erotusSana' => 'täyden palvelun muutolla'],
      ['id' => 'taysi-laatikot', 'nimi' => 'Täyden palvelun muutto ja laatikot', 'kuvaus' => 'Pakkaus, kuljetus ja purku sekä laatikot', 'laatikot' => true, 'taysi' => true, 'erotusSana' => 'täyden palvelun muutolla ja laatikoilla'],
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
    'laatikkovuokra' => ['paivaHintaEuroa' => $rent, 'oletusPaivat' => 14, 'suuntia' => 2, 'matkat' => $trips],
  ];

  return apply_filters('mh_interactive_data', $data);
}

/**
 * Rows of the comparison. Facts are quoted from the site; the questions are
 * neutral and make no claim about other companies.
 *
 * @return array<int, array{topic:string,title:string,facts:string[],ask:string,link?:array{0:string,1:string}}>
 */
function interactive_comparison_rows(): array {
  $nb = "\u{00A0}";
  $rent = box_rental_products()[0]['price'] ?? '0,19 €';
  $hours = office_hours()['short'] ?? 'Ma–to 8–16, pe 9–15';

  return apply_filters('mh_interactive_comparison_rows', [
    [
      'topic' => 'hinta',
      'title' => 'Hinnan muodostuminen',
      'facts' => [
        "Julkaistu hinnasto kokoluokittain. Hinnat ovat esimerkkihintoja, ja kuluttajahinnat sisältävät ALV:n.",
        'Muutto tehdään kiinteällä kokonaishinnalla tai tuntiveloituksella, ja peruste sovitaan tarjouksessa.',
        'Täyden palvelun muutolla (pakkaus, kuljetus, purku) on oma hinnastonsa.',
      ],
      'ask' => 'Onko hinta kiinteä vai tuntiveloitus, ja onko minimiveloitusta? Sisältääkö hinta ALV:n? Mitä hintaan kuuluu, ja mitkä työt veloitetaan erikseen?',
      'link' => ['Katso hinnasto', home_url('/kotimuutto/muuttopalvelun-hinta/')],
    ],
    [
      'topic' => 'vakuutus',
      'title' => 'Vakuutukset',
      'facts' => [
        "Tiekuljetusvakuutus: korvaus tiekuljetussopimuslain mukaan, enintään 20{$nb}euroa puuttuvaa kiloa kohden.",
        'Toiminnanvastuuvakuutus kolmannelle osapuolelle aiheutuvien henkilö- ja esinevahinkojen varalta.',
      ],
      'ask' => 'Mitkä vakuutukset yrityksellä on, ja mikä on korvauksen yläraja? Voiko vakuutusehdot nähdä etukäteen?',
    ],
    [
      'topic' => 'vakuutus',
      'title' => 'Vahingot ja pakkaaminen',
      'facts' => [
        'Arvoesineille voi ottaa oman muuttovakuutuksen omalla kustannuksella.',
        'Jos pakkaaminen on asiakkaan vastuulla, muuttofirma ei vastaa puutteellisesta pakkaamisesta aiheutuneista vahingoista.',
      ],
      'ask' => 'Mitä tapahtuu, jos tavara vahingoittuu, ja miten vahinko ilmoitetaan? Mitä jää asiakkaan vastuulle, jos pakkaat itse?',
    ],
    [
      'topic' => 'palvelualue',
      'title' => 'Palvelualue',
      'facts' => [
        "Toimipisteet Hyvinkäällä, Vantaalla, Tampereella ja Turussa, avoinna " . mb_strtolower($hours) . ".",
        'Toimimme koko Suomen alueella. Lopullinen hinta riippuu muun muassa matkasta.',
      ],
      'ask' => 'Toimiiko yritys alueellasi, ja miten matka vaikuttaa hintaan?',
    ],
    [
      'topic' => 'ymparisto',
      'title' => 'Ympäristö',
      'facts' => [
        'Ekokompassi-ympäristösertifikaatti vuodesta 2013.',
        'Muuttoautoissa käytetään uusiutuvaa dieseliä (MY Diesel).',
        "Vuokralaatikot ovat uudelleenkäytettäviä, vuokra " . str_replace(' ', $nb, $rent) . " laatikolta päivässä.",
        'Toimipisteissä käytetään fossiilivapaata sähköä.',
      ],
      'ask' => 'Miten yritys voi osoittaa ympäristöväitteensä, esimerkiksi sertifikaatilla? Mitä polttoainetta muuttoautoissa käytetään?',
    ],
    [
      'topic' => 'tarjous',
      'title' => 'Tarjous',
      'facts' => [
        "Alle 60{$nb}m² asunnon muutosta saa tarjouksen välittömästi.",
        'Suuremmista asunnoista tarjous viimeistään seuraavana arkipäivänä.',
        "Tilaamuutto.fi-palvelusta voi tilata muuton suoraan, kun asunto on alle 80{$nb}m².",
        'Tarjouksen voi pyytää suoraan yritykseltä.',
      ],
      'ask' => 'Voiko tarjouksen pyytää suoraan yritykseltä, ja milloin sen saa? Mitä tarjous sisältää, ja tehdäänkö kohteeseen tutustuminen etukäteen?',
    ],
    [
      'topic' => 'tarjous',
      'title' => 'Peruutus',
      'facts' => [
        'Kotimuuton voi perua maksutta viimeistään viisi arkipäivää ennen muuttoa.',
        'Myöhemmin peruttaessa veloitetaan muuttoon varatun kapasiteetin viiden tunnin työ.',
      ],
      'ask' => 'Milloin muuton voi perua, ja mitä peruutus maksaa?',
    ],
    [
      'topic' => 'palvelut',
      'title' => 'Palvelut ja kokemus',
      'facts' => [
        "Perheyritys vuodesta 1992, yli 100{$nb}000 muuttoa, joista yli 10{$nb}000 vuonna 2025.",
        'Kotimuutto (Helppo muutto, Kevyt muutto, Oma muutto ja Pakkausmuutto eli täyden palvelun muutto), yritys- ja toimistomuutto sekä piano- ja kassakaappikuljetus.',
        'Muuttosiivous (vain siivouspaikkakuntien lähellä), kalusteasennus, muuttolaatikoiden vuokraus ja muuttotarvikkeet.',
        "Varastointi yhteistyökumppani Pelican Self Storagen kautta. Pelicanin asiakkaat saavat muutosta jopa 20{$nb}% alennuksen.",
        'Julkisen sektorin muutot Hanselin dynaamisen hankintajärjestelmän kautta.',
      ],
      'ask' => 'Kuka muuton tekee? Mitkä palvelut yritys tekee itse ja mitkä yhteistyökumppanin kautta? Onko yritys ennakkoperintärekisterissä (tärkeää kotitalousvähennyksen kannalta)?',
    ],
  ]);
}

/** Topics shown as filter checkboxes: value => label. */
function interactive_comparison_topics(): array {
  return apply_filters('mh_interactive_comparison_topics', [
    'hinta' => 'Hinta',
    'vakuutus' => 'Vakuutus',
    'palvelualue' => 'Palvelualue',
    'ymparisto' => 'Ympäristö',
    'tarjous' => 'Tarjous ja aikataulu',
    'palvelut' => 'Palvelut',
  ]);
}

/** Loads the stylesheet and script on pages that use either shortcode. */
add_action('wp_enqueue_scripts', function () {
  $post = is_singular() ? get_queried_object() : null;
  if (!$post instanceof \WP_Post) {
    return;
  }
  if (!has_shortcode($post->post_content, 'me_vs_muut') && !has_shortcode($post->post_content, 'hintalaskuri')) {
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

add_shortcode('me_vs_muut', function ($atts) {
  $a = shortcode_atts([
    'kicker' => 'Vertailu',
    'otsikko' => 'Me vs muut: mitä kannattaa verrata muuttofirmaa valitessa',
    'johdanto' => 'Valitse alta sinulle tärkeät aiheet tai lue kaikki. Oikealla ovat Muuttohaukkojen omat tiedot ja kysymykset, jotka kannattaa esittää mille tahansa muuttofirmalle.',
  ], $atts, 'me_vs_muut');

  $rows = interactive_comparison_rows();
  $topics = '';
  foreach (interactive_comparison_topics() as $value => $label) {
    $topics .= interactive_choice('checkbox', 'mhi-aihe', 'mhi-aihe-' . $value, $value, $label);
  }

  $head = '<div class="mh-text-sec__head mhi" data-mhi="vertailu">'
    . '<p class="mh-landing-kicker">' . esc_html($a['kicker']) . '</p>'
    . '<h2 class="wp-block-heading">' . esc_html($a['otsikko']) . '</h2>'
    . '<p class="mhi-lead">' . esc_html($a['johdanto']) . '</p>'
    . '<fieldset class="mhi-group"><legend class="mhi-group__title">Mikä sinulle on tärkeää?</legend>'
    . '<div class="mhi-topics">' . $topics . '</div></fieldset>'
    . '<div class="mhi-status"><p role="status" aria-live="polite" data-mhi-count>Näytetään ' . count($rows) . ' / ' . count($rows) . ' asiaa</p>'
    . '<button type="button" class="mhi-textbtn" data-mhi-reset data-hidden="true">Näytä kaikki</button></div>'
    . '</div>';

  $list = '';
  foreach ($rows as $row) {
    $facts = '';
    foreach ($row['facts'] as $fact) {
      $facts .= '<li>' . esc_html($fact) . '</li>';
    }
    $more = !empty($row['link'])
      ? '<p class="mhi-more"><a href="' . esc_url($row['link'][1]) . '">' . esc_html($row['link'][0]) . '</a></p>'
      : '';
    $list .= '<li class="mhi-compare__row" data-topic="' . esc_attr($row['topic']) . '">'
      . '<h3>' . esc_html($row['title']) . '</h3>'
      . '<dl class="mhi-parts">'
      . '<div class="mhi-part mhi-part--us"><dt>Muuttohaukat</dt><dd><ul class="mhi-facts">' . $facts . '</ul>' . $more . '</dd></div>'
      . '<div class="mhi-part mhi-part--ask"><dt>Kysy muulta yritykseltä</dt><dd>' . esc_html($row['ask']) . '</dd></div>'
      . '</dl></li>';
  }

  $body = '<div class="mh-text-sec__body mhi" data-mhi-list><ul class="mhi-compare">' . $list . '</ul></div>';

  return $head . $body;
});

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
  $trips = '';
  foreach ($data['laatikkovuokra']['matkat'] as $i => $m) {
    $trips .= '<option value="' . esc_attr($m['id']) . '"' . ($i === 0 ? ' selected' : '') . '>' . esc_html($m['nimi']) . '</option>';
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
    . '<p class="mhi-hint" id="mhi-koko-vihje" role="status" hidden>Syötä asunnon koko väliltä ' . (int) $k['min'] . '–' . (int) $k['max'] . "\u{00A0}m².</p></div>"

    . '<fieldset class="mhi-group"><legend class="mhi-group__title">Paketti</legend>' . $packages . '</fieldset>'
    . '<fieldset class="mhi-group"><legend class="mhi-group__title">Lisäpalvelut</legend>' . $extras . '</fieldset>'

    . '<div class="mhi-group" data-mhi-rent-group>'
    . interactive_choice('checkbox', 'mhi-vuokra', 'mhi-vuokra', 'vuokra', 'Vuokraan laatikot erikseen')
    . '<div class="mhi-rental" data-mhi-rent-fields hidden>'
    . '<div class="mhi-field"><label for="mhi-vuokra-maara">Laatikoiden määrä</label><input type="number" id="mhi-vuokra-maara" inputmode="numeric" min="1" max="500" step="1" value="' . (int) $k['oletus'] . '"></div>'
    . '<div class="mhi-field"><label for="mhi-vuokra-paivat">Vuokra-aika (päivää)</label><input type="number" id="mhi-vuokra-paivat" inputmode="numeric" min="1" max="90" step="1" value="' . (int) $data['laatikkovuokra']['oletusPaivat'] . '"></div>'
    . '<div class="mhi-field mhi-field--wide"><label for="mhi-vuokra-matka">Etäisyys toimipisteeltä</label><select id="mhi-vuokra-matka">' . $trips . '</select></div>'
    . '<p class="mhi-hint">Laatikoiden toimitus ja nouto ovat tiistaisin ja torstaisin.</p>'
    . '</div></div>'

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
    . '<div class="mhi-notes"><p>Hinta-arvio perustuu julkaistuun hinnastoon. Tarkka hinta sovitaan tarjouksessa.</p><p>Kuluttajahinnat sisältävät ALV:n.</p></div>'
    . '<p class="mhi-small">Esimerkkihinnat. Tarkka hinta pyydettäessä.</p>'
    . '</div></div>';

  return $head . $body;
});
