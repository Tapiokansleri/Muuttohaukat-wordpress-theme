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

function is_layout_v2(): bool {
  return is_singular() && (bool) get_post_meta(get_queried_object_id(), '_mh_v2', true);
}

add_filter('body_class', function (array $classes): array {
  if (is_layout_v2()) {
    $classes[] = 'mh-v2';
  }
  return $classes;
});

/**
 * The floating buttons (inc/FloatingCta.php) offer the same two actions as the
 * hero, and on small screens they covered the hero's second button: while the
 * hero's buttons are in view, `mh-hero-cta-in-view` on the body hides them
 * (landing.css). Without JavaScript the floating buttons simply stay.
 */
add_action('wp_footer', function () {
  if (!is_layout_v2()) {
    return;
  }
  ?>
  <script>
  (function () {
    var ctas = document.querySelector('.mh-landing-hero .mh-painike-wrap');
    if (!ctas || !('IntersectionObserver' in window)) { return; }
    new IntersectionObserver(function (entries) {
      document.body.classList.toggle('mh-hero-cta-in-view', entries[0].isIntersecting);
    }).observe(ctas);
  })();
  </script>
  <?php
}, 20);
