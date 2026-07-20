( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	var CASE = function ( tag, title, body, quote ) {
		return [ 'core/group', { tagName: 'article', className: 'mh-landing-case' }, [
			[ 'core/paragraph', { className: 'mh-landing-case__tag', content: tag } ],
			[ 'core/heading', { level: 3, content: title } ],
			[ 'core/paragraph', { content: body } ],
			[ 'core/paragraph', { className: 'mh-landing-case__stars', content: '★★★★★' } ],
			[ 'core/paragraph', { className: 'mh-landing-case__quote', content: quote } ]
		] ];
	};

	var CTA_ROW = [ 'muuttohaukat/buttons', {} ];

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner' }, [
			[ 'core/group', { className: 'mh-landing-sec-head' }, [
				[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Esimerkkimuuttoja Paimiossa' } ],
				[ 'core/heading', { level: 2, content: 'Näin muuttopalvelu Paimio toimii käytännössä' } ],
				[ 'core/paragraph', { content: 'Pari esimerkkiä siitä, millaisia muuttoja olemme Paimion alueella hoitaneet.' } ]
			] ],
			[ 'core/group', { className: 'mh-landing-cases' }, [
				CASE( 'Muutto Paimion sisällä', 'Kerrostalokaksio, n. 40 m²', 'Vistalla kohdeosoitteessa muuttoautoa ei saanut oven eteen, joten kantomatka oli tavallista pidempi. Hoidimme Paimion muuton kahdella muuttajalla ja toimitimme muuttolaatikot etukäteen.', '"Työ sujui nopeasti ja muuttopalvelu Paimiossa toimi kaikinpuolin hyvin."' ),
				CASE( 'Muutto Paimiosta Turkuun', 'Yhden huoneen tavarat, n. 15 m²', 'Ei isoja huonekaluja. Yksi muuttaja toimi sekä kantajana että kuljettajana, ja muutto Paimiosta Turkuun valmistui alle neljässä tunnissa.', '"Saapui jopa hieman etuajassa. Suosittelen lämpimästi Paimion muuttopalvelua!"' )
			] ],
			CTA_ROW,
			[ 'core/shortcode', { text: '[brb_collection id=6505]' } ]
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-cases', {
		edit: function ( props ) {
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return window.mhLandingBackground.wrapSection( props, 'mh-landing-section', el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
