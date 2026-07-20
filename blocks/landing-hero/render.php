<?php
/**
 * Landing: Hero — frontend wrapper around InnerBlocks content.
 *
 * @var array  $attributes
 * @var string $content
 */
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-hero']);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
