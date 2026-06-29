<?php
/**
 * Widget area registration.
 *
 * Registers four footer widget areas controllable from
 * Appearance → Widgets in WP admin.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Widgets;

add_action('widgets_init', function () {
  $columns = [
    'footer-1' => __('Footer Column 1', 'muuttohaukat'),
    'footer-2' => __('Footer Column 2', 'muuttohaukat'),
    'footer-3' => __('Footer Column 3', 'muuttohaukat'),
    'footer-4' => __('Footer Column 4', 'muuttohaukat'),
  ];

  foreach ($columns as $id => $name) {
    register_sidebar([
      'id'            => $id,
      'name'          => $name,
      'description'   => __('Footer widget area. Add menus, text, contact info, etc.', 'muuttohaukat'),
      'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
      'after_widget'  => '</div>',
      'before_title'  => '<h3 class="footer-widget__title">',
      'after_title'   => '</h3>',
    ]);
  }
});
