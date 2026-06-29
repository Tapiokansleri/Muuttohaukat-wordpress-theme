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

  /** @var string Theme directory slug. */
  private $theme_slug;

  /** @var string Current theme version. */
  private $version;

  /** @var string Transient key for caching release data. */
  private $cache_key = 'muuttohaukat_github_release';

  /** @var int Cache lifetime in seconds (6 hours). */
  private $cache_ttl = 21600;

  /**
   * @param string $theme_slug Theme directory name.
   * @param string $version    Current version from style.css.
   */
  public function __construct($theme_slug, $version) {
    $this->theme_slug = $theme_slug;
    $this->version    = $version;

    add_filter('pre_set_site_transient_update_themes', [$this, 'check_update']);
    add_filter('themes_api', [$this, 'theme_info'], 10, 3);
    add_filter('upgrader_post_install', [$this, 'post_install'], 10, 3);
  }

  /**
   * Fetch latest release data from GitHub (cached).
   *
   * @return object|false
   */
  private function get_release() {
    $cached = get_transient($this->cache_key);
    if ($cached !== false) {
      return $cached ?: false;
    }

    $url = sprintf('https://api.github.com/repos/%s/releases/latest', $this->repo);
    $response = wp_remote_get($url, [
      'headers' => [
        'Accept' => 'application/vnd.github+json',
      ],
      'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
      set_transient($this->cache_key, 0, 300);
      return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    if (empty($body->tag_name)) {
      set_transient($this->cache_key, 0, 300);
      return false;
    }

    set_transient($this->cache_key, $body, $this->cache_ttl);
    return $body;
  }

  /**
   * Inject update info into WordPress theme update transient.
   *
   * @param object $transient Update transient.
   * @return object
   */
  public function check_update($transient) {
    if (empty($transient->checked) || !isset($transient->checked[$this->theme_slug])) {
      return $transient;
    }

    $release = $this->get_release();
    if (!$release || empty($release->tag_name)) {
      return $transient;
    }

    $remote_version = ltrim($release->tag_name, 'vV');
    if (!version_compare($remote_version, $this->version, '>')) {
      return $transient;
    }

    $zip_url = $this->get_zip_url($release);
    if (!$zip_url) {
      return $transient;
    }

    $transient->response[$this->theme_slug] = [
      'theme'       => $this->theme_slug,
      'new_version' => $remote_version,
      'url'         => sprintf('https://github.com/%s', $this->repo),
      'package'     => $zip_url,
    ];

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
   * After install, move extracted files into the expected theme directory.
   *
   * GitHub ZIPs may extract as Muuttohaukat-wordpress-theme-*; WordPress
   * expects wp-content/themes/muuttohaukat/.
   *
   * @param bool  $response
   * @param array $hook_extra
   * @param array $result
   * @return array
   */
  public function post_install($response, $hook_extra, $result) {
    if (!isset($hook_extra['theme']) || $hook_extra['theme'] !== $this->theme_slug) {
      return $result;
    }

    global $wp_filesystem;

    $theme_dir = get_theme_root() . '/' . $this->theme_slug;
    if ($result['destination'] !== $theme_dir) {
      $wp_filesystem->delete($theme_dir, true);
      $wp_filesystem->move($result['destination'], $theme_dir);
      $result['destination'] = $theme_dir;
    }

    delete_transient($this->cache_key);

    return $result;
  }

  /**
   * Get the ZIP download URL from a release.
   *
   * Prefers a release asset named muuttohaukat.zip, then any .zip asset,
   * then GitHub's auto-generated zipball.
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

    if (!empty($release->zipball_url)) {
      return $release->zipball_url;
    }

    return false;
  }
}

add_action('after_setup_theme', function () {
  $theme = wp_get_theme(get_template());
  new GitHubThemeUpdater(get_template(), $theme->get('Version'));
}, 20);
