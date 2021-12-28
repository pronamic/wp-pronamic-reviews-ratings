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
