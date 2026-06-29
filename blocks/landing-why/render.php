<?php
/** @var array $attributes @var string $content */
$extra = isset($attributes['className']) ? ' ' . $attributes['className'] : '';
$wrapper = get_block_wrapper_attributes([
  'class' => 'mh-landing-section' . $extra,
  'id'    => $attributes['anchor'] ?? 'miksi',
]);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
