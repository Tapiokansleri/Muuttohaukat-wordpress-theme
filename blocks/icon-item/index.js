( function ( blocks, blockEditor, element, components, i18n ) {
	var registerBlockType = blocks.registerBlockType;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var RichText = blockEditor.RichText;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var ICONS = [
		{ label: __( 'Mitali', 'muuttohaukat' ), class: 'fa-solid fa-medal' },
		{ label: __( 'Palkinto', 'muuttohaukat' ), class: 'fa-solid fa-award' },
		{ label: __( 'Sijainti', 'muuttohaukat' ), class: 'fa-solid fa-location-dot' },
		{ label: __( 'Kuorma-auto', 'muuttohaukat' ), class: 'fa-solid fa-truck' },
		{ label: __( 'Kilpi', 'muuttohaukat' ), class: 'fa-solid fa-shield-halved' },
		{ label: __( 'Kello', 'muuttohaukat' ), class: 'fa-solid fa-clock' },
		{ label: __( 'Kuitti', 'muuttohaukat' ), class: 'fa-solid fa-receipt' },
		{ label: __( 'Peukku', 'muuttohaukat' ), class: 'fa-solid fa-thumbs-up' },
		{ label: __( 'Tähti', 'muuttohaukat' ), class: 'fa-solid fa-star' },
		{ label: __( 'Sydän', 'muuttohaukat' ), class: 'fa-solid fa-heart' },
		{ label: __( 'Ruutu', 'muuttohaukat' ), class: 'fa-solid fa-box' },
		{ label: __( 'Koti', 'muuttohaukat' ), class: 'fa-solid fa-house' },
		{ label: __( 'Puhelin', 'muuttohaukat' ), class: 'fa-solid fa-phone' },
		{ label: __( 'Sähköposti', 'muuttohaukat' ), class: 'fa-solid fa-envelope' },
		{ label: __( 'Kädet', 'muuttohaukat' ), class: 'fa-solid fa-handshake' },
		{ label: __( 'Henkilöt', 'muuttohaukat' ), class: 'fa-solid fa-users' },
		{ label: __( 'Ruuviavain', 'muuttohaukat' ), class: 'fa-solid fa-wrench' },
		{ label: __( 'Euro', 'muuttohaukat' ), class: 'fa-solid fa-euro-sign' },
		{ label: __( 'Nopeus', 'muuttohaukat' ), class: 'fa-solid fa-gauge-high' },
		{ label: __( 'Kalenteri', 'muuttohaukat' ), class: 'fa-solid fa-calendar-check' },
		{ label: __( 'Tarkistus', 'muuttohaukat' ), class: 'fa-solid fa-circle-check' },
		{ label: __( 'Lukko', 'muuttohaukat' ), class: 'fa-solid fa-lock' },
		{ label: __( 'Kartta', 'muuttohaukat' ), class: 'fa-solid fa-map-location-dot' },
		{ label: __( 'Paketti', 'muuttohaukat' ), class: 'fa-solid fa-boxes-stacked' }
	];

	function IconPicker( props ) {
		var value = props.value || ICONS[ 0 ].class;
		var onChange = props.onChange;

		return el(
			'div',
			{
				className: 'mh-icon-picker',
				role: 'listbox',
				'aria-label': __( 'Valitse kuvake', 'muuttohaukat' )
			},
			ICONS.map( function ( icon ) {
				var selected = value === icon.class;
				return el(
					'button',
					{
						type: 'button',
						key: icon.class,
						role: 'option',
						'aria-selected': selected,
						className: 'mh-icon-picker__option' + ( selected ? ' is-selected' : '' ),
						title: icon.label,
						onClick: function () {
							onChange( icon.class );
						}
					},
					el( 'i', { className: icon.class + ' mh-icon-picker__icon', 'aria-hidden': true } ),
					el( 'span', { className: 'mh-icon-picker__label' }, icon.label )
				);
			} )
		);
	}

	var VARIANT_OPTIONS = [
		{ label: __( 'Luottamusmerkki (hero)', 'muuttohaukat' ), value: 'trust' },
		{ label: __( 'Syy-kortti (miksi)', 'muuttohaukat' ), value: 'why' }
	];

	function iconEl( iconClass ) {
		return el( 'i', {
			className: 'mh-landing-icon ' + iconClass,
			'aria-hidden': true
		} );
	}

	registerBlockType( 'muuttohaukat/icon-item', {
		edit: function ( props ) {
			var attrs = props.attributes;
			var set = props.setAttributes;
			var isTrust = attrs.variant === 'trust';
			var blockProps = useBlockProps( {
				className: 'mh-icon-item mh-icon-item--' + attrs.variant
			} );

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Kuvake', 'muuttohaukat' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Tyyppi', 'muuttohaukat' ),
							value: attrs.variant,
							options: VARIANT_OPTIONS,
							onChange: function ( value ) {
								set( { variant: value } );
							}
						} ),
						el( IconPicker, {
							value: attrs.icon,
							onChange: function ( value ) {
								set( { icon: value } );
							}
						} )
					)
				),
				el(
					'div',
					blockProps,
					isTrust
						? [
							el(
								'p',
								{ className: 'mh-icon-item__label' },
								iconEl( attrs.icon ),
								el( RichText, {
									tagName: 'span',
									value: attrs.title,
									placeholder: __( 'Otsikko', 'muuttohaukat' ),
									allowedFormats: [],
									onChange: function ( value ) {
										set( { title: value } );
									}
								} )
							),
							el( RichText, {
								tagName: 'p',
								className: 'mh-icon-item__caption',
								value: attrs.description,
								placeholder: __( 'Kuvaus', 'muuttohaukat' ),
								allowedFormats: [],
								onChange: function ( value ) {
									set( { description: value } );
								}
							} )
						]
						: [
							el(
								'h4',
								{ className: 'mh-icon-item__title' },
								iconEl( attrs.icon ),
								el( RichText, {
									tagName: 'span',
									value: attrs.title,
									placeholder: __( 'Otsikko', 'muuttohaukat' ),
									allowedFormats: [],
									onChange: function ( value ) {
										set( { title: value } );
									}
								} )
							),
							el( RichText, {
								tagName: 'p',
								value: attrs.description,
								placeholder: __( 'Kuvaus', 'muuttohaukat' ),
								allowedFormats: [],
								onChange: function ( value ) {
									set( { description: value } );
								}
							} )
						]
				)
			);
		},

		save: function ( props ) {
			var attrs = props.attributes;
			var isTrust = attrs.variant === 'trust';
			var blockProps = useBlockProps.save( {
				className: 'mh-icon-item mh-icon-item--' + attrs.variant
			} );

			return el(
				'div',
				blockProps,
				isTrust
					? [
						el(
							'p',
							{ className: 'mh-icon-item__label' },
							iconEl( attrs.icon ),
							el( RichText.Content, {
								tagName: 'span',
								value: attrs.title
							} )
						),
						el( RichText.Content, {
							tagName: 'p',
							className: 'mh-icon-item__caption',
							value: attrs.description
						} )
					]
					: [
						el(
							'h4',
							{ className: 'mh-icon-item__title' },
							iconEl( attrs.icon ),
							el( RichText.Content, {
								tagName: 'span',
								value: attrs.title
							} )
						),
						el( RichText.Content, {
							tagName: 'p',
							value: attrs.description
						} )
					]
			);
		}
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.element,
	window.wp.components,
	window.wp.i18n
);
