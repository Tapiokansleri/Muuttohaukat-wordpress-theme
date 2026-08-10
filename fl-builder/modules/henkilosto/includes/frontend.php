<?php
/**
 * Frontend template for Henkilöstö module.
 *
 * @var object $settings
 * @var FLHenkilostoModule $module
 */

$heading     = isset( $settings->heading ) ? trim( (string) $settings->heading ) : '';
$heading_tag = isset( $settings->heading_tag ) ? $settings->heading_tag : 'h2';
$allowed_tags = array( 'h2', 'h3', 'h4' );
if ( ! in_array( $heading_tag, $allowed_tags, true ) ) {
	$heading_tag = 'h2';
}

$columns = isset( $settings->columns ) ? absint( $settings->columns ) : 3;
if ( $columns < 1 ) {
	$columns = 1;
}
if ( $columns > 3 ) {
	$columns = 3;
}

$persons = isset( $settings->persons ) && is_array( $settings->persons ) ? $settings->persons : array();
$persons = array_values( array_filter( $persons, static function ( $person ) {
	if ( ! is_object( $person ) ) {
		return false;
	}
	$name  = isset( $person->name ) ? trim( (string) $person->name ) : '';
	$photo = isset( $person->photo ) ? $person->photo : '';
	return ( '' !== $name || ! empty( $photo ) );
} ) );

if ( '' === $heading && empty( $persons ) ) {
	return;
}
?>

<div class="mh-henkilosto" data-module-id="<?php echo esc_attr( $module->node ); ?>">
	<?php if ( '' !== $heading ) : ?>
		<<?php echo tag_escape( $heading_tag ); ?> class="mh-henkilosto__heading">
			<?php echo esc_html( $heading ); ?>
		</<?php echo tag_escape( $heading_tag ); ?>>
	<?php endif; ?>

	<?php if ( ! empty( $persons ) ) : ?>
		<ul class="mh-henkilosto__grid" style="--mh-henkilosto-columns: <?php echo esc_attr( (string) $columns ); ?>;">
			<?php foreach ( $persons as $person ) :
				$name             = isset( $person->name ) ? trim( (string) $person->name ) : '';
				$role             = isset( $person->role ) ? trim( (string) $person->role ) : '';
				$responsibilities = isset( $person->responsibilities ) ? trim( (string) $person->responsibilities ) : '';
				$phone            = isset( $person->phone ) ? trim( (string) $person->phone ) : '';
				$email            = isset( $person->email ) ? trim( (string) $person->email ) : '';
				$photo_id         = isset( $person->photo ) ? absint( $person->photo ) : 0;
				$photo_src        = isset( $person->photo_src ) ? (string) $person->photo_src : '';
				$phone_href       = FLHenkilostoModule::phone_href( $phone );
				$email_href       = $email ? 'mailto:' . antispambot( $email ) : '';
				?>
				<li class="mh-henkilosto__person">
					<div class="mh-henkilosto__media">
						<?php if ( $photo_id ) : ?>
							<?php
							echo wp_get_attachment_image(
								$photo_id,
								'medium_large',
								false,
								array(
									'class'   => 'mh-henkilosto__photo',
									'loading' => 'lazy',
									'alt'     => $name ? $name : '',
								)
							);
							?>
						<?php elseif ( $photo_src ) : ?>
							<img
								class="mh-henkilosto__photo"
								src="<?php echo esc_url( $photo_src ); ?>"
								alt="<?php echo esc_attr( $name ); ?>"
								loading="lazy"
								decoding="async"
							/>
						<?php else : ?>
							<span class="mh-henkilosto__photo mh-henkilosto__photo--placeholder" aria-hidden="true"></span>
						<?php endif; ?>
					</div>

					<div class="mh-henkilosto__info">
						<?php if ( '' !== $name ) : ?>
							<p class="mh-henkilosto__name"><?php echo esc_html( $name ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $role ) : ?>
							<p class="mh-henkilosto__role"><?php echo esc_html( $role ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $responsibilities ) : ?>
							<p class="mh-henkilosto__focus"><?php echo esc_html( $responsibilities ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $phone ) : ?>
							<p class="mh-henkilosto__phone">
								<span class="mh-henkilosto__icon" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
									</svg>
								</span>
								<?php if ( $phone_href ) : ?>
									<a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone ); ?></a>
								<?php else : ?>
									<span><?php echo esc_html( $phone ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<?php if ( '' !== $email ) : ?>
							<p class="mh-henkilosto__email">
								<span class="mh-henkilosto__icon" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
										<polyline points="22,6 12,13 2,6"/>
									</svg>
								</span>
								<a href="<?php echo esc_url( $email_href ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
							</p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
