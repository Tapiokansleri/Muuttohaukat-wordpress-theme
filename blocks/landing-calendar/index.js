( function ( blocks, blockEditor, components, element ) {
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var ToggleControl = components.ToggleControl;
	var el = element.createElement;
	var Fragment = element.Fragment;

	function Panel( props ) {
		return el(
			InspectorControls,
			{},
			el(
				PanelBody,
				{ title: 'Kalenteri', initialOpen: true },
				el( ToggleControl, {
					label: 'Näytä tarjouslupaus',
					help: 'Lihavoitu kappale kalenterin yläpuolella.',
					checked: !! props.attributes.showHighlight,
					onChange: function ( value ) { props.setAttributes( { showHighlight: value } ); }
				} ),
				el( 'p', { style: { margin: '8px 0 0', fontSize: '12px', opacity: 0.8 } },
					'Tekstit tulevat teeman tiedostosta inc/CalendarSection.php, joten ne ovat samat kaikilla sivuilla.'
				)
			)
		);
	}

	registerBlockType( 'muuttohaukat/landing-calendar', {
		edit: function ( props ) {
			var texts = window.mhCalendarTexts || {};
			var body = el( 'div', { className: 'mh-calendar-section__inner' },
				el( 'div', { className: 'mh-calendar-section__text' },
					el( 'h2', { className: 'mh-calendar-section__title' }, texts.heading || 'Varaa muuttopäivä heti kun tiedät sen' ),
					( texts.paragraphs || [] ).map( function ( p, i ) { return el( 'p', { key: i }, p ); } ),
					props.attributes.showHighlight && texts.highlightText
						? el( 'p', { className: 'mh-calendar-section__highlight' }, texts.highlightText )
						: null
				),
				el( 'p', { style: { opacity: 0.7, margin: 0 } }, 'Kolmen kuukauden kalenteri näkyy sivulla.' )
			);

			return el( Fragment, {},
				el( Panel, props ),
				window.mhLandingBackground.wrapSection( props, 'mh-landing-section mh-calendar-section', body )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element );
