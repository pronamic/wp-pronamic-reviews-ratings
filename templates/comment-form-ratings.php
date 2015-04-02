<?php

$post_type = get_post_type();

$types  = pronamic_get_rating_types( $post_type );
$scores = apply_filters( 'pronamic_reviews_ratings_scores', range( 1, 10 ) );

?>
<div class="pronamic-comment-ratings">

	<?php foreach ( $types as $name => $label ) : ?>

		<div class="pronamic-comment-rating-<?php echo $name; ?>">
			<span class="pronamic-comment-rating-label"><?php echo $label; ?></span>

			<span class="pronamic-comment-rating-control"><?php

			$input_name = 'scores[' . $name . ']';

			foreach ( $scores as $value ) {
				$input_id   = 'score-' . $name . '-' . $value;

				printf(
					'<input id="%s" name="%s" value="%d" type="radio" class="star"/>',
					esc_attr( $input_id ),
					esc_attr( $input_name ),
					esc_attr( $value )
				);

				echo ' ';

				printf(
					'<label for="%s">%s</label>',
					esc_attr( $input_id ),
					esc_html( $value )
				);

				echo ' ';
			}

			?></span>
		</div>

	<?php endforeach; ?>

</div>
