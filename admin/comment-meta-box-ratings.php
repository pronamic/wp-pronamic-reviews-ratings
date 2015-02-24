<?php

$post_type = get_post_type( $comment->comment_post_ID );

$types = pronamic_get_rating_types( $post_type );

?>
<table>
	<tr>
		<td>
			<?php _e( 'Rating', 'pronamic_reviews_ratings' ); ?>
		</td>
		<td>
			<?php echo get_comment_meta( $comment->comment_ID, '_pronamic_rating', true ); ?>
		</td>
	</tr>

	<?php foreach ( $types as $name => $label ) : ?>

		<tr>
			<td>
				<?php echo $label; ?>
			</td>
			<td>
				<?php

				$input_name = 'pronamic_comment_ratings[' . $name . ']';
				$meta_key   = '_pronamic_rating_value_' . $name;
				$meta_value = get_comment_meta( $comment->comment_ID, $meta_key, true );

				foreach ( range( 1, 10 ) as $value ) {
					printf(
						'<input name="%s" value="%d" type="radio" class="star" %s />',
						esc_attr( $input_name ),
						esc_attr( $value ),
						checked( $value, $meta_value, false )
					);
				}

				?>
			</td>
		</tr>

	<?php endforeach; ?>

</table>
