<?php
/**
 * Landing: Hero — frontend wrapper around InnerBlocks content.
 *
 * @var array  $attributes
 * @var string $content
 */
$extra = isset($attributes['className']) ? ' ' . $attributes['className'] : '';
$wrapper = get_block_wrapper_attributes(['class' => 'mh-landing-hero' . $extra]);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
