<?php
/**
 * Frontend template for Usein kysyttyä module
 */

$questions_text = isset( $settings->questions_text ) ? $settings->questions_text : '';
$questions = FLUseinKysyttyaModule::parse_questions( $questions_text );

if ( empty( $questions ) ) {
	return;
}

$title   = isset( $settings->title ) && ! empty( $settings->title ) ? esc_html( $settings->title ) : 'Usein kysyttyä';
$columns = isset( $settings->columns ) ? absint( $settings->columns ) : 2;
$used_question_anchor_ids = array();
?>

<div class="fl-usein-kysyttya" data-module-id="<?php echo esc_attr( $module->node ); ?>">
	<?php if ( ! empty( $title ) ) : ?>
		<h2 class="fl-usein-kysyttya-title"><?php echo $title; ?></h2>
	<?php endif; ?>
	
	<div class="fl-usein-kysyttya-grid" style="--columns: <?php echo esc_attr( $columns ); ?>;">
		<?php foreach ( $questions as $index => $question ) : 
			if ( empty( $question->question ) || empty( $question->answer ) ) {
				continue;
			}
			
			$question_id = 'faq-' . $module->node . '-' . $index;
			$question_anchor_id = sanitize_title( $question->question );
			if ( '' === $question_anchor_id ) {
				$question_anchor_id = 'kysymys-' . $index;
			}
			$base_question_anchor_id = $question_anchor_id;
			$duplicate_index = 2;
			while ( in_array( $question_anchor_id, $used_question_anchor_ids, true ) ) {
				$question_anchor_id = $base_question_anchor_id . '-' . $duplicate_index;
				$duplicate_index++;
			}
			$used_question_anchor_ids[] = $question_anchor_id;
			?>
			<div class="fl-usein-kysyttya-item" id="<?php echo esc_attr( $question_anchor_id ); ?>">
				<button class="fl-usein-kysyttya-question" 
				        aria-expanded="false" 
				        aria-controls="<?php echo esc_attr( $question_id ); ?>"
				        data-target="<?php echo esc_attr( $question_id ); ?>"
				        type="button">
					<div class="fl-usein-kysyttya-question-content">
						<span class="fl-usein-kysyttya-question-text"><?php echo esc_html( $question->question ); ?></span>
					</div>
					<span class="fl-usein-kysyttya-chevron" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
				</button>
				<div class="fl-usein-kysyttya-answer" 
				     id="<?php echo esc_attr( $question_id ); ?>"
				     aria-hidden="true">
					<div class="fl-usein-kysyttya-answer-content">
						<?php echo wp_kses_post( $question->answer ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
