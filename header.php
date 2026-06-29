<?php
/**
 * Header Template.
 *
 * @package Muuttohaukat
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<?php
$is_sticky    = get_theme_mod( 'muuttohaukat_header_sticky', true );
$is_fullwidth = get_theme_mod( 'muuttohaukat_header_full_width', false );
$body_classes = array();
if ( $is_sticky ) {
    $body_classes[] = 'header-sticky';
}
if ( $is_fullwidth ) {
    $body_classes[] = 'header-full-width';
}
?>
<body <?php body_class( $body_classes ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Siirry sisältöön', 'muuttohaukat' ); ?></a>

    <header id="site-header" class="site-header" role="banner">
        <?php get_template_part( 'template-parts/header/header-layout' ); ?>
    </header>

    <main id="content" class="site-content">
