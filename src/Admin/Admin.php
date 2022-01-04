<?php
/**
 * Admin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings\Admin;

use Pronamic\WordPress\ReviewsRatings\Plugin;
use WP_Comment;

/**
 * Admin
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Admin {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Admin settings.
	 *
	 * @var AdminSettings
	 */
	private $admin_settings;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		\add_action( 'admin_init', array( $this, 'admin_init' ) );
		\add_action( 'admin_init', array( $this, 'update_db_version' ), 5 );
		\add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Admin settings.
		$this->admin_settings = new AdminSettings( $plugin );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		foreach ( \get_post_types( array( 'public' => true ) ) as $post_type ) {
			// Check post type support.
			if ( ! \post_type_supports( $post_type, 'pronamic_ratings' ) ) {
				continue;
			}

			// Add columns actions/filters.
			$screen_id = 'edit-' . $post_type;

			\add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'manage_posts_columns' ), 10, 1 );
			\add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
			\add_action( 'manage_' . $screen_id . '_sortable_columns', array( $this, 'post_sortable_columns' ), 10 );
		}

		\add_filter( 'manage_pronamic_review_posts_columns', array( $this, 'manage_posts_columns' ), 10, 1 );
		\add_action( 'manage_pronamic_review_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
		\add_action( 'manage_pronamic_review_sortable_columns', array( $this, 'post_sortable_columns' ), 10 );
	}

	/**
	 * Update database.
	 *
	 * @return void
	 */
	public function update_db_version() {
		$option_name = 'pronamic_reviews_ratings_db_version';

		// Check database version.
		if ( \get_option( $option_name ) === Plugin::DB_VERSION ) {
			return;
		}

		// Install database table.
		\pronamic_ratings_install_table(
			'pronamic_post_ratings',
			'
			rating_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			rating_value FLOAT NOT NULL,
			rating_count BIGINT(20) UNSIGNED DEFAULT 0,
			PRIMARY KEY (rating_id),
			UNIQUE KEY post_id (post_id)'
		);

		// Update database version option.
		\update_option( $option_name, Plugin::DB_VERSION );
	}

	/**
	 * Add meta boxes.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/3.8.2/wp-admin/edit-form-comment.php#L130
	 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
	 * @link http://shibashake.com/wordpress-theme/add-a-metabox-to-the-edit-comments-screen
	 * @return void
	 */
	public function add_meta_boxes() {
		\add_meta_box(
			'pronamic_comment_ratings',
			__( 'Ratings', 'pronamic_reviews_ratings' ),
			array( $this, 'comment_meta_box_ratings' ),
			'comment',
			'normal'
		);
	}

	/**
	 * Comment meta box ratings.
	 *
	 * @param WP_Comment $comment Comment.
	 * @return void
	 */
	public function comment_meta_box_ratings( $comment ) {
		\wp_nonce_field( 'pronamic_comment_ratings_save', 'pronamic_comment_ratings_meta_box_nonce' );

		require_once $this->plugin->dir_path . 'admin/comment-meta-box-ratings.php';
	}

	/**
	 * Custom columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function manage_posts_columns( $columns ) {
		$columns['pronamic_rating'] = __( 'Rating', 'pronamic_reviews_ratings' );

		$new_columns = array();

		foreach ( $columns as $name => $label ) {
			if ( 'comments' === $name ) {
				$new_columns['pronamic_rating'] = $columns['pronamic_rating'];
			}

			$new_columns[ $name ] = $label;
		}

		$columns = $new_columns;

		return $columns;
	}

	/**
	 * Sortable columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function post_sortable_columns( $columns ) {
		$columns['pronamic_rating'] = 'rating';

		return $columns;
	}

	/**
	 * Post custom column.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function manage_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'pronamic_rating':
				$scores = \apply_filters( 'pronamic_reviews_ratings_scores', range( 1, 10 ) );

				$rating_value = \get_post_meta( $post_id, '_pronamic_rating_value', true );
				$rating_count = (int) \get_post_meta( $post_id, '_pronamic_rating_count', true );

				if ( 'pronamic_review' === \get_post_type( $post_id ) ) {
					$rating_value = \get_post_meta( $post_id, '_pronamic_rating', true );
					$rating_count = 1;
				}

				// Filter ratings score by post type.
				$object_post_id = \get_post_meta( $post_id, '_pronamic_review_object_post_id', true );

				$object_post_type = \get_post_type( empty( $object_post_id ) ? $post_id : $object_post_id );

				$scores = \apply_filters( 'pronamic_reviews_ratings_scores_' . $object_post_type, $scores );

				if ( \is_numeric( $rating_value ) && $rating_count > 0 ) {
					$max_score = max( $scores );

					for ( $i = 0; $i < $max_score; $i++ ) {
						$value = $rating_value - $i;

						$class = 'empty';

						if ( $value >= 1 ) {
							$class = 'filled';
						} elseif ( $value >= 0.5 ) {
							$class = 'half';
						}

						\printf(
							'<span class="dashicons dashicons-star-%s"></span>',
							\esc_attr( $class )
						);
					}
				} else {
					echo '&mdash;';
				}

				break;
		}
	}
}
