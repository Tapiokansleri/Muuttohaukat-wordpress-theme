<?php
/**
 * Nearby towns ("Muutot myös lähikuntiin", "Muuttolaatikot myös lähikuntiin")
 * in the layout of the locality directory: the block's heading and text with
 * the area page button on the left, the towns in columns on the right
 * (\Muuttohaukat\nearby_directory_html() in inc/CityDirectory.php).
 *
 * @var array $attributes @var string $content
 */
$links = is_array($attributes['links'] ?? null) ? $attributes['links'] : [];
$max   = (int) ($attributes['max'] ?? 15);
$max   = $max > 0 ? min($max, 40) : 15; // city pages 15 (default), office area pages list the whole area
$links = array_slice($links, 0, $max);
$hub       = trim((string) ($attributes['hub'] ?? ''));
$hub_label = trim((string) ($attributes['hubLabel'] ?? ''));
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-section', 'mh-landing-nearby'], [
  'id' => $attributes['anchor'] ?? 'lahikunnat',
]);

if (!$links && !$hub) {
  return;
}
?>
<section <?php echo $wrapper; ?>>
  <div class="mh-landing__inner">
    <?php echo \Muuttohaukat\nearby_directory_html((string) $content, $links, $hub, $hub_label); ?>
  </div>
</section>
