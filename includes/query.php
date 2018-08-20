<?php

function pronamic_sync_rating_to_table( $post_id ) {
	// Sync locations
	global $wpdb;

	$rating_id = $wpdb->get_var( $wpdb->prepare( "SELECT rating_id FROM $wpdb->pronamic_post_ratings WHERE post_id = %d;", $post_id ) );

	$format = array(
		'post_id'      => '%d',
		'rating_value' => '%f',
		'rating_count' => '%d',
	);

	$data = array(
		'post_id'      => $post_id,
		'rating_value' => get_post_meta( $post_id, '_pronamic_rating_value', true ),
		'rating_count' => get_post_meta( $post_id, '_pronamic_rating_count', true ),
	);

	if ( $rating_id ) {
		$result = $wpdb->update( $wpdb->pronamic_post_ratings, $data, array( 'rating_id' => $rating_id ), $format );
	} else {
		$result = $wpdb->insert( $wpdb->pronamic_post_ratings, $data, $format );

		if ( $result ) {
			$rating_id = $wpdb->insert_id;
		}
	}

	return $rating_id;
}

/**
 * Posts clauses
 *
 * http://codex.wordpress.org/WordPress_Query_Vars
 * http://codex.wordpress.org/Custom_Queries
 *
 * @param array $pieces
 * @param WP_Query $query
 * @return string
 */
function pronamic_ratings_posts_clauses( $pieces, $query ) {
	global $wpdb;

	// Fields
	$fields = '';

	if ( '' == $query->get( 'fields' ) ) {
		$fields = ',
			rating.rating_id AS rating_id,
			rating.rating_value AS rating_value,
			rating.rating_count AS rating_count
		';
	}

	// Join
	$join = "
		LEFT JOIN
			$wpdb->pronamic_post_ratings AS rating
				ON $wpdb->posts.ID = rating.post_id
	";

	// Order by
	$orderby = $pieces['orderby'];

	// Order by
	$order  = $query->get( 'order' );

	switch ( $query->get( 'orderby' ) ) {
		case 'rating' :
			$orderby = 'rating_value ' . $order;

			break;
	}

	// Pieces
	$pieces['fields'] .= $fields;
	$pieces['join']   .= $join;

	$pieces['orderby'] = $orderby;

	return $pieces;
}

add_filter( 'posts_clauses', 'pronamic_ratings_posts_clauses', 10, 2 );
