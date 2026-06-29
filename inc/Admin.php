<?php
/**
 * Admin customisations.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Admin;

/** Show all metaboxes by default on the nav menu editor for new users. */
add_action('user_register', function ($user_id) {
  update_user_option($user_id, 'metaboxhidden_nav-menus', []);
}, 10);
