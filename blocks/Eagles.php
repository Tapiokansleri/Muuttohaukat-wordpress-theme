<?php
namespace Muuttohaukat\Blocks;

class Eagles extends \Muuttohaukat\Block {
  public function getSettings() {
    $parent = parent::getSettings();

    return \Muuttohaukat\params($parent, [
      'category' => 'widgets',
    ]);
  }

  public function render($fields, $isPreview = false, $postId = 0) {
    $data = \Muuttohaukat\params(
      array_merge(
        \Muuttohaukat\getDefaultBlockRenderSettings(), [
          'kamikaze' => false,
        ]
      ),
      $fields);

      $classes = [
        'eagles',
        'bg-primary'
      ];

      if ($data['kamikaze']) {
        $classes[] = 'dive';
      }
    ?>

    <div <?=\Muuttohaukat\className(...$classes)?>>

    </div><?php
  }
}
