<?php
namespace Muuttohaukat\Blocks;

class Image extends \Muuttohaukat\Block {
  public function getSettings() {
    $parent = parent::getSettings();

    return \Muuttohaukat\params($parent, [
      'category' => 'widgets',
      'supports' => [
        'align' => false,
      ],
    ]);
  }

  public function render($fields, $isPreview = false, $postId = 0) {
    if ($isPreview && empty($fields['image'])) {
      echo "<p>Select an image from the media library. Try and not upload a 20 megapixel image, but also try and upload a large enough image to fit 4K screens (4096px wide).</p>";

      return null;
    }

    echo \Muuttohaukat\Media\image($fields['image'], ['allowCaption' => true]);
  }
}
