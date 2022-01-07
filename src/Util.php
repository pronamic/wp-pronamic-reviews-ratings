<?php
/**
 * Utilities.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Utilities.
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class Util {
	/**
	 * Array to HTML attributes.
	 *
	 * @param array $attributes Key and value pairs to convert to HTML attributes.
	 * @return string
	 */
	public static function array_to_html_attributes( array $attributes ) {
		$html = '';

		foreach ( $attributes as $key => $value ) {
			// Check boolean attribute.
			if ( \is_bool( $value ) ) {
				if ( $value ) {
					$html .= sprintf( '%s ', $key );
				}

				continue;
			}

			$html .= sprintf( '%s="%s" ', $key, esc_attr( $value ) );
		}

		$html = trim( $html );

		return $html;
	}

	/**
	 * Get rating types in use for a review.
	 *
	 * @param int|false $post_id Post ID.
	 * @return array<string, mixed>
	 */
	public static function get_review_rating_types( $post_id ) {
		$object_post_id = \get_post_meta( $post_id, '_pronamic_review_object_post_id', true );

		// Return global rating types if object post ID is empty.
		if ( empty( $object_post_id ) ) {
			return \pronamic_get_rating_types( 'global' );
		}

		// Return rating types for post type.
		$type = \get_post_type( $object_post_id );

		return \pronamic_get_rating_types( $type );
	}

	/**
	 * Get scores.
	 *
	 * @param string|bool|null $post_type Optional post type to get scores for.
	 * @return array
	 */
	public static function get_post_type_ratings_scores( $post_type = null ) {
		$scores = \apply_filters( 'pronamic_reviews_ratings_scores', range( 1, 10 ) );

		// Scores for object post type.
		if ( ! empty( $post_type ) ) {
			$filtered_scores = \apply_filters( 'pronamic_reviews_ratings_scores_' . $post_type, $scores );

			if ( \is_array( $filtered_scores ) ) {
				$scores = $filtered_scores;
			}
		}

		return $scores;
	}

	/**
	 * Format rating, without decimals if possible.
	 *
	 * @param string|int|float $rating Rating.
	 * @return string
	 */
	public static function format_rating( $rating ) {
		$decimals_zero = \number_format_i18n( $rating, 0 );
		$decimals_one  = \number_format_i18n( $rating, 1 );

		if ( \number_format_i18n( $decimals_zero, 1 ) === $decimals_one ) {
			return $decimals_zero;
		}

		return $decimals_one;
	}
}
