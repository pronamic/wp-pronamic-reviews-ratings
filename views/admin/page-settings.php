<?php
/**
 * Comment meta box ratings.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

?>

<div class="wrap">
	<h1>
		<?php echo \esc_html( \get_admin_page_title() ); ?>
	</h1>

	<form action="options.php" method="post">
		<?php

		\settings_fields( 'pronamic_reviews_ratings' );

		\do_settings_sections( 'pronamic-reviews-ratings' );

		\submit_button();

		?>
	</form>
</div>
