( function ( blocks, blockEditor, components, element, serverSideRender ) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;
	var ServerSideRender = serverSideRender;
	var groups = window.mhStaffGroups || {};

	var options = [ { label: '— Valitse ryhmä —', value: '' } ].concat( Object.keys( groups ).map( function ( slug ) {
		return { label: groups[ slug ], value: slug };
	} ) );

	blocks.registerBlockType( 'muuttohaukat/henkilosto', {
		edit: function ( props ) {
			var a = props.attributes;
			var blockProps = useBlockProps();
			return el( Fragment, {},
				el( InspectorControls, {},
					el( PanelBody, { title: 'Henkilöt', initialOpen: true },
						el( SelectControl, {
							label: 'Ryhmä',
							help: 'Näyttää ryhmän henkilöt siinä järjestyksessä kuin kohdassa Henkilöt.',
							value: a.ryhma,
							options: options,
							onChange: function ( v ) { props.setAttributes( { ryhma: v } ); }
						} ),
						el( TextControl, {
							label: 'Tai nimet',
							help: 'Pilkulla eroteltuina, esim. Jani Vivolin, Jari Lindell. Ohittaa ryhmän.',
							value: a.nimet,
							onChange: function ( v ) { props.setAttributes( { nimet: v } ); }
						} ),
						el( RangeControl, {
							label: 'Palstoja',
							min: 1, max: 3,
							value: a.sarakkeet,
							onChange: function ( v ) { props.setAttributes( { sarakkeet: v } ); }
						} )
					)
				),
				el( 'div', blockProps,
					( a.ryhma || a.nimet )
						? el( ServerSideRender, { block: 'muuttohaukat/henkilosto', attributes: a } )
						: el( 'p', {}, 'Valitse ryhmä tai kirjoita nimet sivupalkissa.' )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.serverSideRender );
