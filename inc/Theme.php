<?php
/**
 * Theme support and core configuration.
 *
 * Registers theme features, text domain, content width,
 * and block editor settings.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Theme;

add_action('after_setup_theme', function () {
  $app = \Muuttohaukat\app();

  // Load theme text domain for translations.
  load_theme_textdomain('muuttohaukat', get_template_directory() . '/languages');

  // Content width used by oEmbeds and the editor.
  $GLOBALS['content_width'] = 1350;

  add_theme_support('custom-logo', [
    'flex-width'  => true,
    'flex-height' => true,
  ]);

  add_theme_support('post-thumbnails');
  add_theme_support('title-tag');
  add_theme_support('html5', [
    'search-form',
    'comment-list',
    'gallery',
    'caption',
  ]);

  // Gutenberg support.
  add_theme_support('align-wide');
  add_theme_support('responsive-embeds');
  add_theme_support('editor-styles');
  add_theme_support('wp-block-styles');

  add_editor_style([
    'assets/css/00-tokens.css',
    'assets/css/editor-fonts.css',
    'assets/css/editor.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
    'assets/css/icon-picker.css',
    'fl-builder/modules/muuttohaukat-painike/css/frontend.css',
    'assets/css/03-button-chevron.css',
    'assets/css/landing.css',
  ]);

  // Font Awesome + icon picker: inc/FontAwesome.php

  // Disable colour options in the editor.
  add_theme_support('editor-color-palette', []);
  add_theme_support('disable-custom-colors');

  add_theme_support('editor-font-sizes', [
    ['name' => $app->translations->getText('Font-size: Much smaller'), 'slug' => 'much-smaller', 'size' => 12],
    ['name' => $app->translations->getText('Font-size: Smaller'),      'slug' => 'smaller',      'size' => 14],
    ['name' => $app->translations->getText('Font-size: Normal'),       'slug' => 'normal',        'size' => 18],
    ['name' => $app->translations->getText('Font-size: Large'),        'slug' => 'large',         'size' => 22],
  ]);

  add_theme_support('custom-line-height');
  remove_theme_support('core-block-patterns');
});
