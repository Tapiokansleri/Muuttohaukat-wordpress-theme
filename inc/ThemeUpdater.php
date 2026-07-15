<?php
/**
 * GitHub-based theme updater.
 *
 * Checks the public GitHub repo for new releases and surfaces them
 * in Appearance → Themes and Dashboard → Updates.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/** Canonical theme directory name (must match release ZIP root folder). */
const THEME_SLUG = 'Muuttohaukat';

/** Legacy folder names from branch-ZIP or lowercase installs. */
const LEGACY_THEME_SLUGS = ['Muuttohaukat-wordpress-theme-main', 'muuttohaukat'];

class GitHubThemeUpdater {

  /** @var string GitHub owner/repo. */
  private $repo = 'Tapiokansleri/Muuttohaukat-wordpress-theme';

  /** @var string Theme directory slug (parent template folder). */
  private $theme_slug;

  /** @var string Transient key for caching release data. */
  private $cache_key = 'muuttohaukat_github_release';

  /** @var int Cache lifetime in seconds (6 hours). */
  private $cache_ttl = 21600;

  /**
   * @param string $theme_slug Theme directory name.
   */
  public function __construct($theme_slug) {
    $this->theme_slug = $theme_slug;

    add_filter('pre_set_site_transient_update_themes', [$this, 'inject_update']);
    add_filter('site_transient_update_themes', [$this, 'inject_update']);
    add_filter('themes_api', [$this, 'theme_info'], 10, 3);
    add_filter('upgrader_post_install', [$this, 'post_install'], 10, 3);

    add_action('load-update-core.php', [$this, 'refresh_release_cache']);
    add_action('load-themes.php', [$this, 'refresh_release_cache']);
    add_action('load-update.php', [$this, 'refresh_release_cache']);
  }

  /**
   * Clear cached GitHub release data on update admin screens.
   */
  public function refresh_release_cache() {
    if (!current_user_can('update_themes')) {
      return;
    }

    delete_transient($this->cache_key);
  }

  /**
   * Slugs that may currently hold this theme on disk.
   *
   * @return string[]
   */
  private function installed_slugs() {
    $slugs = [THEME_SLUG];

    foreach (LEGACY_THEME_SLUGS as $legacy) {
      if (wp_get_theme($legacy)->exists()) {
        $slugs[] = $legacy;
      }
    }

    return array_values(array_unique($slugs));
  }

  /**
   * Whether the canonical theme directory is installed.
   */
  private function theme_is_installed() {
    return wp_get_theme($this->theme_slug)->exists();
  }

  /**
   * Installed version from style.css (canonical or legacy folder).
   *
   * @return string|null
   */
  private function get_installed_version() {
    foreach ($this->installed_slugs() as $slug) {
      $theme = wp_get_theme($slug);
      if ($theme->exists()) {
        return $theme->get('Version');
      }
    }

    return null;
  }

  /**
   * Fetch latest release data from GitHub (cached).
   *
   * @param bool $force Skip cache.
   * @return object|false
   */
  private function get_release($force = false) {
    if (!$force) {
      $cached = get_transient($this->cache_key);
      if ($cached !== false) {
        return $cached ?: false;
      }
    }

    $url = sprintf('https://api.github.com/repos/%s/releases/latest', $this->repo);
    $response = wp_remote_get($url, [
      'timeout' => 15,
      'headers' => [
        'Accept'     => 'application/vnd.github+json',
        'User-Agent' => sprintf('Muuttohaukat-Theme-Updater/%s; %s', $this->get_installed_version() ?: 'unknown', home_url('/')),
      ],
    ]);

    if (is_wp_error($response)) {
      set_transient($this->cache_key, 0, MINUTE_IN_SECONDS * 5);
      return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
      set_transient($this->cache_key, 0, MINUTE_IN_SECONDS * 5);
      return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    if (empty($body->tag_name)) {
      set_transient($this->cache_key, 0, MINUTE_IN_SECONDS * 5);
      return false;
    }

    set_transient($this->cache_key, $body, $this->cache_ttl);
    return $body;
  }

  /**
   * Build update payload when a newer GitHub release exists.
   *
   * @return array<string, string>|false
   */
  private function get_update_payload() {
    $installed_version = $this->get_installed_version();
    if ($installed_version === null) {
      return false;
    }

    $release = $this->get_release();
    if (!$release || empty($release->tag_name)) {
      return false;
    }

    $remote_version = ltrim($release->tag_name, 'vV');
    if (!version_compare($remote_version, $installed_version, '>')) {
      return false;
    }

    $zip_url = $this->get_zip_url($release);
    if (!$zip_url) {
      return false;
    }

    return [
      'theme'       => $this->theme_slug,
      'new_version' => $remote_version,
      'url'         => sprintf('https://github.com/%s', $this->repo),
      'package'     => $zip_url,
    ];
  }

  /**
   * Inject GitHub update info into the theme update transient.
   *
   * Hooked both when WP saves the transient and when it is read, because
   * wp_update_themes() often returns early (cache timeout / WP.org failure)
   * without ever running pre_set_site_transient_update_themes.
   *
   * @param false|object $transient Update transient.
   * @return object
   */
  public function inject_update($transient) {
    if (!is_object($transient)) {
      $transient = new \stdClass();
    }

    if (!isset($transient->response) || !is_array($transient->response)) {
      $transient->response = [];
    }

    if (!isset($transient->checked) || !is_array($transient->checked)) {
      $transient->checked = [];
    }

    if (!isset($transient->no_update) || !is_array($transient->no_update)) {
      $transient->no_update = [];
    }

    $installed_version = $this->get_installed_version();
    if ($installed_version === null) {
      return $transient;
    }

    $update = $this->get_update_payload();

    foreach ($this->installed_slugs() as $slug) {
      $transient->checked[$slug] = $installed_version;

      if ($update) {
        $payload = $update;
        $payload['theme'] = $slug;
        $transient->response[$slug] = $payload;
        unset($transient->no_update[$slug]);
      } else {
        unset($transient->response[$slug]);
      }
    }

    return $transient;
  }

  /**
   * Provide theme info for the "View version X details" modal.
   *
   * @param false|object|array $result
   * @param string             $action
   * @param object             $args
   * @return false|object
   */
  public function theme_info($result, $action, $args) {
    if ($action !== 'theme_information') {
      return $result;
    }

    if (!isset($args->slug)) {
      return $result;
    }

    $known = array_merge([$this->theme_slug], LEGACY_THEME_SLUGS);
    if (!in_array($args->slug, $known, true)) {
      return $result;
    }

    $release = $this->get_release();
    if (!$release) {
      return $result;
    }

    $remote_version = ltrim($release->tag_name, 'vV');
    $theme          = wp_get_theme($args->slug);
    if (!$theme->exists()) {
      $theme = wp_get_theme($this->theme_slug);
    }

    return (object) [
      'name'          => $theme->get('Name'),
      'slug'          => $args->slug,
      'version'       => $remote_version,
      'author'        => $theme->get('Author'),
      'homepage'      => sprintf('https://github.com/%s', $this->repo),
      'download_link' => $this->get_zip_url($release),
      'sections'      => [
        'description' => $theme->get('Description'),
        'changelog'   => nl2br(esc_html($release->body ?? '')),
      ],
    ];
  }

  /**
   * Find the directory that contains style.css (handles GitHub zipballs and
   * archives that wrap the theme in a repo-named subfolder).
   *
   * @param string $path Directory to search.
   * @return string
   */
  private function locate_theme_root($path) {
    global $wp_filesystem;

    if ($wp_filesystem->exists($path . '/style.css')) {
      return $path;
    }

    $list = $wp_filesystem->dirlist($path, false, false);
    if (!is_array($list)) {
      return $path;
    }

    foreach ($list as $name => $info) {
      if (empty($info['type']) || $info['type'] !== 'd') {
        continue;
      }

      $candidate = $path . '/' . $name;
      if ($wp_filesystem->exists($candidate . '/style.css')) {
        return $candidate;
      }
    }

    return $path;
  }

  /**
   * Move a nested theme folder's contents into the expected theme directory.
   *
   * @param string $nested_dir Directory containing style.css.
   * @param string $theme_dir  Target theme directory slug path.
   */
  private function promote_nested_theme($nested_dir, $theme_dir) {
    global $wp_filesystem;

    if ($nested_dir === $theme_dir) {
      return;
    }

    $list = $wp_filesystem->dirlist($nested_dir, false, false);
    if (!is_array($list)) {
      return;
    }

    foreach ($list as $name => $info) {
      $wp_filesystem->move($nested_dir . '/' . $name, $theme_dir . '/' . $name, true);
    }

    $wp_filesystem->delete($nested_dir, true);
  }

  /**
   * After install, move extracted files into the expected theme directory.
   *
   * @param bool  $response
   * @param array $hook_extra
   * @param array $result
   * @return array
   */
  public function post_install($response, $hook_extra, $result) {
    if (!isset($hook_extra['theme'])) {
      return $result;
    }

    $known = array_merge([$this->theme_slug], LEGACY_THEME_SLUGS);
    if (!in_array($hook_extra['theme'], $known, true)) {
      return $result;
    }

    global $wp_filesystem;

    $theme_dir = get_theme_root() . '/' . $this->theme_slug;
    $source    = $this->locate_theme_root($result['destination']);

    if ($source !== $theme_dir) {
      if ($wp_filesystem->exists($theme_dir)) {
        $wp_filesystem->delete($theme_dir, true);
      }
      $wp_filesystem->move($source, $theme_dir);
      $result['destination'] = $theme_dir;
    }

    if (!$wp_filesystem->exists($theme_dir . '/style.css')) {
      $nested = $this->locate_theme_root($theme_dir);
      if ($nested !== $theme_dir) {
        $this->promote_nested_theme($nested, $theme_dir);
      }
    }

    foreach (LEGACY_THEME_SLUGS as $legacy) {
      $legacy_dir = get_theme_root() . '/' . $legacy;
      if ($legacy_dir !== $theme_dir && $wp_filesystem->exists($legacy_dir)) {
        $wp_filesystem->delete($legacy_dir, true);
      }
    }

    $active = get_stylesheet();
    if (in_array($active, LEGACY_THEME_SLUGS, true) || $active !== $this->theme_slug) {
      switch_theme($this->theme_slug);
    }

    delete_transient($this->cache_key);
    delete_site_transient('update_themes');

    return $result;
  }

  /**
   * Get the ZIP download URL from a release.
   *
   * @param object $release GitHub release object.
   * @return string|false
   */
  private function get_zip_url($release) {
    if (empty($release->assets) || !is_array($release->assets)) {
      return false;
    }

    $preferred = ['Muuttohaukat.zip', 'muuttohaukat.zip'];

    foreach ($preferred as $name) {
      foreach ($release->assets as $asset) {
        if (!empty($asset->browser_download_url) && $asset->name === $name) {
          return $asset->browser_download_url;
        }
      }
    }

    return false;
  }
}

/**
 * One-time migration: rename legacy theme folders to the canonical slug.
 */
function maybe_migrate_theme_folder() {
  if (get_option('muuttohaukat_theme_folder_migrated')) {
    return;
  }

  $root     = get_theme_root();
  $active   = get_template();
  $target   = $root . '/' . THEME_SLUG;
  $known    = array_merge([THEME_SLUG], LEGACY_THEME_SLUGS);
  $source   = null;

  if (in_array($active, LEGACY_THEME_SLUGS, true) && is_dir($root . '/' . $active)) {
    $source = $root . '/' . $active;
  } else {
    foreach (LEGACY_THEME_SLUGS as $legacy) {
      $legacy_path = $root . '/' . $legacy;
      if (is_dir($legacy_path)) {
        $source = $legacy_path;
        break;
      }
    }
  }

  if ($source === null || $source === $target) {
    update_option('muuttohaukat_theme_folder_migrated', 1, true);
    return;
  }

  require_once ABSPATH . 'wp-admin/includes/file.php';

  global $wp_filesystem;
  if (!WP_Filesystem()) {
    return;
  }

  if ($wp_filesystem->exists($target)) {
    $existing = wp_get_theme(THEME_SLUG);
    $incoming = wp_get_theme(basename($source));
    $existing_ver = $existing->exists() ? $existing->get('Version') : '0';
    $incoming_ver = $incoming->exists() ? $incoming->get('Version') : '0';

    if (version_compare($incoming_ver, $existing_ver, '>')) {
      $wp_filesystem->delete($target, true);
    } else {
      update_option('muuttohaukat_theme_folder_migrated', 1, true);
      return;
    }
  }

  if (!$wp_filesystem->move($source, $target)) {
    return;
  }

  if (in_array($active, $known, true)) {
    switch_theme(THEME_SLUG);
  }

  update_option('muuttohaukat_theme_folder_migrated', 1, true);
}

add_action('after_setup_theme', __NAMESPACE__ . '\\maybe_migrate_theme_folder', 10);
add_action('after_setup_theme', function () {
  new GitHubThemeUpdater(THEME_SLUG);
}, 20);
