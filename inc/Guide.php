<?php
/**
 * Guide article template: "Artikkeli: opas (hero ja osiot)" (template-guide.php).
 *
 * A blog format with its own page template. It is opt-in: only a post or page whose template is chosen as this one (the
 * "Template" setting of the editor, `_wp_page_template` = template-guide.php) is drawn this way; every other article keeps
 * single-post.php or the blocks template (inc/Articles.php).
 *
 * The page is made of:
 *   - a hero: title, standfirst, facts (reading time, number of tools, updated date), two buttons and a photo
 *   - sections that alternate brand yellow and white from the hero down. The content is ordinary Gutenberg content: every
 *     top-level H2 starts a section. Options go in the H2 block's "Additional CSS class(es)": `sec-wide` (heading above, the
 *     body over the full width, for tools and tables), `sec-summary` (the tick list). A paragraph with the class `is-lead`
 *     straight after the H2 stays beside the heading.
 *   - the live price calculator where the content has a top-level Shortcode block `[hintalaskuri ...]` (the same shortcode as
 *     the price page, inc/InteractiveSections.php, so prices stay in sync): it becomes a section of its own, always white
 *     because its result panel is yellow, and the hero's "Laske hinta-arvio" button jumps to it (#hintalaskuri)
 *   - a closing call to action and "Lue myös" cards, from the post meta
 *   - FAQPage structured data: inc/StructuredData.php prints it for any page whose content has an mh-faq block
 *   - a trust bar under the hero (meta _mh_guide_trust)
 *
 * Section options (H2 class names) and opt-in components (html blocks in the content):
 *   sec-toc     the section's body is a table of contents built from the other sections' headings
 *   sec-panel   two columns: the heading and all text left, the block `<ul class="mh-panel">` right. Panel items are
 *               `<li data-icon="shield"><strong>Title</strong><span>Text</span></li>` (max 7, icon names in guide_icon())
 *   .mh-cards   card grid: `<div class="mh-cards"><article class="mh-card"><span class="mh-card__chip">..</span><h3>..</h3><p>..</p></article>..</div>`
 *   .mh-steps   timeline: `<ol class="mh-steps"><li><span class="mh-steps__chip">..</span><h3>..</h3><p>..</p></li>..</ol>`
 *   .mh-example two cards that compare numbers: `<div class="mh-example"><div class="mh-example__card"><h3>..</h3><dl><div><dt>..</dt><dd>..</dd></div>..</dl></div>..</div>`
 *   .mh-template a copyable message (button [data-copy], text in [data-template], highlighted .mh-fill parts)
 *   .mh-vs      the "Muuttohaukat vs toinen tarjous" card (six rows, Kyllä / Ei / En tiedä)
 *   .mh-stack   on a table: one card per row on phones (cells need data-label)
 *   [laatikkoarvio]  the box estimator (a Shortcode block inside a section): how many boxes, rental cost, order link; numbers from the
 *               price calculator's data, see guide_estimator_data()
 *   - styles for small interactive blocks that come with the content (.mh-vs "Muuttohaukat vs toinen tarjous", .mh-tool, .mh-tabs)
 *
 * Post meta (all optional, set by the script that creates the article):
 *   _mh_guide_standfirst   the lead paragraph under the title
 *   _mh_guide_image        attachment ID of the hero photo, _mh_guide_image_alt its alt text
 *   _mh_guide_facts        JSON [{"value":"9 min","label":"lukuaika","icon":"clock"}, ...]; default: reading time, tools, updated
 *   _mh_guide_cta          JSON {"title":..., "text":..., "image":attachment ID, "alt":..., "buttons":[{"label","href","style"}]}
 *   _mh_guide_actions      JSON [{"label":..,"href":..,"style":"yellow|black|outline"}] the hero buttons (default: price calculator and quote)
 *   _mh_guide_related      JSON [{"id":post ID,"title":optional,"text":optional,"image":attachment ID}, ...]
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

const GUIDE_TEMPLATE = 'template-guide.php';

function guide_is_active(): bool {
  $post = is_singular() ? get_queried_object() : null;

  return $post instanceof \WP_Post && get_page_template_slug($post) === GUIDE_TEMPLATE;
}

add_action('wp_enqueue_scripts', function () {
  if (!guide_is_active()) {
    return;
  }
  $file = '/assets/css/guide.css';
  $path = get_stylesheet_directory() . $file;
  wp_enqueue_style('muuttohaukat-guide', get_stylesheet_directory_uri() . $file, ['muuttohaukat-base', 'muuttohaukat-painike'], file_exists($path) ? (string) filemtime($path) : null);
}, 20);

add_filter('body_class', function ($classes) {
  return guide_is_active() ? array_merge($classes, ['mh-guide-page']) : $classes;
});

/** Does the article carry the live price calculator ([hintalaskuri])? */
function guide_has_calculator(\WP_Post $post): bool {
  return has_shortcode($post->post_content, 'hintalaskuri');
}

/**
 * The box estimator, shortcode [laatikkoarvio]: a card with four questions (size in m², more things than usual, rental days,
 * delivery) and a result that shows how many boxes, what the rental costs and a link to order. The numbers are NOT its own: the
 * recommendation table, the 0.7 boxes per m² above the table, the day price, the default 14 days and the delivery prices come from
 * the price calculator's data (interactive_calculator_data(), key laatikkovuokra), so the article and the calculator always agree
 * and a price change in the theme reaches both. The rule is printed openly under the card. Only the "more things than usual"
 * step is the site's own estimate (GUIDE_ESTIMATE_MORE: +20 %, rounded up to five boxes) and is labelled as such. The script
 * (assets/js/box-estimator.js) is loaded only on pages that use the shortcode; it pushes box_estimator_* events to the dataLayer.
 */
const GUIDE_ESTIMATE_MORE  = 1.2;
const GUIDE_ESTIMATE_ROUND = 5;

function guide_estimator_data(): array {
  $data = function_exists(__NAMESPACE__ . '\\interactive_calculator_data') ? interactive_calculator_data() : [];
  $box  = $data['laatikkovuokra'] ?? null;
  if (!$box || empty($box['suositus'])) {
    return [];
  }
  $trips = [];
  foreach ($box['matkat'] ?? [] as $trip) {
    $name = ucfirst((string) ($trip['nimi'] ?? ''));
    if (!empty($trip['laskematon'])) {
      $trips[] = ['id' => (string) $trip['id'], 'label' => 'Yli 30 km: tuntiveloitus, pyydä tarjous', 'cents' => null];
    } elseif ((float) ($trip['suuntaEuroa'] ?? 0) > 0) {
      $trips[] = ['id' => (string) $trip['id'], 'label' => $name . ': ' . number_format((float) $trip['suuntaEuroa'], 0, ',', '') . "\u{00A0}€ suunta", 'cents' => (int) round((float) $trip['suuntaEuroa'] * 100)];
    } else {
      $trips[] = ['id' => (string) $trip['id'], 'label' => 'Noudan itse toimipisteestä', 'cents' => 0];
    }
  }

  return [
    'rentCents'  => (int) round((float) ($box['paivaHintaEuroa'] ?? 0.19) * 100),
    'days'       => (int) ($box['oletusPaivat'] ?? 14),
    'directions' => (int) ($box['suuntia'] ?? 2),
    'rows'       => array_map(fn($r) => ['maxM2' => (int) $r['maxM2'], 'boxes' => (int) $r['laatikoita']], $box['suositus']),
    'factor'     => (float) ($box['suositusKerroin'] ?? 0.7),
    'trips'      => $trips,
    'min'        => (int) ($data['koko']['min'] ?? 20),
    'max'        => (int) ($data['koko']['max'] ?? 150),
    'start'      => (int) ($data['koko']['oletus'] ?? 50),
    'more'       => GUIDE_ESTIMATE_MORE,
    'round'      => GUIDE_ESTIMATE_ROUND,
    'order'      => defined(__NAMESPACE__ . '\\BOX_ORDER_URL') ? BOX_ORDER_URL : 'https://tilaamuutto.fi/tilaus/laatikot',
    'boxPage'    => home_url('/muuttolaatikot-ja-muuttotarvikkeet/'),
  ];
}

/** Money as text: 106,40 € or 50 € (a non-breaking space before the euro sign). */
function guide_estimator_money(int $cents): string {
  return ($cents % 100 === 0 ? (string) ($cents / 100) : number_format($cents / 100, 2, ',', '')) . "\u{00A0}€";
}

/** The recommendation for a size, the same function as suositusLaatikot() in interactive.js. */
function guide_estimator_boxes(array $d, int $m2): int {
  foreach ($d['rows'] as $row) {
    if ($m2 <= $row['maxM2']) {
      return $row['boxes'];
    }
  }

  return (int) round($m2 * $d['factor']);
}

/** The rule as text, printed under the card ("Näin luku lasketaan"), with one worked example. */
function guide_estimator_rule_html(array $d): string {
  $rows = '';
  $prev = $d['min'];
  foreach ($d['rows'] as $row) {
    $rows .= '<li><strong>' . ($prev === $d['min'] ? 'Alle ' . ($row['maxM2'] + 1) : $prev . '–' . $row['maxM2']) . "\u{00A0}m²:</strong> " . $row['boxes'] . ' laatikkoa</li>';
    $prev  = $row['maxM2'] + 1;
  }
  $last  = end($d['rows']);
  $rows .= '<li><strong>Yli ' . $last['maxM2'] . "\u{00A0}m²:</strong> " . str_replace('.', ',', (string) $d['factor']) . ' laatikkoa neliötä kohden, pyöristettynä</li>';
  $step  = '';
  $over  = guide_estimator_boxes($d, $last['maxM2'] + 1);
  if ($over < $last['boxes']) {
    $step = 'Raja on porrasmainen: ' . ($last['maxM2'] + 1) . "\u{00A0}m² kodille suositus on " . $over . ' laatikkoa, kun ' . $last['maxM2'] . "\u{00A0}m² kodille se on " . $last['boxes'] . '.';
  }
  $start   = guide_estimator_boxes($d, $d['start']);
  $rent    = $start * $d['days'] * $d['rentCents'];
  $trip    = $d['trips'][0]['cents'] ?? null;
  $example = '';
  if ($trip) {
    $example = '<p>Esimerkki: ' . $d['start'] . "\u{00A0}m², tavallinen tavaramäärä, " . $d['days'] . ' päivää ja toimitus alle 12' . "\u{00A0}km: " . $start . ' laatikkoa, vuokra ' . guide_estimator_money($rent)
      . ', toimitus ja nouto ' . guide_estimator_money($trip * $d['directions']) . ', yhteensä ' . guide_estimator_money($rent + $trip * $d['directions']) . '.</p>';
  }

  return '<details class="mh-est__how"><summary>Näin luku lasketaan</summary>'
    . '<p>Perusluku on Muuttohaukkojen verkkotilauspalvelun, Tilaamuutto.fi:n, tilauslomakkeen suositus asunnon koon mukaan. Sama suositus on Muuttohaukkojen hintalaskurissa.</p>'
    . '<ul>' . $rows . '</ul>'
    . ($step !== '' ? '<p>' . $step . '</p>' : '')
    . '<p>Vuokra on laatikkomäärä × vuokrapäivät × päivähinta, ja toimitus ja nouto hinnoitellaan suunnittain. Valinta "tavallista enemmän tavaraa" lisää perusmäärään '
    . (int) round(($d['more'] - 1) * 100) . "\u{00A0}% ja pyöristää ylöspäin " . (int) $d['round'] . ' laatikon tarkkuuteen. Tämä lisäys on Muuttohaukkojen oma arvio, ei mittaustulos.</p>'
    . $example
    . '</details>';
}

add_shortcode('laatikkoarvio', function () {
  $d = guide_estimator_data();
  if (!$d) {
    return '';
  }
  $uri     = get_stylesheet_directory_uri();
  $path    = get_stylesheet_directory() . '/assets/js/box-estimator.js';
  wp_enqueue_script('muuttohaukat-box-estimator', $uri . '/assets/js/box-estimator.js', [], file_exists($path) ? (string) filemtime($path) : null, true);
  $trip_options = '';
  foreach ($d['trips'] as $i => $trip) {
    $trip_options .= '<option value="' . esc_attr($trip['id']) . '"' . ($i === 0 ? ' selected' : '') . '>' . esc_html($trip['label']) . '</option>';
  }
  $day_options = '';
  foreach ([7, 14, 21, 28] as $days) {
    $day_options .= '<option value="' . $days . '"' . ($days === $d['days'] ? ' selected' : '') . '>' . $days . ' päivää</option>';
  }
  $json = esc_attr(wp_json_encode($d, JSON_UNESCAPED_UNICODE));

  return '<div class="mh-est" id="laatikkolaskuri-kortti" data-est="' . $json . '">'
    . '<div class="mh-est__grid">'
    . '<form class="mh-est__form" onsubmit="return false">'
    . '<div class="mh-est__field"><label for="mh-est-m2">Asunnon koko</label>'
    . '<div class="mh-est__size"><input id="mh-est-m2" type="number" inputmode="numeric" min="' . (int) $d['min'] . '" max="' . (int) $d['max'] . '" step="1" value="' . (int) $d['start'] . '" data-est-control="size"><span aria-hidden="true">m²</span></div>'
    . '<input class="mh-est__range" id="mh-est-range" type="range" min="' . (int) $d['min'] . '" max="' . (int) $d['max'] . '" step="1" value="' . (int) $d['start'] . '" aria-label="Asunnon koko liukusäätimellä" data-est-control="size">'
    . '<p class="mh-est__hint" id="mh-est-hint" hidden></p></div>'
    . '<fieldset class="mh-est__field mh-est__choice"><legend>Onko kodissa tavallista enemmän tavaraa?</legend>'
    . '<p class="mh-est__note">Esimerkiksi täysi kirjahylly, paljon astioita, harrastusvälineitä tai varasto tai autotalli täynnä tavaraa.</p>'
    . '<div class="mh-est__opts"><input type="radio" name="mh-est-more" id="mh-est-more-0" value="0" checked data-est-control="more"><label for="mh-est-more-0">Ei</label>'
    . '<input type="radio" name="mh-est-more" id="mh-est-more-1" value="1" data-est-control="more"><label for="mh-est-more-1">Kyllä</label></div></fieldset>'
    . '<div class="mh-est__field"><label for="mh-est-days">Vuokra-aika</label><select id="mh-est-days" data-est-control="days">' . $day_options . '</select></div>'
    . '<div class="mh-est__field"><label for="mh-est-trip">Toimitus ja nouto</label><select id="mh-est-trip" data-est-control="trip">' . $trip_options . '</select></div>'
    . '</form>'
    . '<div class="mh-est__result" role="status" aria-live="polite"><p class="mh-est__placeholder">Valitse asunnon koko, niin näet suosituksen. Ilman JavaScriptiä suositukset näkyvät taulukossa yllä.</p></div>'
    . '</div>'
    . guide_estimator_rule_html($d)
    . '</div>';
});

/**
 * Buttons of the hero and of the closing box: [{"label":"..","href":"/path or #anchor or https://..","style":"yellow|black|outline"}, ...]
 * from the post meta `_mh_guide_actions` and the meta `_mh_guide_cta` key `buttons`; the defaults when the meta has none.
 */
function guide_buttons_html(array $buttons, array $defaults): string {
  $out = '';
  foreach ($buttons ?: $defaults as $button) {
    $label = trim((string) ($button['label'] ?? ''));
    $href  = trim((string) ($button['href'] ?? ''));
    if ($label === '' || $href === '') {
      continue;
    }
    $style = in_array($button['style'] ?? '', ['yellow', 'black', 'outline'], true) ? $button['style'] : 'black';
    $href  = $href[0] === '/' ? home_url($href) : $href;
    $out  .= '<a class="mh-painike mh-painike--' . $style . '" href="' . esc_url($href) . '">' . esc_html($label) . '</a>';
  }

  return $out;
}

/** A JSON post meta value as an array. */
function guide_meta_json(int $post_id, string $key): array {
  $raw = get_post_meta($post_id, $key, true);
  if (is_array($raw)) {
    return $raw;
  }
  $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

  return is_array($decoded) ? $decoded : [];
}

/** The article's sections: [title, id, classes, lead, body] (HTML), split at every top-level H2 block. */
function guide_sections(\WP_Post $post): array {
  $sections = [];
  $current  = null;
  $blank    = ['kind' => 'text', 'title' => '', 'id' => '', 'classes' => [], 'lead' => '', 'body' => ''];

  foreach (parse_blocks($post->post_content) as $block) {
    if ($block['blockName'] === null && trim((string) $block['innerHTML']) === '') {
      continue;
    }
    // The price calculator: a section of its own (the shortcode prints its own heading and both columns)
    if ($block['blockName'] === 'core/shortcode' && has_shortcode((string) $block['innerHTML'], 'hintalaskuri')) {
      if ($current !== null) {
        $sections[] = $current;
      }
      $source     = trim((string) $block['innerHTML']);
      $atts       = shortcode_parse_atts(trim((string) preg_replace('/^\[hintalaskuri|\]$/', '', $source)));
      $title      = is_array($atts) && !empty($atts['otsikko']) ? (string) $atts['otsikko'] : 'Hintalaskuri';
      $sections[] = array_merge($blank, ['kind' => 'calculator', 'id' => 'hintalaskuri', 'title' => $title, 'body' => $source]);
      $current    = null;
      continue;
    }
    $is_h2 = $block['blockName'] === 'core/heading' && (int) ($block['attrs']['level'] ?? 2) === 2;
    if ($is_h2) {
      if ($current !== null) {
        $sections[] = $current;
      }
      $current = [
        'kind'    => 'text',
        'title'   => trim(wp_strip_all_tags((string) $block['innerHTML'])),
        'id'      => (string) ($block['attrs']['anchor'] ?? ''),
        'classes' => array_values(array_filter(preg_split('/\s+/', (string) ($block['attrs']['className'] ?? '')))),
        'lead'    => '',
        'body'    => '',
      ];
      if (in_array('sec-toc', $current['classes'], true)) {
        $current['kind'] = 'toc';
      }
      continue;
    }
    $current ??= $blank;
    $html = render_block($block);
    // A panel section: the panel list goes right, everything else stays left under the heading
    if (in_array('sec-panel', $current['classes'], true)) {
      if ($block['blockName'] === 'core/html' && strpos((string) $block['innerHTML'], 'mh-panel') !== false) {
        $current['body'] .= $html;
      } else {
        $current['lead'] .= $html;
      }
      continue;
    }
    $is_lead = $block['blockName'] === 'core/paragraph' && strpos((string) ($block['attrs']['className'] ?? ''), 'is-lead') !== false;
    if ($is_lead && $current['body'] === '') {
      $current['lead'] .= $html;
      continue;
    }
    $current['body'] .= $html;
  }
  if ($current !== null) {
    $sections[] = $current;
  }

  return array_map(function ($section) {
    foreach (['lead', 'body'] as $part) {
      $section[$part] = guide_decorate(wp_filter_content_tags(do_shortcode($section[$part])));
    }
    if ($section['id'] === '' && $section['title'] !== '') {
      $section['id'] = sanitize_title($section['title']);
    }

    return $section;
  }, $sections);
}

/** Panel items get their icon: `<li data-icon="shield">..</li>` becomes an icon circle plus the text. */
function guide_decorate(string $html): string {
  if (strpos($html, 'data-icon') === false) {
    return $html;
  }

  return (string) preg_replace_callback('#<li data-icon="([a-z-]+)">(.*?)</li>#s', function ($m) {
    return '<li class="mh-panel__item"><span class="mh-ic">' . guide_icon($m[1]) . '</span><div class="mh-panel__txt">' . $m[2] . '</div></li>';
  }, $html);
}

/** The table of contents: every other section with a heading, the calculator included (its title from the shortcode). */
function guide_toc_html(array $sections): string {
  $items = '';
  foreach ($sections as $section) {
    if ($section['kind'] === 'toc' || in_array('sec-summary', $section['classes'], true) || in_array('sec-notoc', $section['classes'], true)) {
      continue;
    }
    $title = $section['title'];
    if ($title === '' || $section['id'] === '') {
      continue;
    }
    $items .= '<li><a href="#' . esc_attr($section['id']) . '">' . esc_html(wptexturize($title)) . '</a></li>';
  }

  return $items === '' ? '' : '<ol class="mh-toc">' . $items . '</ol>';
}

/** Minutes to read the visible text (scripts and styles left out), 200 words a minute. */
function guide_reading_minutes(string $html): int {
  $text  = preg_replace('#<(script|style|noscript)\b.*?</\1>#is', ' ', $html);
  $words = str_word_count(wp_strip_all_tags((string) $text), 0, 'äöåÄÖÅéÉ');

  return max(1, (int) ceil($words / 200));
}

function guide_icon(string $name): string {
  $paths = [
    'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    'tools'    => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2.2"/><circle cx="8" cy="17" r="2.2"/>',
    'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4M15 3v4"/>',
    'check'    => '<path d="M5 12.5l4.5 4.5L19 7"/>',
    'euro'     => '<path d="M17 6.5a6.5 6.5 0 1 0 0 11"/><path d="M5 10.5h8M5 13.5h8"/>',
    'shield'   => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-4"/>',
    'doc'      => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M9.5 12h5M9.5 16h5"/>',
    'alert'    => '<path d="M12 4l9 16H3z"/><path d="M12 10v4M12 17v.5"/>',
    'truck'    => '<path d="M3 6h11v10H3zM14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
    'box'      => '<path d="M4 8l8-4 8 4v9l-8 4-8-4z"/><path d="M4 8l8 4 8-4M12 12v9"/>',
    'pin'      => '<path d="M12 21s-6-5.5-6-10a6 6 0 1 1 12 0c0 4.5-6 10-6 10z"/><circle cx="12" cy="11" r="2.2"/>',
    'search'   => '<circle cx="11" cy="11" r="6"/><path d="M16 16l4.5 4.5"/>',
    'scale'    => '<path d="M12 4v16M6 20h12M5 7h14"/><path d="M5 7l-2.5 6a2.8 2.8 0 0 0 5 0zM19 7l-2.5 6a2.8 2.8 0 0 0 5 0z"/>',
    'percent'  => '<path d="M18 6L6 18"/><circle cx="7.5" cy="7.5" r="2"/><circle cx="16.5" cy="16.5" r="2"/>',
    'people'   => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/><path d="M16 5.5a3 3 0 0 1 0 5.5M17.5 14c2 .5 3 2.2 3 5"/>',
    'list'     => '<path d="M8 6h12M8 12h12M8 18h12"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
  ];

  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($paths[$name] ?? $paths['check']) . '</svg>';
}

/** The hero's facts: from the meta, else reading time, tools in the article and the update date. */
function guide_facts(\WP_Post $post, string $rendered): array {
  $facts = guide_meta_json($post->ID, '_mh_guide_facts');
  if ($facts) {
    // tokens in the values: {minutes} reading time, {modified} the update date
    $minutes  = guide_reading_minutes($rendered);
    $modified = get_the_modified_date('j.n.Y', $post);
    foreach ($facts as &$fact) {
      $fact['value'] = str_replace(['{minutes}', '{modified}'], [(string) $minutes, (string) $modified], (string) ($fact['value'] ?? ''));
    }
    unset($fact);

    return $facts;
  }
  $facts[] = ['value' => guide_reading_minutes($rendered) . ' min', 'label' => 'lukuaika', 'icon' => 'clock'];
  if (guide_has_calculator($post)) {
    $facts[] = ['value' => 'Hintalaskuri', 'label' => 'arvio heti tällä sivulla', 'icon' => 'tools'];
  }
  $tools = preg_match_all('/class="[^"]*\bmh-tool\b/', $rendered);
  if ($tools) {
    $facts[] = ['value' => (string) $tools, 'label' => $tools === 1 ? 'käytännön työkalu' : 'käytännön työkalua', 'icon' => 'tools'];
  }
  $facts[] = ['value' => get_the_modified_date('j.n.Y', $post), 'label' => 'päivitetty', 'icon' => 'calendar'];

  return $facts;
}

function guide_image(int $id, string $alt, string $sizes, bool $first = false): string {
  if (!$id || !get_post($id)) {
    return '';
  }
  $attrs = ['alt' => $alt, 'sizes' => $sizes, 'decoding' => 'async', 'loading' => $first ? 'eager' : 'lazy'];
  if ($first) {
    $attrs['fetchpriority'] = 'high';
  }

  return (string) wp_get_attachment_image($id, 'large', false, $attrs);
}

/**
 * Article structured data: headline, description, dates, hero image, publisher (Muuttohaukat Oy) and the author. The author is the
 * organisation until a person is named in the post meta `_mh_guide_author` ({"name":"..","jobTitle":"..","url":".."}); nothing about
 * a person is invented here. Printed only for a page that is shown (never for the password form).
 */
function guide_article_schema(\WP_Post $post): string {
  $home      = home_url('/');
  $publisher = ['@type' => 'Organization', 'name' => 'Muuttohaukat Oy', 'url' => $home, 'taxID' => '0887272-7'];
  $person    = guide_meta_json($post->ID, '_mh_guide_author');
  $author    = !empty($person['name'])
    ? array_filter(['@type' => 'Person', 'name' => (string) $person['name'], 'jobTitle' => (string) ($person['jobTitle'] ?? ''), 'url' => (string) ($person['url'] ?? ''), 'worksFor' => $publisher])
    : $publisher;
  $image     = (int) get_post_meta($post->ID, '_mh_guide_image', true);
  $data      = [
    '@context'         => 'https://schema.org',
    '@type'            => 'Article',
    '@id'              => get_permalink($post) . '#artikkeli',
    'mainEntityOfPage' => get_permalink($post),
    'headline'         => wp_strip_all_tags($post->post_title),
    'description'      => (string) get_post_meta($post->ID, '_genesis_description', true),
    'inLanguage'       => 'fi',
    'datePublished'    => get_the_date('c', $post),
    'dateModified'     => get_the_modified_date('c', $post),
    'author'           => $author,
    'publisher'        => $publisher,
  ];
  if ($image && ($url = wp_get_attachment_image_url($image, 'large'))) {
    $data['image'] = $url;
  }

  return '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>';
}

/** The trust bar under the hero: [{"icon":"shield","text":".."}, ...] from the meta _mh_guide_trust. */
function guide_trust_html(\WP_Post $post): string {
  $items = guide_meta_json($post->ID, '_mh_guide_trust');
  $out   = '';
  foreach ($items as $item) {
    $text = trim((string) ($item['text'] ?? ''));
    if ($text === '') {
      continue;
    }
    $out .= '<li>' . guide_icon((string) ($item['icon'] ?? 'check')) . '<span>' . esc_html($text) . '</span></li>';
  }

  return $out === '' ? '' : '<ul class="mh-guide-trust__list">' . $out . '</ul>';
}

/** "Lue myös" cards. */
function guide_cards_html(array $items): string {
  $out = '';
  foreach ($items as $item) {
    $target = get_post((int) ($item['id'] ?? 0));
    if (!$target instanceof \WP_Post) {
      continue;
    }
    $title = (string) ($item['title'] ?? '') !== '' ? (string) $item['title'] : $target->post_title;
    $text  = (string) ($item['text'] ?? '');
    $image = guide_image((int) ($item['image'] ?? 0), '', '(max-width: 640px) 100vw, (max-width: 1100px) 50vw, 400px');
    $link  = esc_url(get_permalink($target));
    $out  .= '<li class="mh-guide-card">'
      . ($image !== '' ? '<a class="mh-guide-card__media" href="' . $link . '" tabindex="-1" aria-hidden="true">' . $image . '</a>' : '')
      . '<h3 class="mh-guide-card__title"><a href="' . $link . '">' . esc_html($title) . '</a></h3>'
      . ($text !== '' ? '<p class="mh-guide-card__text">' . esc_html($text) . '</p>' : '')
      . '</li>';
  }

  return $out === '' ? '' : '<ul class="mh-guide-cards">' . $out . '</ul>';
}
