<?php
/**
 * Gravity Forms editor script.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

?>
<script type="text/javascript">
	jQuery( document ).on( 'gform_load_field_settings', function( event, field, form ) {
		var pronamicRatingType = typeof field.pronamicRatingType == 'undefined' ? '' : field.pronamicRatingType;
		var pronamicReviewPostIdFieldId = typeof form.pronamicReviewPostIdFieldId == 'undefined' ? '' : form.pronamicReviewPostIdFieldId;
		var pronamicReviewNameFieldId = typeof form.pronamicReviewNameFieldId == 'undefined' ? '' : form.pronamicReviewNameFieldId;
		var pronamicReviewEmailFieldId = typeof form.pronamicReviewEmailFieldId == 'undefined' ? '' : form.pronamicReviewEmailFieldId;
		var pronamicReviewCommentFieldId = typeof form.pronamicReviewCommentFieldId == 'undefined' ? '' : form.pronamicReviewCommentFieldId;

		jQuery( '#prr_rating_type' ).val( pronamicRatingType );
		jQuery( '#prr_is_review_post_id' ).prop( 'checked', field.id == pronamicReviewPostIdFieldId );
		jQuery( '#prr_is_review_name' ).prop( 'checked', field.id == pronamicReviewNameFieldId );
		jQuery( '#prr_is_review_email' ).prop( 'checked', field.id == pronamicReviewEmailFieldId );
		jQuery( '#prr_is_review_comment' ).prop( 'checked', field.id == pronamicReviewCommentFieldId );
	} );
</script>
