( function ( blocks, blockEditor, components, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var ToggleControl = components.ToggleControl;
	var el = element.createElement;
	var Fragment = element.Fragment;

	function officeList() {
		var offices = window.mhOffices || {};
		var options = Object.keys( offices ).map( function ( key ) {
			return { label: offices[ key ].label, value: key };
		} );
		options.push( { label: 'Ei toimipistettä', value: '' } );
		return options;
	}

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing-sec-head' }, [
			[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Paikallinen palvelu' } ],
			[ 'core/heading', { level: 2, content: 'Muutto hoituu paikan päältä' } ]
		] ],
		[ 'core/paragraph', { content: 'Kerro tässä, mistä toimipisteestä muutto lähtee, mitkä kunnat kuuluvat samaan käyntiin ja mikä alueella on muuttajan kannalta olennaista.' } ]
	];

	function Panel( props ) {
		var offices = window.mhOffices || {};
		var chosen = offices[ props.attributes.office ];

		return el(
			InspectorControls,
			{},
			el(
				PanelBody,
				{ title: 'Paikkakunta ja toimipiste', initialOpen: true },
				el( TextControl, {
					label: 'Paikkakunta',
					help: 'Käytetään rakenteisessa datassa (areaServed).',
					value: props.attributes.city || '',
					onChange: function ( value ) { props.setAttributes( { city: value } ); }
				} ),
				el( SelectControl, {
					label: 'Toimipiste',
					value: props.attributes.office || '',
					options: officeList(),
					onChange: function ( value ) { props.setAttributes( { office: value } ); }
				} ),
				chosen ? el( 'p', { style: { margin: '0 0 12px', fontSize: '12px', opacity: 0.8 } },
					chosen.street + ', ' + chosen.zip + ' ' + chosen.city
				) : null,
				el( ToggleControl, {
					label: 'Lisää MovingCompany-merkintä',
					checked: !! props.attributes.schema,
					onChange: function ( value ) { props.setAttributes( { schema: value } ); }
				} )
			)
		);
	}

	function OfficeCard( office ) {
		if ( ! office ) return null;
		var hours = ( window.mhOfficeHours || {} ).text || '';

		return el( 'aside', { className: 'mh-landing-local__office' },
			el( 'p', { className: 'mh-landing-kicker' }, office.label ),
			el( 'p', { className: 'mh-landing-local__address' }, office.street, el( 'br' ), office.zip + ' ' + office.city ),
			el( 'p', { className: 'mh-landing-local__phone' }, office.phone ),
			el( 'p', { className: 'mh-landing-local__hours' }, hours ),
			el( 'p', { className: 'mh-landing-local__link' },
				el( 'span', { className: 'mh-landing__button mh-landing__button--ghost' }, office.area )
			)
		);
	}

	registerBlockType( 'muuttohaukat/landing-local', {
		edit: function ( props ) {
			var offices = window.mhOffices || {};
			var innerProps = useInnerBlocksProps(
				{ className: 'mh-landing-local__text' },
				{ template: TEMPLATE, templateLock: false }
			);

			var body = el( 'div', { className: 'mh-landing__inner mh-landing-local__inner' },
				el( 'div', innerProps ),
				OfficeCard( offices[ props.attributes.office ] )
			);

			return el( Fragment, {},
				el( Panel, props ),
				window.mhLandingBackground.wrapSection( props, 'mh-landing-section mh-landing-local', body )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element );
