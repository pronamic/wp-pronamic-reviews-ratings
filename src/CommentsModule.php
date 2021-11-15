<?php
/**
 * Comments Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Comments Module
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class CommentsModule {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct comments module.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		/*
		 * Actions.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment.php#L1687
		 */
		\add_filter( 'preprocess_comment', array( $this, 'validate_ratings' ), 0 );
		\add_action( 'preprocess_comment', array( $this, 'update_comment_type' ) );

		/*
		 * Comment form actions.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L2068
		 * @link https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L2101
		 */
		\add_action( 'comment_form_logged_in_after', array( $this, 'comment_form_expansion' ) );
		\add_action( 'comment_form_after_fields', array( $this, 'comment_form_expansion' ) );
	}

	/**
	 * Validate comment ratings.
	 *
	 * @param array $commentdata Comment data.
	 * @return array
	 */
	public function validate_ratings( $commentdata ) {
		if ( ! \filter_has_var( \INPUT_POST, 'pronamic_review' ) ) {
			return $commentdata;
		}

		$post_id = $commentdata['comment_post_ID'];

		$ratings = \filter_input( \INPUT_POST, 'scores', \FILTER_VALIDATE_INT, \FILTER_REQUIRE_ARRAY );

		$types = \pronamic_get_rating_types( \get_post_type( $post_id ) );

		foreach ( $types as $name => $label ) {
			if ( ! isset( $ratings[ $name ] ) || empty( $ratings[ $name ] ) ) {
				// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-comments-post.php#L121
				\wp_die( \wp_kses_post( \__( '<strong>ERROR</strong>: please fill in the rating fields.', 'pronamic_reviews_ratings' ) ) );

				exit;
			}
		}

		return $commentdata;
	}

	/**
	 * Update comment type
	 *
	 * @param array $commentdata Comment data.
	 * @return array
	 */
	public function update_comment_type( $commentdata ) {
		if ( \filter_has_var( \INPUT_POST, 'pronamic_review' ) ) {
			$commentdata['comment_type'] = 'pronamic_review';
		}

		return $commentdata;
	}

	/**
	 * Comment Form expansion.
	 *
	 *  @see https://github.com/WordPress/WordPress/blob/3.8.1/wp-includes/comment-template.php#L1920
	 */
	public function comment_form_expansion() {
		$post_id   = \get_the_ID();
		$post_type = \get_post_type( $post_id );

		if ( \post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			require_once $this->plugin->dir_path . 'templates/comment-form-ratings.php';
		}
	}
}
