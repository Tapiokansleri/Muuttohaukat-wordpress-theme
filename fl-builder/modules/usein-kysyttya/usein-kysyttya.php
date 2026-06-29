<?php

/**
 * @class FLUseinKysyttyaModule
 */
class FLUseinKysyttyaModule extends FLBuilderModule {

	public function __construct() {
		parent::__construct(array(
			'name'            => __( 'Usein kysyttyä', 'fl-builder' ),
			'description'     => __( 'Display frequently asked questions in an accordion layout.', 'fl-builder' ),
			'category'        => __( 'Content', 'fl-builder' ),
			'editor_export'   => false,
			'partial_refresh' => true,
			'icon'            => 'editor-help.svg',
		));
	}

	public function enqueue_scripts() {
		$this->add_js( 'usein-kysyttya-frontend', $this->url . 'js/frontend.js', array( 'jquery' ), '', true );
		$this->add_css( 'usein-kysyttya-frontend', $this->url . 'css/frontend.css' );
	}

	/**
	 * Parse the textarea content into an array of question/answer pairs.
	 *
	 * Expected format:
	 * [question]Question text here[/question]
	 * [answer]Answer HTML here[/answer]
	 *
	 * Repeatable blocks.
	 */
	public static function parse_questions( $text ) {
		if ( empty( $text ) ) {
			return array();
		}

		$questions = array();
		$pattern = '/\[question\](.*?)\[\/question\]\s*\[answer\](.*?)\[\/answer\]/si';

		if ( preg_match_all( $pattern, $text, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$q = trim( $match[1] );
				$a = trim( $match[2] );
				if ( ! empty( $q ) ) {
					$questions[] = (object) array(
						'question' => $q,
						'answer'   => $a,
					);
				}
			}
		}

		return $questions;
	}
}

if ( class_exists( 'FLBuilder' ) ) {

	FLBuilder::register_module('FLUseinKysyttyaModule', array(
		'general' => array(
			'title'    => __( 'General', 'fl-builder' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'title' => array(
							'type'    => 'text',
							'label'   => __( 'Title', 'fl-builder' ),
							'default' => 'Usein kysyttyä',
						),
						'questions_text' => array(
							'type'    => 'textarea',
							'label'   => __( 'Questions & Answers', 'fl-builder' ),
							'rows'    => 20,
							'default' => "[question]Esimerkkikysymys?[/question]\n[answer]Tässä on vastaus.[/answer]",
							'help'    => __( 'Enter each Q&A pair using [question]...[/question] and [answer]...[/answer] tags. You can add as many pairs as you want.', 'fl-builder' ),
						),
					),
				),
			),
		),
		'style' => array(
			'title'    => __( 'Style', 'fl-builder' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'columns' => array(
							'type'    => 'select',
							'label'   => __( 'Columns', 'fl-builder' ),
							'default' => '2',
							'options' => array(
								'1' => '1',
								'2' => '2',
							),
						),
					),
				),
			),
		),
	));
}
