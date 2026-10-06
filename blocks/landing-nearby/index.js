( function ( blocks, blockEditor, components, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var el = element.createElement;
	var Fragment = element.Fragment;
	var MAX = 15;

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing-sec-head' }, [
			[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Lähikunnat' } ],
			[ 'core/heading', { level: 2, content: 'Muutot myös naapurikuntiin' } ]
		] ]
	];

	function toText( links ) {
		return ( links || [] ).map( function ( link ) {
			return link.label + ' | ' + link.url;
		} ).join( '\n' );
	}

	function fromText( text ) {
		return String( text || '' ).split( '\n' ).map( function ( line ) {
			var parts = line.split( '|' );
			return { label: ( parts[ 0 ] || '' ).trim(), url: ( parts[ 1 ] || '' ).trim() };
		} ).filter( function ( link ) {
			return link.label && link.url;
		} ).slice( 0, MAX );
	}

	function Panel( props ) {
		var count = ( props.attributes.links || [] ).length;

		return el(
			InspectorControls,
			{},
			el(
				PanelBody,
				{ title: 'Linkit', initialOpen: true },
				el( TextareaControl, {
					label: 'Kunnat, yksi rivi kutakin',
					help: 'Muoto: Kunnan nimi | /polku/. Enintään ' + MAX + ' riviä, nyt ' + count + '.',
					rows: 10,
					value: toText( props.attributes.links ),
					onChange: function ( value ) { props.setAttributes( { links: fromText( value ) } ); }
				} ),
				el( TextControl, {
					label: 'Alueen sivu',
					help: 'Esimerkiksi /hyvinkaa-uusimaa/',
					value: props.attributes.hub || '',
					onChange: function ( value ) { props.setAttributes( { hub: value } ); }
				} ),
				el( TextControl, {
					label: 'Alueen sivun painikkeen teksti',
					value: props.attributes.hubLabel || '',
					onChange: function ( value ) { props.setAttributes( { hubLabel: value } ); }
				} )
			)
		);
	}

	registerBlockType( 'muuttohaukat/landing-nearby', {
		edit: function ( props ) {
			var links = ( props.attributes.links || [] ).slice( 0, MAX );
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: false } );

			var list = links.length
				? el( 'ul', { className: 'mh-landing-nearby__list' }, links.map( function ( link, i ) {
					return el( 'li', { key: i }, el( 'span', {}, link.label ) );
				} ) )
				: el( 'p', { style: { opacity: 0.7 } }, 'Lisää kunnat oikean reunan Linkit-paneelissa.' );

			var body = el( 'div', { className: 'mh-landing__inner' },
				el( 'div', innerProps ),
				el( 'nav', { className: 'mh-landing-nearby__nav' }, list ),
				props.attributes.hub
					? el( 'p', { className: 'mh-landing-nearby__hub' },
						el( 'span', { className: 'mh-landing__button mh-landing__button--primary' },
							props.attributes.hubLabel || 'Alueen toimipiste' ) )
					: null
			);

			return el( Fragment, {},
				el( Panel, props ),
				window.mhLandingBackground.wrapSection( props, 'mh-landing-section mh-landing-nearby', body )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element );
