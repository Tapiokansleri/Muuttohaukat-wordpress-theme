<?php
/**
 * ACF field configuration and options pages.
 *
 * Populates ACF select fields with dynamic choices (Tailwind spacing,
 * PostListing templates) and registers per-language options pages.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\ACF;

if (is_admin()) {
  /**
   * Populate Wrapper block margin/padding fields with Tailwind spacing values.
   */
  $spacingPopulator = function ($field) {
    $options = explode(' ', 'auto 0 1 2 3 4 5 6 7 8 9 10');
    $choices = [];

    foreach ($options as $choice) {
      $choices[$choice] = $choice;
    }

    $field['choices'] = $choices;

    return $field;
  };

  add_filter('acf/load_field/key=field_624ccbcfe7872', $spacingPopulator);
  add_filter('acf/load_field/key=field_624ccdf92ad23', $spacingPopulator);
  add_filter('acf/load_field/key=field_624ccea6c2193', $spacingPopulator);
  add_filter('acf/load_field/key=field_624cce96c2192', $spacingPopulator);
  add_filter('acf/load_field/key=field_625c9671f235e', $spacingPopulator);
  add_filter('acf/load_field/key=field_625c9671f235f', $spacingPopulator);
  add_filter('acf/load_field/key=field_625c9671f2362', $spacingPopulator);
  add_filter('acf/load_field/key=field_625c9671f2363', $spacingPopulator);

  /**
   * Populate PostListing template select field with registered templates.
   */
  $templatePopulator = function ($field) {
    $templates = \Muuttohaukat\getPostListTemplateList();
    $names = [];

    foreach ($templates as $name => $template) {
      $names[$name] = $name;
    }

    $field['choices'] = $names;

    return $field;
  };

  add_filter('acf/load_field/key=field_6177fa3360bb3', $templatePopulator);

  /**
   * Register per-language ACF options pages.
   */
  if (\function_exists('acf_add_options_page')) {
    add_action('init', function () {
      $app = \Muuttohaukat\app();

      foreach ($app->translations->getLanguages() as $lang) {
        $lang = strtoupper($lang);

        \acf_add_options_page([
          'page_title' => "{$lang} settings",
          'menu_title' => "{$lang} settings",
          'parent_slug' => 'options-general.php',
        ]);
      }
    });
  }
}

/** Configure ACF Extended settings. */
add_action('acfe/init', function () {
  acfe_update_setting('modules/single_meta', true);
  acfe_update_setting('dev', !\Muuttohaukat\isProd());
});
