<?php
/**
 * Staff without Beaver Builder (Tapio, 22.9.2026: "Muuta sivusto niin, että
 * Beaver builderiä ei tarvita").
 *
 * The people are posts of the person type (Henkilöt in the admin): the name
 * is the title, the photo the featured image, role, responsibilities, phone
 * and e-mail the ACF "Person" fields, languages the field below, and the
 * group (office, Koko Suomi, Johto) the Henkilöryhmä taxonomy. The order
 * inside a group is the page order (menu_order). The lists render with the
 * same markup and stylesheet as the old Beaver Builder Henkilöstö module
 * (partials/staff-list.php), so they look the same.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/** Group slug => label; the office keys map through staff_group_for_slug(). */
function staff_groups(): array {
  return apply_filters('mh_staff_groups', [
    'paakaupunkiseutu' => 'Pääkaupunkiseutu',
    'hyvinkaa'         => 'Hyvinkää, Hämeenlinna ja Riihimäki',
    'tampere'          => 'Tampere ja Pirkanmaa',
    'turku'            => 'Turku, Varsinais-Suomi ja Satakunta',
    'koko-suomi'       => 'Koko Suomi',
    'johto-hr'         => 'Johto, HR, palkat ja laskutus',
  ]);
}

/** The old staff template slugs (offices()['staff']) to groups. */
function staff_group_for_slug(string $slug): string {
  $map = [
    'pk-seudun-henkilot'  => 'paakaupunkiseutu',
    'hyvinkaa-henkilot'   => 'hyvinkaa',
    'tampere-henkilot'    => 'tampere',
    'turku-henkilot'      => 'turku',
    'koko-suomi-henkilot' => 'koko-suomi',
    'johto-hr-henkilot'   => 'johto-hr',
  ];
  return $map[$slug] ?? (isset(staff_groups()[$slug]) ? $slug : '');
}

add_action('init', function () {
  register_taxonomy('henkiloryhma', ['person'], [
    'labels' => [
      'name'          => 'Henkilöryhmät',
      'singular_name' => 'Henkilöryhmä',
      'menu_name'     => 'Ryhmät',
      'all_items'     => 'Kaikki ryhmät',
      'edit_item'     => 'Muokkaa ryhmää',
      'add_new_item'  => 'Lisää ryhmä',
    ],
    'public'            => false,
    'show_ui'           => true,
    'show_in_rest'      => true,
    'show_admin_column' => true,
    'hierarchical'      => true, // checkboxes in the editor
    'rewrite'           => false,
  ]);
}, 20);

// Person posts keep their order within a group: page attributes in the editor.
add_action('init', function () {
  add_post_type_support('person', ['page-attributes', 'thumbnail']);
}, 30);

// Languages, beside the old ACF "Person" fields.
add_action('acf/init', function () {
  if (!function_exists('acf_add_local_field_group')) return;
  acf_add_local_field_group([
    'key'      => 'group_mh_person_languages',
    'title'    => 'Kielet',
    'fields'   => [[
      'key'          => 'field_mh_person_languages',
      'label'        => 'Kielet',
      'name'         => 'mh_kielet',
      'type'         => 'text',
      'instructions' => 'Kielikoodit pilkulla eroteltuina, esim. fi, en. Näkyvät lippuina nimen vieressä.',
      'default_value' => 'fi',
    ]],
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'person']]],
    'position' => 'side',
  ]);
});

/** One person post as the object the list template expects. */
function staff_person(\WP_Post $post): object {
  $f = function_exists('get_field') ? (array) get_field('acf', $post->ID) : (array) maybe_unserialize(get_post_meta($post->ID, 'acf', true));
  return (object) [
    'photo'            => (string) get_post_thumbnail_id($post),
    'name'             => get_the_title($post),
    'role'             => (string) ($f['title'] ?? ''),
    'responsibilities' => (string) ($f['responsibilities'] ?? ''),
    'phone'            => (string) ($f['phone'] ?? ''),
    'email'            => (string) ($f['email'] ?? ''),
    'languages'        => (string) get_post_meta($post->ID, 'mh_kielet', true),
  ];
}

/**
 * People of a group in their order, or the named people in the given order.
 *
 * @return object[]
 */
function staff_persons(string $group = '', array $names = []): array {
  $args = ['post_type' => 'person', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']];
  if ($group !== '') {
    $args['tax_query'] = [['taxonomy' => 'henkiloryhma', 'field' => 'slug', 'terms' => $group]];
  }
  $posts = get_posts($args);
  if ($names) {
    $byName = [];
    foreach ($posts as $p) $byName[get_the_title($p)] ??= $p;
    $posts = array_values(array_filter(array_map(fn($n) => $byName[$n] ?? null, $names)));
  }
  return array_map(__NAMESPACE__ . '\\staff_person', $posts);
}

/**
 * The people list, same markup as the old Henkilöstö module.
 *
 * @param object[] $persons
 */
function staff_render(array $persons, string $node_id, string $heading = '', int $columns = 2): string {
  if (!$persons && $heading === '') {
    return '';
  }
  enqueue_staff_style();
  $settings = (object) ['heading' => $heading, 'heading_tag' => 'h2', 'columns' => $columns, 'persons' => $persons];
  $module = (object) ['node' => $node_id];
  ob_start();
  include get_stylesheet_directory() . '/partials/staff-list.php';
  return trim((string) ob_get_clean());
}

/**
 * Helpers of the old Beaver Builder module, without Beaver Builder.
 */
final class StaffHelpers {
  public static function language_labels(): array {
    return ['fi' => 'Suomi', 'en' => 'Englanti', 'sv' => 'Ruotsi', 'de' => 'Saksa', 'et' => 'Viro', 'ru' => 'Venäjä', 'fr' => 'Ranska', 'es' => 'Espanja'];
  }

  public static function normalize_languages($raw): array {
    $labels = self::language_labels();
    $aliases = [
      'fi' => 'fi', 'suomi' => 'fi', 'finnish' => 'fi', 'fin' => 'fi',
      'en' => 'en', 'englanti' => 'en', 'english' => 'en', 'eng' => 'en',
      'sv' => 'sv', 'ruotsi' => 'sv', 'swedish' => 'sv', 'swe' => 'sv',
      'de' => 'de', 'saksa' => 'de', 'german' => 'de', 'ger' => 'de', 'deu' => 'de',
      'et' => 'et', 'viro' => 'et', 'eesti' => 'et', 'estonian' => 'et', 'est' => 'et',
      'ru' => 'ru', 'venaja' => 'ru', 'russian' => 'ru', 'rus' => 'ru',
      'fr' => 'fr', 'ranska' => 'fr', 'french' => 'fr', 'fra' => 'fr',
      'es' => 'es', 'espanja' => 'es', 'spanish' => 'es', 'spa' => 'es',
    ];
    if (is_string($raw)) $raw = preg_split('/[\s,;\/|]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($raw) || !$raw) return ['fi'];
    $out = [];
    foreach ($raw as $item) {
      $key = str_replace(['ä', 'ö', 'å'], ['a', 'o', 'a'], strtolower(trim((string) $item)));
      if (isset($aliases[$key], $labels[$aliases[$key]])) $out[$aliases[$key]] = $aliases[$key];
    }
    return $out ? array_values($out) : ['fi'];
  }

  public static function language_flag_svg(string $code): string {
    $flags = [
      'fi' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 11" aria-hidden="true"><rect width="18" height="11" fill="#fff"/><rect x="5" width="3" height="11" fill="#002F6C"/><rect y="4" width="18" height="3" fill="#002F6C"/></svg>',
      'en' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30" aria-hidden="true"><rect width="60" height="30" fill="#012169"/><path d="M0,0 60,30 M60,0 0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 60,30 M60,0 0,30" stroke="#C8102E" stroke-width="2"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></svg>',
      'sv' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 10" aria-hidden="true"><rect width="16" height="10" fill="#006AA7"/><rect x="5" width="2" height="10" fill="#FECC00"/><rect y="4" width="16" height="2" fill="#FECC00"/></svg>',
      'de' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 3" aria-hidden="true"><rect width="5" height="1" y="0" fill="#000"/><rect width="5" height="1" y="1" fill="#D00"/><rect width="5" height="1" y="2" fill="#FFCE00"/></svg>',
      'et' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33 21" aria-hidden="true"><rect width="33" height="7" y="0" fill="#0072CE"/><rect width="33" height="7" y="7" fill="#000"/><rect width="33" height="7" y="14" fill="#fff"/></svg>',
      'ru' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6" aria-hidden="true"><rect width="9" height="2" y="0" fill="#fff"/><rect width="9" height="2" y="2" fill="#0039A6"/><rect width="9" height="2" y="4" fill="#D52B1E"/></svg>',
      'fr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" aria-hidden="true"><rect width="1" height="2" x="0" fill="#002395"/><rect width="1" height="2" x="1" fill="#fff"/><rect width="1" height="2" x="2" fill="#ED2939"/></svg>',
      'es' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" aria-hidden="true"><rect width="3" height="2" fill="#C60B1E"/><rect width="3" height="1" y="0.5" fill="#FFC400"/></svg>',
    ];
    return $flags[$code] ?? '';
  }

  public static function phone_href(string $phone): string {
    $digits = preg_replace('/[^\d+]/', '', $phone);
    return $digits ? 'tel:' . $digits : '';
  }

  public static function email_href(string $email): string {
    $email = trim($email);
    if ($email === '' || strpos($email, '@') === false) return '';
    return 'mailto:' . antispambot($email);
  }
}

// The groups for the Henkilöstö block's sidebar.
add_action('enqueue_block_editor_assets', function () {
  wp_add_inline_script('wp-blocks', 'window.mhStaffGroups = ' . wp_json_encode(staff_groups()) . ';', 'before');
});

// The Henkilöstö stylesheet in the head on pages with the block.
add_action('wp_enqueue_scripts', function () {
  $post = is_singular() ? get_queried_object() : null;
  if ($post instanceof \WP_Post && has_block('muuttohaukat/henkilosto', $post)) {
    enqueue_staff_style();
  }
}, 21);
