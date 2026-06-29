<?php

/**
 * @class FLMuuttohaukatPainikeModule
 *
 * One or two side-by-side branded buttons (yellow / black).
 * The second button is optional — leave its text blank to hide it.
 */
class FLMuuttohaukatPainikeModule extends FLBuilderModule {

	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Muuttohaukat painike', 'muuttohaukat' ),
			'description'     => __( 'Yksi tai kaksi vierekkäistä Muuttohaukat-painiketta (musta / keltainen).', 'muuttohaukat' ),
			'category'        => __( 'Muuttohaukat', 'muuttohaukat' ),
			'editor_export'   => false,
			'partial_refresh' => true,
			'icon'            => 'button.svg',
		) );
	}

	public function enqueue_scripts() {
		$this->add_css( 'muuttohaukat-painike', $this->url . 'css/frontend.css' );
	}
}

if ( class_exists( 'FLBuilder' ) ) {

	$color_options = array(
		'black'  => __( 'Musta', 'muuttohaukat' ),
		'yellow' => __( 'Keltainen', 'muuttohaukat' ),
	);

	$target_options = array(
		'_self'  => __( 'Sama välilehti', 'muuttohaukat' ),
		'_blank' => __( 'Uusi välilehti', 'muuttohaukat' ),
	);

	FLBuilder::register_module( 'FLMuuttohaukatPainikeModule', array(
		'button_1' => array(
			'title'    => __( 'Painike 1', 'muuttohaukat' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'button_1_text' => array(
							'type'    => 'text',
							'label'   => __( 'Teksti', 'muuttohaukat' ),
							'default' => 'Pyydä tarjous',
						),
						'button_1_url' => array(
							'type'    => 'link',
							'label'   => __( 'URL', 'muuttohaukat' ),
							'default' => 'https://muuttohaukat.kansleri.fi/tarjouspyynto/kotimuutto/',
						),
						'button_1_target' => array(
							'type'    => 'select',
							'label'   => __( 'Linkin kohde', 'muuttohaukat' ),
							'default' => '_self',
							'options' => $target_options,
						),
						'button_1_color' => array(
							'type'    => 'select',
							'label'   => __( 'Väri', 'muuttohaukat' ),
							'default' => 'black',
							'options' => $color_options,
						),
					),
				),
			),
		),
		'button_2' => array(
			'title'    => __( 'Painike 2 (valinnainen)', 'muuttohaukat' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'description' => __( 'Jätä teksti tyhjäksi, jos haluat vain yhden painikkeen.', 'muuttohaukat' ),
					'fields' => array(
						'button_2_text' => array(
							'type'    => 'text',
							'label'   => __( 'Teksti', 'muuttohaukat' ),
							'default' => '',
						),
						'button_2_url' => array(
							'type'    => 'link',
							'label'   => __( 'URL', 'muuttohaukat' ),
							'default' => '',
						),
						'button_2_target' => array(
							'type'    => 'select',
							'label'   => __( 'Linkin kohde', 'muuttohaukat' ),
							'default' => '_self',
							'options' => $target_options,
						),
						'button_2_color' => array(
							'type'    => 'select',
							'label'   => __( 'Väri', 'muuttohaukat' ),
							'default' => 'black',
							'options' => $color_options,
						),
					),
				),
			),
		),
	) );
}
