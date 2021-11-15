<?php
/**
 * Gravity Forms integration.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

/**
 * Gravity Forms - Field advanced settings
 * 
 * @param int $position Settings position.
 * @param int $form_id Gravity Forms form ID.
 * @return void
 */
function prr_gform_field_advanced_settings( $position, $form_id ) {
	// Check settings position.
	if ( 100 != $position ) {
		return;
	}

	global $pronamic_reviews_ratings_plugin;

	// Require advanced field settings.
	require_once $pronamic_reviews_ratings_plugin->dir_path . '/admin/gravityforms/field-advanced-settings.php';
}

\add_action( 'gform_field_advanced_settings', 'prr_gform_field_advanced_settings', 10, 2 );

/**
 * Gravity Forms - Editor JavaScript
 *
 * @return void
 */
function prr_gform_editor_js() {
	global $pronamic_reviews_ratings_plugin;
		
	include $pronamic_reviews_ratings_plugin->dir_path . '/admin/gravityforms/form-editor-js.php';
}

\add_action( 'gform_editor_js', 'prr_gform_editor_js' );

/**
 * Get score for survey field.
 *
 * @param array $entry Gravity Forms entry.
 * @param array $field Gravity Forms field.
 * @return float
 */
function prr_gform_get_score( $entry, $field ) {
	// Check field type.
	if ( 'survey' !== $field['type'] ) {
		return null;
	}

	/*
	 * Check Gravity Forms Survey add-on.
	 *
	 * @link https://github.com/gravityforms/gravityformssurvey/blob/2.1/survey.php#L60
	 */
	if ( ! \method_exists( 'GFSurvey', 'get_instance' ) ) {
		return null;
	}

	// Get score for survey field.
	$gsurvey = GFSurvey::get_instance();

	/*
	 * Check field score method.
	 *
	 * @link https://github.com/gravityforms/gravityformssurvey/blob/2.1/survey.php#L802
	 */
	if ( ! \method_exists( $gsurvey, 'get_field_score' ) ) {
		return null;
	}

	// Get field score.
	$score = $gsurvey->get_field_score( $field, $entry );

	if ( isset( $field['gsurveyLikertEnableMultipleRows'], $field['gsurveyLikertRows'] ) && $field['gsurveyLikertEnableMultipleRows'] ) {
		$score = $score / count( $field['gsurveyLikertRows'] );
	}

	return $score;
}

/**
 * Entry detail sidebar middle
 * 
 * @param array $form  Gravity Forms form.
 * @param array $entry Gravity Forms entry.
 * @return void
 */
function prr_gform_entry_detail_sidebar_middle( $form, $entry ) {
	global $pronamic_reviews_ratings_plugin;
	
	require_once $pronamic_reviews_ratings_plugin->dir_path . '/admin/gravityforms/meta-box-entry-detail-review.php';
}

\add_action( 'gform_entry_detail_sidebar_middle', 'prr_gform_entry_detail_sidebar_middle', 10, 2 );

/**
 * Maybe create review for Gravity Forms entry.
 *
 * @return void
 */
function prr_gform_maybe_create_review() {
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

\add_action( 'admin_init', 'prr_gform_maybe_create_review' );
