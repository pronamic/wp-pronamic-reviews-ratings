<?php

/**
 * Gravity Forms - Field advanced settings
 * 
 * @param int $position
 * @param int $form_id
 */
function prr_gform_field_advanced_settings( $position, $form_id ) {
	if ( $position == 100 ) : ?>

		<li class="prepopulate_field_setting field_setting" style="display: list-item;">
			<label for="prr_rating_type">
				<?php _e( 'Rating Type', 'pronamic_reviews_ratings' ); ?>
			</label>

			<select id="prr_rating_type" name="prr_rating_type" onchange="SetFieldProperty( 'pronamicRatingType', jQuery( this ).val() );">
				<option value=""></option>

				<?php foreach ( pronamic_get_rating_types() as $name => $label ) : ?>
					<option value="<?php echo esc_attr( $name ); ?>"><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</li>
		<li class="prepopulate_field_setting field_setting" style="display: list-item;">
			<input type="checkbox" id="prr_is_review_post_id" onchange="var field = GetSelectedField(); window.form.pronamicReviewPostIdFieldId = field.id;" />

			<label for="prr_is_review_post_id" class="inline">
				<?php _e( 'Is Review Post ID', 'pronamic_reviews_ratings' ); ?>
			</label>
		</li>
		<li class="prepopulate_field_setting field_setting" style="display: list-item;">
			<input type="checkbox" id="prr_is_review_name" onchange="var field = GetSelectedField(); window.form.pronamicReviewNameFieldId = field.id;" />

			<label for="prr_is_review_name" class="inline">
				<?php _e( 'Is Review Name', 'pronamic_reviews_ratings' ); ?>
			</label>
		</li>
		<li class="prepopulate_field_setting field_setting" style="display: list-item;">
			<input type="checkbox" id="prr_is_review_email" onchange="var field = GetSelectedField(); window.form.pronamicReviewEmailFieldId = field.id;" />

			<label for="prr_is_review_email" class="inline">
				<?php _e( 'Is Review E-mail', 'pronamic_reviews_ratings'); ?>
			</label>
		</li>
		<li class="prepopulate_field_setting field_setting" style="display: list-item;">
			<input type="checkbox" id="prr_is_review_comment" onchange="var field = GetSelectedField(); window.form.pronamicReviewCommentFieldId = field.id;" />

			<label for="prr_is_review_comment" class="inline">
				<?php _e( 'Is Review Comment', 'pronamic_reviews_ratings'); ?>
			</label>
		</li>
		

	<?php endif;
}

add_action( 'gform_field_advanced_settings', 'prr_gform_field_advanced_settings', 10, 2 );

/**
 * Gravity Forms - Editor JavaScript
 */
function pronamic_companies_gform_editor_js() {
	?>
	<script type="text/javascript">
		jQuery( document ).bind( 'gform_load_field_settings', function( event, field, form ) {
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
	<?php
}

add_action( 'gform_editor_js', 'pronamic_companies_gform_editor_js' );

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
	?>
	<div class="postbox" id="notifications_container">
		<h3 style="cursor:default;"><span><?php esc_html_e( 'Review & Ratings', 'pronamic_reviews_ratings' ); ?></span></h3>

		<div class="inside">
			<?php 

			$review_post_id  = '';
			if ( isset( $form['pronamicReviewPostIdFieldId'] ) && isset( $lead[ $form['pronamicReviewPostIdFieldId'] ] ) ) {
				$review_post_id = $lead[ $form['pronamicReviewPostIdFieldId'] ];
			}

			$review_name  = '';
			if ( isset( $form['pronamicReviewNameFieldId'] ) && isset( $lead[ $form['pronamicReviewNameFieldId'] ] ) ) {
				$review_name = $lead[ $form['pronamicReviewNameFieldId'] ];
			}

			$review_email = '';
			if ( isset( $form['pronamicReviewEmailFieldId'] ) && isset( $lead[ $form['pronamicReviewEmailFieldId'] ] ) ) {
				$review_email = $lead[ $form['pronamicReviewEmailFieldId'] ];
			}

			$review_comment = '';
			if ( isset( $form['pronamicReviewCommentFieldId'] ) && isset( $lead[ $form['pronamicReviewCommentFieldId'] ] ) ) {
				$review_comment = $lead[ $form['pronamicReviewCommentFieldId'] ];
			}

			$rating_types = pronamic_get_rating_types();

			$scores = array();
			foreach( $rating_types as $type => $label ) {
				$scores[ $type ] = array();
			}

			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['pronamicRatingType'] ) && ! empty( $field['pronamicRatingType'] ) ) {
					$rating_type = $field['pronamicRatingType'];

					if ( isset( $scores[ $rating_type ] ) ) {
						$score = prr_gform_get_score( $lead, $field );

						$scores[ $rating_type ][] = $score;
					}
				} 
			}
			
			$comment_id = gform_get_meta( $lead['id'], 'pronamic_review_id' );
			$comment = get_comment( $comment_id );

			if ( ! empty( $comment ) ) : ?>
	
				<p>
					<a href="<?php echo get_edit_comment_link( $comment ); ?>"><?php _e( 'Edit', 'pronamic_reviews_ratings' ); ?></a> | 
					<a href="<?php echo get_comment_link( $comment ); ?>"><?php _e( 'View', 'pronamic_reviews_ratings' ); ?></a>
				</p>

			<?php else : ?>
	
				<table>
					<thead>
						<tr>
							<th scope="col" style="text-align: left;"><?php _e( 'Type', 'pronamic_reviews_ratings' ); ?></th>
							<th scope="col" style="text-align: left;"><?php _e( 'Score', 'pronamic_reviews_ratings' ); ?></th>
						</tr>
					</thead>
					
					<tbody>
	
						<?php foreach ( $rating_types as $type => $label ) : ?>
	
							<tr>
								<?php 
								
								$name   = 'scores[' . $type . ']';
								$values = $scores[ $type ];
								$sum    = array_sum( $values );
								$count  = count( $values );
								$value  = $count > 0 ? $sum / $count : '';
								$value  = pronamic_transform_rating( range( 1, 10 ), range( 1, 5 ), $value );
	
								?>
								<td><?php echo $label; ?></td>
								<td>
									<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
									<?php echo $value; ?>
								</td>
							</tr>
						
						<?php endforeach; ?>
	
					</tbody>
				</table>
	
				<input type="hidden" name="pronamic_review_lead_id" value="<?php echo esc_attr( $lead['id'] ); ?>" />
	
				<p>
					Post ID<br />
					<input type="text" name="pronamic_review_post_id" value="<?php echo esc_attr( $review_post_id ); ?>" />
				</p>
				<p>
					Name<br />
					<input type="text" name="pronamic_review_name" value="<?php echo esc_attr( $review_name ); ?>" />
				</p>
				<p>
					E-Mail<br />
					<input type="text" name="pronamic_review_email" value="<?php echo esc_attr( $review_email ); ?>" />
				</p>
				<p>
					Review<br />
					<textarea name="pronamic_review_comment" rows="10" cols="40"><?php echo esc_textarea( $review_comment ); ?></textarea>
				</p>
	
				<?php submit_button( __( 'Create Review Comment', 'pronamic_reviews_ratings' ), 'secondary', 'pronamic_create_review', false ); ?>
			
			<?php endif; ?>
		</div>
	</div>
	<?php
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
			'comment_type'         => 'review',
		);

		$comment_id = wp_new_comment( $data );
		
		gform_update_meta( $lead_id, 'pronamic_review_id', $comment_id );

		$url = add_query_arg( 'pronamic_review_id', $comment_id );

		wp_redirect( $url );

		exit;
	}
}

add_action( 'admin_init', 'prr_gform_maybe_create_review' );
