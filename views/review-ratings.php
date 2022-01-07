<?php
/**
 * Review ratings.
 *
 * @package Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

$rating_types = Util::get_review_rating_types( \get_the_ID() );

if ( empty( $rating_types ) ) {
	return;
}

// Scores.
$object_post_id = \get_post_meta( \get_the_ID(), '_pronamic_review_object_post_id', true );

$object_post_type = null;

if ( ! empty( $object_post_id ) ) {
	$object_post_type = \get_post_type( $object_post_id );

	if ( false === $object_post_type ) {
		$object_post_type = null;
	}
}

$scores = Util::get_post_type_ratings_scores( $object_post_type );

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
				\esc_html( __( 'Rating', 'pronamic_reviews_ratings' ) ),
				\esc_html( Util::format_rating( $rating ) )
			);
		}

		?>
	</dl>
</div>
