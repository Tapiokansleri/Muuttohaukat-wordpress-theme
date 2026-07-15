<?php
/**
 * Font Awesome + shared icon-picker assets.
 *
 * Loads FA on every frontend page and inside the block editor iframe.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\FontAwesome;

const STYLE_HANDLE = 'muuttohaukat-font-awesome';
const FA_VERSION   = '6.5.1';
const FA_CDN_URL     = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';

/**
 * Register Font Awesome and the editor icon-picker script/styles.
 */
function register_assets() {
  $theme_uri = get_template_directory_uri();
  $version   = wp_get_theme()->get('Version');

  wp_register_style(STYLE_HANDLE, FA_CDN_URL, [], FA_VERSION);

  wp_register_script(
    'mh-icon-picker',
    $theme_uri . '/assets/js/icon-picker.js',
    ['wp-element', 'wp-i18n'],
    $version,
    true
  );

  wp_register_style(
    'mh-icon-picker',
    $theme_uri . '/assets/css/icon-picker.css',
    [STYLE_HANDLE],
    $version
  );
}

/**
 * Enqueue FA if it is not already queued.
 */
function enqueue_font_awesome() {
  if (!wp_style_is(STYLE_HANDLE, 'registered')) {
    wp_register_style(STYLE_HANDLE, FA_CDN_URL, [], FA_VERSION);
  }

  if (!wp_style_is(STYLE_HANDLE, 'enqueued')) {
    wp_enqueue_style(STYLE_HANDLE);
  }
}

add_action('init', __NAMESPACE__ . '\\register_assets', 5);

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_font_awesome', 5);

// Editor iframe + frontend whenever block assets load.
add_action('enqueue_block_assets', __NAMESPACE__ . '\\enqueue_font_awesome');

add_action('enqueue_block_editor_assets', function () {
  enqueue_font_awesome();
  wp_enqueue_style('mh-icon-picker');
  wp_enqueue_script('mh-icon-picker');
});
