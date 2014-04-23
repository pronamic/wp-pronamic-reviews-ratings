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
				<?php _e( 'Post ID', 'pronamic_reviews_ratings' ); ?><br />
				<input type="text" name="pronamic_review_post_id" value="<?php echo esc_attr( $review_post_id ); ?>" />
			</p>
			<p>
				<?php _e( 'Name', 'pronamic_reviews_ratings' ); ?><br />
				<input type="text" name="pronamic_review_name" value="<?php echo esc_attr( $review_name ); ?>" />
			</p>
			<p>
				<?php _e( 'E-Mail', 'pronamic_reviews_ratings' ); ?><br />
				<input type="text" name="pronamic_review_email" value="<?php echo esc_attr( $review_email ); ?>" />
			</p>
			<p>
				<?php _e( 'Comment', 'pronamic_reviews_ratings' ); ?><br />
				<textarea name="pronamic_review_comment" rows="10" cols="30"><?php echo esc_textarea( $review_comment ); ?></textarea>
			</p>

			<?php submit_button( __( 'Create Review Comment', 'pronamic_reviews_ratings' ), 'secondary', 'pronamic_create_review', false ); ?>
		
		<?php endif; ?>
	</div>
</div>
