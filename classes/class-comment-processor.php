<?php

/**
 * Comment processor
 */
class Pronamic_WP_ReviewsRatingsCommentProcessor {
	/**
	 * Construct and initalize comment processor
	 */
	public function __construct() {
		// Actions
		// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment.php#L1687
		add_filter( 'preprocess_comment', array( $this, 'validate_ratings' ), 0 );
		add_action( 'preprocess_comment', array( $this, 'update_comment_type' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Validate comment ratings
	 *
	 * @param array $commentdata
	 * @return array
	 */
	public function validate_ratings( $commentdata ) {
		if ( filter_has_var( INPUT_POST, 'pronamic_review' ) ) {
			$ratings = filter_input( INPUT_POST, 'scores', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

			$types = pronamic_get_rating_types();

			foreach ( $types as $name => $label ) {
				if ( ! isset( $ratings[ $name ] ) || empty( $ratings[ $name ] ) ) {
					// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-comments-post.php#L121
					wp_die( __( '<strong>ERROR</strong>: please fill in the rating fields.', 'pronamic_reviews_ratings' ) );

					exit;
				}
			}
		}

		return $commentdata;
	}

	//////////////////////////////////////////////////

	/**
	 * Update comment type
	 *
	 * @param array $commentdata
	 * @return array
	 */
	public function update_comment_type( $commentdata ) {
		if ( filter_has_var( INPUT_POST, 'pronamic_review' ) ) {
			$commentdata['comment_type'] = 'pronamic_review';
		}

		return $commentdata;
	}
}
