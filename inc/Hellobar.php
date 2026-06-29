<?php
/**
 * Hello bar announcement banner.
 *
 * Injects a customisable notification bar right after the opening
 * <body> tag via output buffering. Configurable from Settings > Kansleri Hellobar.
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

/** Register the settings page under Settings. */
add_action('admin_menu', function () {
  add_options_page(
    esc_html__('Hellobar Settings', 'muuttohaukat'),
    esc_html__('Hellobar', 'muuttohaukat'),
    'manage_options',
    'kansleri-hello-bar',
    '\\Muuttohaukat\\Hellobar\\renderSettingsPage'
  );
});

/**
 * Render the settings page form.
 */
function renderSettingsPage() {
  if (!current_user_can('manage_options')) {
    return;
  }

  if (isset($_POST['kansleri_hello_bar_save'])) {
    check_admin_referer('kansleri_hello_bar_options_verify');

    $options = [
      'text'       => sanitize_text_field(wp_unslash($_POST['kansleri_hello_bar_text'] ?? '')),
      'bg_color'   => sanitize_hex_color(wp_unslash($_POST['kansleri_hello_bar_bg_color'] ?? '')),
      'text_color' => sanitize_hex_color(wp_unslash($_POST['kansleri_hello_bar_text_color'] ?? '')),
      'link'       => esc_url_raw(wp_unslash($_POST['kansleri_hello_bar_link'] ?? '')),
      'custom_css' => wp_strip_all_tags(wp_unslash($_POST['kansleri_hello_bar_custom_css'] ?? '')),
    ];
    update_option('kansleri_hello_bar_options', $options);
    echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved.', 'muuttohaukat') . '</p></div>';
  }

  $options = get_option('kansleri_hello_bar_options', []);
  ?>
  <div class="wrap">
    <h1><?= esc_html__('Hellobar Settings', 'muuttohaukat') ?></h1>
    <form method="post">
      <?php wp_nonce_field('kansleri_hello_bar_options_verify'); ?>
      <table class="form-table">
        <tr>
          <th><label for="kansleri_hello_bar_text"><?= esc_html__('Text', 'muuttohaukat') ?></label></th>
          <td><input type="text" id="kansleri_hello_bar_text" name="kansleri_hello_bar_text" value="<?= esc_attr($options['text'] ?? '') ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="kansleri_hello_bar_bg_color"><?= esc_html__('Background Color', 'muuttohaukat') ?></label></th>
          <td><input type="text" id="kansleri_hello_bar_bg_color" name="kansleri_hello_bar_bg_color" value="<?= esc_attr($options['bg_color'] ?? '#333') ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="kansleri_hello_bar_text_color"><?= esc_html__('Text Color', 'muuttohaukat') ?></label></th>
          <td><input type="text" id="kansleri_hello_bar_text_color" name="kansleri_hello_bar_text_color" value="<?= esc_attr($options['text_color'] ?? '#fff') ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="kansleri_hello_bar_link"><?= esc_html__('Link', 'muuttohaukat') ?></label></th>
          <td><input type="url" id="kansleri_hello_bar_link" name="kansleri_hello_bar_link" value="<?= esc_attr($options['link'] ?? '') ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="kansleri_hello_bar_custom_css"><?= esc_html__('Custom CSS', 'muuttohaukat') ?></label></th>
          <td><textarea id="kansleri_hello_bar_custom_css" name="kansleri_hello_bar_custom_css" rows="5" class="large-text"><?= esc_textarea($options['custom_css'] ?? '') ?></textarea></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}
