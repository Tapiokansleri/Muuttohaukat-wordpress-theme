<?php
namespace Muuttohaukat\Templates;

use \Muuttohaukat\Media as M;

/**
 * Search form template.
 *
 * @package Muuttohaukat
 */
function Search($data = []) {
  $app = \Muuttohaukat\app();
  $data = \Muuttohaukat\params([
    'classList' => ['mh-search-form'],
    'action' => '/',
    'placeholder' => 'Etsi sivustolta',
  ], $data);
  ?>

  <form <?=\Muuttohaukat\className(...$data['classList'])?> action="<?=$data['action']?>">
    <input type="search" name="s" placeholder="<?=$app->translations->getText($data['placeholder'])?>">

    <button type="submit">
      <span class="sr-text">
        <?= esc_html__('Search', 'muuttohaukat') ?>
      </span>
    </button>
  </form><?php
}