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

add_action('wp_enqueue_scripts', function () {
  $css = '
    .kansleri-floating-cta-banner {
      position: fixed;
      bottom: 16px;
      left: 16px;
      z-index: 999;
    }

    .kansleri-floating-cta-banner .mh-painike-wrap {
      margin: 0;
      display: flex;
      flex-direction: row;
      flex-wrap: nowrap;
      align-items: center;
      gap: 0.5rem;
    }

    .kansleri-floating-cta-banner .mh-painike {
      --mh-btn-pad-x: 1rem;
      --mh-btn-chevron-width: 0.4em;
      --mh-btn-chevron-height: 0.6em;
      --mh-btn-chevron-shift: 0.35em;
      width: auto;
      padding: 12px 18px;
      font-size: 0.75rem;
      letter-spacing: 0.04em;
      white-space: nowrap;
    }

    .kansleri-floating-cta-banner .mh-painike:hover,
    .kansleri-floating-cta-banner .mh-painike:focus-visible {
      padding-top: 12px;
      padding-bottom: 12px;
    }

    @media (max-width: 640px) {
      .kansleri-floating-cta-banner {
        bottom: 12px;
        left: 12px;
        right: 12px;
      }

      .kansleri-floating-cta-banner .mh-painike-wrap {
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.5rem;
      }

      .kansleri-floating-cta-banner .mh-painike {
        --mh-btn-pad-x: 0.875rem;
        width: auto;
        max-width: none;
        flex: 0 1 auto;
        padding: 10px 14px;
        font-size: 0.6875rem;
      }

      .kansleri-floating-cta-banner .mh-painike:hover,
      .kansleri-floating-cta-banner .mh-painike:focus-visible {
        padding-top: 10px;
        padding-bottom: 10px;
      }
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

  wp_register_style('muuttohaukat-floating-cta', false, ['muuttohaukat-button-chevron']);
  wp_enqueue_style('muuttohaukat-floating-cta');
  wp_add_inline_style('muuttohaukat-floating-cta', $css);
}, 20);

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
    <div class="mh-painike-wrap">
      <?php if (!empty($text)) : ?>
      <a href="<?= esc_url($link) ?>" class="mh-painike mh-painike--yellow">
        <?= esc_html($text) ?>
      </a>
      <?php endif; ?>
      <?php if (!empty($secondText)) : ?>
      <a href="<?= esc_url($secondLink) ?>" class="mh-painike mh-painike--black">
        <?= esc_html($secondText) ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php
});
