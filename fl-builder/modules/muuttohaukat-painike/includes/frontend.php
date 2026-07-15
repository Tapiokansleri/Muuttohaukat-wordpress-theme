<?php
/**
 * Frontend template for Muuttohaukat painike module.
 */

$buttons = array();

if ( ! empty( $settings->button_1_text ) ) {
	$buttons[] = array(
		'text'   => $settings->button_1_text,
		'url'    => isset( $settings->button_1_url ) ? $settings->button_1_url : '',
		'target' => isset( $settings->button_1_target ) ? $settings->button_1_target : '_self',
		'color'  => isset( $settings->button_1_color ) ? $settings->button_1_color : 'yellow',
	);
}

if ( ! empty( $settings->button_2_text ) ) {
	$buttons[] = array(
		'text'   => $settings->button_2_text,
		'url'    => isset( $settings->button_2_url ) ? $settings->button_2_url : '',
		'target' => isset( $settings->button_2_target ) ? $settings->button_2_target : '_self',
		'color'  => isset( $settings->button_2_color ) ? $settings->button_2_color : 'black',
	);
}

if ( empty( $buttons ) ) {
	return;
}
?>
<div class="mh-painike-wrap">
	<?php foreach ( $buttons as $btn ) :
		$color_class = 'yellow' === $btn['color'] ? 'mh-painike--yellow' : 'mh-painike--black';
		$target      = '_blank' === $btn['target'] ? '_blank' : '_self';
		$rel         = '_blank' === $target ? ' rel="noopener noreferrer"' : '';
	?>
		<a class="mh-painike <?php echo esc_attr( $color_class ); ?>"
		   href="<?php echo esc_url( $btn['url'] ); ?>"
		   target="<?php echo esc_attr( $target ); ?>"<?php echo $rel; ?>>
			<?php echo esc_html( $btn['text'] ); ?>
		</a>
	<?php endforeach; ?>
</div>
