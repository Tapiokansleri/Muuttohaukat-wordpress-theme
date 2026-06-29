( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

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
				[ 'core/paragraph', { className: 'mh-landing-kicker', content: 'Muutot ja kuljetukset Paimiossa' } ],
				[ 'core/heading', { level: 2, content: 'Yksi yhteydenotto — koko muutto hoidossa Paimiossa' } ],
				[ 'core/paragraph', { content: 'Jätä tarjouspyyntö sitoumuksetta, niin saat selkeän tarjouksen ja paimiolaiset ammattilaiset hoitamaan muuttosi. Muuttopalvelu Paimio toimii Paimion sisällä, Varsinais-Suomessa ja koko Suomen alueella — pitkäkään muuttomatka ei ole ongelma.' } ]
			] ],
			[ 'core/group', { className: 'mh-landing-features' }, [
				FEATURE( '01', 'Auto ja ammattilaiset ajallaan', 'Muuttoauto ja osaavat muuttajat Paimion osoitteessasi sovittuna aikana — ei myöhästelyä, ei arvailua.', '/wp-content/themes/muuttohaukat/img/1.png' ),
				FEATURE( '02', 'Tavarat suojataan huolella', 'Suojaamme kalusteet ammattilaisten ottein ja kannamme tavarat sisään asti, halutuille paikoille Paimion alueella.', '/wp-content/themes/muuttohaukat/img/2.png' ),
				FEATURE( '03', 'Pakkaus ja purku halutessasi', 'Hoidamme tarvittaessa myös pakkaamisen, purkamisen sekä kalusteiden purun ja kasauksen Paimion muutossasi.', '/wp-content/themes/muuttohaukat/img/3.png' ),
				FEATURE( '04', 'Selkeä hinta, kaikki vakuutettu', 'Ei piilokuluja eikä epäselviä sopimuksia. Viemme roskat mennessämme ja vakuutamme jokaisen muuttokuljetuksen.', '' )
			] ],
			CTA_ROW
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-service', {
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
