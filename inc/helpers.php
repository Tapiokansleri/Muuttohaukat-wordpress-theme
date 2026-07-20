<?php
/**
 * General utility functions.
 *
 * Environment detection, string helpers, template utilities,
 * and transient caching wrappers.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Get the current environment name from WP_ENV constant or env variable.
 *
 * @return string Environment name (production, staging, development).
 */
function env() {
  if (defined('WP_ENV')) {
    return WP_ENV;
  } else {
    define('WP_ENV', getenv('WP_ENV') ?: 'production');
  }
  return WP_ENV;
}

/** @return bool True if current environment is production. */
function isProd() {
  return env() === 'production';
}

/** @return bool True if current environment is staging. */
function isStaging() {
  return env() === 'staging';
}

/** @return bool True if current environment is development. */
function isDev() {
  return env() === 'development';
}

/**
 * Get the current request URL using WordPress functions.
 *
 * @return string Full URL of the current request.
 */
function currentUrl() {
  return home_url(add_query_arg(null, null));
}

/**
 * Convert a string to a URL-safe slug.
 *
 * @param string $string Input string.
 * @return string Slugified string.
 */
function slugify(string $string = '') {
  $string = str_replace(' ', '-', $string);
  $string = strtolower($string);
  return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
}

/**
 * Access nested array values using dot notation.
 *
 * @param array  $data    Source array.
 * @param string $key     Dot-separated key path.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function dotty($data = [], $key = '', $default = false) {
  if (!empty($data)) {
    if (strpos($key, '.') > -1) {
      $levels = explode('.', $key);
      $value = $data;

      for ($level = 0; $level < count($levels); $level++) {
        $value = $value[$levels[$level]] ?? $default;
      }

      if (is_array($value) && empty($value)) {
        return $default;
      }

      return $value;
    }

    return $data[$key] ?? $default;
  }

  return $default;
}

/**
 * Merge provided values into defaults, filtering empty non-boolean values.
 *
 * @param array $defaults Default values.
 * @param array $provided User-provided overrides.
 * @return array Merged result.
 */
function params($defaults = [], $provided = []) {
  if (!is_array($defaults) || !is_array($provided)) {
    throw new \Exception('Invalid data provided to params, both parameters must be arrays!');
  }

  return array_replace_recursive($defaults, array_filter($provided, function ($value) {
    if (is_bool($value)) {
      return true;
    }

    return !empty($value);
  }));
}

/**
 * Build an escaped HTML class attribute from arguments.
 *
 * @param string ...$args CSS class names.
 * @return string class="..." attribute string.
 */
function className() {
  $args = func_get_args();
  $classes = \esc_attr(PHP_EOL . join(PHP_EOL, $args));

  return "class=\"$classes\"";
}

/**
 * Get the escaped post title.
 *
 * @param string|null $title Optional title override.
 * @return string Escaped title.
 */
function title($title = null) {
  if (!$title) {
    $title = get_the_title();
  }

  return \esc_html(apply_filters('the_title', $title));
}

/**
 * Process raw content through WordPress content filters.
 *
 * @param string|null $content Raw content or null for current post.
 * @return string Filtered content.
 */
function content($content = null) {
  if (is_null($content)) {
    $content = get_the_content();
  }

  $content = \wptexturize($content);
  $content = \convert_smilies($content);
  $content = \wpautop($content);
  $content = \shortcode_unautop($content);
  $content = \prepend_attachment($content);
  $content = \wp_filter_content_tags($content);

  return $content;
}

/**
 * Render Gutenberg content wrapped in a container div with data attributes.
 *
 * @param array|null $paginationOpts Optional wp_link_pages() arguments.
 */
function gutenbergContent($paginationOpts = null) {
  global $numpages, $page, $multipage, $post;

  $id = \esc_attr($post->ID);
  $attrs = "data-id='{$id}'";
  if ($multipage) {
    $p = \esc_attr($page);
    $pages = \esc_attr($numpages);

    $attrs = "{$attrs} data-page='{$p}' data-pages='{$pages}'";
  }

  // On BB-built pages the_content() outputs BB module markup, which has its
  // own typography. Skip the .mh-gutenberg.prose wrapper there so Tailwind
  // Typography (and theme rules like .mh-gutenberg li { margin: 0.25em 0 })
  // don't leak into BB content.
  $is_bb_page = class_exists('FLBuilderModel') && \FLBuilderModel::is_builder_enabled($post->ID);
  $is_landing = post_has_landing_blocks($post);

  if ($is_bb_page) {
    $wrapper_class = 'mh-bb-content';
  } elseif ($is_landing) {
    $wrapper_class = '';
  } else {
    $wrapper_class = 'mh-gutenberg prose prose-lg max-w-none';
  }

  if ($wrapper_class !== '') {
    echo "<div class='{$wrapper_class}' {$attrs}>";
  } else {
    echo "<div {$attrs}>";
  }
  \the_content();

  if ($paginationOpts !== null) {
    \wp_link_pages($paginationOpts);
  }

  echo '</div>';
}

/**
 * Wrap content in an HTML element.
 *
 * @param string $wrappable Content to wrap (should be pre-escaped).
 * @param array  $options   Element tag and className.
 * @return string Wrapped HTML.
 */
function wrapper($wrappable, $options = []) {
  $options = params([
    'element' => 'div',
    'className' => 'wrapper',
  ], $options);

  $tag   = tag_escape($options['element']);
  $class = \esc_attr($options['className']);

  return "<{$tag} class='{$class}'>{$wrappable}</{$tag}>";
}

/**
 * Turns links into non-functional ones in block previews.
 *
 * @param string $link      The original URL.
 * @param bool   $isPreview Whether the block is in preview mode.
 * @return string '#' in preview mode, original link otherwise.
 */
function neutralizeLink($link, $isPreview = false) {
  return $isPreview ? '#' : $link;
}

/**
 * Capture output of a callable into a string.
 *
 * @param callable $fn     Function to call.
 * @param mixed    ...$params Arguments to pass.
 * @return string Captured output.
 */
function capture($fn, ...$params) {
  \ob_start();
  $fn(...$params);
  return \ob_get_clean();
}

/**
 * Wrap data in a transient cache.
 *
 * @param mixed       $data       Data to cache.
 * @param array       $opts       Transient options (key, options).
 * @param string|null &$missReason Set to 'Miss', 'Bypass', or null (hit).
 * @return mixed Cached or fresh data.
 */
function withTransient($data, $opts = [], &$missReason = null) {
  if (!class_exists('\Muuttohaukat\Transientify')) {
    return $data;
  }

  $options = params([
    'key' => null,
    'options' => [
      'expires' => \HOUR_IN_SECONDS,
      'type' => 'general',
      'bypassPermissions' => ['edit_posts'],
    ]
  ], $opts);

  if (!$options['key']) {
    throw new \Exception('Unable to create transient without key');
  }

  $transient = new Transientify($options['key'], $options['options']);
  $missReason = null;

  return $transient->get(function ($transientify) use (&$data) {
    return $transientify->set($data);
  }, $missReason);
}

/**
 * Format a transient miss reason for HTML comment output.
 *
 * @param string|null $missReason The miss reason from Transientify.
 * @return string 'Hit', 'Miss', or 'Bypass'.
 */
function transientResult($missReason = null) {
  if (\is_null($missReason)) {
    return 'Hit';
  }

  return $missReason;
}
