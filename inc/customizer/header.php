<?php
/**
 * Customizer: Header settings.
 *
 * Registers all header-related settings for the WordPress Customizer:
 * design, colors, CTA buttons, sticky behavior, and navigation styling.
 *
 * @package Muuttohaukat
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register header customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function muuttohaukat_customizer_header( $wp_customize ) {

    // =================================================================
    // Panel: Header
    // =================================================================
    $wp_customize->add_panel( 'muuttohaukat_header_panel', array(
        'title'    => __( 'Header', 'muuttohaukat' ),
        'priority' => 25,
    ) );

    // =================================================================
    // Section: Design & Layout
    // =================================================================
    $wp_customize->add_section( 'muuttohaukat_header_design', array(
        'title' => __( 'Design & Layout', 'muuttohaukat' ),
        'panel' => 'muuttohaukat_header_panel',
    ) );

    // Logo Max Width
    $wp_customize->add_setting( 'muuttohaukat_logo_max_width', array(
        'default'           => 200,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'muuttohaukat_logo_max_width', array(
        'label'       => __( 'Logo Max Width (px)', 'muuttohaukat' ),
        'section'     => 'muuttohaukat_header_design',
        'type'        => 'range',
        'input_attrs' => array( 'min' => 50, 'max' => 400, 'step' => 5 ),
    ) );

    // Header Height
    $wp_customize->add_setting( 'muuttohaukat_header_height', array(
        'default'           => 80,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'muuttohaukat_header_height', array(
        'label'       => __( 'Header Height (px)', 'muuttohaukat' ),
        'section'     => 'muuttohaukat_header_design',
        'type'        => 'range',
        'input_attrs' => array( 'min' => 50, 'max' => 150, 'step' => 5 ),
    ) );

    // Header Background Color
    $wp_customize->add_setting( 'muuttohaukat_header_bg_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_header_bg_color', array(
        'label'   => __( 'Background Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_design',
    ) ) );

    // Header Text Color
    $wp_customize->add_setting( 'muuttohaukat_header_text_color', array(
        'default'           => '#1a1a1a',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_header_text_color', array(
        'label'   => __( 'Text / Link Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_design',
    ) ) );

    // Sticky Header
    $wp_customize->add_setting( 'muuttohaukat_header_sticky', array(
        'default'           => true,
        'sanitize_callback' => 'muuttohaukat_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'muuttohaukat_header_sticky', array(
        'label'   => __( 'Sticky Header', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_design',
        'type'    => 'checkbox',
    ) );

    // Full Width Header
    $wp_customize->add_setting( 'muuttohaukat_header_full_width', array(
        'default'           => false,
        'sanitize_callback' => 'muuttohaukat_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'muuttohaukat_header_full_width', array(
        'label'   => __( 'Full Width Header', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_design',
        'type'    => 'checkbox',
    ) );

    // =================================================================
    // Section: Navigation
    // =================================================================
    $wp_customize->add_section( 'muuttohaukat_header_navigation', array(
        'title' => __( 'Navigation', 'muuttohaukat' ),
        'panel' => 'muuttohaukat_header_panel',
    ) );

    // Nav Link Color
    $wp_customize->add_setting( 'muuttohaukat_nav_link_color', array(
        'default'           => '#1a1a1a',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_nav_link_color', array(
        'label'   => __( 'Link Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_navigation',
    ) ) );

    // Nav Hover Color
    $wp_customize->add_setting( 'muuttohaukat_nav_hover_color', array(
        'default'           => '#ffed00',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_nav_hover_color', array(
        'label'   => __( 'Hover Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_navigation',
    ) ) );

    // Nav Active Color
    $wp_customize->add_setting( 'muuttohaukat_nav_active_color', array(
        'default'           => '#ffed00',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_nav_active_color', array(
        'label'   => __( 'Active Item Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_navigation',
    ) ) );

    // Nav Font Size
    $wp_customize->add_setting( 'muuttohaukat_nav_font_size', array(
        'default'           => 15,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'muuttohaukat_nav_font_size', array(
        'label'       => __( 'Font Size (px)', 'muuttohaukat' ),
        'section'     => 'muuttohaukat_header_navigation',
        'type'        => 'range',
        'input_attrs' => array( 'min' => 12, 'max' => 22, 'step' => 1 ),
    ) );

    // Nav Text Transform
    $wp_customize->add_setting( 'muuttohaukat_nav_text_transform', array(
        'default'           => 'uppercase',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'muuttohaukat_nav_text_transform', array(
        'label'   => __( 'Text Transform', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_navigation',
        'type'    => 'select',
        'choices' => array(
            'none'       => __( 'None', 'muuttohaukat' ),
            'uppercase'  => __( 'Uppercase', 'muuttohaukat' ),
            'capitalize' => __( 'Capitalize', 'muuttohaukat' ),
        ),
    ) );

    // Submenu Background Color
    $wp_customize->add_setting( 'muuttohaukat_submenu_bg_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_submenu_bg_color', array(
        'label'   => __( 'Submenu Background', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_navigation',
    ) ) );

    // Submenu Text Color
    $wp_customize->add_setting( 'muuttohaukat_submenu_text_color', array(
        'default'           => '#1a1a1a',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_submenu_text_color', array(
        'label'   => __( 'Submenu Text Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_navigation',
    ) ) );

    // =================================================================
    // Section: CTA Buttons
    // =================================================================
    $wp_customize->add_section( 'muuttohaukat_header_cta', array(
        'title' => __( 'CTA Buttons', 'muuttohaukat' ),
        'panel' => 'muuttohaukat_header_panel',
    ) );

    // CTA Border Radius
    $wp_customize->add_setting( 'muuttohaukat_cta_border_radius', array(
        'default'           => 4,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'muuttohaukat_cta_border_radius', array(
        'label'       => __( 'Button Border Radius (px)', 'muuttohaukat' ),
        'description' => __( '0 = square, 50 = pill', 'muuttohaukat' ),
        'section'     => 'muuttohaukat_header_cta',
        'type'        => 'range',
        'input_attrs' => array( 'min' => 0, 'max' => 50, 'step' => 1 ),
    ) );

    // Primary CTA
    $wp_customize->add_setting( 'muuttohaukat_cta_primary_text', array(
        'default'           => 'Tilaa muutto',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'muuttohaukat_cta_primary_text', array(
        'label'   => __( 'Primary Button Text', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'muuttohaukat_cta_primary_url', array(
        'default'           => 'https://tilaamuutto.fi',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'muuttohaukat_cta_primary_url', array(
        'label'   => __( 'Primary Button URL', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'muuttohaukat_cta_primary_bg', array(
        'default'           => '#ffed00',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_cta_primary_bg', array(
        'label'   => __( 'Primary Button Background', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
    ) ) );

    $wp_customize->add_setting( 'muuttohaukat_cta_primary_color', array(
        'default'           => '#000000',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_cta_primary_color', array(
        'label'   => __( 'Primary Button Text Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
    ) ) );

    // Secondary CTA
    $wp_customize->add_setting( 'muuttohaukat_cta_secondary_text', array(
        'default'           => 'Tarjouspyyntö',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'muuttohaukat_cta_secondary_text', array(
        'label'   => __( 'Secondary Button Text', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'muuttohaukat_cta_secondary_url', array(
        'default'           => '/tarjouspyynto',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'muuttohaukat_cta_secondary_url', array(
        'label'   => __( 'Secondary Button URL', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'muuttohaukat_cta_secondary_bg', array(
        'default'           => '#000000',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_cta_secondary_bg', array(
        'label'   => __( 'Secondary Button Background', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
    ) ) );

    $wp_customize->add_setting( 'muuttohaukat_cta_secondary_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_cta_secondary_color', array(
        'label'   => __( 'Secondary Button Text Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
    ) ) );

    // Show CTAs on Mobile
    $wp_customize->add_setting( 'muuttohaukat_cta_show_mobile', array(
        'default'           => true,
        'sanitize_callback' => 'muuttohaukat_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'muuttohaukat_cta_show_mobile', array(
        'label'   => __( 'Show CTA Buttons on Mobile', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_cta',
        'type'    => 'checkbox',
    ) );

    // =================================================================
    // Section: Mobile Menu
    // =================================================================
    $wp_customize->add_section( 'muuttohaukat_header_mobile', array(
        'title'       => __( 'Mobile Menu', 'muuttohaukat' ),
        'panel'       => 'muuttohaukat_header_panel',
        'description' => __( 'Colors for the mobile menu overlay. Leave empty to inherit from Desktop settings.', 'muuttohaukat' ),
    ) );

    // Mobile Menu Background
    $wp_customize->add_setting( 'muuttohaukat_mobile_menu_bg', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_mobile_menu_bg', array(
        'label'   => __( 'Background Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_mobile',
    ) ) );

    // Mobile Menu Text Color
    $wp_customize->add_setting( 'muuttohaukat_mobile_menu_color', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_mobile_menu_color', array(
        'label'   => __( 'Text Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_mobile',
    ) ) );

    // Mobile Menu Link Color
    $wp_customize->add_setting( 'muuttohaukat_mobile_menu_link_color', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_mobile_menu_link_color', array(
        'label'   => __( 'Link Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_mobile',
    ) ) );

    // Mobile Menu Link Hover Color
    $wp_customize->add_setting( 'muuttohaukat_mobile_menu_link_hover', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'muuttohaukat_mobile_menu_link_hover', array(
        'label'   => __( 'Link Hover Color', 'muuttohaukat' ),
        'section' => 'muuttohaukat_header_mobile',
    ) ) );
}
add_action( 'customize_register', 'muuttohaukat_customizer_header' );

/**
 * Copy compatible Haukka theme mods once, without replacing values already
 * configured for Muuttohaukat.
 */
function muuttohaukat_migrate_haukka_theme_mods() {
    if ( get_option( 'muuttohaukat_migrated_haukka_theme_mods', false ) ) {
        return;
    }

    // WordPress derives this option from the stylesheet directory. Support
    // both spellings because old Windows installs were case-insensitive while
    // Linux production stores the exact directory casing.
    $legacy_mods = array();
    foreach ( array( 'theme_mods_haukka', 'theme_mods_Haukka' ) as $legacy_option ) {
        $candidate = get_option( $legacy_option, array() );
        if ( is_array( $candidate ) ) {
            $legacy_mods = array_merge( $legacy_mods, $candidate );
        }
    }
    $option_name = 'theme_mods_Muuttohaukat';
    $current_mods = get_option( $option_name, array() );

    if ( ! is_array( $legacy_mods ) ) {
        $legacy_mods = array();
    }
    if ( ! is_array( $current_mods ) ) {
        $current_mods = array();
    }

    $prefixed_settings = array(
        'logo_max_width',
        'header_height',
        'header_bg_color',
        'header_text_color',
        'header_sticky',
        'header_full_width',
        'nav_link_color',
        'nav_hover_color',
        'nav_active_color',
        'nav_font_size',
        'nav_text_transform',
        'submenu_bg_color',
        'submenu_text_color',
        'cta_border_radius',
        'cta_primary_text',
        'cta_primary_url',
        'cta_primary_bg',
        'cta_primary_color',
        'cta_secondary_text',
        'cta_secondary_url',
        'cta_secondary_bg',
        'cta_secondary_color',
        'cta_show_mobile',
        'mobile_menu_bg',
        'mobile_menu_color',
        'mobile_menu_link_color',
        'mobile_menu_link_hover',
    );

    $map = array(
        'custom_logo'        => 'custom_logo',
        'nav_menu_locations' => 'nav_menu_locations',
    );

    foreach ( $prefixed_settings as $setting ) {
        $map[ 'haukka_' . $setting ] = 'muuttohaukat_' . $setting;
    }

    $changed = false;
    foreach ( $map as $legacy_key => $current_key ) {
        if ( array_key_exists( $legacy_key, $legacy_mods ) && ! array_key_exists( $current_key, $current_mods ) ) {
            $current_mods[ $current_key ] = $legacy_mods[ $legacy_key ];
            $changed = true;
        }
    }

    if ( $changed ) {
        update_option( $option_name, $current_mods );
    }

    update_option( 'muuttohaukat_migrated_haukka_theme_mods', true );
}
// Run after the canonical-folder migration so WordPress reads the destination
// theme-mod option immediately on legacy-folder upgrades.
add_action( 'after_setup_theme', 'muuttohaukat_migrate_haukka_theme_mods', 30 );

/**
 * Sanitize checkbox value.
 *
 * @param mixed $value Input value.
 * @return bool
 */
function muuttohaukat_sanitize_checkbox( $value ) {
    return (bool) $value;
}

/**
 * Output header CSS custom properties from customizer settings.
 */
function muuttohaukat_header_inline_styles() {
    $logo_max_width  = absint( get_theme_mod( 'muuttohaukat_logo_max_width', 200 ) );
    $height          = absint( get_theme_mod( 'muuttohaukat_header_height', 80 ) );
    $bg_color        = get_theme_mod( 'muuttohaukat_header_bg_color', '#ffffff' );
    $text_color      = get_theme_mod( 'muuttohaukat_header_text_color', '#1a1a1a' );
    $nav_color       = get_theme_mod( 'muuttohaukat_nav_link_color', '#1a1a1a' );
    $nav_hover       = get_theme_mod( 'muuttohaukat_nav_hover_color', '#ffed00' );
    $nav_active      = get_theme_mod( 'muuttohaukat_nav_active_color', '#ffed00' );
    $nav_font_size   = get_theme_mod( 'muuttohaukat_nav_font_size', 15 );
    $nav_transform   = get_theme_mod( 'muuttohaukat_nav_text_transform', 'uppercase' );
    $sub_bg          = get_theme_mod( 'muuttohaukat_submenu_bg_color', '#ffffff' );
    $sub_text        = get_theme_mod( 'muuttohaukat_submenu_text_color', '#1a1a1a' );
    $cta1_bg         = get_theme_mod( 'muuttohaukat_cta_primary_bg', '#ffed00' );
    $cta1_color      = get_theme_mod( 'muuttohaukat_cta_primary_color', '#000000' );
    $cta2_bg         = get_theme_mod( 'muuttohaukat_cta_secondary_bg', '#000000' );
    $cta2_color      = get_theme_mod( 'muuttohaukat_cta_secondary_color', '#ffffff' );
    $cta_radius      = get_theme_mod( 'muuttohaukat_cta_border_radius', 4 );
    $mobile_bg       = get_theme_mod( 'muuttohaukat_mobile_menu_bg', '' );
    $mobile_color    = get_theme_mod( 'muuttohaukat_mobile_menu_color', '' );
    $mobile_link     = get_theme_mod( 'muuttohaukat_mobile_menu_link_color', '' );
    $mobile_hover    = get_theme_mod( 'muuttohaukat_mobile_menu_link_hover', '' );

    $css = ":root {
        --logo-max-width: {$logo_max_width}px;
        --header-height: {$height}px;
        --header-bg: {$bg_color};
        --header-color: {$text_color};
        --nav-color: {$nav_color};
        --nav-hover: {$nav_hover};
        --nav-active: {$nav_active};
        --nav-font-size: {$nav_font_size}px;
        --nav-transform: {$nav_transform};
        --submenu-bg: {$sub_bg};
        --submenu-color: {$sub_text};
        --cta-primary-bg: {$cta1_bg};
        --cta-primary-color: {$cta1_color};
        --cta-secondary-bg: {$cta2_bg};
        --cta-secondary-color: {$cta2_color};
        --cta-border-radius: {$cta_radius}px;";

    if ( $mobile_bg ) {
        $css .= "\n        --mobile-menu-bg: {$mobile_bg};";
    }
    if ( $mobile_color ) {
        $css .= "\n        --mobile-menu-color: {$mobile_color};";
    }
    if ( $mobile_link ) {
        $css .= "\n        --mobile-menu-link-color: {$mobile_link};";
    }
    if ( $mobile_hover ) {
        $css .= "\n        --mobile-menu-link-hover: {$mobile_hover};";
    }

    $css .= "\n    }";

    wp_register_style( 'muuttohaukat-header-vars', false );
    wp_enqueue_style( 'muuttohaukat-header-vars' );
    wp_add_inline_style( 'muuttohaukat-header-vars', wp_strip_all_tags( $css ) );
}
add_action( 'wp_enqueue_scripts', 'muuttohaukat_header_inline_styles' );
