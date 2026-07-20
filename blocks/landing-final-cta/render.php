<?php
/** @var array $attributes @var string $content */
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-section', 'mh-landing-section--final'], [
  'id' => $attributes['anchor'] ?? 'tarjous',
]);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
