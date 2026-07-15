<?php
/**
 * Block editor sidebar plugin — "Add landing page blocks" button.
 *
 * Registers a Gutenberg PluginDocumentSettingPanel on every block-editor
 * screen (pages + all custom post types). Pressing the button appends the
 * full default landing layout (all 7 sections) to the end of the current
 * content.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

add_action('enqueue_block_editor_assets', function () {
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen) return;

  // Skip on screens with no post context (Site Editor, etc.).
  $post_type = $screen->post_type ?? '';
  if ($post_type === '' || $post_type === 'attachment') return;

  $theme_uri = get_template_directory_uri();
  $version   = wp_get_theme()->get('Version');

  // Load landing styles inside the editor so block previews match front-end.
  wp_enqueue_style('mh-tokens-editor',  $theme_uri . '/assets/css/00-tokens.css', [], $version);
  wp_enqueue_style('mh-landing-editor', $theme_uri . '/assets/css/landing.css', ['mh-tokens-editor'], $version);

  wp_enqueue_script(
    'mh-landing-sidebar',
    $theme_uri . '/assets/js/landing-sidebar.js',
    ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-blocks'],
    $version,
    true
  );

  $content = function_exists(__NAMESPACE__ . '\\landing_default_content')
    ? landing_default_content()
    : '';

  wp_localize_script('mh-landing-sidebar', 'mhLandingSidebar', [
    'content' => $content,
    'strings' => [
      'panelTitle'  => __('Muuttohaukat Landing', 'muuttohaukat'),
      'description' => __('Insert the full Muuttohaukat landing page layout (7 sections) at the end of this page.', 'muuttohaukat'),
      'button'      => __('Add landing page blocks', 'muuttohaukat'),
      'unavailable' => __('Landing content is not available.', 'muuttohaukat'),
      'empty'       => __('No blocks to insert.', 'muuttohaukat'),
    ],
  ]);
});
