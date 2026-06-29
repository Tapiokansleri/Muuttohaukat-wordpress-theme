<?php
/**
 * Floating CTA banner.
 *
 * Displays a fixed-position banner with two CTA buttons.
 * Configurable from Settings > Floating CTA Settings.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\FloatingCta;

/** Enqueue the floating CTA styles using wp_add_inline_style. */
add_action('wp_enqueue_scripts', function () {
  $css = '
    .kansleri-floating-cta-banner {
      display: block;
      position: fixed;
      bottom: 20px;
      left: 20px;
      z-index: 999;
      background: none;
      padding: 10px;
      border-radius: 0;
    }
  ';

  $onlyMobile = get_option('kansleri_floating_cta_only_on_mobile', false);
  if ($onlyMobile) {
    $css .= '
      @media (min-width: 769px) {
        .kansleri-floating-cta-banner { display: none; }
      }
    ';
  }

  wp_register_style('muuttohaukat-floating-cta', false);
  wp_enqueue_style('muuttohaukat-floating-cta');
  wp_add_inline_style('muuttohaukat-floating-cta', $css);
});

/** Render the floating CTA in the footer based on location settings. */
add_action('wp_footer', function () {
  $location = get_option('kansleri_floating_cta_location', 'everywhere');
  $should_display = false;

  switch ($location) {
    case 'everywhere':
      $should_display = true;
      break;
    case 'home':
      $should_display = is_front_page() || is_home();
      break;
    case 'pages':
      $should_display = is_page();
      break;
    case 'posts':
      $should_display = is_single() && !is_attachment();
      break;
  }

  if (!$should_display) {
    return;
  }

  $link        = get_option('kansleri_floating_cta_link', '');
  $text        = get_option('kansleri_floating_cta_text', '');
  $secondLink  = get_option('kansleri_floating_cta_second_link', '');
  $secondText  = get_option('kansleri_floating_cta_second_text', '');

  if (empty($text) && empty($secondText)) {
    return;
  }
  ?>
  <div class="kansleri-floating-cta-banner">
    <?php if (!empty($text)) : ?>
    <a href="<?= esc_url($link) ?>" class="btn btn-primary mr-2">
      <?= esc_html($text) ?>
    </a>
    <?php endif; ?>
    <?php if (!empty($secondText)) : ?>
    <a href="<?= esc_url($secondLink) ?>" class="btn btn-secondary">
      <?= esc_html($secondText) ?>
    </a>
    <?php endif; ?>
  </div>
  <?php
});

/** Register settings (only registers, does not call add_option on every load). */
add_action('admin_init', function () {
  register_setting('kansleri_floating_cta_options_group', 'kansleri_floating_cta_link', ['sanitize_callback' => 'esc_url_raw']);
  register_setting('kansleri_floating_cta_options_group', 'kansleri_floating_cta_text', ['sanitize_callback' => 'sanitize_text_field']);
  register_setting('kansleri_floating_cta_options_group', 'kansleri_floating_cta_second_link', ['sanitize_callback' => 'esc_url_raw']);
  register_setting('kansleri_floating_cta_options_group', 'kansleri_floating_cta_second_text', ['sanitize_callback' => 'sanitize_text_field']);
  register_setting('kansleri_floating_cta_options_group', 'kansleri_floating_cta_location', ['sanitize_callback' => 'sanitize_text_field']);
  register_setting('kansleri_floating_cta_options_group', 'kansleri_floating_cta_only_on_mobile', ['sanitize_callback' => 'absint']);
});

/** Register the settings page. */
add_action('admin_menu', function () {
  add_options_page(
    esc_html__('Floating CTA Settings', 'muuttohaukat'),
    esc_html__('Floating CTA', 'muuttohaukat'),
    'manage_options',
    'kansleri_floating_cta',
    '\\Muuttohaukat\\FloatingCta\\renderSettingsPage'
  );
});

/**
 * Render the Floating CTA settings page.
 */
function renderSettingsPage() {
  if (!current_user_can('manage_options')) {
    return;
  }
  ?>
  <div class="wrap">
    <h1><?= esc_html__('Floating CTA Settings', 'muuttohaukat') ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields('kansleri_floating_cta_options_group'); ?>
      <table class="form-table">
        <tr>
          <th scope="row"><label for="kansleri_floating_cta_link"><?= esc_html__('CTA Link', 'muuttohaukat') ?></label></th>
          <td><input type="url" id="kansleri_floating_cta_link" name="kansleri_floating_cta_link" value="<?= esc_attr(get_option('kansleri_floating_cta_link', '')) ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="kansleri_floating_cta_text"><?= esc_html__('CTA Text', 'muuttohaukat') ?></label></th>
          <td><input type="text" id="kansleri_floating_cta_text" name="kansleri_floating_cta_text" value="<?= esc_attr(get_option('kansleri_floating_cta_text', '')) ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="kansleri_floating_cta_second_link"><?= esc_html__('Second Button Link', 'muuttohaukat') ?></label></th>
          <td><input type="url" id="kansleri_floating_cta_second_link" name="kansleri_floating_cta_second_link" value="<?= esc_attr(get_option('kansleri_floating_cta_second_link', '')) ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="kansleri_floating_cta_second_text"><?= esc_html__('Second Button Text', 'muuttohaukat') ?></label></th>
          <td><input type="text" id="kansleri_floating_cta_second_text" name="kansleri_floating_cta_second_text" value="<?= esc_attr(get_option('kansleri_floating_cta_second_text', '')) ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="kansleri_floating_cta_location"><?= esc_html__('Display On', 'muuttohaukat') ?></label></th>
          <td>
            <select id="kansleri_floating_cta_location" name="kansleri_floating_cta_location">
              <option value="everywhere" <?php selected('everywhere', get_option('kansleri_floating_cta_location')); ?>><?= esc_html__('Everywhere', 'muuttohaukat') ?></option>
              <option value="home" <?php selected('home', get_option('kansleri_floating_cta_location')); ?>><?= esc_html__('Home Page Only', 'muuttohaukat') ?></option>
              <option value="pages" <?php selected('pages', get_option('kansleri_floating_cta_location')); ?>><?= esc_html__('All Pages', 'muuttohaukat') ?></option>
              <option value="posts" <?php selected('posts', get_option('kansleri_floating_cta_location')); ?>><?= esc_html__('All Posts', 'muuttohaukat') ?></option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><?= esc_html__('Only on Mobile', 'muuttohaukat') ?></th>
          <td><input type="checkbox" id="kansleri_floating_cta_only_on_mobile" name="kansleri_floating_cta_only_on_mobile" value="1" <?php checked('1', get_option('kansleri_floating_cta_only_on_mobile')); ?>></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}
