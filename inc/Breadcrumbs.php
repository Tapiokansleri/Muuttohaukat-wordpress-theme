<?php
/**
 * Theme-native visual breadcrumbs.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat\Breadcrumbs;

/**
 * Add an item to the breadcrumb trail.
 *
 * @param array       $items Breadcrumb items.
 * @param string      $label Item label.
 * @param string|null $url   Item URL, or null for the current item.
 */
function addItem(&$items, $label, $url = null) {
  if ($label === '') {
    return;
  }

  $items[] = [
    'label' => wp_strip_all_tags($label),
    'url'   => $url,
  ];
}

/**
 * Return the translated posts-page label.
 *
 * @return string
 */
function getPostsLabel() {
  $posts_page_id = (int) get_option('page_for_posts');

  if ($posts_page_id) {
    return get_the_title($posts_page_id);
  }

  return \Muuttohaukat\app()->translations->getText('Title: News');
}

/**
 * Add the posts index to a breadcrumb trail.
 *
 * @param array $items Breadcrumb items.
 */
function addPostsIndex(&$items) {
  $posts_page_id = (int) get_option('page_for_posts');
  $url = $posts_page_id ? get_permalink($posts_page_id) : get_post_type_archive_link('post');

  if (!$url) {
    $url = home_url('/');
  }

  addItem($items, getPostsLabel(), $url);
}

/**
 * Add a custom post type archive when one exists.
 *
 * @param array  $items    Breadcrumb items.
 * @param string $post_type Post type name.
 */
function addPostTypeArchive(&$items, $post_type) {
  $post_type_object = get_post_type_object($post_type);
  $archive_url = get_post_type_archive_link($post_type);

  if (!$post_type_object || !$archive_url) {
    return;
  }

  addItem($items, $post_type_object->labels->name, $archive_url);
}

/**
 * Add hierarchical term ancestors.
 *
 * @param array    $items Breadcrumb items.
 * @param \WP_Term $term Current term.
 */
function addTermAncestors(&$items, $term) {
  if (!$term->parent) {
    return;
  }

  $ancestor_ids = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));

  foreach ($ancestor_ids as $ancestor_id) {
    $ancestor = get_term($ancestor_id, $term->taxonomy);

    if (!$ancestor || is_wp_error($ancestor)) {
      continue;
    }

    $url = get_term_link($ancestor);
    addItem($items, $ancestor->name, is_wp_error($url) ? null : $url);
  }
}

/**
 * Build breadcrumb items for the current request.
 *
 * @return array
 */
function getItems() {
  if (is_front_page()) {
    return [];
  }

  $items = [];
  $home_label = \Muuttohaukat\app()->translations->getText('Breadcrumb: Home');
  addItem($items, $home_label, home_url('/'));

  if (is_home()) {
    addItem($items, getPostsLabel());
    return $items;
  }

  if (is_singular()) {
    $post = get_queried_object();

    if (!$post instanceof \WP_Post) {
      return $items;
    }

    if ($post->post_type === 'page') {
      $ancestor_ids = array_reverse(get_post_ancestors($post));

      foreach ($ancestor_ids as $ancestor_id) {
        addItem($items, get_the_title($ancestor_id), get_permalink($ancestor_id));
      }
    } elseif ($post->post_type === 'post') {
      addPostsIndex($items);
    } else {
      addPostTypeArchive($items, $post->post_type);

      if (is_post_type_hierarchical($post->post_type)) {
        $ancestor_ids = array_reverse(get_post_ancestors($post));

        foreach ($ancestor_ids as $ancestor_id) {
          addItem($items, get_the_title($ancestor_id), get_permalink($ancestor_id));
        }
      }
    }

    addItem($items, get_the_title($post));
    return $items;
  }

  if (is_category() || is_tag() || is_tax()) {
    $term = get_queried_object();

    if (!$term instanceof \WP_Term) {
      return $items;
    }

    if ($term->taxonomy === 'category' || $term->taxonomy === 'post_tag') {
      addPostsIndex($items);
    } else {
      $taxonomy = get_taxonomy($term->taxonomy);

      if ($taxonomy && !empty($taxonomy->object_type)) {
        addPostTypeArchive($items, reset($taxonomy->object_type));
      }
    }

    addTermAncestors($items, $term);
    addItem($items, single_term_title('', false));
    return $items;
  }

  if (is_post_type_archive()) {
    $post_type = get_query_var('post_type');
    $post_type = is_array($post_type) ? reset($post_type) : $post_type;
    $post_type_object = get_post_type_object($post_type);
    addItem($items, $post_type_object ? $post_type_object->labels->name : post_type_archive_title('', false));
    return $items;
  }

  if (is_date()) {
    addPostsIndex($items);
    $year = (int) get_query_var('year');

    if (is_year()) {
      addItem($items, (string) $year);
    } else {
      addItem($items, (string) $year, get_year_link($year));
      $month = (int) get_query_var('monthnum');
      $month_label = date_i18n(_x('F', 'monthly archives date format', 'muuttohaukat'), mktime(0, 0, 0, $month, 1, $year));

      if (is_month()) {
        addItem($items, $month_label);
      } else {
        addItem($items, $month_label, get_month_link($year, $month));
        addItem($items, (string) ((int) get_query_var('day')));
      }
    }

    return $items;
  }

  if (is_author()) {
    addPostsIndex($items);
    $author = get_queried_object();
    addItem($items, $author instanceof \WP_User ? $author->display_name : get_the_author());
    return $items;
  }

  if (is_search()) {
    addItem($items, sprintf(__('Hakutulokset haulle: %s', 'muuttohaukat'), get_search_query(false)));
    return $items;
  }

  if (is_404()) {
    addItem($items, __('Sivua ei löytynyt', 'muuttohaukat'));
    return $items;
  }

  if (is_archive()) {
    addItem($items, \Muuttohaukat\app()->translations->getText('Title: Archive'));
  }

  return $items;
}

/**
 * Render the accessible visual breadcrumb trail.
 */
function render() {
  $items = getItems();

  if (count($items) < 2) {
    return;
  }
  ?>
  <nav class="site-footer__breadcrumbs breadcrumbs" aria-label="<?= esc_attr__('Murupolku', 'muuttohaukat') ?>">
    <span class="site-footer__breadcrumbs-label"><?= esc_html__('Olet sivulla', 'muuttohaukat') ?></span>
    <ol class="breadcrumbs__list">
      <?php foreach ($items as $index => $item) : ?>
        <?php $is_current = $index === count($items) - 1; ?>
        <li class="breadcrumbs__item">
          <?php if (!$is_current && !empty($item['url'])) : ?>
            <a class="breadcrumbs__link" href="<?= esc_url($item['url']) ?>"><?= esc_html($item['label']) ?></a>
          <?php else : ?>
            <span class="breadcrumbs__current"<?= $is_current ? ' aria-current="page"' : '' ?>><?= esc_html($item['label']) ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <?php
}
