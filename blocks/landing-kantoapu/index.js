( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	var CTA_ROW = [ 'muuttohaukat/buttons', {} ];

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner mh-landing-split' }, [
			[ 'core/group', {}, [
				[ 'core/group', { className: 'mh-landing-sec-head' }, [
					[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Kantoapu Paimion muuttoihin' } ],
					[ 'core/heading', { level: 2, content: 'Tarpeeksi käsipareja — säästät aikaa ja selän' } ]
				] ],
				[ 'core/paragraph', { content: 'Muutto Paimiossa sujuu huomattavasti kevyemmin, kun apuna on tarpeeksi käsipareja. Erityisesti isoissa muutoissa kannettavaa kertyy niin paljon, että ammattilaisten kantoapu maksaa itsensä takaisin.' } ],
				[ 'core/paragraph', { content: 'Muuttopalvelu Paimio räätälöi muuton aina tarpeidesi mukaan: voit tilata kokonaisvaltaisen muuttopalvelun kantajineen tai pelkän auton kuljettajineen ja hoitaa kantoavun itse.' } ],
				CTA_ROW
			] ],
			[ 'core/image', { className: 'mh-landing-split__media', alt: 'Kantoapu Paimion muuttoon' } ]
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-kantoapu', {
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
