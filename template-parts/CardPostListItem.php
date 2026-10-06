<?php

namespace Muuttohaukat\Templates;

/**
 * Card-style post list item template: the shared article card
 * (inc/ArticleCards.php), so every article feed looks the same.
 *
 * @package Muuttohaukat
 */
function CardPostListItem($data = [], $i = 0, $isPreview = false)
{
  $post = get_post();
  if (!$post) {
    return;
  }

  $html = \Muuttohaukat\article_card_html($post, 'h3');

  // In the editor preview the links must not navigate away.
  if ($isPreview) {
    $html = preg_replace('/href="[^"]*"/', 'href="#"', $html);
  }

  echo $html;
}
