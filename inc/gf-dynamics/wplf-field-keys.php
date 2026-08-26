<?php
/**
 * Canonical LibreForm `entries` keys used by AddOfferToDynamics.
 *
 * Local LibreForm post IDs (for reference; production may differ):
 * - tarjouspyynto-yritysmuutto → 1791
 * - tarjouspyynto-kotimuutto   → 1793
 * - tilaa-muuttotarvikkeet     → 1795
 *
 * @package Muuttohaukat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All known WPLF field keys across D365-captured forms.
 *
 * @return string[]
 */
function mh_gf_dynamics_wplf_field_keys() {
	return array(
		// Contact
		'Nimi',
		'Email',
		'Puhnro',
		'YrityksenNimi',
		'Y-tunnus',
		// Branch + addresses
		'Toimipiste',
		'Lähtöosoite',
		'Lähtöpostinro',
		'Lähtökunta',
		'LähtöTyyppi',
		'LähtöPA',
		'LähtöKerros',
		'LähtöHissi',
		'KohdeOsoite',
		'KohdePostinro',
		'KohdeKunta',
		'KohdeTyyppi',
		'KohdePA',
		'KohdeKerros',
		'KohdeHissi',
		// Dates
		'Muuttopvm',
		'TarvikkeidenToimituspvm',
		'TarvikkeidenPalautuspvm',
		// Accessories
		'Muuttolaatikkoja',
		'Mappilaatikkoja',
		'Henkarilaatikkoja',
		'Merkintätarrapaketti',
		'Kuplamuovirulla',
		'Muuttosäkkirulla',
		'Pakkauspaperi',
		'Muuttoteippi',
		'Käsikiristekalvo',
		'Käsikiristekalvo100mm',
		// Additional services
		'Sisustussuunnittelu',
		'Pakkauspalvelu',
		'Kalusteasennus',
		'Varastointi',
		'MuuttosiivousLähtöpaikassa',
		'MuuttosiivousKohteessa',
		'PianonTaiKassakaapinMuutto',
		// Submit
		'Lisätiedot',
		'Referrer',
		'Käyttöehdot',
	);
}

/**
 * Choices for GF dynamic_field_map key dropdown.
 *
 * @return array<int, array{label: string, value: string}>
 */
function mh_gf_dynamics_wplf_field_choices() {
	$choices = array();
	foreach ( mh_gf_dynamics_wplf_field_keys() as $key ) {
		$choices[] = array(
			'label' => $key,
			'value' => $key,
		);
	}
	return $choices;
}

/**
 * Canonical checked values for LibreForm checkboxes.
 *
 * Unchecked boxes must be omitted from `entries` entirely.
 *
 * @return array<string, string>
 */
function mh_gf_dynamics_checkbox_canonical_values() {
	return array(
		'LähtöHissi'                 => 'on',
		'KohdeHissi'                 => 'on',
		'Sisustussuunnittelu'        => 'Kyllä',
		'Pakkauspalvelu'             => 'Kyllä',
		'Kalusteasennus'             => 'Kyllä',
		'Varastointi'                => 'Kyllä',
		'MuuttosiivousLähtöpaikassa' => 'Kyllä',
		'MuuttosiivousKohteessa'     => 'Kyllä',
		'PianonTaiKassakaapinMuutto' => 'Kyllä',
		'Käyttöehdot'                => 'Hyväksytty',
	);
}
