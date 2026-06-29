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
   * Whether this theme directory is installed.
   */
  private function theme_is_installed() {
    return wp_get_theme($this->theme_slug)->exists();
  }

  /**
   * Installed version from style.css.
   *
   * @return string|null
   */
  private function get_installed_version() {
    if (!$this->theme_is_installed()) {
      return null;
    }

    return wp_get_theme($this->theme_slug)->get('Version');
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

    $transient->checked[$this->theme_slug] = $installed_version;

    $update = $this->get_update_payload();
    if ($update) {
      $transient->response[$this->theme_slug] = $update;
      unset($transient->no_update[$this->theme_slug]);
    } else {
      unset($transient->response[$this->theme_slug]);
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

    if (!isset($args->slug) || $args->slug !== $this->theme_slug) {
      return $result;
    }

    $release = $this->get_release();
    if (!$release) {
      return $result;
    }

    $remote_version = ltrim($release->tag_name, 'vV');
    $theme          = wp_get_theme($this->theme_slug);

    return (object) [
      'name'          => $theme->get('Name'),
      'slug'          => $this->theme_slug,
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
   * Whether $path is a strict subdirectory of $parent.
   *
   * @param string $path
   * @param string $parent
   * @return bool
   */
  private function path_is_within($path, $parent) {
    $path   = trailingslashit(wp_normalize_path($path));
    $parent = trailingslashit(wp_normalize_path($parent));

    return $path !== $parent && strpos($path, $parent) === 0;
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
   * @param bool|WP_Error $response
   * @param array         $hook_extra
   * @param array         $result
   * @return bool|WP_Error
   */
  public function post_install($response, $hook_extra, $result) {
    if (!isset($hook_extra['theme']) || $hook_extra['theme'] !== $this->theme_slug) {
      return $response;
    }

    if (is_wp_error($response)) {
      return $response;
    }

    if (empty($result['destination'])) {
      return $response;
    }

    global $wp_filesystem;

    $theme_dir   = wp_normalize_path(get_theme_root() . '/' . $this->theme_slug);
    $destination = wp_normalize_path($result['destination']);
    $theme_root  = wp_normalize_path($this->locate_theme_root($destination));

    // Zip extracted into a subfolder of the upgrade/temp destination.
    if ($theme_root !== $destination && $this->path_is_within($theme_root, $destination)) {
      $this->promote_nested_theme($theme_root, $destination);
      $theme_root = $destination;
    }

    // Zip extracted into a subfolder of the live theme directory — flatten
    // only; never delete the parent folder in this case.
    if ($theme_root !== $theme_dir && $this->path_is_within($theme_root, $theme_dir)) {
      $this->promote_nested_theme($theme_root, $theme_dir);
      $theme_root = $theme_dir;
    }

    // Zip extracted outside wp-content/themes/muuttohaukat (upgrade temp dir).
    if ($theme_root !== $theme_dir && !$this->path_is_within($theme_root, $theme_dir)) {
      if (!$wp_filesystem->exists($theme_root . '/style.css')) {
        return new \WP_Error(
          'muuttohaukat_theme_missing',
          __('Theme update package is missing style.css.', 'muuttohaukat')
        );
      }

      $staged = $theme_dir . '.github-stage-' . wp_unique_id();
      if (!$wp_filesystem->move($theme_root, $staged, true)) {
        return new \WP_Error(
          'muuttohaukat_theme_stage',
          __('Could not stage the theme update.', 'muuttohaukat')
        );
      }

      if ($wp_filesystem->exists($theme_dir)) {
        $wp_filesystem->delete($theme_dir, true);
      }

      if (!$wp_filesystem->move($staged, $theme_dir)) {
        if ($wp_filesystem->exists($staged)) {
          $wp_filesystem->move($staged, $theme_dir);
        }

        return new \WP_Error(
          'muuttohaukat_theme_install',
          __('Could not install the theme update.', 'muuttohaukat')
        );
      }

      $theme_root = $theme_dir;
    }

    if (!$wp_filesystem->exists($theme_dir . '/style.css')) {
      return new \WP_Error(
        'muuttohaukat_theme_missing_dir',
        sprintf(
          /* translators: %s: theme directory slug */
          __('The theme directory %s does not exist.', 'muuttohaukat'),
          $this->theme_slug
        )
      );
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
    if (!empty($release->assets) && is_array($release->assets)) {
      foreach ($release->assets as $asset) {
        if (!empty($asset->browser_download_url) && $asset->name === 'muuttohaukat.zip') {
          return $asset->browser_download_url;
        }
      }

      foreach ($release->assets as $asset) {
        if (!empty($asset->browser_download_url) && strpos($asset->name, '.zip') !== false) {
          return $asset->browser_download_url;
        }
      }
    }

    return false;
  }
}

add_action('after_setup_theme', function () {
  new GitHubThemeUpdater(get_template());
}, 20);
