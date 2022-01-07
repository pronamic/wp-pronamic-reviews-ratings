<?php
/**
 * Object ratings.
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

// Scores.
$scores = Util::get_post_type_ratings_scores( \get_post_type() );

?>
<div class="pronamic-review-ratings">
	<dl>

		<?php foreach ( $rating_types as $rating_type ) : ?>

			<?php

			$name   = $rating_type['name'];
			$label  = \array_key_exists( 'label', $rating_type ) && ! empty( $rating_type['label'] ) ? $rating_type['label'] : $rating_type['name'];
			$rating = \get_post_meta( \get_the_ID(), '_pronamic_rating_value_' . $rating_type['name'], true );

			if ( empty( $rating ) ) {
				continue;
			}

			?>

			<dt class="pronamic-review-ratings__term pronamic-review-ratings__term__<?php echo \esc_attr( $name ); ?>">
				<?php echo \esc_html( $label ); ?>
			</dt>

			<dd class="pronamic-review-ratings__description pronamic-review-ratings__description__<?php echo \esc_attr( $name ); ?>">
				<?php

				$max_score = max( $scores );

				for ( $i = 0; $i < $max_score; $i++ ) {
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

		$rating_value = \get_post_meta( \get_the_ID(), '_pronamic_rating_value', true );

		if ( ! empty( $rating_value ) ) {
			\printf(
				'<dt>%s</dt><dd>%s</dd>',
				\esc_html( __( 'Rating', 'pronamic_reviews_ratings' ) ),
				\esc_html( Util::format_rating( $rating_value ) )
			);
		}

		?>

		<?php

		$rating_count = (int) \get_post_meta( \get_the_ID(), '_pronamic_rating_count', true );

		if ( ! empty( $rating_count ) ) {
			\printf(
				'<dt>%s</dt><dd>%s</dd>',
				\esc_html( __( 'Number of reviews', 'pronamic_reviews_ratings' ) ),
				\esc_html( $rating_count )
			);
		}

		?>
	</dl>
</div>
