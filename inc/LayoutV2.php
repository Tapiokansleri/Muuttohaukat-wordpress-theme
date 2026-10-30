<?php
/**
 * Opt-in "v2" layout for one page: post meta `_mh_v2` = 1 adds the body class
 * `mh-v2`, and landing.css then uses a larger type scale on phones and tablets,
 * keeps the hero short on phones and gives the floating buttons a proper tap
 * size. Made for the password protected front page preview (30.10.2026), so
 * the change can be judged on one page before it goes to the whole site.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

add_filter('body_class', function (array $classes): array {
  if (is_singular() && get_post_meta(get_queried_object_id(), '_mh_v2', true)) {
    $classes[] = 'mh-v2';
  }
  return $classes;
});
