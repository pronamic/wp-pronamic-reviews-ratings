<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Plugin
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Plugin {
	/**
	 * Database version.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Plugin file directory path.
	 *
	 * @var string
	 */
	public $dir_path;

	/**
	 * Comments module.
	 *
	 * @var CommentsModule
	 */
	private $comments_module;

	/**
	 * Admin.
	 *
	 * @var Admin
	 */
	private $admin;

	/**
	 * Construct plugin.
	 *
	 * @param string $file Plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file     = $file;
		$this->dir_path = \plugin_dir_path( $file );

		// Tables.
		\pronamic_ratings_register_table( 'pronamic_post_ratings' );

		// Actions.
		\add_action( 'init', array( $this, 'init' ) );
		\add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		\add_action( 'save_post', array( $this, 'save_post' ) );

		// Comments module.
		$this->comments_module = new CommentsModule( $this );

		// Admin.
		if ( \is_admin() ) {
			$this->admin = new Admin( $this );
		}
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function init() {
		global $pronamic_rating_types;

		$pronamic_rating_types = array();

		\do_action( 'pronamic_reviews_ratings_init' );
	}

	/**
	 * Plugins loaded.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		\load_plugin_textdomain( 'pronamic_reviews_ratings', false, \dirname( \plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Save post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post( $post_id ) {
		// Check post type support.
		$post_type = \get_post_type( $post_id );

		if ( ! \post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			return;
		}

		// Update post.
		\pronamic_ratings_post_update( $post_id );
	}
}
