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
 *   - a closing call to action and "Lue myös" cards, from the post meta
 *
 * Post meta (all optional, set by the script that creates the article):
 *   _mh_guide_standfirst   the lead paragraph under the title
 *   _mh_guide_image        attachment ID of the hero photo, _mh_guide_image_alt its alt text
 *   _mh_guide_facts        JSON [{"value":"9 min","label":"lukuaika","icon":"clock"}, ...]; default: reading time, tools, updated
 *   _mh_guide_cta          JSON {"title":..., "text":..., "image":attachment ID, "alt":...}
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
  $blank    = ['title' => '', 'id' => '', 'classes' => [], 'lead' => '', 'body' => ''];

  foreach (parse_blocks($post->post_content) as $block) {
    if ($block['blockName'] === null && trim((string) $block['innerHTML']) === '') {
      continue;
    }
    $is_h2 = $block['blockName'] === 'core/heading' && (int) ($block['attrs']['level'] ?? 2) === 2;
    if ($is_h2) {
      if ($current !== null) {
        $sections[] = $current;
      }
      $current = [
        'title'   => trim(wp_strip_all_tags((string) $block['innerHTML'])),
        'id'      => (string) ($block['attrs']['anchor'] ?? ''),
        'classes' => array_values(array_filter(preg_split('/\s+/', (string) ($block['attrs']['className'] ?? '')))),
        'lead'    => '',
        'body'    => '',
      ];
      continue;
    }
    $current ??= $blank;
    $html = render_block($block);
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
      $section[$part] = wp_filter_content_tags(do_shortcode($section[$part]));
    }

    return $section;
  }, $sections);
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
  ];

  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($paths[$name] ?? $paths['check']) . '</svg>';
}

/** The hero's facts: from the meta, else reading time, tools in the article and the update date. */
function guide_facts(\WP_Post $post, string $rendered): array {
  $facts = guide_meta_json($post->ID, '_mh_guide_facts');
  if ($facts) {
    return $facts;
  }
  $facts[] = ['value' => guide_reading_minutes($rendered) . ' min', 'label' => 'lukuaika', 'icon' => 'clock'];
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
