<?php
/**
 * Template utility functions.
 *
 * SVG rendering, style string building, and PostListing template registry.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

/**
 * Render an SVG from the media library inside a wrapper div.
 *
 * Strips script tags from the SVG to prevent XSS from uploaded files.
 *
 * @param int   $mediaId Attachment ID of the SVG file.
 * @param array $data    Options: className.
 * @return string HTML div containing the sanitised SVG.
 */
function librarySvg(int $mediaId, array $data = []) {
  $data = \Muuttohaukat\params([
    'className' => ['mh-svg'],
  ], $data);

  $src = get_post_meta($mediaId, '_wp_attached_file', true);
  if (empty($src)) {
    return '';
  }

  $uploadDir = wp_get_upload_dir();
  $fullSrc = $uploadDir['basedir'] . '/' . $src;

  if (!file_exists($fullSrc)) {
    return '';
  }

  $svg = file_get_contents($fullSrc);
  $svg = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $svg);

  $class = \Muuttohaukat\className(...$data['className']);

  return "<div {$class}>{$svg}</div>";
}

/**
 * Convert an associative array of CSS properties to an escaped inline style string.
 *
 * @param array $styles Key-value pairs of CSS properties and values.
 * @return string Escaped style attribute value.
 */
function buildStyleString($styles = []) {
  $str = '';

  foreach ($styles as $k => $v) {
    $str .= "{$k}: {$v};";
  }

  return \esc_attr($str);
}

/**
 * Get the registered PostListing template functions.
 *
 * Template names are used as ACF select field values and
 * REST API parameters, mapped to their callable functions.
 *
 * @return array<string, string> Template name => callable function name.
 */
function getPostListTemplateList() {
  return [
    'Card'                => '\Muuttohaukat\Templates\CardPostListItem',
    'CardBackgroundImage' => '\Muuttohaukat\Templates\CardBackgroundImagePostListItem',
  ];
}
