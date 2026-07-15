<?php
/**
 * Theme settings admin page — Teeman asetukset.
 *
 * Centralises theme-level options (floating CTA, hellobar, etc.)
 * under Appearance → Teeman asetukset.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\ThemeSettings;

const PAGE_SLUG    = 'muuttohaukat-theme-settings';
const OPTION_GROUP = 'muuttohaukat_theme_settings';

/** @return array<string, string> */
function tabs() {
  return [
    'floating-cta' => __('Kelluva CTA', 'muuttohaukat'),
    'hellobar'     => __('Ilmoituspalkki', 'muuttohaukat'),
    'links'        => __('Muut asetukset', 'muuttohaukat'),
  ];
}

/**
 * Sanitize hellobar option array.
 *
 * @param mixed $input Raw input.
 * @return array<string, string>
 */
function sanitizeHelloBarOptions($input) {
  if (!is_array($input)) {
    return [];
  }

  return [
    'text'       => sanitize_text_field($input['text'] ?? ''),
    'bg_color'   => sanitize_hex_color($input['bg_color'] ?? '') ?: '#333333',
    'text_color' => sanitize_hex_color($input['text_color'] ?? '') ?: '#ffffff',
    'link'       => esc_url_raw($input['link'] ?? ''),
    'custom_css' => wp_strip_all_tags($input['custom_css'] ?? ''),
  ];
}

/** Register settings and admin page. */
add_action('admin_init', function () {
  register_setting(OPTION_GROUP, 'kansleri_floating_cta_link', ['sanitize_callback' => 'esc_url_raw']);
  register_setting(OPTION_GROUP, 'kansleri_floating_cta_text', ['sanitize_callback' => 'sanitize_text_field']);
  register_setting(OPTION_GROUP, 'kansleri_floating_cta_second_link', ['sanitize_callback' => 'esc_url_raw']);
  register_setting(OPTION_GROUP, 'kansleri_floating_cta_second_text', ['sanitize_callback' => 'sanitize_text_field']);
  register_setting(OPTION_GROUP, 'kansleri_floating_cta_location', ['sanitize_callback' => 'sanitize_text_field']);
  register_setting(OPTION_GROUP, 'kansleri_floating_cta_only_on_mobile', [
    'sanitize_callback' => function ($value) {
      return absint($value);
    },
    'default' => 0,
  ]);
  register_setting(OPTION_GROUP, 'kansleri_hello_bar_options', [
    'sanitize_callback' => __NAMESPACE__ . '\\sanitizeHelloBarOptions',
    'default'           => [],
  ]);
  register_setting(OPTION_GROUP, 'muuttohaukat_d365_endpoint', [
    'sanitize_callback' => 'esc_url_raw',
    'default'           => '',
  ]);
});

add_action('admin_menu', function () {
  add_theme_page(
    __('Teeman asetukset', 'muuttohaukat'),
    __('Teeman asetukset', 'muuttohaukat'),
    'manage_options',
    PAGE_SLUG,
    __NAMESPACE__ . '\\renderPage'
  );
});

add_action('admin_init', function () {
  if (!current_user_can('update_themes')) {
    return;
  }

  if (empty($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== PAGE_SLUG) {
    return;
  }

  if (empty($_GET['muuttohaukat_check_updates'])) {
    return;
  }

  check_admin_referer('muuttohaukat_check_updates');

  delete_transient('muuttohaukat_github_release');
  delete_site_transient('update_themes');
  wp_update_themes();

  wp_safe_redirect(add_query_arg([
    'page'                      => PAGE_SLUG,
    'tab'                       => 'links',
    'muuttohaukat_updates_checked' => '1',
  ], admin_url('themes.php')));
  exit;
});

add_action('admin_init', function () {
  if (!current_user_can('manage_options')) {
    return;
  }

  if (empty($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== PAGE_SLUG) {
    return;
  }

  if (empty($_GET['muuttohaukat_clear_d365_log'])) {
    return;
  }

  check_admin_referer('muuttohaukat_clear_d365_log');
  delete_option('muuttohaukat_d365_log');

  wp_safe_redirect(add_query_arg([
    'page'                    => PAGE_SLUG,
    'tab'                     => 'links',
    'muuttohaukat_log_cleared' => '1',
  ], admin_url('themes.php')));
  exit;
});

/**
 * Current settings tab from query string.
 */
function currentTab() {
  $tab  = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'floating-cta';
  $tabs = tabs();

  return array_key_exists($tab, $tabs) ? $tab : 'floating-cta';
}

/**
 * Render tab navigation.
 */
function renderTabs($active) {
  $tabs = tabs();
  echo '<nav class="nav-tab-wrapper wp-clearfix">';
  foreach ($tabs as $slug => $label) {
    $url   = add_query_arg(['page' => PAGE_SLUG, 'tab' => $slug], admin_url('themes.php'));
    $class = $slug === $active ? ' nav-tab nav-tab-active' : ' nav-tab';
    echo '<a href="' . esc_url($url) . '" class="' . esc_attr(trim($class)) . '">' . esc_html($label) . '</a>';
  }
  echo '</nav>';
}

/**
 * Render D365 endpoint settings on the links tab.
 */
function renderD365Fields() {
  $stored = get_option('muuttohaukat_d365_endpoint', '');
  $display = $stored;

  if ($display === '' && defined('MUUTTOHAUKAT_D365_ENDPOINT')) {
    $display = MUUTTOHAUKAT_D365_ENDPOINT;
  }

  if ($display === '') {
    $display = \Muuttohaukat\d365_endpoint_default();
  }

  // Copy wp-config value into the field when DB is empty (local dev / migration).
  if ($stored === '' && defined('MUUTTOHAUKAT_D365_ENDPOINT') && $display === MUUTTOHAUKAT_D365_ENDPOINT) {
    $source = 'wp-config.php';
  } elseif ($stored !== '') {
    $source = __('Teeman asetukset', 'muuttohaukat');
  } elseif (defined('MUUTTOHAUKAT_D365_ENDPOINT')) {
    $source = 'wp-config.php';
  } elseif ($display !== \Muuttohaukat\d365_endpoint_default()) {
    $source = __('Oletus', 'muuttohaukat');
  } else {
    $source = __('Oletus (lisää id ja code -parametrit)', 'muuttohaukat');
  }

  $effective = \Muuttohaukat\d365_endpoint();
  $configured = $effective !== '' && $effective !== \Muuttohaukat\d365_endpoint_default();
  ?>
  <form method="post" action="options.php" style="margin-top: 1.5em;">
    <?php settings_fields(OPTION_GROUP); ?>
    <input type="hidden" name="_wp_http_referer" value="<?= esc_attr(add_query_arg(['page' => PAGE_SLUG, 'tab' => 'links'], admin_url('themes.php'))) ?>">
    <div class="card" style="max-width: 640px; padding: 1em 1.25em;">
      <h2 class="title" style="margin-top: 0;"><?= esc_html__('Dynamics 365 / lomakelähetykset', 'muuttohaukat') ?></h2>
      <p class="description">
        <?= esc_html__('Azure Function -osoite, johon tarjouspyyntölomakkeet lähetetään. Tarvitaan LibreForm-lomakkeille (kotimuutto, yritysmuutto, muuttotarvikkeet).', 'muuttohaukat') ?>
      </p>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="muuttohaukat_d365_endpoint"><?= esc_html__('D365 endpoint URL', 'muuttohaukat') ?></label></th>
          <td>
            <input
              type="text"
              id="muuttohaukat_d365_endpoint"
              name="muuttohaukat_d365_endpoint"
              value="<?= esc_attr($display) ?>"
              class="large-text code"
              style="width: 100%;"
            >
            <p class="description">
              <?= esc_html__('Oletusosoite täytetään automaattisesti. Tallenna muutokset, jos haluat tallentaa sen tietokantaan.', 'muuttohaukat') ?>
            </p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?= esc_html__('Tila', 'muuttohaukat') ?></th>
          <td>
            <?php if ($configured) : ?>
              <span style="color: #00a32a;"><?= esc_html__('Konfiguroitu', 'muuttohaukat') ?></span>
              <?php if ($source) : ?>
                <br><span class="description"><?= esc_html(sprintf(__('Lähde: %s', 'muuttohaukat'), $source)) ?></span>
              <?php endif; ?>
            <?php else : ?>
              <span style="color: #d63638;"><?= esc_html__('Puuttuu — lomakkeet eivät välity Dynamics 365:een', 'muuttohaukat') ?></span>
            <?php endif; ?>
          </td>
        </tr>
      </table>
      <?php submit_button(__('Tallenna muutokset', 'muuttohaukat')); ?>
    </div>
  </form>
  <?php
}

/**
 * Locate a readable PHP error log file.
 *
 * @return string|false
 */
function locateErrorLogFile() {
  $candidates = array_filter([
    ini_get('error_log') ?: null,
    WP_CONTENT_DIR . '/debug.log',
    ABSPATH . '../logs/php/error.log',
    dirname(ABSPATH) . '/logs/php/error.log',
  ]);

  foreach ($candidates as $path) {
    if (is_string($path) && $path !== '' && is_readable($path) && is_file($path)) {
      return $path;
    }
  }

  return false;
}

/**
 * Read the last lines from a text file.
 *
 * @param string $file
 * @param int    $lines
 * @return string[]
 */
function tailLogFile($file, $lines = 80) {
  $handle = @fopen($file, 'rb');
  if (!$handle) {
    return [];
  }

  fseek($handle, 0, SEEK_END);
  $pos = ftell($handle);
  $buffer = '';
  $collected = [];

  while ($pos > 0 && count($collected) < $lines) {
    $read = min(4096, $pos);
    $pos -= $read;
    fseek($handle, $pos);
    $buffer = fread($handle, $read) . $buffer;
    $parts = explode("\n", $buffer);
    $buffer = array_shift($parts);

    foreach (array_reverse($parts) as $line) {
      if ($line === '' && empty($collected)) {
        continue;
      }
      array_unshift($collected, $line);
      if (count($collected) >= $lines) {
        break 2;
      }
    }
  }

  if ($buffer !== '' && count($collected) < $lines) {
    array_unshift($collected, $buffer);
  }

  fclose($handle);

  return array_slice($collected, -$lines);
}

/**
 * Render D365 and PHP error log on the links tab.
 */
function renderErrorLog() {
  if (!empty($_GET['muuttohaukat_log_cleared'])) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Loki tyhjennetty.', 'muuttohaukat') . '</p></div>';
  }

  $d365_entries = get_option('muuttohaukat_d365_log', []);
  if (!is_array($d365_entries)) {
    $d365_entries = [];
  }

  $clear_url = wp_nonce_url(add_query_arg([
    'page'                      => PAGE_SLUG,
    'tab'                       => 'links',
    'muuttohaukat_clear_d365_log' => '1',
  ], admin_url('themes.php')), 'muuttohaukat_clear_d365_log');

  $log_file = locateErrorLogFile();
  $php_lines = $log_file ? tailLogFile($log_file, 80) : [];
  $d365_php_lines = array_values(array_filter($php_lines, function ($line) {
    return stripos($line, '[D365]') !== false
      || stripos($line, 'Failed to send confirmation email') !== false;
  }));
  ?>
  <div class="card" style="max-width: 960px; margin-top: 1.5em; padding: 1em 1.25em;">
    <h2 class="title" style="margin-top: 0;"><?= esc_html__('Error log', 'muuttohaukat') ?></h2>

    <h3><?= esc_html__('D365-lomakeloki', 'muuttohaukat') ?></h3>
    <p class="description"><?= esc_html__('Viimeisimmät lomakelähetykset Dynamics 365:een (tallennetaan automaattisesti).', 'muuttohaukat') ?></p>
    <?php if (empty($d365_entries)) : ?>
      <p><em><?= esc_html__('Ei kirjauksia vielä.', 'muuttohaukat') ?></em></p>
    <?php else : ?>
      <textarea readonly rows="10" class="large-text code" style="font-family: Consolas, Monaco, monospace;"><?php
        foreach ($d365_entries as $entry) {
          if (!is_array($entry)) {
            continue;
          }
          echo esc_html(sprintf('[%s] %s', $entry['time'] ?? '', $entry['message'] ?? '')) . "\n";
        }
      ?></textarea>
      <p>
        <a href="<?= esc_url($clear_url) ?>" class="button button-secondary"><?= esc_html__('Tyhjennä D365-loki', 'muuttohaukat') ?></a>
      </p>
    <?php endif; ?>

    <h3 style="margin-top: 1.5em;"><?= esc_html__('PHP error log', 'muuttohaukat') ?></h3>
    <?php if (!$log_file) : ?>
      <p><em><?= esc_html__('Error log -tiedostoa ei löytynyt tai sitä ei voi lukea.', 'muuttohaukat') ?></em></p>
    <?php elseif (empty($d365_php_lines) && empty($php_lines)) : ?>
      <p><em><?= esc_html__('Loki on tyhjä.', 'muuttohaukat') ?></em></p>
      <p class="description"><?= esc_html(sprintf(__('Tiedosto: %s', 'muuttohaukat'), $log_file)) ?></p>
    <?php else : ?>
      <p class="description">
        <?= esc_html(sprintf(__('Tiedosto: %s — näytetään D365- ja lomakevirheet (viimeiset %d riviä).', 'muuttohaukat'), $log_file, count($d365_php_lines) ?: count($php_lines))) ?>
      </p>
      <textarea readonly rows="12" class="large-text code" style="font-family: Consolas, Monaco, monospace;"><?=
        esc_textarea(implode("\n", $d365_php_lines ?: array_slice($php_lines, -30)))
      ?></textarea>
    <?php endif; ?>
  </div>
  <?php
}

/**
 * Render GitHub updater status on the links tab.
 */
function renderUpdateStatus() {
  if (!current_user_can('update_themes')) {
    return;
  }

  $theme     = wp_get_theme(\Muuttohaukat\THEME_SLUG);
  $installed = $theme->get('Version');
  $updates   = get_site_transient('update_themes');
  $pending   = is_object($updates) && !empty($updates->response[\Muuttohaukat\THEME_SLUG]['new_version'])
    ? $updates->response[\Muuttohaukat\THEME_SLUG]['new_version']
    : null;

  if (!empty($_GET['muuttohaukat_updates_checked'])) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Päivitystarkistus suoritettu.', 'muuttohaukat') . '</p></div>';
  }

  $check_url = wp_nonce_url(add_query_arg([
    'page'                       => PAGE_SLUG,
    'tab'                        => 'links',
    'muuttohaukat_check_updates' => '1',
  ], admin_url('themes.php')), 'muuttohaukat_check_updates');
  ?>
  <div class="card" style="max-width: 640px; margin-top: 1.5em; padding: 1em 1.25em;">
    <h2 class="title" style="margin-top: 0;"><?= esc_html__('Teeman päivitykset', 'muuttohaukat') ?></h2>
    <p>
      <?= esc_html(sprintf(__('Asennettu versio: %s', 'muuttohaukat'), $installed)) ?>
      <?php if ($pending) : ?>
        <br><strong><?= esc_html(sprintf(__('Saatavilla: %s', 'muuttohaukat'), $pending)) ?></strong>
        — <a href="<?= esc_url(admin_url('update-core.php')) ?>"><?= esc_html__('Asenna päivitys', 'muuttohaukat') ?></a>
      <?php else : ?>
        <br><?= esc_html__('Ei uusia päivityksiä tai tarkistus vaatii verkkoyhteyden GitHubiin.', 'muuttohaukat') ?>
      <?php endif; ?>
    </p>
    <p>
      <a href="<?= esc_url($check_url) ?>" class="button button-secondary"><?= esc_html__('Tarkista päivitykset nyt', 'muuttohaukat') ?></a>
      <a href="<?= esc_url(admin_url('update-core.php')) ?>" class="button"><?= esc_html__('Avaa päivityssivu', 'muuttohaukat') ?></a>
    </p>
  </div>
  <?php
}

/** Render the theme settings page. */
function renderPage() {
  if (!current_user_can('manage_options')) {
    return;
  }

  $tab = currentTab();
  ?>
  <div class="wrap">
    <h1><?= esc_html__('Teeman asetukset', 'muuttohaukat') ?></h1>
    <?php renderTabs($tab); ?>

    <?php if ($tab === 'links') : ?>
      <?php renderD365Fields(); ?>
      <?php renderErrorLog(); ?>
      <?php renderUpdateStatus(); ?>
      <div class="muuttohaukat-theme-settings-links" style="margin-top: 1.5em; max-width: 640px;">
        <p><?= esc_html__('Nämä asetukset hallitaan muualla WordPress-hallinnassa:', 'muuttohaukat') ?></p>
        <ul style="list-style: disc; padding-left: 1.5em;">
          <li style="margin-bottom: 0.5em;">
            <a href="<?= esc_url(admin_url('customize.php?autofocus[panel]=muuttohaukat_header_panel')) ?>">
              <?= esc_html__('Ulkoasu → Mukauta → Header (logo, navigaatio, CTA-painikkeet)', 'muuttohaukat') ?>
            </a>
          </li>
          <li style="margin-bottom: 0.5em;">
            <a href="<?= esc_url(admin_url('widgets.php')) ?>">
              <?= esc_html__('Ulkoasu → Widgetit (footer-sarakkeet)', 'muuttohaukat') ?>
            </a>
          </li>
          <?php if (function_exists('acf_get_options_page')) : ?>
            <li style="margin-bottom: 0.5em;">
              <a href="<?= esc_url(admin_url('options-general.php?page=acf-options-fi-settings')) ?>">
                <?= esc_html__('Asetukset → FI settings (footer-valikot, copyright)', 'muuttohaukat') ?>
              </a>
            </li>
          <?php endif; ?>
          <li style="margin-bottom: 0.5em;">
            <a href="<?= esc_url(admin_url('nav-menus.php')) ?>">
              <?= esc_html__('Ulkoasu → Valikot', 'muuttohaukat') ?>
            </a>
          </li>
        </ul>
      </div>
    <?php else : ?>
      <form method="post" action="options.php" style="margin-top: 1.5em;">
        <?php settings_fields(OPTION_GROUP); ?>
        <input type="hidden" name="_wp_http_referer" value="<?= esc_attr(add_query_arg(['page' => PAGE_SLUG, 'tab' => $tab], admin_url('themes.php'))) ?>">

        <?php if ($tab === 'floating-cta') : ?>
          <?php renderFloatingCtaFields(); ?>
        <?php elseif ($tab === 'hellobar') : ?>
          <?php renderHellobarFields(); ?>
        <?php endif; ?>

        <?php submit_button(__('Tallenna muutokset', 'muuttohaukat')); ?>
      </form>
    <?php endif; ?>
  </div>
  <?php
}

/** Floating CTA settings fields. */
function renderFloatingCtaFields() {
  ?>
  <h2 class="title"><?= esc_html__('Kelluva CTA', 'muuttohaukat') ?></h2>
  <p class="description">
    <?= esc_html__('Kiinteä painikepalkki sivun alareunassa. Näytetään vain jos vähintään yhden painikkeen teksti on täytetty.', 'muuttohaukat') ?>
  </p>
  <table class="form-table" role="presentation">
    <tr>
      <th scope="row"><label for="kansleri_floating_cta_text"><?= esc_html__('Ensimmäinen painike — teksti', 'muuttohaukat') ?></label></th>
      <td><input type="text" id="kansleri_floating_cta_text" name="kansleri_floating_cta_text" value="<?= esc_attr(get_option('kansleri_floating_cta_text', '')) ?>" class="regular-text"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_floating_cta_link"><?= esc_html__('Ensimmäinen painike — linkki', 'muuttohaukat') ?></label></th>
      <td><input type="url" id="kansleri_floating_cta_link" name="kansleri_floating_cta_link" value="<?= esc_attr(get_option('kansleri_floating_cta_link', '')) ?>" class="regular-text"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_floating_cta_second_text"><?= esc_html__('Toinen painike — teksti', 'muuttohaukat') ?></label></th>
      <td><input type="text" id="kansleri_floating_cta_second_text" name="kansleri_floating_cta_second_text" value="<?= esc_attr(get_option('kansleri_floating_cta_second_text', '')) ?>" class="regular-text"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_floating_cta_second_link"><?= esc_html__('Toinen painike — linkki', 'muuttohaukat') ?></label></th>
      <td><input type="url" id="kansleri_floating_cta_second_link" name="kansleri_floating_cta_second_link" value="<?= esc_attr(get_option('kansleri_floating_cta_second_link', '')) ?>" class="regular-text"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_floating_cta_location"><?= esc_html__('Näytä sivuilla', 'muuttohaukat') ?></label></th>
      <td>
        <select id="kansleri_floating_cta_location" name="kansleri_floating_cta_location">
          <option value="everywhere" <?php selected('everywhere', get_option('kansleri_floating_cta_location', 'everywhere')); ?>><?= esc_html__('Kaikkialla', 'muuttohaukat') ?></option>
          <option value="home" <?php selected('home', get_option('kansleri_floating_cta_location', 'everywhere')); ?>><?= esc_html__('Vain etusivu', 'muuttohaukat') ?></option>
          <option value="pages" <?php selected('pages', get_option('kansleri_floating_cta_location', 'everywhere')); ?>><?= esc_html__('Kaikki sivut', 'muuttohaukat') ?></option>
          <option value="posts" <?php selected('posts', get_option('kansleri_floating_cta_location', 'everywhere')); ?>><?= esc_html__('Kaikki artikkelit', 'muuttohaukat') ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th scope="row"><?= esc_html__('Vain mobiilissa', 'muuttohaukat') ?></th>
      <td>
        <input type="hidden" name="kansleri_floating_cta_only_on_mobile" value="0">
        <label for="kansleri_floating_cta_only_on_mobile">
          <input type="checkbox" id="kansleri_floating_cta_only_on_mobile" name="kansleri_floating_cta_only_on_mobile" value="1" <?php checked(1, (int) get_option('kansleri_floating_cta_only_on_mobile', 0)); ?>>
          <?= esc_html__('Piilota työpöydällä', 'muuttohaukat') ?>
        </label>
      </td>
    </tr>
  </table>
  <?php
}

/** Hellobar settings fields. */
function renderHellobarFields() {
  $options = get_option('kansleri_hello_bar_options', []);
  ?>
  <h2 class="title"><?= esc_html__('Ilmoituspalkki', 'muuttohaukat') ?></h2>
  <p class="description">
    <?= esc_html__('Koko sivun levyinen ilmoituspalkki sivun yläreunassa. Jätä teksti tyhjäksi piilottaaksesi palkin.', 'muuttohaukat') ?>
  </p>
  <table class="form-table" role="presentation">
    <tr>
      <th scope="row"><label for="kansleri_hello_bar_text"><?= esc_html__('Teksti', 'muuttohaukat') ?></label></th>
      <td><input type="text" id="kansleri_hello_bar_text" name="kansleri_hello_bar_options[text]" value="<?= esc_attr($options['text'] ?? '') ?>" class="regular-text"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_hello_bar_link"><?= esc_html__('Linkki', 'muuttohaukat') ?></label></th>
      <td><input type="url" id="kansleri_hello_bar_link" name="kansleri_hello_bar_options[link]" value="<?= esc_attr($options['link'] ?? '') ?>" class="regular-text"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_hello_bar_bg_color"><?= esc_html__('Taustaväri', 'muuttohaukat') ?></label></th>
      <td><input type="text" id="kansleri_hello_bar_bg_color" name="kansleri_hello_bar_options[bg_color]" value="<?= esc_attr($options['bg_color'] ?? '#333333') ?>" class="regular-text" placeholder="#333333"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_hello_bar_text_color"><?= esc_html__('Tekstin väri', 'muuttohaukat') ?></label></th>
      <td><input type="text" id="kansleri_hello_bar_text_color" name="kansleri_hello_bar_options[text_color]" value="<?= esc_attr($options['text_color'] ?? '#ffffff') ?>" class="regular-text" placeholder="#ffffff"></td>
    </tr>
    <tr>
      <th scope="row"><label for="kansleri_hello_bar_custom_css"><?= esc_html__('Lisä-CSS', 'muuttohaukat') ?></label></th>
      <td><textarea id="kansleri_hello_bar_custom_css" name="kansleri_hello_bar_options[custom_css]" rows="5" class="large-text code"><?= esc_textarea($options['custom_css'] ?? '') ?></textarea></td>
    </tr>
  </table>
  <?php
}
