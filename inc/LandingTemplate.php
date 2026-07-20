<?php
/**
 * Register native Gutenberg blocks under blocks/<slug>/block.json
 * (route-calculator + the seven landing-* section blocks).
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

add_action('init', function () {
  foreach (glob(get_template_directory() . '/blocks/*/block.json') as $manifest) {
    register_block_type(dirname($manifest));
  }
}, 20);
