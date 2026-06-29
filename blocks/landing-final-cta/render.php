<?php
/** @var array $attributes @var string $content */
$extra = isset($attributes['className']) ? ' ' . $attributes['className'] : '';
$wrapper = get_block_wrapper_attributes([
  'class' => 'mh-landing-section mh-landing-section--final' . $extra,
  'id'    => $attributes['anchor'] ?? 'tarjous',
]);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
