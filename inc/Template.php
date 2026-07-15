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
 * Allows only presentation-safe SVG elements and attributes.
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
  $basePath = realpath($uploadDir['basedir']);
  $fullPath = realpath($fullSrc);

  if (
    !$basePath ||
    !$fullPath ||
    strpos($fullPath, $basePath . DIRECTORY_SEPARATOR) !== 0 ||
    strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) !== 'svg' ||
    get_post_mime_type($mediaId) !== 'image/svg+xml'
  ) {
    return '';
  }

  $svg = file_get_contents($fullPath);
  if ($svg === false) {
    return '';
  }

  $common = [
    'id' => true,
    'class' => true,
    'style' => true,
    'transform' => true,
    'fill' => true,
    'fill-rule' => true,
    'fill-opacity' => true,
    'stroke' => true,
    'stroke-width' => true,
    'stroke-linecap' => true,
    'stroke-linejoin' => true,
    'stroke-opacity' => true,
    'opacity' => true,
    'clip-path' => true,
    'mask' => true,
  ];
  $allowed = [
    'svg' => array_merge($common, [
      'xmlns' => true,
      'xmlns:xlink' => true,
      'viewbox' => true,
      'width' => true,
      'height' => true,
      'preserveaspectratio' => true,
      'role' => true,
      'aria-hidden' => true,
      'aria-label' => true,
      'focusable' => true,
    ]),
    'g' => $common,
    'path' => array_merge($common, ['d' => true]),
    'rect' => array_merge($common, ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true]),
    'circle' => array_merge($common, ['cx' => true, 'cy' => true, 'r' => true]),
    'ellipse' => array_merge($common, ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true]),
    'line' => array_merge($common, ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true]),
    'polyline' => array_merge($common, ['points' => true]),
    'polygon' => array_merge($common, ['points' => true]),
    'title' => [],
    'desc' => [],
    'defs' => [],
    'clippath' => array_merge($common, ['clippathunits' => true]),
    'mask' => array_merge($common, ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'maskunits' => true]),
    'lineargradient' => array_merge($common, ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true]),
    'radialgradient' => array_merge($common, ['cx' => true, 'cy' => true, 'r' => true, 'fx' => true, 'fy' => true, 'gradientunits' => true]),
    'stop' => array_merge($common, ['offset' => true, 'stop-color' => true, 'stop-opacity' => true]),
    'use' => array_merge($common, ['href' => true, 'xlink:href' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true]),
    'symbol' => array_merge($common, ['viewbox' => true, 'preserveaspectratio' => true]),
  ];
  $svg = wp_kses($svg, $allowed);

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
    // Legacy values retained in existing Haukka block content.
    'SimplePostListItem'  => '\Muuttohaukat\Templates\SimplePostListItem',
    'PackingBox'          => '\Muuttohaukat\Templates\CardPostListItem',
  ];
}
