<?php
/**
 * Henkilöstö block: a staff group or named people (person posts, inc/Staff.php).
 *
 * @var array $attributes
 */
$names   = array_values(array_filter(array_map('trim', explode(',', (string) ($attributes['nimet'] ?? '')))));
$group   = sanitize_title((string) ($attributes['ryhma'] ?? ''));
$persons = $names ? \Muuttohaukat\staff_persons('', $names) : ($group !== '' ? \Muuttohaukat\staff_persons($group) : []);
$people  = \Muuttohaukat\staff_render($persons, 'henkilosto-' . substr(md5($group . implode(',', $names)), 0, 8), '', (int) ($attributes['sarakkeet'] ?? 2));

if ($people === '') {
  return;
}
echo '<div ' . get_block_wrapper_attributes(['class' => 'mh-landing-local__staff mh-staff-group']) . '>' . $people . '</div>';
