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

class Pronamic_WP_ReviewsRatingsPlugin {
	/**
	 * Plugin file
	 * 
	 * @var string
	 */
	public $file;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an reviews and ratings plugin 
	 * 
	 * @param string $file
	 */
	public function __construct( $file ) {
		$this->file     = $file;
		$this->dir_path = plugin_dir_path( $file );
		
		// Includes
		include $this->dir_path . 'includes/functions.php';
		include $this->dir_path . 'includes/gravityforms.php';

		// Actions
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		
		// Actions - Comment Form
		// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L2068
		// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment-template.php#L2101
		add_action( 'comment_form_logged_in_after', array( $this, 'comment_form_expansion' ) );
		add_action( 'comment_form_after_fields', array( $this, 'comment_form_expansion' ) );

		// Comment Processor
		$this->comment_processor = new Pronamic_WP_ReviewsRatingsCommentProcessor();

		// Admin
		if ( is_admin() ) {
			$this->admin = new Pronamic_WP_ReviewsRatingsAdmin( $this );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public function init() {
		global $pronamic_rating_types;
		
		$pronamic_rating_types = array();
		
		do_action( 'pronamic_reviews_ratings_init' );
	}

	//////////////////////////////////////////////////

	/**
	 * Plugins loaded
	 */
	public function plugins_loaded() {
		load_plugin_textdomain( 'pronamic_reviews_ratings', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	//////////////////////////////////////////////////

	/**
	 * Comment Form expansion
	 * 
	 *  @see https://github.com/WordPress/WordPress/blob/3.8.1/wp-includes/comment-template.php#L1920
	 */
	public function comment_form_expansion() {
		include $this->dir_path . 'templates/comment-form-ratings.php';
	}
}

class Pronamic_WP_ReviewsRatingsAdmin {
	private $plugin;

	//////////////////////////////////////////////////

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		
		// Actions
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Add meta boxes
	 *
	 * @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-admin/edit-form-comment.php#L130
	 * @see http://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
	 * @see http://shibashake.com/wordpress-theme/add-a-metabox-to-the-edit-comments-screen
	 */
	function add_meta_boxes() {
		add_meta_box( 'pronamic_comment_ratings', __( 'Ratings', 'pronamic_reviews_ratings' ), array( $this, 'comment_meta_box_ratings' ), 'comment', 'normal' );
	}

	/**
	 * Comment meta box ratings
	 */
	public function comment_meta_box_ratings() {
		wp_nonce_field( 'pronamic_comment_ratings_save', 'pronamic_comment_ratings_meta_box_nonce' );

		include $this->plugin->dir_path . 'admin/comment-meta-box-ratings.php';
	}
}

class Pronamic_WP_ReviewsRatingsCommentProcessor {
	/**
	 * Construct and initalize comment processor
	 */
	public function __construct() {
		

		// Actions
		// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-includes/comment.php#L1687
		add_filter( 'preprocess_comment', array( $this, 'validate_ratings' ), 0 );
		add_action( 'preprocess_comment', array( $this, 'update_comment_type' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Validate comment ratings
	 * 
	 * @param array $commentdata
	 * @return array
	 */
	public function validate_ratings( $commentdata ) {
		if ( filter_has_var( INPUT_POST, 'pronamic_review' ) ) {
			$ratings = filter_input( INPUT_POST, 'scores', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

			$types = pronamic_get_rating_types();

			foreach ( $types as $name => $label ) {
				if ( ! isset( $ratings[ $name ] ) || empty( $ratings[ $name ] ) ) {
					// @see https://github.com/WordPress/WordPress/blob/3.8.2/wp-comments-post.php#L121
					wp_die( __('<strong>ERROR</strong>: please fill in the rating fields.', 'pronamic_reviews_ratings' ) );
					exit;
				}
			}
		}

		return $commentdata;
	}

	//////////////////////////////////////////////////

	/**
	 * Update comment type
	 * 
	 * @param array $commentdata
	 * @return array
	 */
	public function update_comment_type( $commentdata ) {
		if ( filter_has_var( INPUT_POST, 'pronamic_review' ) ) {
			$commentdata['comment_type'] = 'pronamic_review';
		}
		
		return $commentdata;
	}
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

	foreach ( $results as $result ) {
		$meta_key_value = $result->meta_key;
		$meta_key_count = str_replace( '_pronamic_rating_value', '_pronamic_rating_count', $meta_key_value );

		update_post_meta( $post_id, $meta_key_value, $result->rating_value );
		update_post_meta( $post_id, $meta_key_count, $result->rating_count );
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

	foreach( $types as $name => $label ) {
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
