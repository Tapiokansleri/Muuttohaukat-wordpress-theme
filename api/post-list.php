<?php

namespace Muuttohaukat\Routes;

/**
 * REST endpoint for PostListing block.
 * Returns HTML chunks for AJAX pagination and filtering.
 */
class PostListing extends \Muuttohaukat\RestRoute {
  /**
   * Allowed WP_Query argument keys for the public REST endpoint.
   */
  private static $allowedArgs = [
    'post_type', 'posts_per_page', 'paged', 'offset',
    'orderby', 'order',
    'category__in', 'category__not_in',
    'tag__in', 'tag__not_in',
    'post__in', 'post__not_in',
    'ignore_sticky_posts',
    's',
  ];

  public function __construct() {
    parent::__construct('muuttohaukat/v1', 'postlisting');

    $this->registerEndpoint(
      '/query',
      [
        'methods' => 'GET',
        'callback' => [$this, 'getHTMLChunk'],
        'permission_callback' => '__return_true',
      ],
      [
        'expires' => 1,
      ]
    );
  }

  /**
   * Whitelist-based query parameter sanitizer.
   * Only allows known-safe WP_Query arguments.
   */
  public function sanitizeArgs($args) {
    $safe = [];

    foreach ((array) $args as $key => $value) {
      if (in_array($key, self::$allowedArgs, true)) {
        $safe[$key] = $value;
      }
    }

    // Enforce post status
    $safe['post_status'] = 'publish';

    // Cap posts_per_page
    if (isset($safe['posts_per_page'])) {
      $safe['posts_per_page'] = min(absint($safe['posts_per_page']), 100);
    }

    // Restrict post types to those with show_in_rest
    if (isset($safe['post_type'])) {
      $allowed_types = get_post_types(['show_in_rest' => true]);
      if (is_array($safe['post_type'])) {
        $safe['post_type'] = array_intersect($safe['post_type'], $allowed_types);
      } elseif (!in_array($safe['post_type'], $allowed_types, true)) {
        unset($safe['post_type']);
      }
    }

    return $safe;
  }

  /**
   * Get a chunk of HTML based on request parameters.
   */
  public function getHTMLChunk($request) {
    $params = $request->get_params();
    $templates = \Muuttohaukat\getPostListTemplateList();

    $template = $params['template'] ?? null;
    $args = $this->sanitizeArgs(json_decode($params['args'] ?? null));

    if (!($templates[$template] ?? null)) {
      return new \WP_REST_Response([
        'error' => "Template $template does not exist",
      ], 500);
    }

    $template = $templates[$template];
    $query = new \WP_Query($args);

    $havePosts = [$query, "have_posts"];
    $thePost = [$query, "the_post"];
    $pages = $query->max_num_pages;

    \ob_start();

    $i = 0;
    if (!$havePosts()) {
      $title = \Muuttohaukat\app()->translations->getText('No posts found');

      echo "<h2>" . esc_html($title) . "</h2>";
    }

    while ($havePosts()) {
      $thePost();
      $i++;
      $template([], $i, false);
    }
    $html = \ob_get_clean();

    return ['html' => $html, 'args' => $args, 'pages' => $pages];
  }
}
