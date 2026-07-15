<?php
/**
 * Menu registration and configuration.
 *
 * Registers navigation menus, adds depth-based CSS classes,
 * and configures wp_nav_menu defaults.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Menus;

/** Register theme navigation menus. */
add_action('init', function () {
  register_nav_menus([
    'header-menu' => esc_html__('Header menu', 'muuttohaukat'),
  ]);
});

/**
 * Add depth-level CSS classes to menu items (e.g. level-0, level-1).
 */
$menu_lookup = [];
add_filter('nav_menu_css_class', function ($classes, $item) use (&$menu_lookup) {
  $menu_lookup[$item->ID] = [
    'parent' => $item->menu_item_parent,
    'level'  => $item->menu_item_parent !== '0'
      ? ($menu_lookup[(int) $item->menu_item_parent]['level'] ?? 0) + 1
      : 0,
  ];

  $classes[] = 'level-' . $menu_lookup[$item->ID]['level'];
  return $classes;
}, 999999, 2);

/**
 * Compare two URLs for equality after normalising trailing slashes.
 *
 * @param string $x First URL.
 * @param string $y Second URL.
 * @return bool True if URLs match.
 */
function urlCompare($x, $y) {
  return strcasecmp(trailingslashit($x), trailingslashit($y)) === 0;
}

/**
 * Clean up wp_nav_menu defaults: remove container.
 */
add_filter('wp_nav_menu_args', function ($args = '') {
  $navMenuArgs = [];
  $navMenuArgs['container'] = false;

  if (empty($args['items_wrap'])) {
    $navMenuArgs['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
  }

  return array_merge($args, $navMenuArgs);
});

/** Remove the id attribute from menu items. */
add_filter('nav_menu_item_id', '__return_null');
