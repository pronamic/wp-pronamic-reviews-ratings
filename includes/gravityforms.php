<?php

/**
 * Gravity Forms - Field advanced settings
 * 
 * @param int $position
 * @param int $form_id
 */
function prr_gform_field_advanced_settings( $position, $form_id ) {
	if ( $position == 100 ) {
		global $pronamic_reviews_ratings_plugin;
		
		include $pronamic_reviews_ratings_plugin->dir_path . '/admin/gravityforms/field-advanced-settings.php';
	}
}

add_action( 'gform_field_advanced_settings', 'prr_gform_field_advanced_settings', 10, 2 );

/**
 * Gravity Forms - Editor JavaScript
 */
function pronamic_companies_gform_editor_js() {
	global $pronamic_reviews_ratings_plugin;
		
	include $pronamic_reviews_ratings_plugin->dir_path . '/admin/gravityforms/form-editor-js.php';
}

add_action( 'gform_editor_js', 'pronamic_companies_gform_editor_js' );

/**
 * Get score
 * 
 * @param array $lead
 * @param array $field
 * @return float
 */
function prr_gform_get_score( $lead, $field ) {
	$score = null;

	$field_type = $field['type'];

	if ( 'survey' == $field_type ) {
		// @see https://github.com/gravityforms/gravityformssurvey/blob/2.1/survey.php#L60
		if ( method_exists( 'GFSurvey', 'get_instance' ) ) {			
			$gsurvey = GFSurvey::get_instance();

			// @see https://github.com/gravityforms/gravityformssurvey/blob/2.1/survey.php#L802
			if ( method_exists( $gsurvey, 'get_field_score' ) ) {
				$score = $gsurvey->get_field_score( $field, $lead );

				if ( isset( $field['gsurveyLikertEnableMultipleRows'], $field['gsurveyLikertRows'] ) && $field['gsurveyLikertEnableMultipleRows'] ) {
					$score = $score / count( $field['gsurveyLikertRows'] );
				}
			}
		}
	}
	
	return $score;
}

/**
 * Entry detail sidebar middle
 * 
 * @param array $form
 * @param array $lead
 */
function prr_gform_entry_detail_sidebar_middle( $form, $lead ) {
	global $pronamic_reviews_ratings_plugin;
	
	include $pronamic_reviews_ratings_plugin->dir_path . '/admin/gravityforms/meta-box-entry-detail-review.php';
}

add_action( 'gform_entry_detail_sidebar_middle', 'prr_gform_entry_detail_sidebar_middle', 10, 2 );

function prr_gform_maybe_create_review() {
	if ( filter_has_var( INPUT_POST, 'pronamic_create_review' ) ) {
		$lead_id = filter_input( INPUT_POST, 'pronamic_review_lead_id', FILTER_SANITIZE_STRING );		
		$post_id = filter_input( INPUT_POST, 'pronamic_review_post_id', FILTER_SANITIZE_STRING );
		$name    = filter_input( INPUT_POST, 'pronamic_review_name', FILTER_SANITIZE_STRING );
		$email   = filter_input( INPUT_POST, 'pronamic_review_email', FILTER_SANITIZE_EMAIL );
		$review  = filter_input( INPUT_POST, 'pronamic_review_comment', FILTER_SANITIZE_STRING );
		
		$data = array(
			'comment_post_ID'      => $post_id,
			'comment_author'       => $name,
			'comment_author_email' => $email,
			'comment_author_url'   => '',
			'comment_content'      => $review,
			'comment_type'         => 'pronamic_review',
		);

		$comment_id = wp_new_comment( $data );
		
		gform_update_meta( $lead_id, 'pronamic_review_id', $comment_id );

		$url = add_query_arg( 'pronamic_review_id', $comment_id );

		wp_redirect( $url );

		exit;
	}
}

add_action( 'admin_init', 'prr_gform_maybe_create_review' );
