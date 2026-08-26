<?php
/**
 * Builds a LibreForm-shaped JSON payload from a Gravity Forms entry.
 *
 * @package Muuttohaukat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MH_GF_Dynamics_Payload_Builder {

	/**
	 * Map GF entry fields into WPLF `entries` using the feed field map.
	 *
	 * Empty values are omitted (LibreForm omits unchecked checkboxes).
	 * Known checkbox keys are normalized to LibreForm's canonical values.
	 *
	 * @param array                    $feed  GF feed.
	 * @param array                    $entry GF entry.
	 * @param array                    $form  GF form.
	 * @param MH_GF_Dynamics_AddOn|null $addon Add-on instance (for get_field_value).
	 * @return array<string, string>
	 */
	public static function build_entries( $feed, $entry, $form, $addon = null ) {
		$map = GFAddOn::get_dynamic_field_map_fields( $feed, 'field_map' );
		if ( ! is_array( $map ) ) {
			$map = array();
		}

		$allowed   = array_flip( mh_gf_dynamics_wplf_field_keys() );
		$canonical = mh_gf_dynamics_checkbox_canonical_values();
		$entries   = array();

		foreach ( $map as $wplf_key => $gf_field_id ) {
			if ( ! isset( $allowed[ $wplf_key ] ) || $gf_field_id === '' || $gf_field_id === null ) {
				continue;
			}

			$value = self::resolve_field_value( $form, $entry, $gf_field_id, $addon );
			$value = self::normalize_value( $value );

			if ( $value === null || $value === '' ) {
				continue;
			}

			if ( isset( $canonical[ $wplf_key ] ) ) {
				// Any truthy mapped value for a LibreForm checkbox becomes the canonical checked string.
				$entries[ $wplf_key ] = $canonical[ $wplf_key ];
				continue;
			}

			$entries[ $wplf_key ] = $value;
		}

		// LibreForm always includes Referrer as the page URL when the template sets it.
		if ( ! isset( $entries['Referrer'] ) ) {
			$source = self::clean_url( rgar( $entry, 'source_url' ) );
			if ( $source !== '' ) {
				$entries['Referrer'] = $source;
			}
		} else {
			$entries['Referrer'] = self::clean_url( $entries['Referrer'] );
		}

		return $entries;
	}

	/**
	 * Full Azure envelope matching theme `wplfAfterSubmission` POST body.
	 *
	 * @param array                     $feed  GF feed.
	 * @param array                     $entry GF entry.
	 * @param array                     $form  GF form.
	 * @param MH_GF_Dynamics_AddOn|null $addon Add-on.
	 * @return array{kind: string, data: array}
	 */
	public static function build( $feed, $entry, $form, $addon = null ) {
		$wplf_form_id = absint( rgars( $feed, 'meta/wplf_form_id' ) );
		$entries      = self::build_entries( $feed, $entry, $form, $addon );

		return self::build_envelope( $wplf_form_id, $entries, $entry );
	}

	/**
	 * Wrap mapped entries in the WPLF submission envelope (no GF dependency beyond helpers).
	 *
	 * @param int                $wplf_form_id Impersonated LibreForm post ID.
	 * @param array<string,string> $entries    Mapped field values.
	 * @param array              $entry        GF-like entry (id, dates, source_url).
	 * @return array{kind: string, data: array}
	 */
	public static function build_envelope( $wplf_form_id, array $entries, array $entry ) {
		$entry_id = (int) ( isset( $entry['id'] ) ? $entry['id'] : 0 );
		if ( $entry_id < 0 ) {
			$entry_id = 0;
		}
		$title = sprintf( 'Submission %d', $entry_id );

		$created  = isset( $entry['date_created'] ) ? $entry['date_created'] : null;
		$modified = isset( $entry['date_updated'] ) ? $entry['date_updated'] : null;
		if ( ! is_string( $modified ) || $modified === '' ) {
			$modified = $created;
		}

		$uuid = function_exists( 'wp_generate_uuid4' )
			? wp_generate_uuid4()
			: sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff )
			);

		$data = array(
			'ID'           => $entry_id,
			'uuid'         => $uuid,
			'title'        => $title,
			'referrer'     => array(
				'type' => 'post',
				'url'  => self::clean_url( isset( $entry['source_url'] ) ? $entry['source_url'] : '' ),
			),
			// Azure Function deserializes historyId as System.Int32 — null → HTTP 400.
			'historyId'    => 0,
			'createdAt'    => is_string( $created ) ? $created : null,
			'modifiedAt'   => is_string( $modified ) ? $modified : null,
			'usedFallback' => false,
			'formId'       => (int) $wplf_form_id,
			'entries'      => (object) $entries,
			'formFields'   => new stdClass(),
			'meta'         => new stdClass(),
		);

		return array(
			'kind' => 'getSubmission',
			'data' => $data,
		);
	}

	/**
	 * Encode payload the same way the theme does (wp_json_encode defaults).
	 *
	 * @param array $payload Payload array.
	 * @return string|false
	 */
	public static function encode( $payload ) {
		return wp_json_encode( $payload );
	}

	/**
	 * @param array                     $form        GF form.
	 * @param array                     $entry       GF entry.
	 * @param string                    $gf_field_id Field ID or special key.
	 * @param MH_GF_Dynamics_AddOn|null $addon       Add-on.
	 * @return mixed
	 */
	private static function resolve_field_value( $form, $entry, $gf_field_id, $addon ) {
		if ( $addon instanceof MH_GF_Dynamics_AddOn ) {
			return $addon->get_field_value( $form, $entry, $gf_field_id );
		}

		return rgar( $entry, (string) $gf_field_id );
	}

	/**
	 * Decode HTML entities GF may leave in source URLs (&amp; → &).
	 *
	 * @param mixed $url Raw URL.
	 * @return string
	 */
	private static function clean_url( $url ) {
		if ( ! is_string( $url ) || $url === '' ) {
			return '';
		}
		return html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	/**
	 * Flatten GF multi-values to a single string; treat empty arrays as empty.
	 *
	 * @param mixed $value Raw value.
	 * @return string|null
	 */
	private static function normalize_value( $value ) {
		if ( is_array( $value ) ) {
			$value = array_filter(
				$value,
				static function ( $item ) {
					return $item !== null && $item !== '';
				}
			);
			if ( empty( $value ) ) {
				return null;
			}
			$value = implode( ', ', $value );
		}

		if ( $value === null ) {
			return null;
		}

		if ( is_bool( $value ) ) {
			return $value ? '1' : '';
		}

		if ( is_scalar( $value ) ) {
			return trim( (string) $value );
		}

		return null;
	}
}
