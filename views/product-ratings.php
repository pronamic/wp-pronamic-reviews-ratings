<?php
/**
 * Product ratings.
 *
 * @package Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

if ( ! \post_type_supports( \get_post_type(), 'pronamic_ratings' ) ) {
	return;
}

$rating_types = \pronamic_get_rating_types( \get_post_type() );

if ( empty( $rating_types ) ) {
	return;
}

$scores = \apply_filters( 'pronamic_reviews_ratings_scores', range( 1, 10 ) );
$scores = \apply_filters( 'pronamic_reviews_ratings_scores_' . \get_post_type(), $scores );

?>
<div class="pronamic-review-ratings">
	<dl>

		<?php foreach ( $rating_types as $type ) : ?>

			<?php

			$name   = $type['name'];
			$label  = \array_key_exists( 'label', $type ) && ! empty( $type['label'] ) ? $type['label'] : $type['name'];
			$rating = \get_post_meta( \get_the_ID(), '_pronamic_rating_value_' . $type['name'], true );

			if ( empty( $rating ) ) {
				continue;
			}

			?>

			<dt class="pronamic-review-ratings__term pronamic-review-ratings__term__<?php echo \esc_attr( $name ); ?>">
				<?php echo \esc_html( $label ); ?>
			</dt>

			<dd class="pronamic-review-ratings__description pronamic-review-ratings__description__<?php echo \esc_attr( $name ); ?>">
				<?php


				for ( $i = 0; $i < max( $scores ); $i++ ) {
					$value = $rating - $i;

					$class = 'empty';

					if ( $value >= 1 ) {
						$class = 'filled';
					} elseif ( $value >= 0.5 ) {
						$class = 'half';
					}

					\printf(
						'<span class="dashicons dashicons-star-%s"></span>',
						\esc_attr( $class )
					);
				}

				?>
			</dd>

		<?php endforeach; ?>

		<?php

		$rating_value = \get_post_meta( get_the_ID(), '_pronamic_rating_value', true );

		if ( ! empty( $rating_value ) ) {
			printf(
					'<dt>%s</dt><dd>%s</dd>',
					__( 'Rating', 'pronamic_review_ratings' ),
					number_format_i18n( $rating_value, 1 )
			);
		}

		?>

		<?php

		$rating_count = \get_post_meta( get_the_ID(), '_pronamic_rating_count', true );

		if ( ! empty( $rating_count ) ) {
			printf(
					'<dt>%s</dt><dd>%s</dd>',
					__( 'Number of reviews', 'pronamic_review_ratings' ),
					number_format_i18n( $rating_count )
			);
		}

		?>
	</dl>
</div>
