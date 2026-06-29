<?php
/**
 * Landing page template glue:
 *   1. Register the native route-calculator block (blocks/route-calculator/).
 *   2. Auto-populate post_content with default landing blocks when a page is
 *      saved with the landing template AND its content is empty.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Register all native blocks under blocks/<slug>/block.json
 * (route-calculator + the seven landing-* section blocks).
 */
add_action('init', function () {
  foreach (glob(get_template_directory() . '/blocks/*/block.json') as $manifest) {
    register_block_type(dirname($manifest));
  }
});

/**
 * Editor sidebar: "Muuttohaukat Landing" panel with an Insert button that
 * appends the full 7-section landing layout to the post being edited.
 */
add_action('enqueue_block_editor_assets', function () {
  $handle = 'mh-landing-inserter';
  $rel    = '/assets/js/landing-inserter.js';
  $path   = get_template_directory() . $rel;
  if (!file_exists($path)) return;

  wp_enqueue_script(
    $handle,
    get_template_directory_uri() . $rel,
    ['wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-blocks', 'wp-data', 'wp-i18n'],
    filemtime($path),
    true
  );

  wp_localize_script($handle, 'MhLandingInserter', [
    'markup' => function_exists(__NAMESPACE__ . '\\landing_default_content')
      ? landing_default_content()
      : '',
  ]);
});

/**
 * Auto-populate the page content with the default landing block layout
 * the first time a page is saved with the landing template AND has empty
 * content. Tracked by the `_landing_initialized` post meta flag.
 */
add_action('save_post_page', __NAMESPACE__ . '\\landing_autoload_default_content', 20, 3);

function landing_autoload_default_content($post_id, $post, $update) {
  if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  $template = get_post_meta($post_id, '_wp_page_template', true);
  if ($template !== 'template-landing-page.php') return;

  if (get_post_meta($post_id, '_landing_initialized', true)) return;

  if (trim((string) $post->post_content) !== '') {
    // Page already has content — don't overwrite, just mark as initialized.
    update_post_meta($post_id, '_landing_initialized', '1');
    return;
  }

  $default = function_exists(__NAMESPACE__ . '\\landing_default_content')
    ? landing_default_content()
    : '';

  if ($default === '') return;

  // Re-entrant safety.
  remove_action('save_post_page', __NAMESPACE__ . '\\landing_autoload_default_content', 20);
  wp_update_post([
    'ID'           => $post_id,
    'post_content' => $default,
  ]);
  add_action('save_post_page', __NAMESPACE__ . '\\landing_autoload_default_content', 20, 3);

  update_post_meta($post_id, '_landing_initialized', '1');
}
