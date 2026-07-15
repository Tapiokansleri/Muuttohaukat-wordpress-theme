<?php

namespace Muuttohaukat\Templates;

/**
 * Card post list item with background image.
 *
 * @package Muuttohaukat
 */
function CardBackgroundImagePostListItem($data = [], $i, $isPreview = false)
{
  $id = get_the_ID();
  $data = \Muuttohaukat\params([
    'title' => \Muuttohaukat\title(),
    'link' => \get_permalink(),
    'excerpt' => \Muuttohaukat\Post\getExcerpt(),
    // 'image' => \Muuttohaukat\Media\image()
    'image' => get_post_thumbnail_id(),
    // 'fields' => \get_fields($id), // all acf fields
  ], $data);

  $data["link"] = \Muuttohaukat\neutralizeLink($data["link"], $isPreview);
  $image = !empty($data['image']) ? \Muuttohaukat\Media\image($data['image'], ['responsive' => false]) : \Muuttohaukat\getFlyingEagle();

?>

  <a href="<?= \esc_attr($data['link']) ?>" class="group w-full md:w-1/2 lg:w-1/3 px-2 py-2 lg:py-4 lg:px-4 flex">
    <div class="tcard h-full w-full bg-base-100 sff hover:scale-95 shadow-xl transition duration-300 rotate-0 z-10" style="--rounded-box: 0">
      <figure>
        <?= $image ?>
      </figure>

      <div class="card-body rounded-none">
        <h2 class="card-title">
          <?= \esc_html($data['title']) ?>
        </h2>

        <p>
          <?= \esc_html($data['excerpt']) ?>
        </p>

        <div class="card-actions justify-center">
          <span class="btn btn-primary group-hover:bg-primary-focus" href="<?= $data["link"] ?>"><?= esc_html__('Klikkaa & lue lisää', 'muuttohaukat') ?></span>
        </div>
      </div>
    </div>
  </a>
<?php
}
