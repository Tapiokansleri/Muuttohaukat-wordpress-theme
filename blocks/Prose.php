<?php
namespace Muuttohaukat\Blocks;


class Prose extends \Muuttohaukat\Block {
  public function getSettings() {
    $parent = parent::getSettings();

    return \Muuttohaukat\params($parent, [
      // 'category' => 'widgets',
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
        'prose',
        // 'bg-primary'
      ];

      $template = [['core/heading', ['level' => 2, 'content' => 'Typographic masterpieces go here']]];
    ?>

    <div <?=\Muuttohaukat\className(...$classes)?>>
      <?php
        echo '<InnerBlocks template="' . esc_attr(wp_json_encode($template)) . '" />';
      ?>
    </div><?php
  }
}
