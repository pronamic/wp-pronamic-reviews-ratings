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

$rating_types = Util::get_review_rating_types( \get_the_ID() );

// Select2
wp_enqueue_style( 'select2' );
wp_enqueue_script( 'select2' );

$search_results_rest_endpoint = \rest_url() . 'wp/v2/search/';

?>
<script>
	jQuery( document ).ready(
		function( $ ) {
			const reviewOjectField = document.getElementById( 'pronamic-review-object-id' );

			$( reviewOjectField ).select2(
				{
					allowClear: true,
					ajax: {
						url: '<?php echo esc_url( $search_results_rest_endpoint ); ?>',
						dataType: 'json',
						delay: 250,
						minimumInputLength: 2,
						data: function( params ) {
							return {
								search: params.term,
								per_page: 20
							};
						},
						processResults: function( data, params ) {
							return {
								results: data.map(
									post => (
										{
											id: post.id,
											text: post.title
										}
									)
								)
							};
						},
						cache: true
					}
				}
			);
		}
	);
</script>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="pronamic-review-object-id">
				<?php esc_html_e( 'Reviewed object post ID', 'pronamic_reviews_ratings' ); ?>
			</label>
		</th>
		<td>
			<?php

			$object_post_id = \get_post_meta( get_the_ID(), '_pronamic_review_object_post_id', true );

			?>
			<select class="regular-text" id="pronamic-review-object-id" name="pronamic_review_object_post_id" data-placeholder="<?php esc_attr_e( 'Select post…', 'pronamic_reviews_ratings' ); ?>">
				<option value="" <?php echo empty( $object_post_id ) ? 'selected' : ''; ?>>
					<?php esc_html_e( 'Select a post...', 'pronamic_reviews_ratings' ); ?>
				</option>

				<?php if ( ! empty( $object_post_id ) ) : ?>

					<option value="<?php echo esc_attr( $object_post_id ); ?>" selected>
						<?php echo esc_html( get_the_title( $object_post_id ) ); ?>
					</option>

				<?php endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="pronamic-review-author">
				<?php esc_html_e( 'Author', 'pronamic_reviews_ratings' ); ?>
			</label>
		</th>
		<td>
			<?php

			$author = \get_post_meta( \get_the_ID(), '_pronamic_review_author', true );

			$atts = array(
				'id'    => 'pronamic-review-author',
				'name'  => 'pronamic_review_author',
				'type'  => 'text',
				'value' => $author,
				'class' => 'regular-text',
			);

			\printf(
				'<input %s />',
				// @codingStandardsIgnoreStart
				Util::array_to_html_attributes( $atts )
				// @codingStandardsIgnoreEnd
			);

			?>
		</td>
	</tr>

	<?php

	// Scores.
	$object_post_id = (int) \get_post_meta( \get_the_ID(), '_pronamic_review_object_post_id', true );

	$object_post_type = empty( $object_post_id ) ? null : \get_post_type( $object_post_id );

	$scores = Util::get_post_type_ratings_scores( $object_post_type );

	?>

	<?php foreach ( $rating_types as $rating_type ) : ?>

		<?php

		$name  = $rating_type['name'];
		$label = \array_key_exists( 'label', $rating_type ) && ! empty( $rating_type['label'] ) ? $rating_type['label'] : $rating_type['name'];

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
					'type'  => 'number',
					'value' => \get_post_meta( \get_the_ID(), '_pronamic_rating_value_' . $name, true ),
					'min'   => min( $scores ),
					'max'   => max( $scores ),
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
				echo Util::format_rating( $rating );
			}

			?>
		</td>
	</tr>
</table>