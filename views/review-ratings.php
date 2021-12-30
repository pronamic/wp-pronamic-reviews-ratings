<?php
/**
 * Review ratings.
 *
 * @package Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

$object_post_id = \get_post_meta( \get_the_ID(), '_pronamic_review_object_post_id', true );

if ( empty( $object_post_id ) ) {
	return;
}

$object_post_type = \get_post_type( $object_post_id );

$rating_types = \pronamic_get_rating_types( $object_post_type );

if ( empty( $rating_types ) ) {
	return;
}

$scores = \apply_filters( 'pronamic_reviews_ratings_scores', range( 1, 10 ) );
$scores = \apply_filters( 'pronamic_reviews_ratings_scores_' . $object_post_type, $scores );

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

		$rating = \get_post_meta( \get_the_ID(), '_pronamic_rating', true );

		if ( ! empty( $rating ) ) {
			\printf(
				'<dt>%s</dt><dd>%s</dd>',
				\esc_html( __( 'Rating', 'pronamic_review_ratings' ) ),
				\esc_html( \number_format_i18n( $rating, 1 ) )
			);
		}

		?>
	</dl>
</div>
