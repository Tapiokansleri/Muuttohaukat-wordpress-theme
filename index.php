<?php
/**
 * The index/archive template. Last fallback in the WordPress template hierarchy.
 *
 * @see https://wphierarchy.com
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

$app   = app();
$postlisting = $app->getBlock('PostListing');

get_header(); ?>

<div class="mh-root mh-root--archive bg-white">
  <div class="prose mx-auto my-8">
    <h2><strong><?= esc_html($app->translations->getText('Title: Blog Heading')) ?></strong></h2>
  </div>

  <div class="px-4">
    <?php
    echo withTransient(capture([$postlisting, 'render'], [
      'mode' => 'mainQuery',
      'paginated' => true,
      'trackStateInUrl' => true,
      'template' => 'Card',
    ]), [
      'key' => 'indexPostListing',
      'options' => [
        'type' => 'manual-block',
      ]
    ], $missReason);

    echo "\n<!-- Block " . esc_html($postlisting->getName()) . " cache: " . esc_html(transientResult($missReason)) . " -->";
    ?>
  </div>
</div>

<?php get_footer();
