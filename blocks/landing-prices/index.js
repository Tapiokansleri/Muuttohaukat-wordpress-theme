( function ( blocks, blockEditor, components, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;
	var PanelBody = components.PanelBody;
	var ToggleControl = components.ToggleControl;
	var TextControl = components.TextControl;
	var el = element.createElement;
	var Fragment = element.Fragment;

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing-sec-head' }, [
			[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Hinnat' } ],
			[ 'core/heading', { level: 2, content: 'Mitä muutto maksaa' } ]
		] ]
	];

	function Panel( props ) {
		return el(
			InspectorControls,
			{},
			el(
				PanelBody,
				{ title: 'Taulukko', initialOpen: true },
				el( ToggleControl, {
					label: 'Näytä sarake Muuttopalvelu',
					help: 'Ilman muuttolaatikoita.',
					checked: !! props.attributes.showBasic,
					onChange: function ( value ) { props.setAttributes( { showBasic: value } ); }
				} ),
				el( ToggleControl, {
					label: 'Näytä huomautus ja linkki hintasivulle',
					checked: !! props.attributes.note,
					onChange: function ( value ) { props.setAttributes( { note: value } ); }
				} ),
				el( TextControl, {
					label: 'Hintakerroin',
					help: 'Kertoo taulukon hinnat, esim. 2.15 = +115 % (täyden palvelun muutto). Tulokset pyöristetään lähimpään 5 euroon.',
					type: 'number',
					step: '0.01',
					min: '0',
					value: props.attributes.multiplier === undefined ? 1 : props.attributes.multiplier,
					onChange: function ( value ) {
						var n = parseFloat( value );
						props.setAttributes( { multiplier: isNaN( n ) || n <= 0 ? 1 : n } );
					}
				} ),
				el( 'p', { style: { margin: '8px 0 0', fontSize: '12px', opacity: 0.8 } },
					'Luvut tulevat teeman tiedostosta inc/LandingLocal.php, joten ne ovat samat kaikilla sivuilla.'
				)
			)
		);
	}

	function Table( attributes ) {
		var rows = window.mhPrices || [];
		var showBasic = !! attributes.showBasic;
		var multiplier = parseFloat( attributes.multiplier ) || 1;

		// Same rule as render.php: multiply every number, round to the nearest 5 €.
		function cell( value ) {
			if ( ! value ) return 'Pyydä tarjous';
			if ( Math.abs( multiplier - 1 ) < 0.0001 ) return value;
			return String( value ).replace( /\d+/g, function ( n ) {
				return String( Math.round( parseInt( n, 10 ) * multiplier / 5 ) * 5 );
			} );
		}

		var head = [ el( 'th', { key: 'h0', scope: 'col' }, 'Asunnon koko' ) ];
		if ( showBasic ) head.push( el( 'th', { key: 'h1', scope: 'col' }, 'Muuttopalvelu' ) );
		head.push( el( 'th', { key: 'h2', scope: 'col' }, 'Muutto ja laatikot' ) );

		var body = rows.map( function ( row, i ) {
			var cells = [
				el( 'th', { key: 'c0', scope: 'row' },
					row.size,
					row.type ? el( 'span', { className: 'mh-landing-prices__type' }, row.type ) : null
				)
			];
			if ( showBasic ) cells.push( el( 'td', { key: 'c1' }, cell( row.basic ) ) );
			cells.push( el( 'td', { key: 'c2' }, cell( row.boxes ) ) );
			return el( 'tr', { key: i }, cells );
		} );

		return el( 'div', { className: 'mh-landing-prices__table-wrap' },
			el( 'table', { className: 'mh-landing-prices__table' },
				el( 'thead', {}, el( 'tr', {}, head ) ),
				el( 'tbody', {}, body )
			)
		);
	}

	registerBlockType( 'muuttohaukat/landing-prices', {
		edit: function ( props ) {
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: false } );

			var body = el( 'div', { className: 'mh-landing__inner' },
				el( 'div', innerProps ),
				Table( props.attributes ),
				props.attributes.note
					? el( 'p', { className: 'mh-landing-prices__note' },
						'Hinta määräytyy asunnon koon, tavaramäärän ja matkan mukaan. Tarkemmat hinnat ja se, mitä niihin sisältyy, ovat muuttopalvelun hinta -sivulla.' )
					: null
			);

			return el( Fragment, {},
				el( Panel, props ),
				window.mhLandingBackground.wrapSection( props, 'mh-landing-section mh-landing-prices', body )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element );
