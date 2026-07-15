<?php
/**
 * Role-restricted, sanitized SVG upload support.
 *
 * @package Muuttohaukat
 */

namespace Muuttohaukat\SvgUploads;

use enshrined\svgSanitize\Sanitizer;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_readable($autoload)) {
  require_once $autoload;
}

const MIME_TYPE = 'image/svg+xml';

/** Determine whether the current user has an explicitly allowed role. */
function current_user_can_upload_svg() {
  if (!is_user_logged_in()) {
    return false;
  }

  $user = wp_get_current_user();

  return current_user_can('upload_files')
    && (bool) array_intersect(['administrator', 'editor'], (array) $user->roles);
}

/**
 * Remove network-loaded href/src values that the upstream allowlist permits.
 *
 * The sanitizer blocks executable URLs and remote CSS url() values, but allows
 * ordinary HTTP(S) hrefs. Uploaded media must remain self-contained.
 */
function remove_remote_references($svg) {
  $previous = libxml_use_internal_errors(true);
  $document = new \DOMDocument();
  $loaded = $document->loadXML($svg, LIBXML_NONET);
  libxml_clear_errors();
  libxml_use_internal_errors($previous);

  if (!$loaded || !$document->documentElement) {
    return false;
  }

  $style_elements = [];
  foreach ($document->getElementsByTagName('style') as $style_element) {
    $style_elements[] = $style_element;
  }
  foreach ($style_elements as $style_element) {
    $style_element->parentNode->removeChild($style_element);
  }

  foreach ($document->getElementsByTagName('*') as $element) {
    for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
      $attribute = $element->attributes->item($index);
      $name = strtolower($attribute->localName ?: $attribute->nodeName);

      if (in_array($name, ['srcset', 'poster'], true)) {
        $element->removeAttributeNode($attribute);
        continue;
      }

      if (in_array($name, ['href', 'src'], true)) {
        $value = trim($attribute->value);
        $is_fragment = strpos($value, '#') === 0;
        $is_embedded_raster = (bool) preg_match(
          '~^data:image/(?:png|gif|jpe?g|webp);base64,[a-z0-9+/=\s]+$~i',
          $value
        );

        if (!$is_fragment && !$is_embedded_raster) {
          $element->removeAttributeNode($attribute);
        }
        continue;
      }

      if (
        $name === 'style'
        && (
          stripos($attribute->value, '@import') !== false
          || preg_match('~url\s*\(\s*(?![\'"]?#)~i', $attribute->value)
        )
      ) {
        $element->removeAttributeNode($attribute);
      }
    }
  }

  return $document->saveXML($document->documentElement);
}

/** Add SVG to the upload allowlist for administrators and editors only. */
add_filter('upload_mimes', function ($mimes) {
  if (current_user_can_upload_svg()) {
    $mimes['svg'] = MIME_TYPE;
  } else {
    unset($mimes['svg']);
  }

  return $mimes;
});

/**
 * Sanitize an SVG before WordPress moves it into the uploads directory.
 *
 * Both regular uploads (including REST media uploads) and sideloads use this
 * callback through their respective prefilter hooks.
 */
function sanitize_upload($file) {
  $filename = isset($file['name']) ? (string) $file['name'] : '';

  if (strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)) !== 'svg') {
    return $file;
  }

  if (!current_user_can_upload_svg()) {
    $file['error'] = __('Only administrators and editors may upload SVG files.', 'muuttohaukat');
    return $file;
  }

  if (!class_exists(Sanitizer::class)) {
    $file['error'] = __('SVG uploads are unavailable because the sanitizer is not installed.', 'muuttohaukat');
    return $file;
  }

  $temporary_file = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
  $maximum_size = (int) apply_filters('muuttohaukat_svg_max_bytes', 2 * MB_IN_BYTES);
  $file_size = $temporary_file !== '' && is_readable($temporary_file)
    ? filesize($temporary_file)
    : false;

  if ($file_size === false || $file_size > $maximum_size) {
    $file['error'] = __('The SVG file is unreadable or too large.', 'muuttohaukat');
    return $file;
  }

  $svg = $temporary_file !== '' ? file_get_contents($temporary_file) : false;

  if ($svg === false || $svg === '') {
    $file['error'] = __('The SVG file could not be read.', 'muuttohaukat');
    return $file;
  }

  $sanitizer = new Sanitizer();
  $sanitizer->removeRemoteReferences(true);
  $sanitized_svg = $sanitizer->sanitize($svg);

  if ($sanitized_svg === false || trim($sanitized_svg) === '') {
    $file['error'] = __('The SVG file is invalid or unsafe.', 'muuttohaukat');
    return $file;
  }

  $sanitized_svg = remove_remote_references($sanitized_svg);
  if ($sanitized_svg === false || trim($sanitized_svg) === '') {
    $file['error'] = __('The SVG file is invalid or unsafe.', 'muuttohaukat');
    return $file;
  }

  if (file_put_contents($temporary_file, $sanitized_svg, LOCK_EX) === false) {
    $file['error'] = __('The sanitized SVG file could not be saved.', 'muuttohaukat');
    return $file;
  }

  $file['type'] = MIME_TYPE;

  return $file;
}

add_filter('wp_handle_upload_prefilter', __NAMESPACE__ . '\\sanitize_upload');
add_filter('wp_handle_sideload_prefilter', __NAMESPACE__ . '\\sanitize_upload');

/** Correct WordPress filetype detection for allowed, sanitized SVG files. */
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes, $real_mime = null) {
  if (
    current_user_can_upload_svg()
    && strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)) === 'svg'
  ) {
    $data['ext'] = 'svg';
    $data['type'] = MIME_TYPE;
    $data['proper_filename'] = false;
  }

  return $data;
}, 10, 5);

/** Allow sanitized vector uploads through WordPress 6.8+ image checks. */
add_filter('wp_prevent_unsupported_mime_type_uploads', function ($prevent, $mime_type = null) {
  if (current_user_can_upload_svg() && in_array($mime_type, [MIME_TYPE, 'image/svg'], true)) {
    return false;
  }

  return $prevent;
}, 10, 2);

/** Convert an SVG length such as "120px" to a pixel number. */
function svg_length_to_pixels($length) {
  if (!preg_match('/^\s*([0-9]*\.?[0-9]+)\s*(px|pt|pc|mm|cm|in)?\s*$/i', (string) $length, $matches)) {
    return 0.0;
  }

  $value = (float) $matches[1];
  $unit = isset($matches[2]) ? strtolower($matches[2]) : 'px';
  $multipliers = [
    'px' => 1,
    'pt' => 96 / 72,
    'pc' => 16,
    'mm' => 96 / 25.4,
    'cm' => 96 / 2.54,
    'in' => 96,
  ];

  return $value * $multipliers[$unit];
}

/** Read intrinsic SVG dimensions from width/height or the viewBox. */
function get_svg_dimensions($file) {
  if (!is_string($file) || $file === '' || !is_readable($file)) {
    return [0, 0];
  }

  $previous = libxml_use_internal_errors(true);
  $document = new \DOMDocument();
  $loaded = $document->load($file, LIBXML_NONET);
  libxml_clear_errors();
  libxml_use_internal_errors($previous);

  if (!$loaded || !$document->documentElement) {
    return [0, 0];
  }

  $svg = $document->documentElement;
  $width = svg_length_to_pixels($svg->getAttribute('width'));
  $height = svg_length_to_pixels($svg->getAttribute('height'));

  if (($width <= 0 || $height <= 0) && $svg->hasAttribute('viewBox')) {
    $view_box = preg_split('/[\s,]+/', trim($svg->getAttribute('viewBox')));

    if (is_array($view_box) && count($view_box) === 4) {
      $width = $width > 0 ? $width : (float) $view_box[2];
      $height = $height > 0 ? $height : (float) $view_box[3];
    }
  }

  return [
    max(0, (int) round($width)),
    max(0, (int) round($height)),
  ];
}

/** Store dimensions so SVG attachments behave consistently in the media UI. */
add_filter('wp_generate_attachment_metadata', function ($metadata, $attachment_id) {
  if (get_post_mime_type($attachment_id) !== MIME_TYPE) {
    return $metadata;
  }

  $file = get_attached_file($attachment_id);
  list($width, $height) = get_svg_dimensions($file);
  $uploads = wp_get_upload_dir();
  $relative_file = is_string($file) ? _wp_relative_upload_path($file) : '';
  $fallback_file = is_string($file)
    ? ltrim(str_replace($uploads['basedir'], '', $file), '/\\')
    : '';

  return [
    'width' => $width,
    'height' => $height,
    'file' => $relative_file !== '' ? $relative_file : $fallback_file,
    'sizes' => [],
  ];
}, 10, 2);

/** Expose intrinsic SVG dimensions to the media modal and REST responses. */
add_filter('wp_prepare_attachment_for_js', function ($response, $attachment, $metadata) {
  if ($attachment->post_mime_type !== MIME_TYPE) {
    return $response;
  }

  $width = isset($metadata['width']) ? (int) $metadata['width'] : 0;
  $height = isset($metadata['height']) ? (int) $metadata['height'] : 0;

  $response['width'] = $width;
  $response['height'] = $height;

  if (isset($response['sizes']['full'])) {
    $response['sizes']['full']['width'] = $width;
    $response['sizes']['full']['height'] = $height;
  }

  return $response;
}, 10, 3);
