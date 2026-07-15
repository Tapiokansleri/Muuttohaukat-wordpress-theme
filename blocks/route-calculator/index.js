( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var el = element.createElement;

	// Dynamic block — actual UI is rendered server-side by render.php.
	// The editor preview is just a placeholder so editors know the block
	// is there and saving works.
	registerBlockType( 'muuttohaukat/route-calculator', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'mh-landing-route-form-placeholder' } );
			return el( 'div', blockProps,
				el( 'div', {
					style: {
						padding: '32px 24px',
						background: 'rgba(0, 0, 0, 0.04)',
						border: '1px dashed rgba(0, 0, 0, 0.2)',
						textAlign: 'center',
						fontStyle: 'italic',
						color: '#555'
					}
				}, 'Reittilaskuri — renderöidään etusivulla' )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
