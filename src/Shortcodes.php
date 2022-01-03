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
		\add_shortcode( 'pronamic_reviews', array( $this, 'pronamic_reviews' ) );
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
				'object_post_id' => null,
				'count'          => 5,
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
