<?php

/**
 * Admin
 */
class Pronamic_WP_ReviewsRatingsAdmin {
	private $plugin;

	//////////////////////////////////////////////////

	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'admin_init', array( $this, 'update' ), 5 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Admin initialize
	 */
	public function admin_init() {
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			if ( post_type_supports( $post_type, 'pronamic_ratings' ) ) {
				$screen_id = 'edit-' . $post_type;

				add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'manage_posts_columns' ), 10, 1 );
				add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
				add_action( 'manage_' . $screen_id . '_sortable_columns', array( $this, 'post_sortable_columns' ), 10 );
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Update
	 */
	public function update() {
		$option = 'pronamic_reviews_ratings_db_version';

		if ( get_option( $option ) != Pronamic_WP_ReviewsRatingsPlugin::DB_VERSION ) {
			$this->install();

			update_option( $option, Pronamic_WP_ReviewsRatingsPlugin::DB_VERSION );
		}
	}

	/**
	 * Install
	 */
	public function install() {
		pronamic_ratings_install_table( 'pronamic_post_ratings', '
			rating_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			rating_value FLOAT NOT NULL,
			rating_count BIGINT(20) UNSIGNED DEFAULT 0,
			PRIMARY KEY  (rating_id),
			UNIQUE KEY post_id (post_id)
		' );
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
	public function comment_meta_box_ratings( $comment ) {
		wp_nonce_field( 'pronamic_comment_ratings_save', 'pronamic_comment_ratings_meta_box_nonce' );

		include $this->plugin->dir_path . 'admin/comment-meta-box-ratings.php';
	}

	//////////////////////////////////////////////////

	/**
	 * Save post
	 *
	 * @param int $post_id
	 */
	public function save_post( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			pronamic_ratings_post_update( $post_id );
		}
	}

	//////////////////////////////////////////////////
	// Admin columns
	//////////////////////////////////////////////////

	public function manage_posts_columns( $columns ) {
		$columns['pronamic_rating'] = __( 'Rating', 'pronamic_reviews_ratings' );

		$new_columns = array();

		foreach ( $columns as $name => $label ) {
			if ( 'comments' == $name ) {
				$new_columns['pronamic_rating'] = $columns['pronamic_rating'];
			}

			$new_columns[ $name ] = $label;
		}

		$columns = $new_columns;

		return $columns;
	}

	public function post_sortable_columns( $columns ) {
		$columns['pronamic_rating'] = 'rating';

		return $columns;
	}

	public function manage_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'pronamic_rating' :
				$rating_value = get_post_meta( $post_id, '_pronamic_rating_value', true );
				$rating_count = get_post_meta( $post_id, '_pronamic_rating_count', true );

				if ( $rating_count > 0 ) {
					$score = round( $rating_value ) / 2;

					for ( $i = 0; $i < 5; $i++ ) {
						$value = $score - $i;

						$class = 'empty';

						if ( $value >= 1 ) {
							$class = 'filled';
						} elseif ( $value == 0.5 ) {
							$class = 'half';
						}

						printf( '<span class="dashicons dashicons-star-%s"></span>', $class );
					}
				} else {
					echo '&mdash;';
				}

				break;
		}
	}
}
