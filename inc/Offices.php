<?php
/**
 * Office pages and the contact page (Tapio, 22.9.2026: one structure for the
 * four office pages and /yhteystiedot/, all in Gutenberg, modelled on the
 * Vantaa page): the address and opening hours in the hero, then "sinua
 * palvelee" with the office card, a map and the people.
 *
 *   [toimipiste_tiedot toimipiste="vantaa"]  address, hours, phone, map link
 *   [toimipiste_tiedot]                      the switchboard, e-mail and hours
 *   [henkilosto pohja="koko-suomi-henkilot"] people from a Beaver Builder staff template
 *
 * The map is an embedded Google map (landing-local block, attribute map).
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

function office_address(array $office): string {
  return sprintf('%s, %s %s', $office['street'], $office['zip'], $office['city']);
}

/** Google Maps search for the office, for "Katso kartalta" links. */
function office_map_link(array $office): string {
  return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Muuttohaukat ' . office_address($office));
}

/** An embedded map of the office, loaded lazily. */
function office_map_html(array $office): string {
  // The address Google itself redirects maps?output=embed to; the redirect
  // response carries X-Frame-Options: SAMEORIGIN, the embed does not.
  $query = str_replace('%20', '+', rawurlencode('Muuttohaukat, ' . office_address($office)));
  $src = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m3!2m1!1s' . $query . '!6i15!3m1!1sfi!5m1!1sfi';

  return '<div class="mh-office-map"><iframe src="' . esc_url($src) . '" title="' . esc_attr(sprintf('Kartta: %s, %s', $office['label'], office_address($office)))
    . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>';
}

/**
 * The hero facts: address with a map link, opening hours and phone for one
 * office, or the switchboard, e-mail and hours when no office is given.
 */
function office_facts_html(?array $office): string {
  $hours = office_hours();
  $icon = fn($name) => '<i class="fa-solid fa-' . $name . '" aria-hidden="true"></i>';
  $rows = [];

  if ($office) {
    $rows[] = $icon('location-dot') . '<span><strong>' . esc_html(office_address($office)) . '</strong> · <a href="' . esc_url(office_map_link($office)) . '" target="_blank" rel="noopener">Katso kartalta</a></span>';
  }
  $rows[] = $icon('clock') . '<span>' . esc_html($hours['short']) . '</span>';
  $phone = $office['phone'] ?? company_facts()['phone'];
  $rows[] = $icon('phone') . '<span><a href="tel:' . esc_attr(preg_replace('/\s+/', '', $phone)) . '">' . esc_html($phone) . '</a></span>';
  if (!$office) {
    $email = company_facts()['email'] ?? 'muuttohaukat@muuttohaukat.com';
    $rows[] = $icon('envelope') . '<span><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></span>';
  }

  return '<ul class="mh-office-facts">' . implode('', array_map(fn($r) => '<li>' . $r . '</li>', $rows)) . '</ul>';
}

add_shortcode('toimipiste_tiedot', function ($atts) {
  $atts = shortcode_atts(['toimipiste' => ''], $atts, 'toimipiste_tiedot');
  return office_facts_html($atts['toimipiste'] !== '' ? office($atts['toimipiste']) : null);
});

/**
 * A staff group as the settings object the list template and the older
 * callers expect: the people are person posts (inc/Staff.php). $slug may be
 * an old staff template slug (offices()['staff']) or a group slug.
 */
function staff_settings(string $slug): ?object {
  $persons = staff_persons(staff_group_for_slug($slug));
  return $persons ? (object) ['heading' => '', 'columns' => 2, 'persons' => $persons] : null;
}

/**
 * The people of a group without a heading (the section has one).
 */
function staff_people_html(string $slug, string $node_id): string {
  return staff_render(staff_persons(staff_group_for_slug($slug)), $node_id);
}

/**
 * The Henkilöstö module settings of one node in a page's old Beaver Builder
 * data (kept when the page moved to Gutenberg); read as plain post meta.
 */
function staff_settings_from_post(int $post_id, string $node): ?object {
  foreach ((array) get_post_meta($post_id, '_fl_builder_data', true) as $n) {
    if (is_object($n) && ($n->node ?? '') === $node && ($n->settings->type ?? '') === 'henkilosto') {
      return $n->settings;
    }
  }
  return null;
}

/**
 * [henkilosto pohja="koko-suomi"] a staff group (old template slugs work
 * too), or [henkilosto sivu="94" solmu="abc123"] the people of a module in
 * a page's old Beaver Builder data.
 */
add_shortcode('henkilosto', function ($atts) {
  $atts = shortcode_atts(['pohja' => '', 'sivu' => '', 'solmu' => ''], $atts, 'henkilosto');
  if ($atts['sivu'] !== '' && $atts['solmu'] !== '') {
    $staff = staff_settings_from_post((int) $atts['sivu'], $atts['solmu']);
    $people = $staff ? staff_render(array_values(array_filter((array) ($staff->persons ?? []), 'is_object')), 'henkilosto-' . sanitize_key($atts['solmu'])) : '';
  } else {
    $people = staff_people_html(sanitize_title($atts['pohja']), 'henkilosto-' . sanitize_title($atts['pohja']));
  }

  return $people === '' ? '' : '<div class="mh-landing-local__staff mh-staff-group">' . $people . '</div>';
});

// The Henkilöstö stylesheet in the head on pages that use the shortcode.
add_action('wp_enqueue_scripts', function () {
  $post = is_singular() ? get_queried_object() : null;
  if ($post instanceof \WP_Post && has_shortcode($post->post_content, 'henkilosto')) {
    enqueue_staff_style();
  }
}, 21);

/**
 * [toimipisteet]: the four offices with address, map link and area page,
 * e.g. where boxes and supplies can be picked up.
 */
add_shortcode('toimipisteet', function () {
  $items = '';
  foreach (offices() as $office) {
    $items .= '<li class="mh-office-list__item">'
      . '<h3 class="mh-office-list__name"><a href="' . esc_url(home_url($office['page'])) . '">' . esc_html($office['label']) . '</a></h3>'
      . '<p>' . esc_html(office_address($office)) . '</p>'
      . '<p><a href="' . esc_url(office_map_link($office)) . '" target="_blank" rel="noopener">Katso kartalta</a></p>'
      . '</li>';
  }
  return '<ul class="mh-office-list not-prose">' . $items . '</ul>';
});

/**
 * [yhteyshenkilot nimet="Jani Vivolin, Jari Lindell"]: named people (person
 * posts), in the given order. Replaced the old ACF Personnel block.
 */
add_shortcode('yhteyshenkilot', function ($atts) {
  $atts = shortcode_atts(['nimet' => ''], $atts, 'yhteyshenkilot');
  $names = array_values(array_filter(array_map('trim', explode(',', $atts['nimet']))));
  $people = $names ? staff_render(staff_persons('', $names), 'yhteyshenkilot-' . substr(md5($atts['nimet']), 0, 8)) : '';

  return $people === '' ? '' : '<div class="mh-landing-local__staff mh-staff-group">' . $people . '</div>';
});
