<?php
/**
 * /muuttotarvikkeet/ is both a page (the supplies list, which paginates) and
 * the rewrite slug of the `accessory` post type. /muuttotarvikkeet/page/2/
 * therefore resolved to an accessory called "page" and returned 404. A rule
 * on top sends the paged addresses to the page.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

// Bump when a rule changes: the rewrite rules are flushed once per version,
// so a theme update is enough to apply them on any site.
const REWRITE_RULES_VERSION = '2026-09-21';

add_action('init', function () {
  add_rewrite_rule('^muuttotarvikkeet/page/([0-9]+)/?$', 'index.php?pagename=muuttotarvikkeet&paged=$matches[1]', 'top');

  if (get_option('mh_rewrite_rules_version') !== REWRITE_RULES_VERSION) {
    flush_rewrite_rules(false);
    update_option('mh_rewrite_rules_version', REWRITE_RULES_VERSION, true);
  }
}, 20);
