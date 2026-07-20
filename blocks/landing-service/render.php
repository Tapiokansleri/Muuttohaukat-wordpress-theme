<?php
/**
 * Landing: Tarjous ja tilaus
 *
 * @var array  $attributes
 * @var string $content
 */
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-section'], [
  'id' => $attributes['anchor'] ?? 'palvelu',
]);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
