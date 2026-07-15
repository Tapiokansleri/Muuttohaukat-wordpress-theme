<?php
/**
 * Post helper functions.
 *
 * Excerpt retrieval with ACF ingress fallback,
 * content preview generation, and archive link helpers.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Post;

/**
 * Return post excerpt. Tries ACF ingress field first, then native excerpt, then content preview.
 */
function getExcerpt($post_id = null, $fallback = true) {
  if (is_null($post_id)) {
    $post_id = get_the_ID();
  }

  if (function_exists('get_field') && ($acf_ingress = get_field("ingress", $post_id))) {
    return $acf_ingress;
  } elseif (has_excerpt($post_id)) {
    return get_the_excerpt($post_id);
  } elseif ($fallback) {
    return getPreview($post_id);
  }

  return false;
}

/**
 * Get preview from the content. Stops at first paragraph.
 */
function getPreview($post_id = null) {
  if (is_null($post_id)) {
    $post_id = get_the_ID();
  }

  $post = get_post($post_id);
  if (!$post) {
    return '';
  }

  $str = wpautop($post->post_content);
  $paragraph_end = strpos($str, '</p>');
  if ($paragraph_end !== false) {
    $str = substr($str, 0, $paragraph_end + 4);
  }
  $str = strip_tags($str, '<a><strong><em>');

  return $str;
}

/**
 * Returns post type archive link.
 */
function archiveLink($post_type = 'post') {
  if ($post_type === 'post') {
    return get_permalink(get_option('page_for_posts'));
  }
  $archive = get_post_type_archive_link($post_type);
  if (!$archive) {
    throw new \Exception("Post type has no archive or it doesn't exist.");
  }
  return $archive;
}
