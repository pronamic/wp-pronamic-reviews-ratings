<?php

/**
 * Plugin
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

		// Filters
		add_filter( 'request', array( $this, 'request_orderby_rating' ) );

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
		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		if ( post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			include $this->dir_path . 'templates/comment-form-ratings.php';
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Request order by rating
	 *
	 * @param array $vars
	 */
	function request_orderby_rating( $vars ) {
		if ( isset( $vars['orderby'] ) && 'rating' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
					'meta_key' => '_pronamic_rating_value',
					'orderby'  => 'meta_value_num',
			) );
		}

		return $vars;
	}
}
