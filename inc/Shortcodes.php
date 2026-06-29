<?php

namespace Muuttohaukat;

/**
 * Register category taxonomy for pages.
 */
add_action('init', function () {
  if (!post_type_supports('page', 'category')) {
    register_taxonomy_for_object_type('category', 'page');
  }
});

/**
 * [query_pages_by_category category="ID"] shortcode.
 * Lists published pages belonging to the given category.
 */
add_shortcode('query_pages_by_category', function ($atts) {
  $atts = shortcode_atts([
    'category' => '',
  ], $atts, 'query_pages_by_category');

  if (empty($atts['category'])) {
    return '';
  }

  $query = new \WP_Query([
    'post_type' => 'page',
    'post_status' => 'publish',
    'category__in' => [(int) $atts['category']],
    'posts_per_page' => -1,
  ]);

  if (!$query->have_posts()) {
    wp_reset_postdata();
    return '<p>' . esc_html__('Ei sivuja tässä kategoriassa.', 'muuttohaukat') . '</p>';
  }

  $output = '<div class="paikkakunnat-container not-prose"><div class="paikkakunnat">';
  $output .= '<h2>' . esc_html__('Paikkakunnat', 'muuttohaukat') . '</h2><ul>';

  while ($query->have_posts()) {
    $query->the_post();
    $output .= '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
  }

  $output .= '</ul></div></div>';
  wp_reset_postdata();

  return $output;
});

/**
 * Enqueue paikkakunnat styles.
 */
add_action('wp_enqueue_scripts', function () {
  $themeUri = get_stylesheet_directory_uri();
  $version = wp_get_theme()->get('Version');

  wp_enqueue_style('paikkakunnat', $themeUri . '/assets/css/paikkakunnat.css', ['muuttohaukat-base'], $version);
});
