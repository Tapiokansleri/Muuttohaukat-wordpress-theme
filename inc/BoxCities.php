<?php
/**
 * [muuttolaatikot_paikkakunnat]: every published moving box city page,
 * grouped by the office that serves the town (audit 3.6). The 111 pages were
 * linked only from the sitemap; the box hub now links all of them.
 *
 * The list is the shared locality directory (inc/CityDirectory.php), so it
 * looks the same as the moving and cleaning directories.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

add_shortcode('muuttolaatikot_paikkakunnat', function () {
  return city_directory_html(['palvelu' => 'muuttolaatikot']);
});
