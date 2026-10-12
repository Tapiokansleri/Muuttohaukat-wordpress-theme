<?php
/**
 * Behaviour the LibreForm offer forms have and the Gravity Forms ones need too.
 *
 * Applies to every Gravity Forms form with an active Dynamics feed and finds the
 * fields through the feed's field map (the LibreForm keys), so no field IDs are
 * hard-coded:
 *
 * - Dates (Muuttopvm, TarvikkeidenToimituspvm, TarvikkeidenPalautuspvm): not today
 *   and not in the past, and the return date not before the delivery or the moving
 *   date. Same rules and messages as the LibreForm form (assets/js/client.js), but
 *   checked on the server; the date pickers also start from tomorrow.
 * - Moving date from ?date=Y-m-d (the [muuttopaivat] calendar links): a date field
 *   with the dynamic population parameter "date" is filled in its own date format.
 * - A successful submission pushes `gf_lead_submit` to the dataLayer, so Google Tag
 *   Manager can count these leads; its LibreForm trigger only listens to forms with
 *   the class wplf-form.
 *
 * @package Muuttohaukat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LibreForm keys of the date fields the rules apply to.
 *
 * @return string[]
 */
function mh_gf_dynamics_date_keys() {
	return array( 'Muuttopvm', 'TarvikkeidenToimituspvm', 'TarvikkeidenPalautuspvm' );
}

/**
 * Active Dynamics feeds of a form.
 *
 * @param int $form_id Form ID.
 * @return array[]
 */
function mh_gf_dynamics_active_feeds( $form_id ) {
	static $cache = array();

	$form_id = absint( $form_id );
	if ( isset( $cache[ $form_id ] ) ) {
		return $cache[ $form_id ];
	}

	$feeds = array();
	if ( $form_id && class_exists( 'GFAPI' ) ) {
		$found = GFAPI::get_feeds( null, $form_id, 'muuttohaukat-gf-dynamics', true );
		if ( ! is_wp_error( $found ) && is_array( $found ) ) {
			$feeds = $found;
		}
	}

	$cache[ $form_id ] = $feeds;
	return $feeds;
}

/**
 * LibreForm key => Gravity Forms field or input ID, from the form's active Dynamics feeds.
 *
 * @param int $form_id Form ID.
 * @return array<string, string>
 */
function mh_gf_dynamics_field_map_for_form( $form_id ) {
	$map = array();
	foreach ( mh_gf_dynamics_active_feeds( $form_id ) as $feed ) {
		foreach ( (array) rgars( $feed, 'meta/field_map' ) as $row ) {
			$key   = rgar( $row, 'key' ) === 'gf_custom' ? (string) rgar( $row, 'custom_key' ) : (string) rgar( $row, 'key' );
			$value = (string) rgar( $row, 'value' );
			if ( $key !== '' && $value !== '' && ! isset( $map[ $key ] ) ) {
				$map[ $key ] = $value;
			}
		}
	}
	return $map;
}

/**
 * Impersonated LibreForm post ID of the form's first active Dynamics feed.
 *
 * @param int $form_id Form ID.
 * @return int
 */
function mh_gf_dynamics_wplf_form_for_form( $form_id ) {
	foreach ( mh_gf_dynamics_active_feeds( $form_id ) as $feed ) {
		$wplf = absint( rgars( $feed, 'meta/wplf_form_id' ) );
		if ( $wplf ) {
			return $wplf;
		}
	}
	return 0;
}

/**
 * A date field's submitted value as Y-m-d, or '' when empty or not a real date.
 *
 * @param GF_Field $field Date field.
 * @param mixed    $value Submitted value in the field's date format.
 * @return string
 */
function mh_gf_dynamics_date_ymd( $field, $value ) {
	if ( ! is_string( $value ) || trim( $value ) === '' || ! class_exists( 'GFCommon' ) ) {
		return '';
	}

	$parsed = GFCommon::parse_date( trim( $value ), $field->dateFormat ? $field->dateFormat : 'mdy' );
	if ( empty( $parsed['year'] ) || empty( $parsed['month'] ) || empty( $parsed['day'] )
		|| ! checkdate( (int) $parsed['month'], (int) $parsed['day'], (int) $parsed['year'] ) ) {
		return '';
	}

	return sprintf( '%04d-%02d-%02d', $parsed['year'], $parsed['month'], $parsed['day'] );
}

/**
 * The submitted Y-m-d value of the date field mapped to a LibreForm key.
 *
 * @param array  $form Form.
 * @param string $key  LibreForm key.
 * @return string
 */
function mh_gf_dynamics_posted_date( $form, $key ) {
	$map = mh_gf_dynamics_field_map_for_form( rgar( $form, 'id' ) );
	if ( empty( $map[ $key ] ) ) {
		return '';
	}

	$field = GFAPI::get_field( $form, $map[ $key ] );
	if ( ! $field || $field->type !== 'date' ) {
		return '';
	}

	return mh_gf_dynamics_date_ymd( $field, rgpost( 'input_' . str_replace( '.', '_', (string) $field->id ) ) );
}

/**
 * Server-side date rules for the mapped date fields (the LibreForm rules of client.js).
 *
 * @param array    $result Validation result.
 * @param mixed    $value  Submitted value.
 * @param array    $form   Form.
 * @param GF_Field $field  Field.
 * @return array
 */
function mh_gf_dynamics_validate_dates( $result, $value, $form, $field ) {
	if ( empty( $result['is_valid'] ) || ! is_object( $field ) || $field->type !== 'date' ) {
		return $result;
	}

	$map = mh_gf_dynamics_field_map_for_form( rgar( $form, 'id' ) );
	$key = '';
	foreach ( mh_gf_dynamics_date_keys() as $date_key ) {
		if ( isset( $map[ $date_key ] ) && (string) $map[ $date_key ] === (string) $field->id ) {
			$key = $date_key;
			break;
		}
	}
	if ( $key === '' ) {
		return $result;
	}

	$ymd = mh_gf_dynamics_date_ymd( $field, is_array( $value ) ? '' : $value );
	if ( $ymd === '' ) {
		return $result; // Empty or not parseable: Gravity Forms' own date checks decide.
	}

	$today   = current_time( 'Y-m-d' );
	$message = '';
	if ( $ymd === $today ) {
		$message = 'Päivä ei voi olla tänään.';
	} elseif ( $ymd < $today ) {
		$message = 'Päivä ei voi olla menneisyydessä.';
	} elseif ( $key === 'TarvikkeidenPalautuspvm' ) {
		$delivery = mh_gf_dynamics_posted_date( $form, 'TarvikkeidenToimituspvm' );
		$moving   = mh_gf_dynamics_posted_date( $form, 'Muuttopvm' );
		if ( $delivery !== '' && $ymd < $delivery ) {
			$message = 'Päivä ei voi olla ennen toimitusta.';
		} elseif ( $moving !== '' && $ymd < $moving ) {
			$message = 'Päivä ei voi olla ennen muuttoa.';
		}
	}

	if ( $message !== '' ) {
		$result['is_valid'] = false;
		$result['message']  = $message;
	}

	return $result;
}

add_filter( 'gform_field_validation', 'mh_gf_dynamics_validate_dates', 10, 4 );

/**
 * Date pickers of the mapped date fields start from tomorrow.
 *
 * Runs after Gravity Forms' own gform_post_render handlers (setTimeout), so the
 * jQuery UI date pickers exist when the option is set.
 *
 * @param array $form Form.
 */
function mh_gf_dynamics_datepicker_min( $form ) {
	$map = mh_gf_dynamics_field_map_for_form( rgar( $form, 'id' ) );
	$ids = array();
	foreach ( mh_gf_dynamics_date_keys() as $key ) {
		if ( ! empty( $map[ $key ] ) && ctype_digit( (string) $map[ $key ] ) ) {
			$ids[] = '#input_' . absint( $form['id'] ) . '_' . absint( $map[ $key ] );
		}
	}
	if ( ! $ids || ! class_exists( 'GFFormDisplay' ) ) {
		return;
	}

	$script = 'setTimeout(function(){jQuery(' . wp_json_encode( implode( ',', $ids ) ) . ').each(function(){'
		. 'var $i=jQuery(this);if($i.hasClass("hasDatepicker")){$i.datepicker("option","minDate",1);}});},0);';
	GFFormDisplay::add_init_script( $form['id'], 'mh_gf_dynamics_datepicker_min', GFFormDisplay::ON_PAGE_RENDER, $script );
}

add_action( 'gform_register_init_scripts', 'mh_gf_dynamics_datepicker_min' );

/**
 * ?date=Y-m-d from the moving day calendar fills a date field whose dynamic
 * population parameter is "date", in that field's own format. Dates from today
 * backwards are ignored, as on the LibreForm form.
 *
 * @param mixed         $value Default value.
 * @param GF_Field|null $field Field.
 * @return mixed
 */
function mh_gf_dynamics_prefill_moving_date( $value, $field = null ) {
	$raw = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! is_object( $field ) || $field->type !== 'date' || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m ) ) {
		return $value;
	}
	if ( ! checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) || $raw <= current_time( 'Y-m-d' ) ) {
		return $value;
	}

	$formats = array(
		'mdy'       => 'm/d/Y',
		'dmy'       => 'd/m/Y',
		'dmy_dash'  => 'd-m-Y',
		'dmy_dot'   => 'd.m.Y',
		'ymd_slash' => 'Y/m/d',
		'ymd_dash'  => 'Y-m-d',
		'ymd_dot'   => 'Y.m.d',
	);
	$format = isset( $formats[ $field->dateFormat ] ) ? $formats[ $field->dateFormat ] : 'm/d/Y';

	return gmdate( $format, gmmktime( 0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1] ) );
}

add_filter( 'gform_field_value_date', 'mh_gf_dynamics_prefill_moving_date', 10, 2 );

/**
 * dataLayer event for a successful offer form submission (not spam, not aborted).
 *
 * GTM needs a Custom Event trigger on `gf_lead_submit` for the same lead
 * conversion tags its LibreForm trigger fires today.
 *
 * @param string|array $confirmation Confirmation.
 * @param array        $form         Form.
 * @param array        $entry        Entry.
 * @return string|array
 */
function mh_gf_dynamics_lead_event( $confirmation, $form, $entry ) {
	if ( ! is_string( $confirmation ) || (int) rgar( $entry, 'id' ) <= 0 || rgar( $entry, 'status' ) === 'spam' ) {
		return $confirmation;
	}

	$wplf = mh_gf_dynamics_wplf_form_for_form( rgar( $form, 'id' ) );
	if ( ! $wplf ) {
		return $confirmation;
	}

	$data = array(
		'event'        => 'gf_lead_submit',
		'gf_form_id'   => (int) rgar( $form, 'id' ),
		'wplf_form_id' => $wplf,
		'lomake'       => preg_replace( '/^tarjouspyynto-/', '', (string) get_post_field( 'post_name', $wplf ) ),
	);

	return $confirmation . '<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push(' . wp_json_encode( $data ) . ');</script>';
}

add_filter( 'gform_confirmation', 'mh_gf_dynamics_lead_event', 20, 3 );
