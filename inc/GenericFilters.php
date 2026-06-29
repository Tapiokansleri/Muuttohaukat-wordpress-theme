<?php
/**
 * Generic filters and miscellaneous hooks.
 *
 * Title prefixes for non-production environments, content cleanup,
 * archive title fixes, breadcrumb customisation, and oEmbed wrapping.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\GenericFilters;

/**
 * Prefix the document title with an environment indicator on non-production sites.
 *
 * @param string $title The original title.
 * @return string Prefixed title or the original on production for anonymous users.
 */
function titlePrefix($title) {
  if (\Muuttohaukat\isProd() && !is_user_logged_in()) {
    return $title;
  }

  if (\Muuttohaukat\isDev()) {
    return "[D] {$title}";
  }

  if (\Muuttohaukat\isStaging()) {
    return "[S] {$title}";
  }

  if (\Muuttohaukat\isProd() && is_user_logged_in()) {
    return "[P] {$title}";
  }

  return $title;
}

add_filter('the_seo_framework_title_from_generation', '\\Muuttohaukat\\GenericFilters\\titlePrefix');
add_filter('admin_title', '\\Muuttohaukat\\GenericFilters\\titlePrefix');
add_filter('wp_title', '\\Muuttohaukat\\GenericFilters\\titlePrefix');

/**
 * Remove empty paragraphs left by the visual editor.
 *
 * @param string $content Post content.
 * @return string Cleaned content.
 */
function stripEmptyParagraphs($content) {
  return str_replace('<p>&nbsp;</p>', '', $content);
}

add_filter('the_content', '\\Muuttohaukat\\GenericFilters\\stripEmptyParagraphs');

/**
 * Remove the admin bar top-margin bump so the theme controls header positioning.
 */
function removeAdminBarMargin() {
  remove_action('wp_head', '_admin_bar_bump_cb');
}

add_action('get_header', '\\Muuttohaukat\\GenericFilters\\removeAdminBarMargin');

/** Hide SEO Framework traffic light column in post lists. */
add_filter('the_seo_framework_show_seo_column', '__return_false');

/**
 * Fix archive titles to use translated strings and remove the default "Category:" prefix.
 *
 * @param string $title Default archive title.
 * @return string Cleaned archive title.
 */
function fixArchiveTitle($title) {
  $app = \Muuttohaukat\app();

  if (is_home()) {
    return $app->translations->getText('Title: News');
  } elseif (is_category()) {
    return single_cat_title($app->translations->getText('Title: Category') . ': ', false);
  } elseif (is_tag()) {
    return single_tag_title($app->translations->getText('Title: Tag') . ': ', false);
  } elseif (is_author()) {
    return '<span class="vcard">' . esc_html(get_the_author()) . '</span>';
  } elseif (is_year()) {
    return get_the_date(_x('Y', 'yearly archives date format', 'muuttohaukat'));
  } elseif (is_month()) {
    return get_the_date(_x('F Y', 'monthly archives date format', 'muuttohaukat'));
  } elseif (is_day()) {
    return get_the_date(_x('F j, Y', 'daily archives date format', 'muuttohaukat'));
  } elseif (is_post_type_archive()) {
    return post_type_archive_title('', false);
  } elseif (is_tax()) {
    return single_term_title('', false);
  }

  return $app->translations->getText('Title: Archive');
}

add_filter('get_the_archive_title', '\\Muuttohaukat\\GenericFilters\\fixArchiveTitle');

/**
 * Add translatable %home% tag to Breadcrumb NavXT.
 */
add_filter('bcn_template_tags', function ($replacements, $type, $id) {
  $app = \Muuttohaukat\app();
  $replacements['%home%'] = $app->translations->getText('Breadcrumb: Home');

  return $replacements;
}, 10, 3);

/**
 * Wrap oEmbed output in a responsive container.
 *
 * @param string $cache The oEmbed HTML.
 * @return string Wrapped HTML.
 */
function embedWrap($cache) {
  return '<div class="responsive-embed">' . $cache . '</div>';
}

add_filter('embed_oembed_html', '\\Muuttohaukat\\GenericFilters\\embedWrap');

/** Increase srcset max image width from the default 1600px. */
add_filter('max_srcset_image_width', function () {
  return 2560;
});

/**
 * Add a `bb-page` body class when Beaver Builder is rendering the current
 * page. Used by CSS to trim default heading vertical margins without
 * overriding BB column/module horizontal layout.
 */
add_filter('body_class', function ($classes) {
  if (is_singular() && class_exists('FLBuilderModel') && \FLBuilderModel::is_builder_enabled(get_the_ID())) {
    $classes[] = 'bb-page';
  }
  return $classes;
});
