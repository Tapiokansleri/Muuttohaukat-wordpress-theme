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
    $secondary_url  = get_theme_mod( 'muuttohaukat_cta_secondary_url', '/tarjouspyynto/' );

    $has_primary   = ! empty( $primary_text ) && ! empty( $primary_url );
    $has_secondary = ! empty( $secondary_text ) && ! empty( $secondary_url );

    if ( ! $has_primary && ! $has_secondary ) {
        return;
    }
    ?>
    <div class="<?php echo esc_attr( $class ); ?>">
        <?php if ( $has_primary ) : ?>
            <a id="header-cta-primary" class="header-cta__btn header-cta__btn--primary" href="<?php echo esc_url( $primary_url ); ?>">
                <span class="header-cta__label"><?php echo esc_html( $primary_text ); ?></span>
            </a>
        <?php endif; ?>
        <?php if ( $has_secondary ) : ?>
            <a id="header-cta-secondary" class="header-cta__btn header-cta__btn--secondary" href="<?php echo esc_url( $secondary_url ); ?>">
                <span class="header-cta__label"><?php echo esc_html( $secondary_text ); ?></span>
            </a>
        <?php endif; ?>
    </div>
    <?php
}
endif;

if ( ! function_exists( 'muuttohaukat_lang_switcher' ) ) :
/**
 * The language switcher next to the CTA buttons (Tapio 24.11.2026: "kielivalikko CTA buttonien viereen, ei valikkoon"):
 * a small button with the current language's code (FI / EN / SE) that opens a list of the other languages. The links go to
 * the translation of the page (Polylang), or to the language's front page when there is none. The full name of each language
 * is in aria-label. Nothing is printed without Polylang or with one language.
 *
 * @param string $context 'desktop' or 'mobile' (an extra class).
 */
function muuttohaukat_lang_switcher( $context = 'desktop' ) {
    if ( ! function_exists( 'pll_the_languages' ) ) {
        return;
    }
    $languages = pll_the_languages( array( 'raw' => 1, 'hide_if_no_translation' => 0, 'hide_current' => 0 ) );
    if ( ! is_array( $languages ) || count( $languages ) < 2 ) {
        return;
    }
    $labels  = array( 'fi' => 'FI', 'en' => 'EN', 'sv' => 'SE' );
    $names   = array( 'fi' => 'Suomeksi', 'en' => 'In English', 'sv' => 'På svenska' );
    $current = null;
    $others  = array();
    foreach ( $languages as $language ) {
        if ( ! empty( $language['current_lang'] ) ) {
            $current = $language;
        } else {
            $others[] = $language;
        }
    }
    if ( null === $current ) {
        $current = array_shift( $others );
    }
    $code = static function ( $language ) use ( $labels ) {
        return $labels[ $language['slug'] ] ?? strtoupper( $language['slug'] );
    };
    $name = static function ( $language ) use ( $names ) {
        return $names[ $language['slug'] ] ?? $language['name'];
    };
    $id = 'lang-switch-' . $context;
    ?>
    <div class="lang-switch lang-switch--<?php echo esc_attr( $context ); ?>">
        <button type="button" class="lang-switch__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>" aria-label="<?php echo esc_attr( $name( $current ) . ': ' . implode( ', ', array_map( $name, $others ) ) ); ?>">
            <span class="lang-switch__code" lang="<?php echo esc_attr( $current['slug'] ); ?>"><?php echo esc_html( $code( $current ) ); ?></span>
            <svg class="lang-switch__chevron" width="10" height="10" viewBox="0 0 10 10" aria-hidden="true" focusable="false"><path d="M1.5 3.5 5 7l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <ul id="<?php echo esc_attr( $id ); ?>" class="lang-switch__list">
            <?php foreach ( $others as $language ) : ?>
                <li><a class="lang-switch__link" href="<?php echo esc_url( $language['url'] ); ?>" hreflang="<?php echo esc_attr( $language['slug'] ); ?>" lang="<?php echo esc_attr( $language['slug'] ); ?>" aria-label="<?php echo esc_attr( $name( $language ) ); ?>"><?php echo esc_html( $code( $language ) ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    static $script = false;
    if ( ! $script ) {
        $script = true;
        add_action( 'wp_footer', 'muuttohaukat_lang_switcher_script', 99 );
    }
}
endif;

if ( ! function_exists( 'muuttohaukat_lang_switcher_script' ) ) :
/** Opens and closes the language list: a click or a tap on the button, Esc and a click elsewhere close it. */
function muuttohaukat_lang_switcher_script() {
    ?>
<script id="muuttohaukat-lang-switch">
(function () {
  function close(box) { box.classList.remove('is-open'); box.querySelector('.lang-switch__toggle').setAttribute('aria-expanded', 'false'); }
  document.querySelectorAll('.lang-switch').forEach(function (box) {
    var button = box.querySelector('.lang-switch__toggle');
    button.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = !box.classList.contains('is-open');
      document.querySelectorAll('.lang-switch.is-open').forEach(close);
      if (open) { box.classList.add('is-open'); button.setAttribute('aria-expanded', 'true'); }
    });
    box.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(box); button.focus(); } });
  });
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.lang-switch.is-open').forEach(function (box) { if (!box.contains(e.target)) { close(box); } });
  });
})();
</script>
    <?php
}
endif;

if ( ! function_exists( 'muuttohaukat_header_extras' ) ) :
/**
 * Output the desktop extras area (the language switcher, then the CTA buttons to its right).
 */
function muuttohaukat_header_extras() {
    ?>
    <div id="header-extras" class="header-extras">
        <?php muuttohaukat_lang_switcher( 'desktop' ); ?>
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
            <?php muuttohaukat_lang_switcher( 'mobile' ); ?>
        </div>
        <?php endif; ?>
    </nav>
    <?php
}
endif;
