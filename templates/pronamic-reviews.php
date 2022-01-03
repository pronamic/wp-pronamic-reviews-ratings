<?php
/**
 * Pronamic reviews template.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

if ( ! isset( $query ) || ! ( $query instanceof \WP_Query ) ) {
	return;
}

if ( ! $query->have_posts() ) {
	return;
}

?>

<?php if ( $query->have_posts() ) : ?>

	<ul class="pronamic-reviews">

		<?php while ( $query->have_posts() ) : ?>

			<?php $query->the_post(); ?>

			<li>
				<div class="pronamic-reviews__review">

					<?php if ( has_post_thumbnail() ) : ?>

						<div class="pronamic-reviews__review__thumbnail">
							<figure>
								<?php the_post_thumbnail(); ?>
							</figure>
						</div>

					<?php endif; ?>

					<div class="pronamic-reviews__review__content">
						<h2>
							<?php the_title(); ?>
						</h2>

						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</li>

		<?php endwhile; ?>

	</ul>

<?php endif; ?>
