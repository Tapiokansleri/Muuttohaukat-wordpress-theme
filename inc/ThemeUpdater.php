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

/**
 * Whether a directory contains a Muuttohaukat theme stylesheet.
 *
 * Checking the header prevents an archive with an unexpected root from
 * replacing the canonical theme directory merely because it has a style.css.
 *
 * @param string $directory Theme directory.
 * @return bool
 */
function has_valid_theme_stylesheet($directory) {
  global $wp_filesystem;

  $stylesheet = untrailingslashit($directory) . '/style.css';
  if (!$wp_filesystem->exists($stylesheet) || !$wp_filesystem->is_file($stylesheet)) {
    return false;
  }

  $contents = $wp_filesystem->get_contents($stylesheet);
  if (!is_string($contents) || $contents === '') {
    return false;
  }

  if (!preg_match('/^[ \t\/*#@]*Theme Name:\s*(.+?)\s*$/mi', $contents, $matches)) {
    return false;
  }

  return strcasecmp(trim($matches[1], " \t\n\r\0\x0B*/"), 'Muuttohaukat') === 0;
}

/**
 * Normalize a path without changing its case.
 *
 * Theme directory case is significant on common WordPress hosts.
 *
 * @param string $path Filesystem path.
 * @return string
 */
function normalize_theme_path($path) {
  return untrailingslashit(wp_normalize_path($path));
}

/**
 * Whether two differently-spelled paths resolve to the same location.
 *
 * This is needed for case-only migrations on case-insensitive filesystems.
 *
 * @param string $left  First path.
 * @param string $right Second path.
 * @return bool
 */
function theme_paths_resolve_to_same_location($left, $right) {
  $left_real  = realpath($left);
  $right_real = realpath($right);

  if ($left_real === false || $right_real === false) {
    return false;
  }

  return strcasecmp(wp_normalize_path($left_real), wp_normalize_path($right_real)) === 0;
}

/**
 * Safely move one verified theme directory to the canonical location.
 *
 * The existing canonical directory is retained as a backup until the new
 * target's style.css has been verified. On failure, both source and target
 * are restored to their original locations.
 *
 * @param string $source Source directory containing the updated theme.
 * @param string $target Canonical theme directory.
 * @return bool
 */
function safely_move_theme_to_canonical($source, $target) {
  global $wp_filesystem;

  $source = normalize_theme_path($source);
  $target = normalize_theme_path($target);

  if (!has_valid_theme_stylesheet($source)) {
    return false;
  }

  if ($source === $target) {
    return has_valid_theme_stylesheet($target);
  }

  $same_location = theme_paths_resolve_to_same_location($source, $target);
  $source_nested = strpos($source . '/', $target . '/') === 0;

  // Never replace a different theme that happens to occupy the target path.
  if (
    $wp_filesystem->exists($target) &&
    !$same_location &&
    !$source_nested &&
    !has_valid_theme_stylesheet($target)
  ) {
    return false;
  }

  $root          = normalize_theme_path(dirname($target));
  $token         = str_replace('.', '', uniqid('', true));
  $staging       = $root . '/.muuttohaukat-new-' . $token;
  $backup        = $root . '/.muuttohaukat-backup-' . $token;
  $original      = $source;
  $source_staged = false;

  // A nested archive root and a case-only rename must first leave the target.
  if ($source_nested || $same_location) {
    if (!$wp_filesystem->move($source, $staging, false)) {
      return false;
    }
    $source        = $staging;
    $source_staged = true;
  }

  $target_backed_up = false;
  if ($wp_filesystem->exists($target)) {
    if (!$wp_filesystem->move($target, $backup, false)) {
      if ($source_staged) {
        $wp_filesystem->move($source, $original, false);
      }
      return false;
    }
    $target_backed_up = true;
  }

  if (!$wp_filesystem->move($source, $target, false)) {
    if ($target_backed_up) {
      $wp_filesystem->move($backup, $target, false);
    }
    if ($source_staged) {
      $wp_filesystem->move($source, $original, false);
    }
    return false;
  }

  if (!has_valid_theme_stylesheet($target)) {
    $wp_filesystem->move($target, $source, false);
    if ($target_backed_up) {
      $wp_filesystem->move($backup, $target, false);
    }
    if ($source_staged) {
      $wp_filesystem->move($source, $original, false);
    }
    return false;
  }

  if ($target_backed_up) {
    $wp_filesystem->delete($backup, true);
  }

  return true;
}

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
    $slugs = [];

    foreach (array_merge([$this->theme_slug], LEGACY_THEME_SLUGS) as $slug) {
      if (wp_get_theme($slug)->exists()) {
        $slugs[] = $slug;
      }
    }

    return array_values(array_unique($slugs));
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
   * After install, move extracted files into the expected theme directory.
   *
   * @param bool  $response
   * @param array $hook_extra
   * @param array $result
   * @return bool|\WP_Error
   */
  public function post_install($response, $hook_extra, $result) {
    if (!isset($hook_extra['theme'])) {
      return $response;
    }

    $known = array_merge([$this->theme_slug], LEGACY_THEME_SLUGS);
    if (!in_array($hook_extra['theme'], $known, true)) {
      return $response;
    }

    if (is_wp_error($response) || !$response || empty($result['destination'])) {
      return $response;
    }

    global $wp_filesystem;

    $theme_dir       = normalize_theme_path(get_theme_root() . '/' . $this->theme_slug);
    $source          = normalize_theme_path($this->locate_theme_root($result['destination']));
    $active_template = get_template();
    $active_style    = get_stylesheet();

    if (!safely_move_theme_to_canonical($source, $theme_dir)) {
      return new \WP_Error(
        'muuttohaukat_invalid_theme_update',
        __('The Muuttohaukat update could not be verified or moved to the canonical theme folder.', 'muuttohaukat')
      );
    }

    if (
      in_array($active_template, LEGACY_THEME_SLUGS, true) ||
      in_array($active_style, LEGACY_THEME_SLUGS, true)
    ) {
      switch_theme($this->theme_slug);

      // Remove only the exact legacy folder that was active and has now been
      // successfully replaced by the verified canonical installation.
      $replaced_slugs = array_unique([$active_template, $active_style]);
      foreach ($replaced_slugs as $legacy_slug) {
        if (!in_array($legacy_slug, LEGACY_THEME_SLUGS, true)) {
          continue;
        }

        $legacy_dir = normalize_theme_path(get_theme_root() . '/' . $legacy_slug);
        if (
          $legacy_dir !== $theme_dir
          && $wp_filesystem->exists($legacy_dir)
          && has_valid_theme_stylesheet($theme_dir)
          && has_valid_theme_stylesheet($legacy_dir)
        ) {
          $wp_filesystem->delete($legacy_dir, true);
        }
      }
    }

    delete_transient($this->cache_key);
    delete_site_transient('update_themes');

    return $response;
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
  $active_template = get_template();
  $active_style     = get_stylesheet();
  $target   = $root . '/' . THEME_SLUG;
  $source   = null;

  if (in_array($active_template, LEGACY_THEME_SLUGS, true) && is_dir($root . '/' . $active_template)) {
    $source = $root . '/' . $active_template;
  } elseif (in_array($active_style, LEGACY_THEME_SLUGS, true) && is_dir($root . '/' . $active_style)) {
    $source = $root . '/' . $active_style;
  } else {
    foreach (LEGACY_THEME_SLUGS as $legacy) {
      $legacy_path = $root . '/' . $legacy;
      if (is_dir($legacy_path)) {
        $source = $legacy_path;
        break;
      }
    }
  }

  if ($source === null) {
    update_option('muuttohaukat_theme_folder_migrated', 1, true);
    return;
  }

  require_once ABSPATH . 'wp-admin/includes/file.php';

  global $wp_filesystem;
  if (!WP_Filesystem()) {
    return;
  }

  if (!has_valid_theme_stylesheet($source)) {
    return;
  }

  if (
    $wp_filesystem->exists($target) &&
    !theme_paths_resolve_to_same_location($source, $target)
  ) {
    $existing = wp_get_theme(THEME_SLUG);
    $incoming = wp_get_theme(basename($source));
    $existing_ver = $existing->exists() ? $existing->get('Version') : '0';
    $incoming_ver = $incoming->exists() ? $incoming->get('Version') : '0';

    if (
      has_valid_theme_stylesheet($target) &&
      !version_compare($incoming_ver, $existing_ver, '>')
    ) {
      if (
        in_array($active_template, LEGACY_THEME_SLUGS, true) ||
        in_array($active_style, LEGACY_THEME_SLUGS, true)
      ) {
        switch_theme(THEME_SLUG);
      }
      update_option('muuttohaukat_theme_folder_migrated', 1, true);
      return;
    }
  }

  if (!safely_move_theme_to_canonical($source, $target)) {
    return;
  }

  if (
    in_array($active_template, LEGACY_THEME_SLUGS, true) ||
    in_array($active_style, LEGACY_THEME_SLUGS, true)
  ) {
    switch_theme(THEME_SLUG);
  }

  update_option('muuttohaukat_theme_folder_migrated', 1, true);
}

add_action('after_setup_theme', __NAMESPACE__ . '\\maybe_migrate_theme_folder', 10);
add_action('after_setup_theme', function () {
  new GitHubThemeUpdater(THEME_SLUG);
}, 20);
