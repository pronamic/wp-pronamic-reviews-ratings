<?php
/**
 * Comment meta box ratings.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

use Pronamic\WordPress\ReviewsRatings\Util;

$product_post_id = \get_post_meta( \get_the_ID(), '_pronamic_review_product_id', true );

$rating_types = \pronamic_get_rating_types( \get_post_type( $product_post_id ) );

?>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="pronamic-review-product-id">
				<?php esc_html_e( 'Reviewed product post ID', 'pronamic_reviews_ratings' ); ?>
			</label>
		</th>
		<td>
			<?php

			$product_post_id = \get_post_meta( get_the_ID(), '_pronamic_review_product_id', true );

			$atts = array(
					'id'    => 'pronamic-review-product-id',
					'name'  => 'pronamic_review_product_id',
					'type'  => 'text',
					'value' => $product_post_id,
			);


			// Edit product post link.
			$edit_product_post_link = '';

			if ( ! empty( $product_post_id ) ) {
				$edit_product_post_link = __( 'No product post found with this ID.', 'pronamic_reviews_ratings' );

				$product_post = \get_post( $product_post_id );

				if ( $product_post instanceof \WP_Post ) {
					$edit_product_post_link = \sprintf(
						'<a href="%1$s" title="%2$s">%2$s</a>',
						\get_edit_post_link( $product_post_id ),
						\get_the_title( $product_post_id )
					);
				}
			}

			\printf(
				'<input %s /> %s',
				// @codingStandardsIgnoreStart
				Util::array_to_html_attributes( $atts ),
				// @codingStandardsIgnoreEn,
				\wp_kses_post( $edit_product_post_link )
			);

			?>
		</td>
	</tr>

	<?php foreach ( $rating_types as $type ) : ?>

		<?php

		$name = $type['name'];
		$label = \array_key_exists( 'label', $type ) && ! empty( $type['label'] ) ? $type['label'] : $type['name'];

		?>

		<tr>
			<th scope="row">
				<label for="pronamic-review-rating-<?php echo \esc_attr( $name ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			</th>
			<td>
				<?php

				$atts = array(
					'id'    => \sprintf( 'pronamic-review-rating-%s', $name ),
					'name'  => \sprintf( 'pronamic_review_rating[%s]', $name ),
					'type'  => 'text',
					'value' => \get_post_meta( get_the_ID(), '_pronamic_rating_value_' . $name, true ),
				);

				\printf(
					'<input %s />',
					// @codingStandardsIgnoreStart
					Util::array_to_html_attributes( $atts )
					// @codingStandardsIgnoreEn
				);

				?>
			</td>
		</tr>

	<?php endforeach; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Rating', 'pronamic_ratings_review' ); ?>
		</th>
		<td>
			<?php

			$rating = \get_post_meta( get_the_ID(), '_pronamic_rating', true );

			if ( empty( $rating ) ) {
				echo '&mdash;';
			} else {
				echo number_format_i18n( $rating, 1 );
			}

			?>
		</td>
	</tr>
</table>