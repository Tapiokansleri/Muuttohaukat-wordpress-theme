<?php

namespace Muuttohaukat\Templates;

/**
 * Card-style post list item template.
 *
 * @package Muuttohaukat
 */
function CardPostListItem($data = [], $i = 0, $isPreview = false)
{
  $id = get_the_ID();
  $data = \Muuttohaukat\params([
    'title' => \Muuttohaukat\title(),
    'link' => \get_permalink(),
    // 'categories' => get_the_category(),
    'image' => get_post_thumbnail_id(),
    // 'fields' => \get_fields($id), // all acf fields
  ], $data);

  $data["link"] = \Muuttohaukat\neutralizeLink($data["link"], $isPreview);
  // $image = \Muuttohaukat\Media\getImageData($data['image']);
  $image = !empty($data['image']) ? \Muuttohaukat\Media\image($data['image']) : \Muuttohaukat\getFlyingEagle();
  $time = get_the_date('d.m.Y', $id);
?>

  <a href="<?= \esc_attr($data['link']) ?>" class="group px-2 py-2 lg:py-4 lg:px-4 w-full md:w-1/2 lg:w-1/4">
    <article class="tcard rounded-none h-full w-full bg-base-100 shadow-xl sff hover:scale-95 transition duration-300" style="--padding-card: 0rem">
      <figure>
        <?= $image ?>
      </figure>

      <div class="card-body py-2 px-2 lg:py-2 lg:px-4">
        <h2 class="card-title"><?= \esc_html($data['title']) ?></h2>
        <time class="text-xs"><?= $time ?></time>
        <div class="card-actions justify-center mt-2">
          <span href="<?= \esc_attr($data['link']) ?>" class="btn btn-primary group-hover:bg-primary-focus"><?= esc_html__('Lue lisää', 'muuttohaukat') ?></span>
        </div>
      </div>
    </article>
  </a>
<?php
}
