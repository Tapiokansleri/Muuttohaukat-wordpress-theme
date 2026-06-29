<?php
/**
 * The search results template.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

use \Muuttohaukat\Templates as T;

$app = app();
$postlisting = $app->getBlock('PostListing');
$q = get_search_query();

get_header(); ?>

<div class="mh-root mh-root--search-page mh-scheme--base-white">
  <div class="mh-gutenberg">
    <div class="mh-search-container mh-container">
      <?= T\Search() ?>

      <h1>
        <?= esc_html__('Search:', 'muuttohaukat') ?> <?= esc_html($q) ?>
      </h1>
    </div>

    <?php
    echo withTransient(capture([$postlisting, 'render'], [
      'blockSettings' => [
        'scheme' => ['base' => 'white'],
        'whitespace' => ['paddings' => ['']],
      ],
      'template' => 'Card',
      'mode' => 'mainQuery',
      'paginated' => true,
      'trackStateInUrl' => false,
    ]), [
      'key' => 'searchPostListing-' . sanitize_key($q),
      'options' => [
        'expires' => 60,
        'type' => 'manual-block',
      ]
    ], $missReason);

    echo "\n<!-- Block " . esc_html($postlisting->getName()) . " cache: " . esc_html(transientResult($missReason)) . " -->";
    ?>
  </div>
</div>

<?php get_footer();
