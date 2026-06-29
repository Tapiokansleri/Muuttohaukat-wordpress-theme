<?php
/**
 * Hello bar announcement banner.
 *
 * Injects a customisable notification bar right after the opening
 * <body> tag via output buffering. Configurable from Appearance → Teeman asetukset → Ilmoituspalkki.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Hellobar;

/** Start output buffering on the front-end to inject the bar. */
add_action('template_redirect', function () {
  if (!is_admin()) {
    ob_start('\\Muuttohaukat\\Hellobar\\injectBar');
  }
});

/**
 * Output buffer callback — injects the hello bar HTML after <body>.
 *
 * @param string $buffer Full page HTML.
 * @return string Modified HTML with hello bar injected.
 */
function injectBar($buffer) {
  $options = get_option('kansleri_hello_bar_options');

  if (empty($options['text'])) {
    return $buffer;
  }

  $text       = esc_html($options['text']);
  $bg_color   = !empty($options['bg_color']) ? sanitize_hex_color($options['bg_color']) : '#333';
  $text_color = !empty($options['text_color']) ? sanitize_hex_color($options['text_color']) : '#fff';
  $link       = !empty($options['link']) ? esc_url($options['link']) : '#';
  $custom_css = !empty($options['custom_css']) ? wp_strip_all_tags($options['custom_css']) : '';

  $html  = "\n<div id='kansleri-hello-bar' style='--hello-bar-bg: {$bg_color}; --hello-bar-color: {$text_color};'>\n";
  $html .= "    <a href='{$link}'>{$text}</a>\n";
  $html .= "</div>\n";

  $base_css = <<<CSS
#kansleri-hello-bar {
  position: static; top: 0; width: 100%; z-index: 9999;
  background-color: var(--hello-bar-bg, #333);
  color: var(--hello-bar-color, #fff);
  text-align: center;
  padding: 0.875rem 2rem;
  font-size: 0.9375rem;
  line-height: 1.4;
}
#kansleri-hello-bar a { color: inherit; text-decoration: none; }
@media (min-width: 1280px) and (max-width: 1535.98px) {
  #kansleri-hello-bar { font-size: 0.875rem; padding: 0.8125rem 1.75rem; }
}
@media (min-width: 1024px) and (max-width: 1279.98px) {
  #kansleri-hello-bar { font-size: 0.8125rem; padding: 0.75rem 1.5rem; }
}
@media (min-width: 768px) and (max-width: 1023.98px) {
  #kansleri-hello-bar { font-size: 0.8125rem; padding: 0.75rem 1.25rem; line-height: 1.35; }
}
@media (min-width: 640px) and (max-width: 767.98px) {
  #kansleri-hello-bar { font-size: 0.75rem; padding: 0.6875rem 1rem; line-height: 1.35; }
}
@media (max-width: 639.98px) {
  #kansleri-hello-bar { font-size: 0.6875rem; padding: 0.625rem 1rem; line-height: 1.3; }
}
CSS;

  $html .= "<style>{$base_css}\n{$custom_css}</style>\n";

  $buffer = preg_replace('/<body([^>]*)>/i', '<body$1>' . $html, $buffer, 1);

  return $buffer;
}

