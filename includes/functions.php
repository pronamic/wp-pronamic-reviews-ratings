<?php
/**
 * Functions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

/**
 * Register rating type.
 *
 * The 'name' is used for some meta keys (max length = 255).
 *
 * _pronamic_rating_value_$name
 * _pronamic_rating_count_$name
 *
 * @param string $name Rating type name.
 * @param string $args Arguments.
 * @return void
 */
function pronamic_register_rating_type( $name, $args ) {
	global $pronamic_rating_types;

	$pronamic_rating_types[ $name ] = $args;
}

/**
 * Get rating types.
 *
 * @param string|null $post_type Post type.
 * @return array
 */
function pronamic_get_rating_types( $post_type = null ) {
	global $pronamic_rating_types;

	// Rating types.
	$rating_types = $pronamic_rating_types;

	// Post type.
	if ( ! empty( $post_type ) ) {
		global $wp_post_types;

		if ( isset( $wp_post_types[ $post_type ], $wp_post_types[ $post_type ]->pronamic_rating_types ) ) {
			$post_rating_types = $wp_post_types[ $post_type ]->pronamic_rating_types;

			$rating_types = \array_intersect_key(
				$rating_types,
				\array_flip( $post_rating_types )
			);
		}
	}

	// Return.
	return $rating_types;
}

/**
 * Transform rating.
 *
 * @param array $from_range From range.
 * @param array $to_range   To range.
 * @param int   $rating     Rating.
 * @return mixed
 */
function pronamic_transform_rating( $from_range, $to_range, $rating ) {
	$from_count = \count( $from_range );
	$to_count   = \count( $to_range );

	$delta = $from_count / $to_count;

	$from_i = \array_search( $rating, $from_range );

	$to_i = \floor( $from_i / $delta );

	$rating_new = $to_range[ $to_i ];

	return $rating_new;
}

/**
 * Register a table with $wpdb.
 *
 * @param string $key    The key to be used on the $wpdb object.
 * @param bool   $name   The actual name of the table, without $wpdb->prefix.
 * @param bool   $prefix Prefix.
 */
function pronamic_ratings_register_table( $key, $name = false, $prefix = false ) {
	global $wpdb;

	if ( false === $name ) {
		$name = $key;
	}

	$wpdb->tables[] = $name;
	$wpdb->$key     = $wpdb->prefix . $name;
}

/**
 * Install table.
 *
 * @param string $key     Database table key.
 * @param array  $columns Columns.
 * @return void
 */
function pronamic_ratings_install_table( $key, $columns ) {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$full_table_name = $wpdb->$key;

	$charset_collate = '';

	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}

	$table_options = $charset_collate;

	\dbDelta( "CREATE TABLE $full_table_name ( $columns ) $table_options" );
}
