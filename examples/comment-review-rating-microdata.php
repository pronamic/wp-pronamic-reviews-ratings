<?php
/**
 * Comment review microdata.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

/*
 * Comment with microdata.
 *
 * @link http://schema.org/Review
 * @link http://schema.org/Rating
 * @link https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L1742
 */
?>
<li <?php \comment_class(); ?> id="li-comment-<?php \comment_ID(); ?>">
	<div itemprop="review" itemscope itemtype="http://schema.org/Review">
		<span itemprop="name">
			<?php

			\printf(
				/* translators: %d: comment ID */
				\esc_html( __( 'Comment %d', 'text_domain' ) ),
				\esc_html( \get_comment_ID() )
			);

			?>
		</span> -
		by <span itemprop="author"><?php \comment_author(); ?></span>,

		<meta itemprop="datePublished" content="<?php \comment_date( 'Y-m-d' ); ?>" /><?php \comment_date(); ?>

		<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
			<meta itemprop="worstRating" content="1" />
			<span itemprop="ratingValue"><?php echo \esc_html( \get_comment_meta( \get_comment_ID(), '_pronamic_rating_value', true ) ); ?></span>/
			<span itemprop="bestRating">10</span> stars
		</div>

		<div class="comment-content" itemprop="description">
			<?php \comment_text(); ?>
		</div>
	</div>
</li>
