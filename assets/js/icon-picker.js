( function ( element, i18n ) {
	var el = element.createElement;
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

	window.mhIconPicker = {
		ICONS: ICONS,
		IconPicker: IconPicker
	};
} )( window.wp.element, window.wp.i18n );
