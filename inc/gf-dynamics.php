<?php
/**
 * Gravity Forms → Dynamics bridge (theme-embedded).
 *
 * Registers a GFFeedAddOn when Gravity Forms is present. No-op otherwise —
 * LibreForm / theme D365 forwarding is completely unaffected.
 *
 * @package Muuttohaukat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MH_GF_DYNAMICS_VERSION', '1.0.0' );
define( 'MH_GF_DYNAMICS_FILE', __FILE__ );
define( 'MH_GF_DYNAMICS_PATH', __DIR__ . '/gf-dynamics/' );

/**
 * Bootstrap Gravity Forms feed add-on.
 *
 * Must run on `init` (not `gform_loaded`): the theme loads after plugins, so
 * `gform_loaded` has already fired by the time this file is included. Instantiates
 * the add-on directly because GFAddOn::init_addons() also already ran.
 */
function mh_gf_dynamics_bootstrap() {
	if ( ! class_exists( 'GFForms' ) || ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
		return;
	}

	if ( class_exists( 'MH_GF_Dynamics_AddOn' ) ) {
		return;
	}

	GFForms::include_feed_addon_framework();

	require_once MH_GF_DYNAMICS_PATH . 'wplf-field-keys.php';
	require_once MH_GF_DYNAMICS_PATH . 'class-payload-builder.php';
	require_once MH_GF_DYNAMICS_PATH . 'class-dynamics-addon.php';

	GFAddOn::register( 'MH_GF_Dynamics_AddOn' );
	// Theme loads after gform_loaded / init:15 — instantiate and init manually.
	MH_GF_Dynamics_AddOn::get_instance()->init();
}

add_action( 'init', 'mh_gf_dynamics_bootstrap', 20 );

/**
 * admin-post handlers must resolve even if GFAddOn init order is awkward.
 */
function mh_gf_dynamics_admin_post_resend() {
	mh_gf_dynamics_bootstrap();
	$addon = mh_gf_dynamics();
	if ( ! $addon ) {
		wp_die( esc_html__( 'Dynamics bridge is not available.', 'muuttohaukat-gf-dynamics' ) );
	}
	$addon->handle_resend();
}

function mh_gf_dynamics_admin_post_test() {
	mh_gf_dynamics_bootstrap();
	$addon = mh_gf_dynamics();
	if ( ! $addon ) {
		wp_die( esc_html__( 'Dynamics bridge is not available.', 'muuttohaukat-gf-dynamics' ) );
	}
	$addon->handle_test_connection();
}

add_action( 'admin_post_mh_gf_dynamics_resend', 'mh_gf_dynamics_admin_post_resend' );
add_action( 'admin_post_mh_gf_dynamics_test', 'mh_gf_dynamics_admin_post_test' );

/**
 * Singleton accessor used by entry-detail callbacks.
 *
 * @return MH_GF_Dynamics_AddOn|null
 */
function mh_gf_dynamics() {
	if ( ! class_exists( 'MH_GF_Dynamics_AddOn' ) ) {
		return null;
	}
	return MH_GF_Dynamics_AddOn::get_instance();
}
