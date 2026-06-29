<?php
namespace Muuttohaukat;

/**
 * Transient caching with role-based key separation.
 *
 * Stores cached data per user role so that logged-in editors see
 * fresh content while visitors get cached responses. Supports
 * bypass via URL parameter or user capability.
 *
 * @package Muuttohaukat
 */
class Transientify {
  const DEFAULT_EXPIRY = 360;

  /** @var string Computed transient key. */
  public $key;

  /** @var int Cache lifetime in seconds. */
  public $expires = 0;

  /** @var bool Whether to skip the cache. */
  public $bypass = false;

  /** @var bool Enable gzip compression for cached data. */
  public static $compress = false;

  /**
   * @param string $key              Unique cache key.
   * @param array  $transientOptions expires, bypassPermissions, bypassKey, type.
   */
  public function __construct(string $key = null, $transientOptions = []) {
    if (is_null($key)) {
      throw new \Exception('No key provided');
    }

    $transientOptions = array_merge([
      'expires'           => self::DEFAULT_EXPIRY,
      'bypassPermissions' => [],
      'bypassKey'         => 'FORCE_FRESH',
      'type'              => 'general',
    ], $transientOptions);

    $type = $transientOptions['type'];
    $this->key = "k1t|{$type}|";

    $user = wp_get_current_user();
    if ($user && $user->ID) {
      $data = get_userdata($user->ID);
      $primaryRole = $data->roles[0] ?? 'visitor';
      $this->key .= "{$primaryRole}|";
    } else {
      $this->key .= 'visitor|';
    }

    $this->key .= $key;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $bypassParam = ($_GET['bypass'] ?? false) === $transientOptions['bypassKey'];
    $bypassPermission = (bool) count(array_filter($transientOptions['bypassPermissions'], 'current_user_can'));

    $this->bypass = $bypassParam || $bypassPermission;
    $this->expires = $transientOptions['expires'];

    if (!get_option('muuttohaukat_transients_enabled', false)) {
      $this->bypass = true;
    }
  }

  /**
   * Delete this transient from the database.
   *
   * @return bool True if deleted, false otherwise.
   */
  public function delete() {
    return delete_transient($this->key);
  }

  /**
   * Get cached data or execute the callback to generate it.
   *
   * @param callable    $dataCb     Receives this Transientify instance; should call ->set().
   * @param string|null &$missReason Set to 'Miss', 'Bypass', or null (hit).
   * @return mixed Cached or freshly generated data.
   */
  public function get(callable $dataCb, &$missReason = '') {
    $missReason = null;

    if (!$this->bypass) {
      $transient = get_transient($this->key);

      if ($transient) {
        if (self::$compress) {
          $transient = gzuncompress($transient);
        }

        $transient = unserialize($transient);
      } else {
        $transient = false;
      }

      if ($transient !== false) {
        return $transient;
      }

      $missReason = 'Miss';
    } else {
      $missReason = 'Bypass';
    }

    return $dataCb($this);
  }

  /**
   * Store data in the transient cache.
   *
   * @param mixed $data Data to cache.
   * @return mixed The same data, for chaining.
   */
  public function set($data) {
    if (!$this->bypass) {
      $copy = serialize($data);

      set_transient(
        $this->key,
        self::$compress ? gzcompress($copy) : $copy,
        $this->expires
      );
    }

    return $data;
  }
}
