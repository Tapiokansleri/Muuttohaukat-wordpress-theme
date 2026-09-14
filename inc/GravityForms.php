<?php
/**
 * Gravity Forms front end: look like the LibreForm quote forms.
 *
 * Colours go through Gravity Forms' own style defaults so the Orbital theme
 * (date picker, validation, multi-page) keeps working; sizes, spacing and
 * toggles live in assets/css/gravity-forms.css.
 *
 * @package Muuttohaukat
 */

namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Default Orbital style settings for every form. Settings saved on a form block still win.
 * Border radius is not set here: GF treats 0 as "unset" and falls back to 3px (see CSS).
 */
add_filter('gform_default_styles', function ($styles) {
  return array_merge(is_array($styles) ? $styles : [], [
    'theme' => 'orbital',
    'inputSize' => 'md',
    'inputBorderColor' => '#d6d6d6',
    'inputBackgroundColor' => '#ffffff',
    'inputColor' => '#0c0c0c',
    'inputPrimaryColor' => '#000000',
    'labelFontSize' => 16,
    'labelColor' => '#0c0c0c',
    'descriptionFontSize' => 16,
    'descriptionColor' => '#0c0c0c',
    'buttonPrimaryBackgroundColor' => '#ffed00',
    'buttonPrimaryColor' => '#000000',
  ]);
});

add_action('wp_enqueue_scripts', function () {
  if (!class_exists('GFForms')) {
    return;
  }

  wp_enqueue_style(
    'muuttohaukat-gravity-forms',
    get_stylesheet_directory_uri() . '/assets/css/gravity-forms.css',
    ['muuttohaukat-content'],
    wp_get_theme()->get('Version')
  );
});
