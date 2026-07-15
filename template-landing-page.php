<?php
/**
 * Template Name: Landing page
 * Template Post Type: page, accessory, person, muuttopalvelut, muuttosiivous, muutto, muuttolaatikot, arkistot, muuttoauto, landing-page, muuttovinkit
 *
 * Minimal wrapper. All section content is rendered as Gutenberg blocks via
 * the_content(). The seven default sections are auto-populated the first
 * time a page is saved with this template (see inc/LandingTemplate.php).
 *
 * Pattern inserter, route-calculator block, and CSS live in:
 *   - inc/LandingPatterns.php
 *   - inc/LandingTemplate.php
 *   - blocks/route-calculator/
 *   - assets/css/landing.css
 *
 * @package Muuttohaukat
 */

namespace Muuttohaukat;

get_header(); ?>

<main id="content" class="mh-landing" role="main">
  <?php
  if (have_posts()) {
    while (have_posts()) {
      the_post();
      the_content();
    }
  }
  ?>
</main>

<?php
include get_template_directory() . '/template-parts/landing/sticky-cta.php';

get_footer();
