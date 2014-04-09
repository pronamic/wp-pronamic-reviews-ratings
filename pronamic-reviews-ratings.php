<?php
/*
Plugin Name: Pronamic Reviews and Ratings
Plugin URI: http://www.happywp.com/plugins/pronamic-reviews-ratings/
Description: The Pronamic Reviews Ratings plugin for WordPress is a powerful, extendable reviews and ratings plugin.

Version: 1.0.0
Requires at least: 3.0

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: pronamic_reviews_ratings
Domain Path: /languages/

License: GPL

GitHub URI: https://github.com/pronamic/wp-pronamic-reviews-ratings
*/


/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
*/
function pronamic_reviews_ratings_loaded() {
	load_plugin_textdomain( 'pronamic_reviews_ratings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'pronamic_reviews_ratings_loaded' );


function pronamic_reviews_ratings_init() {
	global $pronamic_rating_types;
	
	$pronamic_rating_types = array();

	do_action( 'pronamic_reviews_ratings_init' );
}

add_action( 'init', 'pronamic_reviews_ratings_init' );

function pronamic_register_rating_type( $name, $args ) {
	global $pronamic_rating_types;

	$pronamic_rating_types[ $name ] = $args;
}

function pronamic_get_rating_types() {
	global $pronamic_rating_types;
	
	return $pronamic_rating_types;
}

/**
 * @see https://github.com/WordPress/WordPress/blob/3.8.1/wp-includes/comment-template.php#L1920
 */
function pronamic_comment_form_ratings( $fields ) {
	$types = pronamic_get_rating_types();

	foreach ( $types as $name => $label ) {
		echo $label;

		echo ' ';

		$input_name = 'scores[' . $name . ']';

		foreach ( range( 1, 5 ) as $value ) {
			printf( '<input name="%s" value="%d" type="radio" class="star"/>', esc_attr( $input_name ), esc_attr( $value ) );
		}

		echo '<br />';
	}
}

// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L2068
add_action( 'comment_form_logged_in_after', 'pronamic_comment_form_ratings' );

// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L2101
add_action( 'comment_form_after_fields', 'pronamic_comment_form_ratings' );

/**
 * Ratings check
 * 
 * @param array $comment_data
 * @return array
 */
function pronamic_ratings_check( $comment_data ) {
	$scores = isset( $_POST['scores'] ) ? $_POST['scores'] : array();
	$score_types = reviews_get_scores();

	foreach ( $score_types as $name => $label ) {
		if ( ! isset( $scores[ $name ] ) || empty( $scores[ $name ] ) ) {
			// @see http://translate.wordpress.org/projects/wp/3.8.x/nl/default?filters[term]=%3Cstrong%3EERROR%3C%2Fstrong%3E&filters[user_login]=&filters[status]=current_or_waiting_or_fuzzy_or_untranslated&filter=Filter&sort[by]=priority&sort[how]=desc
			wp_die( __('<strong>ERROR</strong>: please fill in the rating fields.', 'pronamic_reviews_ratings' ) );
			exit;
		}
	}

	return $comment_data;
}

add_filter( 'preprocess_comment', 'pronamic_ratings_check', 0 );

// @see https://github.com/woothemes/woocommerce/blob/v2.1.6/includes/abstracts/abstract-wc-product.php#L964
// @see https://github.com/woothemes/woocommerce/blob/v2.1.6/includes/abstracts/abstract-wc-product.php#L999
function pronamic_ratings_update( $post_id ) {
	global $wpdb;

	$query = "
		SELECT
			meta_key,
			COUNT(meta_value),
			SUM(meta_value) / COUNT( meta_value ) AS meta_value
		FROM
			$wpdb->commentmeta
				LEFT JOIN
			$wpdb->comments 
					ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
		WHERE
			meta_key LIKE %s
				AND
			comment_post_ID = %d
				AND
			comment_approved = '1'
				AND
			meta_value > 0
		GROUP BY
			meta_key
		;"
	;

	$query = $wpdb->prepare( $query, '_pronamic_rating%', $post_id );

	$results = $wpdb->get_results( $query );

	foreach ( $results as $result ) {
		update_post_meta( $post_id, $result->meta_key, $result->meta_value );
	}
}

function pronamic_insert_comment_ratings_update( $id, $comment ) {
	pronamic_ratings_update( $comment->comment_post_ID );
	
	
}

add_action( 'wp_insert_comment', 'pronamic_insert_comment_ratings_update', 10, 2 );

/**
 * Comment post
 * 
 * @param int $comment_id
 */
function pronamic_ratings_comment_post( $comment_id ) {
	$scores = isset( $_POST['scores'] ) ? $_POST['scores'] : array();
	$score_types = reviews_get_scores();

	foreach( $score_types as $name => $label ) {
		$meta_key   = '_pronamic_rating_' . $name;
		$meta_value = $_POST['scores'][ $name ];

		update_comment_meta( $comment_id, $meta_key, $meta_value, true );
	}

	$rating = array_sum( $scores ) / count( $scores );

	update_comment_meta( $comment_id, '_pronamic_rating', $rating, true );
}

add_action( 'comment_post', 'pronamic_ratings_comment_post', 1 );
