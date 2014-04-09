<?php

$types = pronamic_get_rating_types();

?>
<div class="pronamic-comment-ratings">

	<?php foreach ( $types as $name => $label ) : ?>
	
		<div class="pronamic-comment-rating-<?php echo $name; ?>">
			<span class="pronamic-comment-rating-label"><?php echo $label; ?></span>
			
			<span class="pronamic-comment-rating-control"><?php
	
			$input_name = 'scores[' . $name . ']';

			foreach ( range( 1, 5 ) as $value ) {
				printf( '<input name="%s" value="%d" type="radio" class="star"/>', esc_attr( $input_name ), esc_attr( $value ) );
			}
			
			?></span>
		</div>
	
	<?php endforeach; ?>

</div>
