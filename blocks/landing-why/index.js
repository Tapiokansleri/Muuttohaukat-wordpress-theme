( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	// Why item: icon comes from a CSS ::before rule on the title, keyed off
	// the modifier class on the parent group. Keeps the heading content pure
	// text (no inline HTML to sanitize) so the editor preview matches the
	// frontend rendering.
	var ITEM = function ( iconModifier, title, body ) {
		return [ 'core/group', { className: 'mh-landing-why__item mh-landing-why__item--' + iconModifier }, [
			[ 'core/heading', { level: 4, className: 'mh-landing-why__title', content: title } ],
			[ 'core/paragraph', { content: body } ]
		] ];
	};

	var CTA_ROW = [ 'muuttohaukat/buttons', {} ];

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner' }, [
			[ 'core/group', { className: 'mh-landing-sec-head' }, [
				[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Miksi Muuttohaukat?' } ],
				[ 'core/heading', { level: 2, content: 'Tehokkaita — ja ihmisiä ihmisten kanssa Paimiossa' } ]
			] ],
			[ 'core/group', { className: 'mh-landing-why' }, [
				ITEM( 'award', 'Yli 30 vuoden kokemus', 'Olemme tehneet kymmeniätuhansia muuttoja Paimiossa, Varsinais-Suomessa ja ympäri Suomen vuodesta 1992.' ),
				ITEM( 'shield-halved', 'Omaisuutesi hyvissä käsissä', 'Suojaamme, kuljetamme varovasti ja vakuutamme kaikki muuttokuljetukset Paimion alueella.' ),
				ITEM( 'clock', 'Pidämme kiinni aikatauluista', 'Lupaamme vain sen, minkä pystymme pitämään — sovittu aika Paimion muuttopäivänä pitää.' ),
				ITEM( 'receipt', 'Kaikki yhdellä tarjouksella', 'Auto, kantajat ja laatikot Paimion muuttoosi hoituvat kerralla — ei erillistä soittelua eri tahoille.' ),
				ITEM( 'location-dot', 'Paikallinen tuntemus Paimiossa', 'Tunnemme Paimion kadut, kohteet ja aikataulut — osaamme ennakoida ongelmat ennen kuin ne syntyvät.' ),
				ITEM( 'thumbs-up', 'Luotettava muuttokumppani', 'Paimiolaiset asiakkaat palaavat meille kerta toisensa jälkeen. Muuttopalvelu Paimio, johon voi luottaa.' )
			] ],
			CTA_ROW
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-why', {
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
