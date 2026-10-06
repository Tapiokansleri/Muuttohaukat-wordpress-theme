<?php
/** @var array $attributes */
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-section', 'mh-calendar-section'], [
  'id' => $attributes['anchor'] ?? 'muuttopaiva',
]);
?>
<section <?php echo $wrapper; ?>>
  <?php echo \Muuttohaukat\calendar_section_inner_html(!empty($attributes['showHighlight'])); ?>
</section>
