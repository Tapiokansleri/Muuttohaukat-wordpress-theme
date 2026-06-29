<?php
namespace Muuttohaukat\Templates;

/**
 * Simple post list item template.
 *
 * @package Muuttohaukat
 */
function SimplePostListItem($data = [], $i, $isPreview = false) {
  $id = get_the_ID();
  $data = \Muuttohaukat\params([
    'title' => \Muuttohaukat\title(),
    'link' => \get_permalink(),
    // 'categories' => get_the_category(),
    'image' => get_post_thumbnail_id(),
    // 'fields' => \get_fields($id), // all acf fields
  ], $data);

  $data["link"] = \Muuttohaukat\neutralizeLink($data["link"], $isPreview);
  $image = !empty($data['image']) ? \Muuttohaukat\Media\image($data['image']) : \Muuttohaukat\getFlyingEagle();
  ?>

  <div class="px-2 py-2 lg:py-4 lg:px-4 w-full md:w-1/3 lg:w-1/4">
    <article class="card rounded-none bg-base-100 shadow-xl corner-triangle" style="--padding-card: 0rem">
      <figure>
        <?=$image?>
      </figure>

      <div class="card-body py-2 px-2 lg:py-4 lg:px-4">
        <h2 class="card-title"><?=\esc_html($data['title'])?></h2>
        <div class="card-actions justify-center">
          <a href="<?=\esc_attr($data['link'])?>" class="btn btn-primary"><?= esc_html__('Lue lisää', 'muuttohaukat') ?></a>
        </div>
      </div>
    </article>
  </div>
  <?php
}
