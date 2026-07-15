<?php
/**
 * Editor configuration and block render utilities.
 *
 * Configures TinyMCE style formats for ACF classic editor fields
 * and wraps orphan block content in prose containers.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Default field values shared by all ACF block render() methods.
 *
 * @return array Base fields that every block can expect.
 */
function getDefaultBlockRenderSettings() {
  return [];
}

/**
 * Wrap orphan (non-block) content in a prose container.
 *
 * Content without a blockName is freeform HTML from the classic editor
 * or between blocks. This wraps it for consistent styling.
 */
add_filter('render_block', function ($block_content, $block) {
  if (null === $block['blockName'] && !empty($block_content) && !ctype_space($block_content)) {
    $block_content = '<div class="wrapper flex my-4 items-center justify-center lg:my-10"><div class="prose">' . $block_content . '</div></div>';
  }
  return $block_content;
}, 10, 2);

/**
 * Register custom style formats for TinyMCE (used in ACF WYSIWYG fields).
 */
add_filter('tiny_mce_before_init', function ($init) {
  $style_formats = [
    ['title' => 'Highlight',    'classes' => 'highlight',    'inline' => 'span'],
    ['title' => 'Much bigger',  'classes' => 'much-bigger',  'inline' => 'span'],
    ['title' => 'Bigger',       'classes' => 'bigger',       'inline' => 'span'],
    ['title' => 'Smaller',      'classes' => 'smaller',      'inline' => 'span'],
    ['title' => 'Much smaller', 'classes' => 'much-smaller', 'inline' => 'span'],
  ];

  $init['style_formats'] = wp_json_encode($style_formats);

  return $init;
});

/**
 * Add the style select dropdown to the TinyMCE toolbar.
 */
add_filter('mce_buttons_2', function ($buttons) {
  array_unshift($buttons, 'styleselect');
  return $buttons;
});
