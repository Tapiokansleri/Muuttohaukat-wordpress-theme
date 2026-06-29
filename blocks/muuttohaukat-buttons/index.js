( function ( blocks, blockEditor, element, components, i18n ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var COLOR_OPTIONS = [
		{ label: __( 'Musta', 'muuttohaukat' ),     value: 'black' },
		{ label: __( 'Keltainen', 'muuttohaukat' ), value: 'yellow' }
	];

	var TARGET_OPTIONS = [
		{ label: __( 'Sama välilehti', 'muuttohaukat' ), value: '_self' },
		{ label: __( 'Uusi välilehti', 'muuttohaukat' ), value: '_blank' }
	];

	function buttonClass( color ) {
		return 'mh-buttons__btn mh-buttons__btn--' + ( color === 'black' ? 'black' : 'yellow' );
	}

	registerBlockType( 'muuttohaukat/buttons', {
		edit: function ( props ) {
			var attrs = props.attributes;
			var set = props.setAttributes;
			var blockProps = useBlockProps( { className: 'mh-buttons-wrap' } );

			function controlsFor( num ) {
				var prefix = 'button' + num;
				return [
					el( TextControl, {
						key: prefix + 'Text',
						label: __( 'Teksti', 'muuttohaukat' ),
						value: attrs[ prefix + 'Text' ],
						onChange: function ( v ) { var u = {}; u[ prefix + 'Text' ] = v; set( u ); }
					} ),
					el( TextControl, {
						key: prefix + 'Url',
						label: __( 'URL', 'muuttohaukat' ),
						value: attrs[ prefix + 'Url' ],
						onChange: function ( v ) { var u = {}; u[ prefix + 'Url' ] = v; set( u ); }
					} ),
					el( SelectControl, {
						key: prefix + 'Target',
						label: __( 'Linkin kohde', 'muuttohaukat' ),
						value: attrs[ prefix + 'Target' ],
						options: TARGET_OPTIONS,
						onChange: function ( v ) { var u = {}; u[ prefix + 'Target' ] = v; set( u ); }
					} ),
					el( SelectControl, {
						key: prefix + 'Color',
						label: __( 'Väri', 'muuttohaukat' ),
						value: attrs[ prefix + 'Color' ],
						options: COLOR_OPTIONS,
						onChange: function ( v ) { var u = {}; u[ prefix + 'Color' ] = v; set( u ); }
					} )
				];
			}

			function previewBtn( num ) {
				var text = attrs[ 'button' + num + 'Text' ];
				if ( ! text ) return null;
				return el( 'span', {
					key: num,
					className: buttonClass( attrs[ 'button' + num + 'Color' ] )
				}, text );
			}

			return el( Fragment, {},
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Painike 1', 'muuttohaukat' ), initialOpen: true }, controlsFor( 1 ) ),
					el( PanelBody, {
						title: __( 'Painike 2 (valinnainen)', 'muuttohaukat' ),
						initialOpen: false
					}, controlsFor( 2 ) )
				),
				el( 'div', blockProps, previewBtn( 1 ), previewBtn( 2 ) )
			);
		},

		save: function ( props ) {
			var attrs = props.attributes;
			var blockProps = useBlockProps.save( { className: 'mh-buttons-wrap' } );

			function btn( num ) {
				var text = attrs[ 'button' + num + 'Text' ];
				if ( ! text ) return null;
				var url = attrs[ 'button' + num + 'Url' ] || '#';
				var target = attrs[ 'button' + num + 'Target' ] === '_blank' ? '_blank' : '_self';
				var rel = target === '_blank' ? 'noopener noreferrer' : null;
				return el( 'a', {
					key: num,
					className: buttonClass( attrs[ 'button' + num + 'Color' ] ),
					href: url,
					target: target,
					rel: rel
				}, text );
			}

			return el( 'div', blockProps, btn( 1 ), btn( 2 ) );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.i18n );
