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

	var FEATURE = function ( num, title, body, img ) {
		return [ 'core/group', { className: 'mh-landing-feature' }, [
			[ 'core/image', { className: 'mh-landing-feature__media', url: img, alt: title } ],
			[ 'core/group', { className: 'mh-landing-feature__body' }, [
				[ 'core/paragraph', { className: 'mh-landing-feature__num', content: num } ],
				[ 'core/heading', { level: 4, content: title } ],
				[ 'core/paragraph', { content: body } ]
			] ]
		] ];
	};

	var CTA_ROW = [ 'muuttohaukat/buttons', {} ];

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner' }, [
			[ 'core/group', { className: 'mh-landing-sec-head' }, [
				[ 'core/heading', { level: 2, content: 'Haluatko tarjouksen asiantuntijalta?' } ],
				[ 'core/paragraph', { content: 'Saat tarjouksen alle 60 m² asuntosi muutosta välittömästi! Suuremmat asunnot viimeistään seuraavana arkipäivänä. Voit myös tilata alle 80 m² kokoisen asunnon muuton suoraan osoitteessa: tilaamuutto.fi' } ],
				[ 'core/paragraph', { content: 'Olet tekemässä erinomaisen valinnan. Valitse sinulle parhaiten sopiva vaihtoehto alta!' } ]
			] ],
			[ 'core/group', { className: 'mh-landing-features' }, [
				FEATURE( '01', 'Meiltä myös yritysmuutot', 'Tarjoamme luotettavan muuttopalvelun myös yritysasiakkaille. Ota yhteyttä ja kerro tarpeistasi.', themeRoot + '/assets/img/muuttohaukka_01.b9243e77.svg' ),
				FEATURE( '02', 'Tilaa muutto heti tilaamuutto.fi-palvelusta', 'TilaaMuutto.fi – palvelun kautta voit tilata muuttopalvelun mihin kellonaikaan tahansa. Palvelun kautta voi tilata muuttopalvelun alle 80 m² kokoisen asunnon muuttoon. Palvelusta saat tilattua myös muuttolaatikot. Muuttopalvelu myös osamaksulla. Palvelu on tarkoitettu kuluttaja-asiakkaille.', themeRoot + '/assets/img/muuttohaukka_02.c3b3b03f.svg' )
			] ],
			CTA_ROW
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-service', {
		edit: function ( props ) {
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return window.mhLandingBackground.wrapSection( props, 'mh-landing-section', el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
