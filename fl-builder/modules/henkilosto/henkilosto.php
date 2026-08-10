<?php

/**
 * @class FLHenkilostoModule
 *
 * Section heading + repeatable staff entries (photo left, details right).
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
	 * Build a tel: href from a display phone string.
	 *
	 * @param string $phone
	 * @return string
	 */
	public static function phone_href( $phone ) {
		$digits = preg_replace( '/[^\d+]/', '', (string) $phone );
		return $digits ? 'tel:' . $digits : '';
	}
}

if ( class_exists( 'FLBuilder' ) ) {

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
								'help'    => __( 'Esim. Aluevastaava – suomi, englanti', 'muuttohaukat' ),
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
							),
							'email' => array(
								'type'    => 'text',
								'label'   => __( 'Sähköposti', 'muuttohaukat' ),
								'default' => '',
							),
						),
					),
				),
			),
		),
	) );
}
