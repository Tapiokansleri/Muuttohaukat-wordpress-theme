<?php

/**
 * Register custom post types and taxonomies here.
 */

namespace Muuttohaukat\CCT;

add_action('init', function () {
  /**
   * REMEMBER TO FLUSH PERMALINKS AND CONFIGURE POLYLANG SETTINGS AFTER ADDING NEW POST TYPES!
   *
   * Don't forget or else...
   */

  add_post_type_support('page', 'excerpt');

  $args = [
    'label'  => esc_html__('Accessories', 'muuttohaukat'),
    'labels' => [
      'menu_name'          => esc_html__('Accessories', 'muuttohaukat'),
      'name_admin_bar'     => esc_html__('Accessory', 'muuttohaukat'),
      'add_new'            => esc_html__('Add accessory', 'muuttohaukat'),
      'add_new_item'       => esc_html__('Add new accessory', 'muuttohaukat'),
      'new_item'           => esc_html__('New accessory', 'muuttohaukat'),
      'edit_item'          => esc_html__('Edit accessory', 'muuttohaukat'),
      'view_item'          => esc_html__('View accessory', 'muuttohaukat'),
      'update_item'        => esc_html__('View accessory', 'muuttohaukat'),
      'all_items'          => esc_html__('All Accessories', 'muuttohaukat'),
      'search_items'       => esc_html__('Search Accessories', 'muuttohaukat'),
      'parent_item_colon'  => esc_html__('Parent accessory', 'muuttohaukat'),
      'not_found'          => esc_html__('No Accessories found', 'muuttohaukat'),
      'not_found_in_trash' => esc_html__('No Accessories found in Trash', 'muuttohaukat'),
      'name'               => esc_html__('Accessories', 'muuttohaukat'),
      'singular_name'      => esc_html__('Accessory', 'muuttohaukat'),
    ],
    'public'              => true,
    'exclude_from_search' => true,
    'publicly_queryable'  => true,
    'show_ui'             => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'show_in_rest'        => true,
    'capability_type'     => 'post',
    'hierarchical'        => false,
    'has_archive'         => false,
    'query_var'           => true,
    'can_export'          => true,
    'rewrite_no_front'    => false,
    'show_in_menu'        => true,
    'menu_icon'           => 'dashicons-clipboard',
    'supports' => [
      'title',
      'editor',
      'author',
      'thumbnail',
      'excerpt',
      'revisions',
    ],
    'taxonomies' => ['category'],

    'rewrite' => [
      'slug' => 'muuttotarvikkeet'
    ]
  ];

  register_post_type('accessory', $args);

  $args = [
    'label' => esc_html__('Persons', 'muuttohaukat'),
    'labels' => [
      'menu_name' => esc_html__('Persons', 'muuttohaukat'),
      'name_admin_bar' => esc_html__('Person', 'muuttohaukat'),
      'add_new' => esc_html__('Add person', 'muuttohaukat'),
      'add_new_item' => esc_html__('Add new person', 'muuttohaukat'),
      'new_item' => esc_html__('New person', 'muuttohaukat'),
      'edit_item' => esc_html__('Edit person', 'muuttohaukat'),
      'view_item' => esc_html__('View person', 'muuttohaukat'),
      'update_item' => esc_html__('View person', 'muuttohaukat'),
      'all_items' => esc_html__('All person', 'muuttohaukat'),
      'search_items' => esc_html__('Search person', 'muuttohaukat'),
      'parent_item_colon' => esc_html__('Parent person', 'muuttohaukat'),
      'not_found' => esc_html__('No Persons found', 'muuttohaukat'),
      'not_found_in_trash' => esc_html__('No Persons found in Trash', 'muuttohaukat'),
      'name' => esc_html__('Persons', 'muuttohaukat'),
      'singular_name' => esc_html__('Person', 'muuttohaukat'),
    ],
    'public' => false,
    'exclude_from_search' => true,
    'publicly_queryable' => false,
    'show_ui' => true,
    'show_in_nav_menus' => true,
    'show_in_admin_bar' => false,
    'show_in_rest' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'has_archive' => false,
    'query_var' => true,
    'can_export' => true,
    'rewrite_no_front' => false,
    'show_in_menu' => true,
    'menu_icon' => 'dashicons-admin-users',
    'supports' => [
      'title',
      'editor',
      'author',
      'thumbnail',
      'excerpt',
      'revisions',
    ],
    'taxonomies' => [],
    'rewrite' => true
  ];

  register_post_type('person', $args);


  /**
   * REMEMBER TO FLUSH PERMALINKS AND CONFIGURE POLYLANG SETTINGS AFTER ADDING NEW POST TYPES!
   *
   * Don't forget or else...
   */

  // $page = get_post_type_object('page');
  // $page->template = [
  //   ['core/image', []],
  // ];
});
