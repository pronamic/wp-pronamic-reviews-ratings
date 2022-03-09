<?php
/**
 * Review post type.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Review post type
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class ReviewPostType {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// Actions.
		\add_action( 'init', array( $this, 'register_post_type' ) );
		\add_action( 'save_post_pronamic_review', array( $this, 'save_review_post' ), 1 );
		\add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Filters.
		\add_filter( 'the_content', array( $this, 'review_content' ) );
	}

	/**
	 * Register post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		\register_post_type(
			'pronamic_review',
			array(
				'label'               => __( 'Review', 'pronamic_reviews_ratings' ),
				'description'         => __( 'Reviews', 'pronamic_reviews_ratings' ),
				'labels'              => array(
					'name'                  => _x( 'Reviews', 'Post Type General Name', 'pronamic_reviews_ratings' ),
					'singular_name'         => _x( 'Review', 'Post Type Singular Name', 'pronamic_reviews_ratings' ),
					'menu_name'             => __( 'Reviews', 'pronamic_reviews_ratings' ),
					'name_admin_bar'        => __( 'Review', 'pronamic_reviews_ratings' ),
					'archives'              => __( 'Review Archives', 'pronamic_reviews_ratings' ),
					'attributes'            => __( 'Review Attributes', 'pronamic_reviews_ratings' ),
					'parent_item_colon'     => __( 'Parent Review:', 'pronamic_reviews_ratings' ),
					'all_items'             => __( 'All Reviews', 'pronamic_reviews_ratings' ),
					'add_new_item'          => __( 'Add New Review', 'pronamic_reviews_ratings' ),
					'add_new'               => __( 'Add New', 'pronamic_reviews_ratings' ),
					'new_item'              => __( 'New Review', 'pronamic_reviews_ratings' ),
					'edit_item'             => __( 'Edit Review', 'pronamic_reviews_ratings' ),
					'update_item'           => __( 'Update Review', 'pronamic_reviews_ratings' ),
					'view_item'             => __( 'View Review', 'pronamic_reviews_ratings' ),
					'view_items'            => __( 'View Reviews', 'pronamic_reviews_ratings' ),
					'search_items'          => __( 'Search Review', 'pronamic_reviews_ratings' ),
					'not_found'             => __( 'Not found', 'pronamic_reviews_ratings' ),
					'not_found_in_trash'    => __( 'Not found in Trash', 'pronamic_reviews_ratings' ),
					'featured_image'        => __( 'Featured Image', 'pronamic_reviews_ratings' ),
					'set_featured_image'    => __( 'Set featured image', 'pronamic_reviews_ratings' ),
					'remove_featured_image' => __( 'Remove featured image', 'pronamic_reviews_ratings' ),
					'use_featured_image'    => __( 'Use as featured image', 'pronamic_reviews_ratings' ),
					'insert_into_item'      => __( 'Insert into review', 'pronamic_reviews_ratings' ),
					'uploaded_to_this_item' => __( 'Uploaded to this review', 'pronamic_reviews_ratings' ),
					'items_list'            => __( 'Reviews list', 'pronamic_reviews_ratings' ),
					'items_list_navigation' => __( 'Reviews list navigation', 'pronamic_reviews_ratings' ),
					'filter_items_list'     => __( 'Filter reviews list', 'pronamic_reviews_ratings' ),
				),
				'supports'            => array( 'title', 'editor', 'thumbnail' ),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-admin-page',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
				'show_in_rest'        => true,
				'rewrite'             => array(
					'slug' => \get_option(
						'pronamic_reviews_rewrite_slug',
						\_x( 'reviews', 'Rewrite slug', 'pronamic_reviews_ratings' )
					),
				),
			)
		);
	}

	/**
	 * Add meta boxes.
	 *
	 * @param string $post_type Post Type.
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {
		if ( 'pronamic_review' !== $post_type ) {
			return;
		}

		\add_meta_box(
			'pronamic_reviews_ratings_review_details',
			__( 'Review details', 'pronamic_ideal' ),
			array( $this, 'meta_box_review_details' ),
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Pronamic Pay gateway config meta box.
	 *
	 * @param \WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_review_details( $post ) {
		\wp_nonce_field( 'pronamic_reviews_ratings_save_review', 'pronamic_reviews_ratings_nonce' );

		require __DIR__ . '/../views/admin/meta-box-review-details.php';
	}

	/**
	 * Save review post.
	 *
	 * @param int $post_id Review post ID.
	 * @return void
	 */
	public function save_review_post( $post_id ) {
		// Save meta box details.
		if (
			\filter_has_var( \INPUT_POST, 'pronamic_reviews_ratings_nonce' )
				&&
			\check_admin_referer( 'pronamic_reviews_ratings_save_review', 'pronamic_reviews_ratings_nonce' )
		) {
			// Object post ID.
			if ( \filter_has_var( \INPUT_POST, 'pronamic_review_object_post_id' ) ) {
				$object_post_id = \filter_input( \INPUT_POST, 'pronamic_review_object_post_id', \FILTER_VALIDATE_INT );

				if ( empty( $object_post_id ) ) {
					\delete_post_meta( $post_id, '_pronamic_review_object_post_id' );
				} else {
					\update_post_meta( $post_id, '_pronamic_review_object_post_id', $object_post_id );
				}
			}

			// Author.
			if ( \filter_has_var( \INPUT_POST, 'pronamic_review_author' ) ) {
				$author = \filter_input( \INPUT_POST, 'pronamic_review_author', \FILTER_SANITIZE_STRING );

				if ( empty( $author ) ) {
					\delete_post_meta( $post_id, '_pronamic_review_author' );
				} else {
					\update_post_meta( $post_id, '_pronamic_review_author', $author );
				}
			}

			// Ratings.
			$rating_types = Util::get_review_rating_types( $post_id );

			$ratings = \filter_input( \INPUT_POST, 'pronamic_review_rating', \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY );

			foreach ( $rating_types as $type ) {
				$name = $type['name'];

				if ( ! \array_key_exists( $name, $ratings ) ) {
					continue;
				}

				if ( empty( $ratings[ $name ] ) ) {
					\delete_post_meta( $post_id, '_pronamic_rating_value_' . $name );
				} else {
					\update_post_meta( $post_id, '_pronamic_rating_value_' . $name, round( $ratings[ $name ], 2 ) );
				}
			}
		}
	}

	/**
	 * The review content filter.
	 *
	 * @param string $content Review post content.
	 * @return string
	 */
	public function review_content( $content ) {
		$post_id = \get_the_ID();

		if ( 'pronamic_review' !== \get_post_type( $post_id ) ) {
			return $content;
		}

		\ob_start();

		require __DIR__ . '/../views/review-ratings.php';

		$ratings_content = \ob_get_clean();

		return $content . \PHP_EOL . $ratings_content;
	}
}
