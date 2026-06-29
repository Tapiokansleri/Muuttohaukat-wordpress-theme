<?php
/**
 * String registration for Polylang translations.
 *
 * Registers all translatable strings used by the theme (UI labels,
 * pagination, error messages, date validation) with Polylang and
 * makes translated values available via the $strings array.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

$app = app();
$strings = [
  'Font-size: Default' => 'Default',
  'Font-size: Much smaller' => 'Much smaller',
  'Font-size: Smaller' => 'Smaller',
  'Font-size: Normal' => 'Normal',
  'Font-size: Large' => 'Large',

  'Pagination: Previous' => 'Edellinen',
  'Pagination: Next' => 'Seuraava',
  'Pagination: First' => 'Ensimmäinen',
  'Pagination: Last' => 'Viimeinen',

  'Title: Category' => 'Kategoria',
  'Title: Tag' => 'Tagi',
  'Title: Archive' => 'Arkisto',
  'Title: 404' => 'Teit väärän käännöksen, 404!',
  'Title: Blog Heading' => 'Ajankohtaista',

  'Breadcrumb: Home' => 'Etusivu',
  'Placeholder: Find from page' => 'Etsi sivulta',

  'PostListFetchError' => 'Something went wrong! Try doing that again in a moment.',
  'PostListJsonError' => 'Something is wrong. We\'re working on the problem. Try again later.',

  'post_tag' => 'Tagi',
  'category' => 'Kategoria',
  'No posts found' => 'Ei sisältöä valituilla hakutermeillä.',
  'Loading' => 'Ladataan',

  // JS date validation strings (passed to frontend via wp_localize_script)
  'dateIsToday' => 'Päivä ei voi olla tänään.',
  'dateInPast' => 'Päivä ei voi olla menneisyydessä.',
  'dateBeforeDelivery' => 'Päivä ei voi olla ennen toimitusta.',
  'dateBeforeMove' => 'Päivä ei voi olla ennen muuttoa.',

  // Mobile menu a11y
  'toggleSubmenu' => 'Avaa alavalikko',

  'Etsi sivustolta' => 'Etsi sivustolta',
  'Title: News' => 'Ajankohtaista',
];

foreach ($strings as $k => $v) {
  $app->translations->registerString($k, $v);

  $strings[$k] = $app->translations->getText($k);
}

add_action('rest_api_init', function() {
  if (function_exists('pll_default_language')) {
    // Set the language in api requests
    // https://github.com/polylang/polylang/issues/160#issuecomment-345991147

    $defaultLanguage = pll_default_language();
    $languages = pll_languages_list();
    $getRequestLanguage = \filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $requestLanguage = $getRequestLanguage ? $getRequestLanguage : 'fi';
    $language = in_array($requestLanguage, $languages) ? $requestLanguage : $defaultLanguage;

    PLL()->curlang = PLL()->model->get_language($language);
  }
});
