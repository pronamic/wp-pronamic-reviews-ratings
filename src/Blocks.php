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
        
        wp_localize_script( 'pronamic-pay-reviews-ratings-blocks', 'rest_prefix', array(
            'prefix' => rest_get_url_prefix(),
        ) );
        wp_enqueue_script( 'pronamic-pay-reviews-ratings-blocks' );
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
		// Block review author.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/review-author',
			array(
				'uses_context'    => array(
					'postId',
				),
				'attributes'      => array(
					'showText' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id = $block->context['postId'];

					$value = \get_post_meta( $post_id, '_pronamic_review_author', true );

					return sprintf(
						'%s<span class="pronamic-review-author">%s</span>',
						( $attributes['showText'] ) ? '<span>' . __( 'Review by ', 'pronamic_reviews_ratings' ) . '</span>' : '',
						\esc_html( $value )
					);
				},
			)
		);

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
				'attributes'      => array(
					'showNumber'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'showText'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'displayText' => array(
						'type'    => 'string',
						'default' => 'Overall rating',
					),
					'ratingType'  => array(
						'type'    => 'string',
						'default' => 'Overall rating',
					),
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$rating = 0;
					$scores = 10;
					$max_score = $scores;
					
					$post_id = $block->context['postId'];

					if ( get_post_type() == 'pronamic_review' ) {
						$rating_extension = ( 'Overall rating' != $attributes['ratingType'] ) ? '_value_' . $attributes['ratingType'] : '';
						$ratings = \get_post_meta( $post_id, '_pronamic_rating' . $rating_extension, false );

						foreach ( $ratings as $selected_rating ) {
							$rating += $selected_rating;
						}

						$rating = ( 0 == $rating ) ? 0 : $rating / count( $ratings );
					} else {
						$rating_extension = ( 'Overall rating' != $attributes['ratingType'] ) ? '_' . $attributes['ratingType'] : '';
						$rating = \get_post_meta( $post_id, '_pronamic_rating_value' . $rating_extension, true );
					}

					$html = '';

					for ( $i = 0; $i < $max_score; $i++ ) {
						$value = (float)$rating - $i;

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

					if ( empty( $rating ) ) {
						$rating = '0';
					}

					$show_text = sprintf(
						'<span class="rating-text"> %s </span>',
						( $attributes['showText'] ) ? $attributes['displayText'] : ''
					);

					$show_number = sprintf(
						'<span class="rating-number"> %s </span>',
						( $attributes['showNumber'] ) ? ( '10' == $rating ) ? number_format( $rating, 1 ) : "<span style='opacity:0;'>0</span>" . number_format( $rating, 1 ) : ''
					);


					return sprintf(
						'<span class="rating">
                            %s <br style="display: none;">
                            <span class="pronamic-rating-stars"> %s </span> 
                            %s 
                        </span>',
						$show_text,
						$html,
						$show_number
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

		// Block sub rating count.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/rating-sub-stars',
			array(
				'uses_context'    => array(
					'postId',
				),
				'attributes'      => array(
					'showNumber' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id = $block->context['postId'];
					
					$rating_types = Util::get_review_rating_types( $post_id );
				
					$html = '';

					foreach ( $rating_types as $rating_type ) {
						$rating_name = $rating_type['name'];
						$rating_label = $rating_type['label'];

						$rating = (int) \get_post_meta( $post_id, '_pronamic_rating_value_' . $rating_name, true );

						if ( 0 == $rating ) {
							continue;
						}

						$scores = Util::get_post_type_ratings_scores( \get_post_type( $post_id ) );

						$max_score = max( $scores );

						$stars = '';

						for ( $i = 0; $i < $max_score; $i++ ) {
							$value = $rating - $i;
	
							$class = 'empty';
	
							if ( $value >= 1 ) {
								$class = 'filled';
							} elseif ( $value >= 0.5 ) {
								$class = 'half';
							}

							$stars .= \sprintf(
								'<span class="dashicons dashicons-star-%s"></span>',
								\esc_attr( $class )
							);
						}

						if ( empty( $rating ) ) {
							$rating = '0';
						}

						$show_number = sprintf(
							'%s',
							( $attributes['showNumber'] ) ? ( '10' == $rating ) ? number_format( $rating, 1 ) : "<span style='opacity:0;'>0</span>" . number_format( $rating, 1 ) : ''
						);
                        $show_number = ( empty( $show_number ) ) ? $show_number : '<span class="rating-sub-number">' . $show_number . '</span>';


						$html .= sprintf(
							'<span class="rating">
                                <span class="rating-sub-text"> %s </span> <br style="display: none;">
                                <span class="pronamic-rating-sub-stars"> %s </span> 
                                %s 
                            </span>',
							$rating_label,
							$stars,
							$show_number
						);
					}

					// Template for in the block editor.
					if ( empty( $html ) ) {
						$html = \sprintf(
							'
                            <span class="rating"><span class="rating-sub-text"> sub-star rating </span> <span class="pronamic-rating-sub-stars"><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span><span class="dashicons dashicons-star-empty"></span></span> %s</span>
                            ',
							( $attributes['showNumber'] ) ? '<span class="rating-sub-number"> <span style="opacity:0;">0</span>0.0</span>' : ''
						);
					}

					return $html;               
				},
			)
		);

		// Block reviewed object title.
		\register_block_type(
			$this->plugin->dir_path . 'js/dist/blocks/rating-reviewed-object-title',
			array(
				'uses_context'    => array(
					'postId',
				),
				'render_callback' => function ( $attributes, $content, $block ) {
					if ( ! array_key_exists( 'postId', $block->context ) ) {
						return '';
					}

					$post_id               = $block->context['postId'];
					$object_post_id        = \get_post_meta( $post_id, '_pronamic_review_object_post_id', true );
					$reviewed_object_title = \get_the_title( $object_post_id );

					return '<span class="rating-reviewed-object-title">' . $reviewed_object_title . '</span>';
				},
			)
		);
	}
}
