( function ( blocks, blockEditor, element ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var el = element.createElement;

	// Trust item: icon comes from a CSS ::before rule keyed off the
	// modifier class — no core/html block, no inline FA markup. Lets the
	// editor preview show the same icon + label + caption as the frontend
	// because both contexts run the same landing.css.
	var TRUST_ITEM = function ( iconModifier, label, caption ) {
		return [ 'core/group', { className: 'mh-landing-hero__trust-item mh-landing-hero__trust-item--' + iconModifier }, [
			[ 'core/paragraph', { className: 'mh-landing-hero__trust-label', content: label } ],
			[ 'core/paragraph', { className: 'mh-landing-hero__trust-caption', content: caption } ]
		] ];
	};

	var TEMPLATE = [
		[ 'core/group', { className: 'mh-landing__inner mh-landing-hero__inner' }, [
			[ 'core/group', { className: 'mh-landing-hero__content' }, [
				[ 'core/paragraph', { className: 'mh-landing-eyebrow', content: 'Muuttopalvelu Paimio · vuodesta 1992' } ],
				[ 'core/heading', { level: 1, className: 'mh-landing-hero__title', content: 'Muuttopalvelu <em>Paimio</em>' } ],
				[ 'core/paragraph', { className: 'mh-landing-hero__lead', content: 'Muutto edessä Paimiossa? Muuttopalvelu Paimio hoitaa raskaan työn puolestasi — ammattitaidolla, sovitussa aikataulussa ja selkeällä hinnalla, koko Paimion alueella ja Varsinais-Suomessa.' } ],
				[ 'muuttohaukat/buttons', {} ],
				[ 'core/group', { className: 'mh-landing-hero__trust' }, [
					TRUST_ITEM( 'medal', '30+ v', 'kokemusta muuttopalveluista' ),
					TRUST_ITEM( 'location-dot', 'Paimio', 'paikallinen muuttopalvelu' ),
					TRUST_ITEM( 'truck', 'Koko Suomi', 'palvelualue muuttopalvelulle' )
				] ]
			] ],
			[ 'core/image', { className: 'mh-landing-hero__media' } ]
		] ]
	];

	registerBlockType( 'muuttohaukat/landing-hero', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'mh-landing-hero' } );
			var innerProps = useInnerBlocksProps( {}, { template: TEMPLATE, templateLock: 'all' } );
			return el( 'section', blockProps, el( 'div', innerProps ) );
		},
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
