<?php
/**
 * Shared data for the city (paikkakunta) landing blocks.
 *
 * One place for the four offices and the price list, so a change is made
 * once and every city page follows. Both arrays are filterable, and both
 * are handed to the block editor as window.mhOffices / window.mhPrices so
 * the editor preview matches the front end without a REST round trip.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * The four offices. Addresses and postcodes match the Google profiles.
 *
 * @return array<string, array<string, mixed>>
 */
function offices(): array {
  return apply_filters('mh_offices', [
    'hyvinkaa' => [
      'key'     => 'hyvinkaa',
      'label'   => 'Hyvinkään toimipiste',
      'from'    => 'Hyvinkään toimipisteestä',
      'street'  => 'Kerkkolankatu 31',
      'zip'     => '05800',
      'city'    => 'Hyvinkää',
      'phone'   => '010 400 4500',
      'area'    => 'Uusimaa, Kanta-Häme ja Päijät-Häme',
      'page'    => '/hyvinkaa-uusimaa/',
      'profile' => '',
      'staff'   => 'hyvinkaa-henkilot',
      'reviews' => 7649, // Business Reviews Bundle collection "Hyvinkää"
    ],
    'vantaa' => [
      'key'     => 'vantaa',
      'label'   => 'Vantaan toimipiste',
      'from'    => 'Vantaan toimipisteestä',
      'street'  => 'Taivaltie 4',
      'zip'     => '01610',
      'city'    => 'Vantaa',
      'phone'   => '010 400 4500',
      'area'    => 'pääkaupunkiseutu',
      'page'    => '/paakaupunkiseutu-helsinki-vantaa-espoo/',
      'profile' => '',
      'staff'   => 'pk-seudun-henkilot',
      'reviews' => 7652, // "Helsinki PK seutu"
    ],
    'tampere' => [
      'key'     => 'tampere',
      'label'   => 'Tampereen toimipiste',
      'from'    => 'Tampereen toimipisteestä',
      'street'  => 'Tietohallinnonkatu 17',
      'zip'     => '33840',
      'city'    => 'Tampere',
      'phone'   => '010 400 4500',
      'area'    => 'Pirkanmaa',
      'page'    => '/tampere-pirkanmaa/',
      'profile' => '',
      'staff'   => 'tampere-henkilot',
      'reviews' => 6503, // "Muuttohaukat Oy Tampere"
    ],
    'turku' => [
      'key'     => 'turku',
      'label'   => 'Turun toimipiste',
      'from'    => 'Turun toimipisteestä',
      'street'  => 'Lemminkäisenkatu 36',
      'zip'     => '20520',
      'city'    => 'Turku',
      'phone'   => '010 400 4500',
      'area'    => 'Varsinais-Suomi',
      'page'    => '/turku-varsinais-suomi/',
      'profile' => '',
      'staff'   => 'turku-henkilot',
      'reviews' => 7643, // "Turku"
    ],
  ]);
}

/**
 * One office by key, or null when the key is unknown.
 */
function office(string $key): ?array {
  $all = offices();
  return $all[$key] ?? null;
}

/**
 * The office's people: the person posts of its staff group (inc/Staff.php),
 * the same list the office page shows, so staff changes are made once, in
 * Henkilöt.
 */
function office_staff(array $office): ?object {
  return staff_settings((string) ($office['staff'] ?? '')); // inc/Offices.php
}

/**
 * The people row of the Paikallinen palvelu section.
 */
function office_staff_html(array $office): string {
  $staff = office_staff($office);
  // The section has its own kicker; no heading of its own.
  $people = $staff ? staff_render($staff->persons, 'paikallisesti-' . $office['key']) : '';

  return $people === ''
    ? ''
    : '<div class="mh-landing-local__staff"><p class="mh-landing-kicker">Yhteyshenkilöt</p>' . $people . '</div>';
}

/**
 * [yritysmuutto_yhteyshenkilot]: the people whose responsibilities include
 * business moves, office by office. Used on /yritysmuutto/ (audit action 7:
 * who is responsible).
 */
function business_staff_html(): string {
  $html = '';
  foreach (offices() as $office) {
    $staff = office_staff($office);
    if (!$staff) {
      continue;
    }
    $persons = array_values(array_filter($staff->persons, fn($person) => stripos((string) ($person->responsibilities ?? ''), 'yritysmuut') !== false));
    $people = $persons ? staff_render($persons, 'yritysmuutot-' . $office['key']) : '';
    if ($people !== '') {
      $html .= '<div class="mh-business-staff__office"><p class="mh-landing-kicker">' . esc_html($office['label']) . '</p>' . $people . '</div>';
    }
  }

  return $html === '' ? '' : '<div class="mh-business-staff">' . $html . '</div>';
}

add_shortcode('yritysmuutto_yhteyshenkilot', __NAMESPACE__ . '\\business_staff_html');

/**
 * The Henkilöstö stylesheet (the old Beaver Builder module's, now in assets/css).
 */
function enqueue_staff_style(): void {
  $file = '/assets/css/henkilosto.css';
  $path = get_stylesheet_directory() . $file;

  wp_enqueue_style(
    'mh-henkilosto',
    get_stylesheet_directory_uri() . $file,
    [],
    file_exists($path) ? (string) filemtime($path) : wp_get_theme()->get('Version')
  );
}

/**
 * City pages show the reviews of their own office (audit 3.9). The shared
 * Tarjouspyyntö block holds one [brb_collection]; on a page with _mh_office
 * it is swapped for that office's collection, in the same grid view.
 * The plugin's site-wide collections (option brb_glob_colls, the flash
 * pop-up) are printed in wp_footer through the same shortcode and stay as
 * they are; swapping them put a second review grid below the footer.
 */
add_filter('pre_do_shortcode_tag', function ($output, $tag, $attr) {
  static $inside = false;
  if ($tag !== 'brb_collection' || $inside || !is_singular() || doing_action('wp_footer')) {
    return $output;
  }
  $global = array_map('intval', explode(',', (string) get_option('brb_glob_colls', '')));
  if (in_array((int) ($attr['id'] ?? 0), $global, true)) {
    return $output;
  }

  $office = office((string) get_post_meta(get_queried_object_id(), '_mh_office', true));
  if (!$office || empty($office['reviews']) || (int) ($attr['id'] ?? 0) === (int) $office['reviews']) {
    return $output;
  }

  $inside = true;
  $html = do_shortcode(sprintf('[brb_collection id="%d" view_mode="grid"]', (int) $office['reviews']));
  $inside = false;

  return $html;
}, 10, 3);

// Load it in the head on pages with the block, not late from the render.
add_action('wp_enqueue_scripts', function () {
  $post = is_singular() ? get_queried_object() : null;
  if ($post instanceof \WP_Post && (has_block('muuttohaukat/landing-local', $post) || has_shortcode($post->post_content, 'yritysmuutto_yhteyshenkilot'))) {
    enqueue_staff_style();
  }
}, 21);

/**
 * Opening hours, shown as text and as schema.org openingHours.
 */
function office_hours(): array {
  return apply_filters('mh_office_hours', [
    'text'   => 'Avoinna ma-to klo 8-16 ja pe klo 9-15.',
    'short'  => 'Ma–to 8–16, pe 9–15',
    'schema' => ['Mo-Th 08:00-16:00', 'Fr 09:00-15:00'],
  ]);
}

/**
 * Price list by apartment size, as published on /kotimuutto/muuttopalvelun-hinta/.
 *
 * A null price renders as a quote link instead of a number. The 40-59 m²
 * price without boxes is null on purpose: the price page shows 450 as both
 * the lower and the upper bound, and the real upper bound is unconfirmed.
 *
 * @return array<int, array<string, string|null>>
 */
function price_rows(): array {
  return apply_filters('mh_price_rows', [
    ['size' => '20-29 m²',  'type' => 'yksiö',       'basic' => '300-500 €',  'boxes' => '350-600 €'],
    ['size' => '40-59 m²',  'type' => 'kaksio',      'basic' => null,         'boxes' => '500-700 €'],
    ['size' => '60-79 m²',  'type' => 'kolmio',      'basic' => '600-850 €',  'boxes' => '650-950 €'],
    ['size' => '80-100 m²', 'type' => 'perheasunto', 'basic' => '800-1000 €', 'boxes' => '850-1150 €'],
  ]);
}

/**
 * MovingCompany markup for one office, tied to the city page it appears on.
 *
 * @param array  $office One row from offices().
 * @param string $city   City the page serves, used for areaServed.
 */
function office_schema(array $office, string $city = ''): array {
  $hours = office_hours();

  $schema = [
    '@context'      => 'https://schema.org',
    '@type'         => 'MovingCompany',
    '@id'           => home_url('/#toimipiste-' . $office['key']),
    'name'          => 'Muuttohaukat, ' . $office['label'],
    'legalName'     => 'Muuttohaukat Oy',
    'vatID'         => 'FI08872727',
    'telephone'     => $office['phone'],
    'url'           => home_url($office['page']),
    'address'       => [
      '@type'           => 'PostalAddress',
      'streetAddress'   => $office['street'],
      'postalCode'      => $office['zip'],
      'addressLocality' => $office['city'],
      'addressCountry'  => 'FI',
    ],
    'openingHours'  => $hours['schema'],
    'parentOrganization' => [
      '@type' => 'Organization',
      'name'  => 'Muuttohaukat Oy',
      'url'   => home_url('/'),
    ],
  ];

  if ($city !== '') {
    $schema['areaServed'] = ['@type' => 'City', 'name' => $city];
  }

  if (!empty($office['profile'])) {
    $schema['sameAs'] = [$office['profile']];
  }

  return apply_filters('mh_office_schema', $schema, $office, $city);
}

/**
 * Hand the shared data to the block editor.
 *
 * Attached to wp-blocks, which every block-editor screen loads, so the
 * handle does not depend on how WordPress names a block's script.
 */
add_action('enqueue_block_editor_assets', function () {
  $data = 'window.mhOffices = ' . wp_json_encode(offices()) . ';'
        . 'window.mhPrices = ' . wp_json_encode(price_rows()) . ';'
        . 'window.mhOfficeHours = ' . wp_json_encode(office_hours()) . ';';

  wp_add_inline_script('wp-blocks', $data, 'before');
}, 5);
