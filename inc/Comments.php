<?php
/**
 * Disable comments and their administration interfaces site-wide.
 *
 * @package Muuttohaukat
 */

namespace Muuttohaukat\Comments;

const REPLACEMENT_READY = true;

/**
 * Remove comment and trackback support from every public content type.
 *
 * Attachments are included explicitly because WordPress does not report them
 * as public in every version.
 */
add_action('init', function () {
  $post_types = get_post_types(['public' => true], 'names');
  $post_types[] = 'attachment';

  foreach (array_unique($post_types) as $post_type) {
    if (post_type_supports($post_type, 'comments')) {
      remove_post_type_support($post_type, 'comments');
    }

    if (post_type_supports($post_type, 'trackbacks')) {
      remove_post_type_support($post_type, 'trackbacks');
    }
  }
}, 100);

/** Close comments and pingbacks, including on existing content. */
add_filter('comments_open', '__return_false', 100);
add_filter('pings_open', '__return_false', 100);
add_filter('comments_array', '__return_empty_array', 100);
add_filter('get_comments_number', '__return_zero', 100);
add_filter('pre_option_default_comment_status', function () {
  return 'closed';
});
add_filter('pre_option_default_ping_status', function () {
  return 'closed';
});

/** Remove public comment feeds and their rewrite rules. */
add_filter('feed_links_show_comments_feed', '__return_false');
add_filter('post_comments_feed_link', '__return_empty_string');
add_filter('comments_rewrite_rules', '__return_empty_array');

/** Remove the core REST comment routes. */
add_filter('rest_endpoints', function ($endpoints) {
  foreach (array_keys($endpoints) as $route) {
    if (preg_match('~^/wp/v2/comments(?:/|$)~', $route)) {
      unset($endpoints[$route]);
    }
  }

  return $endpoints;
}, 99);

/** Remove XML-RPC methods that read, write, or notify comments. */
add_filter('xmlrpc_methods', function ($methods) {
  $comment_methods = [
    'wp.getCommentCount',
    'wp.getComment',
    'wp.getComments',
    'wp.deleteComment',
    'wp.editComment',
    'wp.newComment',
    'pingback.ping',
    'pingback.extensions.getPingbacks',
  ];

  foreach ($comment_methods as $method) {
    unset($methods[$method]);
  }

  return $methods;
});

/** Reject programmatic attempts to create new comments. */
add_filter('pre_comment_approved', function () {
  return new \WP_Error(
    'comments_closed',
    __('Comments are disabled on this site.', 'muuttohaukat'),
    ['status' => 403]
  );
}, 100);

/** Remove comment-related admin menu entries. */
add_action('admin_menu', function () {
  remove_menu_page('edit-comments.php');
  remove_submenu_page('options-general.php', 'options-discussion.php');
}, 999);

/** Prevent direct access to comment and discussion administration screens. */
add_action('admin_init', function () {
  global $pagenow;

  if (in_array($pagenow, ['comment.php', 'edit-comments.php', 'options-discussion.php'], true)) {
    wp_safe_redirect(admin_url());
    exit;
  }
});

/** Remove comment controls from post editing screens. */
add_action('admin_menu', function () {
  foreach (get_post_types([], 'names') as $post_type) {
    remove_meta_box('commentstatusdiv', $post_type, 'normal');
    remove_meta_box('commentsdiv', $post_type, 'normal');
  }
}, 999);

/** Remove comment dashboard widgets and the admin-bar shortcut. */
add_action('wp_dashboard_setup', function () {
  remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}, 999);

add_action('admin_bar_menu', function ($admin_bar) {
  $admin_bar->remove_node('comments');
}, 999);

/** Remove the Recent Comments widget from the available widget list. */
add_action('widgets_init', function () {
  unregister_widget('WP_Widget_Recent_Comments');
});
