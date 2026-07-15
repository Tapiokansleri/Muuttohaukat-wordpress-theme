/**
 * Load Leadoo in-page scripts declared by ACF Leadoo blocks.
 */
(function () {
  'use strict';

  function loadLeadooBlocks() {
    var roots = document.querySelectorAll('.leadoo-root[data-script]');

    roots.forEach(function (root) {
      if (root.getAttribute('data-loaded') === 'true') {
        return;
      }

      var src = root.getAttribute('data-script');
      if (!src) {
        return;
      }

      root.setAttribute('data-loaded', 'true');

      var script = document.createElement('script');
      script.src = src;
      script.async = true;
      root.appendChild(script);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadLeadooBlocks);
  } else {
    loadLeadooBlocks();
  }
})();
