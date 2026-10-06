<?php
/**
 * Nearby municipality lists and the office card.
 *
 * The markup lives here so the Gutenberg blocks and the Beaver Builder
 * Themer layout for the `muutto` post type render exactly the same thing.
 * Per city data (office, inflected name, road distance and the nearest
 * municipalities) is stored in post meta by the audit scripts.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

const NEARBY_MAX = 15;

/**
 * Region hub page for an office key.
 */
function office_hub(string $office_key): array {
  $hubs = apply_filters('mh_office_hubs', [
    'hyvinkaa' => ['path' => '/hyvinkaa-uusimaa/', 'label' => 'Hyvinkää ja Uusimaa'],
    'vantaa'   => ['path' => '/paakaupunkiseutu-helsinki-vantaa-espoo/', 'label' => 'Pääkaupunkiseutu'],
    'tampere'  => ['path' => '/tampere-pirkanmaa/', 'label' => 'Tampere ja Pirkanmaa'],
    'turku'    => ['path' => '/turku-varsinais-suomi/', 'label' => 'Turku ja Varsinais-Suomi'],
  ]);

  return $hubs[$office_key] ?? ['path' => '', 'label' => ''];
}

/**
 * City facts saved on the post by the audit scripts.
 */
function city_facts($post_id): array {
  $links = get_post_meta($post_id, '_mh_nearby', true);

  return [
    'office' => (string) get_post_meta($post_id, '_mh_office', true),
    'city'   => (string) get_post_meta($post_id, '_mh_city', true),
    'iness'  => (string) get_post_meta($post_id, '_mh_iness', true),
    'links'  => is_array($links) ? $links : [],
  ];
}

/**
 * The office card, shared by the block and the Themer layout.
 */
function office_card_html(array $office): string {
  $hours = office_hours();

  ob_start();
  ?>
  <aside class="mh-landing-local__office">
    <p class="mh-landing-kicker"><?php echo esc_html($office['label']); ?></p>
    <p class="mh-landing-local__address">
      <?php echo esc_html($office['street']); ?><br>
      <?php echo esc_html($office['zip'] . ' ' . $office['city']); ?>
    </p>
    <p class="mh-landing-local__phone">
      <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $office['phone'])); ?>"><?php echo esc_html($office['phone']); ?></a>
    </p>
    <p class="mh-landing-local__hours"><?php echo esc_html($hours['text']); ?></p>
    <p class="mh-landing-local__link">
      <a class="mh-landing__button mh-landing__button--ghost" href="<?php echo esc_url(home_url($office['page'])); ?>">
        <?php echo esc_html($office['area']); ?>
      </a>
    </p>
  </aside>
  <?php

  return (string) ob_get_clean();
}

/**
 * A section head with the kicker and the heading.
 */
function section_head_html(string $kicker, string $heading): string {
  return '<div class="mh-landing-sec-head"><p class="mh-landing-kicker">' . esc_html($kicker)
    . '</p><h2>' . esc_html($heading) . '</h2></div>';
}

/**
 * The local service text for one city, as paragraphs.
 */
function local_paragraphs(array $office, string $city, string $iness, int $km, string $time): array {
  if ($office['city'] === $city) {
    $heading = sprintf('Muutto %s hoituu omasta toimipisteestämme', $iness);
    $first = sprintf('Toimipisteemme on %s osoitteessa %s, %s %s. Muuttoauto, kantajat ja muuttolaatikot lähtevät samasta paikasta.',
      $iness, $office['street'], $office['zip'], $office['city']);
  } elseif ($km > 0 && $km <= 120) {
    $heading = sprintf('Muutto %s hoituu %s', $iness, $office['from']);
    $first = sprintf('Muuttoauto ja kantajat lähtevät %s osoitteesta %s, %s %s.',
      $office['from'], $office['street'], $office['zip'], $office['city']);
    $first .= $time !== ''
      ? sprintf(' Matkaa on noin %d kilometriä ja ajoaika noin %s.', $km, $time)
      : sprintf(' Matkaa on noin %d kilometriä.', $km);
  } else {
    $heading = sprintf('Muutot %s ajetaan sovitun aikataulun mukaan', $iness);
    $first = sprintf('Lähin toimipisteemme on %s osoitteessa %s, %s %s. Teemme muuttoja koko Suomessa, joten saat %s saman palvelun kuin toimipisteiden lähellä.',
      $office['label'], $office['street'], $office['zip'], $office['city'], $iness);
  }

  return [
    'heading' => $heading,
    'paragraphs' => [
      $first,
      'Saat muuttoauton, kantajat ja muuttolaatikot samalta toimittajalta. Teemme myös pakkauksen, muuttosiivouksen ja varastoinnin sekä siirrämme pianon ja kassakaapin.',
    ],
  ];
}

/* -------------------------------------------------------------------------
   Beaver Builder: the `muutto` Themer layout lists every other city page.
   The audit replaces that with the nearest municipalities and the office.
   ------------------------------------------------------------------------- */

/**
 * Node ids in the "Muutto" Themer layout (post 5978).
 */
function muutto_layout_nodes(): array {
  return apply_filters('mh_muutto_layout_nodes', [
    'heading' => '5rx2ack8obh1',   // "Muut paikkakunnat"
    'grid'    => 'b8zwdclnh5j9',   // post grid listing every city page
  ]);
}

add_filter('fl_builder_render_module_content', function ($content, $module) {
  if (!is_singular('muutto')) {
    return $content;
  }

  $nodes = muutto_layout_nodes();
  $node_id = is_object($module) && isset($module->node) ? $module->node : '';
  $type = is_object($module) && isset($module->slug) ? $module->slug : '';

  $post_id = get_queried_object_id();
  $facts = city_facts($post_id);

  if (!$facts['links'] && !$facts['office']) {
    return $content;
  }

  // The old heading is replaced by the headings inside the new sections.
  if ($node_id === $nodes['heading'] || ($type === 'heading' && trim(wp_strip_all_tags((string) $content)) === 'Muut paikkakunnat')) {
    return '';
  }

  if ($node_id !== $nodes['grid'] && $type !== 'post-grid') {
    return $content;
  }

  $office = office($facts['office']);
  $iness = $facts['iness'] !== '' ? $facts['iness'] : $facts['city'];
  $html = '';

  if ($office) {
    $km = (int) get_post_meta($post_id, '_mh_route_km', true);
    $time = (string) get_post_meta($post_id, '_mh_route_time', true);
    $local = local_paragraphs($office, $facts['city'], $iness, $km, $time);

    $text = '';
    foreach ($local['paragraphs'] as $paragraph) {
      $text .= '<p>' . esc_html($paragraph) . '</p>';
    }

    $html .= '<section class="mh-landing-section mh-landing-local mh-landing-section--bg-white" id="paikallisesti">'
      . '<div class="mh-landing__inner mh-landing-local__inner">'
      . '<div class="mh-landing-local__text">' . section_head_html('Paikallinen palvelu', $local['heading']) . $text . '</div>'
      . office_card_html($office)
      . office_staff_html($office)
      . '</div>'
      . '<script type="application/ld+json">' . wp_json_encode(office_schema($office, $facts['city']), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
      . '</section>';
  }

  if ($facts['links']) {
    $html .= '<section class="mh-landing-section mh-landing-nearby mh-landing-section--bg-white" id="lahikunnat">'
      . '<div class="mh-landing__inner">'
      . nearby_directory_html(
        section_head_html('Lähikunnat', 'Muutot myös lähikuntiin')
          . '<p>' . esc_html(sprintf('Teemme muuttoja %s ja naapurikunnissa. Valitse oma kuntasi listalta.', $iness)) . '</p>',
        array_slice($facts['links'], 0, NEARBY_MAX),
        office_hub($facts['office'])['path'],
        office_hub($facts['office'])['label']
      )
      . '</div></section>';
  }

  return $html !== '' ? $html : $content;
}, 10, 2);

/**
 * The sections above use the landing stylesheets, which are normally loaded
 * only for pages whose content holds landing blocks.
 */
add_action('wp_enqueue_scripts', function () {
  if (!is_singular('muutto')) {
    return;
  }

  $theme_uri = get_stylesheet_directory_uri();
  $version = wp_get_theme()->get('Version');
  wp_enqueue_style('muuttohaukat-landing', $theme_uri . '/assets/css/landing.css', ['muuttohaukat-base'], $version);
  wp_enqueue_style('muuttohaukat-landing-local', $theme_uri . '/blocks/landing-local/style.css', ['muuttohaukat-landing'], $version);
  wp_enqueue_style('muuttohaukat-landing-nearby', $theme_uri . '/blocks/landing-nearby/style.css', ['muuttohaukat-landing'], $version);
  enqueue_staff_style();
}, 21);
