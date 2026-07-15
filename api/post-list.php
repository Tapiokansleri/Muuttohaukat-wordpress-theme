<?php

namespace Muuttohaukat\Routes;

/**
 * REST endpoint for PostListing block.
 * Returns HTML chunks for AJAX pagination and filtering.
 */
class PostListing extends \Muuttohaukat\RestRoute {
  public function __construct() {
    parent::__construct('muuttohaukat/v1', 'postlisting');

    $this->registerEndpoint(
      '/query',
      [
        'methods' => 'GET',
        'callback' => [$this, 'getHTMLChunk'],
        'permission_callback' => [$this, 'checkRateLimit'],
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

    if (!is_array($args)) {
      $args = [];
    }

    $integer_args = [
      'posts_per_page' => [1, 100],
      'paged' => [1, 10000],
      'offset' => [0, 100000],
    ];
    foreach ($integer_args as $key => $bounds) {
      if (isset($args[$key]) && is_numeric($args[$key])) {
        $safe[$key] = max($bounds[0], min((int) $args[$key], $bounds[1]));
      }
    }

    $id_arrays = [
      'category__in', 'category__not_in',
      'tag__in', 'tag__not_in',
      'post__in', 'post__not_in',
    ];
    foreach ($id_arrays as $key) {
      if (!isset($args[$key])) {
        continue;
      }

      $values = is_array($args[$key]) ? $args[$key] : [$args[$key]];
      $ids = [];
      foreach (array_slice($values, 0, 100) as $value) {
        if (is_numeric($value)) {
          $id = absint($value);
          if ($id) {
            $ids[$id] = $id;
          }
        }
      }
      $safe[$key] = array_values($ids);
    }

    if (isset($args['ignore_sticky_posts'])) {
      $safe['ignore_sticky_posts'] = (bool) rest_sanitize_boolean($args['ignore_sticky_posts']);
    }

    if (isset($args['s']) && is_scalar($args['s'])) {
      $safe['s'] = substr(sanitize_text_field((string) $args['s']), 0, 200);
    }

    if (isset($args['order']) && is_scalar($args['order'])) {
      $order = strtoupper((string) $args['order']);
      if (in_array($order, ['ASC', 'DESC'], true)) {
        $safe['order'] = $order;
      }
    }

    if (isset($args['orderby']) && is_scalar($args['orderby'])) {
      $orderby = sanitize_key((string) $args['orderby']);
      $allowed_orderby = [
        'none', 'id', 'author', 'title', 'name', 'type', 'date', 'modified',
        'parent', 'rand', 'comment_count', 'relevance', 'menu_order', 'post__in',
      ];
      if (in_array($orderby, $allowed_orderby, true)) {
        $safe['orderby'] = $orderby;
      }
    }

    $safe['post_status'] = 'publish';

    if (isset($args['post_type'])) {
      $allowed_types = get_post_types(['show_in_rest' => true]);
      if (is_array($args['post_type'])) {
        $types = [];
        foreach (array_slice($args['post_type'], 0, 20) as $type) {
          if (is_scalar($type)) {
            $types[] = sanitize_key((string) $type);
          }
        }
        $types = array_values(array_intersect($types, $allowed_types));
        if ($types) {
          $safe['post_type'] = $types;
        }
      } elseif (is_scalar($args['post_type'])) {
        $type = sanitize_key((string) $args['post_type']);
        if (in_array($type, $allowed_types, true)) {
          $safe['post_type'] = $type;
        }
      }
    }

    return $safe;
  }

  /**
   * Apply a small per-address request budget to anonymous clients.
   */
  public function checkRateLimit() {
    if (is_user_logged_in()) {
      return true;
    }

    $address = isset($_SERVER['REMOTE_ADDR'])
      ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
      : 'unknown';
    $key = 'mh_postlisting_rate_' . md5($address);
    $count = (int) get_transient($key);

    if ($count >= 120) {
      return new \WP_Error(
        'rest_rate_limit_exceeded',
        __('Too many requests. Please try again shortly.', 'muuttohaukat'),
        ['status' => 429]
      );
    }

    set_transient($key, $count + 1, MINUTE_IN_SECONDS);
    return true;
  }

  /**
   * Get a chunk of HTML based on request parameters.
   */
  public function getHTMLChunk($request) {
    $params = $request->get_params();
    $templates = \Muuttohaukat\getPostListTemplateList();

    $template = $params['template'] ?? null;
    $raw_args = $params['args'] ?? '{}';

    if (!is_string($raw_args)) {
      return new \WP_Error(
        'invalid_postlisting_args',
        __('The args parameter must be a JSON object.', 'muuttohaukat'),
        ['status' => 400]
      );
    }

    $decoded_args = json_decode($raw_args);
    if (json_last_error() !== JSON_ERROR_NONE || !is_object($decoded_args)) {
      return new \WP_Error(
        'invalid_postlisting_json',
        __('The args parameter must contain a valid JSON object.', 'muuttohaukat'),
        ['status' => 400]
      );
    }

    $args = $this->sanitizeArgs((array) $decoded_args);

    if (!is_string($template) || !isset($templates[$template]) || !is_callable($templates[$template])) {
      return new \WP_Error(
        'invalid_postlisting_template',
        __('The requested post template does not exist.', 'muuttohaukat'),
        ['status' => 400]
      );
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
    \wp_reset_postdata();

    return ['html' => $html, 'args' => $args, 'pages' => $pages];
  }
}
