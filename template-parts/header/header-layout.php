<?php
/**
 * Unified Header Layout.
 *
 * Single flat HTML structure — visual layout controlled by CSS Grid
 * and the customizer settings.
 *
 * Structure:
 *   .header-inner
 *     > .site-branding       (logo)
 *     > .mobile-menu-toggle  (hamburger button)
 *     > .main-navigation     (nav menu + mobile CTA)
 *     > .header-extras       (desktop CTA buttons)
 *
 * @package Muuttohaukat
 */

require_once get_template_directory() . '/template-parts/header/header-helpers.php';
?>
<div class="header-inner">
    <?php muuttohaukat_header_branding(); ?>
    <?php muuttohaukat_header_mobile_toggle(); ?>
    <?php muuttohaukat_header_nav(); ?>
    <?php muuttohaukat_header_extras(); ?>
</div>
