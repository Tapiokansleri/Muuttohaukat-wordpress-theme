<?php
/**
 * Google Tag Manager and global Leadoo integrations.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Integrations;

/**
 * Keep the legacy GTM setting semantics: tracking is active when the ACF
 * option is enabled, and remains visible to authenticated administrators.
 *
 * @return bool
 */
function gtm_enabled() {
    $enabled = false;

    if ( function_exists( 'get_field' ) ) {
        // App::getOption preserves Haukka's language-prefixed option lookup
        // (for example fi_gtmEnabled). The direct name is a compatibility
        // fallback for installations that stored the field without a prefix.
        $enabled = (bool) \Muuttohaukat\app()->getOption( 'gtmEnabled' );
        if ( ! $enabled ) {
            $enabled = (bool) get_field( 'gtmEnabled', 'options' );
        }
    }

    return $enabled || is_user_logged_in();
}

/**
 * Print the GTM loader in the document head.
 */
function print_gtm_head() {
    if ( ! gtm_enabled() ) {
        return;
    }
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-56J2BS4');</script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action( 'wp_head', __NAMESPACE__ . '\print_gtm_head', 20 );

/**
 * Print the GTM no-script fallback immediately after the body opens.
 */
function print_gtm_body() {
    if ( ! gtm_enabled() ) {
        return;
    }
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-56J2BS4" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action( 'wp_body_open', __NAMESPACE__ . '\print_gtm_body', 1 );

/**
 * Load Leadoo's site-wide dynamic bot, as the Haukka theme did.
 */
function enqueue_leadoo() {
    wp_enqueue_script(
        'muuttohaukat-leadoo-global',
        'https://bot.leadoo.com/bot/dynamic.js?company=R2WzcAC',
        array(),
        null,
        false
    );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_leadoo' );

/**
 * Preserve the async loading used by the legacy Leadoo embed.
 *
 * @param string $tag    Script HTML.
 * @param string $handle Registered script handle.
 * @return string
 */
function make_leadoo_async( $tag, $handle ) {
    if ( 'muuttohaukat-leadoo-global' !== $handle || false !== strpos( $tag, ' async' ) ) {
        return $tag;
    }

    return str_replace( '<script ', '<script async ', $tag );
}
add_filter( 'script_loader_tag', __NAMESPACE__ . '\make_leadoo_async', 10, 2 );
