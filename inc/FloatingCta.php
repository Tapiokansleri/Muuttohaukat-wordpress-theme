<?php
/**
 * Floating CTA banner.
 *
 * Displays a fixed-position banner with two CTA buttons.
 * Configurable from Appearance → Teeman asetukset → Kelluva CTA.
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

