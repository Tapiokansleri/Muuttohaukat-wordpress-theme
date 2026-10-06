<?php
/**
 * Media helper functions.
 *
 * Provides responsive image rendering with srcset support,
 * image metadata retrieval, and sizes attribute generation.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Media;

/**
 * Render an image tag with optional srcset and responsive support.
 *
 * @param int|array|null $image Attachment ID or ACF image array.
 * @param array          $data  Options: size, className, responsive, sizes, allowCaption.
 * @return string|false HTML img tag or false on failure.
 */
function image($image = null, array $data = []) {
  $data = \Muuttohaukat\params([
    'size' => 'medium',
    'className' => ['mh-image'],
    'responsive' => true,
    'sizes' => null,
    'allowCaption' => false,
    'altFallback' => '', // used when the attachment has no alt text
  ], $data);

  $image = getImageData($image, $data['size']);
  $class = \Muuttohaukat\className(...$data['className']);

  if (!$image) {
    return false;
  }

  // Audit 3.7: empty alt only when nothing describes the image.
  $alt_text = trim((string) $image['alt']);
  if ($alt_text === '') {
    $alt_text = trim((string) $data['altFallback']) ?: alt_from_title($image['title'] ?? '');
  }

  $src    = \esc_url($image['src']);
  $alt    = \esc_attr($alt_text);
  $width  = \esc_attr($image['width']);
  $height = \esc_attr($image['height']);

  $tag = "<img src='{$src}' {$class} alt='{$alt}' width='{$width}' height='{$height}'";

  if ($data['responsive'] && !empty($image['srcset'])) {
    $data['sizes'] = empty($data['sizes']) ? getImageSizesAttribute($image['srcset']) : $data['sizes'];

    $srcset = \esc_attr($image['srcset']);
    $sizes  = \esc_attr($data['sizes']);
    $tag .= " srcset='{$srcset}' sizes='{$sizes}'";
  }

  if (!empty($image['title'])) {
    $tag .= " title='" . \esc_attr($image['title']) . "'";
  }

  $tag .= '>';

  if ($data['allowCaption'] && !empty($image['caption'])) {
    $caption = \esc_html($image['caption']);

    return "<figure class='mh-figure'>{$tag}<figcaption class='mh-figure__caption'>{$caption}</figcaption></figure>";
  }

  return $tag;
}

/**
 * An alt text from an attachment title, or '' when the title is only a file
 * or camera name (IMG_1234, DSC01234, "kuva 3"). A trailing "(2)" that
 * WordPress adds to duplicate titles is dropped.
 */
function alt_from_title($title) {
  $title = trim(preg_replace('/\s*\(\d+\)$/u', '', \wp_strip_all_tags((string) $title)));
  // File-name titles ("rullakko_takaseinä", "Lindell_Jari_") read better with spaces.
  $title = trim(preg_replace('/[\s_]+/u', ' ', $title));
  if ($title === '' || !preg_match('/\p{L}{3}/u', $title) || preg_match('/^(img|dsc|dscn|pxl|image|kuva|photo|screenshot|näyttökuva)[\s_-]*\d/iu', $title)) {
    return '';
  }

  return $title;
}

/**
 * Audit 3.7: images typed into post content or Beaver Builder text modules
 * keep a hard-coded alt="" after their attachment gets an alt text (the 111
 * moving box pages print seven product photos this way). Fill it from the
 * attachment when the page is rendered.
 */
\add_filter('wp_content_img_tag', function ($image, $context, $attachment_id) {
  if (!preg_match('/\salt=(""|\'\')/', $image) || !preg_match('/\ssrc=["\']([^"\']+)/', $image, $src)) {
    return $image;
  }

  $id  = content_image_attachment($src[1], (int) $attachment_id);
  $alt = $id ? trim((string) \get_post_meta($id, '_wp_attachment_image_alt', true)) : '';

  return $alt === '' ? $image : preg_replace('/\salt=(""|\'\')/', ' alt="' . \esc_attr($alt) . '"', $image, 1);
}, 10, 3);

/**
 * The attachment behind a content image. The wp-image-N class counts only
 * when it points at the same file, because imported posts carry the IDs of
 * other images.
 */
function content_image_attachment($src, $class_id) {
  if ($class_id) {
    $meta = \wp_get_attachment_metadata($class_id);
    if (is_array($meta) && \wp_image_file_matches_image_meta($src, $meta, $class_id)) {
      return $class_id;
    }
  }

  // attachment_url_to_postid() knows only the full size file.
  return (int) \attachment_url_to_postid(preg_replace('/-\d+x\d+(?=\.\w+$)/', '', $src));
}

/**
 * Generate a sizes attribute value from a srcset string.
 *
 * @param string $rawSrcSet The raw srcset attribute value.
 * @return string The computed sizes attribute value.
 */
function getImageSizesAttribute($rawSrcSet) {
  $sets = explode(', ', $rawSrcSet);
  $sets = array_filter(array_map(function ($set) {
    if (empty($set)) {
      return null;
    }

    $parts = explode(' ', $set);
    if (count($parts) < 2) {
      return null;
    }

    return [
      'url' => $parts[0],
      'size' => $parts[1],
    ];
  }, $sets));
  $sizes = '';

  $prevPxSize = null;
  foreach ($sets as $set) {
    $size = $set['size'];
    $pxSize = str_replace('w', 'px', $size);

    if (!$prevPxSize) {
      $sizes .= "(min-width: {$pxSize}) {$size}, \n";
    } else {
      $sizes .= "(max-width: {$pxSize}) and (min-width: {$prevPxSize}) {$size},\n";
    }

    $prevPxSize = $pxSize;
  }

  $sizes .= '100vw';

  return $sizes;
}

/**
 * Get image data from WordPress by attachment ID or ACF image array.
 *
 * @param int|array|null $image Attachment ID or ACF image array.
 * @param string         $size  WordPress image size name.
 * @return array|false Image data array or false on failure.
 */
function getImageData($image = null, string $size = 'medium') {
  if (is_array($image)) {
    $id = $image['ID'];
  } else if (is_numeric($image)) {
    $id = absint($image);
  } else {
    return false;
  }

  $attachment = get_post($id);
  if (!$attachment) {
    return false;
  }

  $src = wp_get_attachment_image_src($id, $size);
  if (!$src) {
    return false;
  }

  $mime = get_post_mime_type($id);
  $url = wp_get_attachment_url($id);
  $srcset = wp_get_attachment_image_srcset($id, $size) ?: '';

  // SVG attachments often have corrupt size metadata (missing upload subdir in srcset).
  if ($mime === 'image/svg+xml') {
    $srcset = '';
    if (is_string($url) && $url !== '') {
      $src[0] = $url;
    }
  }

  return [
    'src'         => $src[0],
    'width'       => $src[1],
    'height'      => $src[2],
    'srcset'      => $srcset,
    'description' => $attachment->post_content,
    'title'       => $attachment->post_title,
    'alt'         => get_post_meta($id, '_wp_attachment_image_alt', true) ?: '',
    'caption'     => $attachment->post_excerpt,
  ];
}
