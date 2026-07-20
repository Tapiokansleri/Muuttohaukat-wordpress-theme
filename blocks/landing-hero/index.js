( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;
	var scriptUrl = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
	var themeRoot = scriptUrl
		? new URL( '../../', scriptUrl ).href.replace( /\/$/, '' )
		: '/wp-content/themes/Muuttohaukat';
	var heroImage = themeRoot + '/assets/img/haukka.png';

	var TRUST_ITEM = function ( icon, label, caption ) {
		return [ 'muuttohaukat/icon-item', {
			variant: 'trust',
			icon: icon,
			title: label,
			description: caption
		} ];
	};

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner mh-landing-hero__inner' }, [
			[ 'core/group', { className: 'mh-landing-hero__content' }, [
				[ 'core/paragraph', { className: 'mh-landing-eyebrow', content: 'Muuttohaukat · vuodesta 1992' } ],
				[ 'core/heading', { level: 1, className: 'mh-landing-hero__title', content: 'Muuttopalvelut' } ],
				[ 'core/paragraph', { className: 'mh-landing-hero__lead', content: 'Meiltä saat muuttopalvelut Paimion alueella. Asiakaslupauksemme „Muutamme käsityksesi muuttamisesta®” rakentuu ystävällisen hyväntuulisen palvelun, jatkuvan parantamisen ja asiakasymmärryksen sekä palveluun tyytyväisen asiakkaan kokemuksen myötä. Kun tarvitset luotettavan muuttopalveluyhtiön kumppaniksesi muuton toteutuksessa Paimion seudulla, ota yhteyttä ja pyydä tarjous alta.' } ],
				[ 'muuttohaukat/buttons', {} ],
				[ 'core/group', { className: 'mh-landing-hero__trust' }, [
					TRUST_ITEM( 'fa-solid fa-medal', '30+ v', 'kokemusta muuttopalveluista' ),
					TRUST_ITEM( 'fa-solid fa-location-dot', 'Paikallinen', 'muuttopalvelu alueellasi' ),
					TRUST_ITEM( 'fa-solid fa-truck', 'Koko Suomi', 'palvelualue muuttopalvelulle' )
				] ]
			] ],
			[ 'core/image', {
				className: 'mh-landing-hero__media',
				url: heroImage,
				alt: 'Muuttohaukat — muuttopalvelut'
			} ]
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-hero', {
		edit: function ( props ) {
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return window.mhLandingBackground.wrapSection( props, 'mh-landing-hero', el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
