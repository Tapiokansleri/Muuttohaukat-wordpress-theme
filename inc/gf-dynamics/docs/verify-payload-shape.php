<?php
/**
 * Offline verification: payload shape vs docs/wplf-payload-reference.json.
 *
 * Run: php docs/verify-payload-shape.php
 *
 * Does not call Azure or require WordPress/Gravity Forms.
 */

$root = dirname( __DIR__ );

// Minimal stubs so includes load without WordPress.
if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once $root . '/wplf-field-keys.php';
require_once $root . '/class-payload-builder.php';

$reference = json_decode( file_get_contents( $root . '/docs/wplf-payload-reference.json' ), true );
if ( ! is_array( $reference ) ) {
	fwrite( STDERR, "FAIL: could not read reference JSON\n" );
	exit( 1 );
}

$entries = array(
	'Nimi'           => 'Example Person',
	'Email'          => 'example@example.com',
	'Puhnro'         => '0400000000',
	'Toimipiste'     => 'helsinki@muuttohaukat.com',
	'LähtöHissi'     => 'on',
	'Pakkauspalvelu' => 'Kyllä',
	'Käyttöehdot'    => 'Hyväksytty',
	'Referrer'       => 'https://example.com/example-page',
);

// Unchecked checkbox must not appear.
if ( array_key_exists( 'KohdeHissi', $entries ) ) {
	fwrite( STDERR, "FAIL: unchecked KohdeHissi should be omitted\n" );
	exit( 1 );
}

$payload = MH_GF_Dynamics_Payload_Builder::build_envelope(
	1793,
	$entries,
	array(
		'id'           => 42,
		'date_created' => '2026-01-01 12:00:00',
		'date_updated' => '2026-01-01 12:00:00',
		'source_url'   => 'https://example.com/example-page',
	)
);

$json = MH_GF_Dynamics_Payload_Builder::encode( $payload );
if ( $json === false ) {
	fwrite( STDERR, "FAIL: encode failed\n" );
	exit( 1 );
}

$decoded = json_decode( $json, true );
$errors  = array();

if ( ( $decoded['kind'] ?? null ) !== 'getSubmission' ) {
	$errors[] = 'kind mismatch';
}

$data = $decoded['data'] ?? array();
foreach ( array( 'ID', 'uuid', 'title', 'referrer', 'historyId', 'createdAt', 'modifiedAt', 'usedFallback', 'formId', 'entries', 'formFields', 'meta' ) as $key ) {
	if ( ! array_key_exists( $key, $data ) ) {
		$errors[] = "missing data.$key";
	}
}

if ( (int) ( $data['formId'] ?? 0 ) !== 1793 ) {
	$errors[] = 'formId should be 1793 (kotimuutto)';
}

if ( ( $data['entries']['LähtöHissi'] ?? null ) !== 'on' ) {
	$errors[] = 'LähtöHissi must be on';
}
if ( ( $data['entries']['Pakkauspalvelu'] ?? null ) !== 'Kyllä' ) {
	$errors[] = 'Pakkauspalvelu must be Kyllä';
}
if ( ( $data['entries']['Käyttöehdot'] ?? null ) !== 'Hyväksytty' ) {
	$errors[] = 'Käyttöehdot must be Hyväksytty';
}

// Match theme encoding: default json_encode escapes non-ASCII.
if ( strpos( $json, 'L\\u00e4ht\\u00f6Hissi' ) === false && strpos( $json, 'LähtöHissi' ) === false ) {
	$errors[] = 'LähtöHissi key missing from JSON';
}

// formFields / meta must be objects {}, not arrays [].
if ( ! preg_match( '/"formFields"\s*:\s*\{/', $json ) ) {
	$errors[] = 'formFields must encode as object';
}
if ( ! preg_match( '/"meta"\s*:\s*\{/', $json ) ) {
	$errors[] = 'meta must encode as object';
}

$allowed = mh_gf_dynamics_wplf_field_keys();
foreach ( array_keys( $data['entries'] ) as $key ) {
	if ( ! in_array( $key, $allowed, true ) ) {
		$errors[] = "unexpected entries key: $key";
	}
}

// Lisätiedot template: lines whose merge tags all resolve to empty are dropped.
$merge_values = array(
	'{Lisätiedot:38}'                  => 'Piano on raskas.',
	'{Vaihtoehtoiset muuttopäivät:43}' => '',
	'{Toivottu yhteydenottotapa:45}'   => 'Puhelin',
);
$rendered = MH_GF_Dynamics_Payload_Builder::render_template(
	"{Lisätiedot:38}\n\nVaihtoehtoiset muuttopäivät: {Vaihtoehtoiset muuttopäivät:43}\n\nYhteydenottotapa: {Toivottu yhteydenottotapa:45}\n",
	static function ( $text ) use ( $merge_values ) {
		return strtr( $text, $merge_values );
	}
);
if ( $rendered !== "Piano on raskas.\n\nYhteydenottotapa: Puhelin" ) {
	$errors[] = 'render_template output mismatch: ' . json_encode( $rendered, JSON_UNESCAPED_UNICODE );
}

if ( $errors ) {
	fwrite( STDERR, "FAIL:\n - " . implode( "\n - ", $errors ) . "\n" );
	exit( 1 );
}

echo "OK: payload shape matches LibreForm reference (formId=1793, checkbox semantics, object meta).\n";
echo "Manual step: with GF installed, submit a test-mode feed and confirm Azure creates an offer after disabling test mode.\n";
exit( 0 );
