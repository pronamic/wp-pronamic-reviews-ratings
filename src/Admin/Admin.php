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
use Pronamic\WordPress\ReviewsRatings\Util;
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

		// Admin scripts.
		\add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Admin settings.
		$this->admin_settings = new AdminSettings( $plugin );
	}

	/**
	 * Admin scripts.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		\wp_register_script(
			'select2',
			\plugin_dir_url( $this->plugin->file ) . 'assets/select2/js/select2.js',
			array( 'jquery' ),
			'4.1.0',
			true
		);

		\wp_register_style(
			'select2',
			\plugin_dir_url( $this->plugin->file ) . 'assets/select2/css/select2.css',
			array(),
			'4.1.0',
		);
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
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
	 * @link https://shibashake.com/wordpress-theme/add-a-metabox-to-the-edit-comments-screen
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
	 * Rating stars.
	 *
	 * @param int $rating_value The rating value.
	 * @param int $star_size    Size of a rating star.
	 * 
	 * @return string
	 */
	public function get_rating_stars( $rating_value, $star_size = 20 ) {
		if ( ! is_numeric( $rating_value ) ) {
			return;
		}

		$percentage = ( $rating_value / 10 ) * 100;

		?>
		<svg width="<?php echo esc_attr( $star_size * 5 ); ?>" height="<?php echo esc_attr( $star_size ); ?>" xmlns="http://www.w3.org/2000/svg">
			<defs>
				<symbol id="star" width="<?php echo esc_attr( $star_size ); ?>" height="<?php echo esc_attr( $star_size ); ?>" viewBox="0 0 24 24">
					<path d="M12 0.587036L15.668 8.15504L24 9.30604L17.936 15.134L19.416 23.413L12 19.446L4.583 23.413L6.064 15.134L0 9.30604L8.332 8.15504L12 0.587036Z" fill="currentColor" />
				</symbol>

				<clipPath id="star-clip">
					<rect x="0" y="0" width="<?php echo esc_attr( $percentage ); ?>%" height="100%" />
				</clipPath>
			</defs>

			<g opacity="0.3">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>

					<use xlink:href="#star" x="<?php echo esc_attr(  $i * $star_size ); ?>" y="0" />

				<?php endfor; ?>
			</g>

			<g clip-path="url(#star-clip)">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>

					<use xlink:href="#star" x="<?php echo esc_attr(  $i * $star_size ); ?>" y="0" />

				<?php endfor; ?>
			</g>
		</svg>

		<?php
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
				$rating_value = \get_post_meta( $post_id, '_pronamic_rating_value', true );
				$rating_count = (int) \get_post_meta( $post_id, '_pronamic_rating_count', true );

				if ( 'pronamic_review' === \get_post_type( $post_id ) ) {
					$rating_value = \get_post_meta( $post_id, '_pronamic_rating', true );
					$rating_count = 1;
				}

				// Scores.
				$object_post_id = \get_post_meta( $post_id, '_pronamic_review_object_post_id', true );

				$post_type = \get_post_type( empty( $object_post_id ) ? $post_id : $object_post_id );

				$scores = Util::get_post_type_ratings_scores( $post_type );

				if ( \is_numeric( $rating_value ) && $rating_count > 0 ) {
					$this->get_rating_stars( $rating_value );
				} else {
					echo '&mdash;';
				}

				break;
		}
	}
}
