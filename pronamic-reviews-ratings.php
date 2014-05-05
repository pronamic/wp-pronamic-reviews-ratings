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
 * Require classes
 */
foreach ( glob( plugin_dir_path( __FILE__ ) . 'classes/*.php' ) as $file ) {
    require_once $file;
}

/**
 * Global init
 */
global $pronamic_reviews_ratings_plugin;

$pronamic_reviews_ratings_plugin = new Pronamic_WP_ReviewsRatingsPlugin( __FILE__ );


// @see https://github.com/woothemes/woocommerce/blob/v2.1.6/includes/abstracts/abstract-wc-product.php#L964
// @see https://github.com/woothemes/woocommerce/blob/v2.1.6/includes/abstracts/abstract-wc-product.php#L999
function pronamic_ratings_post_update( $post_id ) {
	global $wpdb;

	$query = "
		SELECT
			meta_key,
			COUNT(meta_value) AS rating_count,
			SUM(meta_value) / COUNT( meta_value ) AS rating_value
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

	$query = $wpdb->prepare( $query, '_pronamic_rating_value%', $post_id );

	$results = $wpdb->get_results( $query );

	if ( empty( $results ) ) {
		update_post_meta( $post_id, '_pronamic_rating_value', '' );
		update_post_meta( $post_id, '_pronamic_rating_count', '' );
	} else {
		foreach ( $results as $result ) {
			$meta_key_value = $result->meta_key;
			$meta_key_count = str_replace( '_pronamic_rating_value', '_pronamic_rating_count', $meta_key_value );

			update_post_meta( $post_id, $meta_key_value, $result->rating_value );
			update_post_meta( $post_id, $meta_key_count, $result->rating_count );
		}
	}
}

function pronamic_insert_comment_ratings_update( $id, $comment ) {
	pronamic_ratings_comment_post_update( $comment->comment_post_ID );
}

add_action( 'wp_insert_comment', 'pronamic_insert_comment_ratings_update', 10, 2 );

/**
 * Comment post
 *
 * @param int $comment_id
 */
function pronamic_ratings_comment_post( $comment_ID ) {
	$scores = isset( $_POST['scores'] ) ? $_POST['scores'] : array();
	$types = pronamic_get_rating_types();

	foreach ( $types as $name => $label ) {
		$meta_key   = '_pronamic_rating_value_' . $name;
		$meta_value = $_POST['scores'][ $name ];

		update_comment_meta( $comment_ID, $meta_key, $meta_value );
	}

	$rating = array_sum( $scores ) / count( $scores );

	update_comment_meta( $comment_ID, '_pronamic_rating_value', $rating );

	pronamic_ratings_comment_post_update( $comment_ID );
}

add_action( 'comment_post', 'pronamic_ratings_comment_post', 1 );

/**
 * Ratings comment post update
 *
 * @param int $comment_ID
 */
function pronamic_ratings_comment_post_update( $comment_ID ) {
	$comment = get_comment( $comment_ID );

	pronamic_ratings_post_update( $comment->comment_post_ID );
}

/**
 * Edit comment
 *
 * @param int $comment_ID
 */
function pronamic_ratings_edit_comment( $comment_ID ) {
	if ( filter_has_var( INPUT_POST, 'pronamic_comment_ratings_meta_box_nonce' ) ) {
		$nonce = filter_input( INPUT_POST, 'pronamic_comment_ratings_meta_box_nonce', FILTER_SANITIZE_STRING );

		if ( wp_verify_nonce( $nonce, 'pronamic_comment_ratings_save' ) ) {
			$ratings = filter_input( INPUT_POST, 'pronamic_comment_ratings', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

			foreach ( $ratings as $name => $value ) {
				$meta_key   = '_pronamic_rating_value_' . $name;
				$meta_value = $value;

				update_comment_meta( $comment_ID, $meta_key, $meta_value );
			}

			$rating = array_sum( $ratings ) / count( $ratings );

			update_comment_meta( $comment_ID, '_pronamic_rating_value', $rating );

			pronamic_ratings_comment_post_update( $comment_ID );
		}
	}
}

add_action( 'edit_comment', 'pronamic_ratings_edit_comment' );
