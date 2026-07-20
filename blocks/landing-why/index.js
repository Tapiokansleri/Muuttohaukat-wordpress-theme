( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	var ITEM = function ( icon, title, body ) {
		return [ 'muuttohaukat/icon-item', {
			variant: 'why',
			icon: icon,
			title: title,
			description: body
		} ];
	};

	var CTA_ROW = [ 'muuttohaukat/buttons', {} ];

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner' }, [
			[ 'core/group', { className: 'mh-landing-sec-head' }, [
				[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Miksi Muuttohaukat?' } ],
				[ 'core/heading', { level: 2, content: 'Tehokkaita — ja ihmisiä ihmisten kanssa Paimiossa' } ]
			] ],
			[ 'core/group', { className: 'mh-landing-why' }, [
				ITEM( 'fa-solid fa-award', 'Yli 30 vuoden kokemus', 'Olemme tehneet kymmeniätuhansia muuttoja Paimiossa, Varsinais-Suomessa ja ympäri Suomen vuodesta 1992.' ),
				ITEM( 'fa-solid fa-shield-halved', 'Omaisuutesi hyvissä käsissä', 'Suojaamme, kuljetamme varovasti ja vakuutamme kaikki muuttokuljetukset Paimion alueella.' ),
				ITEM( 'fa-solid fa-clock', 'Pidämme kiinni aikatauluista', 'Lupaamme vain sen, minkä pystymme pitämään — sovittu aika Paimion muuttopäivänä pitää.' ),
				ITEM( 'fa-solid fa-receipt', 'Kaikki yhdellä tarjouksella', 'Auto, kantajat ja laatikot Paimion muuttoosi hoituvat kerralla — ei erillistä soittelua eri tahoille.' ),
				ITEM( 'fa-solid fa-location-dot', 'Paikallinen tuntemus Paimiossa', 'Tunnemme Paimion kadut, kohteet ja aikataulut — osaamme ennakoida ongelmat ennen kuin ne syntyvät.' ),
				ITEM( 'fa-solid fa-thumbs-up', 'Luotettava muuttokumppani', 'Paimiolaiset asiakkaat palaavat meille kerta toisensa jälkeen. Muuttopalvelu Paimio, johon voi luottaa.' )
			] ],
			CTA_ROW
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-why', {
		edit: function ( props ) {
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return window.mhLandingBackground.wrapSection( props, 'mh-landing-section', el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
