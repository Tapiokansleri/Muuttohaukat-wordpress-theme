<?php
/**
 * One-time work performed whenever Muuttohaukat is activated.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\ThemeActivation;

/**
 * Move an active WPCodeBox Search Console meta value into TSF settings.
 *
 * The value is read directly from the database and is never written to theme
 * source or logs.
 */
function migrateSearchConsoleVerification() {
  $settings = get_option('autodescription-site-settings', []);
  if (!is_array($settings)) {
    $settings = [];
  }

  if (!empty($settings['google_verification'])) {
    return function_exists('the_seo_framework');
  }

  global $wpdb;
  $table = $wpdb->prefix . 'wpcb_snippets';
  $wpdb->last_error = '';
  $table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));

  if ($wpdb->last_error !== '') {
    return false;
  }

  if ($table_exists !== $table) {
    return false;
  }

  $wpdb->last_error = '';
  $code = $wpdb->get_var($wpdb->prepare(
    "SELECT code FROM {$table} WHERE enabled = 1 AND code LIKE %s LIMIT 1",
    '%' . $wpdb->esc_like('google-site-verification') . '%'
  ));

  if ($wpdb->last_error !== '') {
    return false;
  }

  if (!is_string($code) || $code === '') {
    return true;
  }

  if (!function_exists('the_seo_framework')) {
    return false;
  }

  $previous = libxml_use_internal_errors(true);
  $document = new \DOMDocument();
  $loaded = $document->loadHTML($code, LIBXML_NONET);
  libxml_clear_errors();
  libxml_use_internal_errors($previous);

  if (!$loaded) {
    return false;
  }

  $token = '';
  foreach ($document->getElementsByTagName('meta') as $meta) {
    if ($meta->getAttribute('name') === 'google-site-verification') {
      $token = sanitize_text_field($meta->getAttribute('content'));
      break;
    }
  }

  if ($token === '') {
    return false;
  }

  $settings['google_verification'] = $token;
  update_option('autodescription-site-settings', $settings);
  $saved = get_option('autodescription-site-settings', []);

  return is_array($saved) && ($saved['google_verification'] ?? '') === $token;
}

/** Confirm that a plugin's theme replacement is available before disabling it. */
function replacementIsReady($plugin) {
  switch ($plugin) {
    case 'shortcode-query-pages-by-category/query-pages-by-category.php':
      return defined('Muuttohaukat\CATEGORY_SHORTCODE_REPLACEMENT_READY')
        && \Muuttohaukat\CATEGORY_SHORTCODE_REPLACEMENT_READY === true;
    case 'disable-comments/disable-comments.php':
      return defined('Muuttohaukat\Comments\REPLACEMENT_READY')
        && \Muuttohaukat\Comments\REPLACEMENT_READY === true;
    case 'svg-support/svg-support.php':
      return class_exists('\enshrined\svgSanitize\Sanitizer')
        && function_exists('\Muuttohaukat\SvgUploads\sanitize_upload');
    case 'wp_query-route-to-rest-api/wp_query-route-to-rest-api.php':
      return class_exists('\Muuttohaukat\Routes\PostListing');
    case 'wpcodebox2/wpcodebox2.php':
      return is_readable(get_stylesheet_directory() . '/assets/css/pricing-table.css')
        && is_readable(get_stylesheet_directory() . '/assets/css/home-hero.css')
        && migrateSearchConsoleVerification();
    case 'breadcrumb-navxt/breadcrumb-navxt.php':
      return function_exists('\Muuttohaukat\Breadcrumbs\render');
    case 'syllable-hyphenator/syllable-hyphenator.php':
      return true;
    default:
      return true;
  }
}

/**
 * Deactivate plugins whose functionality is built into this theme.
 *
 * Network-active plugins are intentionally left untouched because disabling
 * them network-wide from a single site's theme activation would be unsafe.
 *
 * @return array{deactivated:string[],network_active:string[],not_ready:string[]}
 */
function deactivateDuplicatePlugins() {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';

  $plugins = [
    'kansleri-hellobar/kansleri-hellobar.php',
    'kansleri-floating-cta/kansleri-floating-cta.php',
    'shortcode-query-pages-by-category/query-pages-by-category.php',
    'disable-comments/disable-comments.php',
    'svg-support/svg-support.php',
    'syllable-hyphenator/syllable-hyphenator.php',
    'wp_query-route-to-rest-api/wp_query-route-to-rest-api.php',
    'wpcodebox2/wpcodebox2.php',
    'breadcrumb-navxt/breadcrumb-navxt.php',
  ];
  $active = [];
  $network_active = [];
  $not_ready = [];

  foreach ($plugins as $plugin) {
    if (is_multisite() && is_plugin_active_for_network($plugin)) {
      $network_active[] = $plugin;
    } elseif (is_plugin_active($plugin)) {
      if (replacementIsReady($plugin)) {
        $active[] = $plugin;
      } else {
        $not_ready[] = $plugin;
      }
    }
  }

  if ($active) {
    deactivate_plugins($active, false, false);
  }

  return [
    'deactivated'    => $active,
    'network_active' => $network_active,
    'not_ready'      => $not_ready,
  ];
}

/**
 * Read the first language-specific legacy ACF footer containing menus.
 *
 * @return array<int, array<string, mixed>>
 */
function legacyFooterMenus() {
  if (!function_exists('get_field')) {
    return [];
  }

  $app = \Muuttohaukat\app();
  $languages = $app->translations->getLanguages();
  if (!$languages) {
    $languages = ['fi'];
  }

  foreach ($languages as $language) {
    $footer = $app->getOption('footer', $language);
    if (is_array($footer) && !empty($footer['menus']) && is_array($footer['menus'])) {
      return $footer['menus'];
    }
  }

  return [];
}

/**
 * Convert a legacy ACF footer menu row to editable block-widget markup.
 *
 * @param array<string, mixed> $menu Legacy footer menu row.
 * @return string
 */
function footerMenuBlockContent(array $menu) {
  $items = [];

  foreach ((array) ($menu['menu'] ?? []) as $post_value) {
    $post_id = $post_value instanceof \WP_Post ? $post_value->ID : absint($post_value);
    if (!$post_id || get_post_status($post_id) !== 'publish') {
      continue;
    }

    $items[] = '<!-- wp:list-item --><li><a href="' . esc_url(get_permalink($post_id)) . '">'
      . esc_html(get_the_title($post_id))
      . '</a></li><!-- /wp:list-item -->';
  }

  if (!$items) {
    return '';
  }

  $content = '<!-- wp:group {"className":"muuttohaukat-seeded-footer-menu"} -->'
    . '<div class="wp-block-group muuttohaukat-seeded-footer-menu">';
  $title = sanitize_text_field($menu['name'] ?? '');

  if ($title !== '') {
    $content .= '<!-- wp:heading {"level":3} -->'
      . '<h3 class="wp-block-heading">' . esc_html($title) . '</h3>'
      . '<!-- /wp:heading -->';
  }

  $content .= '<!-- wp:list --><ul class="wp-block-list">'
    . implode('', $items)
    . '</ul><!-- /wp:list -->';

  return $content . '</div><!-- /wp:group -->';
}

/**
 * Populate empty footer sidebars from the legacy ACF defaults.
 *
 * Existing widgets are never replaced. Re-activating the theme remains
 * idempotent because only empty sidebars receive a widget.
 *
 * @return int Number of widgets created.
 */
function seedFooterWidgets() {
  $menus = array_slice(legacyFooterMenus(), 0, 4);
  if (!$menus) {
    return 0;
  }

  $sidebars = get_option('sidebars_widgets', []);
  $widgets = get_option('widget_block', []);

  if (!is_array($sidebars)) {
    $sidebars = [];
  }
  if (!is_array($widgets)) {
    $widgets = [];
  }

  $next_id = 1;
  foreach (array_keys($widgets) as $key) {
    if (is_int($key) || ctype_digit((string) $key)) {
      $next_id = max($next_id, (int) $key + 1);
    }
  }

  $created = 0;
  foreach ($menus as $index => $menu) {
    $sidebar_id = 'footer-' . ($index + 1);
    if (!empty($sidebars[$sidebar_id]) || !is_array($menu)) {
      continue;
    }

    $content = footerMenuBlockContent($menu);
    if ($content === '') {
      continue;
    }

    while (isset($widgets[$next_id])) {
      $next_id++;
    }

    $widget_id = 'block-' . $next_id;
    $widgets[$next_id] = ['content' => $content];
    $sidebars[$sidebar_id] = [$widget_id];
    $next_id++;
    $created++;
  }

  if ($created) {
    $widgets['_multiwidget'] = 1;
    $sidebars['array_version'] = 3;
    update_option('widget_block', $widgets);
    update_option('sidebars_widgets', $sidebars);
  }

  return $created;
}

add_action('after_switch_theme', function () {
  $plugin_result = deactivateDuplicatePlugins();
  $widgets_created = seedFooterWidgets();

  set_transient('muuttohaukat_activation_result', [
    'deactivated'     => $plugin_result['deactivated'],
    'network_active'  => $plugin_result['network_active'],
    'not_ready'       => $plugin_result['not_ready'],
    'widgets_created' => $widgets_created,
  ], MINUTE_IN_SECONDS * 5);
});

add_action('admin_notices', function () {
  if (!current_user_can('activate_plugins')) {
    return;
  }

  $result = get_transient('muuttohaukat_activation_result');
  if (!is_array($result)) {
    return;
  }
  delete_transient('muuttohaukat_activation_result');

  if (!empty($result['deactivated'])) {
    echo '<div class="notice notice-success is-dismissible"><p>'
      . esc_html(sprintf(
        __('%d päällekkäistä lisäosaa poistettiin käytöstä, koska vastaavat toiminnot sisältyvät Muuttohaukat-teemaan.', 'muuttohaukat'),
        count($result['deactivated'])
      ))
      . '</p></div>';
  }

  if (!empty($result['widgets_created'])) {
    echo '<div class="notice notice-success is-dismissible"><p>'
      . esc_html(sprintf(
        __('Footer-alueille luotiin %d oletusvimpainta.', 'muuttohaukat'),
        (int) $result['widgets_created']
      ))
      . '</p></div>';
  }

  if (!empty($result['network_active'])) {
    echo '<div class="notice notice-warning"><p>'
      . esc_html(sprintf(
        __('%d päällekkäistä lisäosaa on aktivoitu verkonlaajuisesti, joten niitä ei voitu poistaa käytöstä automaattisesti. Poista lisäosat käytöstä verkon hallinnassa.', 'muuttohaukat'),
        count($result['network_active'])
      ))
      . '</p></div>';
  }

  if (!empty($result['not_ready'])) {
    echo '<div class="notice notice-error"><p>'
      . esc_html(sprintf(
        __('%d lisäosaa jätettiin käyttöön, koska teeman korvaava toiminto ei läpäissyt valmiustarkistusta. Tarkista teeman julkaisutiedostot ennen uutta aktivointia.', 'muuttohaukat'),
        count($result['not_ready'])
      ))
      . '</p></div>';
  }
});
