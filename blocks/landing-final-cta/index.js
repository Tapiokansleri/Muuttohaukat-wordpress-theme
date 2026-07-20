( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner mh-landing-final' }, [
			[ 'core/heading', { level: 2, content: 'Aloita muutto pyytämällä tarjous muuttopalvelusta Paimioon' } ],
			[ 'core/paragraph', { content: 'Kun valitset Muuttohaukat Paimion muuttopalveluksi, sinun ei tarvitse miettiä kuka kantaa tai milloin auto tulee. Saat kaiken yhdellä yhteydenotolla, selkeällä hinnalla ja sovitulla aikataululla.' } ],
			[ 'muuttohaukat/buttons', {
				button1Text: 'Pyydä tarjous Paimioon',
				button1Url: '#tarjous',
				button1Color: 'yellow',
				button2Text: 'Tilaa heti: tilaamuutto.fi',
				button2Url: 'https://tilaamuutto.fi',
				button2Target: '_blank',
				button2Color: 'black'
			} ]
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-final-cta', {
		edit: function ( props ) {
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return window.mhLandingBackground.wrapSection( props, 'mh-landing-section mh-landing-section--final', el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
