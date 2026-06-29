<?php
/**
 * Header Helpers — Reusable components for the header.
 *
 * Functions:
 *   muuttohaukat_header_branding()      - Logo
 *   muuttohaukat_header_nav()           - Primary navigation + mobile extras
 *   muuttohaukat_header_cta_buttons()   - CTA buttons
 *   muuttohaukat_header_mobile_toggle() - Hamburger button
 *   muuttohaukat_header_extras()        - Desktop CTA wrapper
 *
 * @package Muuttohaukat
 */

if ( ! function_exists( 'muuttohaukat_header_branding' ) ) :
/**
 * Output site logo / branding.
 */
function muuttohaukat_header_branding() {
    ?>
    <div id="site-branding" class="site-branding">
        <?php if ( has_custom_logo() ) : ?>
            <?php the_custom_logo(); ?>
        <?php else :
            $logo_path = get_template_directory() . '/assets/img/muuttohaukat.svg';
            ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                <?php
                if ( file_exists( $logo_path ) ) {
                    echo file_get_contents( $logo_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local SVG file
                } else {
                    echo '<span class="site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
                }
                ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}
endif;

if ( ! function_exists( 'muuttohaukat_header_mobile_toggle' ) ) :
/**
 * Output the mobile menu hamburger button.
 */
function muuttohaukat_header_mobile_toggle() {
    ?>
    <button id="mobile-menu-toggle" class="mobile-menu-toggle" aria-controls="mobile-menu" aria-expanded="false">
        <span class="hamburger-icon">
            <span class="hamburger-icon__line"></span>
            <span class="hamburger-icon__line"></span>
            <span class="hamburger-icon__line"></span>
        </span>
        <span class="screen-reader-text"><?php esc_html_e( 'Valikko', 'muuttohaukat' ); ?></span>
    </button>
    <?php
}
endif;

if ( ! function_exists( 'muuttohaukat_header_cta_buttons' ) ) :
/**
 * Output CTA buttons.
 *
 * @param string $class Optional wrapper class.
 */
function muuttohaukat_header_cta_buttons( $class = 'header-cta' ) {
    $primary_text   = get_theme_mod( 'muuttohaukat_cta_primary_text', 'Tilaa muutto' );
    $primary_url    = get_theme_mod( 'muuttohaukat_cta_primary_url', 'https://tilaamuutto.fi' );
    $secondary_text = get_theme_mod( 'muuttohaukat_cta_secondary_text', 'Tarjouspyyntö' );
    $secondary_url  = get_theme_mod( 'muuttohaukat_cta_secondary_url', '/tarjouspyynto' );

    $has_primary   = ! empty( $primary_text ) && ! empty( $primary_url );
    $has_secondary = ! empty( $secondary_text ) && ! empty( $secondary_url );

    if ( ! $has_primary && ! $has_secondary ) {
        return;
    }
    ?>
    <div class="<?php echo esc_attr( $class ); ?>">
        <?php if ( $has_primary ) : ?>
            <a id="header-cta-primary" class="header-cta__btn header-cta__btn--primary" href="<?php echo esc_url( $primary_url ); ?>">
                <?php echo esc_html( $primary_text ); ?>
            </a>
        <?php endif; ?>
        <?php if ( $has_secondary ) : ?>
            <a id="header-cta-secondary" class="header-cta__btn header-cta__btn--secondary" href="<?php echo esc_url( $secondary_url ); ?>">
                <?php echo esc_html( $secondary_text ); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}
endif;

if ( ! function_exists( 'muuttohaukat_header_extras' ) ) :
/**
 * Output the desktop extras area (CTA buttons).
 */
function muuttohaukat_header_extras() {
    ?>
    <div id="header-extras" class="header-extras">
        <?php muuttohaukat_header_cta_buttons(); ?>
    </div>
    <?php
}
endif;

if ( ! function_exists( 'muuttohaukat_header_nav' ) ) :
/**
 * Output primary navigation including mobile CTA.
 */
function muuttohaukat_header_nav() {
    $show_mobile_cta = get_theme_mod( 'muuttohaukat_cta_show_mobile', true );
    $logo_path       = get_template_directory() . '/assets/img/muuttohaukat.svg';
    ?>
    <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Päävalikko', 'muuttohaukat' ); ?>">
        <div class="mobile-menu-header">
            <div class="mobile-menu-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                        <?php
                        if ( file_exists( $logo_path ) ) {
                            echo file_get_contents( $logo_path ); // phpcs:ignore
                        } else {
                            echo '<span class="site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
                        }
                        ?>
                    </a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-close" aria-label="<?php esc_attr_e( 'Sulje valikko', 'muuttohaukat' ); ?>">&times;</button>
        </div>
        <?php
        wp_nav_menu( array(
            'theme_location' => 'header-menu',
            'menu_id'        => 'primary-menu',
            'menu_class'     => 'nav-menu',
            'container'      => false,
            'fallback_cb'    => false,
            'depth'          => 2,
        ) );
        ?>
        <?php if ( $show_mobile_cta ) : ?>
        <div class="mobile-header-extras">
            <?php muuttohaukat_header_cta_buttons( 'mobile-header-cta' ); ?>
        </div>
        <?php endif; ?>
    </nav>
    <?php
}
endif;
