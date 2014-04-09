<?php

function pronamic_register_rating_type( $name, $args ) {
	global $pronamic_rating_types;

	$pronamic_rating_types[ $name ] = $args;
}

function pronamic_get_rating_types() {
	global $pronamic_rating_types;

	return $pronamic_rating_types;
}
