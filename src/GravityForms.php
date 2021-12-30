<?php
/**
 * Gravity Forms support
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

use GFAPI;

/**
 * Gravity Forms support
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class GravityForms {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct Gravity Forms support.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		\add_action( 'admin_init', array( $this, 'admin_init' ), 0 );
	}

	/**
	 * Admin.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Actions.
		\add_action( 'gform_editor_js', array( $this, 'gform_editor_js' ) );
		\add_action( 'gform_entry_detail_sidebar_middle', array( $this, 'gform_entry_detail_sidebar_middle' ), 10, 2 );
		\add_action( 'gform_field_advanced_settings', array( $this, 'gform_field_advanced_settings' ), 10, 2 );

		$this->maybe_create_review();
		$this->maybe_create_review_post();
	}

	/**
	 * Gravity Forms - Field advanced settings
	 *
	 * @param int $position Settings position.
	 * @param int $form_id Gravity Forms form ID.
	 * @return void
	 */
	public function gform_field_advanced_settings( $position, $form_id ) {
		// Check settings position.
		if ( 100 != $position ) {
			return;
		}

		// Require advanced field settings.
		require_once $this->plugin->dir_path . '/admin/gravityforms/field-advanced-settings.php';
	}

	/**
	 * Gravity Forms - Editor JavaScript
	 *
	 * @return void
	 */
	public function gform_editor_js() {
		// Require form editor script.
		require_once $this->plugin->dir_path . '/admin/gravityforms/form-editor-js.php';
	}

	/**
	 * Entry detail sidebar middle
	 *
	 * @param array $form  Gravity Forms form.
	 * @param array $entry Gravity Forms entry.
	 * @return void
	 */
	public function gform_entry_detail_sidebar_middle( $form, $entry ) {
		// Require entry detail review meta box.
		require_once $this->plugin->dir_path . '/admin/gravityforms/meta-box-entry-detail-review.php';
	}

	/**
	 * Maybe create review for Gravity Forms entry.
	 *
	 * @return void
	 */
	private function maybe_create_review() {
		// Check create review action.
		if ( ! \filter_has_var( \INPUT_POST, 'pronamic_create_review' ) ) {
			return;
		}

		// New comment.
		$comment_id = \wp_new_comment(
			array(
				'comment_post_ID'      => \filter_input( \INPUT_POST, 'pronamic_review_post_id', \FILTER_SANITIZE_STRING ),
				'comment_author'       => \filter_input( \INPUT_POST, 'pronamic_review_name', \FILTER_SANITIZE_STRING ),
				'comment_author_email' => \filter_input( \INPUT_POST, 'pronamic_review_email', \FILTER_SANITIZE_EMAIL ),
				'comment_author_url'   => '',
				'comment_content'      => \filter_input( \INPUT_POST, 'pronamic_review_comment', \FILTER_SANITIZE_STRING ),
				'comment_type'         => 'pronamic_review',
			)
		);

		// Update entry meta.
		$entry_id = \filter_input( \INPUT_POST, 'pronamic_review_lead_id', \FILTER_SANITIZE_STRING );

		\gform_update_meta( $entry_id, 'pronamic_review_id', $comment_id );

		// Redirect.
		$url = \add_query_arg( 'pronamic_review_id', $comment_id );

		\wp_safe_redirect( $url );

		exit;
	}

	/**
	 * Maybe create review post for Gravity Forms entry.
	 *
	 * @return void
	 */
	private function maybe_create_review_post() {
		// Check create review action.
		if ( ! \filter_has_var( \INPUT_POST, 'pronamic_create_review_post' ) ) {
			return;
		}

		// Meta input.
		$meta_input = array(
			'_pronamic_review_author'         => \filter_input( \INPUT_POST, 'pronamic_review_name', \FILTER_SANITIZE_STRING ),
			'_pronamic_review_object_post_id' => \filter_input( \INPUT_POST, 'pronamic_review_post_id', \FILTER_SANITIZE_STRING ),
		);

		// Ratings.
		$rating_types = \pronamic_get_rating_types();

		$scores = array();

		$form = GFAPI::get_form( \filter_input( \INPUT_GET, 'id', \FILTER_VALIDATE_INT ) );

		$entry = GFAPI::get_entry( \filter_input( \INPUT_GET, 'lid', \FILTER_VALIDATE_INT ) );

		foreach ( $form['fields'] as $field ) {
			// Check field rating type.
			if ( ! isset( $field['pronamicRatingType'] ) || empty( $field['pronamicRatingType'] ) ) {
				continue;
			}

			// Add score for rating type.
			$rating_type = $field['pronamicRatingType'];

			if ( ! \array_key_exists( $rating_type, $scores ) ) {
				$scores[ $rating_type ] = array();
			}

			$score = \prr_gform_get_score( $entry, $field );

			$scores[ $rating_type ][] = $score;
		}

		foreach ( $rating_types as $rating_type ) :
			if ( ! array_key_exists( $rating_type['name'], $scores ) ) {
				continue;
			}

			$name   = $rating_type['name'];
			$values = $scores[ $name ];
			$sum    = \array_sum( $values );
			$count  = \count( $values );
			$value  = $count > 0 ? $sum / $count : '';

			$meta_input[ '_pronamic_rating_value_' . $name ] = $value;
		endforeach;

		// Insert post arguments.
		$args = array(
			'post_title'   => \filter_input( \INPUT_POST, 'pronamic_review_title', \FILTER_SANITIZE_STRING ),
			'post_content' => \filter_input( \INPUT_POST, 'pronamic_review_comment', \FILTER_SANITIZE_STRING ),
			'meta_input'   => $meta_input,
		);

		$post_id = $this->plugin->ratings_controller->create_review_post( $args );

		if ( $post_id instanceof \WP_Error ) {
			return;
		}

		// Redirect.
		$url = \get_permalink( $post_id );

		\wp_safe_redirect( $url );

		exit;
	}
}
