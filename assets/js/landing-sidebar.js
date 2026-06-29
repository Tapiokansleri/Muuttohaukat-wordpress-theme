/**
 * Muuttohaukat — Landing page sidebar button.
 *
 * Adds a "Add landing page blocks" button to the document sidebar of every
 * block editor (pages + custom post types). Clicking appends all 7
 * default landing sections to the end of the current content.
 *
 * No build step — plain ES5, wp.element.createElement.
 */
(function (wp) {
  if (!wp || !wp.plugins || !wp.element || !wp.components || !wp.data || !wp.blocks) {
    return;
  }

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var Button = wp.components.Button;
  var registerPlugin = wp.plugins.registerPlugin;

  // PluginDocumentSettingPanel moved between namespaces across WP versions.
  var PluginDocumentSettingPanel =
    (wp.editor && wp.editor.PluginDocumentSettingPanel) ||
    (wp.editPost && wp.editPost.PluginDocumentSettingPanel);

  if (!PluginDocumentSettingPanel) return;

  function appendLandingBlocks() {
    var data = window.mhLandingSidebar || {};
    var content = data.content || '';
    if (!content) {
      window.alert(data.strings && data.strings.unavailable || 'Landing content unavailable.');
      return;
    }

    var parsed = wp.blocks.parse(content);
    if (!parsed || !parsed.length) {
      window.alert(data.strings && data.strings.empty || 'No blocks to insert.');
      return;
    }

    var blockOrder = wp.data.select('core/block-editor').getBlockOrder();
    var insertAt = blockOrder.length;
    wp.data.dispatch('core/block-editor').insertBlocks(parsed, insertAt);
  }

  function PanelContent() {
    var data = window.mhLandingSidebar || {};
    var strings = data.strings || {};

    return el(
      Fragment,
      null,
      el(
        'p',
        { style: { margin: '0 0 12px 0' } },
        strings.description || 'Insert the full Muuttohaukat landing page layout at the end of this page.'
      ),
      el(
        Button,
        {
          variant: 'primary',
          isPrimary: true,
          onClick: appendLandingBlocks
        },
        strings.button || 'Add landing page blocks'
      )
    );
  }

  function MhLandingSidebar() {
    var data = window.mhLandingSidebar || {};
    var strings = data.strings || {};

    return el(
      PluginDocumentSettingPanel,
      {
        name: 'mh-landing-sidebar',
        title: strings.panelTitle || 'Muuttohaukat Landing',
        className: 'mh-landing-sidebar-panel'
      },
      el(PanelContent, null)
    );
  }

  registerPlugin('mh-landing-sidebar', { render: MhLandingSidebar });
})(window.wp);
