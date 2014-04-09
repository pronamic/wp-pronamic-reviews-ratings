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
