<?php

/**
 * Register rating type
 * 
 * The 'name' is used for some meta keys (max length = 255)
 * @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-admin/includes/schema.php#L78
 * 
 * _pronamic_rating_value_$name
 * _pronamic_rating_count_$name
 * 
 * @param string $name
 * @param string $args
 */
function pronamic_register_rating_type( $name, $args ) {
	global $pronamic_rating_types;

	$pronamic_rating_types[ $name ] = $args;
}

function pronamic_get_rating_types() {
	global $pronamic_rating_types;

	return $pronamic_rating_types;
}
