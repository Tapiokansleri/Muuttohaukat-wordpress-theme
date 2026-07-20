/**
 * Shared background control for Muuttohaukat landing section blocks.
 */
( function ( wp ) {
	if ( ! wp || ! wp.element || ! wp.blockEditor || ! wp.components ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var RadioControl = wp.components.RadioControl;
	var __ = wp.i18n.__;

	var OPTIONS = [
		{ label: __( 'Valkoinen', 'muuttohaukat' ), value: 'white' },
		{ label: __( 'Keltainen', 'muuttohaukat' ), value: 'yellow' },
		{ label: __( 'Musta', 'muuttohaukat' ), value: 'black' }
	];

	function className( background ) {
		var bg = background || 'white';
		return 'mh-landing-section--bg-' + bg;
	}

	function Inspector( props ) {
		return el(
			InspectorControls,
			{},
			el(
				PanelBody,
				{ title: __( 'Tausta', 'muuttohaukat' ), initialOpen: false },
				el( RadioControl, {
					selected: props.attributes.background || 'white',
					options: OPTIONS,
					onChange: function ( value ) {
						props.setAttributes( { background: value } );
					}
				} )
			)
		);
	}

	function wrapSection( props, baseClass, children ) {
		var blockProps = useBlockProps( {
			className: baseClass + ' ' + className( props.attributes.background )
		} );

		return el(
			Fragment,
			{},
			el( Inspector, props ),
			el( 'section', blockProps, children )
		);
	}

	window.mhLandingBackground = {
		className: className,
		inspector: Inspector,
		wrapSection: wrapSection
	};
} )( window.wp );
