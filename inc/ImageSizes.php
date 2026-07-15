<?php
/**
 * Image size configuration.
 *
 * Overrides default WordPress image sizes with dimensions
 * suited for responsive layouts up to 4K displays.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\ImageSizes;

/** Configure default image sizes on admin init. */
add_action('admin_init', function () {
  $image_sizes = [
    ['name' => 'thumbnail',    'w' => 450,  'h' => 450],
    ['name' => 'medium',       'w' => 1366, 'h' => 760],
    ['name' => 'medium_large', 'w' => 1980, 'h' => 1080],
    ['name' => 'large',        'w' => 2560, 'h' => 1440],
    ['name' => 'extra_large',  'w' => 4096, 'h' => 2160],
  ];

  foreach ($image_sizes as $size) {
    $existing_w = intval(get_option($size['name'] . '_size_w'));

    if ($existing_w !== $size['w']) {
      update_option($size['name'] . '_size_h', $size['h']);
      update_option($size['name'] . '_size_w', $size['w']);
    }
  }

  update_option('image_default_align', 'none');
  update_option('image_default_link_type', 'none');
  update_option('image_default_size', 'large');
});
