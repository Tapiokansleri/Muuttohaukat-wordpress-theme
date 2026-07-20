<?php
/**
 * Shared helpers for Muuttohaukat landing section blocks.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * CSS class for a landing section background attribute.
 */
function landing_block_background_class(array $attributes): string {
  $bg = $attributes['background'] ?? 'white';

  return in_array($bg, ['white', 'yellow', 'black'], true)
    ? 'mh-landing-section--bg-' . $bg
    : 'mh-landing-section--bg-white';
}

/**
 * Build block wrapper attributes with background class included.
 *
 * @param array $attributes Block attributes from render callback.
 * @param array $base_classes Base section classes (without background).
 * @param array $extra        Additional get_block_wrapper_attributes keys (id, etc.).
 */
function landing_block_wrapper_attributes(array $attributes, array $base_classes, array $extra = []): string {
  $classes = array_merge($base_classes, [landing_block_background_class($attributes)]);

  if (!empty($attributes['className'])) {
    $classes[] = $attributes['className'];
  }

  return get_block_wrapper_attributes(array_merge($extra, [
    'class' => implode(' ', array_filter($classes)),
  ]));
}
