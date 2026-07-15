<?php
/**
 * Landing: Muutot ja kuljetukset
 *
 * @var array  $attributes
 * @var string $content
 */
$extra = isset($attributes['className']) ? ' ' . $attributes['className'] : '';
$wrapper = get_block_wrapper_attributes([
  'class' => 'mh-landing-section' . $extra,
  'id'    => $attributes['anchor'] ?? 'palvelu',
]);
?>
<section <?php echo $wrapper; ?>><?php echo $content; ?></section>
