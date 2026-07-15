<?php
/**
 * Theme setup: class autoloading, app initialization, and helpers.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Load classes (App, Block, RestRoute, Translations, Transientify, NavWalker).
 */
foreach (glob(get_template_directory() . '/classes/class-*.php') as $filename) {
  require_once $filename;
}

/**
 * Initialize the App singleton.
 */
$app = App::init([
  'blocks' => glob(get_template_directory() . '/blocks/*.php'),
  'templates' => glob(get_template_directory() . '/template-parts/*.php'),
  'languageSlugs' => ['fi'],
]);

/**
 * Return the App singleton.
 */
function app() {
  return App::init();
}

/**
 * Debug helper — outputs data in a readable <pre> block.
 */
function debug($data) {
  echo '<pre>';
  echo htmlspecialchars(var_export($data, true));
  echo '</pre>';
}

/**
 * Disable WordPress fatal error recovery emails on non-production environments.
 */
if ((getenv('WP_ENV') ?: 'production') !== 'production') {
  add_filter('recovery_mode_email', function ($email) {
    $email['to'] = '';
    return $email;
  });
}
