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

use Pronamic\WordPress\ReviewsRatings\Admin\Admin;

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
	 * Admin.
	 *
	 * @var Admin
	 */
	private $admin;

	/**
	 * Rating types.
	 *
	 * @var array
	 */
	private $rating_types;

	/**
	 * Ratings controller.
	 *
	 * @var RatingsController
	 */
	public $ratings_controller;

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
		\add_action( 'init', array( $this, 'init' ), 20 );
		\add_action( 'init', array( $this, 'register_settings' ) );
		\add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		// Review post type.
		new ReviewPostType();

		// Controllers.
		$this->ratings_controller = new RatingsController( $this );

		new CommentsController( $this );
		new Shortcodes( $this );
		new GravityForms( $this );

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
		$this->load_rating_types();

		\do_action( 'pronamic_reviews_ratings_init' );

		// Post types support.
		foreach ( $this->get_rating_types() as $rating_type ) {
			if ( \array_key_exists( 'post_types', $rating_type ) ) {
				foreach ( $rating_type['post_types'] as $post_type ) {
					// Check post type.
					$post_type_object = \get_post_type_object( $post_type );

					if ( null === $post_type_object ) {
						continue;
					}

					// Add post type support.
					\add_post_type_support( $post_type, 'pronamic_ratings' );

					// Add rating types to post type object.
					if ( ! \property_exists( $post_type_object, 'pronamic_rating_types' ) ) {
						$post_type_object->pronamic_rating_types = array();
					}

					$post_type_object->pronamic_rating_types[] = $rating_type['name'];
				}
			}
		}
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		// Rating types setting.
		register_setting(
			'pronamic_reviews_ratings',
			'pronamic_reviews_ratings_types',
			array(
				'type' => 'string',
			)
		);
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
	 * Get plugin version.
	 *
	 * @return string|null
	 */
	public function get_version() {
		$version = null;

		$data = \get_plugin_data( $this->file );

		if ( \array_key_exists( 'Version', $data ) && ! empty( $data['Version'] ) ) {
			$version = (string) $data['Version'];
		}

		return $version;
	}

	/**
	 * Get rating types.
	 *
	 * @return array
	 */
	public function get_rating_types() {
		return $this->rating_types;
	}

	/**
	 * Load rating types.
	 *
	 * @return void
	 */
	private function load_rating_types() {
		$rating_types = \get_option( 'pronamic_reviews_ratings_types', array() );

		foreach ( $rating_types as $rating_type ) {
			$this->register_rating_type( $rating_type['name'], $rating_type );
		}
	}

	/**
	 * Register rating type.
	 *
	 * @param string $name Rating type name.
	 * @param array  $args Arguments.
	 * @return void
	 * @throws \InvalidArgumentException Throws exception for empty rating type name.
	 */
	public function register_rating_type( $name, $args ) {
		// Check name.
		if ( empty( $name ) ) {
			throw new \InvalidArgumentException( __( 'Name is required to register a rating type. ', 'pronamic_reviews_ratings' ) );
		}

		// Check if rating type has already been registered.
		$rating_type = \wp_list_filter( $this->rating_types, array( 'name' => $name ) );

		if ( ! empty( $rating_type ) ) {
			return;
		}

		// Use string value as label.
		if ( is_string( $args ) ) {
			$args = array(
				'label' => $args,
			);
		}

		// Make sure arguments is an array.
		if ( ! \is_array( $args ) ) {
			$args = array();
		}

		if ( ! \array_key_exists( 'name', $args ) ) {
			$args['name'] = $name;
		}

		// Add rating type.
		$this->rating_types[ $name ] = $args;
	}
}
