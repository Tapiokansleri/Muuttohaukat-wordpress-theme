<?php
/**
 * One locality directory for every service: all published city pages of a
 * service, grouped by the office that serves the town, in the same section
 * everywhere (front page, /muuttosiivous/, the cleaning city pages and the
 * moving box pages).
 *
 *   [paikkakunnat palvelu="muutto|muuttosiivous|muuttolaatikot"
 *     kicker="…" otsikko="…" teksti="…" painike="…" linkki="…"]
 *
 * A town's office comes from the moving city pages (_mh_city / _mh_office),
 * so cleaning and box pages follow the same office as the town's moving page.
 * Pages whose town has no office (regions) form the last group. The list
 * follows the published pages, so it needs no upkeep.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Services: post type, the title prefix to drop from labels, the default
 * texts and whether a group heading links to the office's area page.
 */
function city_directory_services(): array {
  return apply_filters('mh_city_directory_services', [
    'muutto' => [
      'post_type'    => 'muutto',
      'prefix'       => '/^Muuttopalvelu\s+/iu',
      'kicker'       => 'Paikkakunnat',
      'title'        => 'Muuttopalvelu paikkakunnittain',
      'text'         => 'Muutamme koteja ja yrityksiä neljästä toimipisteestä käsin. Valitse oma paikkakuntasi, niin näet hinnat, toimipisteen ja lähimmät muuttajat.',
      'office_links' => true,
      'areas'        => 'Muut paikkakunnat',
    ],
    'muuttosiivous' => [
      'post_type'    => 'muuttosiivous',
      'prefix'       => '/^Muuttosiivous\s+/iu',
      'kicker'       => 'Paikkakunnat',
      'title'        => 'Muuttosiivous paikkakunnittain',
      'text'         => 'Teemme muuttosiivouksia näissä kaupungeissa ja niiden lähikunnissa. Valitse oma paikkakuntasi.',
      'office_links' => false,
      'areas'        => 'Muut paikkakunnat',
    ],
    'muuttolaatikot' => [
      'post_type'    => 'muuttolaatikot',
      'prefix'       => '/^Muuttolaatikot\s+/iu',
      'kicker'       => 'Paikkakunnat',
      'title'        => 'Muuttolaatikot paikkakunnittain',
      'text'         => 'Toimitamme muuttolaatikot ja noudamme ne muuton jälkeen. Valitse oma paikkakuntasi.',
      'office_links' => false,
      'areas'        => 'Maakunnat',
    ],
  ]);
}

/**
 * Town name (lower case) => office key, from the moving city pages.
 */
function city_office_map(): array {
  static $map = null;
  if ($map !== null) {
    return $map;
  }
  global $wpdb;
  $meta = [];
  $rows = $wpdb->get_results("SELECT pm.post_id, pm.meta_key, pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE p.post_status = 'publish' AND pm.meta_key IN ('_mh_city', '_mh_office')");
  foreach ($rows as $row) {
    $meta[$row->post_id][$row->meta_key] = $row->meta_value;
  }
  $map = [];
  foreach ($meta as $m) {
    if (!empty($m['_mh_city']) && !empty($m['_mh_office'])) {
      $map[mb_strtolower($m['_mh_city'])] = $m['_mh_office'];
    }
  }
  return $map;
}

/**
 * Office key (or 'areas') => [['label', 'url', 'id'], …], towns in Finnish order.
 */
function city_directory_groups(string $service): array {
  $conf = city_directory_services()[$service] ?? null;
  if (!$conf) {
    return [];
  }
  $office_of = city_office_map();

  $groups = [];
  $posts = get_posts(['post_type' => $conf['post_type'], 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
  foreach ($posts as $post) {
    $city = (string) get_post_meta($post->ID, '_mh_city', true);
    $name = $city !== '' ? $city : html_entity_decode(wp_strip_all_tags(get_the_title($post)), ENT_QUOTES, 'UTF-8');
    $name = trim(preg_replace($conf['prefix'], '', $name), " :\t");
    $key  = mb_strtolower(trim(preg_replace('/\s*\(.*\)$/u', '', $name)));
    $office = (string) get_post_meta($post->ID, '_mh_office', true) ?: ($office_of[$key] ?? 'areas');
    $groups[office($office) ? $office : 'areas'][] = ['label' => $name, 'url' => get_permalink($post), 'id' => $post->ID];
  }

  $collator = class_exists('\Collator') ? new \Collator('fi_FI') : null;
  foreach ($groups as &$towns) {
    usort($towns, fn($a, $b) => $collator ? $collator->compare($a['label'], $b['label']) : strcmp($a['label'], $b['label']));
  }
  unset($towns);

  return $groups;
}

/**
 * The directory section.
 *
 * @param array $args palvelu, kicker, otsikko, teksti, painike, linkki
 */
function city_directory_html(array $args): string {
  $service = $args['palvelu'] ?? 'muutto';
  $conf = city_directory_services()[$service] ?? null;
  $groups = $conf ? city_directory_groups($service) : [];
  if (!$groups) {
    return '';
  }

  static $n = 0;
  $id = 'mh-cities-' . $service . (++$n > 1 ? '-' . $n : '');
  $current = is_singular() ? get_queried_object_id() : 0;
  $order = array_merge(array_keys(offices()), ['areas']);

  ob_start();
  ?>
  <section class="mh-cities not-prose" aria-labelledby="<?php echo esc_attr($id); ?>">
    <div class="mh-cities__head">
      <p class="mh-cities__kicker"><?php echo esc_html($args['kicker'] ?? $conf['kicker']); ?></p>
      <h2 id="<?php echo esc_attr($id); ?>" class="mh-cities__title"><?php echo esc_html($args['otsikko'] ?? $conf['title']); ?></h2>
      <p><?php echo esc_html($args['teksti'] ?? $conf['text']); ?></p>
      <?php if (!empty($args['painike']) && !empty($args['linkki'])) : ?>
        <p><a class="mh-painike mh-painike--yellow" href="<?php echo esc_url($args['linkki']); ?>"><?php echo esc_html($args['painike']); ?></a></p>
      <?php endif; ?>
    </div>
    <div class="mh-cities__groups">
      <?php foreach ($order as $key) :
        if (empty($groups[$key])) continue;
        $office = $key === 'areas' ? null : office($key);
        $label = $office['label'] ?? $conf['areas'];
        $count = count($groups[$key]);
        $page = ($office && $conf['office_links'] && !empty($office['page'])) ? home_url($office['page']) : '';
        ?>
        <div class="mh-cities__group">
          <h3 class="mh-cities__label">
            <?php if ($page) : ?><a href="<?php echo esc_url($page); ?>"><?php echo esc_html($label); ?></a><?php else : echo esc_html($label); endif; ?>
            <span><?php echo esc_html(sprintf($count === 1 ? '%d paikkakunta' : '%d paikkakuntaa', $count)); ?></span>
          </h3>
          <ul class="mh-cities__list">
            <?php foreach ($groups[$key] as $town) : ?>
              <li><a href="<?php echo esc_url($town['url']); ?>"<?php echo $town['id'] === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html($town['label']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php

  return (string) ob_get_clean();
}

add_shortcode('paikkakunnat', function ($atts) {
  $atts = shortcode_atts(['palvelu' => 'muutto', 'kicker' => null, 'otsikko' => null, 'teksti' => null, 'painike' => '', 'linkki' => ''], $atts, 'paikkakunnat');
  return city_directory_html(array_filter($atts, fn($v) => $v !== null));
});

/**
 * "Muutot myös lähikuntiin" and the other nearby-town sections (the
 * landing-nearby block, inc/Nearby.php) in the same layout as the directory:
 * heading, text and the area page button on the left, the towns in columns
 * on the right.
 *
 * @param string $head_html Eyebrow, heading and text, already escaped.
 * @param array  $links     [['label' => …, 'url' => …], …]
 */
function nearby_directory_html(string $head_html, array $links, string $hub_path = '', string $hub_label = ''): string {
  $items = '';
  $count = 0;
  foreach ($links as $link) {
    $label = trim((string) ($link['label'] ?? ''));
    $url   = trim((string) ($link['url'] ?? ''));
    if ($label === '' || $url === '') {
      continue;
    }
    $href = preg_match('#^https?://#', $url) ? $url : home_url($url);
    $items .= '<li><a href="' . esc_url($href) . '">' . esc_html($label) . '</a></li>';
    $count++;
  }

  $button = '';
  $hub_path = trim($hub_path);
  if ($hub_path !== '') {
    $hub_href = preg_match('#^https?://#', $hub_path) ? $hub_path : home_url($hub_path);
    $button = '<p class="mh-cities__button"><a class="mh-painike mh-painike--yellow" href="' . esc_url($hub_href) . '">'
      . esc_html(trim($hub_label) !== '' ? $hub_label : 'Alueen toimipiste') . '</a></p>';
  }

  $list = '';
  if ($count) {
    $list = '<div class="mh-cities__groups"><nav class="mh-cities__group" aria-label="Lähimmät paikkakunnat">'
      . '<h3 class="mh-cities__label">Lähimmät paikkakunnat <span>' . esc_html(sprintf($count === 1 ? '%d paikkakunta' : '%d paikkakuntaa', $count)) . '</span></h3>'
      . '<ul class="mh-cities__list">' . $items . '</ul></nav></div>';
  }

  return '<div class="mh-cities mh-cities--nearby not-prose"><div class="mh-cities__head">' . $head_html . $button . '</div>' . $list . '</div>';
}
