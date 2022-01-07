<?php
/**
 * Blocks
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Blocks
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class Blocks {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct blocks support.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		\add_action( 'init', array( $this, 'register_script' ) );
		\add_action( 'init', array( $this, 'register_block_types' ) );

		// Filters.
		\add_filter( 'block_categories_all', array( $this, 'block_categories' ), 10, 2 );
	}

	/**
	 * Register script.
	 *
	 * @return void
	 */
	public function register_script() {
		$asset_file = include $this->plugin->dir_path . 'js/dist/index.asset.php';

		\wp_register_script(
			'pronamic-pay-reviews-ratings-blocks',
			\plugins_url( '/js/dist/index.js', $this->plugin->file ),
			$asset_file['dependencies'],
			$asset_file['version'],
			false
		);

		// Script translations.
		\wp_set_script_translations(
			'pronamic-pay-reviews-ratings-blocks',
			'pronamic_reviews_ratings',
			__DIR__ . '/../languages'
		);
	}

	/**
	 * Block categories.
	 *
	 * @param array    $categories Block categories.
	 * @param \WP_Post $post       Post being loaded.
	 *
	 * @return array
	 */
	public function block_categories( $categories, $post ) {
		$categories[] = array(
			'slug'  => 'pronamic-reviews-ratings',
			'title' => __( 'Pronamic Reviews & Ratings', 'pronamic_reviews_ratings' ),
			'icon'  => null,
		);

		return $categories;
	}

	/**
	 * Register block types.
	 *
	 * @return void
	 */
	public function register_block_types() {
		// Block rating value.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/rating-value',
			array(
				'uses_context'    => array(
					'postId',
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id = $block->context['postId'];

					$value = \get_post_meta( $post_id, '_pronamic_rating_value', true );

					if ( ! empty( $value ) ) {
						$value = Util::format_rating( $value );
					}

					return sprintf( '<span class="pronamic-rating-value">%s</span>', $value );
				},
			)
		);

		// Block rating count.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/rating-count',
			array(
				'uses_context'    => array(
					'postId',
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id = $block->context['postId'];

					$count = (int) \get_post_meta( $post_id, '_pronamic_rating_count', true );

					return sprintf(
						'<span class="pronamic-rating-count">%s</span>',
						\sprintf(
							/* translators: %d: number of ratings */
							\_n( '%d rating', '%d ratings', $count, 'pronamic_reviews_ratings' ),
							$count
						)
					);
				},
			)
		);

		// Block rating count.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/rating-stars',
			array(
				'uses_context'    => array(
					'postId',
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id = $block->context['postId'];

					$rating = (int) \get_post_meta( $post_id, '_pronamic_rating_value', true );

					$scores = Util::get_post_type_ratings_scores( \get_post_type( $post_id ) );

					$max_score = max( $scores );

					$html = '';

					for ( $i = 0; $i < $max_score; $i++ ) {
						$value = $rating - $i;

						$class = 'empty';

						if ( $value >= 1 ) {
							$class = 'filled';
						} elseif ( $value >= 0.5 ) {
							$class = 'half';
						}

						$html .= \sprintf(
							'<span class="dashicons dashicons-star-%s"></span>',
							\esc_attr( $class )
						);
					}

					return sprintf(
						'<span class="pronamic-rating-stars">%s</span>',
						$html
					);
				},
			)
		);

		// Block ratings.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/ratings',
			array(
				'uses_context'    => array(
					'postId',
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id = $block->context['postId'];

					if ( ! \post_type_supports( \get_post_type( $post_id ), 'pronamic_ratings' ) ) {
						return '';
					}

					\ob_start();

					require __DIR__ . '/../views/object-ratings.php';

					$ratings_content = \ob_get_clean();

					return $ratings_content;
				},
			)
		);
	}
}
