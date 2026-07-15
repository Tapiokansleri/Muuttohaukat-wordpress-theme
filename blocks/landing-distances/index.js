( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	var CTA_ROW = [ 'muuttohaukat/buttons', {} ];

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner' }, [
			[ 'core/group', { className: 'mh-landing-sec-head' }, [
				[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Paimion muuton ajoajat ja välimatkat' } ],
				[ 'core/heading', { level: 2, content: 'Muutto Paimiosta minne tahansa Suomessa' } ],
				[ 'core/paragraph', { content: 'Suunnitteletko muuttoa Paimiosta toiseen kaupunkiin? Tässä yleisimmät reitit. Muuttopalvelu Paimio toimittaa muuttosi mihin tahansa — kysy tarjous omasta reitistäsi.' } ]
			] ],
			[ 'muuttohaukat/route-calculator', {} ],
			CTA_ROW
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-distances', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'mh-landing-section' } );
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return el( 'section', blockProps, el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
