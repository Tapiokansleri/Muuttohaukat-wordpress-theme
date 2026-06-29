<?php
/**
 * Enqueue stylesheets and scripts.
 *
 * CSS lives in assets/css/ as readable files.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

$app = app();

$localizeData = [
  'lang' => $app->translations->getLanguage(),
  'path' => get_stylesheet_directory_uri(),
  'wpurl' => get_site_url(),
  'translations' => $strings ?? [],
];

remove_action('wp_head', 'print_emoji_detection_script', 7);

add_action('wp_enqueue_scripts', function () use ($localizeData) {
  $themeUri = get_stylesheet_directory_uri();
  $version = wp_get_theme()->get('Version');

  // 1. Design tokens - shared custom properties for all theme styles
  wp_enqueue_style('muuttohaukat-tokens', $themeUri . '/assets/css/00-tokens.css', [], $version);
  // 2. Client CSS (normalize + fonts + legacy theme components)
  wp_enqueue_style('muuttohaukat-client', $themeUri . '/assets/css/client.css', ['muuttohaukat-tokens'], $version);
  // 3. Tailwind (Preflight reset + utilities + DaisyUI + prose)
  wp_enqueue_style('muuttohaukat-tailwind', $themeUri . '/assets/css/tailwind.css', ['muuttohaukat-client'], $version);
  // 4. Base element defaults - token-driven overrides after Tailwind
  wp_enqueue_style('muuttohaukat-base', $themeUri . '/assets/css/02-base.css', ['muuttohaukat-tailwind'], $version);
  // 5. Header styles
  wp_enqueue_style('muuttohaukat-header', $themeUri . '/assets/css/header.css', ['muuttohaukat-base'], $version);
  // 6. Footer styles
  wp_enqueue_style('muuttohaukat-footer', $themeUri . '/assets/css/footer.css', ['muuttohaukat-base'], $version);
  // 7. Content typography - final authority on content area styling
  wp_enqueue_style('muuttohaukat-content', $themeUri . '/assets/css/content.css', ['muuttohaukat-base'], $version);

  $should_load_landing = false;
  if (is_page_template('template-landing-page.php')) {
    $should_load_landing = true;
  } elseif (is_singular()) {
    $post = get_queried_object();
    if ($post && !empty($post->post_content) && (
      strpos($post->post_content, 'mh-landing-') !== false ||
      strpos($post->post_content, 'muuttohaukat/route-calculator') !== false
    )) {
      $should_load_landing = true;
    }
  }

  if ($should_load_landing) {
    // Load FA via its CSS bundle (not the JS SVG framework) so the @font-face
    // is available — landing blocks render icons via CSS ::before { content }
    // rules, which need the icon-font, not the JS-swapped <svg> elements.
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
    wp_enqueue_style('muuttohaukat-landing', $themeUri . '/assets/css/landing.css', ['muuttohaukat-base'], $version);
  }

  wp_enqueue_script('muuttohaukat-client', $themeUri . '/assets/js/client.js', [], $version, true);
  wp_localize_script('muuttohaukat-client', 'wptheme', $localizeData);

  wp_enqueue_script('muuttohaukat-postlisting', $themeUri . '/assets/js/postlisting.js', ['muuttohaukat-client'], $version, true);
});

/**
 * Block editor: load the same minimal stack landing blocks need on the
 * frontend so the in-editor preview matches what visitors see. Tokens give
 * us the CSS custom properties referenced by landing.css; FA's CSS bundle
 * lets icon pseudo-elements render. Always loaded in the editor — there's
 * no reliable way to detect "this post has landing blocks" at enqueue time.
 */
add_action('enqueue_block_editor_assets', function () {
  $themeUri = get_stylesheet_directory_uri();
  $version  = wp_get_theme()->get('Version');

  wp_enqueue_style('muuttohaukat-tokens-editor', $themeUri . '/assets/css/00-tokens.css', [], $version);
  wp_enqueue_style('font-awesome-editor', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');
  wp_enqueue_style('muuttohaukat-landing-editor', $themeUri . '/assets/css/landing.css', ['muuttohaukat-tokens-editor', 'font-awesome-editor'], $version);

  // When editing a landing-page CPT post:
  //   1. Widen the editor canvas to full width so the 2- and 3-column
  //      landing block layouts have room to render. Default Gutenberg
  //      constrains .wp-block to ~840px; landing layouts need 1120px+ to
  //      look like the frontend.
  //   2. Recreate the frontend yellow/white alternation pattern by
  //      targeting top-level .wp-block siblings with :nth-of-type. On the
  //      frontend the same pattern uses .mh-landing > :nth-child(...) but
  //      .mh-landing doesn't wrap blocks in the editor canvas.
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if ($screen && $screen->post_type === 'landing-page') {
    wp_add_inline_style('muuttohaukat-landing-editor', '
      .editor-styles-wrapper .wp-block,
      .editor-styles-wrapper .is-root-container > .wp-block { max-width: none !important; }
      .editor-styles-wrapper .wp-block[data-align="full"],
      .editor-styles-wrapper .wp-block[data-align="wide"] { max-width: none !important; }

      .editor-styles-wrapper .is-root-container > .wp-block:nth-of-type(even) > [class*="wp-block-muuttohaukat-landing-"] {
        background-color: var(--color-accent);
      }
      .editor-styles-wrapper .is-root-container > .wp-block:nth-of-type(odd) > [class*="wp-block-muuttohaukat-landing-"] {
        background-color: var(--color-white);
      }
    ');
  }
});
