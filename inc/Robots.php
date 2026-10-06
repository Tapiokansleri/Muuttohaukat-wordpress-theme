<?php
/**
 * robots.txt through The SEO Framework (audit 3.3), which also adds the
 * sitemap line. These rules used to live in the Virtual Robots.txt plugin.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

function robots_rules(): array {
  return apply_filters('mh_robots_rules', [
    '# Feeds, author archives and search result paths',
    'Disallow: */feed/',
    'Disallow: /arkistot/?feed=',
    'Disallow: /author/',
    'Disallow: /accessory/',
    'Disallow: /search/*/',
    '# Old drafts',
    'Disallow: /muuttolaatikot-luonnos/',
    'Disallow: /uusi-etusivu-luonnos/',
    'Disallow: /Kiinnostaako',
    '# WordPress',
    'Disallow: /wp-json/',
    'Disallow: /xmlrpc.php',
    '# Calendar day addresses (audit 3.2)',
    'Disallow: /*?date=',
  ]);
}

add_filter('the_seo_framework_robots_txt', function ($output) {
  $rules = implode("\n", robots_rules());
  $count = 0;
  // Right after the first "User-agent: *" line, so the rules apply to every bot.
  $output = preg_replace('/^User-agent: \*[ \t]*$/mi', "$0\n" . $rules, (string) $output, 1, $count);

  return $count ? $output : "User-agent: *\n" . $rules . "\n\n" . $output;
});
