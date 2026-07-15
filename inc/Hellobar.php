<?php
/**
 * Hello bar announcement banner.
 *
 * Renders a customisable notification bar through wp_body_open.
 * Configurable from Appearance → Teeman asetukset → Ilmoituspalkki.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Hellobar;

add_action('wp_body_open', __NAMESPACE__ . '\\renderBar');

/**
 * Render the hello bar immediately after the opening body tag.
 */
function renderBar() {
  $options = get_option('kansleri_hello_bar_options');

  if (empty($options['text'])) {
    return;
  }

  $text       = esc_html($options['text']);
  $bg_color   = !empty($options['bg_color']) ? sanitize_hex_color($options['bg_color']) : '#333';
  $text_color = !empty($options['text_color']) ? sanitize_hex_color($options['text_color']) : '#fff';
  $link       = !empty($options['link']) ? esc_url($options['link']) : '#';
  $custom_css = !empty($options['custom_css']) ? wp_strip_all_tags($options['custom_css']) : '';
  $bg_color   = $bg_color ?: '#333';
  $text_color = $text_color ?: '#fff';

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

  echo "\n<div id=\"kansleri-hello-bar\" style=\"--hello-bar-bg: " . esc_attr($bg_color) . "; --hello-bar-color: " . esc_attr($text_color) . ";\">\n";
  echo '    <a href="' . esc_url($link) . '">' . $text . "</a>\n";
  echo "</div>\n";
  echo "<style>{$base_css}\n{$custom_css}</style>\n";
}

