/**
 * Editor sidebar panel: "Muuttohaukat Landing".
 *
 * Adds a button that appends the full 7-section landing layout to the end
 * of the post being edited. The serialized block markup is provided by PHP
 * via wp_localize_script as window.MhLandingInserter.markup.
 */
( function ( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editor || ! wp.element ) return;

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	var el = wp.element.createElement;
	var Button = wp.components.Button;
	var dispatch = wp.data.dispatch;
	var select = wp.data.select;
	var parse = wp.blocks.parse;
	var __ = wp.i18n.__;

	function insertLanding() {
		var data = window.MhLandingInserter || {};
		if ( ! data.markup ) {
			window.alert( __( 'Landing markup missing.', 'muuttohaukat' ) );
			return;
		}
		var blocks = parse( data.markup );
		if ( ! blocks || ! blocks.length ) return;

		var blockEditor = select( 'core/block-editor' );
		var existing = blockEditor.getBlocks();
		dispatch( 'core/block-editor' ).insertBlocks( blocks, existing.length, undefined, false );
	}

	function Panel() {
		return el(
			PluginDocumentSettingPanel,
			{
				name: 'mh-landing-inserter',
				title: __( 'Muuttohaukat Landing', 'muuttohaukat' ),
				className: 'mh-landing-inserter'
			},
			el(
				'p',
				{ style: { marginTop: 0 } },
				__( 'Insert the full Muuttohaukat landing page layout (7 sections) at the end of this page.', 'muuttohaukat' )
			),
			el(
				Button,
				{ variant: 'primary', onClick: insertLanding },
				__( 'Insert landing layout', 'muuttohaukat' )
			)
		);
	}

	registerPlugin( 'mh-landing-inserter', { render: Panel, icon: 'layout' } );
} )( window.wp );
