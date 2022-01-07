<?php
/**
 * Shortcodes
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Shortcodes
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class Shortcodes {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct shortcodes support.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Shortcodes.
		\add_shortcode( 'pronamic_rating_value', array( $this, 'pronamic_rating_value' ) );
		\add_shortcode( 'pronamic_rating_count', array( $this, 'pronamic_rating_count' ) );
		\add_shortcode( 'pronamic_rating_stars', array( $this, 'pronamic_rating_stars' ) );
		\add_shortcode( 'pronamic_ratings', array( $this, 'pronamic_ratings' ) );
		\add_shortcode( 'pronamic_reviews', array( $this, 'pronamic_reviews' ) );
	}

	/**
	 * Shortcode `pronamic_rating_value`.
	 *
	 * @return string
	 */
	public function pronamic_rating_value() {
		$value = \get_post_meta( \get_the_ID(), '_pronamic_rating_value', true );

		return empty( $value ) ? '' : Util::format_rating( $value );
	}

	/**
	 * Shortcode `pronamic_rating_count`.
	 *
	 * @return string
	 */
	public function pronamic_rating_count() {
		$count = (int) \get_post_meta( \get_the_ID(), '_pronamic_rating_count', true );

		return \sprintf(
			/* translators: %d: number of ratings */
			\_n( '%d rating', '%d ratings', $count, 'pronamic_reviews_ratings' ),
			$count
		);
	}

	/**
	 * Shortcode `pronamic_rating_stars`.
	 *
	 * @return string
	 */
	public function pronamic_rating_stars() {
		$rating = (int) \get_post_meta( \get_the_ID(), '_pronamic_rating_value', true );

		$scores = Util::get_post_type_ratings_scores( \get_post_type() );

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
	}

	/**
	 * Shortcode `pronamic_ratings`.
	 *
	 * @param array<mixed> $args Shortcode arguments.
	 * @return string
	 */
	public function pronamic_ratings( $args ) {
		if ( ! \post_type_supports( \get_post_type(), 'pronamic_ratings' ) ) {
			return '';
		}

		\ob_start();

		\wp_enqueue_style( 'dashicons' );

		require __DIR__ . '/../views/object-ratings.php';

		$ratings_content = \ob_get_clean();

		return $ratings_content;
	}

	/**
	 * Shortcode `pronamic_reviews`.
	 *
	 * @param array<mixed> $args Shortcode arguments.
	 * @return string
	 */
	public function pronamic_reviews( $args ) {
		// Shortcode arguments.
		$args = \wp_parse_args(
			$args,
			array(
				'count'          => 5,
				'global'         => false,
				'object_post_id' => null,
			)
		);

		/*
		 * Query.
		 */
		$query = array(
			'post_type'      => 'pronamic_review',
			'posts_status'   => 'publish',
			'posts_per_page' => (int) $args['count'],
		);

		// Object post ID meta query.
		if ( ! empty( $args['object_post_id'] ) ) {
			$query['meta_query'] = array(
				array(
					'key'   => '_pronamic_review_object_post_id',
					'value' => $args['object_post_id'],
				),
			);
		}

		if ( 'true' === $args['global'] ) {
			$query['meta_query'] = array(
				array(
					'key'     => '_pronamic_review_object_post_id',
					'compare' => 'NOT EXISTS',
				),
			);
		}

		$query = new \WP_Query( $query );

		// Load template.
		$path = \locate_template( '/templates/pronamic-reviews.php' );

		if ( empty( $path ) ) {
			$path = __DIR__ . '/../templates/pronamic-reviews.php';
		}

		ob_start();

		require $path;

		$content = \ob_get_clean();

		if ( empty( $content ) ) {
			return '';
		}

		// Enqueue style and return content.
		\wp_enqueue_style(
			'pronamic-reviews-ratings',
			plugins_url( '../css/style.css', __FILE__ ),
			array(),
			$this->plugin->get_version()
		);

		return $content;
	}
}
