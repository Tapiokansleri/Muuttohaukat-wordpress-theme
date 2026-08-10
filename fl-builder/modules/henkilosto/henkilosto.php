<?php

/**
 * @class FLHenkilostoModule
 *
 * Section heading + repeatable staff entries (photo left, details right).
 * Supports bulk import textarea and language flags.
 */
class FLHenkilostoModule extends FLBuilderModule {

	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Henkilöstö', 'muuttohaukat' ),
			'description'     => __( 'Henkilöstölistaus: yläotsikko ja useita henkilöitä (kuva + yhteystiedot).', 'muuttohaukat' ),
			'category'        => __( 'Muuttohaukat', 'muuttohaukat' ),
			'editor_export'   => false,
			'partial_refresh' => true,
			'icon'            => 'group.svg',
		) );
	}

	public function enqueue_scripts() {
		// Use henkilosto.css — NOT css/frontend.css.
		// BB auto-bundles frontend.css into layout-*.css and that cache stays stale.
		$path = $this->dir . 'css/henkilosto.css';
		$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

		wp_enqueue_style(
			'mh-henkilosto',
			$this->url . 'css/henkilosto.css',
			array(),
			$ver
		);
	}

	/**
	 * Supported language codes → labels.
	 *
	 * @return array<string,string>
	 */
	public static function language_labels() {
		return array(
			'fi' => __( 'Suomi', 'muuttohaukat' ),
			'en' => __( 'Englanti', 'muuttohaukat' ),
			'sv' => __( 'Ruotsi', 'muuttohaukat' ),
			'de' => __( 'Saksa', 'muuttohaukat' ),
			'et' => __( 'Viro', 'muuttohaukat' ),
			'ru' => __( 'Venäjä', 'muuttohaukat' ),
			'fr' => __( 'Ranska', 'muuttohaukat' ),
			'es' => __( 'Espanja', 'muuttohaukat' ),
		);
	}

	/**
	 * Normalize language input into known codes.
	 *
	 * @param mixed $raw Array, comma string, or empty.
	 * @return string[]
	 */
	public static function normalize_languages( $raw ) {
		$labels = self::language_labels();
		$aliases = array(
			'fi' => 'fi', 'suomi' => 'fi', 'finnish' => 'fi', 'fin' => 'fi',
			'en' => 'en', 'englanti' => 'en', 'english' => 'en', 'eng' => 'en',
			'sv' => 'sv', 'ruotsi' => 'sv', 'swedish' => 'sv', 'swe' => 'sv',
			'de' => 'de', 'saksa' => 'de', 'german' => 'de', 'ger' => 'de', 'deu' => 'de',
			'et' => 'et', 'viro' => 'et', 'eesti' => 'et', 'estonian' => 'et', 'est' => 'et',
			'ru' => 'ru', 'venaja' => 'ru', 'venäjä' => 'ru', 'russian' => 'ru', 'rus' => 'ru',
			'fr' => 'fr', 'ranska' => 'fr', 'french' => 'fr', 'fra' => 'fr',
			'es' => 'es', 'espanja' => 'es', 'spanish' => 'es', 'spa' => 'es',
		);

		if ( is_string( $raw ) ) {
			$raw = preg_split( '/[\s,;\/|]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY );
		}
		if ( ! is_array( $raw ) || empty( $raw ) ) {
			return array( 'fi' );
		}

		$out = array();
		foreach ( $raw as $item ) {
			$key = strtolower( trim( (string) $item ) );
			$key = str_replace( array( 'ä', 'ö', 'å' ), array( 'a', 'o', 'a' ), $key );
			if ( isset( $aliases[ $key ] ) ) {
				$code = $aliases[ $key ];
				if ( isset( $labels[ $code ] ) ) {
					$out[ $code ] = $code;
				}
			}
		}

		// Always default to Finnish when nothing valid was provided.
		return ! empty( $out ) ? array_values( $out ) : array( 'fi' );
	}

	/**
	 * Inline SVG flag for a language code.
	 *
	 * @param string $code
	 * @return string
	 */
	public static function language_flag_svg( $code ) {
		switch ( $code ) {
			case 'fi':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 11" aria-hidden="true"><rect width="18" height="11" fill="#fff"/><rect x="5" width="3" height="11" fill="#002F6C"/><rect y="4" width="18" height="3" fill="#002F6C"/></svg>';
			case 'en':
				// Simplified Union Jack (no clipPath ids — safe when repeated).
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30" aria-hidden="true"><rect width="60" height="30" fill="#012169"/><path d="M0,0 60,30 M60,0 0,30" stroke="#fff" stroke-width="6"/><path d="M0,0 60,30 M60,0 0,30" stroke="#C8102E" stroke-width="2"/><path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/><path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/></svg>';
			case 'sv':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 10" aria-hidden="true"><rect width="16" height="10" fill="#006AA7"/><rect x="5" width="2" height="10" fill="#FECC00"/><rect y="4" width="16" height="2" fill="#FECC00"/></svg>';
			case 'de':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 3" aria-hidden="true"><rect width="5" height="1" y="0" fill="#000"/><rect width="5" height="1" y="1" fill="#D00"/><rect width="5" height="1" y="2" fill="#FFCE00"/></svg>';
			case 'et':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33 21" aria-hidden="true"><rect width="33" height="7" y="0" fill="#0072CE"/><rect width="33" height="7" y="7" fill="#000"/><rect width="33" height="7" y="14" fill="#fff"/></svg>';
			case 'ru':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6" aria-hidden="true"><rect width="9" height="2" y="0" fill="#fff"/><rect width="9" height="2" y="2" fill="#0039A6"/><rect width="9" height="2" y="4" fill="#D52B1E"/></svg>';
			case 'fr':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" aria-hidden="true"><rect width="1" height="2" x="0" fill="#002395"/><rect width="1" height="2" x="1" fill="#fff"/><rect width="1" height="2" x="2" fill="#ED2939"/></svg>';
			case 'es':
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" aria-hidden="true"><rect width="3" height="2" fill="#C60B1E"/><rect width="3" height="1" y="0.5" fill="#FFC400"/></svg>';
			default:
				return '';
		}
	}

	/**
	 * Build a tel: href from a display phone string.
	 *
	 * @param string $phone
	 * @return string
	 */
	public static function phone_href( $phone ) {
		$digits = preg_replace( '/[^\d+]/', '', (string) $phone );
		if ( ! $digits ) {
			return '';
		}
		// Finnish numbers often stored without +; keep leading 0 for local dial.
		return 'tel:' . $digits;
	}

	/**
	 * Build a mailto: href from an email string.
	 *
	 * @param string $email
	 * @return string
	 */
	public static function email_href( $email ) {
		$email = trim( (string) $email );
		if ( ! $email || ! is_email( $email ) ) {
			// Still link if it looks like an email (bulk paste may use odd domains).
			if ( ! $email || false === strpos( $email, '@' ) ) {
				return '';
			}
		}
		return 'mailto:' . antispambot( $email );
	}

	/**
	 * URL to the example CSV bundled with this module.
	 *
	 * @return string
	 */
	public static function example_csv_url() {
		return trailingslashit( get_stylesheet_directory_uri() ) . 'fl-builder/modules/henkilosto/assets/henkilosto-esimerkki.csv';
	}

	/**
	 * Split one import line into columns (CSV / semicolon / pipe / tab).
	 *
	 * @param string $line
	 * @return string[]
	 */
	public static function split_import_line( $line ) {
		if ( false !== strpos( $line, '|' ) ) {
			return array_map( 'trim', explode( '|', $line ) );
		}
		if ( false !== strpos( $line, "\t" ) ) {
			return array_map( 'trim', preg_split( '/\t+/', $line ) );
		}
		// Finnish Excel often uses semicolon; otherwise comma CSV (quoted fields OK).
		$delimiter = ( substr_count( $line, ';' ) >= 5 || ( false !== strpos( $line, ';' ) && false === strpos( $line, ',' ) ) )
			? ';'
			: ',';
		$parts = str_getcsv( $line, $delimiter );
		return array_map( 'trim', $parts );
	}

	/**
	 * Resolve a CSV photo cell into attachment ID + URL.
	 *
	 * Accepts media library ID or absolute/relative image URL.
	 *
	 * @param string $raw
	 * @return array{0:int,1:string} [ photo_id, photo_src ]
	 */
	public static function resolve_photo_ref( $raw ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return array( 0, '' );
		}

		if ( ctype_digit( $raw ) ) {
			$id = absint( $raw );
			$url = $id ? (string) wp_get_attachment_image_url( $id, 'medium_large' ) : '';
			return array( $id, $url );
		}

		// Allow protocol-relative or site-relative paths.
		$url = $raw;
		if ( 0 === strpos( $url, '//' ) ) {
			$url = ( is_ssl() ? 'https:' : 'http:' ) . $url;
		} elseif ( 0 === strpos( $url, '/' ) ) {
			$url = home_url( $url );
		}

		$id = 0;
		if ( function_exists( 'attachment_url_to_postid' ) ) {
			$id = absint( attachment_url_to_postid( $url ) );
		}

		return array( $id, esc_url_raw( $url ) );
	}

	/**
	 * Parse bulk import text into person objects.
	 *
	 * One person per line. Columns (CSV, semicolon, pipe | or tab):
	 * name, role, responsibilities, phone, email, languages, photo
	 *
	 * Photo: media library ID or image URL.
	 * Languages: comma-separated codes or names, e.g. fi,en or suomi,englanti
	 *
	 * @param string $text
	 * @return object[]
	 */
	public static function parse_bulk_import( $text ) {
		$text = trim( (string) $text );
		if ( '' === $text ) {
			return array();
		}

		// Strip UTF-8 BOM from Excel exports.
		if ( 0 === strpos( $text, "\xEF\xBB\xBF" ) ) {
			$text = substr( $text, 3 );
		}

		$lines   = preg_split( '/\r\n|\r|\n/', $text );
		$persons = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || 0 === strpos( $line, '#' ) ) {
				continue;
			}

			// Skip header row.
			if ( preg_match( '/^(nimi|name|kuva|photo)\b/iu', $line ) ) {
				continue;
			}

			$parts = self::split_import_line( $line );

			// Pad to 7 columns (optional photo last).
			while ( count( $parts ) < 7 ) {
				$parts[] = '';
			}

			$name = $parts[0];
			if ( '' === $name ) {
				continue;
			}

			list( $photo_id, $photo_src ) = self::resolve_photo_ref( $parts[6] );

			$persons[] = (object) array(
				'name'             => $name,
				'role'             => $parts[1],
				'responsibilities' => $parts[2],
				'phone'            => $parts[3],
				'email'            => $parts[4],
				'languages'        => self::normalize_languages( $parts[5] ),
				'photo'            => $photo_id ? (string) $photo_id : '',
				'photo_src'        => $photo_src,
			);
		}

		return $persons;
	}

	/**
	 * Collect persons from repeater + optional bulk import.
	 *
	 * @param object $settings
	 * @return object[]
	 */
	public static function collect_persons( $settings ) {
		$manual = array();
		if ( isset( $settings->persons ) && is_array( $settings->persons ) ) {
			foreach ( $settings->persons as $person ) {
				if ( ! is_object( $person ) ) {
					continue;
				}
				$name  = isset( $person->name ) ? trim( (string) $person->name ) : '';
				$photo = isset( $person->photo ) ? $person->photo : '';
				if ( '' === $name && empty( $photo ) ) {
					continue;
				}
				$person->languages = self::normalize_languages(
					isset( $person->languages ) ? $person->languages : array()
				);
				$manual[] = $person;
			}
		}

		// Bulk textarea is only a staging area; Import-button copies into $persons.
		return $manual;
	}
}

/**
 * AJAX: parse bulk CSV for the Beaver Builder import button.
 */
add_action( 'wp_ajax_mh_henkilosto_parse_import', static function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
	}
	check_ajax_referer( 'mh_henkilosto_import', 'nonce' );

	$text = isset( $_POST['text'] ) ? wp_unslash( (string) $_POST['text'] ) : '';
	$persons = FLHenkilostoModule::parse_bulk_import( $text );

	if ( empty( $persons ) ) {
		wp_send_json_error( array(
			'message' => __( 'Ei kelvollisia rivejä. Tarkista CSV-muoto.', 'muuttohaukat' ),
		) );
	}

	// Ensure JSON-friendly plain arrays (languages always includes fi by default).
	$out = array();
	foreach ( $persons as $person ) {
		$out[] = array(
			'name'             => (string) $person->name,
			'role'             => (string) $person->role,
			'responsibilities' => (string) $person->responsibilities,
			'phone'            => (string) $person->phone,
			'email'            => (string) $person->email,
			'languages'        => FLHenkilostoModule::normalize_languages( $person->languages ),
			'photo'            => (string) $person->photo,
			'photo_src'        => (string) $person->photo_src,
		);
	}

	wp_send_json_success( array( 'persons' => $out ) );
} );

if ( class_exists( 'FLBuilder' ) ) {

	$language_options = FLHenkilostoModule::language_labels();

	$bulk_help = __(
		'Yksi henkilö per rivi. Sarakkeet: CSV (pilkku/puolipiste), | tai sarkain. Järjestys: nimi, titteli, vastuualueet, puhelin, sähköposti, kielet, kuva. Kuva = mediakirjaston ID tai kuvan URL. Kielet esim. fi,en. Rivin voi aloittaa #-merkillä kommenttina.',
		'muuttohaukat'
	);

	$csv_url = FLHenkilostoModule::example_csv_url();

	FLBuilder::register_module( 'FLHenkilostoModule', array(
		'general' => array(
			'title'    => __( 'Yleistä', 'muuttohaukat' ),
			'sections' => array(
				'heading' => array(
					'title'  => __( 'Otsikko', 'muuttohaukat' ),
					'fields' => array(
						'heading' => array(
							'type'    => 'text',
							'label'   => __( 'Yläotsikko', 'muuttohaukat' ),
							'default' => 'Pääkaupunkiseudulla sinua palvelee',
						),
						'heading_tag' => array(
							'type'    => 'select',
							'label'   => __( 'Otsikkotaso', 'muuttohaukat' ),
							'default' => 'h2',
							'options' => array(
								'h2' => 'H2',
								'h3' => 'H3',
								'h4' => 'H4',
							),
						),
					),
				),
				'persons' => array(
					'title'  => __( 'Henkilöt', 'muuttohaukat' ),
					'fields' => array(
						'persons' => array(
							'type'         => 'form',
							'label'        => __( 'Henkilö', 'muuttohaukat' ),
							'form'         => 'henkilosto_person_form',
							'preview_text' => 'name',
							'multiple'     => true,
						),
					),
				),
			),
		),
		'import' => array(
			'title'    => __( 'Bulk-import', 'muuttohaukat' ),
			'sections' => array(
				'bulk' => array(
					'title'  => __( 'Tuo useita henkilöitä', 'muuttohaukat' ),
					'fields' => array(
						'bulk_example_csv' => array(
							'type'    => 'raw',
							'content' => sprintf(
								'<p class="fl-builder-settings-tab-description" style="margin:0 0 12px;">
									<a class="fl-builder-button" href="%1$s" download="henkilosto-esimerkki.csv" target="_blank" rel="noopener noreferrer">%2$s</a>
									<br><span style="display:inline-block;margin-top:8px;opacity:.85;">%3$s</span>
								</p>',
								esc_url( $csv_url ),
								esc_html__( 'Lataa esimerkki CSV', 'muuttohaukat' ),
								esc_html__( 'Avaa Excelissä, täytä rivit ja liitä sisältö alle. Paina sitten Tuo ja korvaa.', 'muuttohaukat' )
							),
						),
						'bulk_import' => array(
							'type'    => 'textarea',
							'label'   => __( 'Bulk-lista', 'muuttohaukat' ),
							'rows'    => 12,
							'default' => '',
							'help'    => $bulk_help,
						),
						'bulk_import_submit' => array(
							'type'    => 'raw',
							'content' => sprintf(
								'<p class="fl-builder-settings-tab-description mh-henkilosto-import-actions" style="margin:12px 0 0;"
									data-nonce="%3$s"
									data-confirm="%4$s"
									data-working="%5$s"
									data-done="%6$s"
									data-error="%7$s"
									data-empty="%8$s"
									data-ajax-url="%9$s">
									<button type="button" class="fl-builder-button fl-builder-button-primary mh-henkilosto-import-submit">%1$s</button>
									<span class="mh-henkilosto-import-status" style="display:inline-block;margin-left:10px;" aria-live="polite"></span>
									<br><span style="display:inline-block;margin-top:8px;opacity:.85;">%2$s</span>
								</p>',
								esc_html__( 'Tuo ja korvaa', 'muuttohaukat' ),
								esc_html__( 'Korvaa kaikki henkilöt listalla, tyhjentää bulk-kentän ja tallentaa palikan.', 'muuttohaukat' ),
								esc_attr( wp_create_nonce( 'mh_henkilosto_import' ) ),
								esc_attr__( 'Korvataanko kaikki nykyiset henkilöt tuoduilla riveillä?', 'muuttohaukat' ),
								esc_attr__( 'Tuodaan…', 'muuttohaukat' ),
								esc_attr__( 'Tuonti valmis — tallennetaan…', 'muuttohaukat' ),
								esc_attr__( 'Tuonti epäonnistui.', 'muuttohaukat' ),
								esc_attr__( 'Liitä ensin CSV-sisältö bulk-kenttään.', 'muuttohaukat' ),
								esc_url( admin_url( 'admin-ajax.php' ) )
							),
						),
					),
				),
			),
		),
		'style' => array(
			'title'    => __( 'Tyyli', 'muuttohaukat' ),
			'sections' => array(
				'layout' => array(
					'title'  => '',
					'fields' => array(
						'columns' => array(
							'type'    => 'select',
							'label'   => __( 'Sarakkeet (työpöytä)', 'muuttohaukat' ),
							'default' => '3',
							'options' => array(
								'1' => '1',
								'2' => '2',
								'3' => '3',
							),
						),
					),
				),
			),
		),
	) );

	FLBuilder::register_settings_form( 'henkilosto_person_form', array(
		'title' => __( 'Lisää henkilö', 'muuttohaukat' ),
		'tabs'  => array(
			'general' => array(
				'title'    => __( 'Yleistä', 'muuttohaukat' ),
				'sections' => array(
					'photo' => array(
						'title'  => __( 'Kuva', 'muuttohaukat' ),
						'fields' => array(
							'photo' => array(
								'type'        => 'photo',
								'label'       => __( 'Valokuva', 'muuttohaukat' ),
								'show_remove' => true,
							),
						),
					),
					'details' => array(
						'title'  => __( 'Tiedot', 'muuttohaukat' ),
						'fields' => array(
							'name' => array(
								'type'    => 'text',
								'label'   => __( 'Nimi', 'muuttohaukat' ),
								'default' => '',
							),
							'role' => array(
								'type'    => 'text',
								'label'   => __( 'Titteli / rooli', 'muuttohaukat' ),
								'default' => '',
								'help'    => __( 'Esim. Aluevastaava', 'muuttohaukat' ),
							),
							'responsibilities' => array(
								'type'    => 'text',
								'label'   => __( 'Vastuualueet', 'muuttohaukat' ),
								'default' => '',
								'help'    => __( 'Esim. Sopimusasiakkaat, yritysmuutot', 'muuttohaukat' ),
							),
							'phone' => array(
								'type'    => 'text',
								'label'   => __( 'Puhelin', 'muuttohaukat' ),
								'default' => '',
								'help'    => __( 'Muuttuu automaattisesti klikattavaksi tel:-linkiksi.', 'muuttohaukat' ),
							),
							'email' => array(
								'type'    => 'text',
								'label'   => __( 'Sähköposti', 'muuttohaukat' ),
								'default' => '',
								'help'    => __( 'Muuttuu automaattisesti klikattavaksi mailto:-linkiksi.', 'muuttohaukat' ),
							),
						),
					),
					'languages' => array(
						'title'  => __( 'Kielet', 'muuttohaukat' ),
						'fields' => array(
							'languages' => array(
								'type'         => 'select',
								'label'        => __( 'Kielet (näytetään lippuina)', 'muuttohaukat' ),
								'default'      => array( 'fi' ),
								'options'      => $language_options,
								'multi-select' => true,
								'help'         => __( 'Oletus: Suomi. Valitse kielet — ne näkyvät lippuina nimen alla.', 'muuttohaukat' ),
							),
						),
					),
				),
			),
		),
	) );
}
