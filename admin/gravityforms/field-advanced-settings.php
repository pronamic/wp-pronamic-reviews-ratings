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
		<?php _e( 'Is Review E-mail', 'pronamic_reviews_ratings' ); ?>
	</label>
</li>
<li class="prepopulate_field_setting field_setting" style="display: list-item;">
	<input type="checkbox" id="prr_is_review_comment" onchange="var field = GetSelectedField(); window.form.pronamicReviewCommentFieldId = field.id;" />

	<label for="prr_is_review_comment" class="inline">
		<?php _e( 'Is Review Comment', 'pronamic_reviews_ratings' ); ?>
	</label>
</li>
