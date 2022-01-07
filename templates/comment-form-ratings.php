<?php
/**
 * Comment form ratings template.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

use Pronamic\WordPress\ReviewsRatings\Util;

$types = \pronamic_get_rating_types( \get_post_type() );

$scores = Util::get_post_type_ratings_scores( \get_post_type() );

?>
<div class="pronamic-comment-ratings">

	<?php foreach ( $types as $name => $label ) : ?>

		<div class="pronamic-comment-rating-<?php echo \esc_attr( $name ); ?>">
			<span class="pronamic-comment-rating-label"><?php echo \esc_html( $label ); ?></span>

			<span class="pronamic-comment-rating-control">
				<?php

				$input_name = 'scores[' . $name . ']';

				foreach ( $scores as $value ) {
					$input_id = 'score-' . $name . '-' . $value;

					\printf(
						'<input id="%s" name="%s" value="%d" type="radio" class="star"/>',
						\esc_attr( $input_id ),
						\esc_attr( $input_name ),
						\esc_attr( $value )
					);

					echo ' ';

					\printf(
						'<label for="%s">%s</label>',
						\esc_attr( $input_id ),
						\esc_html( $value )
					);

					echo ' ';
				}

				?>
			</span>
		</div>

	<?php endforeach; ?>

</div>
